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
    And I load page by link with text "Create custom block"
    And I follow "Flexible Framer"
    And print current URL
    And I fill in "Framer title" with "My Framer Title"
    And I fill in "Block description" with "block_description"
    And I fill in "Item title" with "My Item Title"
    And I press "Select entities"
    And I wait for the ajax response
    And I switch to the iframe "entity_browser_iframe_lighthouse_browser"
    And I wait for the ajax response
    And I press "Upload"
    And I attach the file "icon.png" to "File"
    And I wait until the "//a[@type='image/png; length=1174']" xpath element appears
    And I should see "(1.15 KB)"
    And I select "Image" from "Bundle"
    And I wait until the "//details[contains(@class, 'claro-details')]" xpath element appears
    And I fill in "Alternative text" with "Alternative text"
    And I fill in "Name" with "Name"
    And I fill in "URL alias" with "/framer"
    And I press "Select"
    And I wait for the ajax response
    And I switch to the main window
    And I fill in "Item description" with "Item description1"
    And I fill in "Item description" with "Item cta1"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see "My Framer Title"
    And I should see "My Item Title"
    And I should see a "//div[@class='error-component__img-container']//img[@alt='Alternative text']" xpath element

    When I am on "/admin/content"
    And I check content with title "TestBasicPageTitle"
    And I press "Apply to selected items"
    And I press "Delete"
    Then the url should match "admin/content"
