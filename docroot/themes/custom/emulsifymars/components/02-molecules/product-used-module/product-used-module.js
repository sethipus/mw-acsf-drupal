import Swiper, {Navigation, Pagination, Scrollbar} from 'swiper';

(function ($, Drupal){
  Drupal.behaviors.productUsedCarousel = {
    attach(context) {
      $(context).find('.product-used').once('productUsedCarousel').each(function(){

        if ($('.swiper-wrapper', this).children().length < 2) {
          return;
        }

        // init swiper
        Swiper.use([Navigation, Pagination, Scrollbar]);

        $('.product-used-swiper-container', this).each(function(){
          const swiper = new Swiper(this, {
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
        });
      });
    }
  };
})(jQuery, Drupal);
