(function ($, Drupal) {
  Drupal.behaviors.ambientVideoPlayer = {
    attach(context) {
      // Does the browser actually support the video element?
      var supportsVideo = !!document.createElement('video').canPlayType;
      if (supportsVideo === false) {
        return;
      }
      var videoInitState = function(videoContainer) {
        var video = videoContainer.querySelector('.ambient-video__main');
        if (video === null || video.getAttribute('data-video-init')) {
          return;
        }
        video.muted = true;
        video.loop = true;
        video.autoplay = true;
        video.controls = false;
        video.play();

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

          // Add event listeners to provide info to Data layer
          if (typeof dataLayer !== 'undefined') {
            const componentBlock = video.closest('[data-block-plugin-id]');
            const componentName = componentBlock ? componentBlock.dataset.blockPluginId : '';
            const parentTitleBlock = video.closest('[data-component-title]');
            const videoTitle = parentTitleBlock ? parentTitleBlock.dataset.componentTitle : '';

            dataLayer.push({
              event: 'videoPageView',
              pageName: document.title,
              videoTitle: videoTitle,
              videoFlag: videoContainer.dataset.videoFlag,
              componentName: componentName
            });

            video.addEventListener('play', () => {
              dataLayer.push({
                event: 'videoView',
                pageName: document.title,
                videoStart: 1,
                videoTitle: videoTitle,
                videoFlag: videoContainer.dataset.videoFlag,
                componentName: componentName
              });
            }, {once : true});

            let videoEndedHandler = () => {
              var tr = video.played;
              var hasLoopedOnce = (tr.end(tr.length-1) == video.duration);
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
                video.removeEventListener('timeupdate', videoEndedHandler);
              }
            };

            video.addEventListener("timeupdate", videoEndedHandler);
          }

          // Listen to scroll event to pause video when out of viewport
          let videoVisible = false;
          let manuallyPaused = false;
          document.addEventListener('scroll', function() {
            let videoPosition = video.getBoundingClientRect().top;
            let videoHeight = video.getBoundingClientRect().height;
            let windowHeight = window.innerHeight;

            if (videoPosition - windowHeight > 0 || videoPosition + videoHeight < 0) {
              video.pause();
              videoVisible = false;
            }
            else if (!manuallyPaused && !videoVisible) {
              video.play();
              videoVisible = true;
            }
          });

          // Add events for play/pause button and video container
          playpause.addEventListener('click', function(e) {
            if (video.paused || video.ended) {
              video.play();
              video.muted = false;
              manuallyPaused = false;
            } else {
              video.pause();
              manuallyPaused = true;
            }
          });
          video.addEventListener('click', function(e) {
            if (video.paused || video.ended) {
              video.play();
              manuallyPaused = false;
            } else {
              video.pause();
              manuallyPaused = true;
            }
          });
        }
        video.setAttribute('data-video-init', true);
      };

      // Changes the button state of certain button's so the correct visuals can be displayed with CSS
      var changeButtonState = function(video, playpause, type) {
        // Play/Pause button
        if (type == 'playpause') {
          if (video.paused || video.ended) {
            playpause.setAttribute('data-state', 'play');
            playpause.setAttribute('aria-label', Drupal.t('Play'));
          } else {
            playpause.setAttribute('data-state', 'pause');
            playpause.setAttribute('aria-label', Drupal.t('Pause'));
          }
        }
      };

      // Obtain handles to main elements
      var videos = document.querySelectorAll('.ambient-video');
      videos.forEach(function(video) {
        videoInitState(video);
      });
    }
  };
})(jQuery, Drupal);
