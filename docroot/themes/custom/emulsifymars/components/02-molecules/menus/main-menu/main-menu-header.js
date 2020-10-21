Drupal.behaviors.mainMenu = {
  attach(context) {
    const toggleExpand = document.getElementById('toggle-expand');
    const menu = document.querySelector('#main-nav-mobile:not(.inited)');
    if (menu) {
      const expandMenu = menu.getElementsByClassName('expand-sub');

      // Mobile Menu Show/Hide.
      toggleExpand.addEventListener('click', e => {
        toggleExpand.classList.toggle('toggle-expand--open');
        menu.classList.toggle('main-nav__mobile--open');
        e.preventDefault();
      });

      // Expose mobile sub menu on click.
      for (let i = 0; i < expandMenu.length; i += 1) {
        expandMenu[i].addEventListener('click', e => {
          const menuItem = e.currentTarget;
          const subMenu = menuItem.nextElementSibling;

          menuItem.classList.toggle('expand-sub--open');
          subMenu.classList.toggle('main-menu--sub-open');
        });
      }
      menu.classList.add('inited');
    }
  },
};
