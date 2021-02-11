/**
 * @file
 * Javascript for the ajax pager of search components.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.loadMorePager = {
    attach: function (context, settings) {
      var selectorCardGrid = '.ajax-card-grid__more-link a';
      var selectorFaq = '.faq__see_all a';
      $(selectorFaq).css('cursor', 'pointer');
      var currentQuery = function() {
        const vars = location.search.substring(1).split('&');
        let resultQuery = new Map();
        for (var i = 0; i < vars.length; i++) {
          var pair = vars[i].split('=');
          pair[1] = decodeURIComponent(pair[1]);
          if (!(pair[0].includes('grid_type') || pair[0].includes('action_type') || pair[0].includes('offset'))) {
            resultQuery[pair[0]] = pair[1];
          }
        }
        return resultQuery;
      };

      $(selectorCardGrid, context).on('click', function (e) {
        e.preventDefault();
        var query = currentQuery();
        query.grid_type = $(this).closest('[data-layer-grid-type]').attr('data-layer-grid-type');
        query.action_type = 'results';
        query.page_id = $(this).closest('.card-grid-results').attr('data-layer-page-id');
        if (query.grid_type === 'grid') {
          query.grid_id = $(this).closest('.card-grid-results').attr('data-layer-grid-id');
        }
        var selectorContext = $(this);
        var searchItems = selectorContext.closest('.ajax-card-grid__content').find('.ajax-card-grid__items');
        query.offset = searchItems.children().length;

        if (query.grid_type === 'grid' || query.grid_type === 'search_page') {
          query.limit = Drupal.behaviors.loadMorePager.getLimitByGridType(query.grid_type);
        }

        $.ajax({
          url: '/search-callback',
          data: query,
          success: function (data) {
            if (data.results !== null) {
              data.results.forEach(function(element) {
                var elementWrapper = document.createElement('div');
                elementWrapper.className = 'ajax-card-grid__item_wrapper';
                elementWrapper.innerHTML = element;
                searchItems.append(elementWrapper);
                Drupal.behaviors.productCard.attach(searchItems);
              });
              if (!data.pager) {
                selectorContext.closest('.ajax-card-grid__content')
                .find('.ajax-card-grid__more-link').removeClass('active');
              }
            }
          }
        });
      });

      $(selectorFaq, context).on('click', function (e) {
        e.preventDefault();
        var query = currentQuery();
        query.grid_type = 'faq';
        query.action_type = 'results';
        query.page_id = $('[data-layer-page-id]').attr('data-layer-page-id');
        var searchItems = $('.faq').find('ol.faq_list');
        query.offset = searchItems.children().length;
        $.ajax({
          url: '/search-callback',
          data: query,
          success: function (data, textStatus) {
            if (data.results !== null) {
              data.results.forEach(function(element) {
                searchItems.append(element);
              });
              if (!data.pager) {
                $('.faq__see_all').removeClass('active');
              }
            }
          }
        });
      });

      const initState = function () {

        $('.card-grid-results').each(function () {

          var query = currentQuery();
          query.grid_type = $(this).attr('data-layer-grid-type');
          query.action_type = 'results';
          query.page_id = $(this).attr('data-layer-page-id');
          if (query.grid_type === 'grid') {
            query.grid_id = $(this).attr('data-layer-grid-id');
          }
          query.limit = Drupal.behaviors.loadMorePager.getLimitByGridType(query.grid_type);
          var selectorContext = $(this);
          var searchItems = selectorContext.find('.ajax-card-grid__content').find('.ajax-card-grid__items');
          query.offset = searchItems.children().length;
          $.ajax({
            url: '/search-callback',
            data: query,
            success: function (data) {
              if (data.results !== null) {
                data.results.forEach(function(element) {
                  var elementWrapper = document.createElement('div');
                  elementWrapper.className = 'ajax-card-grid__item_wrapper';
                  elementWrapper.innerHTML = element;
                  searchItems.append(elementWrapper);
                  Drupal.behaviors.productCard.attach(searchItems);
                });
                if (!data.pager) {
                  selectorContext.find('.ajax-card-grid__content')
                    .find('.ajax-card-grid__more-link').removeClass('active');
                }
              }
            }
          });
        });
      };

      if (context === document) {
        initState();
      }
    },

    getLimitByGridType: function (grid_type) {

      let width = window.innerWidth;
      let limit = 8;

      // For mobile and grid type.
      if (grid_type == 'grid' && width <= 768) {
        limit = 4;
      }

      // For mobile and search_page type.
      if (grid_type == 'search_page' && width > 768) {
        limit = 12;
      }
      return limit;
    }
  };
})(jQuery, Drupal, drupalSettings);
