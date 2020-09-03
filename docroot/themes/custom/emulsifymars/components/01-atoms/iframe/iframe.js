Drupal.behaviors.iframe = {
  attach(context) {
    const iframe = document.getElementById('iframe-component');
    iframe.onload = () => {
      // console.log(iframe.documentWindow.body.scrollHeight);
    }
  },
};
