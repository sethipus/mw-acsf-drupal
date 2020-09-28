import Swiper, {Navigation, Pagination, Scrollbar} from 'swiper';

Drupal.behaviors.pdpMultipackCorousel = {
  attach(context) {
    // init swiper
    Swiper.use([Navigation, Pagination, Scrollbar]);
    const swiper = new Swiper('.pdp-multipack-swiper-container', {
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
    const seeAllButton = document.querySelector('#multipack-see-all');
    const multipackSwiper = document.querySelector('#pdp-multipack-swiper-container');
    seeAllButton.addEventListener('click', (event) => {
      event.preventDefault();
      multipackSwiper.classList.add('expanded');
      swiper.update();
    });
  },
};
