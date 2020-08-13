Drupal.behaviors.entryGate = {
  attach(context) {
    const submitBtn = context.getElementsByClassName('entry-gate-form__submit-btn')[0];
    console.log(submitBtn);
  },
};
