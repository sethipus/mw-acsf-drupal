Feature: Recipe Detail Hero Test
  @javascript
  Scenario: Recipe Detail Hero Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Recipe detail hero"
    Then I should see "MARS: Recipe detail hero"

    When I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."
    And I should see a ".recipe-header" element
    And I should see a ".recipe-header__title" element
    And I should see a ".recipe-header__text" element
    And I should see a ".recipe-media" element
    And I should see a ".recipe-media__image-wrapper" element
    And I should see a ".recipe-media__info" element
    And I should see a ".recipe-media__border" element
    And I should see a ".recipe-details" element
    And I should see a ".recipe-details-item" element
    And I should see a ".recipe-details-item__icon-container" element
    And I should see a ".recipe-details-item__icon" element
    And I should see a ".recipe-details-item__icon--clock" element
    And I should see a ".recipe-details-item__info" element
    And I should see a ".recipe-details-item__label" element
    And I should see a ".recipe-details-item__value" element

    When I press "Save layout"
    Then I should see "The layout override has been saved."
