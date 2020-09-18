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
        if (searchString.length > 2) {
          setTimeout(function() {
            $.ajax({
              url: Drupal.url('mars-autocomplete'),
              type: 'GET',
              data: { 'search': searchString },
              dataType: 'json',
              success: function success(results) {
                $('.mars-suggestions').html(results);
              }
            });
          }, 250);
        }
      });
    }
  };
})(jQuery, Drupal);
