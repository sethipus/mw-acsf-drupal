(function ($, Drupal) {
    Drupal.behaviors.newsLetterForm = {
        attach(context) {
            $(document).ready(function() {
                if($('#successMsg').hasClass('successMessage')) {
                    $('html,body').animate({
                        scrollTop: $('#successMsg').offset().top - 100
                    }, 1000);
                }
                if ($('input[name="name[first]"]') || $('input[name="name[last]"]')) {
                    $(".form-item:has(.error)").addClass('error');
                }
                if ($(".form-item:has(.error)").length) {
                    $('.form-item:has(.error)').each(function(e){
                        $('input', this).focus();
                        return false;
                    });
                }
                if ($(".form-item:has(.error)").length == 0 && $('.captcha + span.validation-error').length) {
                    $('.g-recaptcha > div').attr({ id:"g-recaptcha",  tabindex: "1" });
                    setTimeout(function(e) {
                        $('#g-recaptcha').focus().blur();
                        return false;
                    },500);
                }
                if ($('.captcha + span.validation-error').length) {
                    $('.g-recaptcha > div').attr({ id:"g-recaptcha",  tabindex: "1" });
                }
                if ($(".form-item:has(.error)").length == 0 && $('.captcha + span.validation-error').length == 0) {
                    $('.status__container .status--error').delay('200').fadeIn(500);
                }
            })
        }
    }
})(jQuery, Drupal);
