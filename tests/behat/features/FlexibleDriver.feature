Feature: Flexible Driver Test
  @javascript
  Scenario: Flexible Driver Test
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
    And I should see "Flexible driver"
    When I load page by link with text "Flexible driver"
    Then I should see "Block description"
    And I should see "Flexible driver"
    When I fill in "Title" with "MyTitle"
    And I fill in "CTA Link" with "http://link.com"

    And I sleep "1" seconds
    And I wait until the "//input[@value='Select entities']" xpath element appears
    And I click on a "(//input[@value='Select entities'])[1]" xpath element
    And I wait for the ajax response

    When I switch to the iframe "entity_browser_iframe_lighthouse_browser"
    And I wait for the ajax response

    And I press "Upload"
    And I attach the file "icon.png" to "File"
    And I wait until the "//a[@type='image/png; length=1174']" xpath element appears
    And I should see "(1.15 KB)"

    And I select "Image" from "Bundle"
    And I wait until the "//details[contains(@class, 'claro-details')]" xpath element appears

    And I fill in "Alternative text" with "alternative_text_1"
    And I fill in "Name" with "name_1"
    And I fill in "URL alias" with "/image_1"
    And I press "Select"
    And I wait for the ajax response

    And I switch to the main window

    And I click on a "(//input[@value='Select entities'])[2]" xpath element
    And I wait for the ajax response

    When I switch to the iframe "entity_browser_iframe_lighthouse_browser"
    And I wait for the ajax response

    And I press "Upload"
    And I attach the file "icon.png" to "File"
    And I wait until the "//a[@type='image/png; length=1174']" xpath element appears
    And I should see "(1.15 KB)"

    And I select "Image" from "Bundle"
    And I wait until the "//details[contains(@class, 'claro-details')]" xpath element appears

    And I fill in "Alternative text" with "alternative_text_1"
    And I fill in "Name" with "name_2"
    And I fill in "URL alias" with "/image_2"
    And I press "Select"
    And I wait for the ajax response

    And I switch to the main window

    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."
    When I press "Save layout"
    Then I should see "Title"
    And I should see a "//a[contains(@href,'http://link.com')]/span[contains(text(), 'Learn more')]" xpath element
