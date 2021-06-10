function _lazyLoadCookieBanner() {

    if($('#onetrust-sdk').length > 0)
    {
        $('#onetrust-sdk').attr('src',$('#onetrust-sdk').attr('data-src'));
        $.getScript($('#onetrust-sdk').attr('data-src'));
    }

}

