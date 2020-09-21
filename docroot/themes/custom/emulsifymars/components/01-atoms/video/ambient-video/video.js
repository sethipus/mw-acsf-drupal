Drupal.behaviors.inlineVideoPlayer = {
  attach(context) {
    // Does the browser actually support the video element?
    var supportsVideo = !!document.createElement('video').canPlayType;
    if (supportsVideo === false) {
      return;
    }
    var videoInitState = function(videoContainer) {
      var video = videoContainer.querySelector('.ambient-video__main');
      if (video === null || videoContainer.getAttribute('data-video-init')) {
        return;
      }
      video.controls = false;
      video.muted = false;
      if (document.addEventListener) {
        // Obtain handles to buttons and other elements
        var playpause = videoContainer.querySelector('.ambient-video__control');

        // Add event listeners for video specific events
        video.addEventListener('play', function() {
          changeButtonState(video, playpause, 'playpause');
        }, false);
        video.addEventListener('pause', function() {
          changeButtonState(video, playpause, 'playpause');
        }, false);
  
        // Add events for all buttons			
        playpause.addEventListener('click', function(e) {
          if (video.paused || video.ended) video.play();
          else video.pause();
        });
      }
      video.setAttribute('data-video-init', true);
    }

    // Changes the button state of certain button's so the correct visuals can be displayed with CSS
    var changeButtonState = function(video, playpause, type) {
      // Play/Pause button
      if (type == 'playpause') {
        if (video.paused || video.ended) {
          playpause.setAttribute('data-state', 'play');
        } else {
          playpause.setAttribute('data-state', 'pause');
        }
      }
    }
    
    // Obtain handles to main elements
    var videos = document.querySelectorAll('.ambient-video');
    videos.forEach(function(video) {
      videoInitState(video);
    });
  }
}
