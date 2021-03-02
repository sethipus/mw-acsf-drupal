Feature: Article Header Test
  @javascript
  Scenario: Article Header Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Article header"
    Then I should see "Article header"

    When I fill in "Eyebrow" with "my_eyebrow"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see a ".article-header-noimage" element
    And I should see "my_eyebrow"
    And I should see "TestBasicPageTitle"
    And I should see "SHARE"
