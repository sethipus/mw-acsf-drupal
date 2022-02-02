(function ($, Drupal) {
    Drupal.behaviors.homepageHeroBannerText = {
        attach(context) {
                const elements = document.body.querySelectorAll("*[style]")
                elements.forEach(element=> {
                    const breaks = document.querySelectorAll('br');
                    breaks.forEach(element2 => {
                        if(element.style.fontSize) {
                            const {value: size, unit} = element.attributeStyleMap.get('font-size')
                            element.attributeStyleMap.set('line-height', CSS[unit](Math.floor(size * 1)));
                            element2.style.display = 'none';
                            }  
                    })
                })
                
        }
    }
})(jQuery, Drupal);