import Swiper, {Navigation, Pagination, Scrollbar, A11y} from 'swiper';

(function($, _, Drupal){
  Drupal.behaviors.socialFeed = {
    attach(context) {
      // init swiper

      $(context).find('.social-feed').once('socialFeed').each(function(){
        Swiper.use([Navigation, Pagination, Scrollbar, A11y]);

        $('.social-feed-swiper-container', this).each(function(){
          const swiper = new Swiper(this, {
            slidesPerView: 'auto',
            spaceBetween: 20,
            slidesOffsetBefore: 50,
            watchSlidesVisibility: true,
            observer: true,
            observeParents: true,
            keyboard: {
              enabled: true
            },
            a11y: {
              enabled: true,
              prevSlideMessage: Drupal.t('Previous Slide'),
              nextSlideMessage: Drupal.t('Next Slide')
            },
            navigation: {
              nextEl: '.swiper-button-next',
              prevEl: '.swiper-button-prev'
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

          $(window).on('resize', _.debounce(() => {swiper.scrollbar.updateSize();}, 200));
        });
      })
    },
  };
})(jQuery, _, Drupal);
