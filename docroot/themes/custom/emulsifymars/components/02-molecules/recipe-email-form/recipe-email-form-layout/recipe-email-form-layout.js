(function($, Drupal){
  Drupal.behaviors.recipeEmailForm = {
    attach(context) {
      const openPopupButton = $('.js-social-email-btn');
      const recipeEmailLayout = $('.recipe-email-layout');
      const recipeEmailLightbox = $('.recipe-email-lightbox');
      const recipeEmailCloseButton = $('.recipe-email-close-btn');
      const inputFields = $('.recipe-email .form-item');
      const errorMsg = $('.email-recipe-message-box');

      openPopupButton.on('click', (e) => {
        $('.recipe-email-layout').addClass('recipe-email-layout--opened');
        e.preventDefault();
        e.stopPropagation();
      });

      // Popup open/close events
      recipeEmailCloseButton.on('click', () => {
        $('.recipe-email-layout').removeClass('recipe-email-layout--opened');
      });

      recipeEmailLayout.on('click', function(e) {
        $('.recipe-email-layout').removeClass('recipe-email-layout--opened');
      });

      recipeEmailLightbox.on('click', function(e) {
        e.stopPropagation();
      });

      // Validation events
      inputFields.on('focus click', () => {
        errorMsg.html('');
        $('error-border').removeClass('error-border');
      });
    },
  };
})(jQuery, Drupal);
