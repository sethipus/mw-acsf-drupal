Drupal.behaviors.productContentPairUp = {
  attach(context) {
    if (context.getElementById('product-content-pair-up') == null) {
      return;
    }

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
      let parallaxTops = {};

      switch (true) {
        case rect.width >= 1440:
          parallaxTops.svgAsset = {minTop: 80, initTop: 360};
          parallaxTops.supportiveCard = {minTop: 75, initTop: 185};
          break;

        case rect.width >= 768:
          parallaxTops.svgAsset = {minTop: 365, initTop: 640};
          parallaxTops.supportiveCard = {minTop: 361, initTop: 480};
          break;

        default:
          parallaxTops.svgAsset = {minTop: 630, initTop: 840};
          parallaxTops.supportiveCard = {minTop: 306, initTop: 420};

      }

      element.querySelector('.lead-card').style.backgroundPosition = `center ${- (offset * 0.03)}px`;
      element.querySelector('.svg-asset').style.top = Math.max(parallaxTops.svgAsset.minTop, parallaxTops.svgAsset.initTop - offset * 0.3) + 'px';
      element.querySelector('.supportive-card').style.top = Math.max(parallaxTops.supportiveCard.minTop, parallaxTops.supportiveCard.initTop - offset * 0.15) + 'px';

    };

    const listener = () => {
      const parallaxParentElement = context.getElementById('product-content-pair-up');

      if (isInViewport(parallaxParentElement)) {
        updateElementsPositions(parallaxParentElement);
      }
    };

    window.addEventListener('load', listener);
    window.addEventListener('scroll', listener);
    window.addEventListener('resize', listener);
  }
}
