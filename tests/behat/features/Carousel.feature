Feature: Carousel Test
  @javascript
  Scenario: Carousel Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Carousel component"
    Then I should see "Configure block"
    And I should see "Block description"

    When I fill in "Carousel title" with "my_carousel"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see a "//div[@data-block-plugin-id='carousel_block' and @class='block']" xpath element
    And I should see a ".carousel" element
    And I should see a ".carousel__heading" element
    And I should see a ".swiper-container" element
    And I should see a ".swiper-wrapper" element
    And I should see a ".carousel__content" element
