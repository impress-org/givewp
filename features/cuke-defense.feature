Feature: Developer can test his plug-in with a click
  As a developer or designer or tester
  I want to gulp a task called cuke-defense
  So that my cukes run each time a file is modified

  Scenario: File is created
    Given I have an empty directory
    When I add a file to the directory
    Then the cuke-defense task should make a test run

  Scenario: File is modified
    Given I have an empty directory
    When I edit a file in the directory
    Then the cuke-defense task should make a test run