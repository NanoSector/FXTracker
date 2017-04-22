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
 * Shows the actual entry.
 * No parameters - called by action.
 */
function BugTrackerView()
{
	// Our usual variables.
	global $context, $smcFunc, $user_info, $user_profile, $txt, $scripturl, $modSettings, $sourcedir, $settings;

	if (!isset($_GET['entry']))
		fatal_lang_error('no_entry_specified');

	// Cool, pour it over.
	$entryid = (int) $_GET['entry'];

	// Entry no. 0 does not exist. Basta.
	if ($entryid == 0)
		fatal_lang_error('entry_no_exist');
		
	// Entry data, project, and read state.
	$request = $smcFunc['db_query']('', '
		SELECT
			e.id_entry, e.title, e.id_first_note, e.id_last_note, e.type, e.status, e.in_trash, e.attention, e.progress,
			p.id_project, p.title AS project_title,
			fn.id_author, fn.note, fn.posted_time, fn.updated_time,
			m.real_name, m.id_member,
			g.group_name, g.online_color,
			r.id_note AS last_read_note
		FROM {db_prefix}bugtracker_entries AS e
			INNER JOIN {db_prefix}bugtracker_projects AS p ON (e.id_project = p.id_project)
			INNER JOIN {db_prefix}bugtracker_notes AS fn ON (e.id_first_note = fn.id_note)
			INNER JOIN {db_prefix}members AS m ON (fn.id_author = m.id_member)
			INNER JOIN {db_prefix}membergroups AS g ON (m.id_group = g.id_group)
			LEFT JOIN {db_prefix}bugtracker_log_entries AS r ON (e.id_entry = r.id_entry AND r.id_member = {int:userid})
		WHERE e.id_entry = {int:entry}
		LIMIT 1',
		array(
			'entry' => $entryid,
			'userid' => $context['user']['id']
		));
	
	// No entry?
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('entry_no_exist');
	
	$data = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);
	
	// Have we enabled notes?
	if (!empty($modSettings['bt_enable_notes']))
	{
		$nresult = $smcFunc['db_query']('', '
			SELECT
				n.id_note, n.id_author, n.note, n.posted_time,
				m.real_name
			FROM {db_prefix}bugtracker_notes AS n
				INNER JOIN {db_prefix}members AS m ON (n.id_author = m.id_member)
			WHERE n.id_entry = {int:id_entry} AND n.id_note != {int:id_first_note}',
			array(
				'id_entry' => $data['id_entry'],
				'id_first_note' => $data['id_first_note']
			));
		
		$notes = array();
		while ($note = $smcFunc['db_fetch_assoc']($nresult))
		{
			$notes[$note['id_note']] = $note;
			$notes[$note['id_note']]['posted_time'] = timeformat($note['posted_time']);
			$notes[$note['id_note']]['note'] = parse_bbc($note['note']);
		}
		$smcFunc['db_free_result']($nresult);
	}
	
	// Then for the action log...
	$aresult = $smcFunc['db_query']('', '
		SELECT
			a.type, a.from, a.to, a.time,
			m.real_name, m.id_member
		FROM {db_prefix}bugtracker_log_actions AS a
			INNER JOIN {db_prefix}members AS m ON (a.id_user = m.id_member)
		WHERE a.id_entry = {int:entry}
		ORDER BY a.time DESC',
		array(
			'entry' => $data['id_entry'],
		));
	
	$actions = array();
	while ($act = $smcFunc['db_fetch_assoc']($aresult))
	{
		if ($act['type'] == 'mark')
		{
			$act['from'] = $txt['bt_statuses'][$act['from']];
			$act['to'] = $txt['bt_statuses'][$act['to']];
		}
		if ($act['type'] == 'move')
		{
			// Grab the name of the previous project.
			$lrequest = $smcFunc['db_query']('', '
				SELECT
					id_project, title
				FROM {db_prefix}bugtracker_projects
				WHERE id_project IN ({array_int:projectid})
				LIMIT 2',
				array(
					'projectid' => array($act['from'], $act['to']),
				));
		
			while ($p = $smcFunc['db_fetch_assoc']($lrequest))
			{
				if ($p['id_project'] == $act['from'])
					$act['from'] = '<a href="' . $scripturl . '?action=bugtracker;sa=projectindex;project=' . $p['id_project'] . '">' . $p['title'] . '</a>';
				if ($p['id_project'] == $act['to'])
					$act['to'] = '<a href="' . $scripturl . '?action=bugtracker;sa=projectindex;project=' . $p['id_project'] . '">' . $p['title'] . '</a>';
			}
			$smcFunc['db_free_result']($lrequest);
			
			if (is_numeric($act['from']))
				$act['from'] = $txt['na'];
			if (is_numeric($act['to']))
				$act['to'] = $txt['na'];
		}
		$actions[] = $act;
	}
	$smcFunc['db_free_result']($aresult);

	// Setup permissions... Not just one of them!
	$own_any = array('mark', 'mark_new', 'mark_wip', 'mark_done', 'mark_reject', 'mark_attention', 'reply', 'edit', 'remove', 'remove_note', 'edit_note', 'add_note', 'move');
	$is_own = $context['user']['id'] == $data['id_member'];
	foreach ($own_any as $perm)
	{
		$context['can_bt_' . $perm . '_any'] = allowedTo('bt_' . $perm . '_any');
		$context['can_bt_' . $perm . '_own'] = allowedTo('bt_' . $perm . '_own') && $is_own;
	}

	// If we can mark something.... tell us!
	$context['bt_can_mark'] = ($context['can_bt_mark_any'] || $context['can_bt_mark_own']) && ($context['can_bt_mark_new_own'] || $context['can_bt_mark_new_any'] || $context['can_bt_mark_wip_own'] || $context['can_bt_mark_wip_any'] || $context['can_bt_mark_done_own'] || $context['can_bt_mark_done_any'] || $context['can_bt_mark_reject_own'] || $context['can_bt_mark_reject_any']);
	
	// Grab the projects except for this one when we can move entries around.
	if ($context['can_bt_move_any'] || $context['can_bt_move_own'])
	{
		$prequest = $smcFunc['db_query']('', '
			SELECT id_project, title
			FROM {db_prefix}bugtracker_projects
			WHERE id_project != {int:project}',
			array(
				'project' => $data['id_project']
			));
		
		$context['bugtracker']['projects'] = array();
		while ($proj = $smcFunc['db_fetch_assoc']($prequest))
		{
			$context['bugtracker']['projects'][$proj['id_project']] = $proj['title'];
		}
		$smcFunc['db_free_result']($prequest);
		
		// No projects? Can't move.
		if (empty($projects))
		{
			$context['can_bt_move_any'] = false;
			$context['can_bt_move_own'] = false;
		}
		else
			$context['move_projects'] = $projects;
	}

	// Set the title.
	$context['page_title'] = sprintf($txt['view_title'], $data['id_entry']);
	
	// Linktree time.
	$context['linktree'][] = array('url' => $scripturl . '?action=bugtracker;sa=projectindex;project=' . $data['id_project'], 'name' => $data['project_title']);
	$context['linktree'][] = array('url' => $scripturl . '?action=bugtracker;sa=view;entry=' . $data['id_entry'], 'name' => sprintf($txt['entrytitle'], $data['id_entry'], $data['title']));
	
	// Fiddle out the icons.
	$type_icon = $settings['images_url'] . '/bugtracker/types/' . $data['type'] . '.png';
	$status_icon = $settings['images_url'] . '/bugtracker/statuses/' . $data['status'] . ($data['status'] == 'wip' ? '.gif' : '.png');

	// $context time!
	$context['bugtracker']['entry'] = array(
		'id' => $data['id_entry'],
		'name' => $data['title'],
		'desc' => parse_bbc(un_htmlspecialchars($data['note'])),
		'tracker' => array(
			'id' => $data['id_author'],
			'name' => $data['real_name'],
			'group' => !empty($data['online_color']) ? '<span style="color:' . $data['online_color'] . '">' . $data['group_name'] . '</span>' : $data['group_name'],
		),
		'started' => timeformat($data['posted_time']),
		'updated' => timeformat($data['updated_time']),
		'is_updated' => !empty($data['updated_time']),
		'project' => array(
			'id' => $data['id_project'],
			'name' => $data['project_title']
		),
		'type' => array(
			'id' => $data['type'],
			'title' => $txt['bt_types'][$data['type']],
			'icon' => $type_icon,
		),
		'status' => array(
			'id' => $data['status'],
			'title' => $txt['bt_statuses'][$data['status']],
			'icon' => $status_icon,
		),
		'attention' => $data['attention'],
		'progress' => (empty($data['progress']) ? '0' : $data['progress']) . '%',
		'notes' => empty($modSettings['bt_enable_notes']) ? array() : $notes,
		'log' => $actions,
		'in_trash' => $data['in_trash'],
	);
	
	// Setup the quick notes.
	if (!empty($modSettings['bt_quicknote']))
	{
		require_once($sourcedir . '/Subs-Editor.php');
		$editorOptions = array(
			'id' => 'note_text',
			'value' => '',
			'height' => '275px',
			'width' => '100%',
			// XML preview.
			'preview_type' => 1,
			'required' => 1,
		);
		create_control_richedit($editorOptions);
		$context['post_box_name'] = $editorOptions['id'];
	}
	
	if (!$context['user']['is_guest'])
	{
		require_once($sourcedir . '/FXTracker/Subs-View.php');
		$read = hasReadEntry($data['id_entry']);
		if (empty($read[$data['id_entry']]))
		{
			// Get the last note ID.
			$result = $smcFunc['db_query']('', '
				SELECT id_note
				FROM {db_prefix}bugtracker_notes
				WHERE id_entry = {int:entry}
				ORDER BY id_note DESC
				LIMIT 1',
				array(
					'entry' => $data['id_entry']
				));
			
			list ($id_note) = $smcFunc['db_fetch_row']($result);
			$smcFunc['db_free_result']($result);
			
			// Hang on.
			$result = $smcFunc['db_query']('', '
				SELECT id_note
				FROM {db_prefix}bugtracker_log_entries
				WHERE id_entry = {int:entry} AND id_member = {int:member}
				LIMIT 1',
				array(
					'entry' => $data['id_entry'],
					'member' => $context['user']['id']
				));
			
			// Insert if we have to...
			if ($smcFunc['db_num_rows']($result) == 0)
			{
				// Insert a has-read notice for the current user.
				$smcFunc['db_insert']('insert',
					'{db_prefix}bugtracker_log_entries',
					array(
						'id_member' => 'int',
						'id_entry' => 'int',
						'id_note' => 'int',
					),
					array(
						$context['user']['id'],
						$data['id_entry'],
						$id_note
					),
					array());

			// ...but update if we can.
			}
			else
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}bugtracker_log_entries
					SET
						id_note = {int:latest_note}
					WHERE id_entry = {int:entry} AND id_member = {int:member}',
					array(
						'latest_note' => $id_note,
						'entry' => $data['id_entry'],
						'member' => $context['user']['id']
					));
			}
			
		}
	}
	
	// Add a JavaScript snippet.
	loadJavascriptFile('bugtracker-toggle.js');
	loadJavascriptFile('bugtracker-entry.js');


	// Then tell SMF what template to load.
	loadTemplate('fxt/View');
	$context['sub_template'] = 'TrackerView';
}

