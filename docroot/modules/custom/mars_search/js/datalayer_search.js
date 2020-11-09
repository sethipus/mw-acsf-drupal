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
      if (settings.dataLayer.searchPage === 'search_page') {
        eventPrefix = 'siteSearch';
        var searchResults = document.querySelector('.ajax-card-grid__items');
        searchResults.addEventListener('click', function(e) {
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

      // SITE SEARCH dataLayer events
      if (settings.dataLayer.siteSearchResults.siteSearchResults == '0') {
        // SITE SEARCH NO RESULT
        dataLayer.push({
          'event': [eventPrefix, 'ResultNo'].join('_'),
          'siteSearchTerm': settings.dataLayer.siteSearchResults.siteSearchTerm,
          'siteSearchResults': ''
        });
      } else {
        // SITE SEARCH RESULT SHOWN
        dataLayer.push({
          'event': [eventPrefix, 'ResultShown'].join('_'),
          'siteSearchTerm': settings.dataLayer.siteSearchResults.siteSearchTerm,
          'siteSearchResultsNum': settings.dataLayer.siteSearchResults.siteSearchResults
        });
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
