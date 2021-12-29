(function($, Drupal){
  Drupal.behaviors.entryGate = {
    attach(context) {
      var dateSelect;
      $(context).find('.entry-gate').once('entryGate').each(function(){
        const entryGate = $(this);
        const ageLimit = entryGate.data('age');
        const fieldset = $('.entry-gate-form__fieldset', this);
        const dayInput = $('.entry-gate--day', this);
        const monthInput = $('.entry-gate--month', this);
        const yearInput = $('.entry-gate--year', this);
        const submitBtn = $('.entry-gate-form__submit-btn', this);
        const errorMessage = $('.entry-gate-form__error-message', this);
        const link = $('.entry-gate__bottom-paragraph a', this).length > 0 ? $('.entry-gate__bottom-paragraph a', this).last() : submitBtn;
        const a11yDataAttrName = 'data-a11y-block-tabbable';
        const a11yDateFakeLinkId = 'a11y-entry-gate-first-link';
        const firstInputElement = $('.entry-gate-form__input', this).first();
        const dateFormat = fieldset.data('date-format');
        firstInputElement.on('keydown', function (e) {
          if ((e.code === 'Tab' && e.shiftKey) || (e.code === 'ArrowLeft' && e.ctrlKey)) {
            e.preventDefault();
            link.focus();
          }
        });

        link.on('keydown', function (e) {
          if ((e.code === 'Tab'  && !e.shiftKey) || (e.code === 'ArrowRight' && e.ctrlKey)) {
            e.preventDefault();
            firstInputElement.focus();
          }
        });

        // helper for lazyloading external scripts
        const lazyLoadThirdpartyScripts = () => {
          try { _lazyLoadWhereToBuy(); } catch(e) {}
          try { _lazyLoadCookieBanner(); } catch(e) {}
        }

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
          // assume dateStr = 'yyyy-mm-dd'
          if (dateFormat == 'mm_yyyy') {
            var [year, month, dd] = dateStr.split('-').map((p) => parseInt(p, 10));
            var day = 15;
            dateSelect = year + '-' + month + '-' + day;
          }
          else{
            var [year, month, day] = dateStr.split('-').map((p) => parseInt(p, 10));
          }
          const d = new Date(dateStr);
          const monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];
          let isDayValid, isMonthValid, isYearValid;

          isMonthValid = month > 0 && month < 13;
          isDayValid = ((year % 4) == 0 && (month == 2)) ? (day > 0 && day <= 29) // if year is leap
            : ( isMonthValid ? (day > 0 && day <= monthLength[month - 1]) : (day > 0 && day <= 31)); // if month is not valid then day should be > 0 and <= 31
          isYearValid = year >= 1900 && year <= new Date().getFullYear();
          if (!isDayValid) {
          $('#day.entry-gate-form__input').addClass('entry-gate-form__input--invalid');
          }

          if (!isMonthValid) {
          $('#month.entry-gate-form__input').addClass('entry-gate-form__input--invalid');
          }

          if (!isYearValid) {
          $('#year.entry-gate-form__input').addClass('entry-gate-form__input--invalid');
          }

          return isDayValid && isMonthValid && isYearValid;
        };

        // compare cookie value against age limit
        const isOldEnough = (dateStr) => {
          if (dateStr && isValidDate(dateStr)) {
            if(dateFormat == 'mm_yyyy') {
              dateStr = dateSelect;
            }
            const dob = new Date(dateStr);
            const today = new Date();
            let age = today.getFullYear() - dob.getUTCFullYear();
            if (today.getMonth() < dob.getUTCMonth() || (today.getMonth() === dob.getUTCMonth() && today.getDate() < dob.getUTCDate())) {
              age--;
            }
            return (age >= ageLimit);
          }
          return false;
        };
        // allow only numbers and non-printable keys (Ctrl, Alt, Tab etc.)
        const checkValueLength = (event, field, limit) => {
          fieldset.removeClass('entry-gate-form__fieldset--error');
          errorMessage.css({display: 'none'})
          $('.entry-gate-form__input--invalid').removeClass('entry-gate-form__input--invalid');
          if (((/[0-9]/.test(event.key)) && (field.val().length >= limit)) ||
          (/^[a-z!"#$%&'()*+,.\/:;<=>?@\[\] ^_`{|}~-]*$/.test(event.key))) { event.preventDefault(); }
        };

        // display entry gate if cookie is not set or the value of cookie is not
        // enough
        if (isOldEnough(getCookieDate('dateOfBirth'))) {
          // Lazy load scripts
          //lazyLoadThirdpartyScripts();
          entryGate.css({display: 'none'});
          $(document).trigger("popupClosed.entryGate");
          entryGate.attr("data-popup-opened", false);
          entryGate.attr("aria-hidden", "true");
          $(".layout-container").attr("aria-hidden", "false");
        } else {
          let _tabElems = ['a', 'button', 'input', 'textarea', 'select', 'details', '[tabindex]'];
          entryGate.css({display: 'flex'});
          $(document).trigger("popupOpened.entryGate");
          entryGate.attr("data-popup-opened", true);
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

        dayInput.once('entryGate')
          .keydown(function(e) {
            if (e.keyCode == 13) {
              // move focus on "enter keyDown" and prevent sending of the form
              (dateFormat == 'mm_dd') ? yearInput.focus() : monthInput.focus();
              e.preventDefault();
            } else {
              checkValueLength(e, dayInput, 2)
            }
          })
          .keyup(function(e) {
            // Move focus if field is full and digit has just been printed
            if ((dayInput.val().length == 2) && (/[0-9]/.test(e.key))) {
              (dateFormat == 'mm_dd') ? yearInput.focus() : monthInput.focus();
            }
          });

        monthInput.once('entryGate')
          .keydown(function(e) {
            if (e.keyCode == 13) {
              // move focus on "enter keyDown" and prevent sending of the form
              (dateFormat == 'mm_dd') ? dayInput.focus() : yearInput.focus();
              e.preventDefault();
            } else {
              checkValueLength(e, monthInput, 2);
            }
          })
          .keyup(function (e) {
            // Move focus if field is full and digit has just been printed
            if ((monthInput.val().length == 2) && (/[0-9]/.test(e.key))) {
              (dateFormat == 'mm_dd') ? dayInput.focus() : yearInput.focus();
            }
          });

        yearInput.once('entryGate')
          .keydown( e => checkValueLength(e, yearInput, 4));

        submitBtn.once('entryGate').on('click', event => {
          event.preventDefault();
          // Lazy load scripts
          //lazyLoadThirdpartyScripts();
          var givenDateStr = `${yearInput.val()}-${('0'+monthInput.val()).slice(-2)}-${('0'+dayInput.val()).slice(-2)}`;
          if(dateFormat == 'mm_yyyy') {
            var givenDateStr = `${yearInput.val()}-${('0'+monthInput.val()).slice(-2)}-${('0'+15).slice(-2)}`;
          }

          if (!isValidDate(givenDateStr)) {
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
          $(document).trigger("popupClosed.entryGate");
          entryGate.attr("data-popup-opened", false);
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

          $('#skip-link a').eq(0).focus();

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
