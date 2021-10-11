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
 * Tests the message processor.
 *
 * @package message_culactivity_stream
 * @category test
 * @copyright 2020 Amanda Doughty
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* vendor/bin/phpunit message/output/culactivity_stream/tests/message_send_test.php */

defined('MOODLE_INTERNAL') || die();

/**
 * Class for testing the message processor.
 *
 * @group culactivity
 *
 * @package message_culactivity_stream
 * @category test
 * @copyright 2020 Amanda Doughty
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class message_culactivity_stream_send_message_testcase extends advanced_testcase {

    /**
     * Test send message.
     */
    public function test_send_message() {
        global $DB;

        $this->preventResetByRollback(); // Messaging is not compatible with transactions.

        $this->resetAfterTest();

        // Create the test data.
        $course = $this->getDataGenerator()->create_course();

        $user1 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $user2 = $this->getDataGenerator()->create_and_enrol($course, 'teacher');

        $eventdata = new \core\message\message();
        $eventdata->courseid = $course->id;
        $eventdata->name = 'gradenotifications';
        $eventdata->component = 'moodle';
        $eventdata->userfrom = $user2;
        $eventdata->subject = 'message subject';
        $eventdata->fullmessage = 'message body';
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml = '<p>message body</p>';
        $eventdata->smallmessage = 'small message';
        $eventdata->notification = 1;
        $eventdata->userto = $user1;

        // Send the message twice.
        $messageid1 = message_send($eventdata);
        $messageid2 = message_send($eventdata);

        // Check there are now 2 messages in the activity feed table.
        $this->assertEquals(2, $DB->count_records('message_culactivity_stream'));
    }
}
