import Swiper, {Navigation, Pagination, Scrollbar} from 'swiper';

(function ($, _, Drupal) {
  Drupal.behaviors.recommendationsCarousel = {
    attach(context) {

      $(context).find('.recommendations').once('recommendationsCarousel').each(function () {
        const $recommendationContainer = $(this);
        // init swiper
        Swiper.use([Navigation, Pagination, Scrollbar]);

        $recommendationContainer.find('.recommendations-swiper-container').each(function () {
          const nextEl = $recommendationContainer.find(".swiper-button-next").first()[0];
          const prevEl = $recommendationContainer.find(".swiper-button-prev").first()[0];
          const scrollbar = $recommendationContainer.find(".swiper-scrollbar").first()[0];
          const swiper = new Swiper(this, {
            slidesPerView: "auto",
            spaceBetween: 20,
            slidesOffsetBefore: 20,
            noSwipingClass: "swiper-no-swiping",
            watchOverflow: true,
            navigation: {
              nextEl: nextEl,
              prevEl: prevEl,
            },
            scrollbar: {
              el: scrollbar,
            },
            breakpoints: {
              768: {
                spaceBetween: 20,
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

            if (
              (screenWidth >= 1440 && slidesCount <= 4) ||
              (screenWidth >= 1074 && slidesCount <= 3) ||
              (screenWidth >= 768 && slidesCount <= 2) ||
              (slidesCount <= 1)
            ) {
              lockCarousel();
            } else {
              unlockCarousel();
            }
          };

          const lockCarousel = () => {
            swiper.navigation.nextEl.className += " hide-arrow";
            swiper.navigation.prevEl.className += " hide-arrow";
            $(".swiper-wrapper", $recommendationContainer).addClass("no-carousel swiper-no-swiping");
            swiper.update();
            swiper.setTranslate(0);
            swiper.pagination.update()
          }

          const unlockCarousel = () => {
            swiper.navigation.nextEl.classList.remove("hide-arrow");
            swiper.navigation.prevEl.classList.remove("hide-arrow");
            $(".swiper-wrapper", $recommendationContainer).removeClass("no-carousel swiper-no-swiping");
            swiper.update();
            swiper.slideTo(0, 0);
            swiper.pagination.update()
          };

          $(window).on("resize", _.debounce(checkSlides, 100));
          $(window).on("load", checkSlides);
          $(window).on("load", productCardListener);
          $(".swiper-button-next", this).once('recommendationsCarousel').on("click", productCardListener);
          $(".swiper-button-prev", this).once('recommendationsCarousel').on("click", productCardListener);
        });
      });
    },
  };
})(jQuery, _, Drupal);
