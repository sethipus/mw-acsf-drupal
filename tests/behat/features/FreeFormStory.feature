Feature: Free Form Story Test
  @javascript
  Scenario: Free Form Story Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Freeform Story Block"
    Then I should see "Configure block"
    And I should see "Block description"
    And I should see "MARS: Freeform Story Block"
    And I should see "Block aligned"
    And I should see "Header 1"
    And I should see "Header 2"
    And I should see "Image"
    And I should see "Description"
    And I should see "Background shape"
    And I should see "Use custom color"
    And I should see "Background Color Override"
    And I fill in "Header 2" with "header_2"
    And I click on a "//a[@title='Insert Horizontal Line']" xpath element

    When I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see "header_2"
    And I should see a ".freeform-story" element
    And I should see a ".freeform-story__content" element
    And I should see a ".freeform-story__texts" element
    And I should see a ".freeform-story__paragraph" element
