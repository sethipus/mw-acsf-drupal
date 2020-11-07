/**
 * @file
 * Javascript for the search related things.
 */

/**
 * dataLayer page view.
 */
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.dataLayerPageView = {
    attach: function (context, settings) {
      if (typeof dataLayer === 'undefined') {
        return;
      }
      var body = context.querySelector('body');
      if (body === null || body.getAttribute('datalayer-page-view')) {
        console.log(settings);
        return;
      }
      dataLayer.push(settings.dataLayer);
      var searchInputs = document.querySelectorAll('.data-layer-search-form-input');
      searchInputs.forEach(function (input) {
        input.addEventListener('focus', function() {
          // SITE SEARCH START
          dataLayer.push({
            'event': 'siteSearch_Start',
            'siteSearchTerm': '',
            'siteSearchResults': ''
          })
        });
      });
      body.setAttribute('datalayer-page-view', true);
    }
  };
})(jQuery, Drupal, drupalSettings);
