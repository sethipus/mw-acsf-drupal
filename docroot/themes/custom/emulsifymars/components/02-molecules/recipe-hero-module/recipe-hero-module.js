(function ($, Drupal) {
  Drupal.behaviors.recipeHeroModule = {
    attach: function (context) {
      $(context).find('.recipe-media').once('recipeHeroModule').each(function () {
        const recipeHeroModule = this;

        const isInViewport = element => {
          const rect = element.getBoundingClientRect();

          const windowHeight = (window.innerHeight || document.documentElement.clientHeight);
          const windowWidth = (window.innerWidth || document.documentElement.clientWidth);

          const vertInView = (rect.top <= windowHeight) && ((rect.top + rect.height) >= 0);
          const horInView = (rect.left <= windowWidth) && ((rect.left + rect.width) >= 0);

          return (vertInView && horInView);
        }

        const updateElementsPositions = (element) => {
          const rect = element.getBoundingClientRect();
          const offset = (window.innerHeight || document.documentElement.clientHeight) - rect.top;
          let parallaxCoef;

          switch (true) {
            case rect.width >= 1360:
              parallaxCoef = 0.03;
              break;

            case rect.width >= 688:
              parallaxCoef = 0.01;
              break;

            default:
              parallaxCoef = 0.005;
          }

          let recipeMediaImageWrapper = element.querySelector('.recipe-media__image-wrapper');
          if (recipeMediaImageWrapper !== null) {
            recipeMediaImageWrapper.style.backgroundPosition = `center calc(50% - ${offset * parallaxCoef}px`;
          }
        };

        const listener = () => {
          if (isInViewport(recipeHeroModule)) {
            updateElementsPositions(recipeHeroModule);
          }
        };

        window.addEventListener('DOMContentLoaded', listener);
        window.addEventListener('scroll', listener);
        window.addEventListener('resize', listener);
      })
    }
  }
})(jQuery, Drupal);
