Feature: Related Recipes Test
  @javascript
  Scenario: Related Recipes Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "Related Recipes"
    Then I should see "Configure block"
    And I should see "Block description"
    And I should see "Related Recipes"
    And I should see "Items per block"
    And I should see "Override title"

    When I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."

    And I should see a "//div[@data-block-plugin-id='views_block:related_recipes-related_recipes' and @class='block']" xpath element
