Drupal.behaviors.searchFilterBehaviour = {
  attach(context) {
    const searchFilterContainer = context.querySelectorAll('.search-filter-container');
    const selectorSearchFilterContainer = '.search-filter-container';
    const searchFilterOpenButton = context.querySelectorAll('.search-filter-open-button');
    const clearAllButtons = context.querySelectorAll('.search-filter-block__button--clear-all');
    const applyFiltersButtons = context.querySelectorAll('.search-filter-block__button--apply');
    const filters = context.querySelectorAll('.filter-block');
    const filterCheckboxes = context.querySelectorAll('.checkbox-item');

    searchFilterContainer.forEach(filterContainer => {
      if (filterContainer === null || filterContainer.getAttribute('data-filter-init')) {
        return;
      }
      filterContainer.addEventListener('click', function(event) {
        const grid = getGridBlock(event);

        switch (true) {
          case event.target.classList.contains('search-filter-header__close'):
            event.target.closest('.search-filter-block').classList.remove('search-filter-block--opened');
            break;
          case event.target.classList.contains('checkbox-item__input'):
            enableApplyButtons();
            updateCounters(grid);
          case event.target.classList.contains('search-filter-info__applied-clear'):
            const currentFilter = document.getElementById(event.target.getAttribute('data-id'));
            if (currentFilter !== null) {
              currentFilter.checked = false;
              processFilters(grid);
            }
            updateCounters(grid);
            break;
        }
      });
      var filterInput = filterContainer.querySelector('input');
      if (filterInput !== null) {
        filterInput.addEventListener('keypress', (event) => {
          if (event.keyCode === 13) {
            const grid = getGridBlock(event);
            const gridId = getGridId(grid);
            event.target.dataset.gridQuery = prepareQuery(currentQueryFilters(gridId));
            var query = currentQuery();
            if (!query.hasOwnProperty('search')) {
              query.search = {};
            }
            query.search[gridId] = event.target.value;
            updateResults(prepareQuery(query), grid);
            updateFilters(prepareQuery(query), grid);
            pushQuery(query);
          }
        });
      }
      filterContainer.setAttribute('data-filter-init', true);
    });

    filters.forEach(filter => {
      filter.addEventListener('click', () => {
        let open = false;
        if (!filter.classList.contains('filter-block--open'))
          open = true;
        document.querySelectorAll('.filter-block--open').forEach(function (filter) {
          filter.classList.remove('filter-block--open');
        });
        if (open)
          filter.classList.toggle('filter-block--open');
      });
    });

    searchFilterOpenButton.forEach(filterOpenButton => {
      filterOpenButton.addEventListener('click', function(event) {
        const searchFilterBlock = getGridBlock(event).querySelector('.search-filter-block');
        searchFilterBlock.classList.add('search-filter-block--opened');
      });
    });

    clearAllButtons.forEach(function (button) {
      button.addEventListener('click', function(event) {
        const grid = getGridBlock(event);
        const gridId = getGridId(grid);
        grid.querySelector(selectorSearchFilterContainer).querySelectorAll('.checkbox-item__input:checked').forEach(function (input) {
          input.checked = false;
        });
        event.preventDefault();
        updateCounters(grid);
        updateResults(prepareQuery(currentQueryWithoutFilters(gridId)), grid);
        pushQuery(currentQueryWithoutFilters(gridId));
      });
    });

    applyFiltersButtons.forEach(function (button) {
      button.addEventListener('click', function(event) {
        event.preventDefault();
        event.target.closest('.search-filter-block').classList.remove('search-filter-block--opened');
        processFilters(getGridBlock(event));
      });
    });

    filterCheckboxes.forEach(checkbox => {
      checkbox.addEventListener('keypress', (e) => {
        if (e.keyCode === 13) {
          const grid = getGridBlock(e);
          let check = e.target.parentNode.getElementsByClassName('checkbox-item__input')[0];
          check.checked = !check.checked;
          updateCounters(grid);
          enableApplyButtons();
        }
      });
    });

    const getGridBlock = (event) => {
      const target = event.target;
      // Add ', .search-filter-container' to closest parameter for storybook
      return target.closest('[data-block-plugin-id]');
    }

    const getGridId = (grid) => {
      const gridData = grid.querySelector('[data-layer-grid-id]');
      if (gridData === null) {
        return 1;
      }
      return grid.querySelector('[data-layer-grid-id]').dataset.layerGridId;
    }

    // Prepare query object from browser search.
    const currentQuery = () => {
      const search = location.search;
      let hashes = search.slice(search.indexOf('?') + 1).split('&');
      return hashes.reduce(function(params, hash) {
        if (hash === '') {
          return params;
        }
        let [key, val] = hash.split('=');
        // @TODO Find better to parse id Url not supported for IE.
        let id = decodeURIComponent(key).split('[')[1];
        id = id.replace(']','');
        key = decodeURIComponent(key).split('[')[0];
        params[key] = {[id]: decodeURIComponent(val)};
        return params;
      }, {});
    }

    // Current query without taxonomy filters.
    const currentQueryWithoutFilters = (gridId) => {
      let queryMap = currentQuery();

      Object.keys(queryMap).filter(function (item, key) {
        if (item !== 'search' && item !== 'type' && queryMap[item].hasOwnProperty(gridId)) {
          delete queryMap[item][gridId];
          if (Object.keys(queryMap[item]).length == 0) {
            delete queryMap[item];
          }
          return false;
        }
        return true;
      });
      return queryMap;
    }

    // Current query taxonomy filters.
    const currentQueryFilters = (gridId) => {
      let queryMap = currentQuery();

      Object.keys(queryMap).filter(function (item, key) {
        if ((item === 'search') && queryMap[item].hasOwnProperty(gridId)) {
          delete queryMap[item][gridId];
          if (Object.keys(queryMap[item]).length == 0) {
            delete queryMap[item];
          }
          return false;
        }
        return true;
      });
      return queryMap;
    }

    // Update path state in browser without page reload.
    const prepareQuery = (query) => {
      let queryString = '';
      Object.keys(query).forEach(function (key) {
        if (typeof query[key] === 'object') {
          Object.keys(query[key]).forEach(function (id) {
            if (query[key].hasOwnProperty(id)) {
              queryString += `&${key}[${id}]=${query[key][id]}`;
            }
          });
        }
        else {
          if (query.hasOwnProperty(key)) {
            queryString += `&${key}=${query[key]}`;
          }
        }
      });
      return '?' + queryString.substr(1);
    }

    // Update path state in browser without page reload.
    const pushQuery = (query) => {
      window.history.pushState({}, '', location.pathname + prepareQuery(query));
    }

    const processFilters = (grid) => {
      const gridId = getGridId(grid);
      let queryElements = currentQueryWithoutFilters(gridId);
      let appliedFilters = [];
      let appliedIds = [];
      const filterBlocks = grid.querySelectorAll('.filter-block');

      filterBlocks.forEach(function(element) {
        const inputLabels = element.querySelectorAll('.checkbox-item__input:checked + label');
        const inputElements = element.querySelectorAll('.checkbox-item__input:checked');

        inputLabels.forEach(function(label) {
          appliedFilters.push(label.innerText);
        });
        inputElements.forEach(function(input) {
          appliedIds.push(input.getAttribute('id'));
        });
        if (appliedIds.length > 0) {
          const taxonomyFilter = appliedIds.reduce(function(params, key) {
            if (params === '') {
              return key.replace(gridId,'');
            }
            return params + ',' + key.replace(gridId,'');
          }, '');
          queryElements[element.getAttribute('data-filter')] = { [gridId]: taxonomyFilter };
          appliedIds = [];
        }
      });
      updateResults(prepareQuery(queryElements), grid);
      pushQuery(queryElements);
    };

    const updateCounters = (grid) => {
      let appliedFilters = '';
      let appliedFiltersCounter = 0;
      const filterBlocks = grid.querySelectorAll('.filter-block');
      const appliedFiltersContainer = grid.querySelector('.search-filter-info');
      const appliedFiltersBlock = grid.querySelector('.search-filter-info__applied');
      const appliedFiltersCount = grid.querySelector('.search-filter-info__applied-count');
      const appliedFiltersList = grid.querySelector('.search-filter-info__applied-text');
      const clearAllButton = grid.querySelector('.search-filter-info .search-filter-block__button--clear-all');

      filterBlocks.forEach(function(element) {
        const counterElement = element.querySelector('.filter-title__counter');
        const inputLabels = element.querySelectorAll('.checkbox-item__input:checked + label');
        let counter = inputLabels.length;
        counterElement.innerHTML = counter ? counter : '';
        inputLabels.forEach(function(label) {
          appliedFilters += '\
            <span class="search-filter-info__applied-name">\
              <span>'+ label.innerText +'</span>\
              <div data-id="'+ label.getAttribute('for') +'" class="search-filter-info__applied-clear"></div>\
            </span>\
            '
          appliedFiltersCounter++;
        });
      });

      if (appliedFilters.length) {
        appliedFiltersBlock.classList.remove('search-filter-info__applied--hidden');
        clearAllButton.classList.remove('search-filter-block__button--hidden');
        appliedFiltersContainer.classList.remove('search-filter-info--hidden');
      } else {
        appliedFiltersBlock.classList.add('search-filter-info__applied--hidden');
        clearAllButton.classList.add('search-filter-block__button--hidden');
        appliedFiltersContainer.classList.add('search-filter-info--hidden');
      }

      appliedFiltersCount.innerHTML = appliedFiltersCounter;
      appliedFiltersList.innerHTML = appliedFilters;
    }

    const updateResults = (query, grid) => {
      const searchResults = grid.querySelector('.ajax-card-grid__items');
      const searchNoResults = grid.querySelector('.card-grid-results .no-results-container');
      const searchBlock = grid.querySelector('.card-grid-results .ajax-card-grid');
      const pagerButton = grid.querySelector('.ajax-card-grid__more-link');
      const gridType = grid.querySelector('[data-layer-grid-type]').dataset.layerGridType;
      query += '&action_type=results';
      query += '&grid_type=' + gridType;
      if (gridType == 'grid') {
        query += '&grid_id=' + grid.querySelector('[data-layer-grid-id]').dataset.layerGridId;
        query += '&page_id=' + grid.querySelector('[data-layer-page-id]').dataset.layerPageId;
      }

      let xhr = new XMLHttpRequest();
      xhr.open('GET', '/search-callback' + query);
      xhr.responseType = 'json';
      xhr.send();
      xhr.onload = function() {
        if (xhr.status == 200) {
          searchResults.innerHTML = '';
          xhr.response.results.forEach(function(element) {
            var elementWrapper = document.createElement('div');
            elementWrapper.className = 'ajax-card-grid__item_wrapper';
            elementWrapper.innerHTML = element;
            searchResults.append(elementWrapper);
            Drupal.behaviors.productCard.attach(searchResults);
          });
          if (!xhr.response.pager) {
            pagerButton.classList.remove('active');
          }
          else {
            pagerButton.classList.add('active');
          }
          searchNoResults.innerHTML = xhr.response.no_results;
          if (xhr.response.no_results !== '') {
            searchBlock.classList.add('ajax-card-grid--no-results')
          }
          else {
            searchBlock.classList.remove('ajax-card-grid--no-results')
          }
          dataLayerPush(xhr.response.results_count, xhr.response.search_key, grid, gridType);
        }
      };
    }

    const updateFilters = (query, grid) => {
      const gridType = grid.querySelector('[data-layer-grid-type]').dataset.layerGridType;
      query += '&action_type=facet';
      query += '&grid_type=' + gridType;
      if (gridType == 'grid') {
        query += '&grid_id=' + grid.querySelector('[data-layer-grid-id]').dataset.layerGridId;
        query += '&page_id=' + grid.querySelector('[data-layer-page-id]').dataset.layerPageId;
      }

      let xhr = new XMLHttpRequest();
      xhr.open('GET', '/search-callback' + query);
      xhr.responseType = 'json';
      xhr.send();
      xhr.onload = function() {
        if (xhr.status == 200) {
          grid.querySelector('.card-grid-filter').innerHTML = xhr.response.filters;
          Drupal.behaviors.searchFilterBehaviour.attach(grid, drupalSettings);
        }
      };
    }

    const dataLayerPush = (results_count, search_key, grid, gridType) => {
      var eventPrefix = 'siteSearch',
          eventName = '';
      if (gridType == 'grid') {
        eventPrefix = 'cardGrid';
      }
      if (results_count === 0) {
        eventName = eventPrefix + 'Search_ResultNo';
      }
      else {
        eventName = eventPrefix + 'Search_ResultShown';
      }
      if (gridType == 'grid') {
        dataLayer.push({
          'event': eventName,
          [eventPrefix + 'ID']: grid.querySelector('[data-layer-grid-id]').dataset.layerGridId,
          [eventPrefix + 'Name']: grid.querySelector('[data-layer-grid-id]').dataset.layerGridName,
          [eventPrefix + 'SearchTerm']: search_key,
          [eventPrefix + 'SearchResultsNum']: results_count
        });
      }
      else {
        dataLayer.push({
          'event': eventName,
          [eventPrefix + 'Term']: search_key,
          [eventPrefix + 'ResultsNum']: results_count
        });
      }
    }

    const enableApplyButtons = () => {
      const applyButtons = context.querySelectorAll('.search-filter-block__button--apply');

      applyButtons.forEach(function(button) {
        button.classList.remove('search-filter-block__button--disabled');
      });
    }
  },
};
