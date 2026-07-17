@tool @tool_timelocker
Feature: Bulk-schedule gradebook lock dates
  In order to lock gradebook grade items on a rolling schedule
  As a teacher
  I need to choose a schedule start, session length and activities per
  session, select activities, and have their gradebook lock dates applied

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | teacher1 | Teacher   | 1        | teacher1@example.com  |
      | student1 | Student   | 1        | student1@example.com  |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity | name  | course | idnumber |
      | quiz     | Quiz1 | C1     | quiz1    |

  Scenario: Teacher schedules a gradebook lock date for a quiz
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Time locker" in current page administration
    Then I should see "Time locker for C1"
    And I should see "Quiz1"

    And I set the field "schedulestart[day]" to "1"
    And I set the field "schedulestart[month]" to "January"
    And I set the field "schedulestart[year]" to "2030"
    And I set the field "sessionlength" to "7"
    And I set the field "activitiespersession" to "5"
    And I set the field with xpath "//tr[contains(normalize-space(.), 'Quiz1')][1]/td[1]/input[@type='checkbox']" to "1"
    And I press "Save and display"

    Then I should see "Updated the gradebook lock date for 1 activities."
    And I should see "8/01/30" in the "Quiz1" "table_row"

  @javascript
  Scenario: Student sees the grade-lock note on an activity with the note enabled
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Time locker" in current page administration
    And I set the field "schedulestart[day]" to "1"
    And I set the field "schedulestart[month]" to "January"
    And I set the field "schedulestart[year]" to "2030"
    And I set the field "sessionlength" to "7"
    And I set the field "activitiespersession" to "5"
    And I set the field with xpath "//tr[contains(normalize-space(.), 'Quiz1')][1]/td[1]/input[@type='checkbox']" to "1"
    And I set the field with xpath "//tr[contains(normalize-space(.), 'Quiz1')][1]/td[last()]/input[@type='checkbox']" to "1"
    And I press "Save and display"
    Then I should see "Updated the gradebook lock date for 1 activities."
    And I log out

    When I log in as "student1"
    And I am on the "Quiz1" "quiz activity" page
    Then I should see "Grades lock after"
