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
 * Shows the settings page.
 * @param bool $return_config Switch to return the configuration variables.
 */
function bt_settings($return_config = false)
{
	global $txt, $scripturl, $context, $sourcedir;

	require_once($sourcedir . '/ManageServer.php');
	loadLanguage('BugTrackerAdmin');
	loadTemplate('Admin');

	$config_vars = array(
		$txt['fxt_logo'],
		'<a href="' . $scripturl . '?action=admin;area=fxtsettings;recountstats">' . $txt['fxt_recount_stats'] . '</a>',

		'',
		$txt['fxt_general'],
			array('check', 'bt_enable'),
			array('check', 'bt_show_button_important'),

		'',
		$txt['fxt_home'],
			array('int', 'bt_num_latest'),
			array('check', 'bt_show_attention_home'),

		'',
		$txt['fxt_ppage'],
			array('check', 'bt_hide_done_button'),
			array('check', 'bt_hide_reject_button'),
			array('check', 'bt_show_description_ppage')
	);

	if ($return_config)
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=fxtsettings;save';
	$context['page_title'] = $txt['bt_acp_settings_title'];
	$context['settings_title'] = $txt['bt_acp_settings_title'];
	$context['sub_template'] = 'show_settings';
	
	// Add data about the section.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['bt_acp_settings_title'],
		'description' => $txt['bt_acp_settings_desc'],
	);

	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();
		$save_vars = $config_vars;

		saveDBSettings($save_vars);
		redirectexit('action=admin;area=fxtsettings');
	}
	
	// Recounting stats?
	if (isset($_GET['recountstats']))
	{
		// Open up our maintenance file.
		require_once($sourcedir . '/FXTracker/Maintenance.php');
		
		// And fire off the request.
		RecountBTStats();
	}

	prepareDBSettingContext($config_vars);
}

/**
 * Shows the form for adding a new entry.
 * No parameters - called by action.
 */
function bt_manage_projects()
{
        // Need this.
        loadTemplate('fxt/BTACP', 'btacp');
        loadTemplate(false, 'bugtracker');
        loadLanguage('BugTrackerAdmin');

        // Okay... Switch time!
        $areas = array(
                'add' => 'bt_manage_projects_add',
                'home' => 'bt_manage_projects_index',
                'edit' => 'bt_manage_projects_edit',
                'remove' => 'bt_manage_projects_remove',
        );

        $action = 'home';

        if (!empty($_GET['sa']) && isset($areas[$_GET['sa']]))
                $action = $_GET['sa'];
	
        call_helper($areas[$action]);
}

/**
 * Shows the project list.
 * No parameters - called by action.
 */
function bt_manage_projects_index()
{
        global $context, $smcFunc, $txt, $sourcedir, $modSettings, $scripturl;

        $context['page_title'] = $txt['fxt_pmanager'];
	
	// Admin data.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['projects'],
		'description' => $txt['bugtracker_projects_desc'],
	);

        // We're going to create a list for this.
        require_once($sourcedir . '/Subs-List.php');
        $listOptions = array(
		'id' => 'fxt_projects',
		'items_per_page' => $modSettings['defaultMaxMessages'],
		'no_items_label' => sprintf($txt['no_projects'], $scripturl . '?action=admin;area=projects;sa=add'),
		'base_href' => $scripturl . '?action=admin;area=projects',
		'default_sort_col' => 'id',
		'get_items' => array(
			'function' => 'bt_projects_list',
			'params' => array()
		),
		'get_count' => array(
			'function' => 'bt_count_projects',
			'params' => array()
		),
		'columns' => array(
			'id' => array(
				'header' => array('value' => '#'),
				'data' => array(
					'db' => 'id',
                                        'style' => 'width: 10px;',
				),
				'sort' => array(
					'default' => 'id_project ASC',
					'reverse' => 'id_project DESC'
				)
			),
			'name' => array(
				'header' => array('value' => $txt['project_name']),
				'data' => array(
					'db' => 'name',
                                        'style' => 'width: 20%',
				),
				'sort' => array(
					'default' => 'title ASC',
					'reverse' => 'title DESC'
				)
			),
                        'description' => array(
                                'header' => array('value' => $txt['project_desc']),
                                'data' => array(
                                        'db' => 'description',
                                ),
                                'sort' => array(
                                        'default' => 'description ASC',
                                        'reverse' => 'description DESC',
                                )
                        ),
			'issuenum' => array(
				'header' => array('value' => $txt['project_issues']),
				'data' => array(
					'db' => 'issuenum',
                                        'class' => 'centertext',
					'style' => 'width: 40px',
				),
				'sort' => array(
					'default' => 'issuenum ASC',
					'reverse' => 'issuenum DESC'
				)
			),
			'featurenum' => array(
				'header' => array('value' => $txt['project_features']),
				'data' => array(
					'db' => 'featurenum',
					'class' => 'centertext',
					'style' => 'width: 40px',
				),
				'sort' => array(
					'default' => 'featurenum ASC',
					'reverse' => 'featurenum DESC'
				)
			),
                        'delete' => array(
                                'header' => array('value' => $txt['project_actions']),
                                'data' => array(
                                        'db' => 'actions',
                                        'class' => 'righttext',
                                        'style' => 'width:20px',
                                )
                        )
		)
	);

	// Create the list
	createList($listOptions);

        // Hand this over to the templates. We're done here!
        $context['sub_template'] = 'bt_manage_projects_index';
}

