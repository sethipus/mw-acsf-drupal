/**
 * @file
 * Javascript for the Poll component.
 */

/**
 * Provides the initial setup for Poll ajax component.
 */
(function($, Drupal, drupalSettings) {

  /**
   * Implements ajax block behaviour.
   */
  Drupal.behaviors.pollAjax = {
    attach: function (context, settings) {
      var ajaxHandler = function ($block) {
        var blockId = $block.data('ajax-block-id');

        var config = settings.pollConfig[blockId];
        config.block_config['ajax_render'] = true;

        var ajaxSettings = {
          url: Drupal.url('poll_block/ajax'),
          submit: config,
        };
        if (settings.pollConfig[blockId].ajax_block) {
          var ajaxObject = Drupal.ajax(ajaxSettings);
          ajaxObject.execute();
        }
      };

      // Initialise ajax refresh event handlers.
      $('[data-ajax-block-id]').once('ajax-block').each(function () {
          // Execute the handler payload.
          ajaxHandler($(this));
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
