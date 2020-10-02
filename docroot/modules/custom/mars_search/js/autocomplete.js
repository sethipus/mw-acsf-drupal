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
      var selector = '.header__inner input.mars-autocomplete-field, .mars-search-form .mars-autocomplete-field';
      $(selector, context).on('keyup', function () {
        var searchString = $(this).val();
        if (searchString.length > 2) {
          setTimeout(function() {
            $.ajax({
              url: Drupal.url('mars-autocomplete'),
              type: 'GET',
              data: { 'search[1]': searchString },
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
