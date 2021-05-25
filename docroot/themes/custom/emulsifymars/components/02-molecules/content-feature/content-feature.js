(function($, Drupal, _){
  Drupal.behaviors.contentFeature = {
    attach: function (context) {
      $(context).find('.content-feature').once('contentFeature').each(function () {
        const contentFeatureModule = this;
        const bgUrl = contentFeatureModule.getAttribute('data-bgurl');
        const parallaxCoef = 1;

        const isInViewport = element => {
          const boundingRect = element.getBoundingClientRect();

          const windowHeight = window.innerHeight || document.documentElement.clientHeight;
          const windowWidth = window.innerWidth || document.documentElement.clientWidth;

          const vertInView = boundingRect.top <= windowHeight && (boundingRect.top + boundingRect.height) >= 0;
          const horInView = boundingRect.left <= windowWidth && (boundingRect.left + boundingRect.width) >= 0;

          return vertInView && horInView;
        }

        const updateElementsPositions = (element) => {
          const windowHeight = (window.innerHeight || document.documentElement.clientHeight);
          const windowMiddle = windowHeight / 2;

          const boundingRect = element.getBoundingClientRect();
          const containerHeight = boundingRect.height;
          const containerMiddle = boundingRect.top + containerHeight / 2;

          const currentOffset = windowMiddle - containerMiddle;
          const maxOffset = (windowHeight + containerHeight) / 2;
          const parallaxEffectPercentage = currentOffset / maxOffset;

          const parallaxOverflow = containerHeight * parallaxCoef;
          const bgPositionOffset = parallaxOverflow / 2 * parallaxEffectPercentage;
          if(!element.style.backgroundImage)
          {
              contentFeatureModule.setAttribute("style", "background-image: url(" + bgUrl + ");");         
          }
          element.style.backgroundPosition = `50% calc(50% - ${bgPositionOffset}px`;
        };

        const scrollListener = () => {
          if (isInViewport(contentFeatureModule)) {     
            updateElementsPositions(contentFeatureModule);
          }
        };

        window.addEventListener('scroll', _.throttle(scrollListener, 33));
      })
    }
  }
})(jQuery, Drupal, _);