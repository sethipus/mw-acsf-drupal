Feature: Image Inline Test
  @javascript
  Scenario: Image Inline Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Inline image/video block"
    Then I should see "Configure block"
    And I should see "Block description"

    When I fill in "Title" with "image_title"
    And I fill in "Description" with "image_description"
    And I click on a "//input[@data-drupal-selector='edit-settings-image-browser-entity-browser-open-modal']" xpath element
    And I wait for the ajax response
    And I switch to the iframe "entity_browser_iframe_lighthouse_browser"
    And I wait for the ajax response
    And I press "Upload"
    And I attach the file "icon.png" to "File"
    And I wait until the "//a[@type='image/png; length=1174']" xpath element appears
    Then I should see "(1.15 KB)"

    When I select "Image" from "Bundle"
    And I wait until the "//details[contains(@class, 'claro-details')]" xpath element appears
    And I fill in "Alternative text" with "icon_alternative_text"
    And I fill in "Name" with "icon_name"
    And I fill in "URL alias" with "/icon"
    And I press "Select"
    And I wait for the ajax response
    And I switch to the main window
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see a "//div[@data-block-plugin-id='inline_image_video_block' and @class='block']" xpath element
    And I should see a ".block" element
    And I should see a ".article-inline" element
    And I should see a ".article-inline__media" element
    And I should see a ".image" element
    And I should see a ".article-inline__content" element
    And I should see "image_description"

    When I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Fullwidth image/video block"
    Then I should see "Configure block"
    And I should see "Block description"

    When I fill in "Title" with "image_title"
    And I fill in "Description" with "image_description"
    And I click on a "//input[@data-drupal-selector='edit-settings-image-browser-entity-browser-open-modal']" xpath element
    And I wait for the ajax response
    And I switch to the iframe "entity_browser_iframe_lighthouse_browser"
    And I wait for the ajax response
    And I press "Upload"
    And I attach the file "icon.png" to "File"
    And I wait until the "//a[@type='image/png; length=1174']" xpath element appears
    And I should see "(1.15 KB)"
    And I select "Image" from "Bundle"
    And I wait until the "//details[contains(@class, 'claro-details')]" xpath element appears
    And I fill in "Alternative text" with "icon_alternative_text"
    And I fill in "Name" with "icon_name"
    And I fill in "URL alias" with "/icon"
    And I press "Select"
    And I wait for the ajax response
    And I switch to the main window
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see a "//div[@data-block-plugin-id='fullwidth_image_video_block' and @class='block']" xpath element
    And I should see a ".block" element
    And I should see a ".article-full-width" element
    And I should see a ".article-full-width__heading" element
    And I should see a ".article-full-width__media" element
    And I should see a ".image " element
    And I should see "image_title"
    And I should see "image_description"
