Feature: Card Grid Test
  @javascript
  Scenario: Card Grid Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Grid Card"
    And I select "Contact messages" from "View"
    Then I should see "Please wait..."

    When I wait for the ajax response
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see a "//div[@data-block-plugin-id='grid_card']" xpath element
    And I should see a "//input[@id='edit-submit-contact-messages']" xpath element
    And I should see "Subject"
    And I should see "Sender's name"
    And I should see "Contact form"
    And I should see "Created"
    And I should see "Operations"
    And I should see a ".form-item__dropdown" element

    When I select "Contact Form" from "form"
    And I press "Apply"
    Then the url should match "/admin/structure/contact/messages"
    And I should see a "//label[@class='form-item__label' and text()='Contact form']" xpath element
