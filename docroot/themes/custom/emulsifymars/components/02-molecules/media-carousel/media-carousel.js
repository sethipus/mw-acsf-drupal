import Swiper, {Navigation, Pagination, Scrollbar} from 'swiper';
import 'swiper/swiper-bundle.css';

Drupal.behaviors.carouselFeed = {
  attach(context) {
    // init swiper
    Swiper.use([Navigation, Pagination, Scrollbar]);
    const carousel = new Swiper('.carousel-container', {
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      scrollbar: {
        el: '.swiper-scrollbar'
      },
      spaceBetween: 0,
      mousewheel: true,
      keyboard: true,
    });
  },
};
