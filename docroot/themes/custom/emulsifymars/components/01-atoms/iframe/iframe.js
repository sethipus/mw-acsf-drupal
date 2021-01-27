(function ($, Drupal) {
  Drupal.behaviors.iframe = {
    attach(context) {
      $(context).find('.iframe-container').once('iframe').each(function(){
        const $iframe = $('.iframe-container__inner', this);

        const receiveMessage = (event) => {
          if (event.data.action === 'resize') {
            $iframe.css("height", `${event.data.height}px`);
          }
        };
        window.addEventListener('message', receiveMessage, false);
      })
    },
  };
})(jQuery, Drupal);
