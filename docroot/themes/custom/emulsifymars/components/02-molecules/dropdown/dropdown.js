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
    
    var showMenu = function(target, e) {
      if (!target) return false;

      hideMenus();
      $(target).addClass('is-expanded').find(dropdownTrigger).attr('aria-expanded','true');
    };
    
    var toggleMenu = function(target, e) {
      if (!target) return false;

      if ($(target).hasClass('is-expanded')) {
        hideMenus();
      } else {
        hideMenus();
        $(target).addClass('is-expanded').find(dropdownTrigger).attr('aria-expanded','true');
      }
    };
    
    var listenForMouse = function() {
      let target;
      
      $(dropdown).on('mouseenter', function(event) {
        target = $(event.currentTarget);
        
        if (target) {
          showMenu(target, event);
        }
      });
      
      $(dropdown).on('mouseleave', function(event) {
        hideMenus();  
      });
      
      $(dropdown).on('click', function(event) {
        target = $(event.currentTarget);
        if (target) {
          toggleMenu(target, event);
        }
      });
    };
    
    var listenForKeyboard = function() {
      let target;

      $(dropdown).on('focus', dropdownTrigger, function(e) {
        target = $(e.currentTarget).parents(dropdown);
        
        hideMenus();
        showMenu(target, e);
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
