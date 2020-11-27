import moment from 'moment';

Drupal.behaviors.entryGate = {
  attach(context) {
    const entryGate = context.getElementsByClassName('entry-gate')[0];
    const ageLimit = entryGate.getAttribute('data-age');
    const fieldset = entryGate.getElementsByClassName('entry-gate-form__fieldset')[0];
    const dayInput = document.getElementById('day');
    const monthInput = document.getElementById('month');
    const yearInput = document.getElementById('year');
    const submitBtn = entryGate.getElementsByClassName('entry-gate-form__submit-btn')[0];
    const errorMessage = entryGate.getElementsByClassName('entry-gate-form__error-message')[0];
    const link = entryGate.getElementsByClassName('entry-gate__bottom-paragraph')[0].getElementsByTagName('a')[0];

    dayInput.onkeydown = function(e) {
      if (e.code === 'Tab' && e.shiftKey) {
          e.preventDefault();
          link.focus();
      }
    }

    link.onkeydown = function(e) {
      if (e.code === 'Tab'  && !e.shiftKey) {
        e.preventDefault();
        dayInput.focus();
      }
    }

    // helper for getting cooke with specified name
    const getCookieDate = name => {
      const cookieArr = document.cookie.split(";");
      for (let i = 0; i < cookieArr.length; i++) {
        const cookiePair = cookieArr[i].split("=");
        if (name === cookiePair[0].trim()) {
          return moment(decodeURIComponent(cookiePair[1]));
        }
      }
      return null;
    }

    // compare cookie value against age limit
    const isOldEnough = (date) => {
      if (moment.isMoment(date)) {
        return (moment(new Date()).diff(date, 'years')) >= ageLimit;
      }
      return false;
    }

    // allow only numbers and max 2 characters length
    const checkValueLength = (event, field, limit) => {
      fieldset.classList.remove('entry-gate-form__fieldset--error');
      errorMessage.style.display = 'none';
      if (event.keyCode >= 48 && event.keyCode <= 57) {
        if (field.value.length > limit) {
          field.value = field.value.subString(0, limit);
        }
      }
      else {
        event.preventDefault();
      }
    }

    // display entry gate if cookie is not set or the value of cookie is not
    // enough
    entryGate.style.display = isOldEnough(getCookieDate('dateOfBirth')) ? 'none' : 'flex';

    dayInput.focus();

    dayInput.addEventListener('keypress', e => checkValueLength(e, dayInput, 2));
    monthInput.addEventListener('keypress', e => checkValueLength(e, dayInput, 2));
    yearInput.addEventListener('keypress', e => checkValueLength(e, dayInput, 4));

    submitBtn.addEventListener('click', event => {
      event.preventDefault();
      const givenDate = moment(`${yearInput.value}-${monthInput.value}-${dayInput.value}`);

      if (!givenDate.isValid()) {
        // invalid date is entered
        fieldset.classList.add('entry-gate-form__fieldset--error');
        errorMessage.style.display = 'block';
        return;
      }

      if (!isOldEnough(givenDate)) {
        // under the age limit, show error overlay instead of entry gate
        entryGate.classList.add('age-error');
        entryGate.getElementsByClassName('entry-gate__error-link')[0].focus();

        entryGate.getElementsByClassName('entry-gate__error-link')[0].onkeydown = function(e) {
          if (e.code === 'Tab' && e.shiftKey) {
              e.preventDefault();
              entryGate.getElementsByClassName('entry-gate__error-link')[1].focus();
          }
        }

        entryGate.getElementsByClassName('entry-gate__error-link')[1].onkeydown = function(e) {
          if (e.code === 'Tab'  && !e.shiftKey) {
            e.preventDefault();
            entryGate.getElementsByClassName('entry-gate__error-link')[0].focus();
          }
        }

        return;
      }

      // over the age limit, set cookie and hide entry gate
      context.cookie = `dateOfBirth=${givenDate.format('YYYY-MM-DD')}; path=/`;
      entryGate.style.display = 'none';
    })
  },
};
