(function($, Drupal, _){
  Drupal.behaviors.contentFeature = {
    attach(context) {
      $(context).find('.content-feature').once('contentFeature').each(function() {
        const contentFeatureModule = this;

        const isInViewport = element => {
          const rect = element.getBoundingClientRect();

          const windowHeight = (window.innerHeight || document.documentElement.clientHeight);
          const windowWidth = (window.innerWidth || document.documentElement.clientWidth);

          const vertInView = ((rect.top + (rect.height * 0.7)) <= windowHeight) && ((rect.top + rect.height) >= 0);
          const horInView = (rect.left <= windowWidth) && ((rect.left + rect.width) >= 0);

          return (vertInView && horInView);
        }

        const updateElementsPositions = (element) => {
          const rect = element.getBoundingClientRect();
          const windowHeight = (window.innerHeight || document.documentElement.clientHeight);
          let offset = windowHeight - (rect.top + (rect.height * 0.7));
          let parallaxCoef;

          switch (true) {
            case rect.width >= 1360:
              parallaxCoef = 0.06;
              break;

            case rect.width >= 688:
              parallaxCoef = 0.02;
              break;

            default:
              parallaxCoef = 0.01;
          }

          element.style.backgroundPosition = `center calc(50% - ${offset * parallaxCoef}px`;

        };

        const listener = () => {
          if (isInViewport(contentFeatureModule)) {
            updateElementsPositions(contentFeatureModule);
          }
        };

        window.addEventListener('DOMContentLoaded', listener);
        window.addEventListener('scroll', listener);
        window.addEventListener('resize', listener);
      })
    }
  }
})(jQuery, Drupal, _);
