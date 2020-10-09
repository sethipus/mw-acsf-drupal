Drupal.behaviors.fullscreenVideoPlayer = {
  attach(context) {
    // Does the browser actually support the video element?
    var supportsVideo = !!document.createElement('video').canPlayType;
    if (supportsVideo === false) {
      return;
    }

    var videoInitState = function(videoContainer) {
      // Setup memoize function for video elements selectors
      var videoElements = (function() {
        var memo = {};

        function f(n) {
          var value;
          if (n in memo) {
            value = memo[n];
          } else {
            value = videoContainer.querySelector('.fullscreen-video__' + n);
            memo[n] = value;
          }
          return value;
        }

        return f;
      })();

      if (videoElements('video') === null || videoContainer.getAttribute('data-video-init')) {
        return;
      }
      videoElements('video').controls = false;
      videoElements('video').muted = true;
      videoElements('video').loop = true;
      videoElements('video').autoplay = true;
      videoElements('video').play();

      // Display the user defined video controls
      videoElements('controls').setAttribute('data-state', 'hidden');

      // If the browser doesn't support the progress element, set its state for some different styling
      var supportsProgress = (document.createElement('progress').max !== undefined);
      if (!supportsProgress) videoElements('progress-time--inner').setAttribute('data-state', 'fake');

      // Check if the browser supports the Fullscreen API
      var fullScreenEnabled = !!(document.fullscreenEnabled || document.mozFullScreenEnabled || document.msFullscreenEnabled || document.webkitSupportsFullscreen || document.webkitFullscreenEnabled || document.createElement('video').webkitRequestFullScreen);
      // If the browser doesn't support the Fulscreen API then hide the fullscreen button
      if (!fullScreenEnabled) {
        videoElements('fs').style.display = 'none';
      }

      // Only add the events if addEventListener is supported (IE8 and less don't support it, but that will use Flash anyway)
      if (document.addEventListener) {
        // Wait for the video's meta data to be loaded, then set the progress bar's max value to the duration of the video
        videoElements('video').addEventListener('loadedmetadata', function() {
          videoElements('progress-time--inner').setAttribute('max', videoElements('video').duration);
          videoElements('progress-time--duration').innerHTML = '0:00/' + videoElements('video').duration;
        });

        // Add event listeners for video specific events
        videoElements('video').addEventListener('play', function() {
          changeButtonState(videoElements, 'playpause');
        }, false);
        videoElements('video').addEventListener('pause', function() {
          changeButtonState(videoElements, 'playpause');
        }, false);
        videoElements('video').addEventListener('volumechange', function() {
          checkVolume(videoElements);
        }, false);

        // Add events for all buttons
        videoElements('playpause').addEventListener('click', function(e) {
          if (videoElements('video').paused || videoElements('video').ended) videoElements('video').play();
          else videoElements('video').pause();
        });

        // The Media API has no 'stop()' function, so pause the video and reset its time and the progress bar
        videoElements('stop').addEventListener('click', function(e) {
          videoElements('video').pause();
          videoElements('video').currentTime = 0;
          videoElements('progress-time--inner').value = 0;
          // Update the play/pause button's 'data-state' which allows the correct button image to be set via CSS
          changeButtonState(videoElements, 'playpause');
        });
        videoElements('mute').addEventListener('click', function(e) {
          videoElements('video').muted = !videoElements('video').muted;
          changeButtonState(videoElements, 'mute');
        });
        videoElements('fs').addEventListener('click', function(e) {
          handleFullscreen(videoContainer, videoElements);
        });
        videoElements('close').addEventListener('click', function(e) {
          handleFullscreen(videoContainer, videoElements);
        });
        if (videoElements('control')) {
          videoElements('control').addEventListener('click', function(e) {
            handleFullscreen(videoContainer, videoElements);
            videoElements('video').muted = !videoElements('video').muted;
          });
        }
        else if (videoContainer.parentElement.parentElement.querySelector('.homepage-hero-video__container--title .fullscreen-video__control')) {
          var outerControl = videoContainer.parentElement.parentElement.querySelector('.homepage-hero-video__container--title .fullscreen-video__control');
          outerControl.addEventListener('click', function(e) {
            handleFullscreen(videoContainer, videoElements);
            videoElements('video').muted = !videoElements('video').muted;
          });
        }

        // As the video is playing, update the progress bar
        videoElements('video').addEventListener('timeupdate', function() {
          // For mobile browsers, ensure that the progress element's max attribute is set
          if (!videoElements('progress-time--inner').getAttribute('max')) videoElements('progress-time--inner').setAttribute('max', videoElements('video').duration);
          videoElements('progress-time--inner').value = videoElements('video').currentTime;
          videoElements('progress-time--progress-bar').style.width = Math.floor((videoElements('video').currentTime / videoElements('video').duration) * 100) + '%';
          videoElements('progress-time--duration').innerHTML = parseFloat(videoElements('video').currentTime.toFixed(2)) + '/' + videoElements('video').duration;
        });

        // React to the user clicking within the progress bar
        videoElements('progress-time--inner').addEventListener('click', function(e) {
          var pos = e.offsetX / this.offsetWidth;
          videoElements('video').currentTime = pos * videoElements('video').duration;
        });

        // Listen for fullscreen change events (from other controls, e.g. right clicking on the video itself)
        document.addEventListener('fullscreenchange', function(e) {
          setFullscreenData(videoContainer, videoElements, !!(document.fullScreen || document.fullscreenElement));
        });
        document.addEventListener('webkitfullscreenchange', function(e) {
          setFullscreenData(videoContainer, videoElements, !!document.webkitIsFullScreen);
        });
        document.addEventListener('mozfullscreenchange', function(e) {
          setFullscreenData(videoContainer, videoElements, !!document.mozFullScreen);
        });
        document.addEventListener('msfullscreenchange', function(e) {
          setFullscreenData(videoContainer, videoElements, !!document.msFullscreenElement);
        });
      }

      videoElements('video').setAttribute('data-video-init', true);
    }

    // Changes the button state of certain button's so the correct visuals can be displayed with CSS
    var changeButtonState = function(videoElements, type) {
      // Play/Pause button
      if (type == 'playpause') {
        if (videoElements('video').paused || videoElements('video').ended) {
          videoElements('playpause').setAttribute('data-state', 'play');
        } else {
          videoElements('playpause').setAttribute('data-state', 'pause');
        }
      }
      // Mute button
      else if (type == 'mute') {
        videoElements('mute').setAttribute('data-state', videoElements('video').muted ? 'unmute' : 'mute');
      }
    }

    // Check the volume
    var checkVolume = function(videoElements, dir) {
      if (dir) {
        var currentVolume = Math.floor(videoElements('video').volume * 10) / 10;
        if (dir === '+') {
          if (currentVolume < 1) videoElements('video').volume += 0.1;
        } else if (dir === '-') {
          if (currentVolume > 0) videoElements('video').volume -= 0.1;
        }
        // If the volume has been turned off, also set it as muted
        // Note: can only do this with the custom control set as when the 'volumechange' event is raised, there is no way to know if it was via a volume or a mute change
        if (currentVolume <= 0) videoElements('video').muted = true;
        else videoElements('video').muted = false;
      }
      changeButtonState(videoElements, 'mute');
    }

    // Change the volume
    var alterVolume = function(videoElements, dir) {
      checkVolume(videoElements, dir);
    }

    // Set the video container's fullscreen state
    var setFullscreenData = function(videoContainer, videoElements, state) {
      if (!state) videoElements('video').pause();
      videoContainer.setAttribute('data-fullscreen', !!state);
      // Set the fullscreen button's 'data-state' which allows the correct button image to be set via CSS
      videoElements('fs').setAttribute('data-state', !!state ? 'cancel-fullscreen' : 'go-fullscreen');
      videoElements('controls').setAttribute('data-state', !!state ? 'visible' : 'hidden');
    }

    // Checks if the document is currently in fullscreen mode
    var isFullScreen = function() {
      return !!(document.fullScreen || document.webkitIsFullScreen || document.mozFullScreen || document.msFullscreenElement || document.fullscreenElement);
    }

    // Fullscreen
    var handleFullscreen = function(videoContainer, videoElements) {
      // If fullscreen mode is active...
      if (isFullScreen()) {
        // ...exit fullscreen mode
        // (Note: this can only be called on document)
        if (document.exitFullscreen) document.exitFullscreen();
        else if (document.mozCancelFullScreen) document.mozCancelFullScreen();
        else if (document.webkitCancelFullScreen) document.webkitCancelFullScreen();
        else if (document.msExitFullscreen) document.msExitFullscreen();
        setFullscreenData(videoContainer, videoElements, false);
      } else {
        // ...otherwise enter fullscreen mode
        // (Note: can be called on document, but here the specific element is used as it will also ensure that the element's children, e.g. the custom controls, go fullscreen also)
        if (videoContainer.requestFullscreen) videoContainer.requestFullscreen();
        else if (videoContainer.mozRequestFullScreen) videoContainer.mozRequestFullScreen();
        else if (videoContainer.webkitRequestFullScreen) {
          // Safari 5.1 only allows proper fullscreen on the video element. This also works fine on other WebKit browsers as the following CSS (set in styles.css) hides the default controls that appear again, and
          // ensures that our custom controls are visible:
          // figure[data-fullscreen=true] video::-webkit-media-controls { display:none !important; }
          // figure[data-fullscreen=true] .controls { z-index:2147483647; }
          videoElements('video').webkitRequestFullScreen();
        } else if (videoContainer.msRequestFullscreen) videoContainer.msRequestFullscreen();
        setFullscreenData(videoContainer, videoElements, true);
      }
    }

    // Obtain handles to main elements
    var videos = document.querySelectorAll('.fullscreen-video');
    videos.forEach(function(video) {
      videoInitState(video);
    });
  }
}
