(function ($, Drupal) {
  Drupal.behaviors.productSelector = {
    attach: function (context, DrupalSettings) {
      $(context).find('.product-selector').once('product-selector').each(function () {
        const $productSelector = $(this);
        const $itemSelector = $productSelector.find('.product-selector__item-selector');
        const $variantSelector = $productSelector.find('.product-selector__product-variant-selector');
        const $productImage = $productSelector.find('.product-selector__image img');
        const $productTitle = $productSelector.find('.product-selector__title');
        const settings = DrupalSettings.wtb_block;

        const updateData = function (productId, productTitle) {
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
              render(data);
            }
          });
        }

        const render = function (data) {
          $productTitle.html(data.title);
          $productImage.attr({
            src: data.productVariants[0].image_src,
            alt: data.productVariants[0].image_alt
          });

          $variantSelector.empty();

          $.each(data.productVariants, function (i, val) {
            $variantSelector.append('<option ' +
              ' data-id="' + val.gtin + '"' +
              ' data-image-src="' + val.image_src + '"' +
              ' data-image-alt="' + val.image_alt + '"' +
              ' value="' + val.size + '">' + (val.size ? val.size : Drupal.t('not indicated', null, 'MARS')) + '</option>')
          });
          let firstOption = $variantSelector.find('option:first');
          updateScript(firstOption);
        }

        const initEvents = function () {

          $itemSelector.on('change', function () {
            let productId = $(this).find('option:selected').data("id");
            let productTitle = $(this).val();
            updateData(productId, productTitle);
          });

          $variantSelector.on('change', function () {
            let $selectedVariant = $(this).find('option:selected');

            $productImage.attr({
              alt: $selectedVariant.data('image-alt'),
              src: $selectedVariant.data('image-src')
            });

            updateScript($selectedVariant);
          }).change();
        }

        const updateScript = function (selectedVariant) {
          let script = '<script ' +
            'type="text/javascript" ' +
            'src="//fi-v2.global.commerce-connector.com/cc.js" ' +
            'id="cci-widget" ' +
            'data-token="' + settings.data_token + '" ' +
            'data-locale="' + settings.data_locale + '" ' +
            'data-displaylanguage="' + settings.data_displaylanguage + '" ' +
            'data-widgetid="' + settings.widget_id + '" ' +
            'data-ean="' + selectedVariant.data('id') + '" ' +
            'data-subid="' + settings.data_subid + '" ' +
            '></script>';
          $('.product-selector #cci-inline-root').remove();
          $('script#cci-widget[data-widgetid="' + settings.widget_id + '"]').remove();
          $('.product-selector__form-container').append(script);
        }

        initEvents();

      });
    }
  }
})(jQuery, Drupal);
