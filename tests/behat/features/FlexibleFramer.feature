Feature: Flexible Framer Test
  @javascript
  Scenario: Flexible Framer Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Flexible Framer block"
    Then I should see "MARS: Flexible Framer block"

    When I fill in "Header" with "my_framer_header"
    And I press "Add item"
    And I wait for the ajax response
    And I fill in "Item title" with "my_item_title"
    And I fill in "CTA Link URL" with "http://link.com"
    And I fill in "Item description" with "my_item_description"
    And I click on a "//input[@data-drupal-selector='edit-settings-items-0-item-image-browser-entity-browser-open-modal']" xpath element
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
    And I fill in "URL alias" with "/icon"
    And I press "Select"
    And I wait for the ajax response
    And I switch to the main window
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see "my_framer_header"
    And I should see "my_item_title"
    And I should see "my_item_description"
    And I should see a ".flexible-framer" element
    And I should see a "//a[contains(@href,'http://link.com')]/img[@alt='icon_alternative_text']" xpath element
