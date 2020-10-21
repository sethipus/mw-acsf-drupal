@api
Feature: Baseline Tests
  Scenario: Ensure Site is Accessible
    Given I am on "/"
    Then print current URL