/**
 * Grabs a list of the projects.
 * @param int    $start          The number of projects to skip.
 * @param int    $items_per_page The number of projects to show per page.
 * @param string $sort           The way of sorting the projects.
 */
function bt_projects_list($start, $items_per_page, $sort)
{
        global $context, $smcFunc, $scripturl, $txt, $settings, $sourcedir;

        require_once($sourcedir . '/Subs-Post.php');

	// Query time folks!
	$result = $smcFunc['db_query']('', '
		SELECT
                        id_project, title, description
		FROM {db_prefix}bugtracker_projects
		ORDER BY ' . $sort . '
		LIMIT ' . $start . ', ' . $items_per_page,
		array()
	);

        // Format them.
        $projects = array();
        while ($project = $smcFunc['db_fetch_assoc']($result))
        {
                $issueresult = $smcFunc['db_query']('', '
                        SELECT count(id_entry)
                        FROM {db_prefix}bugtracker_entries
                        WHERE type = \'issue\'
                        AND id_project = {int:proj}',
                        array(
                              'proj' => $project['id_project']
                        ));

                list ($issuecount) = $smcFunc['db_fetch_row']($issueresult);
		$smcFunc['db_free_result']($issueresult);

                $featureresult = $smcFunc['db_query']('', '
                        SELECT count(id_entry)
                        FROM {db_prefix}bugtracker_entries
                        WHERE type = \'feature\'
                        AND id_project = {int:proj}',
                        array(
                              'proj' => $project['id_project']
                        ));

                list ($featurecount) = $smcFunc['db_fetch_row']($featureresult);
		$smcFunc['db_free_result']($featureresult);


                $projects[$project['id_project']] = array(
                        'id' => $project['id_project'],
                        'name' => '<a href="' . $scripturl . '?action=admin;area=projects;sa=edit;project=' . $project['id_project'] . '">' . $smcFunc['htmlspecialchars']($project['title']) . '</a>', // This is filtered on save.
                        'description' => parse_bbc($smcFunc['htmlspecialchars']($project['description'])),
                        'issuenum' => $issuecount,
                        'featurenum' => $featurecount,
                        'actions' => '
			<a href="' . $scripturl . '?action=admin;area=projects;sa=edit;project=' . $project['id_project'] . '" title="' . $txt['project_edit'] . '">
                                <img src="' . $settings['images_url'] . '/bugtracker/editc.png" alt="" />
                        </a>
                        <a href="' . $scripturl . '?action=admin;area=projects;sa=remove;project=' . $project['id_project'] . '" title="' . $txt['project_delete'] . '" onclick="return confirm(' . javascriptescape($txt['project_really_delete']) . ')">
                                <img src="' . $settings['images_url'] . '/bugtracker/reject.png" alt="" />
                        </a>'
                );
        }

        // You're free, $result!
        $smcFunc['db_free_result']($result);

        return $projects;
}

/**
 * Gathers the number of projects.
 * No parameters - called by createList.
 */
function bt_count_projects()
{
	global $smcFunc;

	// Just count the amount of projects.
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(id_project)
		FROM {db_prefix}bugtracker_projects',
		array());
	
	list ($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $count;
}

/**
 * Shows the form for editing a project
 * No parameters - called by action.
 */
