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

    When I fill in "Title" with "MyTitle"
    And I fill in "Description" with "MyDescription"
    And I fill in "Label" with "MyLabel"
    And I fill in "Phone Number" with "222-5555-1616"
    And I fill in "Button Label" with "my_button_label"
    And I fill in "Page URL" with "http://contact.com"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."
    And print current URL

    When I press "Save layout"
    Then print current URL
    And I should see "The layout override has been saved."
    And I should see a ".contact-module" element
    And I should see "MyTitle"
    And I should see "MyDescription"
    And I should see a "//a[contains(@href,'http://contact.com')]/span[contains(text(), 'my_button_label')]" xpath element
    And I should see "Follow Us On"

    When I follow "Content"
    And I check content with title "TestBasicPageTitle"
    And I press "Apply to selected items"
    Then the url should match "content/node/delete"
    When I press "Delete"
    Then the url should match "admin/content"
