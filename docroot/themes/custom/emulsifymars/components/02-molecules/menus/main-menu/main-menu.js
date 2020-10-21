Drupal.behaviors.mainMenu = {
  attach(context) {
    const toggleExpand = document.getElementById('toggle-expand');
    const header = document.querySelector('#header-menu-trigger:not(.inited)');
    if (header) {
      const expandHeader = header.getElementsByClassName('main-menu__link--with-sub');

      // Mobile Menu Show/Hide.
      toggleExpand.addEventListener('click', e => {
        toggleExpand.classList.toggle('toggle-expand--open');
        header.classList.toggle('header__primary--open');
        e.preventDefault();
      });

      // Expose mobile sub menu on click.
      for (let i = 0; i < expandHeader.length; i += 1) {
        expandHeader[i].addEventListener('click', e => {
          if (window.innerWidth < 1024) {
            e.preventDefault();
          }
          const menuItem = e.currentTarget;
          const subMenu = menuItem.nextElementSibling;
          subMenu.classList.toggle('main-menu--sub-open');
          subMenu.nextElementSibling.classList.toggle('main-menu--sub-open');
        });
      }
      header.classList.add('inited');
    }
  },
};
