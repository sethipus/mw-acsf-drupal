(function($, _, Drupal){
  Drupal.behaviors.recipeBody = {
    attach: function (context) {
      $(context).find('.recipe-body-content').once('recipeBody').each(() => {
        let productUsedPinned = false;
        productUsedPinned = adaptProductUsedBlock(productUsedPinned);

        $(window).on('resize', _.debounce(() => {
          productUsedPinned = adaptProductUsedBlock(productUsedPinned);
        }, 200));

        function adaptProductUsedBlock(productUsedPinned) {
          const smallScreen = window.innerWidth < 1440;
          let $productUsed = $('.product-used', this);

          if (smallScreen && productUsedPinned) {
            $productUsed.css('margin-top', 0);
            return false;
          } else if (!smallScreen && !productUsedPinned) {
            let $adjacentElement = $('.recipe-info', this).outerHeight(true);
            $productUsed.css('margin-top', '-' + $adjacentElement + 'px');
            return true;
          }

          return productUsedPinned;
        }
      });
    }
  };
})(jQuery, _, Drupal);
