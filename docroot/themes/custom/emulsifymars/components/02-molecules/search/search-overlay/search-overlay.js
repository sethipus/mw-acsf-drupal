Drupal.behaviors.searchOverlay = {
  attach(context) {
      //$('.search-autocomplete-wrapper').hide();
      $('.inline-search').click(function () {
      $('.search-autocomplete-wrapper:visible').slideUp(250, function (){
        $('.search-autocomplete-wrapper').removeClass('opened');
      });
      $('.search-autocomplete-wrapper:hidden').slideDown(250, function (){
        $('.search-autocomplete-wrapper').addClass('opened');
      });
      return false;
    });
  }
};