function bt_manage_projects_edit()
{
	// !!! AJAX?
        global $context, $smcFunc, $sourcedir, $txt, $scripturl;

        // Need this.
        require_once($sourcedir . '/FXTracker/Subs-View.php');

	// Grab the projects.
        $context['bugtracker']['projects'] = grabProjects();

        // Is the project numeric and does it exist...?
        if (!isset($_GET['project']) || !is_numeric($_GET['project']) || !isset($context['bugtracker']['projects'][$_GET['project']]))
                fatal_lang_error('project_no_exist');

        $context['project'] = $context['bugtracker']['projects'][(int) $_GET['project']];

        // Need those.
	require_once($sourcedir . '/Subs-Editor.php');
	include($sourcedir . '/Subs-Post.php');

        // Saving?
        if (!empty($_POST['savingproject']) && array_key_exists($_POST['savingproject'], $context['bugtracker']['projects']))
        {
                $saving = $context['bugtracker']['projects'][$_POST['savingproject']];

                $errors = array();

                // Some stuff not set?
                if ($_POST['proj_name'] === '')
                        $errors[] = $txt['pedit_no_title'];

                if (empty($errors))
                {
                        // Preparse the message.
                        $desc = $smcFunc['htmlspecialchars']($_POST['proj_description']);
			preparsecode($desc);

                        $fproject = array(
                                'title' => $smcFunc['htmlspecialchars']($_POST['proj_name']),
                                'description' => $_POST['proj_description'],
                        );

                        // Then update.
                        $smcFunc['db_query']('', '
                                UPDATE {db_prefix}bugtracker_projects
                                SET
                                        title = {string:name},
                                        description = {string:description}
                                WHERE id_project = {int:id}',
                                array(
                                        'id' => $saving['id'],
                                        'name' => $fproject['title'],
                                        'description' => $fproject['description'],
                                )
                        );

                        // Update the details.
                        $context['project'] = array(
                                'id' => $saving['id'],
                                'name' => $smcFunc['htmlspecialchars']($fproject['title']),
                                'description' => $smcFunc['htmlspecialchars']($fproject['description'])
                        );

                        // Done!
                        redirectexit('action=admin;area=projects');
                }
                else
                        $context['errors'] = $errors;
        }

        // Forcing the success message? :P
        if (isset($_GET['new']))
                $context['success'] = true;

	// Do this...
	un_preparsecode($context['project']['description']);

	// Some settings for it...
	$editorOptions = array(
		'id' => 'proj_description',
		'value' => $smcFunc['htmlspecialchars']($context['project']['description']),
		'height' => '175px',
		'width' => '100%',
		// XML preview.
		'preview_type' => 2,
	);
	create_control_richedit($editorOptions);

        $context['editpage'] = array(
                'url' => $scripturl . '?action=admin;area=projects;sa=edit;project=' . $context['project']['id'],
                'name' => $context['project']['name'],
                'title' => sprintf($txt['pedit_title'], $context['project']['name']),
                'extra' => array(
                        'savingproject' => array(
                                'type' => 'hidden',
                                'name' => 'savingproject',
                                'defaultvalue' => $context['project']['id'],
                        ),
                ),
        );

	// Store the ID. Might need it later on.
	$context['post_box_name'] = $editorOptions['id'];

        // Page title.
        $context['page_title'] = sprintf($txt['pedit_title'], $context['project']['name']);

        // Set the sub template...
        $context['sub_template'] = 'bt_manage_projects_edit';
}

/**
 * Removes a project.
 * No parameters - called by action.
 */
