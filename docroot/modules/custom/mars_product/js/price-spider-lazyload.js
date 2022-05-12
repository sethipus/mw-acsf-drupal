(function($){
    function _lazyLoadWhereToBuy() {

        pricespiderLoaded = true;
        if($('#ps-widget').length > 0 && $('.ps-enabled').length == 0)
        {
            $.getScript($('#ps-widget').attr('data-src'));
        }
    
    }
    let pricespiderLoaded = false;
    
    $('.inline-search__link').click(() => {
        _lazyLoadWhereToBuy();
    });

    $(window).scroll(function(){
        if (
                (
                    isInView($('.ajax-card-grid__items'))
                    ||
                    isInView($('div[data-block-plugin-id="product_content_pair_up_block"]'))
                    ||
                    isInView($('div[data-block-plugin-id="recommendations_module"]'))
                    ||
                    isInView($('div[data-block-plugin-id="recipe_detail_body"]'))
                )
                &&
                !pricespiderLoaded
            )
        {
            _lazyLoadWhereToBuy();
        }
    });

    function isInView(elem){
        return $(elem).length > 0 ? $(elem).offset().top - $(window).scrollTop() < $(elem).height() : false;
     }
})(jQuery);
