Drupal.behaviors.ajaxcardgrid = {
  attach(context) {

    const seeMoreBtn = document.querySelector('.ajax-card-grid__more-link .default-link');
    seeMoreBtn.addEventListener('click', (event) => {
        event.preventDefault();
      }
    );

  },
};
