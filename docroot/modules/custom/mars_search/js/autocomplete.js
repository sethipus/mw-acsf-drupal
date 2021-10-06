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

        var searchString = $(this).val();
        var gridId = $(this).attr('data-grid-id');
        var gridQuery = $(this).attr('data-grid-query');
        var cardsView = $(this).hasClass('mars-cards-view');
        var target_container = $(this).parents('.search-input-wrapper').parent();

        var hideSuggestion = function() {
          $('.mars-search-autocomplete-suggestions-wrapper').hide();
          $('.search-input-wrapper').removeClass('suggested');
          $(target_container).find('.mars-suggestions').html('');
        }
        if ((searchString.length < 3) || (e.keyCode === 13)) {
          hideSuggestion();
          return;
        }
        if (searchString.length > 2) {
          var url = Drupal.url('mars-autocomplete') + '?search[' + gridId + ']=' + encodeURIComponent(searchString) + '&search_id=' + gridId;
          if (gridQuery) {
            url = url + '&' + gridQuery;
          }
          if (cardsView && window.innerWidth > 1024) {
            url = url + '&cards_view=1';
            target_container = $(this).parents('.search-autocomplete-wrapper').parent();
          }
          else if (cardsView) {
            //url = url + '&cards_view=0';
            // #AB291643
          }

          // Update url by component/page type.
          if ($(this).parents('.search-page-header').length ||
            $(this).parents('.faq-filters').length ||
            $(this).parents('.card-grid-filter').length) {
            url += '&limit=5'
          }
          else {
            url += '&limit=4'
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
                else if (
                  $(results).hasClass('no-results') &&
                  $(target_container).parents('.header__menu--secondary-mobile').length
                ) {
                  const suggestions = $(target_container).find('.mars-suggestions');
                  suggestions.html(results);
                  $(target_container).find('.search-input-wrapper').addClass('suggested');
                  $('.mars-search-autocomplete-suggestions-wrapper').show();
                }
                else if (
                  $(results).hasClass('no-results') &&
                  !$(target_container).parents('.header__menu').length
                ) {
                  hideSuggestion();
                }
                $('.faq .suggestions-links li').click(function (){
                  var  clicked_text = $(this).text().replace('…', '');
                  $('.mars-autocomplete-field-faq').val(clicked_text);
                  var press = jQuery.Event("keypress");
                  press.which = 13;
                  $(selector, context).trigger(press);
                });
              }
            });
          }, 25);
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
