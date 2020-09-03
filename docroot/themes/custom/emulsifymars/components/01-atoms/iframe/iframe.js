Drupal.behaviors.iframe = {
  attach(context) {
    const iframe = document.getElementById('iframe-component');
    iframe.onload = function() {
      console.log(iframe);
    }
  },
};
