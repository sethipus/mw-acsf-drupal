(function($, Drupal, drupalSettings) {
  Drupal.behaviors.newsletter_signup = {
    attach(context, settings) {
      $(document).ready(function() { 
        // Alert banner newsletter signup form.
        $('.newsletter-signup-email-submit .webform-button--submit', context).click(function(e) {  
          e.preventDefault();
          var required_message = drupalSettings.mars_newsletter.required_field_message;
          var email_validation_message = drupalSettings.mars_newsletter.email_validation_message;
          var success_message = drupalSettings.mars_newsletter.success_message;
          $(".error").remove();
          var hasError = false;
          var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
          var emailaddressVal = $(this).parents('.webform-submission-mars-newsletter-email-form-form').find(".newsletter-email-value").val();
          $('.success-message').remove();
          $('.email-submit').after('<span class="success-message" style="display:none"> &#10004 ' + success_message + '</span>');
          if(!emailReg.test(emailaddressVal)) {
            $(this).parents('.webform-submission-mars-newsletter-email-form-form').find(".newsletter-email-value").after('<span class="error">' +email_validation_message+ '</span>');
            hasError = true;
          }
          $(this).parents('.webform-submission-mars-newsletter-email-form-form').find('input').each(function () {
            validateField($(this));
          });
          function validateField(field) {
            if ($(field).val() == '' && $(field).prop('required') && !$(field).is(':checkbox')) {
              $(field).after('<span class="error">' +required_message+ '</span>');
              hasError = true;
            }
          }
          if(hasError == true) { 
            return false;
          }
          dataLayer.push({
            'event': 'headerNewsLetterSignup',
            'pageName': '',
            'componentName': 'newsletter_email_form_block',
            'email': emailaddressVal,
            'status': '1',
          });
          $(this).parents('.webform-submission-mars-newsletter-email-form-form').find('.form-item__textfield').val('');
          $('.success-message').fadeIn('slow');
          $('.success-message').delay(5000).fadeOut('slow');
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
