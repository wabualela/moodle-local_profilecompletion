@local @local_profilecompletion @javascript
Feature: Post-login profile completion prompt
  In order to complete configured profile fields quickly
  As a user with missing configured profile data
  I should be prompted with a notification and modal form

  Background:
    Given the following users exist:
      | username | password | firstname | lastname | email                 | city | country |
      | learner1 | test     | Learner   | One      | learner1@example.com  |      | SA      |
    And the following config values are set as admin:
      | enabled   | 1         | local_profilecompletion |
      | fieldkeys | core:city | local_profilecompletion |

  Scenario: Prompt is shown and city is saved from modal
    When I log in as "learner1"
    Then I should see "Complete your profile"
    And I should see "Fill missing fields"
    When I click on "Fill missing fields" "button"
    And I set the field "City/town" to "Riyadh"
    And I press "Save changes"
    Then I should not see "Complete your profile"
