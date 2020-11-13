/**
 * @file
 * Javascript for the search related things.
 */


(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.seeAllAjax = {
    attach: function (context, settings) {
      var selector = '.ajax-card-grid__more-link a';
      console.log(drupalSettings);
      $(selector, context).on('click', function () {
        let id = $(this).closest('.card-grid-results').attr('data-layer-grid-id');

        $(this).closest('.ajax-card-grid__content').find('.ajax-card-grid__items')
          .load('/see-all-callback',
            {
              id: id,
              searchOptions:drupalSettings.cards[id].searchOptions,
              topResults:drupalSettings.cards[id].topResults,
            });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
