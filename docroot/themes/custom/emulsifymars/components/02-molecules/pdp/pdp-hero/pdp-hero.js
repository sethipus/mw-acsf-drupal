import Swiper, {Navigation, Pagination} from 'swiper';
import 'swiper/swiper-bundle.css';

Drupal.behaviors.pdpHero = {
  attach(context) {
    // init swiper
    Swiper.use([Navigation, Pagination]);
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
  },
};
