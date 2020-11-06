(function ($, Drupal) {
Drupal.behaviors.parentPageHeader = {
    attach(context) {
      $(context).find('.parent-page-header').once('parentPageHeader').each(function(){
        const $parentPageHeader = $(this);
        const $button = $parentPageHeader.find('.video-button');
        const $video = $parentPageHeader.find('.video-player');
        if ($button.length < 1 || $video.length < 1) {
          return;
        }
        const videoElement = $video[0];

        $button.html(videoElement.paused ? '||' : '>');
        $button.on('click', () => {
          if(videoElement.paused) {
            videoElement.play();
            $button.html('||');
          } else {
            videoElement.pause();
            $button.html('>');
          }
        });
      });
    },
  };
})(jQuery, Drupal);
