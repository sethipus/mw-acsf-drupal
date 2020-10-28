(function($) {
  Drupal.behaviors.dropdown = {
    attach(context) {
      let dropdown = '.dropdown',
        dropdownTrigger = '.dropdown__trigger',
        expandedDropdown = '.dropdown.is-expanded',
        dropdownContent = '.dropdown__content--outer';

      var hideMenus = function() {
        $(expandedDropdown).each(function(index, element) {
          $(element).removeClass('is-expanded').find(dropdownTrigger).attr('aria-expanded', 'false');
        });
      };

      var showMenu = function(target) {
        if (!target) return false;

        hideMenus();
        $(target).addClass('is-expanded').find(dropdownTrigger).attr('aria-expanded', 'true');
      };

      var toggleMenu = function(target) {
        if (!target) return false;

        if ($(target).hasClass('is-expanded')) {
          hideMenus();
        } else {
          showMenu(target);
        }
      };

      var listenForMouse = function() {
        $(dropdown).once('dropDown').on('mouseenter', function(event) {
          showMenu(event.currentTarget);
        }).on('mouseleave', function() {
          hideMenus();
        }).on('click', function(event) {
          toggleMenu(event.currentTarget);
        });
      };

      var listenForKeyboard = function() {
        $(dropdown).once('dropDownKeyboard').on('focus', dropdownTrigger, function(e) {
          const target = $(e.currentTarget).parents(dropdown);

          hideMenus();
          showMenu(target);
        }).on('focusout', dropdownContent, function(e) {
          setTimeout(function() { // 'focusout' workaround
            if ($(':focus').closest(dropdown).length === 0) {
              hideMenus();
            }
          }, 0);
        });
      };

      if (!Drupal.dropDownLoaded) {
        Drupal.dropDownLoaded = true;
        hideMenus();
      }

      listenForMouse();
      listenForKeyboard();

    },
  };
})(jQuery);
