/**
 * @file
 * Javascript for the ajax pager of search grid block.
 */


(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.seeAllAjax = {
    attach: function (context, settings) {
      var selectorCardGrid = '.ajax-card-grid__more-link a';
      var selectorFaq = '.faq__see_all a';
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
              id ? $('.card-grid-results[data-layer-grid-id=' + id + ']').find('.ajax-card-grid__items').html(data) : $('.ajax-card-grid__items').html(data);
              selectorContext.closest('.ajax-card-grid__content').find('.ajax-card-grid__more-link').hide();
            }
        });
      });

      $(selectorFaq, context).on('click', function (e) {
        e.preventDefault();
        $.ajax({
          url: '/see-all-faq-callback',
          data: currentQuery,
          success: function (data, textStatus) {
            $('.faq').find('.faq_list').html(data);
            $('.faq__see_all').hide();
          }
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
