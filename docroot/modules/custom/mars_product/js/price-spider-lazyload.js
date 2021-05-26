if(window.location.pathname == '/')
{
    $('.ps-widget span').text($('.ps-widget').attr('data-label'));
    $('.ps-widget').css('display','block');
   
    $('.ps-widget').click(function() {

        if ($('.ps-enabled').length == 0) {
            
            $.getScript($('#ps-widget').attr('data-src')).done((script, textStatus) => {
                setTimeout(() => {
                    $(this).find('span:first').remove();
                    $(this).removeClass('default-link default-link-- ');
                    $(this).trigger('click');
                }, 3000);
            });
            return false;
        }
    });
}
else {
    $('.ps-widget').removeClass('default-link default-link-- ');
}