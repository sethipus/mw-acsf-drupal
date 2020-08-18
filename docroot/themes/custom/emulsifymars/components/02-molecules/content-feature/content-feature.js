Drupal.behaviors.contentFeature = {
  attach(context) {
    if (context.getElementById('content-feature') === null) {
      return;
    }
    window.addEventListener('scroll', () => {
      const parallaxElement = context.getElementById('content-feature');
      const offset = window.pageYOffset;
      parallaxElement.style.backgroundPosition = `0 ${- (offset * .3)}px`;
    })

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
