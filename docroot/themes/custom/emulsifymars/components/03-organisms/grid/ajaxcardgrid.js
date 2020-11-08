Drupal.behaviors.ajaxcardgrid = {
  attach(context, settings) {
    var cardSearchResults = settings.dataLayer.hasOwnProperty('cardSearchResults') ? settings.dataLayer.cardSearchResults : '';

    const seeMoreBtn = document.querySelector('.ajax-card-grid__more-link .default-link');
    seeMoreBtn.addEventListener('click', (event) => {
        event.preventDefault();
      }
    );

    // CARD GRID COMPONENT SEARCH RESULT CLICK
    $('[data-type="card"]').on('click', function() {
      dataLayer.push({
        'event': 'cardGridSearch_ResultClick',
        'pageId': cardSearchResults.pageId,
        'pageType': cardSearchResults.pageType,
        'cardGridID': '',
        'cardGridName': '',
        'cardGridSearchTerm': cardSearchResults.cardGridSearchTerm,
        'cardGridSearchClick': '' // Will need to populate based on Olga’s response, i.e. product id, article id etc ???
      });
    });
  },
};
