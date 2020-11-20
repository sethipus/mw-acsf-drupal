Feature: Content Test
  @javascript
  Scenario: Content Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    Then I should see "Choose a block"

    When I load page by link with text "Content Feature Module"
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
