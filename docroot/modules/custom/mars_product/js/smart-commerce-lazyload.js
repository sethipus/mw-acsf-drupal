/**
 * Lazyload Smart Commerce Scripts On Search Click
 */
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
});