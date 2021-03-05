(function ($, Drupal) {
  Drupal.behaviors.wtbConflictResolver = {
    attach: function (context) {

      function updateCCIDOM() {
        let $root_containers = $(context).find('.cci-root-container');
        $root_containers.attr('data-no-focus-lock', true);
        $('[id="widgetMain"]').parent().slice(1).remove();
        $('.product-selector').attr('data-no-focus-lock', true);
      }

      $('.cc-fi-button').on('click', function () {
        updateCCIDOM();
      });

      $('.product-selector__product-variant-selector').on('change', function () {
        updateCCIDOM();
      });

      updateCCIDOM();
    }
  };
})(jQuery, Drupal);
