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
 * Upgrade code for the CUL Activity Stream
 *
 * @package    message
 * @subpackage culactivity_stream
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

/**
 * Upgrade code for the local_culactivity_stream plugin
 *
 * @param int $oldversion The version that we are upgrading from
 */
function xmldb_message_culactivity_stream_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    if ($oldversion < 2014060601) {

        $dbman = $DB->get_manager();

        // Changing type of field contexturl on table message_culactivity_stream to text.
        $table = new xmldb_table('message_culactivity_stream');
        $field = new xmldb_field('contexturl', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timecreated');

        // Launch change of type for field contexturl.
        $dbman->change_field_type($table, $field);

        // Changing nullability of field contexturl on table message_culactivity_stream to null.
        $field = new xmldb_field('contexturl', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timecreated');

        // Launch change of nullability for field contexturl.
        $dbman->change_field_notnull($table, $field);

        // Changing the default of field contexturl on table message_culactivity_stream to drop it.
        $field = new xmldb_field('contexturl', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timecreated');

        // Launch change of default for field contexturl.
        $dbman->change_field_default($table, $field);

        // Changing the default of field component on table message_culactivity_stream_q to local_culactivity_stream.
        $table = new xmldb_table('message_culactivity_stream_q');
        $field = new xmldb_field(
            'component',
            XMLDB_TYPE_CHAR,
            '255',
            null,
            XMLDB_NOTNULL,
            null,
            'local_culactivity_stream',
            'smallmessage'
        );

        // Launch change of default for field component.
        $dbman->change_field_default($table, $field);

        // Changing nullability of field modulename on table message_culactivity_stream_q to null.
        $field = new xmldb_field('modulename', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'component');

        // Launch change of nullability for field modulename.
        $dbman->change_field_notnull($table, $field);

        // Changing the default of field modulename on table message_culactivity_stream_q to drop it.
        $field = new xmldb_field('modulename', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'component');

        // Launch change of default for field modulename.
        $dbman->change_field_default($table, $field);

        // Changing type of field contexturl on table message_culactivity_stream_q to text.
        $field = new xmldb_field('contexturl', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timecreated');

        // Launch change of type for field contexturl.
        $dbman->change_field_type($table, $field);

        // Changing nullability of field contexturl on table message_culactivity_stream_q to null.
        $field = new xmldb_field('contexturl', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timecreated');

        // Launch change of nullability for field contexturl.
        $dbman->change_field_notnull($table, $field);

        // Changing the default of field contexturl on table message_culactivity_stream_q to drop it.
        $field = new xmldb_field('contexturl', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timecreated');

        // Launch change of default for field contexturl.
        $dbman->change_field_default($table, $field);

        // Changing type of field contexturlname on table message_culactivity_stream_q to text.
        $field = new xmldb_field('contexturlname', XMLDB_TYPE_TEXT, null, null, null, null, null, 'contexturl');

        // Launch change of type for field contexturlname.
        $dbman->change_field_type($table, $field);

        // Changing nullability of field contexturlname on table message_culactivity_stream_q to null.
        $field = new xmldb_field('contexturlname', XMLDB_TYPE_TEXT, null, null, null, null, null, 'contexturl');

        // Launch change of nullability for field contexturlname.
        $dbman->change_field_notnull($table, $field);

        // Changing the default of field contexturlname on table message_culactivity_stream_q to drop it.
        $field = new xmldb_field('contexturlname', XMLDB_TYPE_TEXT, null, null, null, null, null, 'contexturl');

        // Launch change of default for field contexturlname.
        $dbman->change_field_default($table, $field);

        // Culactivity_stream savepoint reached.
        upgrade_plugin_savepoint(true, 2014060601, 'message', 'culactivity_stream');
    }

    return true;
}
