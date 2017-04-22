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
 * Inserts the bugtracker action.
 * @param array $actionArray The array with actions.
 */
function fxt_actions(&$actionArray)
{
	// Add the action! Quick!
	$actionArray['bugtracker'] = array('Bugtracker.php', 'BugTrackerMain');
}

/**
 * Inserts the bugtracker permissions.
 * @param array $permissionGroups The permission groups.
 * @param array $permissionList   The permission list.
 */
function fxt_permissions(&$permissionGroups, &$permissionList)
{
	// Permission groups...
	$permissionGroups['membergroup']['simple'] = array('fxt_simple');
	$permissionGroups['membergroup']['classic'] = array('fxt_classic');

	// Permission name => any and own (true) or not (false)
	$permissions = array(
		'view' => false,
		'viewprivate' => false,
		'add' => false,

		'edit' => true,
		'remove' => true,
		'restore' => true,
		'move' => true,

		'mark' => true,
		'mark_new' => true,
		'mark_wip' => true,
		'mark_done' => true,
		'mark_reject' => true,
		'mark_attention' => true,

		'add_note' => true,
		'edit_note' => true,
		'remove_note' => true,
	);

	// Insert the permissions.
	foreach ($permissions as $perm => $ownany)
	{
		if ($ownany)
		{
			$permissionList['membergroup']['bt_' . $perm . '_own'] = array(false, 'fxt_classic', 'fxt_simple');
			$permissionList['membergroup']['bt_' . $perm . '_any'] = array(false, 'fxt_classic', 'fxt_simple');
		}
		else
			$permissionList['membergroup']['bt_' . $perm] = array(false, 'fxt_classic', 'fxt_simple');
	}
}
function template_fxt_copyright_below(){global $fxt_copytext,$txt;echo sprintf($fxt_copytext, $txt['fxt_ver']);}

/**
 * Inserts the bugtracker button.
 * @param array $menu_buttons The menu buttons
 */
function fxt_menubutton(&$menu_buttons)
{
	global $txt, $scripturl, $modSettings, $smcFunc, $user_profile, $context, $sourcedir;

	if (allowedTo('bt_view') && !empty($modSettings['bt_enable']) && !empty($modSettings['bt_show_button_important']))
	{
		$result = $smcFunc['db_query']('', '
			SELECT count(id_entry)
			FROM {db_prefix}bugtracker_entries
			WHERE attention = 1');

		list ($count) = $smcFunc['db_fetch_row']($result);

		$smcFunc['db_free_result']($result);
	}

	$button = array(
		'bugtracker' => array(
			'title' => $txt['bugtracker'] . (!empty($modSettings['bt_show_button_important']) && !empty($count) ? ' <span class="amt">' . $count . '</span>' : ''),
			'href' => $scripturl . '?action=bugtracker',
			'show' => allowedTo('bt_view') && !empty($modSettings['bt_enable']),
			'sub_buttons' => array()
		),
	);

	// Insert the button, Inter-style.
	array_insert($menu_buttons, 3, $button);
}

// 'cus we like it this way. Thanks Inter for this code!
if (!function_exists('array_insert'))
{
	function array_insert(&$array, $position, $insert_array)
	{
		$first_array = array_splice($array, 0, $position);
		$array = array_merge($first_array, $insert_array, $array);
	}
}

/**
 * Load the language file, this early so things like Alerts work.
 * No parameters.
 */
function fxt_preload()
{
	loadLanguage('BugTracker');
}

/**
 * Inserts the bugtracker admin areas.
 * @param array $areas The admin areas.
 */
function fxt_adminareas(&$areas)
{
	global $txt;
	loadLanguage('BugTracker');

	$areas['fxtracker'] = array(
		'title' => $txt['bt_acp_button'],
		'permission' => array('bt_admin'),
		'areas' => array(
			'projects' => array(
				'label' => $txt['bt_acp_projects'],
				'file' => 'FXTracker/Admin.php',
				'icon' => 'boards',
				'function' => 'bt_manage_projects',
			),
			'fxtsettings' => array(
				'label' => $txt['bt_acp_settings'],
				'file' => 'FXTracker/Admin.php',
				'icon' => 'maintain',
				'function' => 'bt_settings',
			),
			'fxtaddsettings' => array(
				'label' => $txt['bt_acp_addsettings'],
				'file' => 'FXTracker/Admin.php',
				'icon' => 'modifications',
				'function' => 'bt_add_settings',
				'subsections' => array(
					'notes' => array($txt['notes']),
					'entries' => array($txt['entries']),
					'filter' => array($txt['filter'])
				)
			)
		)
	);
}
