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
 * Recounts all statistics and updates the entries in {db_prefix}settings.
 * No parameters.
 */
function RecountBTStats()
{
	global $smcFunc;
                
        // Grab the total amount of entries.
        $request = $smcFunc['db_query']('', '
        	SELECT
        		count(id_entry) AS bt_total_entries
        	FROM {db_prefix}bugtracker_entries
        	WHERE in_trash != 1
        	LIMIT 1');

        $info = $smcFunc['db_fetch_assoc']($request);
        $smcFunc['db_free_result']($request);
        
        // Then the issues.
        $request = $smcFunc['db_query']('', '
        	SELECT
        		count(id_entry) AS bt_total_issues
        	FROM {db_prefix}bugtracker_entries
        	WHERE type = "issue" AND in_trash != 1
        	LIMIT 1');

        $info += $smcFunc['db_fetch_assoc']($request);
        $smcFunc['db_free_result']($request);
        
        // And the features.
        $request = $smcFunc['db_query']('', '
        	SELECT
        		count(id_entry) AS bt_total_features
        	FROM {db_prefix}bugtracker_entries
        	WHERE type = "feature" AND in_trash != 1
        	LIMIT 1');

        $info += $smcFunc['db_fetch_assoc']($request);
        $smcFunc['db_free_result']($request);
        
        // And last but not least, the important entries.
        $request = $smcFunc['db_query']('', '
        	SELECT
        		count(id_entry) AS bt_total_important
        	FROM {db_prefix}bugtracker_entries
        	WHERE attention = 1 AND in_trash != 1
        	LIMIT 1');

        $info += $smcFunc['db_fetch_assoc']($request);
        $smcFunc['db_free_result']($request);
        
        // And update the settings!
        updateSettings($info);
	
	// Grab all projects.
	$request = $smcFunc['db_query']('', '
		SELECT id_project
		FROM {db_prefix}bugtracker_projects');
	
	while (list($id) = $smcFunc['db_fetch_row']($request))
	{
		// Grab the number of open entries belonging to this project.
		$orequest = $smcFunc['db_query']('', '
			SELECT count(id_entry)
			FROM {db_prefix}bugtracker_entries
			WHERE id_project = {int:pid} AND status IN ("new", "wip") AND in_trash = 0',
			array('pid' => $id));
		
		list ($open) = $smcFunc['db_fetch_row']($orequest);
		$smcFunc['db_free_result']($orequest);
		
		// And closed entries.
		$crequest = $smcFunc['db_query']('', '
			SELECT count(id_entry)
			FROM {db_prefix}bugtracker_entries
			WHERE id_project = {int:pid} AND in_trash = 0',
			array('pid' => $id));
		
		list ($total) = $smcFunc['db_fetch_row']($crequest);
		$smcFunc['db_free_result']($crequest);
		
		// Now grab the latest entry for this project.
		$lrequest = $smcFunc['db_query']('', '
			SELECT id_entry
			FROM {db_prefix}bugtracker_entries
			WHERE id_project = {int:pid}
			ORDER BY id_entry DESC
			LIMIT 1',
			array('pid' => $id));
		
		list ($eid) = $smcFunc['db_fetch_row']($lrequest);
		$smcFunc['db_free_result']($lrequest);
		
		// Update the project.
		if (!empty($eid))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}bugtracker_projects
				SET
					num_entries = {int:total},
					num_open_entries = {int:open},
					id_last_entry = {int:lei}
				WHERE id_project = {int:pid}',
				array(
					'pid' => $id,
					'total' => $total,
					'open' => $open,
					'lei' => $eid
				));
		else
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}bugtracker_projects
				SET
					num_entries = {int:total},
					num_open_entries = {int:open},
					id_last_entry = 0
				WHERE id_project = {int:pid}',
				array(
					'pid' => $id,
					'total' => $total,
					'open' => $open
				));
	}
	$smcFunc['db_free_result']($request);
}

// This whole thing is an easter egg, sorry... :)
function BugTrackerInsertDummyData()
{
        global $context, $smcFunc, $scripturl;

        if (!$context['user']['is_admin'])
                fatal_error('Get out, lazytard!', false);

        $num_entries = isset($_GET['entries']) && is_numeric($_GET['entries']) ? $_GET['entries'] : 50;

	// Create a couple of lorem ipsum projects...
	$smcFunc['db_insert']('insert',
		'{db_prefix}bugtracker_projects',
		array(
			'name' => 'string',
			'description' => 'string',
			'num_issues' => 'int',
			'num_open_issues' => 'int',
			'last_entry_authorid' => 'int',
			'last_entry_authorname' => 'string',
		),
		array(
			'Dummy Project',
			'A random project generated with the dummy data generator.',
			$num_entries,
			$num_entries,
			$context['user']['id'],
			$context['user']['name']
		),
		array()
	);

        $pid = $smcFunc['db_insert_id']('{db_prefix}bugtracker_projects', 'id');

        // Okay, a for...
        $types = array('issue', 'feature');
        $marks = array('new', 'wip');
        $names = array('A testing entry', 'Testing 1, 2, 3', 'FXTracker Testing', 'Testing entry');
        for ($i = 0; $i < $num_entries; $i++)
        {
                $type = array_rand($types);
                $mark = array_rand($marks);
                $name = array_rand($names);
                $description = 'This is a testing description.';
                $private = 0;
                $attention = rand(0, 1);
                $progress = rand(0, 9) * 10;
                $postedtime = time();

                $smcFunc['db_insert']('insert',
                        '{db_prefix}bugtracker_entries',
                        array(
                                'name' => 'string',
                                'description' => 'string',
                                'type' => 'string',
                                'tracker' => 'int',
                                'private' => 'int',
                                'project' => 'int',
                                'status' => 'string',
                                'attention' => 'int',
                                'progress' => 'int',
                                'startedon' => 'int',
                                'updated' => 'int'
                        ),
                        array(
                                $names[$name],
                                $description,
                                $types[$type],
                                $context['user']['id'],
                                $private,
                                $pid,
                                $marks[$mark],
                                $attention,
                                $progress,
                                $postedtime,
                                $postedtime
                        ),
                        array()
                );

		$last_id = $smcFunc['db_insert_id']('{db_prefix}bugtracker_entries', 'id');
		$last_name = $names[$name];
		$last_time = $postedtime;
        }

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}bugtracker_projects
		SET last_entry_id = {int:led}, last_entry_name = {string:len}, last_entry_time = {int:let}
		WHERE id = {int:pid}',
		array(
			'led' => $last_id,
			'len' => $last_name,
			'let' => $last_time,
			'pid' => $pid
		));
		
	RecountBTStats();

        // Go to our new project.
        redirectexit($scripturl . '?action=bugtracker;sa=projectindex;project=' . $pid);
}
