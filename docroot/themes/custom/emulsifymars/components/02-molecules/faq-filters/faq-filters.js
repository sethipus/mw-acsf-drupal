(function($, Drupal) {
    Drupal.behaviors.faqFiltersTwig = {
        attach(context) {
            $(document).ready(function(){
                $('.clear-icon').click(function(){
                    $(this).siblings('input').val('');
                })
            })
        }
    }
})(jQuery, Drupal);