/**
 * Shows the project index.
 * No parameters - called by action.
 */
function BugTrackerViewProject()
{
	global $context, $smcFunc, $txt, $scripturl, $user_profile, $sourcedir, $modSettings;

	// Requirements.
	require_once($sourcedir . '/FXTracker/Class-Entries.php'); // To get the entries and their data.
	require_once($sourcedir . '/FXTracker/Class-Project.php'); // To get information about the current project.
	if (!empty($modSettings['bt_show_description_ppage']))
		require_once($sourcedir . '/Subs-Post.php');

	// Load the template.
	loadTemplate('fxt/ViewProject');
	
	$pid = (int) $_GET['project'];
	
	$project = new FXTracker_Project($pid);
	$pdata = $project->get();
	
	// Attempt to create a new class.
	$entries = new FXTracker_Entries();
	
	// How many items are closed? Going to cheat a bit here ;)
	$entries->scope('e.id_project', $pdata['id_project']);
	$entries->scope('e.in_trash', '0');
	$entries->scope('e.status', 'done');
	$context['bugtracker']['num_closed'] = $entries->count();

	$entries->scope('e.status', 'reject');
	$context['bugtracker']['num_rejected'] = $entries->count();
	
	// Now we don't really want the status to be filtered anymore, thanks.
	$entries->scope_undo('e.status');
	
	// Obey any requests to resetting filters.
	if (isset($_GET['unsetfilters']) && !empty($_SESSION['fxt_last_filter']))
		unset($_SESSION['fxt_last_filter']);
	
	// Default filters.
	$context['filtered'] = array('new', 'wip', 'issue', 'feature');
	if (!empty($modSettings['bt_enable_filter']) && !empty($_POST['do_filter']) || !empty($_SESSION['fxt_last_filter']))
	{
		// Filter data.
		$fdata = !empty($_POST) ? $_POST : $_SESSION['fxt_last_filter'];
		
		// Are we filtering anything? Possible filters are:
		$filters = array(
			'status' => !empty($fdata['status']) ? $fdata['status'] : array(),
			'type' => !empty($fdata['type']) ? $fdata['type'] : array(),
			'attention' => isset($fdata['attention']) ? '1' : '0',
			'in_trash' => isset($fdata['in_trash']) ? '1' : '0',
			'name' => !empty($fdata['search_title']),
		);
		
		// Filtering statuses?
		if (empty($filters['status']) || empty($filters['type']))
			$context['filter_error'] = 'nothing_set';
		else
		{
			$context['fdata'] = $fdata;
			
			// Setup the session.
			if (isset($_POST['keep_filter']))
				$_SESSION['fxt_last_filter'] = $fdata;
			
			// Or do we remove it?
			elseif (!isset($_POST['keep_filter']) && !empty($_SESSION['fxt_last_filter']))
				unset($_SESSION['fxt_last_filter']);
				
			// Are we filtering by session?
			if (!empty($_SESSION['fxt_last_filter']))
				$context['session_filter'] = true;
			
			$context['filtered'] = array();
			
			// Start off by filtering any status. Doing some manual evil work here, as I don't trust myself.
			foreach ($fdata['status'] as $key => $value)
				$fdata['status'][$key] = $smcFunc['db_escape_string']($value);
			
			$entries->scope_custom('e.status IN(\'' . (implode('\', \'', $fdata['status'])) . '\')');
			$context['filtered'] = array_merge($context['filtered'], $fdata['status']);
			
			// Then types are up. I still don't trust myself.
			foreach ($fdata['type'] as $key => $value)
				$fdata['type'][$key] = $smcFunc['db_escape_string']($value);
			
			$entries->scope_custom('e.type IN(\'' . (implode('\', \'', $fdata['type'])) . '\')');
			$context['filtered'] = array_merge($context['filtered'], $fdata['type']);

			// Doing a search?
			if (!empty($filters['name']) && preg_match('/^[^\']+$/', $fdata['search_title']))
			{
				// Are we using simple search?
				if (empty($modSettings['bt_filter_search']) || $modSettings['bt_filter_search'] == 'simple')
					$entries->scope_custom('e.title LIKE \'%%%1$s%%\' OR e.id_entry LIKE \'%%%1$s%%\'', true, array($fdata['search_title']));
				
				// Or fulltext? This is probably a bit slower.
				else
				{
					// Are we using boolean mode? Even slower, possibly.
					if (!empty($modSettings['bt_filter_search_boolean_mode']) && !empty($_POST['boolean_mode']))
					{
						$entries->scope_custom('(MATCH (e.title) AGAINST(\'%1$s\' IN BOOLEAN MODE) OR MATCH (n.note) AGAINST (\'%1$s\' IN BOOLEAN MODE) OR e.id_entry LIKE \'%%%1$s%%\')', true, array($fdata['search_title']));
						$context['filtered'][] = 'using_boolean';
					}
					else
						$entries->scope_custom('(MATCH (e.title) AGAINST(\'%1$s\' IN NATURAL LANGUAGE MODE) OR MATCH (n.note) AGAINST (\'%1$s\' IN NATURAL LANGUAGE MODE) OR e.id_entry LIKE \'%%%1$s%%\')', true, array($fdata['search_title']));
				}
				$context['filtered'][] = 'search';
			}
			
			// Filtering items requiring attention?
			if (!empty($filters['attention']))
			{
				$entries->scope('e.attention', '1');
				$context['filtered'][] = 'attention';
			}
	
			// Or looking in the, eww, garbage can?
			if (!empty($filters['in_trash']))
			{
				$entries->scope_undo('e.in_trash');
				$context['filtered'][] = 'in_trash';
			}
			
			// Success!
			$context['has_filter'] = true;
		}
	}
	else
		$entries->scope_custom('e.status NOT IN(\'done\', \'reject\')');
	
	// Our URL.
	$context['form_url'] = str_replace(array($_SERVER['SCRIPT_NAME'], ';unsetfilters'), array($scripturl, ''), $_SERVER['REQUEST_URI']);
	
	// Get the entries in this project, and create a nice list.
	$entries->createList($scripturl . '?action=bugtracker;sa=projectindex;project=' . $pdata['id_project']);
	
	// Also stuff the linktree.
	$context['linktree'][] = array('url' => $scripturl . '?action=bugtracker;sa=projectindex;project=' . $pdata['id_project'], 'name' => $pdata['title']);
	
	// What do we have, from issues and such?
	$context['bugtracker']['project'] = $pdata;
	
	// Add our JS.
	loadJavascriptFile('bugtracker-toggle.js');
	loadJavascriptFile('bugtracker-project.js');
	addJavascriptVar('has_filter', !empty($context['has_filter']) ? '1' : '0');

	// Page title time!
	$context['page_title'] = $pdata['title'];

	// Can we add new entries?
	$context['can_bt_add'] = allowedTo('bt_add');

	// And the sub template.
	$context['sub_template'] = 'TrackerViewProject';
}

