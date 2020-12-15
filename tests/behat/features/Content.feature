Feature: Content Test
  @javascript
  Scenario: Content Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    Then I should see "Choose a block"

    When I load page by link with text "MARS: Content Feature Module"
    Then I should see "Block description"
    And I should see "Content Feature Module"
    And I should see "Eyebrow"
    And I should see "Title"
    And I should see "Background"
    And I should see "Description"

    When I fill in "Eyebrow" with "MyEyebrow"
    And I fill in "Title" with "MyTitle"
    And I click on a "//input[@data-drupal-selector='edit-settings-background-browser-entity-browser-open-modal']" xpath element
    And I wait for the ajax response
    And I switch to the iframe "entity_browser_iframe_lighthouse_browser"
    And I wait for the ajax response
    And I press "Upload"
    And I attach the file "icon.png" to "File"
    And I wait until the "//a[@type='image/png; length=1174']" xpath element appears
    Then I should see "(1.15 KB)"

    When I select "Image" from "Bundle"
    And I wait until the "//details[contains(@class, 'claro-details')]" xpath element appears
    And I fill in "Alternative text" with "Alternative text1"
    And I fill in "Name" with "Name1"
    And I fill in "URL alias" with "/error1"
    And I press "Select"
    And I wait for the ajax response
    And I switch to the main window
    And I fill in "Description" with "MyDescription"
    And I fill in "URL" with "http://link.com"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."
    And print current URL

    When I press "Save layout"
    Then print current URL
    And I should see a ".content-feature" element
    And I should see "MYEYEBROW"
    And I should see "MyTitle"
    And I should see "MyDescription"
    And I should see a "//a[contains(@href,'http://link.com')]/span[contains(text(), 'Explore')]" xpath element
