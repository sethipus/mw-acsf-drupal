(function (Drupal) {
  Drupal.behaviors.searchInput = {
    attach(context, settings) {
      var search = document.getElementById('search');
      var request = new XMLHttpRequest();

      request.onreadystatechange = function () {
        if (request.readyState === 4) {
          if (request.status === 200) {
            let obj = JSON.parse(request.response);
            let suggestions = document.getElementById('suggestions');
            suggestions.innerHTML = '<li>' + obj.suggestions.join('</li><li>') + '</li>';
          }
          else {
            console.log('An error occurred during your request: ' + request.status + ' ' + request.statusText)
          }
        }
      }

      search.addEventListener('keypress', function () {
        request.open('Get', 'https://run.mocky.io/v3/a91dbc17-a403-411f-b06f-b6465c25b84f');
        request.send();
      });
    },
  };
})(Drupal);
