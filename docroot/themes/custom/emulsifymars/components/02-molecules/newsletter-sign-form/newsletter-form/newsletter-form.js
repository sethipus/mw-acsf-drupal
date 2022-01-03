(function ($, Drupal) {
    Drupal.behaviors.newsLetterForm = {
        attach(context) {
            $(document).ready(function() {
                if($('#successMsg').hasClass('successMessage')) {
                    $('.successMessage').parents('.status__container').delay(5000).fadeOut('slow');
                }
                
            })
        }
    }
})(jQuery, Drupal);
