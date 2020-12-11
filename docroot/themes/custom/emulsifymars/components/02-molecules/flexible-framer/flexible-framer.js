(function ($, Drupal) {
  Drupal.behaviors.flexibleFramer = {
    attach(context) {
      $(context).find('.card__heading-link').each(function() {
        $(this).html(
          this.innerHTML
            .replace(/\s\s+/g, ' ') // Replace multiple spaces with one,
            .replace(/^\s/, '') // remove spaces before title,
            .replace(' ', '<br />') // insert line-break after first word.
        );
      });
    }
  };
})(jQuery, Drupal);
