Drupal.behaviors.newsletterSignupForm = {
  attach(context) {
    /* Embed Code */
    $(document).ready(function() {
      /* <!-- Google Tag Manager --> */
      (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&amp;l='+l:'';j.async=true;j.src= 'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f); })(window,document,'script','dataLayer','GTM-M3VZDN2');
      /* <!-- End Google Tag Manager --> */
      // Send event data.
      $('.newsletter-form input').one('focusout', function(e) {
        dataLayer.push({
          'event': 'formFieldComplete',
          'pageName': '',
          'componentName': 'newsletter-signup-form',
          'formName': 'newsletter-signup',
          'formFieldName': e.target.name,
          'formFieldValue': e.target.value,
        });
      });

      // Form submit.
      $('.newsletter-form').on('submit', function() {
        dataLayer.push({
          'event': 'formSubmit',
          'pageName': '',
          'componentName': 'newsletter-signup-form',
          'formName': 'newsletter-signup',
          'formSubmitFlag': '1',
        });
        return false;
      });
    });
    /* End of Embed Code */
  }
};
