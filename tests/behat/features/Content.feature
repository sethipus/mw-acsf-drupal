Feature: Content Test
  @javascript
  Scenario: Content Test
    When I login into Drupal
    And I follow "Content"
    Then I should see "Add content"

    When I follow "Add content"
    Then I should see "Basic page"
    When I follow "Basic page"
    Then I should see "Create Basic page"

    When I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And the url should match "testbasicpagetitle"
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
    And the url should match "layout"
    When I follow "Add block "
    Then print current URL
    When I wait for the ajax response
    Then I should see "Choose a block"
    And I should see "Create custom block"

    When I load page by link with text "Content Feature Module"
    Then print current URL
    And I should see "Block description"
    And I should see "Content Feature Module"
    And I should see "Eyebrow"
    And I should see "Title"
    And I should see "Background"
    And I should see "Description"

    When I fill in "Eyebrow" with "MyEyebrow"
    And I fill in "Title" with "MyTitle"
    And I fill in "Background" with "MyBackground"
    And I fill in "Description" with "MyDescription"
    And I fill in "Button Label" with "Explore"
    And I fill in "URL" with "http://link.com"
    Then save a screenshot

    When I follow "Content"
    And I check content with title "TestBasicPageTitle"
    And I press "Apply to selected items"
    Then the url should match "content/node/delete"
    When I press "Delete"
    Then the url should match "admin/content"
    And I close browser
