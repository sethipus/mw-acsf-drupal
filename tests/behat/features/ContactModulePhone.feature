Feature: Contact Module Phone Test
  @javascript
  Scenario: Contact Module Phone Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Contact Help Banner"
    And I fill in "Title" with "MyTitle"
    And I fill in "Description" with "MyDescription"
    And I fill in "Label" with "MyLabel"
    And I fill in "Phone Number" with "222-5555-1616"
    And I fill in "Social Links label" with "MySocialLinks"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."
    And print current URL

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see a ".contact-module" element
    And I should see "MyTitle"
    And I should see "MyDescription"
    And I should see a "//a[contains(@href,'tel:222-5555-1616')]/span[contains(text(), 'MyLabel')]" xpath element
    And I should see "MySocialLinks"
