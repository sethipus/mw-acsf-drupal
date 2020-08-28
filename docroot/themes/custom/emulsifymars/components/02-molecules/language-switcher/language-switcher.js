Drupal.behaviors.languageSwitcher = {
  attach(context) {
    const toggleExpand = context.getElementById('language-switcher-trigger');
    const languageContent = context.getElementById('language-switcher-content');
    
    if (toggleExpand) {
      toggleExpand.addEventListener('click', e => {
        toggleExpand.classList.toggle('language-switcher__trigger--open');
        languageContent.classList.toggle('language-switcher__content--open');
        e.preventDefault();
      });
    }
  },
};
