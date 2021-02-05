import 'objectFitPolyfill';

if (window.MSInputMethodContext && document.documentMode) {
  window.objectFitPolyfill();
}
