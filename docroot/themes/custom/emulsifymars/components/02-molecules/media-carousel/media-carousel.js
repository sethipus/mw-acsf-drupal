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

        const carousel = new Swiper($carouselSwiperContainer[0], {
          spaceBetween: 0,
          mousewheel: true,
          keyboard: true,
          navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
          },
          scrollbar: {
            el: '.swiper-scrollbar'
          },
          thumbs: {
            swiper: carouselContent
          },
        });
      })
    },
  };
})(jQuery, Drupal);
