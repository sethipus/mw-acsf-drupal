import moment from 'moment';

Drupal.behaviors.slideHelper = {
  attach(context) {
    // getting time difference in minutes or hours or days
    const createdAt = context.getElementsByClassName('slide__data');
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
  },
};
