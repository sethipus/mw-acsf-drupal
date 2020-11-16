/**
 * @file
 * Javascript for the ajax pager of search grid block.
 */


(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.seeAllAjax = {
    attach: function (context, settings) {
      var selectorCardGrid = '.ajax-card-grid__more-link a';
      var selectorFaq = '.faq__see_all a';
      $(selectorFaq).removeAttr('href');
      $(selectorFaq).css('cursor', 'pointer');

      $(selectorCardGrid, context).on('click', function () {
        let id = $(this).closest('.card-grid-results').attr('data-layer-grid-id');
        $(this).closest('.ajax-card-grid__content').find('.ajax-card-grid__items')
          .load('/see-all-callback',
            {
              id: id ? id : '',
              searchOptions: id ? drupalSettings.cards[id].searchOptions :
                drupalSettings.t,
              topResults: id ? drupalSettings.cards[id].topResults : '',
            }, function () {
              $('.ajax-card-grid__more-link').hide();
            });
      });
      $(selectorFaq, context).on('click', function () {
        $(this).closest('.faq').find('.faq_list')
          .load('/see-all-faq-callback',
            {
              searchOptions:drupalSettings.searchOptions,
            }, function() {
                $('.faq__see_all').hide();
            });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
