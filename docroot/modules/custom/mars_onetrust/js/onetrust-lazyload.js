let scriptLoadInProgress = false;
function _lazyLoadCookieBanner(callBackSettings = false) {

    if($('#onetrust-sdk').length > 0 && !scriptLoadInProgress)
    {
        scriptLoadInProgress = true;
        $('#onetrust-sdk').attr('src',$('#onetrust-sdk').attr('data-src'));
        $.getScript($('#onetrust-sdk').attr('data-src')).then(() => {
            if(callBackSettings)
            {
                const checkElement = setInterval(() => {
                    if($('#onetrust-pc-btn-handler').length > 0)
                    {
                        clearInterval(checkElement);
                        $('#onetrust-pc-btn-handler').trigger('click');
                    }
                }, 100);
            }
        });
    }

}

const getCookieData = name => {
    const cookieArr = document.cookie.split(";");
    for (let i = 0; i < cookieArr.length; i++) {
        const cookiePair = cookieArr[i].split("=");
        if (name === cookiePair[0].trim()) {
        return decodeURIComponent(cookiePair[1]);
        }
    }
    return null;
};

window.onload = () => {
    if(getCookieData('OptanonAlertBoxClosed') == null)
    {
        $('.cookie-parent-div').slideDown('slow');	
        $('.cookie-parent-div').css('display','flex');

        $('#cookie-banner-settings').click(() => {
            $(this).attr("disabled", "disabled");
            _lazyLoadCookieBanner(true);
        });

        $('#onetrust-close-btn-container').click(() => {
            $('.cookie-parent-div').slideUp('slow');	
        });
        
        $('.cookie-banner-close-button').click(() => {
            closeCookieBanner();
        });

        $('#onetrust-accept-btn-handler').click(() => {
            closeCookieBanner();
        });
    }
};

const closeCookieBanner = () => {
    document.cookie = "OptanonAlertBoxClosed="+new Date().toISOString();
    $('.cookie-parent-div').slideUp('slow');	
}