/**
 * @file
 * Javascript for the search related things.
 */

/**
 * dataLayer page view.
 */
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.dataLayerSearch = {
    attach: function (context, settings) {
      if (typeof dataLayer === 'undefined') {
        return;
      }

      var eventPrefix = 'faqSearch',
          eventSelector = '.mars-autocomplete-field-faq';
      if (settings.dataLayer.searchPage === 'search_page') {
        eventPrefix = 'siteSearch';
        var searchResults = document.querySelector('.ajax-card-grid__items');
        searchResults.addEventListener('click', function(e) {
          var card = e.target.closest('section');
          if (e.target && card) {
            // SITE SEARCH RESULT CLICK
            dataLayer.push({
              'event': [eventPrefix, 'ResultClick'].join('_'),
              [eventPrefix + 'Term']: settings.dataLayer.siteSearchResults.siteSearchTerm,
              [eventPrefix + 'Clicked']: card.dataset.siteSearchClicked
            });
          }
        });
      }

      // SITE SEARCH dataLayer events.
      var searchInput = document.querySelector(eventSelector);
      if (searchInput) {
        searchInput.addEventListener('focus', function() {
          // Data Layer search START.
          dataLayer.push({
            'event': [eventPrefix, 'Start'].join('_'),
            [eventPrefix + 'Term']: '',
            [eventPrefix + 'Results']: ''
          });
        });
      }
      if (settings.dataLayer.siteSearchResults.siteSearchResults == '0') {
        // SITE SEARCH NO RESULT
        dataLayer.push({
          'event': [eventPrefix, 'ResultNo'].join('_'),
          [eventPrefix + 'Term']: settings.dataLayer.siteSearchResults.siteSearchTerm,
          [eventPrefix + 'ResultsNum']: '0'
        });
      } else {
        // SITE SEARCH RESULT SHOWN
        dataLayer.push({
          'event': [eventPrefix, 'ResultShown'].join('_'),
          [eventPrefix + 'Term']: settings.dataLayer.siteSearchResults.siteSearchTerm,
          [eventPrefix + 'ResultsNum']: settings.dataLayer.siteSearchResults.siteSearchResults
        });
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
