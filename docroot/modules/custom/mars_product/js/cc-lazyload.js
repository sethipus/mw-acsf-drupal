function _lazyLoadWhereToBuy() {
    
    if(window.location.pathname == '/')
    {
        if($('#cci-widget').length > 0 && $('.cci-root-container').length == 0)
        {
            $.getScript($('#cci-widget').attr('data-src'));
        }
    }
}