/**
 * Shows the entries which are in the trash of a specific project.
 * No parameters - called by action.
 */
function BugTrackerViewTrash() {
	global $context, $sourcedir, $scripturl, $txt, $smcFunc;

	// Viewing trash or just trash?
	$project = !empty($_GET['project']) && is_numeric($_GET['project']) ? $_GET['project'] : 0;

	// For the following we need Subs-View.php, and Subs-List.php
	require_once($sourcedir . '/FXTracker/Class-Entries.php');
	require_once($sourcedir . '/FXTracker/Class-Project.php');

	// Load the template.
	loadTemplate('fxt/ViewProject');

	$project = new FXTracker_Project($project);
	
	if (!empty($project))
		$pdata = $project->get();

	// Get the entry data.
	$entries = new FXTracker_Entries();
	$entries->scope('e.in_trash', '1');

	if (!empty($pdata))
		$entries->scope('e.id_project', $pdata['id_project']);

	if (isset($_GET['empty']) && !empty($pdata))
        {
                $tentries = array_keys($entries->get());

                require($sourcedir . '/FXTracker/Subs-Edit.php');
                fxt_deleteEntry($tentries, false);
        }
	if (isset($_GET['restore']) && !empty($pdata))
	{
		$tentries = array_keys($entries->get());
		
                require($sourcedir . '/FXTracker/Subs-Edit.php');
                fxt_restoreEntry($tentries);
	}

	// Load the list data.
	$entries->createList($scripturl . '?action=bugtracker;sa=trash;project=' . $pdata['id_project']);

	// As this is put in a list, we need to create it.
	//createList($listOptions);

	// Got any project?
	if (!empty($pdata))
	{
		$context['project'] = $pdata;
		$context['trash_string'] = sprintf($txt['view_trash'], $context['project']['title']);

		$context['linktree'][] = array(
			'name' => $context['project']['title'],
			'url' => $scripturl . '?action=bugtracker;sa=projectindex;project=' . $context['project']['id_project']
		);
	}
	else
		$context['trash_string'] = $txt['view_trash_noproj'];

	$context['linktree'][] = array('name' => $txt['view_trash_noproj'], 'url' => $scripturl . '?action=bugtracker;sa=trash' . (!empty($pdata) ? ';project=' . $pdata['id_project'] : ''), );

	$context['page_title'] = $txt['view_trash_noproj'];
	$context['sub_template'] = 'TrackerViewTrash';
}
