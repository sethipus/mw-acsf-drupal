Drupal.behaviors.mainMenu = {
  attach(context) {
    const toggleExpand = context.getElementById('toggle-expand');
    const header = context.getElementById('header-menu-trigger');
    if (header) {
      // const expandMenu = menu.getElementsByClassName('expand-sub');

      // Mobile Menu Show/Hide.
      toggleExpand.addEventListener('click', e => {
        toggleExpand.classList.toggle('toggle-expand--open');
        header.classList.toggle('header__primary--open');
        e.preventDefault();
      });

      // Expose mobile sub menu on click.
      // for (let i = 0; i < expandMenu.length; i += 1) {
      //   expandMenu[i].addEventListener('click', e => {
      //     const menuItem = e.currentTarget;
      //     const subMenu = menuItem.nextElementSibling;

      //     menuItem.classList.toggle('expand-sub--open');
      //     subMenu.classList.toggle('main-menu--sub-open');
      //   });
      // }
    }
  },
};
