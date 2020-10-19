Feature: Product Detail Hero Test
  @javascript
  Scenario: Product Detail Hero Test
    When I login into Drupal
    And I follow "Content"
    Then I should see "Add content"

    When I follow "Add content"
    Then I should see "Basic page"
    When I follow "Basic page"
    Then I should see "Create Basic page"

    When I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I should see "Basic page TestBasicPageTitle has been created."
    And print current URL

    When I edit added content
    And I should see "Edit Basic page"
    And I should see "TestBasicPageTitle"
    And I should see "View"
    And I should see "Revisions"

    When I press the "Layout" section of added content
    Then print current URL
    When I follow "Add block "
    Then print current URL
    When I wait for the ajax response
    Then I should see "Choose a block"
    And I should see "Create custom block"

    When I load page by link with text "PDP Hero"
    Then print current URL
    And I should see "Configure block"
    And I should see "Block description"
    And I should see "PDP Hero"
    And I should see "Eyebrow"
    And I should see "Available sizes"

    And I should see "Where to buy button settings"
    And I should see "Commerce Vendor"
    And I should see "Widget id"
    And I should see "Product ID"

    And I should see "Nutrition part settings"
    And I should see "Nutrition section label"
    And I should see "Amount per serving label"
    And I should see "Daily value label"
    And I should see "Vitamins & minerals label"
    And I should see "Diet & Allergens part label"

    When I fill in "Widget id" with "333555888"

    When I follow "Content"
    And I check content with title "TestBasicPageTitle"
    And I press "Apply to selected items"
    And I press "Delete"
    And I close browser
