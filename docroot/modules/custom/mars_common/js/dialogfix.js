(function($, Drupal){
    Drupal.behaviors.dialogfix = {
        attach: function (context) {
            $(context).once('dialogfix').on('focusin', function(e) {
                e.stopImmediatePropagation();
            });

            $('.color_picker', context).once('dialogfix').click(() => {
                $('.color_picker').parents('.ui-dialog').attr('tabindex', '');
            });
        }
    };
})(jQuery, Drupal);
