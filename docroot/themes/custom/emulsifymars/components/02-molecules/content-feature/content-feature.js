(function($, Drupal, _){
  Drupal.behaviors.contentFeature = {
    attach(context) {
      const parallaxElement = $('.content-feature', context);

      if (!parallaxElement.length) {
        return;
      }
  
      $(window).once('contentFeature').on('scroll', _.throttle(() => {
        if (isInViewport(parallaxElement[0])){
          const offset = window.pageYOffset;
          parallaxElement.css('background-position', `50% ${- (offset * .3)}px`);
        }
      }, 50));
      
      const isInViewport = element => {
        const scroll = window.scrollY || window.pageYOffset
        const boundsTop = element.getBoundingClientRect().top + scroll
        const viewport = {
          top: scroll,
          bottom: scroll + window.innerHeight,
        }
        const bounds = {
          top: boundsTop,
          bottom: boundsTop + element.clientHeight,
        }
        return (bounds.bottom >= viewport.top && bounds.bottom <= viewport.bottom) ||
          (bounds.top <= viewport.bottom && bounds.top >= viewport.top);
      }
    }
  }
})(jQuery, Drupal, _);
