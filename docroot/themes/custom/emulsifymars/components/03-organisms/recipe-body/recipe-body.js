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
          const $productUsedParent = $('.recipe-body-content');
          const $productUsed = $('.product-used');
          const $recipeInfo = $('.recipe-info');

          // find the first element from list on the page
          const $firstFullwidth = $(fullscreenElementsSelector).first();
          if (smallScreen && productUsedPinned) {
            $productUsed.css('margin-top', 0);
            $firstFullwidth.css('margin-top', 0);
            return false;
          } else if (!smallScreen && !productUsedPinned) {
            const adjacentElementsHeight = $productUsedParent.offset().top - $recipeInfo.offset().top;
            $productUsed.css('margin-top', '-' + ( adjacentElementsHeight ) + 'px');

            if ($firstFullwidth.length && $recipeInfo.length) {
              const productUsedPlaceholderHeight = $firstFullwidth.offset().top - $recipeInfo.offset().top;
              const productUsedHeight = $productUsed.outerHeight();
              if(productUsedHeight > productUsedPlaceholderHeight) {
                $firstFullwidth.css('margin-top', (productUsedHeight - productUsedPlaceholderHeight) + 'px');
              }
            }

            return true;
          }

          return productUsedPinned;
        }
      });
    }
  };
})(jQuery, _, Drupal);
