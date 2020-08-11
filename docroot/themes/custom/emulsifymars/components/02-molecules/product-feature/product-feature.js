Drupal.behaviors.contentFeature = {
  attach(context) {
    const bubble_1_top = context.getElementById('product-feature__bubble_1').getBoundingClientRect().top;
    const bubble_2_top = context.getElementById('product-feature__bubble_2').getBoundingClientRect().top;
    const bubble_3_top = context.getElementById('product-feature__bubble_3').getBoundingClientRect().top;

    window.addEventListener('scroll', () => {
      const bubble_1 = context.getElementById('product-feature__bubble_1');
      const bubble_2 = context.getElementById('product-feature__bubble_2');
      const bubble_3 = context.getElementById('product-feature__bubble_3');
      const offset = window.pageYOffset;

      bubble_1.style.top = `${bubble_1_top - (offset * .75)}px`;
      bubble_2.style.top = `${bubble_2_top - (offset * .75)}px`;
      bubble_3.style.top = `${bubble_3_top - (offset * .75)}px`;
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
