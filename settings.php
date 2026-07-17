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
 * Site-level default settings for tool_timelocker.
 *
 * @package    tool_timelocker
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('tool_timelocker_settings', new lang_string('pluginname', 'tool_timelocker'));

    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configtext(
            'tool_timelocker/sessionlength',
            get_string('sessionlength', 'tool_timelocker'),
            get_string('sessionlength_desc', 'tool_timelocker'),
            7,
            PARAM_INT,
            3
        ));

        $settings->add(new admin_setting_configtext(
            'tool_timelocker/activitiespersession',
            get_string('activitiespersession', 'tool_timelocker'),
            get_string('activitiespersession_desc', 'tool_timelocker'),
            5,
            PARAM_INT,
            3
        ));

        $settings->add(new admin_setting_configcheckbox(
            'tool_timelocker/shownote',
            get_string('shownote', 'tool_timelocker'),
            get_string('shownote_desc', 'tool_timelocker'),
            1
        ));
    }

    $ADMIN->add('tools', $settings);
}
