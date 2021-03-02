(function ($, Drupal) {
  Drupal.behaviors.wtbConflictResolver = {
    attach: function (context) {

      function updateCCIDOM() {
        let $root_containers = $(context).find('.cci-root-container');
        $root_containers.attr('data-no-focus-lock', true);
        $('[id="widgetMain"]').parent().slice(1).remove();
        setTimeout(function () {
          $root_containers.find('div:empty:not([class*="Popup"]').each(function () {
            let $parent = $(this).parent();
            let $parent_id = $parent.attr('id');
            if ($parent_id !== 'widgetMain') {
              $parent.remove();
            }
          });
          $('[id="widgetMain"]').parent().slice(1).remove();
        }, 1000);
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
