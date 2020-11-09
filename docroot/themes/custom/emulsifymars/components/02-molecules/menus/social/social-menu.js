Drupal.behaviors.socialMenu = {
  attach(context, settings) {
    if (typeof dataLayer === 'undefined') {
     return;
    }
    const socialMenuContainer = context.querySelectorAll('.social-menu');  
    socialMenuContainer.forEach(element => {
      element.addEventListener('click', (event) => {
        setTimeout(function() {
          const item = event.target.closest('a');
          const clickName = item.querySelector('img').getAttribute('title');
          let componentName = '';
          const componentBlock = item.closest("[data-block-plugin-id]");
          if (typeof componentBlock !== 'undefined') {
            componentName = componentBlock.dataset.blockPluginId;
          }
          let result = {
            componentName: componentName,
          }
          
          if (clickName === 'Download' || clickName === 'Print') {
            if (componentName === 'recipe_detail_hero') {
              result.recipeName = context.querySelector('.recipe-header__text').innerText.trim();
              result.recipeTaxonomy = settings.dataLayer.taxonomy;
              result.event = `article${clickName}`;
            } 
            if (componentName === 'article_header') {
              result.articleName = context.querySelector('.heading').innerText.trim();
              result.articleTaxonomy = settings.dataLayer.taxonomy;
              result.event = `recipe${clickName}`;
            }
          } 
          else {
            result.event = 'click_Share';
            result.clickName = clickName;
          }
          dataLayer.push(result);
        }, 100);  
      })
    });
  }
}
