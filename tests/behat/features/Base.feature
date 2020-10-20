@api
Feature: Baseline Tests
  Scenario: Ensure Site is Accessible
    Given I am on "/"
    Then the response status code should be 200
    And print current URL
