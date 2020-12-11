import moment from 'moment';

(function($, Drupal){
  Drupal.behaviors.entryGate = {
    attach(context) {
      $(context).find('.entry-gate').once('entryGate').each(function(){
        const entryGate = $(this);
        const ageLimit = entryGate.data('age');
        const fieldset = $('.entry-gate-form__fieldset', this);
        const dayInput = $('.entry-gate--day', this);
        const monthInput = $('.entry-gate--month', this);
        const yearInput = $('.entry-gate--year', this);
        const submitBtn = $('.entry-gate-form__submit-btn', this);
        const errorMessage = $('.entry-gate-form__error-message', this);
        const link = $('.entry-gate__bottom-paragraph a', this);
    
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
          fieldset.removeClass('entry-gate-form__fieldset--error');
          errorMessage.css({display: 'none'})
          if (event.keyCode >= 48 && event.keyCode <= 57) {
            if (field.val().length > limit) {
              field.val(field.val().subString(0, limit));
            }
          }
          else {
            event.preventDefault();
          }
        }
    
        // display entry gate if cookie is not set or the value of cookie is not
        // enough
        isOldEnough(getCookieDate('dateOfBirth')) ? entryGate.css({display: 'none'}) : entryGate.css({display: 'flex'});
    
        dayInput.focus();
    
        dayInput.once('entryGate').on('keypress', e => checkValueLength(e, dayInput, 2));
        monthInput.once('entryGate').on('keypress', e => checkValueLength(e, dayInput, 2));
        yearInput.once('entryGate').on('keypress', e => checkValueLength(e, dayInput, 4));
    
        submitBtn.once('entryGate').on('click', event => {
          event.preventDefault();
          const givenDate = moment(`${yearInput.val()}-${monthInput.val()}-${dayInput.val()}`);
    
          if (!givenDate.isValid()) {
            // invalid date is entered
            fieldset.addClass('entry-gate-form__fieldset--error');
            errorMessage.css({display: 'block'})
            return;
          }
    
          if (!isOldEnough(givenDate)) {
            // under the age limit, show error overlay instead of entry gate
            entryGate.addClass('age-error');
            $('.entry-gate__error-link', this)[0].focus();
    
            $('.entry-gate__error-link', this)[0].onkeydown = function(e) {
              if (e.code === 'Tab' && e.shiftKey) {
                  e.preventDefault();
                  $('.entry-gate__error-link', this)[1].focus();
              }
            }
    
            $('.entry-gate__error-link', this)[1].onkeydown = function(e) {
              if (e.code === 'Tab'  && !e.shiftKey) {
                e.preventDefault();
                $('.entry-gate__error-link', this)[0].focus();
              }
            }
    
            return;
          }
    
          // over the age limit, set cookie and hide entry gate
          document.cookie = `dateOfBirth=${givenDate.format('YYYY-MM-DD')}; path=/`;
          entryGate.css({display: 'none'});
        })
      })
    },
  };
})(jQuery, Drupal)
