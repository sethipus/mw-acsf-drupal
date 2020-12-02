Drupal.behaviors.newsletterSignupForm = {
  attach(context) {
    /* Embed Code */
    $(document).ready(function() {
      /* <!-- Google Tag Manager --> */
      (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&amp;l='+l:'';j.async=true;j.src= 'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f); })(window,document,'script','dataLayer','GTM-M3VZDN2');
      /* <!-- End Google Tag Manager --> */
      // Send event data.
      $('.newsletter-form input').on('focusout', function(e) {
        dataLayer.push({
          'event': 'formfieldComplete',
          'formName': 'newsletter-signup',
          'formFieldName': e.target.id,
          'componentName': 'newsletter-signup'
        });
        $(this).off(e);
      });
    });
    /* End of Embed Code */
  }
};
