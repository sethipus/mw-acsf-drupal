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

    When I fill in "Title" with "Oops"
    
    And I press "Save"
    And I should see "Error page Oops has been created."
    And I should see "Unfortunately, this page does not exist. Here are some helpful links instead:"
    And save a screenshot
    And I should see a "//a/span[text()='Home']" xpath element
    And I should see a "//a/span[text()='Products']" xpath element
    And I should see a "//a/span[text()='About']" xpath element
    And I should see a "//div[@class='error-component__img-container']//img[@class='error-bg-img__image error-bg-img__image--' and @alt='Alternative text1']" xpath element
    And print current URL

    When I edit added content
    And I should see "Edit Error page"
    And I should see "Oops"
    And I should see "View"
    And I should see "Edit"
    And I should see "Delete"
    And I should see "History"
    And I should see "Revisions"
