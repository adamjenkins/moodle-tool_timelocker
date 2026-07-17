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
}
