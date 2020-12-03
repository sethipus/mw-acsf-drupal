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

      var eventPrefix = 'faqSearch';
      var eventSelector = '.mars-autocomplete-field-faq';
      var searchResults = document.querySelector('[data-layer-grid-type]');
      var searchType = searchResults.dataset.layerGridType;
      var siteSearchTerm = searchResults.dataset.layerSearchTerm;
      var siteSearchResults = searchResults.dataset.layerSearchResults;

      if (searchType === 'search_page') {
        eventPrefix = 'siteSearch';
        var searchResults = document.querySelector('.ajax-card-grid__items');
        if (searchResults) {
          searchResults.addEventListener('click', function(e) {
            var card = e.target.closest('section');
            if (e.target && card) {
              // SITE SEARCH RESULT CLICK
              dataLayer.push({
                'event': [eventPrefix, 'ResultClick'].join('_'),
                [eventPrefix + 'Term']: siteSearchTerm,
                [eventPrefix + 'Clicked']: card.dataset.siteSearchClicked
              });
            }
          });
        }
      }

      // SITE SEARCH dataLayer events.
      var searchInput = document.querySelector(eventSelector);
      if (searchInput) {
        searchInput.addEventListener('focus', function () {
          // Data Layer search START.
          dataLayer.push({
            'event': [eventPrefix, 'Start'].join('_'),
            [eventPrefix + 'Term']: '',
            [eventPrefix + 'Results']: ''
          });
        });
      }
      if (siteSearchResults == '0') {
        // SITE SEARCH NO RESULT
        dataLayer.push({
          'event': [eventPrefix, 'ResultNo'].join('_'),
          [eventPrefix + 'Term']: siteSearchTerm,
          [eventPrefix + 'ResultsNum']: '0'
        });
      } else {
        // SITE SEARCH RESULT SHOWN
        dataLayer.push({
          'event': [eventPrefix, 'ResultShown'].join('_'),
          [eventPrefix + 'Term']: siteSearchTerm,
          [eventPrefix + 'ResultsNum']: siteSearchResults
        });
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
