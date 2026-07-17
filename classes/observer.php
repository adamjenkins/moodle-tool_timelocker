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
 * Event observers for tool_timelocker.
 *
 * @package    tool_timelocker
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_timelocker;

/**
 * Observer class handling core events for tool_timelocker.
 */
class observer {
    /**
     * Clean up this plugin's course-level rows when a course is deleted.
     *
     * Moodle does not cascade DB foreign keys, so tool_timelocker and
     * tool_timelocker_items rows for the deleted course would otherwise be
     * orphaned. Deletes the items rows first (via a subquery on the
     * course's tool_timelocker id), then the tool_timelocker row itself.
     *
     * @param \core\event\course_deleted $event The course_deleted event.
     */
    public static function course_deleted(\core\event\course_deleted $event): void {
        global $DB;

        $courseid = $event->courseid;

        $DB->delete_records_select(
            'tool_timelocker_items',
            'timelockerid IN (SELECT id FROM {tool_timelocker} WHERE courseid = :courseid)',
            ['courseid' => $courseid]
        );

        $DB->delete_records('tool_timelocker', ['courseid' => $courseid]);
    }
}
