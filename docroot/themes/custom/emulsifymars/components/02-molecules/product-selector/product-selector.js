(function ($, Drupal) {
  Drupal.behaviors.productSelector = {
    attach: function (context, DrupalSettings) {
      $(context).find('.product-selector').once('product-selector').each(function () {
        const $productSelector = $(this);
        const $itemSelector = $productSelector.find('.product-selector__item-selector');
        const $variantSelector = $productSelector.find('.product-selector__product-variant-selector');
        const $productInformation = $productSelector.find('.product-selector__information');
        const $productTitle = $productSelector.find('.product-selector__title');
        const settings = DrupalSettings.wtb_block;

        const updateData = function (productId, productTitle) {
          let data = {};

          data.productId = productId;
          data.title = productTitle;
          data.scriptDataAttributes = settings.scriptDataAttributes;

          let $variants = $itemSelector.find('option[data-id="'+ productId +'"]').attr('data-variants');
          data.productVariants = JSON.parse($variants);
          render(data);
        }

        const render = function (data) {
          $productTitle.html(data.title);
          $productInformation.addClass('visually-hidden');
          if ($productInformation.parent().find('.product-' + data.productId).length) {
            $productInformation.parent().find('.product-' + data.productId).removeClass('visually-hidden');
          }

          $variantSelector.empty();

          $.each(data.productVariants, function (i, val) {
            $variantSelector.append('<option ' +
              ' data-id="' + val.gtin + '"' +
              ' value="' + val.size + '">' + (val.size ? val.size : Drupal.t('not indicated', null, 'MARS')) + '</option>')
          });
          let firstOption = $variantSelector.find('option:first');
          updateScript(firstOption);
        }

        const updateVariantImage = function () {
          let $selectedVariant = $variantSelector.find('option:selected');
          $productInformation.parent().find('.product-' + $itemSelector.find('option:selected').data("id")).find('img').each(function () {
            if (!$(this).hasClass('gtin-' + $selectedVariant.data('id'))) {
              $(this).addClass('visually-hidden');
            }
            $productInformation.find('img.gtin-' + $selectedVariant.attr('data-id')).removeClass('visually-hidden');
          });
        }

        const initEvents = function () {

          $itemSelector.on('change', function () {
            let productId = $(this).find('option:selected').data("id");
            let productTitle = $(this).val();
            updateData(productId, productTitle);
            updateVariantImage();
          });

          $variantSelector.on('change', function () {
            let $selectedVariant = $(this).find('option:selected');
            updateVariantImage();
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
          // Cleanup global CC object.
          window.CCIW = [];
          // Remove inline widget container.
          $('.product-selector #cci-inline-root').remove();
          // Remove static CC scripts from the page.
          $('script[src*="fi-v2.global.commerce-connector.com/static/js"]').remove();
          // Remove inline widget script by widget ID before replacement.
          $('script#cci-widget[data-widgetid="' + settings.widget_id + '"]').remove();
          // Attach inline widget script to the page with a new SKU.
          $('.product-selector__form-container').append(script);
          // Remove unnecessary duplicated DOM for Popup widget.
          $('.cci-root-container').each(function() {
            if ($(this).contents().attr('id') !== 'widgetMain') {
              $(this).remove();
            }
          });
        }

        initEvents();

      });
      // Product default select in dropdown based on the gtin id query parameter.
      $(document).ready(function() {
        var url = window.location.search.substring(1);
        var gtin_id_url = (url.split('=')[1]) ? url.split('=')[1] : "";
        var gtin_name = (url.split('=')[0]) ? url.split('=')[0] : "";
        $(".product-selector__item-selector option", context).each(function() {
            var gtin_data = $(this).attr("data-variants");
            var arr = ($.parseJSON(gtin_data)) ? $.parseJSON(gtin_data) : "";
            var gtin_id_option = (arr[0]['gtin']) ? arr[0]['gtin'] : "";
            if ((gtin_id_url == gtin_id_option) && (gtin_name == 'gtin') && (gtin_id_url != "") && (gtin_id_option != "")) {
                var selectId = $(this).attr("data-id");
                $(".product-selector__item-selector option[data-id='" + selectId + "']").prop("selected", true).trigger('change');
            }
        });
      });
    }
  }
})(jQuery, Drupal);
