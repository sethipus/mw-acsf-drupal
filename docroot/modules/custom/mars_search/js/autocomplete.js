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
      var selector = 'input.mars-autocomplete-field';
      $(selector, context).on('keyup', function () {
        var searchString = $(this).val();
        if (searchString.length > 2) {
          $.ajax({
            url: Drupal.url('mars-autocomplete'),
            type: 'GET',
            data: { 'q': searchString, 'view_id': $(this).attr('data-view_id'), 'display_id': $(this).attr('data-display_id') },
            dataType: 'json',
            success: function success(results) {
              $('.mars-suggestions').html(results);
            }
          });
        }

      });
    }
  };
})(jQuery, Drupal);
