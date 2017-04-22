<?php

/*
 * FXTracker - A Bug Tracker for SMF
 * ------------------------------------------
 * @package   FXTracker
 * @author    Yoshi2889
 * @copyright Yoshi2889 2012-2013
 * @license   http://creativecommons.org/licenses/by-sa/3.0/deed.en_US CC-BY-SA
 */

/**
 * Initialisation function for FXTracker.
 */
function BugTrackerMain()
{
	// Our usual stuff.
	global $context, $txt, $sourcedir, $scripturl, $modSettings, $smcFunc;

	// Are we allowed to view this?
	isAllowedTo('bt_view');

	if (empty($modSettings['bt_enable']))
		fatal_lang_error('bt_disabled');

	// Load the language and template. Oh, don't forget our CSS file, either.
	loadLanguage('BugTracker');
	loadTemplate('fxt/Copyright', 'bugtracker');

	// Add the template layer for our copyright.
	$context['template_layers'][] = 'fxt_copyright';

	// A list of all actions we can take.
	// 'action' => array('source file', 'bug tracker function'),
	$sactions = array(
		// Notes.
		'addnote' => array('Edit', 'AddNote'),
		'addnote2' => array('Edit', 'AddNote2'),

		// Editing entries and notes.
		'edit' => array('Edit', 'Edit'),
		'edit2' => array('Edit', 'Edit'), // <-- Compatibility and a JIC scenario.
		'editnote' => array('Edit', 'EditNote'),
		'editnote2' => array('Edit', 'EditNote2'),

		// Home.
		'home' => array('Home', 'Home'),

		// Marking entries.
		'mark' => array('Edit', 'MarkEntry'),

		'new' => array('Edit', 'NewEntry'),
		'new2' => array('Edit', 'SubmitNewEntry'),

		'projectindex' => array('View', 'ViewProject'),

		'remove' => array('Edit', 'RemoveEntry'),
		'removenote' => array('Edit', 'RemoveNote'),
		'restore' => array('Edit', 'RestoreEntry'),

		'test' => array('Maintenance', 'InsertDummyData'),
		'trash' => array('View', 'ViewTrash'),
		
		'unread' => array('Unread', 'UnreadEntries'),

		'maintenance' => array('Maintenance', 'Maintenance'),
		'maintenance2' => array('Maintenance', 'PerformMaintenance'),

		'view' => array('View', 'View'),

		'qmod' => array('QuickMod', 'DoQuickMod'),
	);

	// Allow mod creators to easily snap in.
	call_integration_hook('integrate_bugtracker_actions', array(&$sactions));
	if (!function_exists('template_fxt_copyright_below'))
		fatal_error('The FXTracker copyright is missing!');

	// Default is home.
	if (empty($_GET['sa']) || empty($sactions[$_GET['sa']]))
		$_GET['sa'] = 'home';

	require($sourcedir . '/FXTracker/' . $sactions[$_GET['sa']][0] . '.php');
	$action = $_GET['sa'];

	// And add a bit onto the linktree.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=bugtracker',
		'name' => $txt['bugtracker'],
	);

	// Then, execute the function!
	call_helper('BugTracker' . $sactions[$action][1]);
}