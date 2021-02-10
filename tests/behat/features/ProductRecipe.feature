Feature: Product And Recipe Test
  @javascript
  Scenario: Product And Recipe Test
    When I login into Drupal

    When I am on "admin/structure/taxonomy/manage/mars_category/add"
    And I fill in "Name" with "my_category"
    And I press "Save"
    Then I should see "Status message"
    And I should see "Created new term my_category."

    When I am on "admin/structure/taxonomy/manage/mars_main_ingredient/add"
    And I fill in "Name" with "my_ingredient"
    And I press "Save"
    Then I should see "Status message"
    And I should see "Created new term my_ingredient."

    When I am on "/node/add/product_variant"
    And I fill in "Title" with "product_variant_title"
    And I fill in "SKU" with "product_variant_sku"
    And I fill in "Size" with "product_variant_size"
    And I click on a "//a[contains(@class, 'form-required') and contains(text(), 'Media')]" xpath element
    And I click on a "//input[@data-drupal-selector='edit-field-product-key-image-entity-browser-entity-browser-open-modal']" xpath element
    And I wait for the ajax response
    And I switch to the iframe "entity_browser_iframe_lighthouse_browser"
    And I wait for the ajax response
    And I press "Upload"
    And I attach the file "icon.png" to "File"
    And I wait until the "//a[@type='image/png; length=1174']" xpath element appears
    Then I should see "(1.15 KB)"
    When I select "Image" from "Bundle"
    And I wait until the "//details[contains(@class, 'claro-details')]" xpath element appears
    And I fill in "Alternative text" with "alternative_icon_text"
    And I fill in "Name" with "icon_name"
    And I fill in "URL alias" with "/icon"
    And I press "Select"
    And I wait for the ajax response
    And I switch to the main window
    And I press "Save"
    Then I should see "product_variant_size"
    And I should see "SKU"
    And I should see "product_variant_sku"
    And I should see "Key Product Pack Image"
    And I should see a "//img[@alt='alternative_icon_text']" xpath element

    When I am on "admin/structure/taxonomy/manage/mars_format/add"
    And I fill in "Name" with "Format1"
    And I expand "Relations" area
    And I fill in "Weight" with "5"
    And I press "Save"
    Then I should see "Status message"
    And I should see "Created new term Format1."

    When I am on "admin/structure/taxonomy/manage/mars_flavor/add"
    And I fill in "Name" with "Flavor1"
    And I expand "Relations" area
    And I fill in "Weight" with "5"
    And I press "Save"
    Then I should see "Status message"
    And I should see "Created new term Flavor1."


    When I follow "Content"
    And I follow "Add content"
    And I follow "Product"
    When I fill in "Title" with "product_title"
    And I select "Flavor1" from "Flavor"
    And I fill in "Market" with "product_market"
    And I select "my_category" from "Category"
    And I fill in "Segment" with "product_segment"
    And I fill in "Product Name" with "product_name"
    And I select "Format1" from "Format"
    And I click on a "//a[@title='Insert Horizontal Line']" xpath element
    And I expand "Variants" area
    And I fill in "Product Variants" with "product_variant_title"
    And I press "Save"
    Then I should see "Product product_title has been created."
    And I should see "Products"
    And I should see "product_title"
    And I should see "Available sizes"
    And I should see "product_variant_size"
    And I should see a "//picture[@class='pdp-hero-slide__image pdp-hero-slide__image--']//img[@class='pdp-hero-slide__image pdp-hero-slide__image--' and @alt='alternative_icon_text']" xpath element
    And I should see "Nutrition"
    And I should see "More information"
    And I should see "Serving Size:"
    And I should see "Servings Per Container:"
    And I should see "Amount per serving"
    And I should see "% Daily value"
    And I should see "Vitamins | Minerals:"
    And I should see "Ingredients:"
    And I should see "Warnings:"
    And I should see "More information"
    And I should see "More Products Like This"


    When I am on "/node/add/recipe"
    And I fill in "Title" with "recipe_title"
    And I fill in "Cooking time" with "10"
    And I fill in "Ingredients number" with "3"
    And I fill in "Number of servings" with "5"
    And I click on a "//input[@data-drupal-selector='edit-field-recipe-image-entity-browser-entity-browser-open-modal']" xpath element
    And I wait for the ajax response
    And I switch to the iframe "entity_browser_iframe_lighthouse_browser"
    And I wait for the ajax response
    And I press "Upload"
    And I attach the file "icon.png" to "File"
    And I wait until the "//a[@type='image/png; length=1174']" xpath element appears
    Then I should see "(1.15 KB)"
    When I select "Image" from "Bundle"
    And I wait until the "//details[contains(@class, 'claro-details')]" xpath element appears
    And I fill in "Alternative text" with "icon_alternative_text"
    And I fill in "Name" with "icon_name"
    And I fill in "URL alias" with "/framer"
    And I press "Select"
    And I wait for the ajax response
    And I switch to the main window
    And I fill in "field_recipe_ingredients[0][value]" with "my_ingredient"
    And I fill in "field_recipe_description[0][value]" with "my_description"
    And I fill in "Product" with "product_title"
    And I press "Save"
    Then I should see "Recipe"
    And I should see "has been created"
    And I should see "Time"
    And I should see "10 mins"
    And I should see "Ingredients"
    And I should see "3 items"
    And I should see "Makes"
    And I should see "5 servings"
    And I should see a ".recipe-media__image-wrapper" element
    And I should see a ".recipe-details" element

    When I follow "Content"
    And I check content with title "product_title"
    And I press "Apply to selected items"
    Then the url should match "content/node/delete"
    And I press "Delete"

    And I check content with title "recipe_title"
    And I press "Apply to selected items"
    Then the url should match "content/node/delete"
    And I press "Delete"

    When I am on "admin/structure/taxonomy/manage/mars_format/overview"
    And I click link which contains "edit?destination=/admin/structure/taxonomy/manage/mars_format/overview"
    Then I should see "Edit term"
    And I wait until the "//*[@id='edit-delete']" xpath element appears

    When I follow "edit-delete"
    And I press "Delete"
    Then I should see "Deleted term"
    And I should see "Format1"

    When I am on "admin/structure/taxonomy/manage/mars_flavor/overview"
    And I click link which contains "edit?destination=/admin/structure/taxonomy/manage/mars_flavor/overview"
    Then I should see "Edit term"
    And I wait until the "//*[@id='edit-delete']" xpath element appears

    When I follow "edit-delete"
    And I press "Delete"
    Then I should see "Deleted term"
    Then I should see "Flavor1"

    When I follow "Content"
    And I check content with title "product_variant_title"
    And I press "Apply to selected items"
    Then the url should match "content/node/delete"
    And I press "Delete"

    When I am on "admin/structure/taxonomy/manage/mars_category/overview"
    And I click link which contains "edit?destination=/admin/structure/taxonomy/manage/mars_category/overview"
    Then I should see "Edit term"
    And I wait until the "//*[@id='edit-delete']" xpath element appears

    And I follow "edit-delete"
    And I press "Delete"
    Then I should see "Deleted term"
    And I should see "my_category"

    When I am on "admin/structure/taxonomy/manage/mars_main_ingredient/overview"
    And I click link which contains "edit?destination=/admin/structure/taxonomy/manage/mars_main_ingredient/overview"
    Then I should see "Edit term"
    And I wait until the "//*[@id='edit-delete']" xpath element appears

    And I follow "edit-delete"
    And I press "Delete"
    Then I should see "Deleted term"
    And I should see "my_ingredient"
