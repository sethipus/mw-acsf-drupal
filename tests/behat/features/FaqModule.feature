#Feature: Faq Module Test
#  @javascript
#  Scenario: Faq Module Test
# TODO uncomment all and add questions and answers when the id or name attributes are added to iframes with questions and answers
#    When I login into Drupal
#    And I follow "Structure"
#    Then I should see "Structure"
#    When I follow "Entityqueues"
#    Then I should see "Entityqueues"
#    And I should see "Add entity queue"
#    And the url should match "admin/structure/entityqueue"
#
#    When I follow "Edit items"
#    Then the url should match "structure/entityqueue/faq_queue/faq_queue"
#
#    When I fill item "Test QA Blurb1" into subqueue FAQ queue
#    And I press "Add item"
#    And I wait for the ajax response
#    Then I should see "Test QA Blurb1"
#    When I fill item "Test QA Blurb2" into subqueue FAQ queue
#    And I press "Add item"
#    And I wait for the ajax response
#    Then I should see "Test QA Blurb2"
#
#    When I follow "Add content"
#    Then I should see "Basic page"
#    When I follow "Basic page"
#    Then I should see "Create Basic page"
#
#    When I fill in "Title" with "TestBasicPageTitle"
#    And I press "Save"
#    Then the url should match "testbasicpagetitle"
#    And I should see "Basic page TestBasicPageTitle has been created."
#    And print current URL
#
#    When I edit added content
#    Then I should see "Edit Basic page"
#
#    When I press the "Layout" section of added content
#    Then print current URL
#    And the url should match "layout"
#    When I follow "Add block "
#    Then print current URL
#    And I wait for the ajax response
#    Then I should see "Choose a block"
#    And I should see "Create custom block"
#
#    When I load page by link with text "FAQ view"
#    Then print current URL
#    When I press "Add block"
#    Then I should see "You are editing the layout for this Basic page content item."
#    And I should see "You have unsaved changes."
#    And I should see "FAQs"
#    And I should see "Q1"
#    And I should see "A1"
#    And I should see "Q2"
#    And I should see "A2"
#
#    When I press "Save layout"
#    Then print current URL
#    And I should see "The layout override has been saved."
#    And I should see a ".views-element-container" element
#    And I should see a ".faq__see_all" element
#    And I should see "FAQs"
#    And I should see "Q1"
#    And I should see "A1"
#    And I should see "Q2"
#    And I should see "A2"
#
#    And I follow "Content"
#    And I check content with title "TestBasicPageTitle"
#    And I press "Apply to selected items"
#    And the url should match "content/node/delete"
#    And I press "Delete"
#    And the url should match "admin/content"
#
#    And I follow "Structure"
#    Then I should see "Structure"
#    When I follow "Entityqueues"
#    Then I should see "Entityqueues"
#    And I should see "Add entity queue"
#    And the url should match "admin/structure/entityqueue"
