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
        searchResults.addEventListener('click', function (e) {
          var card = e.target.closest('section');
          if (e.target && card) {
            // SITE SEARCH RESULT CLICK
            dataLayer.push({
              'event': [eventPrefix, 'ResultClick'].join('_'),
              'siteSearchTerm': settings.dataLayer.siteSearchResults.siteSearchTerm,
              'siteSearchClicked': card.dataset.siteSearchClicked
            });
          }
        });
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
      var attributesWrapper = document.querySelectorAll('[data-layer-grid-type]');
      attributesWrapper.forEach(function (gridItem) {
        if (gridItem.dataset.layerGridType == 'search_page') {
          eventPrefix = 'siteSearch';
        }
        if (gridItem.dataset.layerSearchResults === '0') {
          // SITE SEARCH NO RESULT
          dataLayer.push({
            'event': [eventPrefix, 'ResultNo'].join('_'),
            'siteSearchTerm': gridItem.dataset.layerSearchTerm,
            'siteSearchResultsNum': '0'
          });
        } else {
          // SITE SEARCH RESULT SHOWN
          dataLayer.push({
            'event': [eventPrefix, 'ResultShown'].join('_'),
            'siteSearchTerm': gridItem.dataset.layerSearchTerm,
            'siteSearchResultsNum': gridItem.dataset.layerSearchResults
          });
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
