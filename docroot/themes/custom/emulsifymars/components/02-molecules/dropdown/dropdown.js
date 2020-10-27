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

      var showMenu = function(target, e) {
        if (!target) return false;

        hideMenus();
        $(target).addClass('is-expanded').find(dropdownTrigger).attr('aria-expanded', 'true');
      };

      var listenForMouse = function() {
        $(dropdown).once('dropDown').on('mouseenter', function(event) {
          const target = $(event.currentTarget);

          if (target) {
            showMenu(target, event);
          }
        }).on('mouseleave', function() {
          hideMenus();
        });
      };

      var listenForKeyboard = function() {
        $(dropdown).once('dropDownKeyboard').on('focus', dropdownTrigger, function(e) {
          const target = $(e.currentTarget).parents(dropdown);

          hideMenus();
          showMenu(target, e);
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
