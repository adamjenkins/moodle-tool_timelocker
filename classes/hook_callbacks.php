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
 * Hook listener callbacks for the tool_timelocker plugin.
 *
 * @package    tool_timelocker
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_timelocker;

/**
 * Hook listener that adds a student-facing grade-lock note to activity pages.
 */
class hook_callbacks {
    /**
     * Add a note near the activity dates telling students when the
     * activity's grade will lock, or was locked.
     *
     * Triggered on every page, so it must cheaply bail out unless the
     * current page is an activity ({@see \moodle_page::$cm}) that has been
     * explicitly configured (via {@see \tool_timelocker\timelocker}) to
     * show the note.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook
     */
    public static function add_activity_lock_note(
        \core\hook\output\before_standard_top_of_body_html_generation $hook
    ): void {
        global $PAGE, $DB, $OUTPUT, $CFG;

        // NOTE: $PAGE->cm is a magic property (moodle_page::magic_get_cm()) and
        // moodle_page defines __get() but not __isset(), so isset()/empty() checks
        // directly on $PAGE->cm always treat it as unset. Read it into a local
        // variable first so the emptiness check actually reflects its value.
        $cm = $PAGE->cm;
        if (empty($cm)) {
            return;
        }

        // Course-scoped lookup: the item must belong to a tool_timelocker
        // configuration row for THIS course, so a note row can never affect
        // an activity in another course (defense in depth).
        $sql = "SELECT i.id
                  FROM {tool_timelocker_items} i
                  JOIN {tool_timelocker} t ON t.id = i.timelockerid
                 WHERE i.cmid = :cmid AND i.shownote = 1 AND t.courseid = :courseid";
        $item = $DB->get_record_sql($sql, ['cmid' => $cm->id, 'courseid' => $cm->course]);
        if (!$item) {
            return;
        }

        require_once($CFG->libdir . '/gradelib.php');
        $items = \grade_item::fetch_all([
            'courseid' => $cm->course,
            'itemtype' => 'mod',
            'itemmodule' => $cm->modname,
            'iteminstance' => $cm->instance,
        ]);
        if (!$items) {
            return;
        }

        // Earliest future locktime / lock state across the cm's grade items.
        $locktime = 0;
        $lockedat = 0;
        foreach ($items as $gi) {
            if ($gi->is_locked()) {
                $lockedat = $lockedat ? min($lockedat, (int) $gi->locked) : (int) $gi->locked;
            }
            $lt = (int) $gi->get_locktime();
            if ($lt > 0) {
                $locktime = $locktime ? min($locktime, $lt) : $lt;
            }
        }
        if (!$lockedat && !$locktime) {
            return;
        }

        $html = $OUTPUT->render_from_template('tool_timelocker/locknote', [
            'islocked' => (bool) $lockedat,
            'date' => userdate($lockedat ?: $locktime),
        ]);
        $PAGE->add_header_extras($html);
    }
}
