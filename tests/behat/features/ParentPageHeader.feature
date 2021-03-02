Feature: Parent Page Header Test
  @javascript
  Scenario: Parent Page Header Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "test_basic_page_title_7"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Parent Page Header"
    Then I should see "Block description"
    And I should see "MARS: Parent Page Header"

    When I fill in "Eyebrow" with "eyebrow_2"
    And I fill in "Title" with "title_2"
    And I fill in "Description" with "description_2"
    And I click on a "//*[@id='edit-settings-background-options-image']" xpath element
    And I click on a "//input[@data-drupal-selector='edit-settings-background-image-browser-entity-browser-open-modal']" xpath element
    And I wait for the ajax response
    And I switch to the iframe "entity_browser_iframe_lighthouse_browser"
    And I wait for the ajax response
    And I press "Upload"
    And I attach the file "icon.png" to "File"
    And I wait until the "//a[@type='image/png; length=1174']" xpath element appears
    Then I should see "(1.15 KB)"

    When I select "Image" from "Bundle"
    And I wait until the "//details[contains(@class, 'claro-details')]" xpath element appears
    And I fill in "Alternative text" with "icon_alternative_text_2"
    And I fill in "Name" with "icon_name_2"
    And I fill in "URL alias" with "/icon_2"
    And I press "Select"
    And I wait for the ajax response
    And I switch to the main window
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see "eyebrow_2"
    And I should see "title_2"
    And I should see "description_2"
    And I should see a "//div[@data-block-plugin-id='parent_page_header']" xpath element
    And I should see a ".parent-page-header--with-bg-media" element

    When I am on "/node/add/page"
    And I fill in "Title" with "test_basic_page_title_7"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Parent Page Header"
    And I fill in "Eyebrow" with "eyebrow_2"
    And I fill in "Title" with "title_2"
    And I fill in "Description" with "description_2"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see "eyebrow_2"
    And I should see "title_2"
    And I should see "description_2"
    And I should see a "//div[@data-block-plugin-id='parent_page_header']" xpath element
