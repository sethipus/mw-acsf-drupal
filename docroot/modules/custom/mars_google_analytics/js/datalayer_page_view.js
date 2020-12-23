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

      var productElements = context.querySelectorAll('[data-datalayer-gtin]');
      var gtins = (settings.dataLayer.products) ? settings.dataLayer.products.split(', ') : [];
      productElements.forEach(function (product) {
        let gtin = product.getAttribute('data-datalayer-gtin');

        if (!gtins.includes(gtin)) {
          gtins.push(gtin);
        }
      });
      settings.dataLayer.products = gtins.join(', ');

      var dataElements = context.querySelectorAll('[data-datalayer-taxonomy]');
      var taxonomy = (settings.dataLayer.taxonomy !== null && settings.dataLayer.taxonomy !== 'null')
        ? JSON.parse(settings.dataLayer.taxonomy)
        : {};

      dataElements.forEach(function (product) {
        let taxonomy_info = JSON.parse(product.getAttribute('data-datalayer-taxonomy'));

        if (typeof taxonomy_info === 'object' && taxonomy_info != null) {
          for (const [key, value] of Object.entries(taxonomy_info)) {
            if (taxonomy.hasOwnProperty(key)) {
              let dif = value.filter(x => !taxonomy[key].includes(x));
              taxonomy[key] = taxonomy[key].concat(dif);
            }
            else {
              taxonomy[key] = value;
            }
          }
        }
      });

      var taxonomy_output = '';
      if (taxonomy !== null) {
        for (const [key, value] of Object.entries(taxonomy)) {
          taxonomy_output += key + ': ' + value.join(', ') + '; ';
        }
      }

      settings.dataLayer.taxonomy = taxonomy_output.trim();
      dataLayer.push(settings.dataLayer);

      // SEARCH START EVENT
      var searchInputs = document.querySelectorAll(".data-layer-search-form-input:not(.mars-autocomplete-field-faq):not(.mars-autocomplete-field-card-grid)");
      searchInputs.forEach(function (input) {
        input.addEventListener('focus', function () {
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
      var getComponentName = function (element) {
        const componentBlock = element.closest('[data-block-plugin-id]');
        var componentName = componentBlock ? componentBlock.dataset.blockPluginId : '';

        return componentName;
      }

      // CARDS CLICK EVENTS
      var cardContainers = context.querySelectorAll('.card-item');
      cardContainers.forEach(function (card) {
        card.addEventListener('click', function (event) {
          setTimeout(function () {
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
      if (header) {
        header.addEventListener('click', function (event) {
          setTimeout(function () {
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
      }

      // BOTTOM NAV EVENTS
      var footer = context.querySelector('footer');
      if (footer) {
        footer.addEventListener('click', function (event) {
          setTimeout(function () {
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
      }

      // EXTERNAL(outbound) LINKS CLICK EVENT
      const links = document.querySelectorAll('a');
      links.forEach((link) => {
        //Check if link is external and add listener
        if (link.href.indexOf(window.location.hostname) < 0) {
          link.addEventListener('click', (event) => {
            setTimeout(function () {
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

      var bindFormEvents = function(formContainer, formName) {
        var contactForm = formContainer.querySelector('form');
        // find what fields of the form has value button was selected
        Array.from(contactForm.elements).forEach((input) => {
          if (input.type === 'button' || input.type === 'submit') {
            input.addEventListener('mousedown', function(e) {
              if (formName === 'Entry Gate Form') {
                return;
              } 
              else if (
               formName === 'Contact & Help'
              ) {
                const config = { attributes: false, childList: true, subtree: false };
                const contactFormSubmitCallback = function(mutationsList, contactValidationObserver) {
                  for(const mutation of mutationsList) {
                    if (
                      mutation.type === 'childList' &&
                      document.querySelector('.ff-ui-dialog-content') !== null &&
                      document.querySelector('.ff-ui-dialog-content').innerHTML.includes('Thank you for getting in touch')
                    ) {
                      dataLayer.push({
                        event: 'formSubmit',
                        pageName: document.title,
                        componentName: 'contact_form',
                        formName: 'Contact & Help',
                      });
                      break;
                    }
                  }
                  contactValidationObserver.disconnect();
                };
                const contactValidationObserver = new MutationObserver(contactFormSubmitCallback);
                contactValidationObserver.observe(document, config);
              } 
              else if (formName === 'Poll Form') {
                dataLayer.push({
                  event: 'formSubmit',
                  pageName: document.title,
                  componentName: getComponentName(formContainer),
                  formName: formName,
                });
              }
            });
          }
          else {
            input.addEventListener('blur', function (e) {
              var fieldName = e.target.name;
              var fieldValue = e.target.value;
              if (/\S+@\S+\.\S+/.test(e.target.value)) {
                fieldValue = '';
              }
              if (formName === 'Poll Form') {
                fieldName = e.target.closest('div').querySelector('.polling__label-text').innerHTML;
                fieldValue = 'chacked';
              }
              dataLayer.push({
                event: 'formFieldComplete',
                pageName: document.title,
                componentName: getComponentName(formContainer),
                formName: formName,
                formFieldName: fieldName,
                formFieldValue: fieldValue,
              });
            });
          }
        });
      }

      // CONTACT US CLICK EVENT
      const formContainer = context.querySelector('.form-integration');
      if (formContainer) {
        // Options for the observer (which mutations to observe)
        const config = { attributes: false, childList: true, subtree: false };
        // Callback function to execute when mutations are observed
        const contactFormCallback = function(mutationsList, observer) {
          // Use traditional 'for loops' for IE 11
          for(const mutation of mutationsList) {
            if (mutation.type === 'childList') {
              bindFormEvents(formContainer, 'Contact & Help');
              observer.disconnect();
              break;
            }
          }
        };
        // Create an observer instance linked to the callback function
        const observer = new MutationObserver(contactFormCallback);
        // Start observing the target node for configured mutations
        observer.observe(formContainer.querySelector('#dvFastForms'), config);
      }

      // POLL MOUSEDOWN EVENT
      context.querySelectorAll('.poll-view').forEach(function (poll) {
        bindFormEvents(poll, 'Poll Form');
      });

      // ENTRY GATE CLICK EVENT
      const entryGateContainer = context.querySelector('.entry-gate__inner');
      if (entryGateContainer) {
        bindFormEvents(entryGateContainer, 'Entry Gate Form');
      }

    }
  };
})(jQuery, Drupal, drupalSettings);
