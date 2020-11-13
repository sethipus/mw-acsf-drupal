Feature: Home Page Hero Test
  @javascript
  Scenario: Home Page Hero Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    Then I should see "Choose a block"
    And I should see "Create custom block"

    When I load page by link with text "Homepage Hero block"
    Then I should see "Configure block"
    And I should see "Block description"
    And I should see "Eyebrow"
    And I should see "Title Link URL"
    And I should see "Title label"
    And I should see "CTA Link URL"
    And I should see "CTA Link Title"

    When I fill in "Eyebrow" with "eye"
    And I fill in "Title Link URL" with "http://www.title.com"
    And I fill in "Title label" with "label"
    And I fill in "CTA Link URL" with "http://www.url.com"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see a ".homepage-hero-standard" element
    And I should see a ".homepage-hero-standard__container" element
    And I should see "eye"
    And I should see "label"
    And I should see "Explore"
    And I should see a "//a[contains(@href,'http://www.title.com')]" xpath element

    When I follow "Content"
    And I check content with title "TestBasicPageTitle"
    And I press "Apply to selected items"
    Then the url should match "content/node/delete"
    When I press "Delete"
    Then the url should match "admin/content"
