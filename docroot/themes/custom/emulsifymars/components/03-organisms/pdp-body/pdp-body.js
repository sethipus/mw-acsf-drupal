import Swiper, {Autoplay, Pagination} from 'swiper';

(function($) {
  Drupal.behaviors.pdpBody = {
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
            clickable: true,
          },
        });

        let swiperControl = document.querySelector(`[data-swiper-index="${index}"] ~ .swiper-control`);
        $(swiperControl, context).once('pdpBody').on('click', (e) => {
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
        context.querySelector(`[data-pdp-size-active="true"]`).dataset.pdpSizeActive = false;
        context.querySelector(`[data-pdp-size-id="${sizeId}"]`).dataset.pdpSizeActive = true;

        let swiperIndex = context.querySelector(`[data-pdp-size-active="true"] .pdp-hero-swiper-container`).dataset.swiperIndex;
        swiperInstances[swiperIndex].update();
        swiperInstances[swiperIndex].autoplay.run();
        let swiperButtonPlay = context.querySelector('.swiper-control-play');
        swiperButtonPlay && swiperButtonPlay.classList.remove('swiper-control-play');

        onScrollEffects();
      }

      var sizeElements = context.querySelectorAll('[data-size-id]');
      sizeElements.forEach((item) => {
        item.addEventListener('click', e => updateSizeSlider(e, item.dataset.sizeId), false);
      });

      //scroll effects: bubbles, section-select and WTB
      function onScrollEffects() {
        const pdp_size_id = context.querySelector('[data-pdp-size-active="true"]').dataset.pdpSizeId;
        
        const pdp_bubble_1 = context.getElementById(`pdp-hero__bubble_1-${pdp_size_id}`);
        const pdp_bubble_2 = context.getElementById(`pdp-hero__bubble_2-${pdp_size_id}`);
        const pdp_bubble_3 = context.getElementById(`pdp-hero__bubble_3-${pdp_size_id}`);
        const pdp_bubble_1_top = $(`#pdp-hero__bubble_1-${pdp_size_id}`, context).offset().top;
        const pdp_bubble_2_top = $(`#pdp-hero__bubble_2-${pdp_size_id}`, context).offset().top;
        const pdp_bubble_3_top = $(`#pdp-hero__bubble_3-${pdp_size_id}`, context).offset().top;

        const pdp_section = $(`[data-pdp-size-id="${pdp_size_id}"]`, context);
        const pdp_hero = $(`#pdp-hero-${pdp_size_id}`, context);
        const pdp_main_image = $(`#pdp-hero-main-image-${pdp_size_id}`, context);
        const pdp_sticky_nav_top = context.getElementById(`sticky-nav-top-${pdp_size_id}`);
        const pdp_sticky_nav_bottom = context.getElementById(`sticky-nav-bottom-${pdp_size_id}`);
        const pdp_wtb = context.getElementById(`where-to-buy-${pdp_size_id}`);

        const pdp_main_image_top = pdp_main_image.offset().top;
        const pdp_hero_bottom = pdp_hero.offset().top + pdp_hero.outerHeight();
        const pdp_section_bottom = pdp_section.offset().top + pdp_section.outerHeight();

        var scrollEventListener = function() {
          var offset = window.pageYOffset;
          pdp_bubble_1.style.top = `${pdp_bubble_1_top - (offset * .75)}px`;
          pdp_bubble_2.style.top = `${pdp_bubble_2_top - (offset * .75)}px`;
          pdp_bubble_3.style.top = `${pdp_bubble_3_top - (offset * .75)}px`;

          offset > pdp_main_image_top ? pdp_sticky_nav_top.classList.add('nav--show') : pdp_sticky_nav_top.classList.remove('nav--show');
          offset > pdp_hero_bottom ? pdp_sticky_nav_bottom.classList.add('sections--hide') : pdp_sticky_nav_bottom.classList.remove('sections--hide');
          offset > pdp_section_bottom ? pdp_wtb.classList.add('where-to-buy--hide') : pdp_wtb.classList.remove('where-to-buy--hide');
        }

        window.removeEventListener('scroll', scrollEventListener);
        window.addEventListener('scroll', scrollEventListener);
      };

      //ToDo: refactor for correct values after slider initialized (only for 1st time)
      setTimeout(onScrollEffects, 200);
    },
  }
})(jQuery);
