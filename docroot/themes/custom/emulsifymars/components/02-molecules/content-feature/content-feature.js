(function($, Drupal, _){
  Drupal.behaviors.contentFeature = {
    attach(context) {
      $(context)
        .find('.content-feature__bg-image')
        .each(function() {
          const parallaxContainer = this;
          const parallaxImage = this.querySelector('.parallax-image');
          let parallaxCoef = 1;
          let parallaxImageScale = 1; // dynamically calculated based on parallaxCoef,windowHeight,containerHeight
          let isParallaxActive = false;

          const isInViewport = (element) => {
            const boundingRect = element.getBoundingClientRect();

            const windowHeight =
              window.innerHeight || document.documentElement.clientHeight;
            const windowWidth =
              window.innerWidth || document.documentElement.clientWidth;

            const vertInView =
              boundingRect.top <= windowHeight &&
              boundingRect.top + boundingRect.height >= 0;
            const horInView =
              boundingRect.left <= windowWidth &&
              boundingRect.left + boundingRect.width >= 0;

            return vertInView && horInView;
          };

          const updateElementPosition = () => {
            if (isInViewport(parallaxContainer)) {
              const windowHeight =
                window.innerHeight || document.documentElement.clientHeight;
              const windowMiddle = windowHeight / 2;

              const boundingRect = parallaxContainer.getBoundingClientRect();
              const containerHeight = boundingRect.height;
              const containerMiddle = boundingRect.top + containerHeight / 2;

              const currentOffset = windowMiddle - containerMiddle;
              const maxOffset = (windowHeight + containerHeight) / 2;
              const parallaxEffectPercentage = currentOffset / maxOffset;

              if (!isParallaxActive) {
                isParallaxActive = true;
                parallaxImage.style.transform = '';
                const imageHeight = parallaxImage.getBoundingClientRect().height;
                parallaxImageScale = (containerHeight / imageHeight) * (parallaxCoef * (windowHeight - containerHeight) / (windowHeight + containerHeight) + 1);
                parallaxImageScale = (parallaxImageScale < 1) ? 1 : parallaxImageScale;
              }

              const parallaxOverflow = containerHeight * parallaxCoef;
              const positionOffset = (parallaxOverflow / 2) * parallaxEffectPercentage;
              parallaxImage.style.transform = `translateY(${positionOffset}px) scale(${parallaxImageScale})`;
            }
            else {
              isParallaxActive = false;
            }
          };

          const resizeListener = () => {
            isParallaxActive = false;
            updateElementPosition();
          };

          window.addEventListener('scroll', _.throttle(updateElementPosition, 10));
          window.addEventListener('resize', _.throttle(resizeListener, 33));
        });
    },
  };
})(jQuery, Drupal, _);
