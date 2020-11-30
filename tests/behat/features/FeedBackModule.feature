Feature: Feedback Module Test
  @javascript
  Scenario: Feedback Module Test
    When I login into Drupal
    And I am on "/poll/add"
    And I click on a "(//input[@value='feedback'])" xpath element
    And I sleep "1" seconds
    And I fill in "Title" with "poll_1"
    And I fill in "Description" with "poll_descrtiption_1"
    And I press "Save"
    Then I should see "poll_1"
    And I should see "has been added."

    When I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Feedback Block"
    And I fill in "Poll Entity" with "poll_1"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see a ".feedback-module__main-content" element
    And I should see a ".feedback-module__heading" element
    And I should see a ".feedback-module__paragraph" element
    And I should see "poll_1"
    And I should see "poll_descrtiption_1"
    And I should see "Contact Us"

    When I am on "admin/content/poll"
    And I click link which contains "edit?destination=/admin/content/poll"
    And I wait until the "//*[@id='edit-delete']" xpath element appears
    And I follow "edit-delete"
    And I press "Delete"
    Then I should see "The poll poll_1 has been deleted"
