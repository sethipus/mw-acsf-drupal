(function($, Drupal){
    Drupal.behaviors.recipeCard = {
      attach(context) {
        $(context).find('.recipe-card').once('recipeCard').each(function(){
            const $recipeCard = $(this);
            const $cardCta = $recipeCard.find('.default-link');
  
            $recipeCard.on('click', (e) => {
              $cardCta.blur();
            });
          })
      }
    }
  })(jQuery, Drupal);