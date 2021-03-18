import Swiper, {Navigation, Pagination, Scrollbar, A11y} from 'swiper';

(function ($, _, Drupal) {
  Drupal.behaviors.recommendationsCarousel = {
    attach(context) {

      $(context).find('.recommendations').once('recommendationsCarousel').each(function () {
        const $recommendationContainer = $(this);
        // init swiper
        Swiper.use([Navigation, Pagination, Scrollbar, A11y]);

        $recommendationContainer.find('.recommendations-swiper-container').each(function () {
          const $nextEl = $recommendationContainer.find(".swiper-button-next").first();
          const nextEl = (typeof $nextEl[0]) !== "undefined" ? $nextEl[0] : null;
          const $prevEl = $recommendationContainer.find(".swiper-button-prev").first();
          const prevEl = (typeof $prevEl[0]) !== "undefined" ? $prevEl[0] : null;
          const $scrollbar = $recommendationContainer.find(".swiper-scrollbar").first();
          const scrollbar = (typeof $scrollbar[0]) !== "undefined" ? $scrollbar[0] : null;

          // START: the same code for
          // recommendations-module.js
          // social-feed.js
          const swiper = new Swiper(this, {
            init: false,
            slidesPerView: "auto",
            spaceBetween: 20,
            slidesOffsetBefore: 20,
            slidesOffsetAfter: 20,
            centerInsufficientSlides: true,
            watchOverflow: true,
            threshold: 5,
            keyboard: {
              enabled: false,
            },
            a11y: {
              enabled: true,
              prevSlideMessage: Drupal.t('Previous Slide'),
              nextSlideMessage: Drupal.t('Next Slide'),
            },
            navigation: {
              nextEl: nextEl,
              prevEl: prevEl,
            },
            scrollbar: {
              el: scrollbar,
              draggable: true,
              dragSize: 88,
            },
            breakpoints: {
              768: {
                spaceBetween: 20,
                slidesOffsetBefore: 40,
                slidesOffsetAfter: 40,
                scrollbar: {
                  dragSize: 140,
                }
              },
              1440: {
                spaceBetween: 30,
                slidesOffsetBefore: 40,
                slidesOffsetAfter: 40,
                scrollbar: {
                  dragSize: 259,
                }
              },
            },
          });

          let isLocked = null,
            slidesOffsetBefore = 0,
            slidesOffsetAfter = 0;

          const adjustCarouselLock = () => {
            if(isLocked !== swiper.isLocked){
              if (isLocked === null) {
                ({slidesOffsetBefore, slidesOffsetAfter} = swiper.params);
              }
              isLocked = swiper.isLocked;
              if(isLocked) {
                swiper.params.slidesOffsetBefore = 0;
                swiper.params.slidesOffsetAfter = 0;
                swiper.params.centerInsufficientSlides = true;
              }
              else {
                swiper.params.slidesOffsetBefore = slidesOffsetBefore;
                swiper.params.slidesOffsetAfter = slidesOffsetAfter;
                swiper.params.centerInsufficientSlides = false;
              }
              swiper.update();
            }
          };

          const adjustInertSlides = _.debounce( () => {
            const setInert = (element) => {
              if(!element.hasAttribute('inert')) {
                element.setAttribute('inert', '');
                element.setAttribute('aria-hidden', 'true');

                const _tabElementsString = ['a','area', 'button', 'input', 'textarea', 'select', 'details','summary', 'iframe', 'object', 'embed', '[tabindex]'];
                const tabElements = element.querySelectorAll(_tabElementsString.join(','));

                tabElements.forEach((elm) => {
                  let tabindexValue = 'none';
                  if(elm.hasAttribute('tabindex')){
                    tabindexValue = elm.getAttribute('tabindex');
                  }
                  elm.setAttribute('data-inert-orig-tabindex', tabindexValue);
                  elm.setAttribute('tabindex', "-1");
                });
              }
            };

            const removeInert = (element) => {
              if(element.hasAttribute('inert')) {
                element.removeAttribute('inert');
                element.removeAttribute('aria-hidden');

                const inertElements = element.querySelectorAll('[data-inert-orig-tabindex]');

                inertElements.forEach((elm) => {
                  let tabindexCurrentValue = elm.getAttribute('tabindex');
                  let tabindexValue = elm.getAttribute('data-inert-orig-tabindex');

                  if (tabindexCurrentValue !== "-1") {
                    tabindexValue = tabindexCurrentValue;
                  }

                  if(tabindexValue === 'none'){
                    elm.removeAttribute('tabindex');
                  }
                  else {
                    elm.setAttribute('tabindex', tabindexValue);
                  }
                  elm.removeAttribute('data-inert-orig-tabindex');
                });
              }
            };

            const isSlideFullyVisible = (slide) => {
              const slideRect = slide.getBoundingClientRect();
              const containerRect = swiper.el.getBoundingClientRect();
              return slideRect.left >= containerRect.left && slideRect.right <= containerRect.right;
            };

            const slides = swiper.slides;
            const activeSlider = swiper.activeIndex;

            // fix invalid inert elements due to 3rd party js (priceSpider) set tabindex="0"
            const invalidInertElements = swiper.el.querySelectorAll('[inert] [tabindex="0"]');
            invalidInertElements.forEach((elm) => {
              elm.setAttribute('data-inert-orig-tabindex', elm.getAttribute('tabindex'));
              elm.setAttribute('tabindex', "-1");
            });

            slides.forEach((slide, i) => {
              if(activeSlider === i || isSlideFullyVisible(slide)){
                removeInert(slide);
                slide.classList.add('is-in-viewport');
              }
              else {
                setInert(slide);
                slide.classList.remove("is-in-viewport");
              }
            });
          }, 100);

          swiper.on('afterInit', () => {
            adjustCarouselLock();
            adjustInertSlides();
          });

          swiper.on('resize', _.debounce( () => {
            adjustCarouselLock();
            adjustInertSlides();
          }, 100));

          swiper.on('breakpoint', (swiper, breakpointParams) => {
            isLocked = null;
          });

          swiper.on('slideChange', () => {
            adjustInertSlides();
          });

          swiper.on('slideChangeTransitionEnd', () => {
            adjustInertSlides();
          });

          swiper.on('transitionEnd', () => {
            adjustInertSlides();
          });

          swiper.init();
          // END: the same code for
          // recommendations-module.js
          // social-feed.js

        });
      });
    },
  };
})(jQuery, _, Drupal);
