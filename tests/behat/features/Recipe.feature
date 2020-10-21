Feature: Recipe Test
  @javascript
  Scenario: Recipe Test
    When I login into Drupal

    #TODO add ingredients before test
    # add this tests after Product.feature because in this tests Product is required

    # Create product
    And I follow "Content"
    Then I should see "Add content"
    When I follow "Add content"
    Then I should see "Recipe"
    When I follow "Recipe"
    Then I should see "Create Recipe"

    When I fill in "Title" with "My Title"
    When I fill in "Cooking time" with "15"
    When I fill in "Ingredients number" with "5"
    When I fill in "Number of servings" with "3"

    And press "Add media"
    When I wait for the ajax response
    And I sleep "1" seconds
#    Then save a screenshot
#    And I click on "//a[contains(@data-button-id, 'edit-tab-selector') and contains(text(), 'Upload')]" xpath element
#    When I wait for the ajax response
#    And I sleep "1" seconds
#
#    And I attach the file "icon.png" to "Choose File"
#    And I wait for the ajax response
#    When I wait for the ajax response
#    And I sleep "1" seconds
#    Then save a screenshot
#    When I fill in "Name" with "icon"
#    And I press "Place"
#    When I wait for the ajax response
#    And I sleep "1" seconds

    #And I click on "//img[contains(@class, 'image-style-medium')]" xpath element


