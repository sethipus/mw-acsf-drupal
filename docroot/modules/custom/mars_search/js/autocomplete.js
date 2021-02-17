/**
 * @file
 * Javascript for the search related things.
 */

/**
 * Search overlay.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.marsAutocomplete = {
    attach: function (context, settings) {
      var selector = '.header__inner input.mars-autocomplete-field, .mars-search-form .mars-autocomplete-field';
      $(selector, context).on('keyup', function (e) {
        if (e.keyCode === 27) {
          return;
        }

        if (e.keyCode === 13) {
          debugger;
        }

        var searchString = $(this).val();
        var gridId = $(this).attr('data-grid-id');
        var gridQuery = $(this).attr('data-grid-query');
        var cardsView = $(this).hasClass('mars-cards-view');
        var target_container = $(this).parents('.search-input-wrapper').parent();
        if (searchString.length < 3) {
          $('.mars-search-autocomplete-suggestions-wrapper').hide();
          $('.search-input-wrapper').removeClass('suggested');
          $(target_container).find('.mars-suggestions').html('');
        }
        if (searchString.length > 2) {
          var url = Drupal.url('mars-autocomplete') + '?search[' + gridId + ']=' + searchString + '&search_id=' + gridId;
          if (gridQuery) {
            url = url + '&' + gridQuery;
          }
          if (cardsView && window.innerWidth > 1024) {
            url = url + '&cards_view=1';
            target_container = $(this).parents('.search-autocomplete-wrapper').parent();
          }

          setTimeout(function() {
            $.ajax({
              url: url,
              type: 'GET',
              dataType: 'json',
              success: function success(results) {
                if (!$(results).hasClass('no-results')) {
                  const suggestions = $(target_container).find('.mars-suggestions');
                  suggestions.html(results);

                  suggestions.each((index, element) => {
                    if (element.nodeType === Node.ELEMENT_NODE) {
                      Drupal.attachBehaviors(element, drupalSettings);
                    }
                  });

                  $(target_container).find('.search-input-wrapper').addClass('suggested');
                  $('.mars-search-autocomplete-suggestions-wrapper').show();
                }
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
})(jQuery, Drupal, drupalSettings);
