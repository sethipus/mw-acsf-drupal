Drupal.behaviors.searchResultsSelectBehaviour = {
  attach(context) {
    const searchResultsItems = context.querySelectorAll('.search-results-item');
    const searchResultsItemsClear = context.querySelectorAll('.search-results-item__clear');

    searchResultsItems.forEach(function(item) {
      item.addEventListener('click', function(event) {
        item.classList.add('search-results-item--active');
      });
    });

    searchResultsItemsClear.forEach(function(clrButton) {
      clrButton.addEventListener('click', function(event) {
        clrButton.closest('.search-results-item').classList.remove('search-results-item--active');
        event.stopPropagation();
      });
    });
  },
};
