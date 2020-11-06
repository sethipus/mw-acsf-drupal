Drupal.behaviors.siteHeader = {
    attach(context) {
        const socialMenuContainer = context.querySelectorAll('.social-menu');

        socialMenuContainer.forEach(element => {
            element.addEventListener('click', (event) => {
                const item = event.target.closest('a');
                if (item && dataLayer && typeof(dataLayer.push) === 'function') {
                    const clickName = item.dataset.clickname;
                    const pageType = item.dataset.pagetype;

                    let result = {
                        componentName: item.dataset.componentname||'Social Menu',
                        pageName: context.title,
                    }

                    if (clickName === 'Download' || clickName === 'Print') {
                        result.event = `${pageType}_${clickName}`;
                        if(pageType === 'Recipe') {
                            result.recipeName = context.querySelector('.recipe-header__text').innerText.trim();
                        } else if (pageType === 'Article') {
                            result.articleName = context.querySelector('.heading').innerText.trim();
                        }
                    } else {
                        result.event = 'click_Share';
                        result.clickName = clickName;
                        result.clickDetails = pageType;
                    }
                    dataLayer.push(result);
                }
            })
        });
    }
}
