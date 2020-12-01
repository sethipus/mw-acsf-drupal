(function($, _, Drupal) {
  Drupal.behaviors.productFeature = {
    attach(context) {
      $(context).find('.product-feature').once('productFeature').each(function(){
        const $productFeature = $(this);
        const $bubble_1_top = $('.product-feature__bubble_1', this)[0].getBoundingClientRect().top;
        const $bubble_2_top = $('.product-feature__bubble_2', this)[0].getBoundingClientRect().top;
        const $bubble_3_top = $('.product-feature__bubble_3', this)[0].getBoundingClientRect().top;
        
        $(window).on('scroll', _.throttle(() => {
          if (isInViewport($productFeature[0])){
            const $bubble_1 = $('.product-feature__bubble_1', this);
            const $bubble_2 = $('.product-feature__bubble_2', this);
            const $bubble_3 = $('.product-feature__bubble_3', this);
            const offset = window.pageYOffset;
            
            $bubble_1.css('top', `${$bubble_1_top - (offset * .75)}px`);
            $bubble_2.css('top', `${$bubble_2_top - (offset * .75)}px`);
            $bubble_3.css('top', `${$bubble_3_top - (offset * .75)}px`);
          }
        }, 200))
    
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
      })
    }
  }
})(jQuery, _, Drupal)
