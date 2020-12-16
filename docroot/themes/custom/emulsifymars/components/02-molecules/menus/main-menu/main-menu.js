import { disableBodyScroll, enableBodyScroll } from 'body-scroll-lock';

(function($) {
  Drupal.behaviors.mainMenu = {
    attach(context) {
      (function(header) {
        if (header.length) {
          $('#toggle-expand').on('click', e => {
            e.preventDefault();
            e.currentTarget.classList.toggle('toggle-expand--open');
            $('#header-menu-trigger').toggleClass('header__primary--open');
            $('#header-menu-trigger').hasClass('header__primary--open') ? disableBodyScroll(document.querySelector('#header-menu-trigger')) : enableBodyScroll(document.querySelector('#header-menu-trigger'));
          });
          header.find('.main-menu__link--with-sub').on('click', e => {
            e.preventDefault();
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