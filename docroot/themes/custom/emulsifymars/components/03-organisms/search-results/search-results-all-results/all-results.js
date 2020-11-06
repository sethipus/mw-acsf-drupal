Drupal.behaviors.allResults = {
  attach: function (context) {
    let searchResultsPage = document.getElementsByClassName('search-results-page')[0];
    let searchResults = document.querySelector('.ajax-card-grid__items');

    // SITE SEARCH RESULT SHOWN
    dataLayer.push({
      'event': 'siteSearch_ResultClick',
      'siteSearchTerm': searchResultsPage.dataset.siteSearchTerm,
      'siteSearchResultsNum': searchResultsPage.dataset.siteSearchResultsNum
    });

    searchResults.addEventListener('click', function(e) {
      let card = e.target.closest('.product-card');
      if (e.target && card){
        // SITE SEARCH RESULT CLICK
        dataLayer.push({
          'event': 'siteSearch_ResultClick',
          'siteSearchTerm': searchResultsPage.dataset.siteSearchTerm,
          'siteSearchClicked': card.dataset.siteSearchClicked
        });
      }
    });
  },
};
