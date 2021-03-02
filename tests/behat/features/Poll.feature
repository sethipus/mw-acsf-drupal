Feature: Poll Test
  @javascript
  Scenario: Poll Test
    When I login into Drupal
    And I am on "/poll/add"
    And I fill in "Title" with "poll_1"
    And I fill in "Description" with "poll_descrtiption_1"
    And I fill in "edit-choice-0-choice" with "response_1"
    And I press "Save"
    Then I should see "poll_1"
    And I should see "has been added."

    When I am on "admin/content/poll"
    And I click link which contains "edit?destination=/admin/content/poll"
    And I wait until the "//*[@id='edit-delete']" xpath element appears
    And I follow "edit-delete"
    And I press "Delete"
    Then I should see "The poll poll_1 has been deleted"