function bt_manage_projects_remove()
{
        global $context, $scripturl, $sourcedir, $smcFunc, $modSettings;

        // Need this.
        include($sourcedir . '/FXTracker/Subs-View.php');

        $context['bugtracker']['projects'] = grabProjects();

        // Got the project?
        if (!empty($_GET['project']) && array_key_exists($_GET['project'], $context['bugtracker']['projects']))
        {
                // Lets get removin'!
                $pid = (int) $_GET['project'];

                // Grab all the entry IDs of this project.
                $result = $smcFunc['db_query']('', '
                        SELECT id_entry, type, attention
                        FROM {db_prefix}bugtracker_entries
                        WHERE id_project = {int:project}',
                        array(
                                'project' => $pid
                        ));

                $eids = array();
                $features = 0;
                $issues = 0;
                $attention = 0;
                while ($eid = $smcFunc['db_fetch_assoc']($result))
                {
                        $eids[] = $eid['id_entry'];
                        if ($eid['type'] == 'feature')
                        	++$features;
                        elseif ($eid['type'] == 'issue')
                        	++$issues;
                        if ($eid['attention'] == 1)
                        	++$attention;
                }

                $smcFunc['db_free_result']($result);

                if (!empty($eids))
		{
                	// Remove every note associated with entries in this project.
                	$smcFunc['db_query']('', '
                	        DELETE FROM {db_prefix}bugtracker_notes
                	        WHERE id_entry IN ({array_int:ids})',
                	        array(
                	                'ids' => $eids,
                	        ));

                	// Then remove all the entries themselves.
                	$smcFunc['db_query']('', '
                	        DELETE FROM {db_prefix}bugtracker_entries
                	        WHERE id_project = {int:pid}',
                	        array(
                	                'pid' => $pid,
                	        )
                	);
		}

                // And kill the project, too.
                $smcFunc['db_query']('', '
                        DELETE FROM {db_prefix}bugtracker_projects
                        WHERE id_project = {int:id}',
                        array(
                                'id' => $pid,
                        )
                );
                
                // Also get the amount of entries down.
                updateSettings(array(
                	'bt_total_entries' => $modSettings['bt_total_entries'] - count($eids),
                	'bt_total_issues' => $modSettings['bt_total_issues'] - $issues,
                	'bt_total_features' => $modSettings['bt_total_features'] - $features,
                	'bt_total_important' => $modSettings['bt_total_important'] - $attention
                ));

                // Now return to the project manage screen.
                redirectexit($scripturl . '?action=admin;area=projects');
        }
        else
                fatal_lang_error('project_no_exist');
}

/**
 * Shows the form for adding a new project.
 * No parameters - called by action.
 */
function bt_manage_projects_add()
{
        global $context, $smcFunc, $scripturl, $txt, $sourcedir;

        // Need those.
	require_once($sourcedir . '/Subs-Editor.php');
	include($sourcedir . '/Subs-Post.php');

        if (isset($_POST['is_fxt']))
        {
                checkSession();

                $errors = array();
                // Anything empty?
                if (empty($_POST['proj_name']))
                        $errors[] = $txt['pedit_no_title'];

                if (empty($_POST['proj_description']))
                        $errors[] = $txt['pedit_no_desc'];

                preparsecode($smcFunc['htmlspecialchars']($_POST['proj_description']));

                $fproject = array(
                        1 => $smcFunc['htmlspecialchars']($_POST['proj_name']),
                        2 => $_POST['proj_description'],
                );

                // No errors? Good.
                if (empty($errors))
                {
                        // Insert it.
                        $smcFunc['db_insert']('insert',
                                '{db_prefix}bugtracker_projects',
                                array(
                                        'title' => 'string',
                                        'description' => 'string',
                                ),
                                $fproject,
                                array()
                        );

                        // Redirect!
                        redirectexit('action=admin;area=projects');
                }
                else
                        $context['errors'] = $errors;

        }

        // Dummy data.
        $context['editpage'] = array(
                'url' => $scripturl . '?action=admin;area=projects;sa=add',
                'name' => (isset($context['errors']) ? $fproject[1] : ''),
                'title' => $txt['padd_title'],
                'extra' => array(
                        'is_fxt' => array(
                                'type' => 'hidden',
                                'name' => 'is_fxt',
                                'defaultvalue' => true
                        ),
                ),
        );

	// Do this...
        if (isset($context['errors']))
                un_preparsecode($fproject[2]);

	// Some settings for it...
	$editorOptions = array(
		'id' => 'proj_description',
		'value' => (isset($context['errors']) ? $fproject[2] : ''),
		'height' => '175px',
		'width' => '100%',
		// XML preview.
		'preview_type' => 2,
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];

        // Page title.
        $context['page_title'] = $txt['padd_title'];

        // Sub template time!
        $context['sub_template'] = 'bt_manage_projects_edit';
}


/**
 * Shows the additional settings screen.
 * No parameters - called by action.
 */
