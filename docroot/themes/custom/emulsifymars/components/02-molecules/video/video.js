Drupal.behaviors.inlineVideoPlayer = {
  attach(context) {
    // Does the browser actually support the video element?
    var supportsVideo = !!document.createElement('video').canPlayType;
  
    if (supportsVideo) {
      // Obtain handles to main elements
      var videoContainer = document.getElementById('video-container');
      var video = document.getElementById('video');
  
      // Video settings
      video.controls = false;
      video.muted = true;

      // Obtain handles to buttons and other elements
      var playpause = document.getElementById('playpause');
  
      if (document.addEventListener) {
        // Changes the button state of certain button's so the correct visuals can be displayed with CSS
        var changeButtonState = function(type) {
          // Play/Pause button
          if (type == 'playpause') {
            if (video.paused || video.ended) {
              playpause.setAttribute('data-state', 'play');
            } else {
              playpause.setAttribute('data-state', 'pause');
            }
          }
        }
  
        // Add event listeners for video specific events
        video.addEventListener('play', function() {
          changeButtonState('playpause');
        }, false);
        video.addEventListener('pause', function() {
          changeButtonState('playpause');
        }, false);
  
        // Add events for all buttons			
        playpause.addEventListener('click', function(e) {
          if (video.paused || video.ended) video.play();
          else video.pause();
        });
      }
    }
  }
}
