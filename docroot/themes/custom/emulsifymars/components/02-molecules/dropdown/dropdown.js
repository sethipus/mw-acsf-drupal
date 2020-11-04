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
          $dropdownTrigger
            .on('focus', function () {
              showMenu();
            })
          $dropdown
            .on('focusout', function () {
              setTimeout(function () {  // 'focusout' workaround
                if ($dropdown.find(':focus').length === 0) {
                  hideMenu();
                }
              });
            });
        };

        hideMenu();
        listenForMouse();
        listenForKeyboard();

      });
    }
  };
})(jQuery, Drupal);
