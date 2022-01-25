(function ($, Drupal) {
    Drupal.behaviors.newsLetterForm = {
        attach(context) {
            $(document).ready(function() {
                if($('#successMsg').hasClass('successMessage')) {
                    $('html,body').animate({
                        scrollTop: $('#successMsg').offset().top - 100
                    }, 1000);
                }
            })
        }
    }
})(jQuery, Drupal);
