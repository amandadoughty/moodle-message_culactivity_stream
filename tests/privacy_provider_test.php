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
 * Privacy provider tests.
 *
 * @package    message_culactivity_stream
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace message_culactivity_stream;

use core_privacy\local\metadata\collection;
use message_culactivity_stream\privacy\provider;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\transform;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider tests class.
 *
 * @group culactivity
 *
 * @package    message_culactivity_stream
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class privacy_provider_test extends \core_privacy\tests\provider_testcase {

    /**
     * Test for provider::get_metadata().
     */
    public function test_get_metadata() {
        $collection = new collection('message_culactivity_stream');
        $newcollection = provider::get_metadata($collection);
        $itemcollection = $newcollection->get_collection();
        $this->assertCount(1, $itemcollection);

        $messagestable = array_shift($itemcollection);
        $this->assertEquals('message_culactivity_stream', $messagestable->get_name());

        $privacyfields = $messagestable->get_privacy_fields();
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('userfromid', $privacyfields);
        $this->assertArrayHasKey('courseid', $privacyfields);
        $this->assertArrayHasKey('smallmessage', $privacyfields);
        $this->assertArrayHasKey('component', $privacyfields);
        $this->assertArrayHasKey('timecreated', $privacyfields);
        $this->assertArrayHasKey('contexturl', $privacyfields);
        $this->assertArrayHasKey('deleted', $privacyfields);
        $this->assertArrayHasKey('timedeleted', $privacyfields);

        $this->assertEquals('privacy:metadata:message_culactivity_stream', $messagestable->get_summary());
    }

    /**
     * Test for provider::get_contexts_for_userid()
     */
    public function test_get_contexts_for_userid() {
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();

        // Test nothing is found before notification is created.
        $contextlist = provider::get_contexts_for_userid($user1->id);
        $this->assertCount(0, $contextlist);
        $contextlist = provider::get_contexts_for_userid($user2->id);
        $this->assertCount(0, $contextlist);

        $this->create_notification($user1->id, $user2->id, $course1->id, time() - (9 * DAYSECS));

        // Test for the sender.
        $contextlist = provider::get_contexts_for_userid($user1->id);
        $this->assertCount(1, $contextlist);
        $contextforuser = $contextlist->current();
        $this->assertEquals(
                \context_user::instance($user1->id)->id,
                $contextforuser->id);

        // Test for the receiver.
        $contextlist = provider::get_contexts_for_userid($user2->id);
        $this->assertCount(1, $contextlist);
        $contextforuser = $contextlist->current();
        $this->assertEquals(
                \context_user::instance($user2->id)->id,
                $contextforuser->id);
    }

    /**
     * Test for provider::get_users_in_context() when there is a notification between users.
     */
    public function test_get_users_in_context() {
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();

        $user1context = \context_user::instance($user1->id);
        $user2context = \context_user::instance($user2->id);

        // Test nothing is found before notification is created.
        $userlist = new \core_privacy\local\request\userlist($user1context, 'message_culactivity_stream');
        \message_culactivity_stream\privacy\provider::get_users_in_context($userlist);
        $this->assertCount(0, $userlist);
        $userlist = new \core_privacy\local\request\userlist($user2context, 'message_culactivity_stream');
        \message_culactivity_stream\privacy\provider::get_users_in_context($userlist);
        $this->assertCount(0, $userlist);

        $this->create_notification($user1->id, $user2->id, $course1->id, time() - (9 * DAYSECS));

        // Test for the sender.
        $userlist = new \core_privacy\local\request\userlist($user1context, 'message_culactivity_stream');
        \message_culactivity_stream\privacy\provider::get_users_in_context($userlist);
        $this->assertCount(1, $userlist);
        $userincontext = $userlist->current();
        $this->assertEquals($user1->id, $userincontext->id);

        // Test for the receiver.
        $userlist = new \core_privacy\local\request\userlist($user2context, 'message_culactivity_stream');
        \message_culactivity_stream\privacy\provider::get_users_in_context($userlist);
        $this->assertCount(1, $userlist);
        $userincontext = $userlist->current();
        $this->assertEquals($user2->id, $userincontext->id);
    }

    /**
     * Test for provider::export_user_data().
     */
    public function test_export_for_context() {
        $this->resetAfterTest();

        // Create users to test with.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        // Create course.
        $course1 = $this->getDataGenerator()->create_course();

        $now = time();

        // Send notifications from user 1 to user 2.
        $this->create_notification($user1->id, $user2->id, $course1->id, $now + (9 * DAYSECS));
        $this->create_notification($user2->id, $user1->id, $course1->id, $now + (8 * DAYSECS));
        $this->create_notification($user1->id, $user2->id, $course1->id, $now + (7 * DAYSECS));

        // Send notifications from user 3 to user 1.
        $this->create_notification($user3->id, $user1->id, $course1->id, $now + (6 * DAYSECS));
        $this->create_notification($user1->id, $user3->id, $course1->id, $now + (5 * DAYSECS));
        $this->create_notification($user3->id, $user1->id, $course1->id, $now + (4 * DAYSECS));

        // Send notifications from user 3 to user 2 - should not be part of the export.
        $this->create_notification($user3->id, $user2->id, $course1->id, $now + (3 * DAYSECS));
        $this->create_notification($user2->id, $user3->id, $course1->id, $now + (2 * DAYSECS));
        $this->create_notification($user3->id, $user2->id, $course1->id, $now + (1 * DAYSECS));

        $user1context = \context_user::instance($user1->id);

        $this->export_context_data_for_user($user1->id, $user1context, 'message_culactivity_stream');

        $writer = writer::with_context($user1context);

        $this->assertTrue($writer->has_any_data());

        // Confirm the notifications.
        $notifications = (array) $writer->get_data([get_string('message_culactivity_stream', 'message_culactivity_stream')]);

        $this->assertCount(6, $notifications);
    }

    /**
     * Test for provider::delete_data_for_all_users_in_context().
     */
    public function test_delete_data_for_all_users_in_context() {
        global $DB;

        $this->resetAfterTest();

        // Create users to test with.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        // Create course.
        $course1 = $this->getDataGenerator()->create_course();

        $now = time();
        $user1context = \context_user::instance($user1->id);

        // Create notifications.
        $n1 = $this->create_notification($user1->id, $user2->id, $course1->id, $now + (9 * DAYSECS));
        $n2 = $this->create_notification($user2->id, $user1->id, $course1->id, $now + (8 * DAYSECS));
        $n3 = $this->create_notification($user2->id, $user3->id, $course1->id, $now + (7 * DAYSECS));

         // There should be 3 notifications.
        $this->assertEquals(3, $DB->count_records('message_culactivity_stream'));
        provider::delete_data_for_all_users_in_context($user1context);
        // Confirm there is only 1 notification.
        $this->assertEquals(1, $DB->count_records('message_culactivity_stream'));
        // And it is not related to user1.
        $this->assertEquals(0,
                $DB->count_records_select('message_culactivity_stream', 'userfromid = ? OR userid = ? ', [$user1->id, $user1->id]));
    }

    /**
     * Test for provider::delete_data_for_user().
     */
    public function test_delete_data_for_user() {
        global $DB;

        $this->resetAfterTest();

        // Create users to test with.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        // Create course.
        $course1 = $this->getDataGenerator()->create_course();

        $now = time();
        $timeread = $now - DAYSECS;

        // Create notifications.
        $n1 = $this->create_notification($user1->id, $user2->id, $course1->id, $now + (9 * DAYSECS), $timeread);
        $n2 = $this->create_notification($user2->id, $user1->id, $course1->id, $now + (8 * DAYSECS));
        $n2 = $this->create_notification($user2->id, $user3->id, $course1->id, $now + (8 * DAYSECS));

        // There should be three notifications.
        $this->assertEquals(3, $DB->count_records('message_culactivity_stream'));

        $user1context = \context_user::instance($user1->id);
        $contextlist = new \core_privacy\local\request\approved_contextlist($user1, 'message_culactivity_stream',
            [$user1context->id]);
        provider::delete_data_for_user($contextlist);

        // Confirm the user 2 data still exists.
        $notifications = $DB->get_records('message_culactivity_stream');
        $this->assertCount(1, $notifications);
        ksort($notifications);

        $notification = array_shift($notifications);
        $this->assertEquals($user2->id, $notification->userfromid);
        $this->assertEquals($user3->id, $notification->userid);
    }

    /**
     * Test for provider::delete_data_for_users().
     */
    public function test_delete_data_for_users() {
        global $DB;

        $this->resetAfterTest();

        // Create users to test with.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();
        $user5 = $this->getDataGenerator()->create_user();
        $user6 = $this->getDataGenerator()->create_user();
        // Create course.
        $course1 = $this->getDataGenerator()->create_course();

        $now = time();

        // Create notifications.
        $n1 = $this->create_notification($user1->id, $user2->id, $course1->id, $now + (9 * DAYSECS));
        $n2 = $this->create_notification($user2->id, $user1->id, $course1->id, $now + (8 * DAYSECS));
        $n2 = $this->create_notification($user2->id, $user3->id, $course1->id, $now + (8 * DAYSECS));

        // There should be three notifications.
        $this->assertEquals(3, $DB->count_records('message_culactivity_stream'));

        $user1context = \context_user::instance($user1->id);
        $approveduserlist = new \core_privacy\local\request\approved_userlist($user1context, 'message_culactivity_stream',
                [$user1->id, $user2->id]);
        provider::delete_data_for_users($approveduserlist);

        // Only user1's data should be deleted. User2 should be skipped as user2 is an invalid user for user1context.

        // Confirm the user 2 data still exists.
        $notifications = $DB->get_records('message_culactivity_stream');

        $this->assertCount(1, $notifications);
        ksort($notifications);

        $notification = array_shift($notifications);
        $this->assertEquals($user2->id, $notification->userfromid);
        $this->assertEquals($user3->id, $notification->userid);
    }

    /**
     * Creates a notification to be used for testing.
     *
     * @param int $userfromid The user id from
     * @param int $userid The user id to
     * @param int $courseid The course the message relates to
     * @param int|null $timecreated The time the notification was created
     * @return int The id of the notification
     * @throws dml_exception
     */
    private function create_notification(int $userfromid, int $userid, int $courseid, int $timecreated = null) {
        global $DB;

        if (is_null($timecreated)) {
            $timecreated = time();
        }

        $record = new \stdClass();
        $record->userfromid = $userfromid;
        $record->userid = $userid;
        $record->courseid = $courseid;
        $record->smallmessage = 'Someone posted in a forum';
        $record->component = 'mod_forum';
        $record->timecreated = $timecreated;
        $record->contexturl = 'http://moodle.dev.city.ac.uk/mod/forum/discuss.php?d=309';

        return $DB->insert_record('message_culactivity_stream', $record);
    }
}
