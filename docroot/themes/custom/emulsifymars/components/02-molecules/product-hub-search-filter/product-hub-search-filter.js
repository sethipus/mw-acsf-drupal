(function (Drupal, drupalSettings, jQuery) {
  Drupal.behaviors.searchFilterBehaviour = {
    attach(context) {
      $(document).ready(function(){
        $('.clear-icon').click(function(){
            $(this).siblings('input').val('');
        })
    })
      const searchFilterContainer = context.querySelectorAll('.search-filter-container');
      const selectorSearchFilterContainer = '.search-filter-container';
      const searchFilterOpenButton = context.querySelectorAll('.search-filter-open-button'); /* mobile view ONLY */
      const clearAllButtons = context.querySelectorAll('.search-filter-block__button--clear-all');
      const applyFiltersButtons = context.querySelectorAll('.search-filter-block__button--apply');
      const filters = context.querySelectorAll('.filter-block');
      const filterCheckboxes = context.querySelectorAll('.checkbox-item');

      searchFilterContainer.forEach(filterContainer => {
        if (filterContainer === null || filterContainer.getAttribute('data-filter-init')) {
          return;
        }
        filterContainer.addEventListener('click', function (event) {
          const grid = getGridBlock(event);

        switch (true) {
          case event.target.classList.contains('search-filter-header__close'):
            event.target.closest('.search-filter-block').classList.remove('search-filter-block--opened');
            enableBodyScroll();
            break;
          case event.target.classList.contains('checkbox-item__input'):
            enableApplyButtons();
            updateAriaChecked(event.target);
            break;
          case event.target.classList.contains('search-filter-info__applied-clear'):
            const currentFilter = document.getElementById(event.target.getAttribute('data-id'));
            if (currentFilter !== null) {
              currentFilter.checked = false;
              updateAriaChecked(currentFilter);
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
        if (!filter.classList.contains('filter-block--open')) {
          open = true;
        }
        document.querySelectorAll('.filter-block--open').forEach(function (filter) {
          filter.classList.remove('filter-block--open');
          filter.querySelector('.filter-title').setAttribute('aria-expanded', false);
        });
        if (open) {
          filter.classList.toggle('filter-block--open');
          filter.querySelector('.filter-title').setAttribute('aria-expanded', true);
        }
      });
    });

    searchFilterOpenButton.forEach(filterOpenButton => {
      filterOpenButton.addEventListener('click', function(event) {
        const searchFilterBlock = getGridBlock(event).querySelector('.search-filter-block');
        searchFilterBlock.classList.add('search-filter-block--opened');
        disableBodyScroll();
      });
    });

    clearAllButtons.forEach(function (button) {
      button.addEventListener('click', function(event) {
        const grid = getGridBlock(event);
        const gridId = getGridId(grid);
        grid.querySelector(selectorSearchFilterContainer).querySelectorAll('.checkbox-item__input:checked').forEach(function (input) {
          input.checked = false;
          updateAriaChecked(input);
        });
        event.preventDefault();
        updateCounters(grid);
        updateResults(prepareQuery(currentQueryWithoutFilters(gridId)), grid);
        pushQuery(currentQueryWithoutFilters(gridId));
      });
    });

    applyFiltersButtons.forEach(function (button) {
      button.addEventListener('click', function(event) {
        const grid = getGridBlock(event);
        event.preventDefault();
        event.target.closest('.search-filter-block').classList.remove('search-filter-block--opened');
        enableBodyScroll();
        const filterBlock = event.target.closest('.filter-block');
        if (filterBlock !== null) {
          filterBlock.querySelector('.filter-title').focus();
        }
        updateCounters(grid);
        processFilters(getGridBlock(event));
      });
    });

    filterCheckboxes.forEach(checkbox => {
      checkbox.addEventListener('keypress', (event) => {
        if (event.keyCode === 32) {
          event.preventDefault();
          const grid = getGridBlock(event);
          let check = checkbox.getElementsByClassName('checkbox-item__input')[0];
          check.checked = !check.checked;
          enableApplyButtons();
          updateAriaChecked(check);
        }
      });
    });

    const updateAriaChecked = (checkboxElement) => {
      const checkboxLabel = checkboxElement.closest('li');
      if (checkboxElement.checked == true) {
        checkboxLabel.setAttribute('aria-selected', true);
      } else {
        checkboxLabel.setAttribute('aria-selected', false);
      }
    };

    const getGridBlock = (event) => {
      const target = event.target;
      // Add ', .search-filter-container' to closest parameter for storybook
      return target.closest('[data-block-plugin-id]') || document;
    };

    const getGridId = (grid) => {
      const gridData = grid.querySelector('[data-layer-grid-id]');
      if (gridData === null) {
        return 1;
      }
      return grid.querySelector('[data-layer-grid-id]').dataset.layerGridId;
    };

    // Prepare query object from browser search.
    const currentQuery = () => {
      const search = location.search;
      let hashes = search.slice(search.indexOf('?') + 1).split('&');
      return hashes.reduce(function (params, hash) {
        if (hash === '') {
          return params;
        }
        let [key, val] = hash.split('=');
        // Skipping GA 's' query param to don't include it into SOLR query.
        if (key === 's') {
          return params;
        }
        // @TODO Find better to parse id Url not supported for IE.
        let id = decodeURIComponent(key).split('[')[1];
        id = id ? id.replace(']', '') : '';
        key = decodeURIComponent(key).split('[')[0];
        params[key] = {[id]: decodeURIComponent(val)};
        return params;
      }, {});
    };

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
        // Adding 's' query param to pass search string to GA dashboard.
        if (queryMap.hasOwnProperty('search') && queryMap.search.hasOwnProperty('1')) {
          queryMap['s'] = queryMap.search['1'];
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

      filterBlocks.forEach(function (element) {
        const inputLabels = element.querySelectorAll('.checkbox-item__input:checked + label');
        const inputElements = element.querySelectorAll('.checkbox-item__input:checked');

          inputLabels.forEach(function (label) {
            appliedFilters.push(label.innerText);
          });
          inputElements.forEach(function (input) {
            appliedIds.push(input.getAttribute('id'));
          });
          if (appliedIds.length > 0) {
            const taxonomyFilter = appliedIds.reduce(function (params, key) {
              if (params === '') {
                return key.replace(gridId, '').replace(element.getAttribute('data-filter'), '');
              }
              return params + ',' + key.replace(gridId, '').replace(element.getAttribute('data-filter'), '');
            }, '');
            queryElements[element.getAttribute('data-filter')] = {[gridId]: taxonomyFilter};
            appliedIds = [];
          }
        });
        updateResults(prepareQuery(queryElements), grid);
        // Adding 's' query param to pass search string to GA dashboard.
        if (queryElements.hasOwnProperty('search') && queryElements.search.hasOwnProperty(1)) {
          queryElements['s'] = queryElements.search['1'];
        }
        pushQuery(queryElements);
      };

      const updateCounters = (grid) => {
        let appliedFilters = '';
        let appliedFiltersAnnounce = [];
        const filterBlocks = grid.querySelectorAll('.filter-block');
        const appliedFiltersContainer = grid.querySelector('.search-filter-info');
        const appliedFiltersBlock = grid.querySelector('.search-filter-info__applied');
        const appliedFiltersCount = grid.querySelector('.search-filter-info__applied-count');
        const appliedFiltersList = grid.querySelector('.search-filter-info__applied-text');
        const clearAllButton = grid.querySelector('.search-filter-info .search-filter-block__button--clear-all');

        filterBlocks.forEach(function (element) {
          const counterElement = element.querySelector('.filter-title__counter');
          const inputLabels = element.querySelectorAll('.checkbox-item__input:checked + label');
          let counter = inputLabels.length;
          counterElement.innerHTML = counter ? counter : '';
          if (counter) {
            inputLabels.forEach(function (label) {
              appliedFilters += '\
              <li class="search-filter-info__applied-name">\
                <span>' + label.innerText + '</span>\
                <button data-id="' + label.getAttribute('for') + '" class="search-filter-info__applied-clear" aria-label="' + Drupal.t('remove ' + label.innerText) + ' "></button>\
              </li>\
              ';
              appliedFiltersAnnounce.push(label.innerText);
            });
          }
        });

        if (appliedFilters.length) {
          appliedFiltersBlock.classList.remove('search-filter-info__applied--hidden');
          clearAllButton.classList.remove('search-filter-block__button--hidden');
          appliedFiltersContainer.classList.remove('search-filter-info--hidden');
          Drupal.announce(Drupal.t('Applied filters (') + appliedFiltersAnnounce.length + '): ' + appliedFiltersAnnounce.join(', '));
        }
        else {
          appliedFiltersBlock.classList.add('search-filter-info__applied--hidden');
          clearAllButton.classList.add('search-filter-block__button--hidden');
          appliedFiltersContainer.classList.add('search-filter-info--hidden');
        }

        appliedFiltersCount.innerHTML = appliedFiltersAnnounce.length;
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
        query += '&page_id=' + grid.querySelector('[data-layer-page-id]').dataset.layerPageId;
        query += '&page_revision_id=' + grid.querySelector('[data-layer-page-revision-id]').dataset.layerPageRevisionId;
        if (gridType == 'grid') {
          query += '&grid_id=' + grid.querySelector('[data-layer-grid-id]').dataset.layerGridId;
        }
        query += '&limit=' + Drupal.behaviors.loadMorePager.getLimitByGridType(gridType);

        let xhr = new XMLHttpRequest();
        xhr.open('GET', drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix + 'search-callback' + query);
        xhr.responseType = 'json';
        xhr.send();
        xhr.onload = function () {
          if (xhr.status == 200) {
            searchResults.innerHTML = '';
            xhr.response.results.forEach(function (element) {
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
            // Update Smart Commerce buttons on changing grid filters.
            if (typeof SmartCart !== 'undefined') {
              SmartCart.updateUsaWidget();
            }
          }
        };
      }

      const updateFilters = (query, grid) => {
        const gridType = grid.querySelector('[data-layer-grid-type]').dataset.layerGridType;
        query += '&action_type=facet';
        query += '&grid_type=' + gridType;
        query += '&page_id=' + grid.querySelector('[data-layer-page-id]').dataset.layerPageId;
        query += '&page_revision_id=' + grid.querySelector('[data-layer-page-revision-id]').dataset.layerPageRevisionId;
        if (gridType == 'grid') {
          query += '&grid_id=' + grid.querySelector('[data-layer-grid-id]').dataset.layerGridId;
        }
        query += '&limit=' + Drupal.behaviors.loadMorePager.getLimitByGridType(gridType);

        let xhr = new XMLHttpRequest();
        xhr.open('GET', drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix + 'search-callback' + query);
        xhr.responseType = 'json';
        xhr.send();
        xhr.onload = function () {
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
        if (typeof dataLayer !== 'undefined') {
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
      }

      const enableApplyButtons = () => {
        const applyButtons = context.querySelectorAll('.search-filter-block__button--apply');

        applyButtons.forEach(function (button) {
          button.classList.remove('search-filter-block__button--disabled');
        });
      }

      const enableBodyScroll = () => {
        let scrollY = document.body.style.top;
        document.body.classList.remove('locked-scroll');
        document.body.style.top = '';
        window.scrollTo(0, parseInt(scrollY || '0') * -1);
      }

      const disableBodyScroll = () => {
        let offset = window.scrollY;
        document.body.classList.add('locked-scroll');
        if (offset) {
          document.body.style.top = `-${offset}px`;
        }
      }
    },
  };
})(Drupal, window.drupalSettings, jQuery);
