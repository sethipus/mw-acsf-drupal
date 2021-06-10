/**
 * @file
 * Javascript for the ajax filter of search components.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.searchFilterSearchPage = {
    attach: function (context, settings) {
      var gridBlock = context.querySelector('[data-layer-grid-type]');
      if (gridBlock === null || gridBlock.getAttribute('data-filter-init')) {
        return;
      }
      var gridType = gridBlock.dataset.layerGridType;
      var pageId = gridBlock.dataset.layerPageId;
      var pageRevisionId = gridBlock.dataset.layerPageRevisionId;
      if (gridType === 'search_page') {
        var selectorInput = '.search-page-header input';
        var selectorTypeFilter = '.search-page-header .search-results-container .results__container a';
        var selectorResults = '.ajax-card-grid .ajax-card-grid__items';
        var searchNoResults = '.search-results-page .no-results-container';
        var searchBlock = '.search-results-page .ajax-card-grid';
        var selectorSearchPager = '.ajax-card-grid .ajax-card-grid__more-link'
        var selectorTypeFilterWrapper = '.search-page-header .search-results-container';
        var selectorFilterWrapper = '.search-results-filter .search-filter-container';
        var selectorSearchHeaderKeys = '.results-key-header-container';
      }

      // Prepare query object from browser search.
      const currentQuery = () => {
        var search = location.search;
        var hashes = search.slice(search.indexOf('?') + 1).split('&');
        return hashes.reduce(function(params, hash) {
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
          id = id.replace(']','');
          key = decodeURIComponent(key).split('[')[0];
          params[key] = {[id]: decodeURIComponent(val)};
          return params;
        }, {});
      }

      // Update path state in browser without page reload.
      var pushQuery = function(query) {
        var queryString = '';
        Object.keys(query).forEach(function (key) {
          if (typeof query[key] === 'object') {
            Object.keys(query[key]).forEach(function (id) {
              queryString += `&${key}[${id}]=${query[key][id]}`;
            });
          }
          else {
            queryString += `&${key}=${query[key]}`;
          }
        });
        window.history.pushState({}, '', location.pathname + '?' + queryString.substr(1));
      }

      // Update search results.
      var updateSearchResults = function(results) {
        var searchItems = $(selectorResults);
        searchItems.empty();
        results.forEach(function(element) {
          var elementWrapper = document.createElement('div');
          elementWrapper.className = 'ajax-card-grid__item_wrapper';
          elementWrapper.innerHTML = element;
          searchItems.append(elementWrapper);
          Drupal.behaviors.productCard.attach(searchItems, settings);
        });

        // Update Smart Commerce buttons on changing grid filters.
        if (typeof SmartCart !== "undefined") {
          SmartCart.updateUsaWidget();
        }
      }

      // Toggle pager.
      var togglePager = function(pager) {
        if (!pager) {
          $(selectorSearchPager).removeClass('active');
        }
        else {
          $(selectorSearchPager).addClass('active');
        }
      }

      var setNoResults = function(noResults) {
        $(searchNoResults).html(noResults);
        if (noResults !== '') {
          $(searchBlock).addClass('ajax-card-grid--no-results')
        }
        else {
          $(searchBlock).removeClass('ajax-card-grid--no-results')
        }
      }

      var setSearchKeyHeader = function(key, noResults) {
        if (key !== '' && (noResults === '' || noResults === null || typeof noResults === 'undefined')) {
          $(selectorSearchHeaderKeys).text(Drupal.t('Results for: ') + key);
          $(selectorSearchHeaderKeys).addClass('active');
        }
        else {
          $(selectorSearchHeaderKeys).text('');
          $(selectorSearchHeaderKeys).removeClass('active');
        }
      }

      // Data layer push event.
      var dataLayerPush = function(results_count, search_text) {
        var eventPrefix = 'siteSearch',
          eventName = '';
        if (results_count === 0) {
          // SITE SEARCH NO RESULT
          eventName = [eventPrefix, 'ResultNo'].join('_');
        } else {
          // SITE SEARCH RESULT SHOWN
          eventName = [eventPrefix, 'ResultShown'].join('_');
        }
        dataLayer.push({
          'event': eventName,
          [eventPrefix + 'Term']: search_text,
          [eventPrefix + 'ResultsNum']: results_count
        });
      }

      $(selectorInput, context).on('keypress', function (e) {
        if (e.which == 13) {
          // Prepare request query.
          var query = currentQuery();
          var searchKey = $(this).val();
          if (searchKey === '') {
            delete query.search;
          }
          else {
            query['search'] = { '1': searchKey };
            // Adding 's' query param to pass search string to GA dashboard.
            query['s'] = searchKey;
          }
          pushQuery(query);
          query.page_id = pageId;
          query.page_revision_id = pageRevisionId;
          query.grid_type = gridType;
          query.offset = 0;
          query.action_type = 'results';
          query.limit = Drupal.behaviors.loadMorePager.getLimitByGridType(gridType);
          $.ajax({
            url: '/search-callback',
            data: query,
            success: function (data, textStatus) {
              if (data.results !== null) {
                updateSearchResults(data.results);
                togglePager(data.pager);
                dataLayerPush(data.results_count, data.search_key);
                setSearchKeyHeader(data.search_key, data.no_results);
                setNoResults(data.no_results);
              }
            }
          });
          query.action_type = 'facet';
          $.ajax({
            url: '/search-callback',
            data: query,
            success: function (data, textStatus) {
              $(selectorTypeFilterWrapper).replaceWith(data.types);
              $(selectorFilterWrapper).replaceWith(data.filters);
              filterEventSubscriber(context);
              clearTypeFilterListener();
              Drupal.behaviors.searchFilterBehaviour.attach(document, drupalSettings);

              if (typeof Drupal.behaviors.searchResultsSelectBehaviour !== "undefined") {
                Drupal.behaviors.searchResultsSelectBehaviour.attach(document, drupalSettings);
              }
            }
          });
        }
      });

      var clearTypeFilterListener = function() {
        $('.search-results-item--active .search-results-item__clear').one('click', function (e) {
          var query = currentQuery();
          // Adding 's' query param to pass search string to GA dashboard.
          if (query.hasOwnProperty('search') && query.search.hasOwnProperty('1')) {
            query['s'] = query.search['1'];
          }
          delete query.type;
          pushQuery(query);
          query.page_id = pageId;
          query.page_revision_id = pageRevisionId;
          query.grid_type = 'search_page';
          query.action_type = 'results';
          query.limit = Drupal.behaviors.loadMorePager.getLimitByGridType(gridType);
          $.ajax({
            url: '/search-callback',
            data: query,
            success: function (data, textStatus) {
              if (data.results !== null) {
                updateSearchResults(data.results);
                togglePager(data.pager);
                dataLayerPush(data.results_count, data.search_key);
              }
            }
          });
          query.action_type = 'facet';
          $.ajax({
            url: '/search-callback',
            data: query,
            success: function (data, textStatus) {
              $(selectorFilterWrapper).replaceWith(data.filters);
              Drupal.behaviors.searchFilterBehaviour.attach(document, drupalSettings);
            }
          });
        });
      }

      var filterEventSubscriber = function(context) {
        $(selectorTypeFilter, context).each(function(index) {
          $(this).on('click', function (e) {
            e.preventDefault();
            var filter = $(e.target).data('type');
            var query = currentQuery();
            query['type'] = { '1': filter };
            // Adding 's' query param to pass search string to GA dashboard.
            if (query.hasOwnProperty('search') && query.search.hasOwnProperty('1')) {
              query['s'] = query['search']['1'];
            }

            // Cleared out filters, when type changed.
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            let type = '';
            if (urlParams.has('type[1]')) {
              type = urlParams.get('type[1]')
            }
            if (query['type'][1] !== type) {
              let protected_keys = [
                "action_type",
                "grid_type",
                "limit",
                "page_id",
                "page_revision_id",
                "s",
                "search",
                "type",
              ];
              Object.entries(query).forEach(([key, value]) => {
                if(protected_keys.indexOf(key) === -1) {
                  delete query['' + key + ''];
                }
              });
            }

            pushQuery(query);
            query.page_id = pageId;
            query.page_revision_id = pageRevisionId;
            query.grid_type = 'search_page';
            query.action_type = 'results';
            query.limit = Drupal.behaviors.loadMorePager.getLimitByGridType(gridType);
            $.ajax({
              url: '/search-callback',
              data: query,
              success: function (data, textStatus) {
                if (data.results !== null) {
                  updateSearchResults(data.results);
                  togglePager(data.pager);
                  dataLayerPush(data.results_count, data.search_key);
                  setNoResults(data.no_results);
                }
              }
            });
            query.action_type = 'facet';
            $.ajax({
              url: '/search-callback',
              data: query,
              success: function (data, textStatus) {
                $(selectorFilterWrapper).replaceWith(data.filters);
                Drupal.behaviors.searchFilterBehaviour.attach(document, drupalSettings);
                clearTypeFilterListener();
              }
            });
          });
        });
      }

      filterEventSubscriber(context);
      var activeTypeFilter = $('.search-results-item--active');
      if (activeTypeFilter !== null) {
        clearTypeFilterListener();
      }
      gridBlock.setAttribute('data-filter-init', true);
    }
  };
})(jQuery, Drupal, drupalSettings);
