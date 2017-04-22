<?php

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot uninstall - please verify you put this file in the same place as SMF\'s SSI.php.');

db_extend('packages');

// Get rid of the tables altogether.
global $smcFunc;

$tables = array(
	'entries',
	'projects',
	'notes',
	'log_mark_read',
	'log_actions'
);

foreach ($tables as $table)
	$smcFunc['db_drop_table']('bugtracker_' . $table);

// And remove these from the settings. Code based on that from SimpleDesk!
$to_remove = array(
	'bt_enable',
	'bt_show_button_important',
	'fxt_maintenance_enable',
	'fxt_maintenance_message',
	'bt_num_latest',
	'bt_show_attention_home',
	'bt_hide_done_button',
	'bt_hide_reject_button',
	
	'bt_total_entries',
	'bt_total_issues',
	'bt_total_features',
	'bt_total_important',

	'bt_enable_notes',
	'bt_quicknote',
	'bt_quicknote_primary',
	'bt_entry_progress_steps',

	'fxt_posttopic_enable',
	'fxt_posttopic_board',
	'fxt_show_topic_prefix',
	'fxt_lock_topic',
	'fxt_topic_message',
);

global $modSettings;

// Disable the bug tracker. Doesn't cause database queries that way.
$modSettings['bt_enable'] = false;

// And remove them.
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable IN ({array_string:settings})',
	array(
		'settings' => $to_remove,
	)
);

// Done.
if (SMF == 'SSI')
	die('The database has been altered. The settings (' . count($to_remove) . ') for FXTracker have been removed, and the tables (' . count($tables) . ') have been dropped.');

?>
