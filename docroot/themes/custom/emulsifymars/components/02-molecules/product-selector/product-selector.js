(function($){
  Drupal.behaviors.productSelector = {
    attach: function (context, settings) {
      const _this = this;
      _this.context = context;

      _this.settings = {
        widget_id: settings.wtb_block['widget_id'],
        data_subid: settings.wtb_block['data_subid'],
        data_locale: settings.wtb_block['data_locale'],
        data_displaylanguage: settings.wtb_block['data_displaylanguage']
      }

      _this.initEvents(context);
    },

    updateData: function (settings, productId, productTitle) {
      const _this = this;
      let data = {};
      let url = '/wtb/get_product_info/' + productId;

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
      let $title = $(context).find('.product-selector__title');
      let $image = $(context).find('.product-selector__image img');
      let $productVariantSelector = $(context).find('.product-selector__product-variant-selector');

      $title.html(data.title);
      $image.attr("src", data.image);

      $productVariantSelector.empty();

      $.each(data.productVariants, function(i, val) {
        $productVariantSelector.append('<option ' +
          ' data-id="' + val.gtin + '"' +
          ' data-image-src="' + val.image_src + '"' +
          ' data-image-alt="' + val.image_alt + '"' +
          ' value="' + val.size + '">' + ( val.size ? val.size + 'oz' : 'not indicated') + '</option>')
      });
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

        let script = '<script id="wtb-container"' +
          'type="text/javascript"' +
          'src=""' +
          'id="wtb-widget"' +
          'data-token="{{ data_token }}"' +
          'data-locale="' + _this.settings.data_locale + '"' +
          'data-displaylanguage="' + _this.settings.data_displaylanguage + '"' +
          'data-widgetid="' + _this.settings.widget_id + '"' +
          'data-ean="{{ products[0].id }}"' +
          'data-subid="' + _this.settings.data_subid + '"' +
          '></script>';

        $('#wtb-container').remove();
        $('.product-selector').append(script);
      });
    },
  };
})(jQuery);
