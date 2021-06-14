/**
 * Lazyload Smart Commerce Scripts On Search Click
 */
$('.inline-search__link').click(() => {
    if(typeof SmartCart === "undefined")
    {
        $.getScript($('#smart-commerce-widget').attr('data-src')).done(() => {
            $.getScript($('#smart-commerce-brand-js').attr('data-src'));
            $.getScript($('#smart-commerce-brand-css').attr('data-src'));            
        });
    }
});