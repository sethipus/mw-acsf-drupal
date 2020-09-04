Drupal.behaviors.recipeBody = {
  attach: function (context) {
    const _this = this;
    let productUsedPinned = false;

    productUsedPinned = this.adaptProductUsedBlock(productUsedPinned);

    window.onresize = function(event) {
      productUsedPinned = _this.adaptProductUsedBlock(productUsedPinned);
    };
  },

  adaptProductUsedBlock: function (productUsedPinned) {
    const smallScreen = window.innerWidth < 1440;

    if (smallScreen && productUsedPinned) {
      console.log('adapt to small screen');
      let productUsed = document.querySelector('.product-used');
      productUsed.setAttribute('style', 'margin-top: 0;');
      return false;
    } else if (!smallScreen && !productUsedPinned) {
      console.log('adapt to wide screen');
      let adjacentElement = document.querySelector('.recipe-info');
      let productUsed = document.querySelector('.product-used');
      productUsed.setAttribute('style', 'margin-top: -' + ( adjacentElement.offsetHeight + 30 ) + 'px;');
      return true;
    }

    return productUsedPinned;
  },
};
