(function ($) {
  Drupal.behaviors.searchOverlay = {
    attach(context) {
      const _this = this;

      searchblock = _this.searchAdapt();

      $(window).on('resize', function (event) {
        searchblock = _this.searchAdapt();
      });

      // Click on close button for desktop.
      $('.search-autocomplete-wrapper .inline-search--closebtn').click(function (event){
        $('.search-autocomplete-wrapper:visible').slideUp(250, function (){
          $(this).removeClass('opened').find('.search-field-wrapper').removeClass('suggested');
          $('.mars-autocomplete-field').val('');
        });
        event.preventDefault();
        event.stopPropagation();
      });

      // Show overlay when Search button clicked.
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

      $(document).click(function(event){
        if ($(event.target).parents('.search-field-wrapper.suggested').length == 0) {
          $('.search-field-wrapper.suggested').removeClass('suggested');
        }

       var parent =  $('.search-autocomplete-wrapper:visible').parent().attr('class');
        if (parent == 'header__inner' && $(event.target).parents('.search-autocomplete-wrapper').length == 0) {
          $('.header__inner .search-autocomplete-wrapper').slideUp(250, function () {
            $(this).removeClass('opened').find('.search-field-wrapper').removeClass('suggested');
            $('.mars-autocomplete-field').val('');
          });
        }

        event.stopPropagation();
      });

      $('.toggle-expand__icon--close').click(function (event){
        $('.mars-autocomplete-field').val('');
      });

    },
    searchAdapt: function () {
      const smallScreen = window.innerWidth < 768;
      if (smallScreen) {
        // Mobile view
        // Move container to .header__menu--secondary-mobile
        if ($('.header__menu--secondary-mobile').find('.search-autocomplete-wrapper').length == 0) {
          $('.search-autocomplete-wrapper').removeClass('opened').removeClass('suggested').prependTo('.header__menu--secondary-mobile');
        }
      } else {
        // Desktop view.
        /// Check if element positioned correctly.
        if ($('.header__inner').children('.search-autocomplete-wrapper').length == 0) {
          $('.search-autocomplete-wrapper').removeClass('opened').removeClass('suggested').hide().appendTo('.header__inner');
        }
      }
    }
  };
})(jQuery);
