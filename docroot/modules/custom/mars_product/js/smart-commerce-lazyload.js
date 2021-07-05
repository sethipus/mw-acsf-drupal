/**
 * Lazyload Smart Commerce Scripts On Search Click
 */
function _lazyLoadWhereToBuy() {

    smartCommerceLoaded = true;
    if(typeof SmartCart === "undefined")
    {
        $.getScript($('#smart-commerce-widget').attr('data-src')).done(() => {
            $.getScript($('#smart-commerce-brand-js').attr('data-src'));
            $.getStylesheet($('#smart-commerce-brand-css').attr('data-src'));            
        });
        /**
         * Load BazarVoice
         */
        $.getScript($('#bazaar-voice-scripts').attr('data-src'));
    }
}

$.getStylesheet = function (href) {
    var $d = $.Deferred();
    var $link = $('<link/>', {
        rel: 'stylesheet',
        type: 'text/css',
        href: href
    }).appendTo('head');
    $d.resolve($link);
    return $d.promise();
};

$('.inline-search__link').click(() => {
    _lazyLoadWhereToBuy();
});

let smartCommerceLoaded = false;
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
            !smartCommerceLoaded
        )
    {
        _lazyLoadWhereToBuy();
    }
})

function isInView(elem){
   return $(elem).length > 0 ? $(elem).offset().top - $(window).scrollTop() < $(elem).height() : false;
}