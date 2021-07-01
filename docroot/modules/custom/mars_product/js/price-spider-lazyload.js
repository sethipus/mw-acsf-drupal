function _lazyLoadWhereToBuy() {

    if($('#ps-widget').length > 0 && $('.ps-enabled').length == 0)
    {
        $.getScript($('#ps-widget').attr('data-src'));
    }
    
}

let pricespiderLoaded = false;
$(window).scroll(function(){
    if (
            (
                isInView($('.ajax-card-grid__items')) 
                || 
                isInView($('div[data-block-plugin-id="product_content_pair_up_block"]')) 
                || 
                isInView($('div[data-block-plugin-id="recommendations_module"]'))
            ) 
            && 
            !pricespiderLoaded
        )
    {
        _lazyLoadWhereToBuy();
        pricespiderLoaded = true;
    }
})

function isInView(elem){
   return $(elem).length > 0 ? $(elem).offset().top - $(window).scrollTop() < $(elem).height() : false;
}