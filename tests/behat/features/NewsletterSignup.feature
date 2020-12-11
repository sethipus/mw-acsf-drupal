Feature: Newsletter Signup Test
  @javascript
  Scenario: Newsletter Signup Test
    When I login into Drupal
    And I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Newsletter SignUp Form"
    Then I should see "Configure block"
    And I should see "Block description"
    And I should see "MARS: Newsletter SignUp Form"

    When I fill in "Form ID" with "Widget1"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see a "//div[@data-block-plugin-id='newsletter_signup_form' and @class='block']" xpath element
    And I should see a ".newsletter-iframe" element
    And I should see a ".newsletter-iframe__inner" element

    When I am on "/node/add/page"
    And I fill in "Title" with "TestBasicPageTitle"
    And I press "Save"
    And I edit added content
    And I press the "Layout" section of added content
    And I follow "Add block "
    And I wait for the ajax response
    And I load page by link with text "MARS: Newsletter SignUp Form"
    Then I should see "Configure block"
    And I should see "Block description"
    And I should see "MARS: Newsletter SignUp Form"

    When I fill in "Form ID" with "Widget2"
    And I press "Add block"
    Then I should see "You are editing the layout for this Basic page content item."

    When I press "Save layout"
    Then I should see "The layout override has been saved."
    And I should see a "//div[@data-block-plugin-id='newsletter_signup_form' and @class='block']" xpath element
    And I should see a ".newsletter-iframe" element
    And I should see a ".newsletter-iframe__inner" element
