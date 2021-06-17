(function($, Drupal){
  Drupal.behaviors.smart_commerce_refresh_buttons = {
    attach: function (context) {
      $(context).ajaxStop(function () {
        if (typeof SmartCart !== "undefined") {
          /**
           * Prevents search widget close on button click.
           */
           $('.search-cards-container .product-card__link').click(() => { return false; });
          SmartCart.updateUsaWidget();
        }
      });
    }
  };
})(jQuery, Drupal);
