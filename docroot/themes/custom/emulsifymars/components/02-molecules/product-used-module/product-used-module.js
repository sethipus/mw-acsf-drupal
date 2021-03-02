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
          const $swiperContainer = $(this);

          const $nextEl = $swiperContainer.find(".swiper-button-next").first();
          const nextEl = (typeof $nextEl[0]) !== "undefined" ? $nextEl[0] : null;
          const $prevEl = $swiperContainer.find(".swiper-button-prev").first();
          const prevEl = (typeof $prevEl[0]) !== "undefined" ? $prevEl[0] : null;
          const $scrollbar = $swiperContainer.find(".swiper-scrollbar").first();
          const scrollbar = (typeof $scrollbar[0]) !== "undefined" ? $scrollbar[0] : null;

          const swiper = new Swiper(this, {
            slidesPerView: 'auto',
            spaceBetween: 20,
            navigation: {
              nextEl: nextEl,
              prevEl: prevEl,
            },
            scrollbar: {
              el: scrollbar
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
