import Swiper, {Navigation, Pagination, Scrollbar, Thumbs, EffectFade} from 'swiper';

Drupal.behaviors.carouselFeed = {
  attach(context) {
    // init swiper
    Swiper.use([Navigation, Pagination, Scrollbar, Thumbs, EffectFade]);
    const carouselContent = new Swiper('.carousel-description-container', {
      spaceBetween: 0,
      effect: 'fade',
      slidesPerView: 1,
      watchSlidesVisibility: true,
      watchSlidesProgress: true,
    });
    const carousel = new Swiper('.carousel-container', {
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
  },
};
