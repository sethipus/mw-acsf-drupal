Drupal.behaviors.searchInput = {
  attach(context, settings) {
    var search = document.getElementById('search');
    var request = new XMLHttpRequest();
    var cardSearchResults = settings.dataLayer.hasOwnProperty('cardSearchResults') ? settings.dataLayer.cardSearchResults : '';

    if (cardSearchResults){
      if (cardSearchResults.cardGridSearchResultsNum) {
        // CARD GRID COMPONENT SEARCH RESULT SHOWN
        dataLayer.push({
          'event': 'cardGridSearch_ResultShown',
          'pageId': cardSearchResults.pageId,
          'pageType': cardSearchResults.pageType,
          'cardGridID': '',
          'cardGridName': '',
          'cardGridSearchTerm': cardSearchResults.cardGridSearchTerm,
          'cardGridSearchResultsNum': cardSearchResults.cardGridSearchResultsNum
        });
      } else {
        // CARD GRID COMPONENT SEARCH NO RESULT
        dataLayer.push({
          'event': 'cardGridSearch_ResultNo',
          'pageId': cardSearchResults.pageId,
          'pageType': cardSearchResults.pageType,
          'cardGridID': '',
          'cardGridName': '',
          'cardGridSearchTerm': cardSearchResults.cardGridSearchTerm,
          'cardSearchResults': ''
        });
      }
    }

    request.onreadystatechange = function() {
      if(request.readyState === 4) {
        if(request.status === 200) {
          let obj = JSON.parse(request.response);
          let suggestions = document.getElementById('suggestions');
          suggestions.innerHTML = '<li>' + obj.suggestions.join('</li><li>') + '</li>';
        } else {
          console.log('An error occurred during your request: ' +  request.status + ' ' + request.statusText)
        }
      }
    }

    search.addEventListener('keypress', function() {
      request.open('Get', 'https://run.mocky.io/v3/a91dbc17-a403-411f-b06f-b6465c25b84f');
      request.send();
    });

    // CARD GRID COMPONENT SEARCH START and SITE SEARCH START should be triggered together
    // dataLayer.push({
    //   'event': 'cardGridSearch_Start',
    //   'pageId': cardSearchResults.pageId,
    //   'pageType': cardSearchResults.pageType,
    //   'cardGridID': '',
    //   'cardGridName': '',
    //   'cardGridSearchTerm': cardSearchResults.cardGridSearchTerm,
    //   'cardSearchResults': ''
    // });
  },
};
