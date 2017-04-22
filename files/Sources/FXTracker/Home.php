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
 * Shows the home page.
 * No parameters - called by action.
 */
function BugTrackerHome()
{
        // Global some stuff
	global $smcFunc, $context, $user_info, $user_profile, $txt, $sourcedir, $modSettings, $scripturl;
	
	// We'll need the power of Class-Project.
	require_once($sourcedir . '/FXTracker/Class-Projects.php');
	$projects = new FXTracker_ProjectList();
	
	// And now, we get the projects.
	$context['projects'] = $projects->getAll();
	
	require_once($sourcedir . '/FXTracker/Class-Entries.php');
	
	// Lets grab the latest entries, if we enabled that.
	if (!empty($modSettings['bt_num_latest']))
	{
		// Set up our entry loader.
		$entries = new FXTracker_Entries();
		$entries->scope('e.in_trash', '0');
		$entries->limit($modSettings['bt_num_latest']);
		
		// We first want our issues...
		$entries->scope('e.type', 'issue');
		$context['recent']['issue'] = $entries->get();
		
		// Then we'd like some features as well.
		$entries->scope('e.type', 'feature');
		$context['recent']['feature'] = $entries->get();
	}
	
	// Anything attention?
	if (!empty($modSettings['bt_show_attention_home']))
	{
		// We're going to create a list for this.
		$entries = new FXTracker_Entries();
		$entries->scope('e.attention', '1');
		$entries->scope('e.in_trash', '0');
		$entries->createList($scripturl . '?action=bugtracker', 'fxt_important');
	}

	// Load our Home template.
	loadTemplate('fxt/Home');
	$context['sub_template'] = 'TrackerHome';
	
	// Set the page title.
	$context['page_title'] = $txt['bt_index'];
}
