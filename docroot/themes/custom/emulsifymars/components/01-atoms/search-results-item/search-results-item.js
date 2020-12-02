Drupal.behaviors.searchResultsSelectBehaviour = {
  attach(context) {
    const searchResultsItems = context.querySelectorAll('.search-results-item');
    const searchResultsItemsClear = context.querySelectorAll('.search-results-item__clear');

    searchResultsItemsClear.forEach(function(clrButton) {
      clrButton.addEventListener('click', function(event) {
        var activeLink = clrButton.closest('.search-results-item');
        activeLink.classList.remove('search-results-item--active');
        activeLink.closest('.results--filter-selected').classList.remove('results--filter-selected');
        activeLink.querySelector('a').click();
        event.stopPropagation();
      });
    });
  },
};
