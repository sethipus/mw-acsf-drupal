function _lazyLoadWhereToBuy() {

    if($('#cci-widget').length > 0 && $('.cci-root-container').length == 0)
    {
        $.getScript($('#cci-widget').attr('data-src'));
    }
}

let commerceConnetor = false;
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
            !commerceConnetor
        )
    {
        _lazyLoadWhereToBuy();
        commerceConnetor = true;
    }
})

function isInView(elem){
   return $(elem).length > 0 ? $(elem).offset().top - $(window).scrollTop() < $(elem).height() : false;
}