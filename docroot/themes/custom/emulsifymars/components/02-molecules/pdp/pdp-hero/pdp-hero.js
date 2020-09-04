import Swiper, {Autoplay, Pagination} from 'swiper';

Drupal.behaviors.pdpHero = {
  attach(context) {
    // init swiper
    Swiper.use([Autoplay, Pagination]);
    var swiperInstances = [];
    var sliderContainers = document.querySelectorAll('.pdp-hero-swiper-container');
    sliderContainers.forEach((sliderContainer, index) => {
      sliderContainer.dataset.swiperIndex = index;
      swiperInstances[index] = new Swiper(`[data-swiper-index="${index}"]`, {
        autoplay: {
          delay: 3000,
        },
        loop: true,
        direction: 'vertical',
        slidesPerView: 1,
        pagination: {
          el: `[data-swiper-index="${index}"] + .swiper-pagination`,
          type: 'bullets',
        },
      });

      let swiperControl = document.querySelector(`[data-swiper-index="${index}"] ~ .swiper-control`);
      swiperControl.addEventListener('click', (e) => {
        event.preventDefault();
        if (swiperInstances[index].autoplay.running) {
          swiperInstances[index].autoplay.stop();
          swiperControl.classList.toggle('swiper-control-play');
        } else {
          swiperInstances[index].autoplay.start();
          swiperControl.classList.toggle('swiper-control-play');
        }
      });
    });

    //size control
    function updateSizeSlider(event, sizeId) {
      event.preventDefault();
      document.querySelector(`[data-size-selected="true"]`).dataset.sizeSelected = false;
      document.querySelector(`[data-size-id="${sizeId}"]`).dataset.sizeSelected = true;
      document.querySelector(`[data-slider-size-active="true"]`).dataset.sliderSizeActive = false;
      document.querySelector(`[data-slider-size-id="${sizeId}"]`).dataset.sliderSizeActive = true;
      document.querySelector(`[data-nutrition-size-active="true"]`).dataset.nutritionSizeActive = false;
      document.querySelector(`[data-nutrition-size-id="${sizeId}"]`).dataset.nutritionSizeActive = true;
      document.querySelector(`[data-allergen-size-active="true"]`).dataset.allergenSizeActive = false;
      document.querySelector(`[data-allergen-size-id="${sizeId}"]`).dataset.allergenSizeActive = true;

      let swiperIndex = document.querySelector(`[data-slider-size-active="true"] > .pdp-hero-swiper-container`).dataset.swiperIndex;
      swiperInstances[swiperIndex].update();
      swiperInstances[swiperIndex].autoplay.run();
      let swiperButtonPlay = document.querySelector('.swiper-control-play');
      swiperButtonPlay && swiperButtonPlay.classList.remove('swiper-control-play');
    }

    var sizeElements = document.querySelectorAll('[data-size-id]');
    sizeElements.forEach((item) => {
      item.addEventListener('click', e => updateSizeSlider(e, item.dataset.sizeId), false);
    });
  },
};
