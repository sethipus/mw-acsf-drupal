import Swiper, {Navigation, Pagination, Scrollbar} from 'swiper';

(function($, _, Drupal){
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
          el: '.swiper-scrollbar',
          draggable: true,
          dragSize: 200
        },
        breakpoints: {
          768: {
            spaceBetween: 30
          }
        }
      });

      $(window).once('socialFeed').on('resize', _.debounce(200, e => {swiper.scrollbar.updateSize()}));
    },
  };

})(jQuery, _, Drupal);
