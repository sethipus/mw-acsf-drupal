Drupal.behaviors.bgVideo = {
    attach(context) {
      const button = context.getElementById('video-button');
      const video = context.getElementById('video-player');
      if (video === null) {
        return;
      }
      button.innerHTML = video.paused ? '||' : '>';
      button.addEventListener('click', () => {
        if(video.paused) {
          video.play();
          button.innerHTML = '||';
        } else {
          video.pause();
          button.innerHTML = '>';
        }
      });
    },
  };
  