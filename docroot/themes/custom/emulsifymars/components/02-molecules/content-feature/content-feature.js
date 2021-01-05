(function($, Drupal, _){
  Drupal.behaviors.contentFeature = {
    attach(context) {
      $(context).find('.content-feature').once('contentFeature').each(function() {
        const contentFeatureModule = this;
        const bgUrl = contentFeatureModule.getAttribute('data-bgurl');
        const parallaxCoef = 0.2;

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
          element.style.backgroundPosition = `center calc(50% - ${offset * parallaxCoef}px`;
        };

        const updateBGSize = element => {
          const containerWidth = element.clientWidth;
          const containerHeight = element.clientHeight;

          const image = document.createElement('img');
          image.src = bgUrl;
          image.onload = () => {
            console.log({
              'containerWidth': containerWidth,
              'containerHeight': containerHeight,
              'imageWidth': image.naturalWidth,
              'imageHeight': image.naturalHeight
            })
          };
        }

        const scrollResizeListener = () => {
          if (isInViewport(contentFeatureModule)) {
            updateElementsPositions(contentFeatureModule);
          }
        };

        const documentReadyListener = () => {
          updateBGSize(contentFeatureModule);
        }

        window.addEventListener('DOMContentLoaded', documentReadyListener);
        window.addEventListener('scroll', scrollResizeListener);
        window.addEventListener('resize', scrollResizeListener);
      })
    }
  }
})(jQuery, Drupal, _);
