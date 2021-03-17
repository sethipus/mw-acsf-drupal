(function ($, Drupal) {
  Drupal.behaviors.dropdown = {
    attach(context) {
      $(context).find('.dropdown').once('dropdown').each(function () {
        const $dropdown = $(this),
          $dropdownTrigger = $(this).find('.dropdown__trigger');

        const hideMenu = function () {
          $dropdown.removeClass('is-expanded');
          $dropdownTrigger.attr('aria-expanded', 'false');
        };

        const showMenu = function () {
          $dropdown.addClass('is-expanded');
          $dropdownTrigger.attr('aria-expanded', 'true');
        };

        const toggleMenu = function () {
          if ($dropdown.hasClass('is-expanded')) {
            hideMenu();
          }
          else {
            showMenu();
          }
        };

        const listenForMouse = function () {
          $dropdown
            .on('mouseenter', function () {
              showMenu();
            })
            .on('mouseleave', function () {
              hideMenu();
            })
            .on('click', function () {
              toggleMenu();
            });
        };

        const listenForKeyboard = function () {
          $dropdown
            .on('focusout', function () {
              setTimeout(function () {  // 'focusout' workaround
                if ($dropdown.find(':focus').length === 0) {
                  hideMenu();
                }
              });
            })
            .on('keyup', function (event) {

              if (event.keyCode == 13) {
                $dropdown.find('.dropdown__item--link:first').children('.dropdown__link').focus();
              }
              if (event.keyCode == 27) {
                hideMenu();
                $dropdownTrigger.focus();
              }
            })
            .on('keyup', '.dropdown__item', function (event) {

              if (event.keyCode == 40) {
                if ($(event.target).parents('.dropdown__item').is('.dropdown__item--link:last')) {
                  $dropdown.find('.dropdown__item--link:first').children('.dropdown__link').focus();
                } else {
                  $(event.target).parents('.dropdown__item').next().children('.dropdown__link').focus();
                }
              }
              if (event.keyCode == 38) {
                if ($(event.target).parents('.dropdown__item').is('.dropdown__item--link:first-child')
                  || $(event.target).parents('.dropdown__item').prev().is('.dropdown__item--label')) {
                  $dropdown.find('.dropdown__item--link:last').children('.dropdown__link').focus();
                } else {
                  $(event.target).parents('.dropdown__item').prev().children('.dropdown__link').focus();
                }
              }
            });
        };

        hideMenu();
        listenForMouse();
        listenForKeyboard();

      });
    }
  };
})(jQuery, Drupal);
