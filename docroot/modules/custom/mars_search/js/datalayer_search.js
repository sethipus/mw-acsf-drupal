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
      if (settings.dataLayer.siteSearchResults.siteSearchResults == '0') {
        // SITE SEARCH NO RESULT
        dataLayer.push({
          'event': 'siteSearch_ResultNo',
          'siteSearchTerm': settings.dataLayer.siteSearchResults.siteSearchTerm,
          'siteSearchResults': ''
        });
      }
      else {
        // SITE SEARCH RESULT SHOWN
        dataLayer.push({
          'event': 'siteSearch_ResultShown',
          'siteSearchTerm': settings.dataLayer.siteSearchResults.siteSearchTerm,
          'siteSearchResultsNum': settings.dataLayer.siteSearchResults.siteSearchResults
        });
        let searchResults = document.querySelector('.ajax-card-grid__items');
        searchResults.addEventListener('click', function(e) {
          let card = e.target.closest('section');
          if (e.target && card) {
            // SITE SEARCH RESULT CLICK
            dataLayer.push({
              'event': 'siteSearch_ResultClick',
              'siteSearchTerm': settings.dataLayer.siteSearchResults.siteSearchTerm,
              'siteSearchClicked': card.dataset.siteSearchClicked
            });
          }
        });
      }
  
      var searchInputs = document.querySelectorAll('.mars-autocomplete-field');
      searchInputs.forEach(function (input) {
        input.addEventListener('focus', function() {
          // SITE SEARCH START
          dataLayer.push({
            'event': 'siteSearch_Start',
            'siteSearchTerm': '',
            'siteSearchResults': ''
          })
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
