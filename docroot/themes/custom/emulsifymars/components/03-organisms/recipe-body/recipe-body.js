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
          const fullscreenElementsSelector = '.footer, .recommendations, .flexible-framer, .article-full-width';
          let $productUsed = $('.product-used');
          let $recipeInfo = $('.recipe-info');

          // find the first element from list on the page
          let $firstFullwidth = $(fullscreenElementsSelector).first();
          if (smallScreen && productUsedPinned) {
            $productUsed.css('margin-top', 0);
            $firstFullwidth.css('margin-top', 0);
            return false;
          } else if (!smallScreen && !productUsedPinned) {
            let adjacentElementsHeight = $recipeInfo.outerHeight(true);
            let productUsedPlaceholderHeight = $productUsed.outerHeight() - ($firstFullwidth.offset().top - $recipeInfo.offset().top);
            $productUsed.css('margin-top', '-' + ( adjacentElementsHeight - 60 ) + 'px');

            if ($firstFullwidth.length && $recipeInfo.length) {
              $firstFullwidth.css('margin-top', productUsedPlaceholderHeight > 0 ? productUsedPlaceholderHeight + 160 : 0 + 'px');
            }

            return true;
          }

          return productUsedPinned;
        }
      });
    }
  };
})(jQuery, _, Drupal);
