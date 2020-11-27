/**
 * @file
 * Javascript for the search related things.
 */

/**
 * dataLayer page view.
 */
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.dataLayerCardGrid = {
    attach: function (context, settings) {
      if (typeof dataLayer === 'undefined') {
        return;
      }

      var grids = document.querySelectorAll('[data-block-plugin-id="search_grid_block"]');
      grids.forEach(function(grid) {
        var gridResults = grid.querySelector('.card-grid-results');
        var cardGridName = gridResults.dataset.layerGridName;
        var cardGridId = gridResults.dataset.layerGridId;
        var cardGridSearchTerm = gridResults.dataset.layerSearchTerm;
        var cardGridSearchResults = gridResults.dataset.layerSearchResults;

        if (cardGridSearchResults == '0') {
          // CARD GRID COMPONENT SEARCH NO RESULT
          dataLayer.push({
            'event': 'cardGridSearch_ResultNo',
            'cardGridID': cardGridId,
            'cardGridName': cardGridName,
            'cardGridSearchTerm': cardGridSearchTerm,
            'cardGridSearchResultsNum': '0'
          });
        } else {
          // CARD GRID COMPONENT SEARCH RESULT SHOWN
          dataLayer.push({
            'event': 'cardGridSearch_ResultShown',
            'cardGridID': cardGridId,
            'cardGridName': cardGridName,
            'cardGridSearchTerm': cardGridSearchTerm,
            'cardGridSearchResultsNum': cardGridSearchResults
          });
        }
        var searchInput = grid.querySelector('.card-grid-filter input');
        if (searchInput) {
          searchInput.addEventListener('focus', function() {
            // CARD GRID COMPONENT SEARCH START
            dataLayer.push({
              'event': 'cardGridSearch_Start',
              'cardGridID': cardGridId,
              'cardGridName': cardGridName,
              'cardGridSearchTerm': '',
              'cardGridSearchResultsNum': ''
            });
          });
        }
        var searchResults = grid.querySelector('.card-grid-results .ajax-card-grid__items');
        searchResults.addEventListener('click', function(e) {
          var card = e.target.closest('section');
          if (e.target && card) {
            // SITE SEARCH RESULT CLICK
            dataLayer.push({
              'event': 'cardGridSearch_ResultClick',
              'cardGridID': cardGridId,
              'cardGridName': cardGridName,
              'cardGridSearchTerm': cardGridSearchTerm,
              'cardGridSearchClicked': card.dataset.siteSearchClicked
            });
          }
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
