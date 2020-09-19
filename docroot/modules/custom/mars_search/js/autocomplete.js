/**
 * @file
 * Javascript for the search related things.
 */

/**
 * Search overlay.
 */
(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.marsAutocomplete = {
    attach: function (context, settings) {
      var selector = '.header__inner input.mars-autocomplete-field';
      $(selector, context).on('keyup', function () {
        var searchString = $(this).val();
        var viewId = $(this).attr('data-view_id');
        var viewDisplayId = $(this).attr('data-display_id');
        if (searchString.length > 2) {
          setTimeout(function() {
            var $this = $(this);
            $.ajax({
              url: Drupal.url('mars-autocomplete'),
              type: 'GET',
              data: { 'q': searchString, 'view_id': viewId, 'display_id': viewDisplayId },
              dataType: 'json',
              success: function success(results) {
                $('.mars-suggestions').html(results);
                $('.search-field-wrapper').addClass('suggested');
                $('.mars-search-autocomplete-suggestions-wrapper').show();
              }
            });
          }, 25);
        }
      });
    }
  };
})(jQuery, Drupal);
