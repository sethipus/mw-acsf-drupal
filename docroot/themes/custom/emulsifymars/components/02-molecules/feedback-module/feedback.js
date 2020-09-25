Drupal.behaviors.feedback = {
  attach(context) {

    const choiceButtons = document.querySelectorAll('.feedback-module__radio');
    choiceButtons.forEach((e) => {
      e.addEventListener('change', () => {
        document.getElementsByClassName('poll-view-form')[0].submit();
      });
    });

  },
};
