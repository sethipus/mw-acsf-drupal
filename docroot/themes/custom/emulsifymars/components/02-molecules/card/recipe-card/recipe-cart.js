(function($, Drupal){
    Drupal.behaviors.recipeCard = {
      attach(context) {
        $(context).find('.recipe-card').once('recipeCard').each(function(){
            const $recipeCard = $(this);
            const $cardCta = $recipeCard.find('.default-link');
  
            
            $recipeCard.on('mouseover', () => {
              $cardCta.addClass('default-link--light')
            });
            $recipeCard.on('mouseleave', () => {
              $cardCta.removeClass('default-link--light')
            });
            $recipeCard.on('click', (e) => {
              $cardCta.removeClass('default-link--light')
              if (
                !e.target.parentNode.classList.contains('where-to-buy')
              ) {
                window.location.href = $cardCta.attr('href');
              }
            });
            WTBInit();
          })
      }
    }
  })(jQuery, Drupal);