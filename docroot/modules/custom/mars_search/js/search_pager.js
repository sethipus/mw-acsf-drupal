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
          if (!(pair[0].includes('grid_type') || pair[0].includes('action_type') || pair[0].includes('offset'))) {
            resultQuery[pair[0]] = pair[1];
          }
        }
        return resultQuery;
      }

      $(selectorCardGrid, context).on('click', function (e) {
        e.preventDefault();
        var query = currentQuery();
        query.grid_type = $(this).closest('[data-layer-grid-type]').attr('data-layer-grid-type');
        query.action_type = 'results';
        if (query.grid_type === 'grid') {
          query.grid_id = $(this).closest('.card-grid-results').attr('data-layer-grid-id');
          query.page_id = $(this).closest('.card-grid-results').attr('data-layer-page-id');
        }
        var selectorContext = $(this);
        var searchItems = selectorContext.closest('.ajax-card-grid__content').find('.ajax-card-grid__items');
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
              });
              if (!data.pager) {
                selectorContext.closest('.ajax-card-grid__content')
                .find('.ajax-card-grid__more-link').visibility = "hidden";
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
    }
  };
})(jQuery, Drupal, drupalSettings);
