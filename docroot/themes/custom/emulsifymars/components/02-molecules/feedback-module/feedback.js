Drupal.behaviors.feedback = {
  attach(context) {

    const choiceButtons = document.querySelectorAll('.feedback-module__radio');
    choiceButtons.forEach((e) => {
      e.addEventListener('change', () => {
        document.querySelector('#edit-vote').dispatchEvent(new Event('mousedown'));
      });
    });

  },
};
