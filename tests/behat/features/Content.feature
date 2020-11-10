Feature: Content Test
  @javascript
  Scenario: Content Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    Then the url should match "testbasicpagetitle"
    And I should see "Basic page TestBasicPageTitle has been created."
    And print current URL

    When I edit added content
    Then I should see "Edit Basic page"
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
    And I fill in "Description" with "MyDescription"
    And I fill in "Button Label" with "Explore"
    And I fill in "URL" with "http://link.com"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."
    And print current URL

    When I press "Save layout"
    Then print current URL
    And I should see a ".content-feature" element
    And I should see "MYEYEBROW"
    And I should see "MyTitle"
    And I should see "MyDescription"
    And I should see a "//a[contains(@href,'http://link.com')]/span[contains(text(), 'Explore')]" xpath element

    When I follow "Content"
    And I check content with title "TestBasicPageTitle"
    And I press "Apply to selected items"
    Then the url should match "content/node/delete"

    When I press "Delete"
    Then the url should match "admin/content"
