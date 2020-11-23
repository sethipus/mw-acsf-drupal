(function($, Drupal){
    Drupal.behaviors.productCard = {
      attach(context) {
        $(context).find('.product-card').once('productCard').each(function(){
          const $productCard = $(this);
          const $cardCta = $productCard.find('.default-link');

          $productCard.on('mouseover', () => {
            $cardCta.addClass('default-link--light')
          });
          $productCard.on('mouseleave', () => {
            $cardCta.removeClass('default-link--light')
          })
        })
      }
    }
  })(jQuery, Drupal);
