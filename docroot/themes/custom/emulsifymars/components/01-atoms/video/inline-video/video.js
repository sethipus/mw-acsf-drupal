(function ($, Drupal) {
  Drupal.behaviors.inlineVideoPlayer = {
    attach(context) {
      const video = document.querySelector('video');
      video.onplay = function() {
        video.currentTime = 0;
        video.onplay = document.querySelector('.inline-video__control').style.display = 'block';
      }
      // Does the browser actually support the video element?
      var supportsVideo = !!document.createElement('video').canPlayType;
      if (supportsVideo === false) {
        return;
      }

      var videoInitState = function (videoContainer) {
        // Setup memoize function for video elements selectors
        var videoElements = (function () {
          var memo = {};

          function f(n) {
            var value;
            if (n in memo) {
              value = memo[n];
            }
            else {
              value = videoContainer.querySelector('.inline-video__' + n);
              memo[n] = value;
            }
            return value;
          }

          return f;
        })();

        if (videoElements('video') === null || videoElements('video').getAttribute('data-video-init')) {
          return;
        }
        videoElements('video').controls = false;
        videoElements('video').muted = false;

        // Display the user defined video controls
        videoElements('controls').setAttribute('data-state', 'hidden');

        // If the browser doesn't support the progress element, set its state for some different styling
        var supportsProgress = (document.createElement('progress').max !== undefined);
        if (!supportsProgress) videoElements('progress-time--inner').setAttribute('data-state', 'fake');

        // Only add the events if addEventListener is supported (IE8 and less don't support it, but that will use Flash anyway)
        if (document.addEventListener) {
          // Wait for the video's meta data to be loaded, then set the progress bar's max value to the duration of the video
          videoElements('video').addEventListener('loadedmetadata', function () {
            videoElements('progress-time--inner').setAttribute('max', videoElements('video').duration);
            videoElements('progress-time--duration').innerHTML = '0:00/' + videoElements('video').duration;
          });

          // Add event listeners for video specific events
          videoElements('video').addEventListener('play', function () {
            changeButtonState(videoElements, 'playpause');
          }, false);
          videoElements('video').addEventListener('pause', function () {
            changeButtonState(videoElements, 'playpause');
          }, false);
          videoElements('video').addEventListener('volumechange', function () {
            checkVolume(videoElements);
          }, false);

          // Add event listeners to provide info to Data layer
          if (typeof dataLayer !== 'undefined') {
            const componentBlock = videoElements('video').closest('[data-block-plugin-id]');
            const componentName = componentBlock ? componentBlock.dataset.blockPluginId : '';
            const parentTitleBlock = videoElements('video').closest('[data-component-title]');
            const videoTitle = parentTitleBlock ? parentTitleBlock.dataset.componentTitle : '';

            dataLayer.push({
              event: 'videoPageView',
              pageName: document.title,
              videoTitle: videoTitle,
              videoFlag: videoContainer.dataset.videoFlag,
              componentName: componentName
            });

            videoElements('video').addEventListener('play', () => {
              dataLayer.push({
                event: 'videoView',
                pageName: document.title,
                videoStart: 1,
                videoTitle: videoTitle,
                videoFlag: videoContainer.dataset.videoFlag,
                componentName: componentName
              });
            }, {once: true});

            let videoEndedHandler = () => {
              var tr = videoElements('video').played;
              var hasLoopedOnce = (tr.length > 0) ?
                                    (tr.end(tr.length - 1) == videoElements('video').duration):
                                    false;
              if (hasLoopedOnce) {
                dataLayer.push({
                  event: 'videoView',
                  pageName: document.title,
                  videoStart: 1,
                  videoComplete: 1,
                  videoTitle: videoTitle,
                  videoFlag: videoContainer.dataset.videoFlag,
                  componentName: componentName
                });
                videoElements('video').removeEventListener('timeupdate', videoEndedHandler);
              }
            }

            videoElements('video').addEventListener("timeupdate", videoEndedHandler);
          }

          // Play and pause when clicking on a video
          videoElements('video').addEventListener('click', function (e) {
            if (videoElements('video').paused || videoElements('video').ended) {
              videoElements('video').play();
            }
            else {
              videoElements('video').pause();
            }
            videoElements('controls').setAttribute('data-state', 'visible');
            setFullcontrolData(videoContainer, videoElements, true);
            videoElements('video').muted = !videoElements('video').muted;
            changeButtonState(videoElements, 'mute');
          });

          // Add events for all buttons
          videoElements('playpause').addEventListener('click', function (e) {
            if (videoElements('video').paused || videoElements('video').ended) videoElements('video').play();
            else videoElements('video').pause();
          });

          videoElements('mute').addEventListener('click', function (e) {
            videoElements('video').muted = !videoElements('video').muted;
            changeButtonState(videoElements, 'mute');
          });
          videoElements('control').addEventListener('click', function (e) {
            handleFullcontrol(videoContainer, videoElements);
            videoElements('video').play();
          });
          videoElements('close').addEventListener('click', function (e) {
            handleFullcontrol(videoContainer, videoElements);
          });
          document.addEventListener('keyup', function (event) {
            if (event.keyCode == 27) {
              handleFullcontrol(videoContainer, videoElements);
            }
          });

          // As the video is playing, update the progress bar
          videoElements('video').addEventListener('timeupdate', function () {
            // For mobile browsers, ensure that the progress element's max attribute is set
            if (!videoElements('progress-time--inner').getAttribute('max')) videoElements('progress-time--inner').setAttribute('max', videoElements('video').duration);
            videoElements('progress-time--inner').value = videoElements('video').currentTime;
            videoElements('progress-time--progress-bar').style.width = Math.floor((videoElements('video').currentTime / videoElements('video').duration) * 100) + '%';
            videoElements('progress-time--duration').innerHTML = parseFloat(videoElements('video').currentTime).toFixed(2) + '/' + videoElements('video').duration.toFixed(2);
          });

          // React to the user clicking within the progress bar
          videoElements('progress-time--inner').addEventListener('click', function (e) {
            var pos = e.offsetX / this.offsetWidth;
            videoElements('video').currentTime = pos * videoElements('video').duration;
          });

          // Listen to scroll event to pause video when out of viewport
          document.addEventListener('scroll', function () {
            let videoPosition = videoElements('video').getBoundingClientRect().top;
            let videoHeight = videoElements('video').getBoundingClientRect().height;
            let windowHeight = window.innerHeight;

            if (videoPosition - windowHeight > 0 || videoPosition + videoHeight < 0) {
              videoElements('video').pause();
            }
          });
        }

        videoElements('video').setAttribute('data-video-init', true);
      }

      // Changes the button state of certain button's so the correct visuals can be displayed with CSS
      var changeButtonState = function (videoElements, type) {
        // Play/Pause button
        if (type == 'playpause' || type === 'control') {
          if (videoElements('video').paused || videoElements('video').ended) {
            videoElements(type).setAttribute('data-state', 'play');
            videoElements(type).setAttribute('aria-label', Drupal.t('Play'));
          }
          else {
            videoElements(type).setAttribute('data-state', 'pause');
            videoElements(type).setAttribute('aria-label', Drupal.t('Pause'));
          }
        }
        // Mute button
        else if (type == 'mute') {
          videoElements('mute').setAttribute('data-state', videoElements('video').muted ? 'unmute' : 'mute');
        }
      }

      // Check the volume
      var checkVolume = function (videoElements, dir) {
        if (dir) {
          var currentVolume = Math.floor(videoElements('video').volume * 10) / 10;
          if (dir === '+') {
            if (currentVolume < 1) videoElements('video').volume += 0.1;
          }
          else if (dir === '-') {
            if (currentVolume > 0) videoElements('video').volume -= 0.1;
          }
          // If the volume has been turned off, also set it as muted
          // Note: can only do this with the custom control set as when the 'volumechange' event is raised, there is no way to know if it was via a volume or a mute change
          if (currentVolume <= 0) videoElements('video').muted = true;
          else videoElements('video').muted = false;
        }
        changeButtonState(videoElements, 'mute');
      }

      // Set the video container's fullcontrol state
      var setFullcontrolData = function (videoContainer, videoElements, state) {
        if (!state) videoElements('video').pause();
        videoContainer.setAttribute('data-fullcontrol', !!state);
        // Set the fullscreen button's 'data-state' which allows the correct button image to be set via CSS
        videoElements('control').setAttribute('data-state', !!state ? 'hidden' : 'play');
        videoElements('controls').setAttribute('data-state', !!state ? 'visible' : 'hidden');
      }

      // Checks if the document is currently in fullcontol mode
      var isFullcontrol = function (videoContainer) {
        return videoContainer.getAttribute('data-fullcontrol');
      }

      // Fullcontrol
      var handleFullcontrol = function (videoContainer, videoElements) {
        // If fullcontrol mode is active...
        if (isFullcontrol(videoContainer) == 'false') {
          videoElements('controls').setAttribute('data-state', 'visible');
          setFullcontrolData(videoContainer, videoElements, true);
        }
        else {
          videoElements('controls').setAttribute('data-state', 'hidden');
          setFullcontrolData(videoContainer, videoElements, false);
        }
      }

      // Obtain handles to main elements
      var videos = document.querySelectorAll('.inline-video');
      videos.forEach(function (video) {
        videoInitState(video);
      });
    }
  };
})(jQuery, Drupal);
