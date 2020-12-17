(function($){
  Drupal.behaviors.productSelector = {
    attach: function (context, settings) {
      const _this = this;
      _this.context = context;

      _this.settings = {
        commerce_vendor: settings.wtb_block['commerce_vendor'],
        widget_id: settings.wtb_block['widget_id'],
        data_subid: settings.wtb_block['data_subid'],
        data_locale: settings.wtb_block['data_locale'],
        data_displaylanguage: settings.wtb_block['data_displaylanguage'],
        data_token: settings.wtb_block['data_token'],
      }

      _this.initEvents(context);
    },

    updateData: function (settings, productId, productTitle) {
      const _this = this;
      let data = {};
      let timestamp = Date.now();
      let url = '/wtb/get_product_info/' + productId + '?v=' + timestamp;

      data.title = productTitle;
      data.scriptDataAttributes = settings.scriptDataAttributes;

      // Response example
      // data.productVariants = [
      //   {
      //     "size": null,
      //     "image_src": "https://via.placeholder.com/450",
      //     "image_alt": null,
      //     "gtin": "00047677482760"
      //   },
      //   {
      //     "size": "8.67",
      //     "image_src": "https://via.placeholder.com/550",
      //     "image_alt": null,
      //     "gtin": "00047677391284"
      //   }
      // ];

      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function success(results) {
          data.productVariants = results;
          _this.render(_this.context, data);
        }
      });
    },

    render: function (context, data) {
      const _this = this;
      let $title = $(context).find('.product-selector__title');
      let $image = $(context).find('.product-selector__image img');
      let $productVariantSelector = $(context).find('.product-selector__product-variant-selector');

      $title.html(data.title);
      $image.attr({
        src: data.productVariants[0].image_src,
        alt: data.productVariants[0].image_alt
      });

      $productVariantSelector.empty();

      $.each(data.productVariants, function(i, val) {
        $productVariantSelector.append('<option ' +
          ' data-id="' + val.gtin + '"' +
          ' data-image-src="' + val.image_src + '"' +
          ' data-image-alt="' + val.image_alt + '"' +
          ' value="' + val.size + '">' + ( val.size ? val.size + 'oz' : 'not indicated') + '</option>')
      });
      let firstOption = $(context).find('.product-selector__product-variant-selector option:first');
      _this.updateScript(firstOption);
    },

    initEvents: function (context) {
      // $('.product-selector__item-selector').chosen();
      const _this = this;
      let $itemSelector = $(context).find('.product-selector__item-selector');
      let $productVariantSelector = $(context).find('.product-selector__product-variant-selector');

      $itemSelector.on('change', function() {
        let productId = $(this).find('option:selected').data("id");
        let productTitle = $(this).val();
        _this.updateData(context, productId, productTitle);
      });

      $productVariantSelector.on('change', function() {
        let $selectedVariant = $(this).find('option:selected');
        let $image = $('.product-selector__image img');

        $image.attr({
          alt: $selectedVariant.data('image-alt'),
          src: $selectedVariant.data('image-src')
        });
        _this.updateScript($selectedVariant);
      });
    },

    updateScript: function(selectedVariant) {
      const _this = this;
      let script = '<script ' +
        'type="text/javascript"' +
        'src="//fi-v2.global.commerce-connector.com/cc.js"' +
        'id="cci-widget"' +
        'data-token="' + _this.settings.data_token + '"' +
        'data-locale="' + _this.settings.data_locale + '"' +
        'data-displaylanguage="' + _this.settings.data_displaylanguage + '"' +
        'data-widgetid="' + _this.settings.widget_id + '"' +
        'data-ean="' + selectedVariant.data('id') + '"' +
        'data-subid="' + _this.settings.data_subid + '"' +
        '></script>';
      $('.product-selector #cci-inline-root').remove();
      $('script#cci-widget').remove();
      $('.product-selector__form-container').append(script);
    },
  };
})(jQuery);
