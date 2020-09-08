import Swiper, {Navigation, Pagination, Scrollbar} from 'swiper';

Drupal.behaviors.productUsedCorousel = {
  attach(context) {
    // init swiper
    Swiper.use([Navigation, Pagination, Scrollbar]);

    const swiper = new Swiper('.product-used-swiper-container', {
      slidesPerView: 'auto',
      spaceBetween: 20,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      scrollbar: {
        el: '.swiper-scrollbar'
      },
      breakpoints: {
        1440: {
          direction: 'vertical'
        }
      }
    });
  },
};
