Drupal.behaviors.contentFeature = {
  attach(context) {
    window.addEventListener('scroll', () => {
      const bubble_1 = context.getElementById('product-feature__bubble_1');
      const bubble_2 = context.getElementById('product-feature__bubble_2');
      const bubble_3 = context.getElementById('product-feature__bubble_3');
      const offset = window.pageYOffset;

      bubble_1.style.backgroundPosition = `0 ${70 - (offset * .3)}px`;
      bubble_2.style.backgroundPosition = `0 ${200 - (offset * .3)}px`;
      bubble_3.style.backgroundPosition = `0 ${450 - (offset * .3)}px`;
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
