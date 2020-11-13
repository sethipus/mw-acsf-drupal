Feature: Product Test
  @javascript
  Scenario: Product Test
    When I login into Drupal

    # Add product variant
    And I am on "/node/add/product_variant"
    And I fill in "Title" with "product_variant_title"
    And I fill in "SKU" with "product_variant_sku"
    And I fill in "Size" with "product_variant_size"
    And I click on a "//a[contains(@class, 'form-required') and contains(text(), 'Media')]" xpath element
    And I press "Select entities"
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

    # Add icon
    When I am on "media/add/image"
    And I attach the file "icon.png" to "Add a new file"
    And I wait until the "//a[@type='image/png; length=1174']" xpath element appears
    And I fill in "Name" with "icon"
    And I fill in "Alternative text" with "icon_alternative_text"
    And I press "Save"
    Then the url should match "admin/content/media"
    And I should see "Image icon has been created."

    # Add format
    When I am on "admin/structure/taxonomy/manage/mars_format/add"
    And I fill in "Name" with "Format1"
    And I expand "Relations" area
    And I fill in "Weight" with "5"
    And I press "Save"
    Then the url should match "admin/structure/taxonomy/manage/mars_format/add"
    And I should see "Status message"
    And I should see "Created new term Format1."

    # Add flavor
    When I am on "admin/structure/taxonomy/manage/mars_flavor/add"
    And I fill in "Name" with "Flavor1"
    And I expand "Relations" area
    And I fill in "Weight" with "5"
    And I press "Save"
    Then the url should match "admin/structure/taxonomy/manage/mars_flavor/add"
    And I should see "Status message"
    And I should see "Created new term Flavor1."

    # Product
    When I follow "Content"
    And I follow "Add content"
    And I follow "Product"
    Then I should see "Create Product"

    When I fill in "Title" with "product_title"
    And I select "Flavor1" from "Flavor"
    And I fill in "Market" with "product_market"
    And I fill in "Sub Brand" with "product_subbrand"
    And I fill in "Segment" with "product_segment"
    And I fill in "Product Name" with "product_name"
    And I select "Format1" from "Format"
    And I click on a "//a[contains(text(), 'Variants')]" xpath element

    # Delete format
    When I am on "admin/structure/taxonomy/manage/mars_format/overview"
    And I click link which contains "edit?destination=/admin/structure/taxonomy/manage/mars_format/overview"
    Then I should see "Edit term"
    When I follow "edit-delete"
    Then I should see "Are you sure you want to delete the taxonomy term Format1?"
    When I press "Delete"
    Then I should see "Deleted term Format1."

    # Delete flavor
    When I am on "admin/structure/taxonomy/manage/mars_flavor/overview"
    And I click link which contains "edit?destination=/admin/structure/taxonomy/manage/mars_flavor/overview"
    Then I should see "Edit term"
    When I follow "edit-delete"
    Then I should see "Are you sure you want to delete the taxonomy term Flavor1?"
    When I press "Delete"
    Then I should see "Deleted term Flavor1."

    # Delete icon
    When I am on "admin/content/media"
    And I click on a "//button[contains(@class, 'dropbutton__toggle')]" xpath element
    And I click on a "//li[contains(@class, 'delete')]/a" xpath element
    Then I should see "Are you sure you want to delete the media item icon? "
    When I press "Delete"
    Then I should see "The media item icon has been deleted."

        #Delete product variant
    When I follow "Content"
    And I check content with title "product_variant_title"
    And I press "Apply to selected items"
    Then the url should match "content/node/delete"
    When I press "Delete"
    Then the url should match "admin/content"
