(function($, Drupal){
  Drupal.behaviors.dropdown = {
    attach(context) {
      let dropdown = '.dropdown',
        dropdownTrigger = '.dropdown__trigger',
        expandedDropdown = '.dropdown.is-expanded',
        dropdownContent = '.dropdown__content--outer';

      var hideMenus = function() {
        $(expandedDropdown).each(function(index, element) {
          $(element).removeClass('is-expanded').find(dropdownTrigger).attr('aria-expanded','false');
        });
      };

      var showMenu = function(target) {
        if (!target) return false;

        hideMenus();
        $(target).addClass('is-expanded').find(dropdownTrigger).attr('aria-expanded','true');
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
        let target;

        $(dropdown).on('mouseenter', function(event) {
          target = $(event.currentTarget);

          if (target) {
            showMenu(target);
          }
        });

        $(dropdown).on('mouseleave', function(event) {
          hideMenus();
        });

        $(dropdown).on('click', function(event) {
          target = $(event.currentTarget);
          if (target) {
            toggleMenu(target);
          }
        });
      };

      var listenForKeyboard = function() {
        let target;

        $(dropdown).on('focus', dropdownTrigger, function(e) {
          target = $(e.currentTarget).parents(dropdown);

          hideMenus();
          showMenu(target);
        });

        $(dropdown).on('focusout', dropdownContent, function(e) {
          setTimeout(function () { // 'focusout' workaround
            if ($(':focus').closest(dropdown).length == 0) {
              hideMenus();
            }
          }, 0);
        });
      };

      //init
      hideMenus();
      listenForMouse();
      listenForKeyboard();
    },
  };
})(jQuery, Drupal);
