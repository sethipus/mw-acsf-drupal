(function($, _){
  Drupal.behaviors.contentFeature = {
    attach(context) {
      if (context.getElementById('content-feature') === null) {
        return;
      }
  
      $(window).on('scroll', _.throttle(() => {
        const parallaxElement = context.getElementById('content-feature');
        if (isInViewport(parallaxElement)){
          const offset = window.pageYOffset;
          parallaxElement.style.backgroundPosition = `50% ${- (offset * .3)}px`;
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
})(jQuery, _);
