/**
 * @file
 * Javascript for the ajax pager of search grid block.
 */


(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.seeAllAjax = {
    attach: function (context, settings) {
      var selectorCardGrid = '.ajax-card-grid__more-link a';
      var selectorFaq = '.faq__see_all a';
      var selectorFaqFilter = '.faq-filters__filters a';
      $(selectorFaq).css('cursor', 'pointer');
      var currentQuery = drupalSettings.path.currentQuery ?
        drupalSettings.path.currentQuery : {};

      $(selectorCardGrid, context).on('click', function (e) {
        e.preventDefault();
        var id = $(this).closest('.card-grid-results').attr('data-layer-grid-id');
        var selectorContext = $(this);
        currentQuery.id = id;
        currentQuery.topResults = id ? drupalSettings.cards[id].topResults : '';
        currentQuery.contentType = id ? drupalSettings.cards[id].contentType : '';
          $.ajax({
            url: '/see-all-callback',
            data: currentQuery,
            success: function (data, textStatus) {
              id ? $('.card-grid-results[data-layer-grid-id=' + id + ']')
                .find('.ajax-card-grid__items').html(data.build) :
                $('.ajax-card-grid__items').html(data.build);
              selectorContext.closest('.ajax-card-grid__content')
                .find('.ajax-card-grid__more-link').hide();
            }
        });
      });

      $(selectorFaq, context).on('click', function (e) {
        e.preventDefault();
        $.ajax({
          url: '/see-all-faq-callback',
          data: currentQuery,
          success: function (data, textStatus) {
            $('.faq').find('.faq_list').html(data.build);
            $('.faq__see_all').hide();
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
            console.log(data);
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
