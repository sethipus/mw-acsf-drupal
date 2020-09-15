(function ($) {
  Drupal.behaviors.searchOverlay = {
    attach(context) {
       $('.header__menu--secondary .inline-search').click(function () {
        $('.search-autocomplete-wrapper:visible').slideUp(250, function (){
          $(this).removeClass('opened');
        });
        $('.search-autocomplete-wrapper:hidden').slideDown(250, function(){
          $(this).addClass('opened');
        });
        return false;
      });

      $('.header__menu--secondary-mobile .inline-search').click(function (){
        $(this).hide();
        $('.search-autocomplete-wrapper-mobile').slideDown(250, function (){
          $(this).addClass('opened');
        });
        return false;
      });

      $(document).click(function(event){
        if ($(event.target).parents('.search-autocomplete-wrapper-mobile').length == 0) {
          $('.header__menu--secondary-mobile .inline-search').show();
          $('.search-autocomplete-wrapper-mobile:visible').slideUp(250, function (){
            $(this).removeClass('opened');
          });
        }
        return false;
      });

    }
  };
})(jQuery);
