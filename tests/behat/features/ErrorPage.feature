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
    And I press "Select entities"
    And I wait for the ajax response

    When I switch to the iframe "entity_browser_iframe_lighthouse_browser"
    And I wait for the ajax response

    And I press "Upload"
    And I attach the file "icon.png" to "File"
    And I wait until the "//a[@type='image/png; length=1174']" xpath element appears
    And I should see "(1.15 KB)"

    And I select "Image" from "Bundle"
    And I wait until the "//details[contains(@class, 'claro-details')]" xpath element appears

    And I fill in "Alternative text" with "Alternative text1"
    And I fill in "Name" with "Name1"
    And I fill in "URL alias" with "/error1"
    And I press "Select"
    And I wait for the ajax response

    And I switch to the main window
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

    When I follow "Content"
    And I check content with title "Oops"
    And I press "Apply to selected items"
    And I press "Delete"
