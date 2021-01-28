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
        const link = $('.entry-gate__bottom-paragraph a', this).length > 0 ? $('.entry-gate__bottom-paragraph a', this).last()[0] : submitBtn[0];
        const a11yDataAttrName = 'data-a11y-block-tabbable';
        const a11yDateFakeLinkId = 'a11y-entry-gate-first-link';

        dayInput[0].onkeydown = function(e) {
          if ((e.code === 'Tab' && e.shiftKey) || (e.code === 'ArrowLeft' && e.ctrlKey)) {
              e.preventDefault();
              link.focus();
          }
        };

        link.onkeydown = function(e) {
          if ((e.code === 'Tab'  && !e.shiftKey) || (e.code === 'ArrowRight' && e.ctrlKey)) {
            e.preventDefault();
            dayInput.focus();
          }
        };

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
        };

        // compare cookie value against age limit
        const isOldEnough = (date) => {
          if (moment.isMoment(date)) {
            return (moment(new Date()).diff(date, 'years')) >= ageLimit;
          }
          return false;
        };

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
        };

        // display entry gate if cookie is not set or the value of cookie is not
        // enough
        if (isOldEnough(getCookieDate('dateOfBirth'))) {
          entryGate.css({display: 'none'});
          entryGate.attr("aria-hidden", "true");
          $(".layout-container").attr("aria-hidden", "false");
        } else {
          let _tabElems = ['a', 'button', 'input', 'textarea', 'select', 'details', '[tabindex]'];

          entryGate.css({display: 'flex'});
          entryGate.attr("aria-hidden", "false");
          $(".layout-container").attr("aria-hidden", "true");

          $('.layout-container')
            .find(_tabElems.map(e => e + ':not([tabindex="-1"])').join(','))
            .each((i, e) => {
              $(e).attr(a11yDataAttrName, $(e).attr('tabindex') !== undefined ? $(e).attr('tabindex') : 'none')
                .attr('tabindex', '-1');
            });

          // Hack for key nav from OneTrust
          $('body').prepend(
            `<a href="#" class="sronly" id="${a11yDateFakeLinkId}"></a>`
          );

          $(`#${a11yDateFakeLinkId}`).on('focus', event => {
            entryGate.find('a, button, input').eq(0).focus();
          });
        }

        dayInput.focus();

        dayInput.once('entryGate').on('keypress', e => checkValueLength(e, dayInput, 2));
        monthInput.once('entryGate').on('keypress', e => checkValueLength(e, monthInput, 2));
        yearInput.once('entryGate').on('keypress', e => checkValueLength(e, yearInput, 4));

        submitBtn.once('entryGate').on('click', event => {
          event.preventDefault();
          const givenDate = moment(`${yearInput.val()}-${monthInput.val()}-${dayInput.val()}`);

          if (dayInput.val().length > 2 || 
              monthInput.val().length > 2 || 
              yearInput.val().length !== 4 ||
              !givenDate.isValid()) {
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
            };

            $('.entry-gate__error-link', this)[1].onkeydown = function(e) {
              if (e.code === 'Tab'  && !e.shiftKey) {
                e.preventDefault();
                $('.entry-gate__error-link', this)[0].focus();
              }
            };
            return;
          }

          // over the age limit, set cookie and hide entry gate
          document.cookie = `dateOfBirth=${givenDate.format('YYYY-MM-DD')}; path=/`;
          entryGate.css({display: 'none'});
          $(".layout-container").attr("aria-hidden", "false");
          entryGate.attr("aria-hidden", "true");

          $(`[${a11yDataAttrName}]`).each((i, e) => {
            if ($(e).attr(a11yDataAttrName) === "none") {
              $(e).removeAttr('tabindex');
            } else {
              $(e).attr("tabindex", $(e).attr(a11yDataAttrName));
            }
            $(e).removeAttr(a11yDataAttrName);
          });

          $(`#${a11yDateFakeLinkId}`).remove();

          $('#skip-link').focus();

          if (typeof dataLayer !== 'undefined') {
            dataLayer.push({
              event: 'formSubmit',
              pageName: document.title,
              componentName: 'entry-gate',
              formName: 'Entry Gate Form',
            });
          }
        });
      });
    },
  };
})(jQuery, Drupal);
