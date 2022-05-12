(function ($) {
  let scriptLoadInProgress = false;

  function _lazyLoadCookieBanner() {

    if ($('#onetrust-sdk').length > 0 && !scriptLoadInProgress) {
      scriptLoadInProgress = true;
      $('#onetrust-sdk').attr('src', $('#onetrust-sdk').attr('data-src'));
      $.getScript($('#onetrust-sdk').attr('data-src'));
    }

  }

  const getCookieData = name => {
    const cookieArr = document.cookie.split(";");
    for (let i = 0; i < cookieArr.length; i++) {
      const cookiePair = cookieArr[i].split("=");
      if (name === cookiePair[0].trim()) {
        return decodeURIComponent(cookiePair[1]);
      }
    }
    return null;
  };

  window.onload = () => {
    if (getCookieData('OptanonAlertBoxClosed') == null && false) {
      $('.cookie-parent-div').slideDown('slow');
      $('.cookie-parent-div').css('display', 'flex');

      $('#cookie-banner-settings').click(() => {
        $(this).attr("disabled", "disabled");
        _lazyLoadCookieBanner(true);
      });

      $('#onetrust-close-btn-container').click(() => {
        $('.cookie-parent-div').slideUp('slow');
      });

      $('.cookie-banner-close-button').click(() => {
        closeCookieBanner();
      });

      $('#onetrust-accept-btn-handler').click(() => {
        closeCookieBanner();
      });
    }

    $('#onetrust-banner-sdk').on('load', addImageBorder());
  };

  const closeCookieBanner = () => {
    document.cookie = "OptanonAlertBoxClosed=" + new Date().toISOString();
    $('.cookie-parent-div').slideUp('slow');
  }

  const addImageBorder = () => {
    const $oneTrust = document.querySelector('#onetrust-consent-sdk');
    const $banner = document.querySelector('#onetrust-banner-sdk');

    if ($oneTrust === null || $banner === null) {
      // If the banner hasn't loaded, call again in a second.
      setTimeout(() => {
        addImageBorder()
      }, 1000);
      return;
    }

    const $bannerImageBorder = document.querySelector('.cookie-banner__border');

    if ($bannerImageBorder) {
      $banner.insertAdjacentElement('afterbegin', $bannerImageBorder);
    }
    $oneTrust.dataset.theme = "drupal";
    $oneTrust.classList.add('--loaded');
    // Global Cookie Banner
    const $oneTrust_override = document.querySelector('.global-cookie-banner');
    if ($oneTrust_override) {
      $oneTrust.classList.add('global-cookie');
    }
  }
})(jQuery);
