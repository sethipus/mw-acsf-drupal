(function($, Drupal){
  Drupal.behaviors.recipeEmailForm = {
    attach(context) {
      const openPopupButton = $('.js-social-email-btn');
      const recipeEmailLayout = $('.recipe-email-layout');
      const recipeEmailLightbox = $('.recipe-email-lightbox');
      const recipeEmailCloseButton = $('.recipe-email-close-btn');
      const inputFields = $('.recipe-email .form-item');
      const errorMsg = $('.email-recipe-message-box');
      const formSubmitButton = $('.js-form-submit');
      const recipeEmailForm = $('#recipe-email-form');

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
        recipeEmailForm.removeClass('recipe-email-form--invalid');
      });

      formSubmitButton.on('click', () => {
        recipeEmailForm.addClass('recipe-email-form--invalid');
      });
    },
  };
})(jQuery, Drupal);
