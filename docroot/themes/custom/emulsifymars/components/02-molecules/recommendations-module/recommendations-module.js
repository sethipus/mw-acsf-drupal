import Swiper, { Navigation, Pagination, Scrollbar } from "swiper";

Drupal.behaviors.recommendationsCorousel = {
  attach(context) {
    // init swiper
    Swiper.use([Navigation, Pagination, Scrollbar]);
    const swiper = new Swiper(".recommendations-swiper-container", {
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
        }
      } else if (window.innerWidth > 768 && window.innerWidth < 1440) {
        if (swiper.slides.length <= 2) {
          swiper.navigation.nextEl.className += " hide-arrow";
        }
      } else {
        if (swiper.slides.length <= 1) {
          swiper.navigation.nextEl.className += " hide-arrow";
        }
      }
    };

    window.addEventListener("resize", checkSlides);
    window.addEventListener("load", checkSlides);
    window.addEventListener("load", productCardListener);
    swiper.navigation.nextEl.addEventListener("click", productCardListener);
    swiper.navigation.prevEl.addEventListener("click", productCardListener);
  },
};
