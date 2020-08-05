import Swiper, {Navigation, Pagination, Scrollbar} from 'swiper';
import 'swiper/swiper-bundle.css';
import moment from 'moment';

Drupal.behaviors.socialFeed = {
  attach(context) {

    // init swiper
    Swiper.use([Navigation, Pagination, Scrollbar]);
    const swiper = new Swiper('.swiper-container', {
      direction: 'horizontal',
      loop: false,
      // slidesPerView: 4,
      // spaceBetween: 40,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      scrollbar: {
        el: '.swiper-scrollbar',
        draggable: true
      },
      breakpoints: {
        768: {
          slidesPerView: 2,
          spaceBetween: 20,
        },
        1024: {
          slidesPerView: 3,
          spaceBetween: 40,
        },
        1460: {
          slidesPerView: 4,
          spaceBetween: 50,
        },
      }
    });

    // getting time difference in minutes or hours or days
    const createdAt = context.getElementsByClassName('createdAt');
    const createdAtElements = Array.from(createdAt);
    createdAtElements.forEach(elem => {
      if(moment().diff(elem.textContent, 'minutes') > 60){
        const hours = moment().diff(elem.textContent, 'hours');
        if(hours > 24) {
          elem.textContent = moment().diff(elem.textContent, 'days') + ' days ago';
        } else {
          elem.textContent = hours + ' hours ago';
        }
      } else {
        elem.textContent = moment().diff(elem.textContent, 'minutes') + ' minutes ago';
      }
    });
  },
};
