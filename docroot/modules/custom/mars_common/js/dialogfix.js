(function($){
    Drupal.behaviors.dialogfix = {
        attach: function (context) {
            $(context).on('focusin', function(e) {
                e.stopImmediatePropagation();
            });

            $('.color_picker', context).once('dialogfix').click(() => {
                $('.color_picker').parents('.ui-dialog').attr('tabindex', '');
            });
        }
    };
})(jQuery);
