/**
 * Lazyload Smart Commerce Scripts On Search Click
 */
$('.inline-search__link').click(() => {
    if(typeof SmartCart === "undefined")
    {
        $.getScript($('#smart-commerce-widget').attr('data-src'));
    }
});