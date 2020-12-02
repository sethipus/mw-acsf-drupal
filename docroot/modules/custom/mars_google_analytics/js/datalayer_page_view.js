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
            var clickName = card.dataset.siteSearchClicked;
            if (item !== null) {
              clickName = item.innerText.trim();
            }
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
                componentName: componentName,
                clickDetails: link.href
              })
            }, 100);
          });
        }
      });

      // POLL MOUSEDOWN EVENT
      const pollContainer = context.querySelector('.polling');
      if (pollContainer) {
        const pollInputs = pollContainer.querySelectorAll('input');
        const pollSubmit = pollContainer.querySelector('.button-vote');
        // Add event listeners to provide info to Data layer
          setTimeout(function() {
            pollSubmit.addEventListener('mousedown', () => {
              // find what radio button was selected
              const selectedInput = [...pollInputs].filter(input => input.checked)[0];
              if (selectedInput) {
                dataLayer.push({
                  pageName: document.title,
                  componentName: getComponentName(pollContainer),
                  formSubmitFlag: 1,
                  formName: 'Poll',
                  formSelected: selectedInput.parentElement.innerText.trim()
                });
              }

            });
          }, 100);
      }

      // CONTACT US CLICK EVENT
      const contactUsContainer = context.querySelector('.ff-form-main');
      if (contactUsContainer) {
        const contactUsForm = contactUsContainer.closest('form');
        const contactUsSubmit = contactUsForm.querySelector('#btnsubmit');
        setTimeout(function() {
          contactUsSubmit.addEventListener('click', () => {
            // find what fields of the form has value button was selected
            const populatedFields = [...contactUsForm.elements].filter(field => field.dataset.ishidden === 'false' && field.value !== '');
            let selectedValues = [];
            for (let i=0; i < populatedFields.length; i++) {
              const currentField = populatedFields[i];
              selectedValues[currentField.name] = populatedFields.value;
            }
            dataLayer.push({
              pageName: document.title,
              componentName: getComponentName(pollContainer),
              formSubmitFlag: 1,
              formName: 'Contact & Help',
              formSelected: selectedValues
            });
          });
        }, 100);
      }


      // ENTRY GATE CLICK EVENT
      const entryGateContainer = context.querySelector('.entry-gate__inner');
      if (entryGateContainer) {
        const entryGateSubmit = entryGateContainer.querySelector('.entry-gate-form__submit-btn');
        // Add event listeners to provide info to Data layer
        setTimeout(function() {
          entryGateSubmit.addEventListener('click', () => {
            const birthInputs = Array.from(entryGateContainer.querySelectorAll('input'));
            const birthInputValues = birthInputs.map(el => el.value);
            if (birthInputValues) {
              dataLayer.push({
                pageName: document.title,
                componentName: getComponentName(entryGateContainer),
                formSubmitFlag: 1,
                formName: 'Entry gate',
                formSelected: birthInputValues
              });
            }
          });
        }, 100);
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
