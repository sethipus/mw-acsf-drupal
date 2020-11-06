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
      var body = context.querySelector('body');
      if (body === null || body.getAttribute('datalayer-page-view')) {
        return;
      }
      console.log(settings);
      dataLayer.push(settings.dataLayer);
      body.setAttribute('datalayer-page-view', true);
    }
  };
})(jQuery, Drupal, drupalSettings);
