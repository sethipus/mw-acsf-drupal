Feature: Social Feed Test
  @javascript
  Scenario: Social Feed Test
    When I login into Drupal
    And I am on "/admin/config/services/juicer_io/feed"
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

    When I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    Then I should see "Basic page TestBasicPageTitle has been created."

    When I edit added content
    And I press the "Layout" section of added content
    Then the url should match "layout"

    When I follow "Add block "
    And I wait for the ajax response
    Then I should see "Choose a block"
    And I should see "Create custom block"

    When I load page by link with text "MARS: Social feed"
    Then I should see "Block description"
    And I should see "Social feed"
    And I should see "Feed"

    When I fill in "Title" with "FeedTitle"
    And I fill in "Feed" with "Feed"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see a ".social-feed-slide__image" element
    And I should see a ".swiper-scrollbar-drag" element

    When I am on "/admin/config/services/juicer_io/feed/feed/edit?destination=/admin/config/services/juicer_io/feed"
    And I follow "edit-delete"
    Then I should see "Are you sure you want to delete"

    When I press "Delete"
    Then I should see "Juicer.io feed configuration deleted: Feed."
    And I should see "There are no juicer.io feed entities yet."
