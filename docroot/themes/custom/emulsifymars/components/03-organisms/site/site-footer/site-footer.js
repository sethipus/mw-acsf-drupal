Drupal.behaviors.siteFooter = {
    attach(context) {
        let footer = context.querySelector('footer');
        footer.addEventListener('click', (event) => {
            console.log(event.target);
            if(event.target.nodeName === 'A') {
                event.preventDefault();
                let response = {
                    event: 'clickFooter',
                    componentId: 1234,
                    componentName: 'siteFooter'
                };

                switch(event.target.parentElement.className) {
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
                console.log(response);
            }
        }, )
    }
}