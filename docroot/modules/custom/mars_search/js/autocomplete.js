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
        var gridId = $(this).attr('data-grid-id');
        var gridQuery = $(this).attr('data-grid-query');
        var cardsView = $(this).hasClass('mars-cards-view');
        //console.log(cardsView);
        if (searchString.length > 2) {
          var url = Drupal.url('mars-autocomplete') + '?search[' + gridId + ']=' + searchString + '&search_id=' + gridId;
          if (gridQuery) {
            url = url + '&' + gridQuery;
          }
          if (cardsView && window.innerWidth > 768) {
            url = url + '&cards_view=1';
          }
          var target_container = $(this).parents('.search-input-wrapper');

          setTimeout(function() {
            $.ajax({
              url: url,
              type: 'GET',
              dataType: 'json',
              success: function success(results) {
                $(target_container).find('.mars-suggestions').html(results);
                $(target_container).addClass('suggested');
                $('.mars-search-autocomplete-suggestions-wrapper').show();
                $('.faq .suggestions-links li').not(':last').click(function (){
                  var  clicked_text = $(this).text();
                  $('.mars-autocomplete-field-faq').val(clicked_text);
                });
              }
            });
          }, 25);
        }
      });
    }
  };
})(jQuery, Drupal);
