/**
 * @file
 * Javascript for the commerce connector.
 */

/**
 * Attach commerce connector script.
 */
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.commerceConnector = {
    attach: function (context, settings) {

      var s = document.createElement("script");
      s.type = "text/javascript";
      s.src = "//fi-v2.global.commerce-connector.com/cc.js";
      s.id = 'cci-widget';
      s.setAttribute('data-token', settings.cc['data-token']);
      s.setAttribute('data-locale', settings.cc['data-locale']);
      s.setAttribute('data-displaylanguage', settings.cc['data-displaylanguage']);
      s.setAttribute('data-widgetid', settings.cc['data-widgetid']);
      s.setAttribute('data-subid', settings.cc['data-subid']);
      $('body').append(s);
    }
  };
})(jQuery, Drupal, drupalSettings);

