(function($, Drupal){
    Drupal.behaviors.dialogfix = {
        attach: function (context) {
            $(context).once('dialogfix').on('focusin', function(e) {
                e.stopImmediatePropagation();
            });

            $('.color_picker', context).once('dialogfix').click(() => {
                $('.color_picker').parents('.ui-dialog').attr('tabindex', '');
            });
             // Image Focal Point Implemention starts here.

            //Adding helper-tool-img class to Homepage hero block.
            var componentName = "mars: homepage hero block"
            var componentValue = $("div[id^='edit-settings-admin-label-']");

            if (componentValue.length > 0) {
              var resultName = componentValue[componentValue.length - 1].innerText.split('\n')[1].toLowerCase();
              if (resultName === componentName) {
                $("table[data-drupal-selector^='edit-settings-background-image-'] img:first-child").addClass("helper-tool-img");
                $("article[data-drupal-selector^='edit-settings-background-image-']").find('img:first-child').parent("div").addClass("helper-tool-target");
              }
            }

            var $focusPointContainers = $('.focuspoint');
            var helperToolImage = 'img.helper-tool-img, div.focal-pointer';
            var $dataAttrInputDesktop = $('.helper-tool-target-desktop-data');
            var $dataAttrInputTablet = $('.helper-tool-target-tablet-data');
            var $dataAttrInputMobile = $('.helper-tool-target-mobile-data');
            var $cssAttrInput = $('.helper-tool-css3-val');
            var focalPointer = '<div class="focal-pointer" style="top: 50%; left: 50%"></div>';

            //This stores focusPoint's data-attribute values
            var focusPointAttr = {
              x: 0,
              y: 0,
              w: 0,
              h: 0
            };

            //Find focal pointer and add below img if empty
            $('.helper-tool-target', context).find('img').each(function () {
              if ($(this).parent('div').find('.focal-pointer').length == 0) {
                $(this).after(focalPointer);
              }
            });

            //Record focal pointer position to input field.
            var topPosition = '50%';
            var leftPosition = '50%';
            if ($dataAttrInputDesktop[0] !== undefined) {
              var recDesktopPosition = $dataAttrInputDesktop[0].value.split('data-rec-pos=')[1] && $dataAttrInputDesktop[0].value.split('data-rec-pos=')[1];
            }
            if ($dataAttrInputMobile[0] !== undefined) {
              var recMobilePosition = $dataAttrInputMobile[0].value.split('data-rec-pos=')[1] && $dataAttrInputMobile[0].value.split('data-rec-pos=')[1];
            }
            if ($dataAttrInputTablet[0] !== undefined) {
              var recTabletPosition = $dataAttrInputTablet[0].value.split('data-rec-pos=')[1] && $dataAttrInputTablet[0].value.split('data-rec-pos=')[1];
            }
            if (recDesktopPosition !== null && recDesktopPosition !== undefined) {
              leftPosition = recDesktopPosition.split(':')[0];
              topPosition = recDesktopPosition.split(':')[1];
              $('table[data-drupal-selector="edit-settings-background-image-selected"] div.focal-pointer').css({
                'top': topPosition,
                'left': leftPosition
              })
            }
            if (recMobilePosition !== null && recMobilePosition !== undefined) {
              leftPosition = recMobilePosition.split(':')[0];
              topPosition = recMobilePosition.split(':')[1];
              $('table[data-drupal-selector="edit-settings-background-image-mobile-selected"] div.focal-pointer').css({
                'top': topPosition,
                'left': leftPosition
              })
            }
            if (recTabletPosition !== null && recTabletPosition !== undefined) {
              leftPosition = recTabletPosition.split(':')[0];
              topPosition = recTabletPosition.split(':')[1];
              $('table[data-drupal-selector="edit-settings-background-image-tablet-selected"] div.focal-pointer').css({
                'top': topPosition,
                'left': leftPosition
              })
            }

            //Helper tool image & Focal pointer click function
            $(helperToolImage, context).on('click', function (e) {

              var imageW = $(this).width();
              var imageH = $(this).height();

              var oriImageW = e.target.naturalWidth;
              var oriImageH = e.target.naturalHeight;

              //To get original size of the image.
              var capturedText = null;
              var isExternalSrc = false;

              $(this).closest("article").children('div').each(function () {
                if ($(this).children('div:contains("Dimension")').length > 0) {
                  capturedText = $(this).text().split('Dimension')[1].trim();
                  isExternalSrc = true;
                  var result = calcOriginalWandH(capturedText, oriImageW, oriImageH);
                  oriImageW = result.split('x')[0];
                  oriImageH = result.split('x')[1];
                }
              });
              if(!isExternalSrc){
                var imgUrl = $(this).attr("src");
                let resultUrlText = validateUrl(imgUrl);
                var validateUrlText = imgUrl.indexOf(resultUrlText);
                if (validateUrlText != -1){
                  actualImageUrl = imgUrl.replace(resultUrlText, "");
                  getMeta(
                    actualImageUrl,
                    (width, height) => {
                      capturedText = width + 'x' + height;
                      var result = calcOriginalWandH(capturedText, oriImageW, oriImageH);
                      oriImageW = result.split('x')[0];
                      oriImageH = result.split('x')[1];
                      triggerFocusPoint(e, $(this), imageW, imageH);
                    }
                  );
                }
              } else {
                triggerFocusPoint(e, $(this), imageW, imageH);
              }
            });

            function calcOriginalWandH(data, capWidth, capHeight){
              if (data !== null) {
                var splitedValue = data.split('x');
                var capturedWidth = splitedValue[0];
                var capturedHeight = splitedValue[1];
                if (parseInt(capturedWidth) >= parseInt(capWidth)) {
                  oriImageW = Math.trunc(capturedWidth);
                }
                if (parseInt(capturedHeight) >= parseInt(capHeight)) {
                  oriImageH = Math.trunc(capturedHeight);
                }
                return oriImageW+'x'+oriImageH;
              }
            }

            function triggerFocusPoint(e, dataObject,imageW, imageH){
              //Calculate FocusPoint coordinates
              var offsetX = e.pageX - dataObject.offset().left;
              var offsetY = e.pageY - dataObject.offset().top;
              var focusX = (offsetX / imageW - .5) * 2;
              var focusY = (offsetY / imageH - .5) * -2;
              focusPointAttr.x = focusX;
              focusPointAttr.y = focusY;

              //Calculate CSS Percentages
              var percentageX = (offsetX / imageW) * 100;
              var percentageY = (offsetY / imageH) * 100;
              var backgroundPosition = percentageX.toFixed(0) + '%:' + percentageY.toFixed(0) + '%';
              var backgroundPositionCSS = 'background-position: ' + backgroundPosition + ';';
              $cssAttrInput.val(backgroundPositionCSS);

              //Leave a sweet target pointer at the focus point.
              dataObject.next('.focal-pointer').css({
                'top': percentageY + '%',
                'left': percentageX + '%'
              });

              //Print coordinates to input field.
              var selectData = dataObject.closest('table').attr('data-drupal-selector');
              var tabletImage = $("table[data-drupal-selector='edit-settings-background-image-tablet-selected']").find('img');
              var mobileImage = $("table[data-drupal-selector='edit-settings-background-image-mobile-selected']").find('img');

              if (selectData.indexOf('mobile') >= 0) {
                if (mobileImage.length > 0) {
                  $dataAttrInputMobile.val('data-focus-x=' + focusPointAttr.x + ' data-focus-y=' + focusPointAttr.y + ' data-image-w=' + oriImageW + ' data-image-h=' + oriImageH + ' data-rec-pos=' + backgroundPosition + '');
                }
              } else if (selectData.indexOf('tablet') >= 0) {
                if (tabletImage.length > 0) {
                  $dataAttrInputTablet.val('data-focus-x=' + focusPointAttr.x + ' data-focus-y=' + focusPointAttr.y + ' data-image-w=' + oriImageW + ' data-image-h=' + oriImageH + ' data-rec-pos=' + backgroundPosition + '');
                }
                if (mobileImage.length == 0) {
                  $dataAttrInputMobile.val('data-focus-x=' + focusPointAttr.x + ' data-focus-y=' + focusPointAttr.y + ' data-image-w=' + oriImageW + ' data-image-h=' + oriImageH + ' data-rec-pos=' + backgroundPosition + '');
                }
              } else {
                updateFocusPoint(focusPointAttr, oriImageW, oriImageH);
                $dataAttrInputDesktop.val('data-focus-x=' + focusPointAttr.x + ' data-focus-y=' + focusPointAttr.y + ' data-image-w=' + oriImageW + ' data-image-h=' + oriImageH + ' data-rec-pos=' + backgroundPosition + '');
                if (mobileImage.length == 0 && tabletImage.length == 0) {
                  $dataAttrInputMobile.val('data-focus-x=' + focusPointAttr.x + ' data-focus-y=' + focusPointAttr.y + ' data-image-w=' + oriImageW + ' data-image-h=' + oriImageH + ' data-rec-pos=' + backgroundPosition + '');
                }
                if (tabletImage.length == 0) {
                  $dataAttrInputTablet.val('data-focus-x=' + focusPointAttr.x + ' data-focus-y=' + focusPointAttr.y + ' data-image-w=' + oriImageW + ' data-image-h=' + oriImageH + ' data-rec-pos=' + backgroundPosition + '');
                }
              }
            }

            //Update focus point containers
            function updateFocusPoint(focusPointAttr, imageW, imageH) {

              $focusPointContainers.attr({
                'data-focus-x': focusPointAttr.x,
                'data-focus-y': focusPointAttr.y,
                'data-image-w': imageW,
                'data-image-h': imageH
              });
              $focusPointContainers.data('focusX', focusPointAttr.x);
              $focusPointContainers.data('focusY', focusPointAttr.y);
              $focusPointContainers.data('imageW', imageW);
              $focusPointContainers.data('imageH', imageH);
              $focusPointContainers.adjustFocus();
            };
            //Image Focal Point Implemention ends.

            function getMeta(url, callback) {
                const img = new Image();
                img.src = url;
                img.onload = function() { callback(this.naturalWidth, this.naturalHeight); }
            }

            function validateUrl(url) {
              let startText = "styles";
              let endText = "public/";
              let startIndex = url.indexOf(startText);
              let endIndex = url.indexOf(endText)+endText.length;
              let resultUrlText = url.substring(startIndex, endIndex);
              return resultUrlText;
            }
        }
    };
})(jQuery, Drupal);
