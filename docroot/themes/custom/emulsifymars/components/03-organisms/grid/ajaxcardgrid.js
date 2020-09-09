Drupal.behaviors.ajaxcardgrid = {
  attach(context) {

    const seeMoreBtn = document.querySelector('.ajax-card-grid__more-link .default-link');
    seeMoreBtn.addEventListener('click', (event) => {
      event.preventDefault();
      jQuery('.js-pager__items.pager li a.button').trigger('click');
    });

  },
};
