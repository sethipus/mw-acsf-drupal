import { disableBodyScroll, enableBodyScroll } from 'body-scroll-lock';

(function($) {
  Drupal.behaviors.mainMenu = {
    attach(context) {
      (function(headerMobile) {
        if (headerMobile.length) {
          $('#toggle-expand').on('click', e => {
            e.preventDefault();
            e.currentTarget.classList.toggle('toggle-expand--open');
            $('#header-menu-trigger').toggleClass('header__primary--open');
            $('#header-menu-trigger').hasClass('header__primary--open') ? disableBodyScroll(document.querySelector('#header-menu-trigger')) : enableBodyScroll(document.querySelector('#header-menu-trigger'));
          });
          headerMobile.find('.main-nav__mobile .main-menu__link--with-sub').on('click', e => {
            e.preventDefault();
            const menuItem = e.currentTarget;
            const subMenu = menuItem.nextElementSibling;
            subMenu.classList.toggle('main-menu--sub-open');
            subMenu.nextElementSibling.classList.toggle('main-menu--sub-open');
          });
        }
      })($('#header-menu-trigger').once('mobileMenuInited'));

      (function($headerDesktop) {
        if ($headerDesktop.length) {
          const hideSubMenu = function ($subMenuItem) {
            $subMenuItem.removeClass('main-menu__item--opened');
            $subMenuItem.find('.main-menu__link--with-sub').attr('aria-expanded', 'false');
          };

          const showSubMenu = function ($subMenuItem) {
            $subMenuItem.addClass('main-menu__item--opened');
            $subMenuItem.find('.main-menu__link--with-sub').attr('aria-expanded', 'true');
          };

          const toggleSubMenu = function ($subMenuItem) {
            if ($subMenuItem.hasClass('main-menu__item--opened')) {
              hideSubMenu($subMenuItem);
            }
            else {
              showSubMenu($subMenuItem);
            }
          };

          $headerDesktop.find('.main-menu__item--with-sub').each(function() {
            const $subMenuItem = $(this);
            $subMenuItem
              .on('mouseenter', function (event) {
                showSubMenu($subMenuItem);
              })
              .on('mouseleave', function (event) {
                hideSubMenu($subMenuItem);
              })
              .on('click','.main-menu__link--with-sub', function (event) {
                event.preventDefault();
                toggleSubMenu($subMenuItem);
              })
              .on('focusout', function () {
                setTimeout(function () {  // 'focusout' workaround
                  if ($subMenuItem.find(':focus').length === 0) {
                    hideSubMenu($subMenuItem);
                  }
                });
              })
              .on('keydown', function (event) {
                if (event.key === 'Escape') {
                  hideSubMenu($subMenuItem);
                  $subMenuItem.find('.main-menu__link--with-sub').focus();
                }
              })
              .on('keydown', '.main-menu__link--with-sub', function (event) {
                if (event.key === 'ArrowDown') {
                  event.preventDefault();
                  showSubMenu($subMenuItem);
                  $subMenuItem.find('.main-menu--sub .main-menu__item--sub:first-child .main-menu__link--sub').focus();
                }
              })
              .on('keydown', '.main-menu__item--sub', function (event) {
                switch (event.key) {
                  case 'ArrowDown':
                    event.preventDefault();
                    if ($(event.target).parents('.main-menu__item--sub').is('.main-menu__item--sub:last-child')) {
                      $subMenuItem.find('.main-menu__item--sub:first').children('.main-menu__link--sub').focus();
                    } else {
                      $(event.target).parents('.main-menu__item--sub').next().children('.main-menu__link--sub').focus();
                    }
                    break;
                  case 'ArrowUp':
                    event.preventDefault();
                    if ($(event.target).parents('.main-menu__item--sub').is('.main-menu__item--sub:first-child')) {
                      $subMenuItem.find('.main-menu__item--sub:last-child').children('.main-menu__link--sub').focus();
                    } else {
                      $(event.target).parents('.main-menu__item--sub').prev().children('.main-menu__link--sub').focus();
                    }
                    break;
                }
              });

          });

        }
      })($('#main-nav-desktop').once('desktopMenuInited'));
    },
  };
})(jQuery);
