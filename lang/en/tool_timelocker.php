<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English language strings for tool_timelocker.
 *
 * @package    tool_timelocker
 * @category   string
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activitiespersession'] = 'Activities per session';
$string['activitiespersession_desc'] = 'How many activities have their gradebook lock date grouped into each session. For example, with a session length of 7 days and 5 activities per session, grades for 5 activities lock every week.';
$string['activitiespersession_help'] = 'How many activities have their gradebook lock date grouped into each session. For example, with a session length of 7 days and 5 activities per session, grades for 5 activities lock every week.';
$string['activitytype'] = 'Activity type';
$string['gradelockednote'] = 'Grades were locked on {$a}';
$string['gradelocknote'] = 'Grades lock after {$a}';
$string['locksapplied'] = 'Updated the gradebook lock date for {$a} activities.';
$string['locktimecolumn'] = 'Gradebook lock';
$string['name'] = 'Name';
$string['nogradableactivities'] = 'This course has no activities with gradebook grade items to lock.';
$string['nolockscheduled'] = '—';
$string['pluginname'] = 'Time locker';
$string['positiveintrequired'] = 'Must be a positive whole number';
$string['privacy:metadata'] = 'The Time locker admin tool only stores course-level configuration (schedule and session settings) and does not store any personal user data.';
$string['refresh'] = 'Refresh';
$string['resetunselected'] = 'Reset unselected';
$string['resetunselected_help'] = 'Clear the gradebook lock date of any activity of this type that is not selected below.';
$string['schedulestart'] = 'Schedule start';
$string['schedulestart_help'] = 'The date the first locking session starts counting from. Selected activities are grouped into sessions of "Activities per session", and each session\'s grade items lock at the end of its session window.';
$string['selectactivity'] = 'Select {$a}';
$string['sessionlength'] = 'Session length (days)';
$string['sessionlength_desc'] = 'The default length, in days, of each locking session.';
$string['sessionlength_help'] = 'The length, in days, of each locking session.';
$string['shownote'] = 'Show student note';
$string['shownote_desc'] = 'Default value for whether a student-facing note about the gradebook lock date is shown on the activity page.';
$string['shownote_help'] = 'Show a note on the activity page telling students when its grade will lock, or was locked.';
$string['shownoteforactivity'] = 'Show note for {$a}';
$string['timelocker:manage'] = 'Manage bulk gradebook lock scheduling';
$string['timelockerforcourse'] = 'Time locker for {$a}';
$string['togglenotes'] = 'Toggle the show-note setting of all activities';
$string['toggleselection'] = 'Toggle selection of all activities';
$string['willlockon'] = 'Locks {$a}';
