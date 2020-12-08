Feature: Recomendations Module Test
  @javascript
  Scenario: Recomendations Module Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    Then the url should match "testbasicpagetitle"
    And I should see "Basic page TestBasicPageTitle has been created."
    And print current URL

    When I edit added content
    Then I should see "Edit Basic page"

    When I press the "Layout" section of added content
    Then the url should match "layout"

    When I follow "Add block "
    And I wait for the ajax response
    And I should see "Choose a block"
    And I should see "Create custom block"

    When I load page by link with text "MARS: Recommendations Module"
    Then I should see "MARS: Recommendations Module"
    And I should see "Recommendations population"
    And I should see "Population Logic"

    When I fill in "Title" with "MyTitle"
    And I click on a "//label[text()='Dynamic']" xpath element
    Then I should see "Please wait..."

    When I wait for the ajax response
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then print current URL
    And I should see "The layout override has been saved."
    And I should see a ".recommendations" element
    And I should see a ".recommendations__heading" element
    And I should see "MyTitle"
