(function($){
  Drupal.behaviors.feedback = {
    attach(context) {
      $('.feedback-module').each(function() {
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
