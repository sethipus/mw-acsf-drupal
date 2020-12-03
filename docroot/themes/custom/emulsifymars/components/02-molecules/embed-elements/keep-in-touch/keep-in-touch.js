Drupal.behaviors.keepInTouchForm = {
  attach(context) {
    /* Embed Code */
    $(document).ready(function() {
      /* <!-- Google Tag Manager --> */
      (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&amp;l='+l:'';j.async=true;j.src= 'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f); })(window,document,'script','dataLayer','GTM-M3VZDN2');
      /* <!-- End Google Tag Manager --> */
      // Validating fields.
      function validateField(field) {
        if ($(field).val() == '') {
          $(field).addClass('error')
            .one('change keypress', function(){
              $(event.target).removeClass('error');
            });
        }
      }

      // Send event data.
      $('.keep-in-touch-page-form input').one('focusout', function(e) {
        dataLayer.push({
          'event': 'formFieldComplete',
          'pageName': '',
          'componentName': 'keep-in-touch-form',
          'formName': 'keep-in-touch',
          'formFieldName': e.target.name,
          'formFieldValue': e.target.value,
        });
      });

      // Try to submit form.
      $('.keep-in-touch-page-form__button').on('click', function() {
        $('.keep-in-touch-page-form').find('input').each(function() {
          validateField($(this));
        });
      });

      // Form submit.
      $('.keep-in-touch-page-form').on('submit', function() {
        dataLayer.push({
          'event': 'formSubmit',
          'pageName': '',
          'componentName': 'keep-in-touch-form',
          'formName': 'keep-in-touch',
          'formSubmitFlag': '1',
        });
        return false;
      });
    });
    /* End of Embed Code */
  }
};
