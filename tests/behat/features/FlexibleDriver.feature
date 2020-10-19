Feature: Flexible Driver Test
  @javascript
  Scenario: Flexible Driver Test
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
    And I should see "Edit"
    And I should see "Delete"
    And I should see "History"
    And I should see "Layout"
    And I should see "Revisions"

    When I press the "Layout" section of added content
    Then print current URL
    When I follow "Add block "
    Then print current URL
    When I wait for the ajax response
    Then I should see "Choose a block"
    And I should see "Create custom block"
    And I should see "Flexible driver"

    When I follow "Content"
    And I check content with title "TestBasicPageTitle"
    And I press "Apply to selected items"
    And I press "Delete"
    And I close browser
