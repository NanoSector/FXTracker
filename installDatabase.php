<?php

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this file in the same place as SMF\'s SSI.php.');

db_extend('packages');

global $smcFunc, $modSettings;

// Add in the hooks...
$hooks = array(
	'integrate_pre_include' => '$sourcedir/Bugtracker-Hooks.php',
	'integrate_actions' => 'fxt_actions',
	'integrate_load_permissions' => 'fxt_permissions',
	'integrate_menu_buttons' => 'fxt_menubutton',
	'integrate_admin_areas' => 'fxt_adminareas',
	'integrate_pre_load' => 'fxt_preload'
);

foreach ($hooks as $hook => $data)
	add_integration_function($hook, $data);

// Then add in the settings...
$settingsArray = array(
	'bt_enable' => true,
	'bt_show_button_important' => false,
	'fxt_maintenance_enable' => false,
	'fxt_maintenance_message' => 'Okay yer trackers, we\'re down for some maintenance... Check back later!',
	'bt_num_latest' => 5,
	'bt_show_attention_home' => true,
	'bt_hide_done_button' => false,
	'bt_hide_reject_button' => false,

	'bt_enable_notes' => true,
	'bt_quicknote' => true,
	'bt_quicknote_primary' => false,
	'bt_entry_progress_steps' => 10,

	'fxt_posttopic_enable' => false,
	'fxt_posttopic_board' => '0',
	'fxt_show_topic_prefix' => 'none',
	'fxt_lock_topic' => false,
	'fxt_topic_message' => '[url=%1$s]Link to entry[/url]

%2$s posted a new entry:
[quote]%3$s[/quote]

You can check it out in the bug tracker.
[b]This topic will NOT be updated when the bug tracker entry has been edited.[/b]',

);
updateSettings($settingsArray);

if (!isset($modSettings['bt_total_entries']))
	$smcFunc['db_insert']('insert',
		'{db_prefix}settings',
		array(
			'variable' => 'string',
			'value' => 'string',
		),
		array(
			array(
				'bt_total_entries',
				'0',
			),
			array(
				'bt_total_issues',
				'0',
			),
			array(
				'bt_total_features',
				'0',
			),
			array(
				'bt_total_important',
				'0',
			),
		),
		array());

// And last but not least create the tables. Empty. Here goes table 1.
/*
 * {db_prefix}bugtracker_entries
 */
$columns = array(
	array('name' => 'id_entry', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true, 'auto' => true),
	array('name' => 'id_first_note', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true),
	array('name' => 'id_last_note', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true),
	array('name' => 'id_project', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true),
	array('name' => 'title', 'type' => 'text'),
	array('name' => 'status', 'type' => 'text'),
	array('name' => 'type', 'type' => 'text'),
	array('name' => 'attention', 'type' => 'tinyint', 'size' => 1, 'unsigned' => true),
	array('name' => 'progress', 'type' => 'tinyint', 'size' => 3, 'unsigned' => true),
	array('name' => 'in_trash', 'type' => 'tinyint', 'size' => 1, 'unsigned' => true)
);

// And the index for it.
$indexes = array(array('type' => 'primary', 'columns' => array('id_entry')));
$smcFunc['db_create_table']('{db_prefix}bugtracker_entries', $columns, $indexes, array(), 'ignore');

/*
 * {db_prefix}bugtracker_projects
 */
$columns = array(
	array('name' => 'id_project', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true, 'auto' => true),
	array('name' => 'title', 'type' => 'text'),
	array('name' => 'description', 'type' => 'text'),
	array('name' => 'id_last_entry', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true),
	array('name' => 'num_entries', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true),
	array('name' => 'num_open_entries', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true)
);

// And the index for it.
$indexes = array(array('type' => 'primary', 'columns' => array('id_project')));
$smcFunc['db_create_table']('{db_prefix}bugtracker_projects', $columns, $indexes, array(), 'ignore');


/*
 * {db_prefix}bugtracker_notes
 */
$columns = array(
	array('name' => 'id_note', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true, 'auto' => true),
	array('name' => 'id_entry', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true),
	array('name' => 'id_author', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true),
	array('name' => 'posted_time', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true),
	array('name' => 'note', 'type' => 'text')
);

// And the index for it.
$indexes = array(array('type' => 'primary', 'columns' => array('id_note')));
$smcFunc['db_create_table']('{db_prefix}bugtracker_notes', $columns, $indexes, array(), 'ignore');

/*
 * {db_prefix}bugtracker_log_entries
 */
$columns = array(
	array('name' => 'id_member', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true),
	array('name' => 'id_entry', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true),
        array('name' => 'id_note', 'type' => 'mediumint', 'size' => 11, 'unsigned' => true)
);


$indexes = array();
$smcFunc['db_create_table']('{db_prefix}bugtracker_log_entries', $columns, $indexes, array(), 'ignore');

/*
 * {db_prefix}bugtracker_log_actions
 */
$columns = array(
	array('name' => 'id_entry', 'type' => 'int', 'size' => 11, 'unsigned' => true),
	array('name' => 'id_user', 'type' => 'int', 'size' => 11, 'unsigned' => true),
	array('name' => 'type', 'type' => 'text'),
	array('name' => 'from', 'type' => 'text'),
	array('name' => 'to', 'type' => 'text'),
	array('name' => 'time', 'type' => 'int', 'size' => 11, 'unsigned' => true)
);


$indexes = array();
$smcFunc['db_create_table']('{db_prefix}bugtracker_log_actions', $columns, $indexes, array(), 'ignore');

// That's it folks!
if (SMF == 'SSI')
	die('Installation of FXTracker is now complete. You can proceed by entering your forum.');

?>
