Drupal.behaviors.keepInTouch = {
  attach(context) {
    /* Embed Code */
    $(document).ready(function() {
      // Validating fields.
      function validateField(field) {
        if ($(field).val() == '') {
          $(field).addClass('error');
        } else {
          $(field).removeClass('error');
        }
      }

      $('input').click(function(){
        $(event.target).removeClass('error');
      });

      // Form submit.
      $('.keep-in-touch-page-form').on('submit', function() {
        $(this).find('input').each(function() {
          validateField($(this));
        });

        return false;
      });
    });
    /* End of Embed Code */
  }
};
