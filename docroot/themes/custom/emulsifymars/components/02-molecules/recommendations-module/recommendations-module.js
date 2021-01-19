import Swiper, {Navigation, Pagination, Scrollbar} from 'swiper';

(function ($, _, Drupal){
  Drupal.behaviors.recommendationsCarousel = {
    attach(context) {

      $(context).find('.recommendations').once('recommendationsCarousel').each(function(){
        const $recommendationContainer = $(this);
         // init swiper
        Swiper.use([Navigation, Pagination, Scrollbar]);

        $('.recommendations-swiper-container', this).each(function(){
          const swiper = new Swiper(this, {
            slidesPerView: "auto",
            spaceBetween: 20,
            slidesOffsetBefore: 20,
            noSwipingClass: "swiper-no-swiping",
            navigation: {
              nextEl: ".swiper-button-next",
              prevEl: ".swiper-button-prev",
            },
            scrollbar: {
              el: ".swiper-scrollbar",
            },
            breakpoints: {
              768: {
                slidesOffsetBefore: 40,
              },
              1440: {
                spaceBetween: 30,
                slidesOffsetBefore: 39,
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
            let screenWidth = window.innerWidth;
            let slidesCount = swiper.slides.length;

            if (  ((screenWidth > 1440) && (slidesCount <= 4)) || // Wide Screen View && equal or less then 4 slides
                  ((screenWidth > 1150 && screenWidth <= 1440) && (slidesCount <= 3)) || // Desktop View && equal or less then 3 slides
                  ((screenWidth > 768 && screenWidth <= 1150) && (slidesCount <= 2)) || // Tablet View && equal or less then 2 slides
                  (slidesCount <= 1)) { // Slides count equal or less then 1
              lockCarousel();
            } else {
              unlockCarousel();
            }
          };

          const lockCarousel = () => {
            swiper.navigation.nextEl.className += " hide-arrow";
            swiper.navigation.prevEl.className += " hide-arrow";
            swiper.setTranslate(0);
            $(".swiper-wrapper", $recommendationContainer).addClass("no-carousel swiper-no-swiping");
            swiper.update();
          }

          const unlockCarousel = () => {
            swiper.navigation.nextEl.classList.remove("hide-arrow");
            swiper.navigation.prevEl.classList.remove("hide-arrow");
            $(".swiper-wrapper", $recommendationContainer).removeClass("no-carousel swiper-no-swiping");
            swiper.update();
          };

          $(window).on("resize", _.debounce(checkSlides, 200));
          $(window).on("load", checkSlides);
          $(window).on("load", productCardListener);
          $(".swiper-button-next", this).once('recommendationsCarousel').on("click", productCardListener);
          $(".swiper-button-prev", this).once('recommendationsCarousel').on("click", productCardListener);
        });
      })
    },
  };
})(jQuery, _, Drupal)
