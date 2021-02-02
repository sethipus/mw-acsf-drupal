import Swiper, {Navigation, Pagination, Scrollbar} from 'swiper';

(function($, _, Drupal){
  Drupal.behaviors.socialFeed = {
    attach(context) {
      // init swiper

      $(context).find('.social-feed').once('socialFeed').each(function(){
        const $socialFeedComponent = $(this);
        Swiper.use([Navigation, Pagination, Scrollbar]);

        $('.social-feed-swiper-container', this).each(function(){
          const $nextEl = $socialFeedComponent.find(".swiper-button-next").first();
          const nextEl = (typeof $nextEl[0]) !== "undefined" ? $nextEl[0] : null;
          const $prevEl = $socialFeedComponent.find(".swiper-button-prev").first();
          const prevEl = (typeof $prevEl[0]) !== "undefined" ? $prevEl[0] : null;
          const $scrollbar = $socialFeedComponent.find(".swiper-scrollbar").first();
          const scrollbar = (typeof $scrollbar[0]) !== "undefined" ? $scrollbar[0] : null;

          const swiper = new Swiper(this, {
            slidesPerView: 'auto',
            spaceBetween: 20,
            slidesOffsetBefore: 50,
            navigation: {
              nextEl: nextEl,
              prevEl: prevEl,
            },
            scrollbar: {
              el: scrollbar,
              draggable: true,
              dragSize: 200
            },
            breakpoints: {
              768: {
                spaceBetween: 30
              }
            }
          });

          $(window).on('resize', _.debounce(() => {swiper.scrollbar.updateSize()}, 200));
        });
      })
    },
  };
})(jQuery, _, Drupal);
