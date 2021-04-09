(function($, Drupal, _) {
  Drupal.behaviors.parallaxImage = {
    attach(context) {
      $(context)
        .find('.parallax-image')
        .each(function() {
          const parallaxImage = this;
          const parallaxCoef = 1;
          const imageParent = parallaxImage.closest('.article-full-width--parallax__media');

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
            if (isInViewport(parallaxImage)) {
              const windowHeight =
                window.innerHeight || document.documentElement.clientHeight;
              const windowMiddle = windowHeight / 2;

              const boundingRect = parallaxImage.getBoundingClientRect();
              const containerHeight = boundingRect.height;
              const containerMiddle = boundingRect.top + containerHeight / 2;

              const currentOffset = windowMiddle - containerMiddle;
              const maxOffset = (windowHeight + containerHeight) / 2;
              const parallaxEffectPercentage = currentOffset / maxOffset;

              const parallaxOverflow = containerHeight * parallaxCoef;
              const positionOffset =
                (parallaxOverflow / 2) * parallaxEffectPercentage;
              parallaxImage.style.transform = `translateY(${positionOffset}px)`;

              // workaround for fixing top/bottom gaps
              if(positionOffset < 0) {
                imageParent.style.height = (containerHeight + positionOffset) + "px";
                parallaxImage.style.marginTop = 0;
              } else {
                imageParent.style.height = (containerHeight - positionOffset) + "px";
                parallaxImage.style.marginTop = (-positionOffset) + "px";
              }
            }
          };

          window.addEventListener('scroll', _.throttle(updateElementPosition, 33));
          window.addEventListener('resize', _.throttle(updateElementPosition, 33));
        });
    },
  };
})(jQuery, Drupal, _);
