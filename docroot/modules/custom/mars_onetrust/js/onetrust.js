/**
 * @file
 * Javascript for the OneTrust integration.
 */

/**
 * Provides the initial setup for OneTrust.
 */
(function (Drupal) {

  'use strict';

  Drupal.behaviors.mars_onetrust = {
    attach: function () {
      function OptanonWrapper() {
        window.dataLayer.push({
          event: 'OneTrustGroupsUpdated'
        });
      }
    }
  };
})(Drupal);
