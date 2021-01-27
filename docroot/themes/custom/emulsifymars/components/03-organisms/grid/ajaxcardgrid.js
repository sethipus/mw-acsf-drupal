(function(Drupal) {
  Drupal.behaviors.ajaxcardgrid = {
    attach(context, settings) {

      const seeMoreBtn = document.querySelector('.ajax-card-grid__more-link .default-link');
      seeMoreBtn.addEventListener('click', (event) => {
          event.preventDefault();
        }
      );

    },
  };
})(Drupal);
