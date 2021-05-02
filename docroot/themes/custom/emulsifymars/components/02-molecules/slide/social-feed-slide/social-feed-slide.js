import 'lazysizes';

(function($, Drupal) {
Drupal.behaviors.slideHelper = {
  attach(context) {
    $(context).find('.social-feed-slide').once('slideHelper').each(function(){
      // getting time difference in minutes or hours or days
      const createdAt = this.getElementsByClassName('social-feed-slide__data');
      const createdAtElements = Array.from(createdAt);

      createdAtElements.forEach(elem => {
        const d = new Date(elem.textContent);
        const now = new Date();
        const diffMs = now.getTime() - d.getTime();
        const diffMins = (diffMs > 0) ? diffMs / (1000*60) : 0;
        if (diffMins > 60) {
          const diffHours = diffMins / 60;
          if (diffHours > 24) {
            const diffDays = diffHours / 24;
            elem.textContent = Math.floor(diffDays) + ' day(s) ago';
          } else {
            elem.textContent = Math.floor(diffHours) + ' hour(s) ago';
          }
        } else {
          elem.textContent = Math.floor(diffMins) + ' minute(s) ago';
        }
      });
    })
  },
};
})(jQuery, Drupal);
