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
 * The course gradebook lock scheduling form.
 *
 * @package    tool_timelocker
 * @copyright  2026 Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_timelocker\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Lets a teacher choose an activity type, a lock schedule and per-session
 * settings for the tool_timelocker course scheduling page.
 *
 * The activity selection table (select / show-note checkboxes) is rendered
 * separately, in view.php, outside this moodleform's own quickform elements.
 * Its checkboxes carry an HTML5 form="" attribute pointing at this form's id
 * (self::FORM_ID) so they still post as part of the same request; view.php
 * reads them with optional_param_array() rather than via get_data().
 */
class timelocker_form extends \moodleform {
    /** @var string the HTML id assigned to this form, so checkboxes rendered
     *  outside it can still submit as part of it via a form="" attribute. */
    const FORM_ID = 'tool_timelocker_form';

    /**
     * Define the form elements.
     */
    public function definition() {
        $mform = $this->_form;
        $mform->updateAttributes(['id' => self::FORM_ID]);

        $courseid = (int) $this->_customdata['courseid'];
        $modules = $this->_customdata['modules'];
        $modtype = $this->_customdata['modtype'];
        $settings = (object) $this->_customdata['settings'];

        $course = get_course($courseid);

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement(
            'header',
            'timelockerheader',
            get_string('timelockerforcourse', 'tool_timelocker', $course->shortname)
        );
        $mform->setExpanded('timelockerheader');

        // Module type selector plus a refresh button to re-display the form for
        // the chosen type; the modform AMD module also auto-submits on change.
        $group = [];
        $group[] = $mform->createElement('select', 'modtype', get_string('activitytype', 'tool_timelocker'), $modules);
        $group[] = $mform->createElement('submit', 'refresh', get_string('refresh', 'tool_timelocker'));
        $mform->addGroup($group, 'modtypegroup', get_string('activitytype', 'tool_timelocker'), [' '], false);
        $mform->setDefault('modtype', $modtype);

        $mform->addElement('date_time_selector', 'schedulestart', get_string('schedulestart', 'tool_timelocker'));
        $mform->setDefault('schedulestart', $settings->schedulestart ?? time());
        $mform->addHelpButton('schedulestart', 'schedulestart', 'tool_timelocker');

        $mform->addElement('text', 'sessionlength', get_string('sessionlength', 'tool_timelocker'), ['size' => 3]);
        $mform->setType('sessionlength', PARAM_INT);
        $mform->addRule('sessionlength', null, 'required', null, 'client');
        $mform->addRule(
            'sessionlength',
            get_string('positiveintrequired', 'tool_timelocker'),
            'regex',
            '/^[1-9][0-9]*$/',
            'client'
        );
        $mform->setDefault('sessionlength', $settings->sessionlength ?? get_config('tool_timelocker', 'sessionlength'));
        $mform->addHelpButton('sessionlength', 'sessionlength', 'tool_timelocker');

        $mform->addElement(
            'text',
            'activitiespersession',
            get_string('activitiespersession', 'tool_timelocker'),
            ['size' => 3]
        );
        $mform->setType('activitiespersession', PARAM_INT);
        $mform->addRule('activitiespersession', null, 'required', null, 'client');
        $mform->addRule(
            'activitiespersession',
            get_string('positiveintrequired', 'tool_timelocker'),
            'regex',
            '/^[1-9][0-9]*$/',
            'client'
        );
        $mform->setDefault(
            'activitiespersession',
            $settings->activitiespersession ?? get_config('tool_timelocker', 'activitiespersession')
        );
        $mform->addHelpButton('activitiespersession', 'activitiespersession', 'tool_timelocker');

        $mform->addElement('advcheckbox', 'shownote', get_string('shownote', 'tool_timelocker'));
        $mform->addHelpButton('shownote', 'shownote', 'tool_timelocker');
        $mform->setDefault('shownote', $settings->shownote ?? get_config('tool_timelocker', 'shownote'));

        $mform->addElement('advcheckbox', 'resetunselected', get_string('resetunselected', 'tool_timelocker'));
        $mform->addHelpButton('resetunselected', 'resetunselected', 'tool_timelocker');
        $mform->setDefault('resetunselected', $settings->resetunselected ?? 0);
        $mform->setAdvanced('resetunselected');

        $this->add_action_buttons();
    }

    /**
     * Add "Save and return to course", "Save and display" and "Cancel"
     * buttons, mirroring the standard activity module forms.
     *
     * @param bool $cancel whether to show a cancel button.
     * @param string|null $submitlabel label for the save-and-display button.
     * @param string|null $submit2label label for the save-and-return button.
     */
    public function add_action_buttons($cancel = true, $submitlabel = null, $submit2label = null) {
        if (is_null($submitlabel)) {
            $submitlabel = get_string('savechangesanddisplay');
        }
        if (is_null($submit2label)) {
            $submit2label = get_string('savechangesandreturntocourse');
        }
        $mform = $this->_form;

        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton2', $submit2label);
        if ($submitlabel !== false) {
            $buttonarray[] = $mform->createElement('submit', 'submitbutton', $submitlabel);
        }
        if ($cancel) {
            $buttonarray[] = $mform->createElement('cancel');
        }
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->setType('buttonar', PARAM_RAW);
    }

    /**
     * Validate the submitted schedule and session settings.
     *
     * @param array $data submitted form data.
     * @param array $files submitted files.
     * @return array field => error message.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        $modules = $this->_customdata['modules'];
        if (!array_key_exists($data['modtype'], $modules)) {
            $errors['modtypegroup'] = get_string('activitytype', 'tool_timelocker');
        }

        if ((int) $data['sessionlength'] < 1) {
            $errors['sessionlength'] = get_string('positiveintrequired', 'tool_timelocker');
        }

        if ((int) $data['activitiespersession'] < 1) {
            $errors['activitiespersession'] = get_string('positiveintrequired', 'tool_timelocker');
        }

        return $errors;
    }
}
