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
 * Privacy Subsystem implementation for message_culactivity_stream.
 *
 * @package    message_culactivity_stream
 * @copyright  2018 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace message_culactivity_stream\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem implementation for message_culactivity_stream.
 *
 * @copyright  2018 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Return the fields which contain personal data.
     *
     * @param collection $items a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table(
            'message_culactivity_stream',
            [
                'userid' => 'privacy:metadata:message_culactivity_stream:userid',
                'userfromid' => 'privacy:metadata:message_culactivity_stream:userfromid',
                'courseid' => 'privacy:metadata:message_culactivity_stream:courseid',
                'smallmessage' => 'privacy:metadata:message_culactivity_stream:smallmessage',
                'component' => 'privacy:metadata:message_culactivity_stream:component',
                'timecreated' => 'privacy:metadata:message_culactivity_stream:timecreated',
                'contexturl' => 'privacy:metadata:message_culactivity_stream:contexturl',
                'deleted' => 'privacy:metadata:message_culactivity_stream:deleted',
                'timedeleted' => 'privacy:metadata:message_culactivity_stream:timedeleted',
            ],
            'privacy:metadata:message_culactivity_stream'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid the userid.
     * @return contextlist the list of contexts containing user info for the user.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        global $DB;

        $contextlist = new contextlist();

        // Messages are in the user context.
        $hasdata = $DB->record_exists_select(
            'message_culactivity_stream',
            'userfromid = ? OR userid = ?',
            [$userid, $userid]
        );

        if ($hasdata) {
            $contextlist->add_user_context($userid);
        }

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }

        $userid = $context->instanceid;

        $hasdata = $DB->record_exists_select('message_culactivity_stream', 'userid = ? OR userfromid = ?', [$userid, $userid]);

        if ($hasdata) {
            $userlist->add_user($userid);
        }
    }

    /**
     * Export personal data for the given approved_contextlist. User and context information is contained within the contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        // Remove non-user and invalid contexts. If it ends up empty then early return.
        $contexts = array_filter($contextlist->get_contexts(), function($context) use($userid) {
            return $context->contextlevel == CONTEXT_USER && $context->instanceid == $userid;
        });

        if (empty($contexts)) {
            return;
        }

        // Export the message_culactivity_stream.
        self::export_user_data_message_culactivity_stream($userid);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_user) {
            return;
        }

        $userid = $context->instanceid;

        $DB->delete_records_select('message_culactivity_stream', 'userfromid = ? OR userid = ?', [$userid, $userid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        // Remove non-user and invalid contexts. If it ends up empty then early return.
        $contexts = array_filter($contextlist->get_contexts(), function($context) use($userid) {
            return $context->contextlevel == CONTEXT_USER && $context->instanceid == $userid;
        });

        if (empty($contexts)) {
            return;
        }

        $DB->delete_records_select('message_culactivity_stream', 'userid = ? OR userfromid = ?', [$userid, $userid]);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }

        // Remove invalid users. If it ends up empty then early return.
        $userids = array_filter($userlist->get_userids(), function($userid) use($context) {
            return $context->instanceid == $userid;
        });

        if (empty($userids)) {
            return;
        }

        $userid = $context->instanceid;

        $DB->delete_records_select('message_culactivity_stream', 'userfromid = ? OR userid = ?', [$userid, $userid]);
    }

    /**
     * Export the notification data.
     *
     * @param int $userid
     */
    protected static function export_user_data_message_culactivity_stream($userid) {
        global $DB;

        $context = \context_user::instance($userid);

        $notificationdata = [];
        $select = "userid = ? OR userfromid = ?";
        $messageculactivitystream = $DB->get_recordset_select(
            'message_culactivity_stream',
            $select,
            [$userid, $userid],
            'timecreated ASC'
        );

        foreach ($messageculactivitystream as $notification) {
            $timedeleted = !is_null($notification->timedeleted) ? transform::datetime($notification->timedeleted) : '-';

            $data = (object) [
                'courseid' => $notification->courseid,
                'smallmessage' => $notification->smallmessage,
                'component' => $notification->component,
                'timecreated' => transform::datetime($notification->timecreated),
                'contexturl' => $notification->contexturl,
                'deleted' => transform::yesno($notification->deleted),
                'timedeleted' => $timedeleted
            ];

            $notificationdata[] = $data;
        }
        $messageculactivitystream->close();

        writer::with_context($context)->export_data(
            [get_string('message_culactivity_stream', 'message_culactivity_stream')],
            (object) $notificationdata
        );
    }
}