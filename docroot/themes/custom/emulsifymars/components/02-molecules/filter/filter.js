(function ($) {
    Drupal.behaviors.searchOverlay = {
        attach(context) {
            filter = context.querySelector('.filter-block');

            filter.addEventListener('click', (element) => {
                element.classList.toggle('filter-block__open');
            });
        }
    };
})(jQuery);
