Drupal.behaviors.siteFooter = {
    attach(context) {
        let footer = context.querySelector('footer');
        if(footer && dataLayer && typeof(dataLayer.push) === 'function') {
            footer.addEventListener('click', (event) => {
                let link = event.target.closest('a');
                if(link) {
                    let response = {
                        event: 'clickFooter',
                        componentName: 'siteFooter',
                        pageType: link.dataset.pagetype
                    };
                    let item = link.parentElement.className.trim().split(' ')[0];

                    switch(item) {
                        case 'footer__logo':
                            response = { ...response, 
                                clickType: 'Brand Logo',
                                clickName: 'Brand Logo',
                                clickDetails: null
                            }
                            break;
                        case 'footer-menu__item':
                            response = { ...response, 
                                clickType: 'Upper menu items',
                                clickName: 'Upper menu items',
                                clickDetails: null
                            }
                            break;
                        case 'legal-links-menu__item': 
                            response = { ...response, 
                                clickType: 'Lower menu items',
                                clickName: 'Lower menu items',
                                clickDetails: null
                            }
                            break;
                        case 'social-menu__item':
                            response = { ...response, 
                                clickType: 'Social icons',
                                clickName: 'Social icons',
                                clickDetails: null
                            }
                            break;
                        case 'dropdown__item':
                            response = { ...response, 
                                clickType: 'Region selectors',
                                clickName: 'Region selectors',
                                clickDetails: event.target.innerText.trim()
                            }
                            break;
                    }
                    dataLayer.push(response);
                }
            });
        }
    }
}
