Drupal.behaviors.ambientVideoPlayer = {
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
          const videoContainer = video.target.closest('figure');
          const videoHeading = videoContainer.closest('section').querySelector('h1');

          dataLayer.push({
            event: 'videoPageView',
            pageName: container.title,
            videoTitle: videoHeading.innerText.trim() || '',
            videoId: videoContainer.dataset.videoId,
            videoFlag: videoContainer.dataset.videoFlag,
            componentName: 'Ambient Video'
          }, {once : true});

          video.addEventListener('play', () => {
            dataLayer.push({
              event: 'videoView',
              pageName: container.title,
              videoStart: 0,
              videoTitle: videoHeading.innerText.trim() || '',
              videoId: videoContainer.dataset.videoId,
              videoFlag: videoContainer.dataset.videoFlag,
              componentName: 'Ambient Video'
            });
          }, {once : true});

          video.addEventListener('ended', () => {
            dataLayer.push({
              event: 'videoView',
              pageName: container.title,
              videoStart: 0,
              videoComplete: 1,
              videoTitle: videoHeading.innerText.trim() || '',
              videoId: videoContainer.dataset.videoId,
              videoFlag: videoContainer.dataset.videoFlag,
              componentName: 'Ambient Video'
            });
          }, {once : true});
        }
        
        // Listen to scroll event to pause video when out of viewport
        let videoVisible = false;
        document.addEventListener('scroll', function() {
          let videoPosition = video.offsetTop;
          let videoHeight = video.getBoundingClientRect().height;
          let windowPosition = window.pageYOffset;
          let windowHeight = window.innerHeight;

          if (videoPosition + videoHeight - windowPosition < 0 || windowPosition + windowHeight - videoPosition < 0) {
            video.pause();
            videoVisible = false;
          } else {
            if(!videoVisible) {
              video.play();
              videoVisible = true;
            }
          }
        });
        
        // Add events for play/pause button and video container			
        playpause.addEventListener('click', function(e) {
          if (video.paused || video.ended) {
            video.play();
          } else {
            video.pause();
          }
        });
        video.addEventListener('click', function(e) {
          if (video.paused || video.ended) {
            video.play();
          } else {
            video.pause();
          }
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
