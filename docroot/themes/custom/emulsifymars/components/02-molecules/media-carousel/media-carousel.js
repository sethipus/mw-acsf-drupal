import Swiper, {Navigation, Pagination, Scrollbar, Thumbs, EffectFade} from 'swiper';

(function($, Drupal) {
  Drupal.behaviors.carouselFeed = {
    attach(context) {
      $(context).find('.carousel').once('carouselFeed').each(function(){
        // init swiper
        Swiper.use([Navigation, Pagination, Scrollbar, Thumbs, EffectFade]);
        const $descriptionSwiperContainer = $('.carousel-description-container', this);
        const $carouselSwiperContainer = $('.carousel-container', this);

        const carouselContent = new Swiper($descriptionSwiperContainer[0], {
          spaceBetween: 0,
          effect: 'fade',
          slidesPerView: 1,
          watchSlidesVisibility: true,
          watchSlidesProgress: true,
        });

        const $nextEl = $carouselSwiperContainer.find(".swiper-button-next").first();
        const nextEl = (typeof $nextEl[0]) !== "undefined" ? $nextEl[0] : null;
        const $prevEl = $carouselSwiperContainer.find(".swiper-button-prev").first();
        const prevEl = (typeof $prevEl[0]) !== "undefined" ? $prevEl[0] : null;
        const $scrollbar = $carouselSwiperContainer.find(".swiper-scrollbar").first();
        const scrollbar = (typeof $scrollbar[0]) !== "undefined" ? $scrollbar[0] : null;

        const carousel = new Swiper($carouselSwiperContainer[0], {
          spaceBetween: 0,
          mousewheel: true,
          keyboard: true,
          navigation: {
            nextEl: nextEl,
            prevEl: prevEl,
          },
          scrollbar: {
            el: scrollbar
          },
          thumbs: {
            swiper: carouselContent
          },
        });
      })
    },
  };
})(jQuery, Drupal);
