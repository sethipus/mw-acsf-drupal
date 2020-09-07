Drupal.behaviors.iframe = {
  attach(context) {
    const iframe = document.getElementById('iframe-component');
    const receiveMessage = (event) => {
      if (event.data.action === 'resize') {
        iframe.style.height = `${event.data.height}px`;
      }
    };
    window.addEventListener('message', receiveMessage, false);
  },
};