function bt_add_settings()
{
	global $context, $txt, $scripturl, $sourcedir;
	
	// Prerequisites.
	require_once($sourcedir . '/ManageServer.php');
	loadLanguage('BugTrackerAdmin');
	loadLanguage('ManageSettings');
	loadTemplate('Admin');
	
	$subActions = array(
		'notes' => 'bt_notes_settings',
		'entries' => 'bt_entries_features',
		'filter' => 'bt_filter_settings'
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'notes';
	$context['sub_action'] = $_REQUEST['sa'];
	
	// Add data about the section.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['bt_acp_addsettings'],
		'description' => $txt['bt_acp_addsettings_desc'],
	);
	
	// We would like the show_settings template, to actually show our settings.
	$context['sub_template'] = 'show_settings';
	
	// Did we just save?
	if (isset($_GET['saved']))
		$context['saved_successful'] = true;
	
	call_helper($subActions[$_REQUEST['sa']]);
}

function bt_entries_features($return_config = false)
{
	global $txt, $smcFunc, $scripturl, $context, $sourcedir;
	
	$config_vars = array(
			array('check', 'bt_posttopic_enable', 'help' => $txt['bt_posttopic_help']),
			array('boards', 'bt_posttopic_board'),
			array('select', 'bt_posttopic_prefix', array('type1' => '[Issue]', 'type2' => 'Issue:', 'type3' => 'ISSUE:', 'none' => $txt['bt_posttopic_prefix_none'])),
			array('check', 'bt_posttopic_lock'),
			array('large_text', 'bt_posttopic_message', 'cols' => '100', 'rows' => '15', 'help' => $txt['bt_posttopic_message_help']),
			
		'',
			array('check', 'bt_entry_progress_allow'),
			array('select', 'bt_entry_progress_steps', array('5' => $txt['bt_eps_per5'], '10' => $txt['bt_eps_per10'])),
			
		'',
			array('check', 'bt_mark_automatic_unimportant'),
			array('check', 'bt_mark_automatic_completed'),
	);

	if ($return_config)
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=fxtaddsettings;sa=entries;save';
	$context['page_title'] = $txt['bt_acp_settings_title'];
	$context['settings_title'] = $txt['entries'];
	$context['settings_message'] = $txt['fxt_posttopic_info'];

	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();
		$save_vars = $config_vars;
		
		if (count($_POST['bt_posttopic_board']) > 1)
			$context['saved_failed'] = $txt['bt_posttopic_board_limit'];
		
		if (empty($context['saved_failed']))
		{
			saveDBSettings($save_vars);
			redirectexit('action=admin;area=fxtaddsettings;sa=entries;saved');
		}
	}

	prepareDBSettingContext($config_vars);
}

function bt_notes_settings($return_config = false)
{
	global $txt, $scripturl, $context, $sourcedir;
	
	$config_vars = array(
			array('check', 'bt_enable_notes'),
			
		'',
			array('check', 'bt_enable_alerts', 'help' => $txt['bt_alerts_help']),

		'',
			array('check', 'bt_quicknote', 'help' => $txt['bt_quicknote_help']),
			array('check', 'bt_quicknote_primary', 'help' => $txt['bt_quicknote_primary_help']),

		'',
			array('select', 'bt_notes_order', array('ASC' => $txt['bt_no_asc'], 'DESC' => $txt['bt_no_desc'])),
	);

	if ($return_config)
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=fxtaddsettings;sa=notes;save';
	$context['page_title'] = $txt['bt_acp_settings_title'];
	$context['settings_title'] = $txt['notes'];
	$context['settings_message'] = $txt['fxt_notes_desc'];

	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();
		$save_vars = $config_vars;

		saveDBSettings($save_vars);
		redirectexit('action=admin;area=fxtaddsettings;sa=notes;saved');
	}

	prepareDBSettingContext($config_vars);
}

function bt_filter_settings($return_config = false)
{
	global $txt, $scripturl, $context, $sourcedir;
	
	$config_vars = array(
			array('check', 'bt_enable_filter'),
		'',
			array('select', 'bt_filter_search', array('simple' => $txt['bt_simple_search'], 'fulltext' => $txt['bt_fulltext_search']), 'help' => 'bt_filter_search_help'),
			array('check', 'bt_filter_search_boolean_mode', 'help' => 'filter_boolean_mode')
	);

	if ($return_config)
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=fxtaddsettings;sa=filter;save';
	$context['page_title'] = $txt['bt_acp_settings_title'];
	$context['settings_title'] = $txt['filter'];
	$context['settings_message'] = $txt['bt_filter_desc'];

	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();
		$save_vars = $config_vars;

		saveDBSettings($save_vars);
		redirectexit('action=admin;area=fxtaddsettings;sa=filter;saved');
	}

	prepareDBSettingContext($config_vars);
}