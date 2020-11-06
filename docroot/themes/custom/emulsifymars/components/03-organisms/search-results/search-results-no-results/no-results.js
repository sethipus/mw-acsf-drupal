Drupal.behaviors.noResults = {
  attach: function (context) {
    let searchResultsPage = document.getElementsByClassName('search-results-page')[0];

    // SITE SEARCH NO RESULT
    dataLayer.push({
      'event': 'siteSearch_ResultNo',
      'siteSearchTerm': searchResultsPage.dataset.siteSearchTerm,
      'siteSearchResults': ''
    });
  },
};
