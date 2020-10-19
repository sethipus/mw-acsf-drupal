#@base
#Feature: Authorization Tests
#  Scenario: Authorization Test
#    Given I am on "/"
#    Then the response status code should be 200
#    When I go to "/admin"
#    Then the response status code should be 403
#    And I should see "You are not authorized to access this page."
#    When I login into Drupal
#    Then the response status code should be 200
#    When I go to "/admin"
#    Then the response status code should be 200
#    And I should see "Home"
#    And I should see "Moderation Dashboard"
#    When I login into Drupal with used url
#    Then the response status code should be 403
#    And I should see "You are not authorized to access this page."
