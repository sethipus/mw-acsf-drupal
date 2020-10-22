/**
 * @file
 * Javascript for the OneTrust integration.
 */

/**
 * Provides the initial setup for OneTrust.
 */
(function () {

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
})();
