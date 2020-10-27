Feature: Flexible Framer Test
  @javascript
  Scenario: Flexible Framer Test
    When I login into Drupal
    And I follow "Content"
    Then I should see "Add content"

    When I follow "Add content"
    Then I should see "Basic page"
    When I follow "Basic page"
    Then I should see "Create Basic page"

    When I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And the url should match "testbasicpagetitle"
    And I should see "Basic page TestBasicPageTitle has been created."
    And print current URL

    When I edit added content
    And I should see "Edit Basic page"
    And I should see "TestBasicPageTitle"
    And I should see "View"
    And I should see "Edit"

    When I press the "Layout" section of added content
    Then print current URL
    And the url should match "layout"
    When I follow "Add block "
    Then print current URL
    When I wait for the ajax response
    Then I should see "Choose a block"
    And I should see "Create custom block"

    When I load page by link with text "Create custom block"
    Then print current URL
    And I should see "Add a new custom block"
    And I should see "Flexible Framer"

    When I follow "Flexible Framer"
    Then print current URL

    When I fill in "Framer title" with "My Framer Title"
    And I fill in "Block description" with "My Block Description"
    And I fill in "Item title" with "My Item Title"
    And I press "Add block"

    # TODO: After error on Flexible Framer disappears -> check fields of Flexible Framer and also delete it

    When I am on "/admin/content"
    And I check content with title "TestBasicPageTitle"
    And I press "Apply to selected items"
    Then the url should match "content/node/delete"
    When I press "Delete"
    Then the url should match "admin/content"
