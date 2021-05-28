$('.cc-fi-button').click(function() {
    if ($('.cci-root-container').length == 0) {
        $.getScript($('#cci-widget').attr('data-src')).done((script, textStatus) => {
            const checkScriptLoaded = setInterval(() => {
                if ($('#widgetMain').length > 0) 
                {
                    clearInterval(checkScriptLoaded); 
                    $(this).trigger('click');
                }
            }, 100);
        });
    }
});