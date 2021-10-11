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
 * Strings for component 'message_culactivity_stream', language 'en'
 *
 * @package    message
 * @subpackage culactivity_stream
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

$string['eventdata'] = 'Cron Job';
$string['eventdata_update_desc'] = 'Cron job to reduce the size of the message_culactivity_stream
table by moving relevant data from eventdata field to other fields.';
$string['message_culactivity_stream'] = 'message_culactivity_stream';
$string['pluginname'] = 'CUL Activity Feed';

$string['privacy:metadata:message_culactivity_stream'] = 'CUL Activity Stream Messages';
$string['privacy:metadata:message_culactivity_stream:sent'] = 'The ID of the user who sent the notification';
$string['privacy:metadata:message_culactivity_stream:userid'] = 'The ID of the user who received the notification';
$string['privacy:metadata:message_culactivity_stream:userfromid'] = 'The ID of the user who sent the notification';
$string['privacy:metadata:message_culactivity_stream:courseid'] = 'The ID of the course related to the notification';
$string['privacy:metadata:message_culactivity_stream:smallmessage'] = 'The small message of the notification';
$string['privacy:metadata:message_culactivity_stream:component'] = 'The component responsible for sending the notification';
$string['privacy:metadata:message_culactivity_stream:timecreated'] = 'The date at which the notification was created';
$string['privacy:metadata:message_culactivity_stream:contexturl'] = 'The URL related to this notification';
$string['privacy:metadata:message_culactivity_stream:deleted'] = 'Whether or not the user deleted the notification';
$string['privacy:metadata:message_culactivity_stream:timedeleted'] = 'The date at which the notification was deleted';
$string['update'] = 'Run the cron';
