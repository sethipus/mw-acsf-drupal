(function($){
  Drupal.behaviors.feedback = {
    attach(context) {
      const feedbackContainer = context.querySelector('.feedback-module');
      const feedbackForm = feedbackContainer.closest('form');
      const feedbackInputs = feedbackContainer.querySelectorAll('input');
      const componentBlock = feedbackContainer.closest('[data-block-plugin-id]');
      const componentName = componentBlock ? componentBlock.dataset.blockPluginId : '';

      // Add event listeners to provide info to Data layer
      if (typeof dataLayer !== 'undefined') {
        feedbackInputs.forEach((input) => {
          input.addEventListener('click', () => {
            let clientId = '';
            const cookies = document.cookie.split(';');
            cookies.forEach(cookie => {
              if(cookie.indexOf('_ga=') !== -1) {
                clientId = cookie.trim().substring(4, cookie.length);
              }
            });

            dataLayer.push({
              pageName: document.title,
              componentName: componentName,
              formSubmitFlag: 1,
              clientId: clientId,
              formName: 'feedback',
              formID: feedbackForm.id || '',
              formfieldId: input.value || ''
            });

          });
        });
      }

      $('.feedback-module').once('feedback').each(function() {
        let radioBtn = $(this).find('.feedback-module__radio');
        let submitBtn = $(this).find('.button-vote');
        radioBtn.each(function() {
          $(this).change(() => {
            submitBtn.mousedown();
          });
        });
      });
    },
  };
})(jQuery);
