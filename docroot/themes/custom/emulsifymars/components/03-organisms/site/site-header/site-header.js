Drupal.behaviors.siteHeader = {
    attach(context) {
        let header = context.querySelector('header');
        if(header && dataLayer && typeof(dataLayer.push) === 'function') {
            header.addEventListener('click', (event) => {
                let link = event.target.closest('a');
                if(link) {
                    let response = {
                        event: 'clickHeader',
                        componentName: 'siteHeader'
                    };
                    let item = link.parentElement.className.trim().split(' ')[0];

                    switch(item) {
                        case 'header__logo':
                            response = { ...response, 
                                clickType: 'Brand Logo',
                                clickName: 'Brand Logo',
                                clickDetails: null
                            }
                            break;
                        case 'header-inline-menu__item':
                            response = { ...response, 
                                clickType: 'Upper menu items',
                                clickName: event.target.innerText.trim(),
                                clickDetails: null
                            }
                            break;
                        case 'main-menu__item': 
                            response = { ...response, 
                                clickType: 'Lower menu items',
                                clickName: event.target.innerText.trim(),
                                clickDetails: null
                            }
                            break;
                        case 'dropdown__item':
                            response = { ...response, 
                                clickType: 'Language selectors',
                                clickName: 'Language selectors',
                                clickDetails: event.target.innerText.trim()
                            }
                            break;
                        case 'status__container':
                            response = { ...response, 
                                clickType: 'Banner',
                                clickName: 'Banner',
                                clickDetails: null
                            }
                            break;
                    }
                    dataLayer.push(response);
                }
            });
        }
    }
}
