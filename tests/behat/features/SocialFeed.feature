Feature: Social Feed Test
  @javascript
  Scenario: Social Feed Test
    When I login into Drupal

    When I am on "/admin/config/services/juicer_io/feed"
    Then I should see "Juicer.io feed entities"
    When I follow "Add juicer.io feed"
    Then I should see "Add juicer.io feed"
    When I fill in "Label" with "Feed"
    And I fill in "Feed id" with "mars-52b9e125-4c3b-408b-9f1d-4aa79779a173"
    And I fill in "Machine-readable name" with "feed"
    And I press "Save"
    Then I should see "Juicer.io feed entities"
    And I should see "Feed label"
    And I should see "Feed"
    And I should see "Feed id"
    And I should see "mars-52b9e125-4c3b-408b-9f1d-4aa79779a173"

    When I follow "Content"
    Then I should see "Add content"
    When I follow "Add content"
    Then I should see "Basic page"
    When I follow "Basic page"
    Then I should see "Create Basic page"
    When I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    Then I should see "Basic page TestBasicPageTitle has been created."
    And print current URL

    When I edit added content
    When I press the "Layout" section of added content
    Then print current URL
    And the url should match "layout"
    When I follow "Add block "
    Then print current URL
    When I wait for the ajax response
    Then I should see "Choose a block"
    And I should see "Create custom block"
    When I load page by link with text "Social feed"
    Then print current URL
    And I should see "Block description"
    And I should see "Social feed"
    And I should see "Feed"
    When I fill in "Feed" with "Feed"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."
    Then print current URL
    When I press "Save layout"
    Then print current URL
    And I should see "The layout override has been saved."
    And I should see "Social feed"
    And I should see a ".social-feed-slide__image" element
    And I should see a ".swiper-scrollbar-drag" element

    When I am on "/admin/config/services/juicer_io/feed/feed/edit?destination=/admin/config/services/juicer_io/feed"
    And I follow "edit-delete"
    Then I should see "Are you sure you want to delete"
    When I press "Delete"
    Then I should see "There are no juicer.io feed entities yet."

    When I follow "Content"
    And I check content with title "TestBasicPageTitle"
    And I press "Apply to selected items"
    And I press "Delete"
