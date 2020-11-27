/**
 * @file
 * Javascript for the ajax pager of search grid block.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.loadMorePager = {
    attach: function (context, settings) {
      var selectorCardGrid = '.ajax-card-grid__more-link a';
      var selectorFaq = '.faq__see_all a';
      var selectorFaqFilter = '.faq-filters__filters a';
      $(selectorFaq).css('cursor', 'pointer');
      var currentQuery = drupalSettings.path.currentQuery ?
        drupalSettings.path.currentQuery : {};

      $(selectorCardGrid, context).on('click', function (e) {
        e.preventDefault();
        currentQuery.grid_type = $(this).closest('[data-layer-grid-type]').attr('data-layer-grid-type');
        currentQuery.action_type = 'pager';
        if (currentQuery.grid_type === 'grid') {
          currentQuery.grid_id = $(this).closest('.card-grid-results').attr('data-layer-grid-id');
          currentQuery.page_id = $(this).closest('.card-grid-results').attr('data-layer-page-id');
        }
        var selectorContext = $(this);
        var searchItems = selectorContext.closest('.ajax-card-grid__content').find('.ajax-card-grid__items');
        currentQuery.offset = searchItems.children().length;
        $.ajax({
          url: '/search-callback',
          data: currentQuery,
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
                .find('.ajax-card-grid__more-link').hide();
              }
            }
          }
        });
      });

      $(selectorFaq, context).on('click', function (e) {
        e.preventDefault();
        currentQuery.grid_type = 'faq';
        currentQuery.action_type = 'pager';
        $.ajax({
          url: '/search-callback',
          data: currentQuery,
          success: function (data, textStatus) {
            if (data.results !== null) {
              data.results.forEach(function(element) {
                $('.faq').find('.faq_list').append(element);
              });
              if (!data.pager) {
                $('.faq__see_all').hide();
              }
            }
          }
        });
      });

      $(selectorFaqFilter, context).on('click', function (e) {
        var currentQuery = {};
        var link = e.delegateTarget.href.split('=');
        var $list = $('.faq_list');
        e.preventDefault();
        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
        }
        else {
          $('.faq-filters__filters a').removeClass('active');
          $(this).addClass('active');
          currentQuery.faq_filter_topic = link;
        }
        currentQuery.isFilterAjax = true;
        $.ajax({
          url: '/see-all-faq-callback',
          data: currentQuery,
          success: function (data, textStatus) {
            $list.html(data.build);
            if (data.showButton) {
              $('.faq__see_all').show();
            }
            else {
              $('.faq__see_all').hide();
            }
          }
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
