(function($){
    function _lazyLoadWhereToBuy() {

        commerceConnetor = true;
        if($('#cci-widget').length > 0 && $('.cci-root-container').length == 0)
        {
            $.getScript($('#cci-widget').attr('data-src'));
        }
    }
    let commerceConnetor = false;
    
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
                !commerceConnetor
            )
        {
            _lazyLoadWhereToBuy();
        }
    });

    function isInView(elem){
        return $(elem).length > 0 ? $(elem).offset().top - $(window).scrollTop() < $(elem).height() : false;
     }
})(jQuery);
