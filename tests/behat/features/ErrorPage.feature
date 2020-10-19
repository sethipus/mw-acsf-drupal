Feature: Error Page Test
  @javascript
  Scenario: Error Page Test
    When I login into Drupal
    And I follow "Content"
    Then I should see "Add content"

    When I follow "Add content"
    Then I should see "Error Page"
    When I follow "Error Page"
    Then I should see "Create Error page"

    When I fill in "Title" with "TestErrorPageTitle"
    And I press "Save"
    And I should see "Error page TestErrorPageTitle has been created."
    And print current URL

    When I edit added content
    And I should see "Edit Error page"
    And I should see "TestErrorPageTitle"
    And I should see "View"
    And I should see "Edit"
    And I should see "Delete"
    And I should see "History"
    And I should see "Revisions"

    When I follow "Content"
    And I check content with title "TestErrorPageTitle"
    And I press "Apply to selected items"
    And I press "Delete"
