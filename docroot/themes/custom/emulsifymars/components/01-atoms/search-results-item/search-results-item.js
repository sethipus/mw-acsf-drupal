(function (Drupal) {
  Drupal.behaviors.searchResultsSelectBehaviour = {
    attach(context) {
      const searchResultsItems = context.querySelectorAll('.search-page-header .search-results-container .results__container a');
      const searchResultsItemsClear = context.querySelectorAll('.search-page-header .search-results-container .search-results-item__clear');

      searchResultsItemsClear.forEach(function (clrButton) {
        clrButton.addEventListener('click', function (event) {
          var activeLink = event.target.closest('.search-results-item');
          activeLink.classList.remove('search-results-item--active');
          activeLink.closest('.results__container--filter-selected').classList.remove('results__container--filter-selected');
        });
      });
      searchResultsItems.forEach(function (linkButton) {
        linkButton.addEventListener('click', function (event) {
          var target = event.target;
          var activeFilter = target.closest('.results__container').querySelector('.search-results-item--active');
          if (activeFilter !== null) {
            activeFilter.classList.remove('search-results-item--active');
          }
          target.closest('.search-results-item').classList.add('search-results-item--active');
          target.closest('.results__container').classList.add('results__container--filter-selected');
        });
      });
    },
  };
})(Drupal);
