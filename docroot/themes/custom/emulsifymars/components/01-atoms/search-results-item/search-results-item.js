Drupal.behaviors.searchResultsSelectBehaviour = {
  attach(context) {
    const searchResultsItems = context.querySelectorAll('.search-results-item');
    const searchResultsItemsClear = context.querySelectorAll('.search-results-item__clear');
    const results = context.querySelector('.search-results-container .results');

    searchResultsItems.forEach(function(item) {
      item.addEventListener('click', function(event) {
        item.classList.add('search-results-item--active');
        results.classList.add('results--active');
      });
    });

    searchResultsItemsClear.forEach(function(clrButton) {
      clrButton.addEventListener('click', function(event) {
        clrButton.closest('.search-results-item').classList.remove('search-results-item--active');

        const isActiveResults = context.querySelector('.search-results-item--active');
        if (!isActiveResults) {
          clrButton.closest('.results').classList.remove('results--active');
        }

        event.stopPropagation();
      });
    });
  },
};
