<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=">
  <script async type="text/JavaScript" src="//apps.bazaarvoice.com/deployments/dovechocolates/main_site/staging/en_US/bv.js"></script>
  <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
  <title>BazaarVoice Test</title>
</head>
<body>
  <p>[Inline Rating]</p>
  <div>
    <div data-bv-show="inline_rating"
      data-bv-product-id="040000529361"
      data-bv-redirect-url="http://mycompany.com/product1"></div>
  </div>
  <p>[END OF - Inline Rating]</p>
  <hr />
  <p>[Rating Summary - Mini]</p>
  <div>
    <div data-bv-show="rating_summary" data-bv-product-id="040000529361"></div>
  </div>
  <p>[END OF - Rating Summary - Mini]</p>
  <hr />
  <p>[Rating Summary - Max]</p>
  <div>
    <div data-bv-show="reviews" data-bv-product-id="040000529361"></div>
  </div>
  <p>[END OF - Rating Summary - Max]</p>
  <hr />
  <p>[API Call]</p>
    <script>
      $(document).ready(function() {
        $.ajax({
          url: "https://stg.api.bazaarvoice.com/data/reviews.json?apiversion=5.4&passkey=***ENTER PASSKEY HERE***&filter=productid:***PRODUCTID***",
          data: "json",
          success: function(data) {
            console.log(data);
            for (i in data['Results']) {
              var info = data['Results'][i];
              console.log(info.AuthorId + " || " + info.ReviewText);
            }
          }
        })
      });
    </script>
  <p>[END OF - API Call]</p>
</body>
</html>
