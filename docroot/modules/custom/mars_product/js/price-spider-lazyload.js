function _lazyLoadWhereToBuy() {
    
    if(window.location.pathname == '/')
    {
        if($('#ps-widget').length > 0 && $('.ps-enabled').length == 0)
        {
            $.getScript($('#ps-widget').attr('data-src'));
        }
    }
    else {
        $('.ps-widget').removeClass('default-link default-link-- ');
    }
    
}