/**
 * @file
 * Javascript for the ajax filter of search components.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.searchFilterFaq = {
    attach: function (context, settings) {
      var selectorFaqInput = '.faq-filters__search input';
      var selectorFaqFilterContainer = '.faq-filters__filters';
      var selectorFaqFilter = '.faq-filters__filters a';
      var selectorFaqNoResults = '.faq .no-results-container'
      // Click functionality for faq filters
      $(".node--faq_contact a.active").addClass("default-link");

      // Prepare query object from browser search.
      var currentQuery = function() {
        var search = location.search;
        var hashes = search.slice(search.indexOf('?') + 1).split('&');
        return hashes.reduce((params, hash) => {
          if (hash === '') {
            return params;
          }
          var [key, val] = hash.split('=');
          // @TODO Find better to parse id Url not supported for IE.
          var id = decodeURIComponent(key).split('[')[1];
          var id = id.replace(']','');
          var key = decodeURIComponent(key).split('[')[0];
          return Object.assign(params, {[key]: {[id]: decodeURIComponent(val)}})
        }, {});
      }

      // Update path state in browser without page reload.
      var pushQuery = function(query) {
        var queryString = '?';
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
        window.history.pushState({}, '', location.pathname + queryString);
      }

      // Update search results.
      var updateSearchResults = function(results) {
        var searchItems = $('.faq').find('ol.faq_list');
        searchItems.empty();
        results.forEach(function(element) {
          searchItems.append(element);
        });
      }

      // Toggle pager.
      var togglePager = function(pager) {
        if (!pager) {
          $('.faq__see_all').removeClass('active');
        }
        else {
          $('.faq__see_all').addClass('active');
        }
      }

      // Data layer push event.
      var dataLayerPush = function(results_count, search_text) {
        var eventPrefix = 'faqSearch',
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

      // Update search results heading.
      var toggleResultsHeading = function(results_count, search_result_text) {
        $('.faq-filters__search-results').removeClass('active');
        $('.faq-filters__search-results').html(results_count + ' ' + search_result_text);
        if (search_result_text !== '') {
          $('.faq-filters__search-results').addClass('active');
        }
      }

      var setNoResults = function(noResults) {
        $(selectorFaqNoResults).html(noResults);
        if (noResults !== '') {
          $(selectorFaqFilterContainer).removeClass('active');
        }
        else {
          $(selectorFaqFilterContainer).addClass('active');
        }
      }

      $(selectorFaqInput, context).on('keypress', function (e) {
        if (e.which == 13) {
          // Prepare request query.
          var query = currentQuery();
          var searchKey = $(this).val();
          if (searchKey === '') {
            delete query.search;
          }
          else {
            query['search'] = { '1': searchKey };
          }
          pushQuery(query);
          query.grid_type = 'faq';
          query.action_type = 'results';
          query.page_id = $('[data-layer-page-id]').attr('data-layer-page-id');
          query.page_revision_id = $('[data-layer-page-revision-id]').attr('data-layer-page-revision-id');
          query.limit = 4;
          query.offset = 0;
          $.ajax({
            url: drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix + 'search-callback',
            data: query,
            success: function (data, textStatus) {
              if (data.results !== null) {
                toggleResultsHeading(data.results_count, data.search_result_text);
                updateSearchResults(data.results);
                togglePager(data.pager);
                dataLayerPush(data.results_count, data.search_key);
                setNoResults(data.no_results);
              }
            }
          });
        }
      });

      $(selectorFaqFilter, context).on('click', function (e) {
        e.preventDefault();
        var target = e.delegateTarget;
        var filter = target.dataset.filterValue;
        var query = currentQuery();
        // Unselect current filter if active.
        if (!$(target).hasClass('active')) {
          query['faq_filter_topic'] = { '1': filter };
          $('.faq-filters__filters a.active').removeClass('active');
          $(target).addClass('active');
        }
        else {
          $('.faq-filters__filters a.active').removeClass('active');
          delete query.faq_filter_topic;
        }
        pushQuery(query);
        query.grid_type = 'faq';
        query.action_type = 'results';
        query.page_id = $('[data-layer-page-id]').attr('data-layer-page-id');
        query.page_revision_id = $('[data-layer-page-revision-id]').attr('data-layer-page-revision-id');
        query.limit = 4;
        $.ajax({
          url: drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix + 'search-callback',
          data: query,
          success: function (data, textStatus) {
            if (data.results !== null) {
              toggleResultsHeading(data.results_count, data.search_result_text);
              updateSearchResults(data.results);
              togglePager(data.pager);
              dataLayerPush(data.results_count, data.search_key);
              setNoResults(data.no_results);
            }
          }
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
