(function($){
  Drupal.behaviors.productSelector = {
    attach: function (context /*, settings */) {
      const _this = this;
      _this.context = context;

      /* Get Script data attributes from drupalSettings */
      // _this.settings = settings;

      _this.settings = {
        widgetid: '001',
        ean: '002',
        subid: '003'
      }

      _this.initEvents(context);
    },

    updateData: function (settings, productId, productTitle) {
      const _this = this;
      let data = {};
      let url = 'http://mars.ddev.site:8080/wtb/get_product_info/' + productId;

      data.title = productTitle;
      data.scriptDataAttributes = settings.scriptDataAttributes;

      // data.productVariants

      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function success(results) {
          data.productVariants = results;

          _this.render(_this.context, data);
        },
        error: function(){
          data.productVariants = [
            {
              "size": null,
              "image_src": "https://via.placeholder.com/450",
              "image_alt": null,
              "gtin": "00047677482760"
            },
            {
              "size": "8.67",
              "image_src": "https://via.placeholder.com/550",
              "image_alt": null,
              "gtin": "00047677391284"
            }
          ];

          _this.render(_this.context, data);
        }
      });
    },

    render: function (context, data) {
      let $title = $(context).find('.product-selector__title');
      let $image = $(context).find('.product-selector__image img');
      let $productVariantSelector = $(context).find('.product-selector__product-variant-selector');

      $title.html(data.title);
      $image.attr("src", data.image);

      $productVariantSelector.empty();

      $.each(data.productVariants, function(i, val) {
        debugger;
        $productVariantSelector.append('<option ' +
          + 'data-id="' + val.gtin + '"' +
          + 'data-image-src="' + val.image_src + '"' +
          + 'data-image-alt="' + val.image_alt + '"' +
          + 'value="' + val.size + '">' + val.size + '</option>')
      });
    },

    initEvents: function (context) {
      // $('.product-selector__item-selector').chosen();
      const _this = this;
      let $itemSelector = $(context).find('.product-selector__item-selector');

      $itemSelector.on('change', function() {
        let productId = $(this).find('option:selected').data("id");
        let productTitle = $(this).val();
        let data = _this.updateData(context, productId, productTitle);
      });
    },
  };
})(jQuery);
