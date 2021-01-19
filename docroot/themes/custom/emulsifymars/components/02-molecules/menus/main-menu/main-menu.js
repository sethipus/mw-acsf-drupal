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
          header.find('.menu-item__heading--with-sub').on('click', e => {
            e.preventDefault();

            let subMenuOpened = e.currentTarget.nextElementSibling.classList.contains('main-menu--sub-open');

            // close all opened submenus
            $('.main-menu--sub-open').removeClass('main-menu--sub-open');
            $('.menu_chevron__icon').removeClass('main-menu--sub-open');

            if (!subMenuOpened) {
              // opening and closing submenu, rotating chevron
              e.currentTarget.nextElementSibling.classList.toggle('main-menu--sub-open');
              $(e.currentTarget).find('.menu_chevron__icon').toggleClass('main-menu--sub-open');
            }
          });
        }
      })($('#header-menu-trigger').once('menuInited'));
    },
  };
})(jQuery);
