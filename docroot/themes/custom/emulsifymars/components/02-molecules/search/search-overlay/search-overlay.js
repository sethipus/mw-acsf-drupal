(function ($) {
  Drupal.behaviors.searchOverlay = {
    attach(context) {
       $('.header__menu--secondary .inline-search').click(function (event) {
        $('.search-autocomplete-wrapper:visible').slideUp(250, function (){
          $(this).removeClass('opened').find('.search-field-wrapper').removeClass('suggested');
        });
        $('.search-autocomplete-wrapper:hidden').slideDown(250, function(){
          $(this).addClass('opened');
        });
         event.preventDefault();
         event.stopPropagation();
      });

      $('.header__menu--secondary-mobile .inline-search').click(function (event){
        $(this).hide();
        $('.search-autocomplete-wrapper-mobile').slideDown(250, function (){
          $(this).addClass('opened');
        });
        event.preventDefault();
        event.stopPropagation();
      });

      $('.search-autocomplete-wrapper .inline-search--closebtn').click(function (event){
        $('.search-autocomplete-wrapper:visible').slideUp(250, function (){
          $(this).removeClass('opened').find('.search-field-wrapper').removeClass('suggested');
          $('.mars-autocomplete-field').val('');
        });
        event.preventDefault();
        event.stopPropagation();
      });

      $(document).click(function(event){
        if ($(event.target).parents('.search-autocomplete-wrapper-mobile').length == 0) {
          $('.header__menu--secondary-mobile .inline-search').show();
          $('.search-autocomplete-wrapper-mobile:visible').slideUp(250, function (){
            $(this).removeClass('opened').find('.search-field-wrapper').removeClass('suggested');
            $('.mars-autocomplete-field').val('');
          });
        }

        if ($(event.target).parents('.search-field-wrapper.suggested').length == 0) {
          $('.search-field-wrapper.suggested').removeClass('suggested');
        }
        event.stopPropagation();
      });

    }
  };
})(jQuery);
