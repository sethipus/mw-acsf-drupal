(function ($, Drupal) {
  Drupal.behaviors.overlayVideoPlayer = {
    attach(context) {
      // Does the browser actually support the video element?
      var supportsVideo = !!document.createElement('video').canPlayType;
      if (supportsVideo === false) {
        return;
      }
      var videoInitState = function(videoContainer) {
        var video = videoContainer.querySelector('.overlay-video__main');
        if (video === null || video.getAttribute('data-video-init')) {
          return;
        }
        video.muted = true;
        video.loop = true;
        video.autoplay = true;
        video.controls = false;
        // video.play();

        if (document.addEventListener) {
          // Obtain handles to buttons and other elements
          var playpause = videoContainer.querySelector('.overlay-video__control');
          var muteunmute = videoContainer.querySelector('.overlay-video__controls');
      
          // Add event listeners for video specific events
          video.addEventListener('play', function() {
            changeButtonState(video, playpause, 'playpause');
          }, false);
          video.addEventListener('pause', function() {
            changeButtonState(video, playpause, 'playpause');
          }, false);

          video.addEventListener('volumechange', function() {
            changeVolume(video, muteunmute, 'muteunmute');
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

          //Listen to scroll event to pause video when out of viewport
          let videoVisible = false;
          let manuallyPaused = false;
          document.addEventListener('scroll', function() {
            let videoPosition = video.getBoundingClientRect().top;
            let videoHeight = video.getBoundingClientRect().height;
            let windowHeight = window.innerHeight;

            if (videoPosition - windowHeight > 0 || videoPosition + videoHeight < 0) {
              video.muted = true;
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
              manuallyPaused = true;
            } else {
              video.pause();
              manuallyPaused = true;
            }
          });

          muteunmute.addEventListener('click', function(e) {
            if(video.muted || video.ended) {      
              video.muted = false;
              manuallyPaused = false;
            }
            else {
              video.muted = true;
              manuallyPaused = true;
            }
          });

          $(".swiper-button-prev, .swiper-button-next").click(function() {
            video.muted = true;
          });
          var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutationRecord) {
              video.muted = true;
            });
            });
            var target = document.querySelectorAll('.swiper-wrapper')[1];
            observer.observe(target, { attributes : true });
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
    // Changes the button state of mute and unmute button's to adjust the audio
      var changeVolume= function( video, muteunmute, type ) {
        if (type == 'muteunmute') {
          if (video.muted || video.ended) {
              muteunmute.setAttribute('data-state', 'mute');
              muteunmute.setAttribute('aria-label', Drupal.t('Mute'));
            } else {
              muteunmute.setAttribute('data-state', 'unmute');
              muteunmute.setAttribute('aria-label', Drupal.t('Unmute'));
          }
        }
      }
      // Obtain handles to main elements
      var videos = document.querySelectorAll('.overlay-video');
      videos.forEach(function(video) {
        videoInitState(video);
      });
    }
  };
})(jQuery, Drupal);
