import moment from 'moment';

(function($, Drupal) {
Drupal.behaviors.slideHelper = {
  attach(context) {
    $(context).find('.social-feed-slide').once('slideHelper').each(function(){
      // getting time difference in minutes or hours or days
      const createdAt = this.getElementsByClassName('social-feed-slide__data');
      const createdAtElements = Array.from(createdAt);

      createdAtElements.forEach(elem => {
        if (moment().diff(elem.textContent, 'minutes') > 60) {
          const hours = moment().diff(elem.textContent, 'hours');
          if (hours > 24) {
            elem.textContent = moment().diff(elem.textContent, 'days') + ' day(s) ago';
          } else {
            elem.textContent = hours + ' hour(s) ago';
          }
        } else {
          elem.textContent = moment().diff(elem.textContent, 'minutes') + ' minute(s) ago';
        }
      });
    })
  },
};
})(jQuery, Drupal);
