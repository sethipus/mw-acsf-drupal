import moment from 'moment';

Drupal.behaviors.entryGate = {
  attach(context) {
    const ageLimit = context.getElementsByClassName('entry-gate')[0].getAttribute('data-age');
    const entryGate = context.getElementsByClassName('entry-gate')[0];
    const fieldset = context.getElementsByClassName('entry-gate-form__fieldset')[0];
    const dayInput = context.getElementById('day');
    const monthInput = context.getElementById('month');
    const yearInput = context.getElementById('year');
    const submitBtn = context.getElementsByClassName('entry-gate-form__submit-btn')[0];
    const errorMessage = context.getElementsByClassName('entry-gate-form__error-message')[0];
    let givenDateIsValid = true;

    // helper for getting cooke with specified name
    const getCookie = name => {
      var cookieArr = document.cookie.split(";");
      for (var i = 0; i < cookieArr.length; i++) {
        var cookiePair = cookieArr[i].split("=");
        if (name == cookiePair[0].trim()) {
          return decodeURIComponent(cookiePair[1]);
        }
      }
      return null;
    }

    // compare cookie value against age limit
    const isOldEnough = (limit) => {
      if(getCookie('dateOfBirth')) {
        return (moment(new Date()).diff(getCookie('dateOfBirth'), 'years')) > limit ? true : false;
      }
    }

    // allow only numbers and max 2 characters length
    const checkValueLength = (event, field, limit) => {
      fieldset.classList.remove('entry-gate-form__fieldset--error');
      errorMessage.style.display = 'none';
      if (event.keyCode >= 48 && event.keyCode <= 57) {
        if (field.value.length > limit) {
          field.value = field.value.subString(0, limit);
        }
      } else {
        event.preventDefault();
      }
    }

    // display entry gate if cookie is not set or the value of cookie is not enough
    entryGate.style.display = isOldEnough(ageLimit) ? 'none' : 'flex';

    dayInput.addEventListener('keypress', e => checkValueLength(e, dayInput, 2));
    monthInput.addEventListener('keypress', e => checkValueLength(e, dayInput, 2));
    yearInput.addEventListener('keypress', e => checkValueLength(e, dayInput, 4));

    submitBtn.addEventListener('click', event => {
      event.preventDefault();
      const givenDate = moment(`${yearInput.value}-${monthInput.value}-${dayInput.value}`);
      givenDateIsValid = givenDate.isValid();
      if (givenDateIsValid === false) {
        // invalid date is entered
        fieldset.classList.add('entry-gate-form__fieldset--error');
        errorMessage.style.display = 'block';
      } else {
        // check age against the age limit
        if ((moment(new Date()).diff(givenDate, 'years')) >= ageLimit) {
          // over the age limit, set cookie and hide entry gate
          context.cookie = `dateOfBirth=${yearInput.value}-${monthInput.value}-${dayInput.value}`;
          entryGate.style.display = 'none';
        } else {
          // under the age limit, show error overlay instead of entry gate
          entryGate.classList.add('age-error');
          console.log('error page');
        }
      }
    })
  },
};
