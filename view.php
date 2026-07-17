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
 * Course-level bulk gradebook lock scheduling page.
 *
 * @package    tool_timelocker
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

use tool_timelocker\timelocker;
use tool_timelocker\modtypes;
use tool_timelocker\form\timelocker_form;

$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);

require_login($course);
$context = context_course::instance($courseid);
require_capability('tool/timelocker:manage', $context);

$url = new moodle_url('/admin/tool/timelocker/view.php', ['courseid' => $courseid]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'tool_timelocker'));
$PAGE->set_heading($course->fullname);
navigation_node::override_active_url($url);

$modules = modtypes::eligible_course_modtypes($courseid);

if (empty($modules)) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'tool_timelocker'));
    echo $OUTPUT->notification(
        get_string('nogradableactivities', 'tool_timelocker'),
        \core\output\notification::NOTIFY_INFO
    );
    echo $OUTPUT->footer();
    exit;
}

$config = $DB->get_record('tool_timelocker', ['courseid' => $courseid]) ?: null;

// Resolve the module type to display: an explicit valid request wins, then the
// course's saved type if still eligible, otherwise the first eligible type.
$requested = optional_param('modtype', '', PARAM_ALPHANUMEXT);
if ($requested !== '' && array_key_exists($requested, $modules)) {
    $modtype = $requested;
} else if ($config && array_key_exists($config->modtype, $modules)) {
    $modtype = $config->modtype;
} else {
    $modtype = array_key_first($modules);
}

// A settings-shaped object so the table renders even before anything is saved.
if ($config) {
    $settings = clone $config;
    $settings->modtype = $modtype;
} else {
    $settings = (object) [
        'id' => 0,
        'courseid' => $courseid,
        'modtype' => $modtype,
        'schedulestart' => time(),
        'sessionlength' => (int) get_config('tool_timelocker', 'sessionlength'),
        'activitiespersession' => (int) get_config('tool_timelocker', 'activitiespersession'),
        'shownote' => (int) get_config('tool_timelocker', 'shownote'),
        'resetunselected' => 0,
    ];
}

$manager = new timelocker();

$mform = new timelocker_form($url->out(false), [
    'courseid' => $courseid,
    'modules' => $modules,
    'modtype' => $modtype,
    'settings' => $settings,
]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
}

if ($fromform = $mform->get_data()) {
    // The activity table's select / show-note checkboxes are rendered outside
    // this moodleform's own elements (see timelocker_form's docblock), so read
    // them directly rather than via get_data().
    $fromform->cmids = optional_param_array('cmids', [], PARAM_INT);
    $fromform->shownote_cmids = optional_param_array('shownote_cmids', [], PARAM_INT);

    $settings = $manager->update($fromform, $courseid);
    $modtype = $settings->modtype;

    // A plain modtype-refresh only persists settings and selections; the
    // actual submit buttons also apply the computed lock dates.
    if (isset($fromform->submitbutton) || isset($fromform->submitbutton2)) {
        $tabledata = $manager->get_table_data($settings);
        $selectedcmids = array_column(array_filter($tabledata, fn($row) => $row['selected']), 'cmid');
        $lockdates = $manager->compute_lockdates(
            $selectedcmids,
            (int) $settings->schedulestart,
            (int) $settings->sessionlength,
            (int) $settings->activitiespersession
        );
        $count = $manager->apply_locks($lockdates, $settings->modtype, $courseid, (bool) $settings->resetunselected);
        \core\notification::success(get_string('locksapplied', 'tool_timelocker', $count));

        if (isset($fromform->submitbutton2)) {
            redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
        }
    }
}

$tabledata = $manager->get_table_data($settings);

// Decorate rows for the template: format the pending lock date, and expose a
// dedicated flag rather than relying on a truthy/zero timestamp in mustache.
$dateformat = get_string('strftimedatetimeshort', 'langconfig');
foreach ($tabledata as &$row) {
    $row['haslocktime'] = $row['locktime'] > 0;
    if ($row['haslocktime']) {
        $row['lockdateattr'] = userdate($row['locktime'], '%Y-%m-%dT%H:%M', 99, false, false);
        $row['lockmessage'] = get_string('willlockon', 'tool_timelocker', userdate($row['locktime'], $dateformat));
    } else {
        $row['lockdateattr'] = '';
        $row['lockmessage'] = '';
    }
}
unset($row);

$mform->set_data($settings);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'tool_timelocker'));
$mform->display();
echo $OUTPUT->render_from_template('tool_timelocker/modtable', [
    'formid' => timelocker_form::FORM_ID,
    'modname' => $modtype,
    'tabledata' => $tabledata,
]);
$PAGE->requires->js_call_amd('tool_timelocker/modform', 'init');
echo $OUTPUT->footer();
