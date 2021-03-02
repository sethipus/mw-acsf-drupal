import Swiper, {Navigation, Pagination, Scrollbar} from 'swiper';

(function($, Drupal){
  Drupal.behaviors.pdpMultipackCorousel = {
    attach(context) {

      $(context).find('.pdp-multipack').once('pdpMultipackCorousel').each(function(){
        // init swiper
        Swiper.use([Navigation, Pagination, Scrollbar]);

        const multipackSwiper = $('.pdp-multipack-swiper-container', this);

        const swiper = new Swiper(multipackSwiper[0], {
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
        });

        //see all functionality for multipack swiper
        const seeAllButton = $('.multipack-see-all');

        seeAllButton.on('click', (event) => {
        event.preventDefault();
        multipackSwiper.addClass('expanded');
        swiper.update();
      });
      });
    },
  };
})(jQuery, Drupal);
