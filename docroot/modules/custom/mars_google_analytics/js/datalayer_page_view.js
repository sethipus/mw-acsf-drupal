/**
 * @file
 * Javascript for the search related things.
 */

/**
 * dataLayer page view.
 */
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.dataLayerPageView = {
    attach: function (context, settings) {
      if (typeof dataLayer === 'undefined') {
        return;
      }

      // PAGE VIEW EVENT
      var body = context.querySelector('body');
      if (body === null || body.getAttribute('datalayer-page-view')) {
        console.log(settings);
        return;
      }
      dataLayer.push(settings.dataLayer);

      // SEARCH START EVENT
      var searchInputs = document.querySelectorAll('.data-layer-search-form-input');
      searchInputs.forEach(function (input) {
        input.addEventListener('focus', function() {
          // SITE SEARCH START
          dataLayer.push({
            'event': 'siteSearch_Start',
            'siteSearchTerm': '',
            'siteSearchResults': ''
          })
        });
      });
      body.setAttribute('datalayer-page-view', true);

      // COMPONENT NAME
      var getComponentName = function(element) {
        const componentBlock = element.closest('[data-block-plugin-id]');
        var componentName = componentBlock ? componentBlock.dataset.blockPluginId : '';

        return componentName;
      }

      // CARDS CLICK EVENTS
      var cardContainers = context.querySelectorAll('.card-item');
      cardContainers.forEach(function(card) {
        card.addEventListener('click', function(event) {
          setTimeout(function() {
            var item = event.target.closest('a');
            var clickName = item.innerText.trim() || card.dataset.siteSearchClicked;
            var componentName = getComponentName(event.target);
            var cardType = card.dataset.cardType;
            // CARD CLICK
            dataLayer.push({
              event: 'clickCards',
              componentName: componentName,
              cardType: cardType,
              clickName: clickName,
              clickDetails: card.dataset.siteSearchClicked
            });
          }, 100);
        })
      });

      // TOP NAV EVENTS
      var header = context.querySelector('header');
      header.addEventListener('click', function(event) {
        setTimeout(function() {
          var componentName = getComponentName(event.target);
          let link = event.target.closest('a');
          if (link) {
            var item = link.parentElement.className.trim().split(' ')[0];
            switch (item) {
              case 'header__logo':
                dataLayer.push({
                  event: 'clickHeader',
                  componentName: componentName,
                  clickType: 'Brand Logo',
                  clickName: 'Brand Logo',
                });
                break;
              case 'header-inline-menu__item':
                dataLayer.push({
                  event: 'clickHeader',
                  componentName: componentName,
                  clickType: 'Upper menu items',
                  clickName: event.target.innerText.trim(),
                });
                break;
              case 'main-menu__item':
                dataLayer.push({
                  event: 'clickHeader',
                  componentName: componentName,
                  clickType: 'Lower menu items',
                  clickName: event.target.innerText.trim(),
                });
                break;
              case 'dropdown__item':
                dataLayer.push({
                  event: 'clickHeader',
                  componentName: componentName,
                  clickType: 'Language selectors',
                  clickName: 'Language selectors',
                  clickDetails: event.target.innerText.trim()
                });
                break;
              case 'status__container':
                dataLayer.push({
                  event: 'clickHeader',
                  componentName: componentName,
                  clickType: 'Banner',
                  clickName: 'Banner',
                });
                break;
            }
          }
        }, 100);
      });

      // BOTTOM NAV EVENTS
      var header = context.querySelector('footer');
      header.addEventListener('click', function(event) {
        setTimeout(function() {
          var componentName = getComponentName(event.target);
          var link = event.target.closest('a');
          if (link) {
            var item = link.parentElement.className.trim().split(' ')[0];
            switch (item) {
              case 'footer__logo':
                dataLayer.push({
                  event: 'clickFooter',
                  componentName: componentName,
                  clickType: 'Brand Logo',
                  clickName: 'Brand Logo',
                });
                break;
              case 'footer-menu__item':
                dataLayer.push({
                  event: 'clickFooter',
                  componentName: componentName,
                  clickType: 'Upper menu items',
                  clickName: 'Upper menu items',
                });
                break;
              case 'legal-links-menu__item':
                dataLayer.push({
                  event: 'clickFooter',
                  componentName: componentName,
                  clickType: 'Lower menu items',
                  clickName: 'Lower menu items',
                });
                break;
              case 'social-menu__item':
                dataLayer.push({
                  event: 'clickFooter',
                  componentName: componentName,
                  clickType: 'Social icons',
                  clickName: 'Social icons',
                });
                break;
              case 'dropdown__item':
                dataLayer.push({
                  event: 'clickFooter',
                  componentName: componentName,
                  clickType: 'Region selectors',
                  clickName: 'Region selectors',
                  clickDetails: event.target.innerText.trim()
                });
                break;
            }
          }
        }, 100);
      });

      // EXTERNAL(outbound) LINKS CLICK EVENT
      const links = document.querySelectorAll('a');
      links.forEach((link) => {
        //Check if link is external and add listener
        if (link.href.indexOf(window.location.hostname) < 0) {
          link.addEventListener('click', (event) => {
            setTimeout(function() {
              const item = event.target.closest('a');
              const componentName = getComponentName(event.target);
              dataLayer.push({
                event: 'clickOutbound',
                clickName: item.innerText.trim(),
                componentName: componentName
              })
            }, 100);
          });
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
