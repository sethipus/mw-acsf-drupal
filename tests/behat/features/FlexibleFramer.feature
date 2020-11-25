Feature: Flexible Framer Test
  @javascript
  Scenario: Flexible Framer Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "Create custom block"
    And I follow "Flexible Framer"
    And print current URL
    And I fill in "Framer title" with "my_framer_title"
    And I fill in "Block description" with "block_description"
    And I fill in "Item title" with "my_item_title"

    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see "my_framer_title"
    And I should see "my_item_title"
