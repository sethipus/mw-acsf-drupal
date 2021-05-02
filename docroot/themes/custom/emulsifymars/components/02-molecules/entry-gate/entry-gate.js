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
        const firstInputElement = $('.entry-gate-form__input', this)[0];

        firstInputElement.onkeydown = function(e) {
          if ((e.code === 'Tab' && e.shiftKey) || (e.code === 'ArrowLeft' && e.ctrlKey)) {
              e.preventDefault();
              link.focus();
          }
        };

        link.onkeydown = function(e) {
          if ((e.code === 'Tab'  && !e.shiftKey) || (e.code === 'ArrowRight' && e.ctrlKey)) {
            e.preventDefault();
            firstInputElement.focus();
          }
        };

        // helper for getting cooke with specified name
        const getCookieDate = name => {
          const cookieArr = document.cookie.split(";");
          for (let i = 0; i < cookieArr.length; i++) {
            const cookiePair = cookieArr[i].split("=");
            if (name === cookiePair[0].trim()) {
              return decodeURIComponent(cookiePair[1]);
            }
          }
          return null;
        };

        const isValidDate = (dateStr) => {
          // assume dateStr = 'yyyy-m[m]-d[d]'
          const [year, month, day] = dateStr.split('-').map((p) => parseInt(p, 10));
          const d = new Date(dateStr);
          return (d && (d.getMonth() + 1) === month && d.getDate() === day && d.getFullYear() === year);
        };

        // compare cookie value against age limit
        const isOldEnough = (dateStr) => {
          if (dateStr && isValidDate(dateStr)) {
            const dob = new Date(dateStr);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            if (today.getMonth() < dob.getMonth() || (today.getMonth() === dob.getMonth() && today.getDate() < dob.getDate())) {
              age--;
            }
            return (age >= ageLimit);
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

          // Add fade in animation after entry-gate render
          setTimeout(function() {
            entryGate.addClass('entry-gate--loaded');
          }, 0);
        }

        firstInputElement.focus();

        dayInput.once('entryGate').on('keypress', e => checkValueLength(e, dayInput, 2));
        monthInput.once('entryGate').on('keypress', e => checkValueLength(e, monthInput, 2));
        yearInput.once('entryGate').on('keypress', e => checkValueLength(e, yearInput, 4));

        submitBtn.once('entryGate').on('click', event => {
          event.preventDefault();
          const givenDateStr = `${yearInput.val()}-${monthInput.val()}-${dayInput.val()}`;

          if (!isValidDate(givenDateStr) || new Date(givenDateStr).getFullYear() < 1900) {
            // invalid date is entered
            fieldset.addClass('entry-gate-form__fieldset--error');
            errorMessage.css({display: 'block'})
            return;
          }

          if (!isOldEnough(givenDateStr)) {
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
                firstInputElement.focus();
              }
            };
            return;
          }

          // over the age limit, set cookie and hide entry gate
          document.cookie = `dateOfBirth=${givenDateStr}; path=/`;
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
