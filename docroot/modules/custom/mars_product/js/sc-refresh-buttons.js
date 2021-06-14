(function($, Drupal){
  Drupal.behaviors.smart_commerce_refresh_buttons = {
    attach: function (context) {
      $(context).ajaxStop(function () {
        if (typeof SmartCart !== "undefined") {
          SmartCart.updateUsaWidget();
        }
      });
    }
  };
})(jQuery, Drupal);
