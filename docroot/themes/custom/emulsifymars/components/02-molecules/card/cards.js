Drupal.behaviors.articleCard = {
    attach(context) {
        const articleContainers = context.querySelectorAll('.article-card');
        const productContainers = context.querySelectorAll('.product-card');
        const recipeContainers = context.querySelectorAll('.recipe-card');
        const recommendationsContainers = context.querySelectorAll('.recommendations-card');

        const cardClickListener = (element, type) => {
            element.addEventListener('click', (event) => {
                const item = event.target.closest('a');
                if(item && dataLayer && typeof(dataLayer.push) === 'function') {
                    dataLayer.push({
                        event: item.dataset.event||'clickCards', 
                        componentName: `${type} Card`,
                        cardType: item.dataset.cardtype||`${type}`,
                        clickName: item.innerText.trim(),
                        cardDetails: {
                            articleName: item.dataset.carddetails||null
                        }
                    })
                }
            })
        }

        articleContainers.forEach(element => {
            cardClickListener(element, 'Article')
        });
        productContainers.forEach(element => {
            cardClickListener(element, 'Product')
        });
        recipeContainers.forEach(element => {
            cardClickListener(element, 'Recipe')
        });
        recommendationsContainers.forEach(element => {
            cardClickListener(element, 'Campaign')
        });
    }
}
