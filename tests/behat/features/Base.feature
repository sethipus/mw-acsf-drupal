Feature: Baseline Tests
  @javascript
  Scenario: Ensure Site is Accessible
    Given I am on "/"
    Then print current URL
