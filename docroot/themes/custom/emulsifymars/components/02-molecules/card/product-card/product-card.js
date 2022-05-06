(function ($, Drupal, _) {
  let WTBInit = _.debounce(
    function () {
      if (
        typeof (window.PriceSpider) !== 'undefined' &&
        typeof (window.PriceSpider.rebind) === 'function'
      ) {
        window.PriceSpider.rebind();
      }
    },
    200
  );
  Drupal.behaviors.productCard = {
    attach(context) {
      $(context).find('.product-card').once('productCard').each(function(){
        const $productCard = $(this);
        const $cardCta = $productCard.find('.default-link');
        
        $productCard.on('mouseover', () => {
          if(window.screen.availWidth >768) {
            $cardCta.addClass('default-link--light')
          }

        });
        $productCard.on('mouseleave', () => {
          $cardCta.removeClass('default-link--light')
        });
        $productCard.on('click', (e) => {
          $cardCta.removeClass('default-link--light');
          if (!$(e.target).closest('.where-to-buy').length) {
            window.location.href = $cardCta.attr('href');
          }
        });
        WTBInit();
      })
    }
  }
})(jQuery, Drupal, _);
