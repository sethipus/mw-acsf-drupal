import Swiper, {Navigation, Pagination, Scrollbar} from 'swiper';

Drupal.behaviors.socialFeed = {
  attach(context) {
    // init swiper
    Swiper.use([Navigation, Pagination, Scrollbar]);
    const swiper = new Swiper('.social-feed-swiper-container', {
      slidesPerView: 'auto',
      spaceBetween: 20,
      slidesOffsetBefore: 50,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      scrollbar: {
        el: '.swiper-scrollbar'
      },
      breakpoints: {
        768: {
          spaceBetween: 30
        }
      }
    });
  },
};
