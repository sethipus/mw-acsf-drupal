Feature: Iframe Test
  @javascript
  Scenario: Iframe Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: iFrame"
    Then I should see "Configure block"
    And I should see "Block description"
    And I should see "MARS: iFrame"
    And I fill in "Accessibility Title" with "my_iframe_title"
    And I fill in "URL" with "http://iframe.com"

    When I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see a "//div[@data-block-plugin-id='iframe_block' and @class='block']/div[@class='iframe-container']/iframe[@class='iframe-container__inner' and @title='my_iframe_title']" xpath element
