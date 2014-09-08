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
 * Contains the definiton of the CUL Activity Stream message processors
 * (adds messages to the table message_culactivity_stream)
 *
 * @package    message
 * @subpackage culactivity_stream
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once($CFG->dirroot.'/message/output/lib.php');

/**
 * Main class for handling messages
 */
class message_output_culactivity_stream extends message_output {

    /**
     * Processes the message, adds it to the message_culactivity_stream table
     * This function is called by send_message function in lib/messagelib.php
     *
     * @global stdClass $CFG
     * @global stdClass $DB
     * @param stdClass $eventdata
     * @return boolean
     */
    public function send_message($eventdata) {
        global $CFG, $DB;

        // Skip any messaging suspended and deleted users.
        if ($eventdata->userto->auth === 'nologin' or $eventdata->userto->suspended or $eventdata->userto->deleted) {
            return true;
        }

        // Insert the notification.
        $notification = new stdClass();
        $notification->courseid = 1;
        $notification->component = 'local_culactivity_stream';
        $notification->userid = $eventdata->userto->id;
        $notification->userfromid = $eventdata->userfrom->id;
        $notification->smallmessage = $eventdata->smallmessage;

        if (isset($eventdata->courseid)) {
            $notification->courseid = $eventdata->courseid;
        }

        if (isset($eventdata->component)) {
            $notification->component = $eventdata->component;
        }

        if (isset($eventdata->contexturl)) {
            $notification->contexturl = $eventdata->contexturl;
        }

        $notification->timecreated = time();
        $result = $DB->insert_record('message_culactivity_stream', $notification);

        return $result;
    }

    /**
     * This defines the config form fragment used on user
     * messaging preferences interface (message/edit.php)
     *
     * @param type $preferences
     * @return null
     */
    public function config_form($preferences) {
        return null;
    }

    /**
     * This processes the data from the config form fragment
     * (used in message/edit.php)
     *
     * @param type $form
     * @param type $preferences
     * @return boolean
     */
    public function process_form($form, &$preferences) {
        return true;
    }

    /**
     * This loads up user config for this plugin set via
     * config form fragment (used in message/edit.php)
     *
     * @global type $USER
     * @param type $preferences
     * @param type $userid
     * @return boolean
     */
    public function load_data(&$preferences, $userid) {
        global $USER;
        return true;
    }

    /**
     * Returns the default message output settings for this output
     *
     * @return int The default settings
     */
    public function get_default_messaging_settings() {
        return MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF;
    }

}
