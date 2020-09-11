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
        e.preventDefault();
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

    //bubbles
    function scrollEffects() {
      const pdp_bubble_1_top = context.getElementById('pdp-hero__bubble_1').getBoundingClientRect().top;
      const pdp_bubble_2_top = context.getElementById('pdp-hero__bubble_2').getBoundingClientRect().top;
      const pdp_bubble_3_top = context.getElementById('pdp-hero__bubble_3').getBoundingClientRect().top;

      const pdp_sticky_nav_top = context.getElementById('sticky-nav-top');
      const pdp_sticky_nav_bottom = context.getElementById('sticky-nav-bottom');
      const pdp_main_image = context.getElementById('pdp-hero-main-image');
      const pdp_nutrition = context.getElementById('section-nutrition');
      const pdp_allergen = context.getElementById('section-allergen');
      
      const pdp_main_image_top = pdp_main_image && pdp_main_image.getBoundingClientRect().top;
      const pdp_nutrition_top = pdp_nutrition && pdp_nutrition.getBoundingClientRect().top;
      const pdp_allergen_bottom = pdp_allergen && pdp_allergen.getBoundingClientRect().bottom;

      window.addEventListener('scroll', () => {
        const pdp_bubble_1 = context.getElementById('pdp-hero__bubble_1');
        const pdp_bubble_2 = context.getElementById('pdp-hero__bubble_2');
        const pdp_bubble_3 = context.getElementById('pdp-hero__bubble_3');
        var offset = window.pageYOffset;

        pdp_bubble_1.style.top = `${pdp_bubble_1_top - (offset * .75)}px`;
        pdp_bubble_2.style.top = `${pdp_bubble_2_top - (offset * .75)}px`;
        pdp_bubble_3.style.top = `${pdp_bubble_3_top - (offset * .75)}px`;

        offset > pdp_main_image_top ? pdp_sticky_nav_top.classList.add('show-nav') : pdp_sticky_nav_top.classList.remove('show-nav');
        offset > pdp_nutrition_top ? pdp_sticky_nav_bottom.classList.add('hide-sections') : pdp_sticky_nav_bottom.classList.remove('hide-sections');
        offset > pdp_allergen_bottom ? pdp_sticky_nav_bottom.classList.add('hide-nav') : pdp_sticky_nav_bottom.classList.remove('hide-nav');
      })
    };
    //ToDo: refactor for correct values after slider initialized (only for 1st time)
    setTimeout(scrollEffects, 200);
  },
};
