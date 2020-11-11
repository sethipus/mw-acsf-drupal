Feature: Flexible Driver Test
  @javascript
  Scenario: Flexible Driver Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"

    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    Then I should see "Choose a block"
    And I should see "Create custom block"
    And I should see "Flexible driver"

    When I load page by link with text "Flexible driver"
    Then I should see "Block description"
    And I should see "Flexible driver"

    When I fill in "Title" with "MyTitle"
    And I fill in "CTA Link" with "http://link.com"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "Title"
    And I should see a "//a[contains(@href,'http://link.com')]/span[contains(text(), 'Learn more')]" xpath element

    When I follow "Content"
    And I check content with title "TestBasicPageTitle"
    And I press "Apply to selected items"
    And I press "Delete"
