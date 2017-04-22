<?php

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot uninstall - please verify you put this file in the same place as SMF\'s SSI.php.');

// Get rid of the integration hooks.
$hooks = array(
	'integrate_pre_include' => '$sourcedir/Bugtracker-Hooks.php',
	'integrate_actions' => 'fxt_actions',
	'integrate_load_permissions' => 'fxt_permissions',
	'integrate_menu_buttons' => 'fxt_menubutton',
	'integrate_admin_areas' => 'fxt_adminareas'
);

foreach ($hooks as $hook => $data)
	remove_integration_function($hook, $data);

// And we're done.
if (SMF == 'SSI')
	die('The hooks (' . count($hooks) . ') for FXTracker have been removed.');

?>
