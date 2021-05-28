if(window.location.pathname == '/')
{
    $('.ps-widget span').text($('.ps-widget').attr('data-label'));
    $('.ps-widget').css('display','block');
   
    $('.ps-widget').click(function() {

        if ($('.ps-enabled').length == 0) {
            
            $.getScript($('#ps-widget').attr('data-src')).done((script, textStatus) => {
                const checkScriptLoaded = setInterval(() => {
                    if ($('.ps-enabled').length > 0) { 
                        clearInterval(checkScriptLoaded);
                        $(this).find('span:first').remove();
                        $(this).removeClass('default-link default-link-- ');
                        $(this).trigger('click');
                    }
                }, 100);
            });
            return false;
        }
    });
}
else {
    $('.ps-widget').removeClass('default-link default-link-- ');
}