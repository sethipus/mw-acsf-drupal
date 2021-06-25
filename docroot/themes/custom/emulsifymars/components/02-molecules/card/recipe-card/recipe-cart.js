(function($, Drupal){
    Drupal.behaviors.recipeCard = {
      attach(context) {
        $(context).find('.recipe-card').once('recipeCard').each(function(){
            const $recipeCard = $(this);
            const $cardCta = $recipeCard.find('.default-link');
  
            $recipeCard.on('mouseover', () => {
              $cardCta.removeClass('default-link--visited')
            });

            $recipeCard.on('click', (e) => {
              $cardCta.addClass('default-link--visited');
            });
          })
      }
    }
  })(jQuery, Drupal);
/**
 * Reload on page redirect / Disable cache. #249188
 */
window.addEventListener('popstate',()=>{
  window.location.reload();
});