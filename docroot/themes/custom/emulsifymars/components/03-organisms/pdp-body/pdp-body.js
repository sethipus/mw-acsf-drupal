import Swiper, {Autoplay, Pagination} from 'swiper';

(function($, _, Drupal) {
  Drupal.behaviors.pdpBody = {
    attach(context) {
      $(context).find('.pdp-body').eq(0).parent().once('pdpBodyParent').each(function(){

      //snapScroll function
      function _defineProperty(obj, key, value) {
        if (key in obj) {
          Object.defineProperty(obj, key, {
            value: value,
            enumerable: true,
            configurable: true,
            writable: true
          });
        } else {
          obj[key] = value;
        }

        return obj;
      }

      function _objectSpread(target) {
        for (var i = 1; i < arguments.length; i++) {
          var source = arguments[i] != null ? arguments[i] : {};
          var ownKeys = Object.keys(source);

          if (typeof Object.getOwnPropertySymbols === 'function') {
            ownKeys = ownKeys.concat(Object.getOwnPropertySymbols(source).filter(function (sym) {
              return Object.getOwnPropertyDescriptor(source, sym).enumerable;
            }));
          }

          ownKeys.forEach(function (key) {
            _defineProperty(target, key, source[key]);
          });
        }

        return target;
      }

      function _toConsumableArray(arr) {
        return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread();
      }

      function _arrayWithoutHoles(arr) {
        if (Array.isArray(arr)) {
          for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) arr2[i] = arr[i];

          return arr2;
        }
      }

      function _iterableToArray(iter) {
        if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter);
      }

      function _nonIterableSpread() {
        throw new TypeError("Invalid attempt to spread non-iterable instance");
      }

      function offsetPaddingCalc(item) {
        let stickyNavTop = $('[data-pdp-size-active="true"] .pdp-hero__sticky-nav-top');
        return (stickyNavTop.length) ? stickyNavTop.eq(0).outerHeight() : 0;
      }

      var SnapScroll = function SnapScroll(selector, options) {
        var defaults = _objectSpread({
          proximity: 100,
          duration: 200,
          easing: function easing(time) {
            return time;
          },
          onSnapWait: 50
        }, options);

        var items = _toConsumableArray(document.querySelectorAll(selector));

        var positions = [];
        var currentlySnapped;
        var snapTimeout;
        var isScrolling;

        var getPositions = function getPositions() {

          positions = items.map(function (item) {
            return {
              offset: $(item).is(":visible") ? item.getBoundingClientRect().top + window.scrollY - offsetPaddingCalc(item) : -5000,
              element: item,
            };
          });
        };

        var animatedScrollTo = function animatedScrollTo() {
          var scrollTargetY = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
          var callback = arguments.length > 1 ? arguments[1] : undefined;
          var _window = window,
              scrollY = _window.scrollY;
          var currentTime = 0;
          var time = Math.max(0.1, Math.min(Math.abs(scrollY - scrollTargetY) / defaults.duration, 0.4));

          var tick = function tick() {
            currentTime += 1 / 60;
            var p = currentTime / time;
            var t = defaults.easing(p);

            if (p < 1) {
              requestAnimationFrame(tick);
              window.scrollTo(0, scrollY + (scrollTargetY - scrollY) * t);
            } else {
              window.scrollTo(0, scrollTargetY);
              callback();
            }
          };

          tick();
        };

        var snapToElement = function snapToElement() {
          var _window2 = window,
              scrollY = _window2.scrollY;
          var snapElement = positions.find(function (element) {
            return element.offset - defaults.proximity <= scrollY && element.offset + defaults.proximity >= scrollY;
          });
          clearTimeout(snapTimeout);

          if (snapElement && !isScrolling && snapElement != currentlySnapped) {
            snapTimeout = setTimeout(function () {
              isScrolling = true;
              animatedScrollTo(snapElement.offset, function () {
                isScrolling = !isScrolling;
              });
              currentlySnapped = snapElement;
            }, defaults.onSnapWait);
          }
        };

        var recalculateLayout = function recalculateLayout() {
          getPositions();
          snapToElement();
        };

        var bindEvents = function bindEvents() {
          window.addEventListener('resize', recalculateLayout);
          window.addEventListener('scroll', snapToElement);
        };

        var destroy = function destroy() {
          window.removeEventListener('resize', recalculateLayout);
          window.removeEventListener('scroll', snapToElement);
        };

        var init = function init() {
          getPositions();
          bindEvents();
        };

        init();
        return {
          init: init,
          destroy: destroy,
          recalculateLayout: recalculateLayout
        };
      };

      // scroll snapping
      let snapScroller = null;
      function initSnapScroller() {
        if(!snapScroller){
          const options = {
            proximity: 300,
          };
          snapScroller = SnapScroll('.scroll-mandatory', options);
        }
        if($(".pdp-hero__content-mobile:visible").length) {
          snapScroller.init();
        }
        else {
          snapScroller.destroy();
        }
      };

      initSnapScroller();

      // init swiper
      Swiper.use([Autoplay, Pagination]);
      var pdpBodyContainers = context.querySelectorAll('.pdp-body');
      pdpBodyContainers.forEach((pdpBodyContainer, index) => {
        let swiper = null;
        const swiperContainer = pdpBodyContainer.querySelector('.swiper-container');
        const slidesAmount = pdpBodyContainer.querySelectorAll('.swiper-slide').length;
        const swiperPagination = pdpBodyContainer.querySelector('.swiper-pagination');

        if(slidesAmount > 1) {
          swiper = new Swiper(swiperContainer, {
            autoplay: {
              delay: 3000,
              disableOnInteraction: false,
            },
            loop: true,
            direction: 'vertical',
            slidesPerView: 1,
            pagination: {
              el: (swiperPagination) ? swiperPagination : null,
              type: 'bullets',
              clickable: true,
            },
          });

          const swiperControl = $('.swiper-control', pdpBodyContainer);
          swiperControl.on('click', (e) => {
            e.preventDefault();
            if (swiper.autoplay.running) {
              swiper.autoplay.stop();
              swiperControl.addClass('swiper-control-play');
            } else {
              swiper.autoplay.start();
              swiperControl.removeClass('swiper-control-play');
            }
          });
        }
      });

      $('.pdp-hero-menu-container', context).on('click', event => {
        event.preventDefault();
        const stickyNavTopHeight = offsetPaddingCalc();
        if (event.target.className.indexOf('pdp-hero__nutrition-menu') > -1) {
          $(context).scrollTop(
            $('.pdp-hero-menu-container .pdp-hero__nutrition-menu:visible').offset().top - stickyNavTopHeight
          );
        } else if (event.target.className.indexOf('pdp-hero__allergen-menu') > -1) {
          $(context).scrollTop(
            $('.pdp-allergen:visible').offset().top - stickyNavTopHeight
          );
        } else if (event.target.className.indexOf('pdp-hero__cooking-menu') > -1) {
          $(context).scrollTop(
            $('.pdp-cooking:visible').offset().top - stickyNavTopHeight
          );
        } else if (event.target.className.indexOf('pdp-hero__more-info-menu') > -1) {
          $(context).scrollTop(
            $('.pdp-more-information:visible').offset().top - stickyNavTopHeight
          );
        }
      });

      $('.pdp-hero__sticky-nav-bottom a[href^="#"]', context).on('click', event => {
        event.preventDefault();
        const stickyNavTopHeight = offsetPaddingCalc();
        $('html, body').animate({
          scrollTop: $(event.target.getAttribute('href')).offset().top - stickyNavTopHeight
        }, 600);
      });

      //size control
      function updateSizeSlider(event, sizeId) {
        const currentPdpBodyContainer = context.querySelector('[data-pdp-size-active="true"]');
        currentPdpBodyContainer.dataset.pdpSizeActive = false;
        const currentSwiperContainer = currentPdpBodyContainer.querySelector('.swiper-container');
        const currentSwiper = currentSwiperContainer.swiper;
        if(currentSwiper) {
          currentSwiper.autoplay.stop();
        }
        const currentSwiperButtonPlay = currentPdpBodyContainer.querySelector('.swiper-control');
        currentSwiperButtonPlay && currentSwiperButtonPlay.classList.add('swiper-control-play');

        const newPdpBodyContainer = context.querySelector(`[data-pdp-size-id="${sizeId}"]`);
        newPdpBodyContainer.dataset.pdpSizeActive = true;
        const newSwiperContainer = newPdpBodyContainer.querySelector('.swiper-container');
        const newSwiper = newSwiperContainer.swiper;
        if(newSwiper) {
          newSwiper.autoplay.stop();
          newSwiper.update();
          newSwiper.autoplay.start();
        }
        const newSwiperButtonPlay = newPdpBodyContainer.querySelector('.swiper-control');
        newSwiperButtonPlay && newSwiperButtonPlay.classList.remove('swiper-control-play');

        initScrollEffects();
      }

      var sizeElements = context.querySelectorAll('[data-size-id]');
      sizeElements.forEach((item) => {
        item.addEventListener('click', e => {
          e.preventDefault();
          if (item.dataset.sizeSelected !== "true") {
            updateSizeSlider(e, item.dataset.sizeId);
            updateReview(e, item.dataset.sizeId);
            initSnapScroller();
          }
        }, false);
      });

      function updateReview(event, sizeId) {
        let reviews = context.querySelectorAll(`div[data-bv-show="reviews"]`);
        if (reviews.length > 0) {
          reviews.forEach((item) => {
            if (item.hasChildNodes()) {
              item.removeChild(item.firstChild);
            }
          });
          let review = context.querySelector(`[data-review-size-id="${sizeId}"] div[data-bv-show="reviews"]`);
          let productId = review.getAttribute('data-bv-product-id');
          review.setAttribute('data-bv-product-id', productId);
        }
      }

      //scroll effects: bubbles, section-select and WTB
      function initScrollEffects() {
        $(window).off('scroll.pdpScrollEffects');
        const pdp_section = $('[data-pdp-size-active="true"]', context);
        const pdp_size_id = pdp_section.attr('data-pdp-size-id');

        const pdp_bubble_1 = $('.pdp-hero__bubble--1', pdp_section);
        const pdp_bubble_2 = $('.pdp-hero__bubble--2', pdp_section);
        const pdp_bubble_3 = $('.pdp-hero__bubble--3', pdp_section);
        const pdp_bubble_1_top = pdp_bubble_1.css({top: ''}).offset().top;
        const pdp_bubble_2_top = pdp_bubble_2.css({top: ''}).offset().top;
        const pdp_bubble_3_top = pdp_bubble_3.css({top: ''}).offset().top;

        const pdp_hero = $(`.pdp-hero--${pdp_size_id}`, pdp_section);
        const pdp_main_image = $(`.pdp-hero-main-image--${pdp_size_id}`, pdp_section);
        const pdp_sticky_nav_top = $(`.pdp-hero__sticky-nav-top--${pdp_size_id}`, pdp_section);
        const pdp_sticky_nav_bottom = $(`.pdp-hero__sticky-nav-bottom--${pdp_size_id}`, pdp_section);
        const pdp_wtb = $(`.where-to-buy--${pdp_size_id}`, pdp_section);

        const pdp_main_image_top = pdp_main_image.offset().top;
        const pdp_hero_bottom = pdp_hero.offset().top + pdp_hero.outerHeight() - window.innerHeight;
        const pdp_section_bottom = pdp_section.offset().top + pdp_section.outerHeight();

        var scrollEventListener = function() {
          const offset = window.pageYOffset;
          pdp_bubble_1.css({top: `${pdp_bubble_1_top - (offset * .75)}px`});
          pdp_bubble_2.css({top: `${pdp_bubble_2_top - (offset * .75)}px`})
          pdp_bubble_3.css({top: `${pdp_bubble_3_top - (offset * .75)}px`})

          offset > pdp_main_image_top ? pdp_sticky_nav_top.addClass('nav--show') : pdp_sticky_nav_top.removeClass('nav--show');
          offset > pdp_hero_bottom ? pdp_sticky_nav_bottom.addClass('sections--hide') : pdp_sticky_nav_bottom.removeClass('sections--hide');
          offset > pdp_section_bottom ? pdp_wtb.addClass('where-to-buy--hide') : pdp_wtb.removeClass('where-to-buy--hide');
        }
        scrollEventListener();
        $(window).on('scroll.pdpScrollEffects', _.throttle(scrollEventListener, 30));
      };

      initScrollEffects();

      $(window).on('resize', _.debounce( () => {
        initSnapScroller();
        initScrollEffects();
      }, 100));
    })
  },
}
})(jQuery, _, Drupal);
