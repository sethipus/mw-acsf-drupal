$('.cc-fi-button').click(function() {
    if ($('.cci-root-container').length == 0) {
        $.getScript($('#cci-widget').attr('data-src')).done((script, textStatus) => {
            setTimeout(() => {
                $(this).trigger('click');
            }, 3000);
        });
    }
});