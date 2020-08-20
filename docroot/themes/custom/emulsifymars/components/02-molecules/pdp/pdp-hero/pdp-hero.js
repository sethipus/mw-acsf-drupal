import Swiper, {Autoplay, Pagination} from 'swiper';

Drupal.behaviors.pdpHero = {
  attach(context) {
    // init swiper
    Swiper.use([Autoplay, Pagination]);
    const swiper = new Swiper('.pdp-hero-swiper-container', {
      autoplay: true,
      loop: true,
      direction: 'vertical',
      slidesPerView: 1,
      pagination: {
        el: '.swiper-pagination',
        type: 'bullets',
      },
    });

    //size control
    function updateSizeSlider(event, sizeId) {
      event.preventDefault();
      let oldSize = document.querySelector(`[data-size-selected="true"]`);
      let newSize = document.querySelector(`[data-size-id="${sizeId}"]`);
      let oldSlider = document.querySelector(`[data-slider-size-active="true"]`);
      let newSlider = document.querySelector(`[data-slider-size-id="${sizeId}"]`);
      oldSize.dataset.sizeSelected = false;
      newSize.dataset.sizeSelected = true;
      oldSlider.dataset.sliderSizeActive = false;
      newSlider.dataset.sliderSizeActive = true;
    };

    var sizeElements = document.querySelectorAll('[data-size-id]');
    sizeElements.forEach((item) => {
      item.addEventListener('click', e => updateSizeSlider(e, item.dataset.sizeId), false);
    })
  },
};
