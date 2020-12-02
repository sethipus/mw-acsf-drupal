(function($) {
  Drupal.behaviors.mainMenu = {
    attach(context) {
      (function(header) {
        if (header.length) {
          $('#toggle-expand').on('click', e => {
            e.currentTarget.classList.toggle('toggle-expand--open');
            $('#header-menu-trigger').toggleClass('header__primary--open');
            e.preventDefault();
          });
          header.find('.main-menu__link--trigger').on('click', e => {
            if ($(window).width() < 1024) {
              e.preventDefault();
            }
            const menuItem = e.currentTarget;
            const subMenu = menuItem.nextElementSibling;
            subMenu.classList.toggle('main-menu--sub-open');
            subMenu.nextElementSibling.classList.toggle('main-menu--sub-open');
          });
        }
      })($('#header-menu-trigger').once('menuInited'));
    },
  };
})(jQuery);