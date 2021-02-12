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
    Then I should see "Basic page TestBasicPageTitle has been created."

    When I edit added content
    Then I should see "Edit Basic page"
    And I should see "TestBasicPageTitle"
    And I should see "View"
    And I should see "Revisions"

    When I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    Then I should see "Choose a block"
    And I should see "Create custom block"

    When I load page by link with text "MARS: PDP Hero"
    Then I should see "Configure block"
    And I should see "Block description"
    And I should see "PDP Hero"
    And I should see "Eyebrow"
    And I should see "Available sizes"
    And I should see "Where to buy button settings"
    And I should see "Widget id"
    And I should see "Product SKU"
    And I should see "Nutrition part settings"
    And I should see "Nutrition section label"
    And I should see "Amount per serving label"
    And I should see "Daily value label"
    And I should see "Vitamins & minerals label"
    And I should see "Diet & Allergens part label"
    And I should see "More information label"
