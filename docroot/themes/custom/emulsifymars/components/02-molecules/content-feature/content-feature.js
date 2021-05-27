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

        const updateBGSize = element => {	
          const image = document.createElement('img');	
          image.onload = () => {	
            const imageWidth = image.naturalWidth;	
            const imageHeight = image.naturalHeight;	
            const imageAspect = imageWidth / imageHeight;	

            const containerHeight = element.clientHeight;	
            const containerWidth = element.clientWidth;	

            // The target dimensions what we should cover with the bg image.	
            const parallaxCorrection = containerHeight * parallaxCoef;	
            const targetHeight = containerHeight + parallaxCorrection;	
            const targetWidth = containerWidth;	
            const targetAspect = targetWidth / targetHeight;	

            let resizedHeight;	
            let resizePercentage;	

            if (targetAspect < imageAspect) {	
              //Resize based on height.	
              resizePercentage = targetHeight / imageHeight;	
            }	
            else {	
              //Resize based on width.	
              resizePercentage = targetWidth / imageWidth;	
            }	

            resizedHeight = imageHeight * resizePercentage;	

            element.style.backgroundSize = `auto ${resizedHeight}px`;	
          };	
          image.src = bgUrl;	
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


        const resizeListener = () => {	
          if (isInViewport(contentFeatureModule)) {	
            updateBGSize(contentFeatureModule);	
          }	
        };	

        const documentReadyListener = () => {	
          updateBGSize(contentFeatureModule);	
        }	

        window.addEventListener('DOMContentLoaded', documentReadyListener);	
        window.addEventListener('scroll', _.throttle(scrollListener, 33));
        window.addEventListener('resize', _.throttle(resizeListener, 33));
      })
    }
  }
})(jQuery, Drupal, _);
