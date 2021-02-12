(function($, _, Drupal) {
  Drupal.behaviors.productFeature = {
    attach(context) {
      $(context).find('.product-feature').once('productFeature').each(function(){
        const productFeature = this;
        var bubble_1_top = productFeature.querySelector('.product-feature__bubble--1').offsetTop;
        var bubble_2_top = productFeature.querySelector('.product-feature__bubble--2').offsetTop;
        var bubble_3_top = productFeature.querySelector('.product-feature__bubble--3').offsetTop;

        const isInViewport = element => {
          const rect = element.getBoundingClientRect();

          const windowHeight = (window.innerHeight || document.documentElement.clientHeight);
          const windowWidth = (window.innerWidth || document.documentElement.clientWidth);

          const vertInView = ((rect.top + (rect.height * 1.1)) <= windowHeight) && ((rect.top + rect.height) >= 0);
          const horInView = (rect.left <= windowWidth) && ((rect.left + rect.width) >= 0);

          return (vertInView && horInView);
        }

        const updateElementsPositions = (element) => {
          const rect = element.getBoundingClientRect();
          const windowHeight = (window.innerHeight || document.documentElement.clientHeight);
          let parallaxCoef;

          switch (true) {
            case rect.width >= 1360:
              parallaxCoef = 1.1;
              break;

            case rect.width >= 688:
              parallaxCoef = 0.5;
              break;

            default:
              parallaxCoef = 0.5;
          }
          let offset = windowHeight - (rect.top + (rect.height * 1.1));
          element.querySelector('.product-feature__bubble--1').style.top = `${(bubble_1_top) - (offset * parallaxCoef)}px`;
          element.querySelector('.product-feature__bubble--2').style.top = `${(bubble_2_top) - (offset * parallaxCoef)}px`;
          element.querySelector('.product-feature__bubble--3').style.top = `${(bubble_3_top) - (offset * parallaxCoef)}px`;

        };

        const restoreElementsPositions = (element) => {
          element.querySelector('.product-feature__bubble--1').style.removeProperty("top");
          element.querySelector('.product-feature__bubble--2').style.removeProperty("top");
          element.querySelector('.product-feature__bubble--3').style.removeProperty("top");

        }

        const listener = () => {
          if (isInViewport(productFeature)) {
            updateElementsPositions(productFeature);
          }
          else {
            restoreElementsPositions(productFeature);
          }
        };

        window.addEventListener('DOMContentLoaded', listener);
        window.addEventListener('scroll', listener);
        window.addEventListener('resize', listener);

      })
    }
  }
})(jQuery, _, Drupal);
