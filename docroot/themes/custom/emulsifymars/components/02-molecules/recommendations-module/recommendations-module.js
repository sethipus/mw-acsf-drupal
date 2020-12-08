import Swiper, {Navigation, Pagination, Scrollbar} from 'swiper';

(function ($, _, Drupal){
  Drupal.behaviors.recommendationsCarousel = {
    attach(context) {

      $(context).find('.recommendations').once('recommendationsCarousel').each(function(){
         // init swiper
        Swiper.use([Navigation, Pagination, Scrollbar]);

        $('.recommendations-swiper-container', this).each(function(){
          const swiper = new Swiper(this, {
            slidesPerView: "auto",
            spaceBetween: 20,
            slidesOffsetBefore: 50,
            navigation: {
              nextEl: ".swiper-button-next",
              prevEl: ".swiper-button-prev",
            },
            scrollbar: {
              el: ".swiper-scrollbar",
            },
            breakpoints: {
              768: {
                spaceBetween: 30,
              },
            },
          });

          const isInViewport = (element) => {
            const rect = element.getBoundingClientRect();
      
            const windowHeight =
              window.innerHeight || document.documentElement.clientHeight;
            const windowWidth =
              window.innerWidth || document.documentElement.clientWidth;
      
            const vertInView =
              rect.top <= windowHeight && rect.top + rect.height >= 0;
            const horInView = rect.left <= windowWidth && rect.left + rect.width >= 0;
      
            return vertInView && horInView;
          };
      
          const productCardListener = () => {
            const productCardList = context.querySelectorAll(".product-card");
      
            productCardList.forEach((productCard) => {
              if (isInViewport(productCard)) {
                productCard.className += " is-in-viewport";
              } else {
                productCard.classList.remove("is-in-viewport");
              }
            });
          };
      
          const checkSlides = () => {
            if (window.innerWidth > 1440) {
              if (swiper.slides.length <= 4) {
                swiper.navigation.nextEl.className += " hide-arrow";
                context.querySelector(".swiper-wrapper").className += " no-carousel"
              } else {
                swiper.navigation.nextEl.classList.remove("hide-arrow");
              }
            } else if (window.innerWidth > 768 && window.innerWidth < 1440) {
                if (swiper.slides.length <= 2) {
                  swiper.navigation.nextEl.className += " hide-arrow";
                } else {
                  swiper.navigation.nextEl.classList.remove("hide-arrow");
                }
            } else {
                if (swiper.slides.length <= 1) {
                  swiper.navigation.nextEl.className += " hide-arrow";
                } else {
                  swiper.navigation.nextEl.classList.remove("hide-arrow");
                }
            }
          };

          $(window).on("resize", _.debounce(() => {checkSlides()}, 200 ));
          $(window).on("load", checkSlides);
          $(window).on("load", productCardListener);
          $(".swiper-button-next", this).once('recommendationsCarousel').on("click", productCardListener);
          $(".swiper-button-prev", this).once('recommendationsCarousel').on("click", productCardListener);
        });
      })
    },
  };
})(jQuery, _, Drupal)
