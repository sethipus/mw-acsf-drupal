Drupal.behaviors.searchOverlay = {
  attach(context) {
    $('.inline-search').click(function () {
      $('.inline-search--closebtn').toggleClass('inline-search--hidden');
      $('.inline-search--searchbtn').toggleClass('inline-search--hidden');
     // $('.search-autocomplete-wrapper').toggleClass('opened').slideToggle(250);
      $('.search-autocomplete-wrapper').toggleClass('opened suggested').slideToggle(250);
      return false;
    });
  }
};
