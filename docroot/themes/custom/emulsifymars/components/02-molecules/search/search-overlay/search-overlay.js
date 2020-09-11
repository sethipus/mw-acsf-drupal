Drupal.behaviors.searchOverlay = {
  attach(context) {
    $('.inline-search').click(function () {
      $('.inline-search--closebtn').toggleClass('inline-search--hidden');
      $('.inline-search--searchbtn').toggleClass('inline-search--hidden');
      $('.search-autocomplete-wrapper').slideToggle(250);
    });
  }
};
