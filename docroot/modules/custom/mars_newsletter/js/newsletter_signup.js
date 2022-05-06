var success_message;
(function($, Drupal, drupalSettings) {
  Drupal.behaviors.newsletter_signup = {
    attach(context, settings) {
      success_message = drupalSettings.mars_newsletter.success_message;
      $(document).ready(function (e) {
        var element = document.getElementById('edit-actions-submit');
        element.onclick = validateCustom;
      });
      // Form validation.
      function validateCustom(event) {
        event.preventDefault();
        var required_message = drupalSettings.mars_newsletter.required_field_message;
        var email_validation_message = drupalSettings.mars_newsletter.email_validation_message;
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        var emailaddressVal = $('#edit-actions-submit').parents('.webform-submission-mars-newsletter-email-form-form').find(".newsletter-email-value").val();
        $(".error").remove();
        $('.success-message').remove();
        if(!emailReg.test(emailaddressVal)) {
          $('#edit-actions-submit').parents('.webform-submission-mars-newsletter-email-form-form').find(".newsletter-email-value").after('<span class="error">' +email_validation_message+ '</span>');
          return false;
        }
        if (!document.getElementById('edit-email').value) {
          $('#edit-actions-submit').parents('.webform-submission-mars-newsletter-email-form-form').find('input').after('<span class="error">' +required_message+ '</span>');
        } else {
          grecaptcha.execute();
        }
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
// Captcha callback.
function captchaCallBack(token) {
  return new Promise(function (resolve, reject) {
    dataLayer.push({
      'event': 'headerNewsLetterSignup',
      'pageName': '',
      'componentName': 'newsletter_email_form_block',
      'email': jQuery('#edit-actions-submit').parents('.webform-submission-mars-newsletter-email-form-form').find('.form-item__textfield').val(),
      'status': '1',
    });
    jQuery('#edit-actions-submit').parents('.webform-submission-mars-newsletter-email-form-form').find('.form-item__textfield').val('');
    jQuery('.email-submit').after('<span class="success-message" style="display:none"> &#10004 ' + success_message + '</span>');
    jQuery('.success-message').fadeIn('slow');
    jQuery('.success-message').delay(5000).fadeOut('slow');
    grecaptcha.reset();
  });
}
