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

namespace tool_timelocker;

/**
 * Unit tests for the tool_timelocker plugin.
 *
 * @package    tool_timelocker
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class timelocker_test extends \advanced_testcase {
    /**
     * eligible_course_modtypes() should include module types that have a
     * grade item in the course, and exclude those that do not.
     *
     * @covers \tool_timelocker\modtypes::eligible_course_modtypes
     */
    public function test_eligible_gradable_modtypes(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_module('quiz', ['course' => $course->id, 'grade' => 100]);
        $this->getDataGenerator()->create_module('label', ['course' => $course->id]);
        $eligible = \tool_timelocker\modtypes::eligible_course_modtypes($course->id);
        $this->assertArrayHasKey('quiz', $eligible);
        $this->assertArrayNotHasKey('label', $eligible);
    }

    /**
     * compute_lockdates() must stagger lock dates by session, grouping
     * activities into sessions of $perssession, in the order given.
     *
     * @covers \tool_timelocker\timelocker::compute_lockdates
     */
    public function test_compute_lockdates_staggers_by_session(): void {
        $start = 1000000; // Arbitrary base.
        $day = DAYSECS;
        $cmids = [11, 12, 13, 14, 15];
        $mgr = new \tool_timelocker\timelocker();
        // Session length 7 days, 2 activities per session.
        $dates = $mgr->compute_lockdates($cmids, $start, 7, 2);
        $this->assertSame($start + 1 * 7 * $day, $dates[11]);
        $this->assertSame($start + 1 * 7 * $day, $dates[12]);
        $this->assertSame($start + 2 * 7 * $day, $dates[13]);
        $this->assertSame($start + 2 * 7 * $day, $dates[14]);
        $this->assertSame($start + 3 * 7 * $day, $dates[15]);
    }

    /**
     * apply_locks() must write the computed lock timestamp to every
     * itemtype='mod' grade item of each selected course module.
     *
     * @covers \tool_timelocker\timelocker::apply_locks
     */
    public function test_apply_locks_writes_grade_item_locktime(): void {
        global $CFG;
        require_once($CFG->libdir . '/gradelib.php');
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $q1 = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id, 'grade' => 100]);
        $q2 = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id, 'grade' => 100]);
        $mgr = new \tool_timelocker\timelocker();
        $cm1 = get_coursemodule_from_instance('quiz', $q1->id)->id;
        $cm2 = get_coursemodule_from_instance('quiz', $q2->id)->id;
        $start = 2000000000;
        $dates = $mgr->compute_lockdates([$cm1, $cm2], $start, 7, 1);
        $count = $mgr->apply_locks($dates, 'quiz', $course->id, false);
        $this->assertSame(2, $count);
        $gi1 = \grade_item::fetch(['courseid' => $course->id, 'itemtype' => 'mod', 'itemmodule' => 'quiz', 'iteminstance' => $q1->id]);
        $this->assertSame($dates[$cm1], (int) $gi1->get_locktime());
    }

    /**
     * apply_locks() with $resetunselected must clear the locktime on
     * activities of the modtype that are not present in $lockdates.
     *
     * @covers \tool_timelocker\timelocker::apply_locks
     */
    public function test_apply_locks_resets_unselected(): void {
        global $CFG;
        require_once($CFG->libdir . '/gradelib.php');
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $qa = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id, 'grade' => 100]);
        $qb = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id, 'grade' => 100]);
        $mgr = new \tool_timelocker\timelocker();
        $cma = get_coursemodule_from_instance('quiz', $qa->id)->id;
        $cmb = get_coursemodule_from_instance('quiz', $qb->id)->id;
        $start = 2000000000;

        // First, lock quiz A.
        $initialdates = $mgr->compute_lockdates([$cma], $start, 7, 1);
        $mgr->apply_locks($initialdates, 'quiz', $course->id, false);
        $gia = \grade_item::fetch([
            'courseid' => $course->id,
            'itemtype' => 'mod',
            'itemmodule' => 'quiz',
            'iteminstance' => $qa->id,
        ]);
        $this->assertNotSame(0, (int) $gia->get_locktime());

        // Now apply with only quiz B selected and resetunselected = true.
        $newdates = $mgr->compute_lockdates([$cmb], $start, 7, 1);
        $mgr->apply_locks($newdates, 'quiz', $course->id, true);

        $gia = \grade_item::fetch([
            'courseid' => $course->id,
            'itemtype' => 'mod',
            'itemmodule' => 'quiz',
            'iteminstance' => $qa->id,
        ]);
        $this->assertSame(0, (int) $gia->get_locktime());
    }

    /**
     * update() must upsert the course's tool_timelocker row and rebuild the
     * tool_timelocker_items rows, including each item's own shownote value.
     *
     * @covers \tool_timelocker\timelocker::update
     */
    public function test_update_persists_config_and_items(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $q1 = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id, 'grade' => 100]);
        $q2 = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id, 'grade' => 100]);
        $cm1 = get_coursemodule_from_instance('quiz', $q1->id)->id;
        $cm2 = get_coursemodule_from_instance('quiz', $q2->id)->id;

        $mgr = new \tool_timelocker\timelocker();
        $formdata = new \stdClass();
        $formdata->modtype = 'quiz';
        $formdata->schedulestart = 2000000000;
        $formdata->sessionlength = 7;
        $formdata->activitiespersession = 1;
        $formdata->shownote = 1;
        $formdata->resetunselected = 0;
        $formdata->cmids = [$cm1, $cm2];
        $formdata->shownote_cmids = [$cm1];

        $settings = $mgr->update($formdata, $course->id);

        $this->assertNotEmpty($settings->id);
        $row = $DB->get_record('tool_timelocker', ['courseid' => $course->id]);
        $this->assertNotFalse($row);
        $this->assertSame('quiz', $row->modtype);
        $this->assertSame(2000000000, (int) $row->schedulestart);
        $this->assertSame(7, (int) $row->sessionlength);
        $this->assertSame(1, (int) $row->activitiespersession);
        $this->assertSame(1, (int) $row->shownote);

        $items = $DB->get_records('tool_timelocker_items', ['timelockerid' => $row->id], 'cmid ASC');
        $this->assertCount(2, $items);
        $items = array_values($items);
        $this->assertSame((int) $cm1, (int) $items[0]->cmid);
        $this->assertSame(1, (int) $items[0]->shownote);
        $this->assertSame((int) $cm2, (int) $items[1]->cmid);
        $this->assertSame(0, (int) $items[1]->shownote);
    }
}
