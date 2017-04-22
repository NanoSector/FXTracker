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
 * Creates an entry.
 * @param array $entryOptions The options with which this entry is created.
 */
function fxt_createEntry($entryOptions)
{
	global $context, $smcFunc, $sourcedir;
	
	// Can we create entries?
	if (!allowedTo('bugtracker_add'))
		return;
		
	// Then lets try to find things we require.
	if (empty($entryOptions['name']) || empty($entryOptions['description']) || empty($entryOptions['project']))
		return;
		
	// Sanitise project ID.
	$entryOptions['project'] = (int) $entryOptions['project'];
	
	// Try to grab this project.
	$presult = $smcFunc['db_query']('', '
		SELECT name
		FROM {db_prefix}bugtracker_projects
		WHERE id = {int:pid}
		LIMIT 1',
		array(
			'pid' => $entryOptions['project'],
		));
		
	// No rows?
	if ($smcFunc['db_num_rows']($presult) == 0)
		return;
		
	// Okay. Fill in the blanks and sanitise stuff.
	include($sourcedir . '/Subs-Post.php');
	
	// Sanitise the description.
}

/**
 * Marks an entry.
 * @param mixed  $id The id(s) to mark. In case of multiple IDs, pass them as an array.
 * @param string $as The way the entries should be marked.
 */
function fxt_markEntry($id = null, $as = null)
{
        global $smcFunc, $context, $txt, $modSettings;
        if (empty($id) || empty($as) || (!is_array($id) && !is_numeric($id)))
                return;
                
	// Do we have multiple IDs?
        $multiple = is_array($id);

        // Is it an array?
        if ($multiple)
                $id = fxt_sanitiseIDs($id);

        // Else it must be a single ID...
        else
                $id = array((int) $id);

	// Check the ID once again.
        if (empty($id))
                return;
                
        // 0 and 1 are reserved.
        if ($as == '1' || $as == '0')
        	return;

        // Is the $as valid?
        // If it is set to either mark as attention or undo it, need a little trick.
        if ($as == 'attention' || $as == 'attention_undo')
        {
                $colname = 'attention';
                $as = ($as == 'attention' ? '1' : '0');
        }
        
        // Otherwise we can just pass in the status column.
        elseif (in_array($as, array_keys($txt['bt_statuses'])))
                $colname = 'status';

	// If it is something else we simply can't mark.
        else
                return;

        // First however, lets check if we have permission and that the entries actually exist.
        // Two flies in one hit!
        $result = $smcFunc['db_query']('', '
                SELECT
			e.id_entry, e.id_project, e.status, e.attention,
			fn.id_note, fn.id_author
                FROM {db_prefix}bugtracker_entries AS e
			INNER JOIN {db_prefix}bugtracker_notes AS fn ON (fn.id_note = e.id_first_note)
                WHERE e.id_entry IN ({array_int:id})',
                array(
                        'id' => $id
                ));

        // Fetch the entries which *do* exist.
        $existingEntries = array();
	$existingNotes = array();
        $projects = array();
        $can_own = allowedTo('bt_mark_own');
        $can_any = allowedTo('bt_mark_any');
        while ($row = $smcFunc['db_fetch_assoc']($result))
        {
		// Don't do anything where it's not needed.
                if (($as == '1' && $row['attention'] == '1') || ($as == '0' && $row['attention'] == '0') || $as == $row['status'])
                	continue;
                
                if ($can_any || ($can_own && $context['user']['id'] == $row['id_author']))
                {
			if (!array_key_exists($row['id_project'], $projects))
				$projects[$row['id_project']] = array('closed' => 0, 'opened' => 0);
			
                        if (!in_array($row['status'], array('new', 'wip')) && in_array($as, array('new', 'wip')))
                        	++$projects[$row['id_project']]['opened'];
                       	elseif (!in_array($row['status'], array('done', 'reject')) && in_array($as, array('done', 'reject')))
                       		++$projects[$row['id_project']]['closed'];
                        
                        if ($as != '1' && $as != '0')
	                        fxt_logAction('mark', $row['id_entry'], 0, $row['status'], $as);
	                else
	                	fxt_logAction('mark', $row['id_entry'], 0, ($as == '1' ? 'no_attention' : 'attention'), ($as == '1' ? 'attention' : 'no_attention'));
	                	
	                $existingEntries[] = $row['id_entry'];
			$existingNotes[] = $row['id_note'];
                }
        }
        $smcFunc['db_free_result']($result);

        // Either no entries were found, or we don't have permission to mark any of the entries.
	// If there are no notes, something went wrong...
        if (empty($existingEntries) || empty($existingNotes))
                return;
	
	// Anything extra to do?
	$extra = '';
	if ($as == 'done' && !empty($modSettings['bt_mark_automatic_unimportant']))
		$extra .= ', attention = 0';

        // Then do the marking!
        $smcFunc['db_query']('', '
                UPDATE {db_prefix}bugtracker_entries
                SET {raw:set} = {string:to}{raw:extra}
                WHERE id_entry IN ({array_int:id})',
                array(
                        'set' => $colname,
                        'to' => $as,
                        'id' => $existingEntries,
			'extra' => $extra,
                ));
	
        $smcFunc['db_query']('', '
                UPDATE {db_prefix}bugtracker_notes
                SET updated_time = {int:time}
                WHERE id_note IN ({array_int:id})',
                array(
                        'id' => $existingNotes,
                        'time' => time()
                ));
	
	

	// Since we updated this entry, delete all mark_read entries associated.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}bugtracker_log_entries
		WHERE id_member != {int:user} AND id_entry IN ({array_int:ids})',
		array(
			'ids' => $id,
			'user' => $context['user']['id']
		));

	// If it is closed, mark it as such in the projects details.
	foreach ($projects as $id => $counts)
	{
		if (in_array($as, array('done', 'reject')))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}bugtracker_projects
				SET num_open_entries = num_open_entries - {int:entrycount}
				WHERE id_project = {int:pid}',
				array(
					'pid' => $id,
					'entrycount' => $counts['closed']
				));
	
		// If it is opened again, also mark that.
		elseif (in_array($as, array('new', 'wip')))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}bugtracker_projects
				SET num_open_entries = num_open_entries + {int:entrycount}
				WHERE id_project = {int:pid}',
				array(
					'pid' => $id,
					'entrycount' => $counts['opened']
				));
	}

        return true;
}

/**
 * Moves an entry between projects, while also updating the counts and such.
 * @param mixed $ids The entry/ies to be moved. Pass as array for multiple.
 * @param int   $p   The project to move the entry/ies to.
 */
function fxt_moveEntry($ids = null, $p = null)
{
        global $smcFunc, $context, $modSettings;

        if (empty($ids) || (!is_array($ids) && !is_numeric($ids)) || empty($p) || !is_numeric($p))
                return;

	// Are we handling multiple IDs?
        $multiple = is_array($ids);

        // Sanitise them if we are.
        if ($multiple)
                $ids = fxt_sanitiseIDs($ids);

        // Else it must be a single ID...
        else
                $ids = array((int) $ids);

	// Check it again.
        if (empty($ids))
                return;
	
	// Lets check if the target project exists.
	$result = $smcFunc['db_query']('', '
		SELECT id_project
		FROM {db_prefix}bugtracker_projects
		WHERE id_project = {int:p}',
		array(
			'p' => $p
		));
	
	if ($smcFunc['db_num_rows']($result) == 0) return;
	$smcFunc['db_free_result']($result);
	
        // Lets check if we have permission and that the entry/ies actually exist.
        $result = $smcFunc['db_query']('', '
                SELECT
			e.id_entry, e.id_project, e.status, e.in_trash,
			fn.id_author
                FROM {db_prefix}bugtracker_entries AS e
			INNER JOIN {db_prefix}bugtracker_notes AS fn ON (fn.id_note = e.id_first_note)
                WHERE e.id_entry IN ({array_int:id})',
                array(
                        'id' => $ids
                ));
	
	$projects_from = array();
	$project_to = array('new' => 0, 'open' => 0);
	$mids = array();
	$can_any = allowedTo('bt_move_any');
	$can_own = allowedTo('bt_move_own');
        while ($row = $smcFunc['db_fetch_assoc']($result))
        {
                if ($can_any || ($can_own && $context['user']['id'] == $row['id_author']))
                {
			if (!isset($projects_from[$row['id_project']]))
				$projects_from[$row['id_project']] = array('removed' => 0, 'min_opened' => 0);
				
			// This entry does not count if it is in the trash.
			if (empty($row['in_trash']))
			{
				// Is it open?
				if (in_array($row['status'], array('new', 'wip')))
				{
					$projects_from[$row['id_project']]['min_opened']++;
					$project_to['open']++;
				}
					
				// Increase counts which always get changed.
				$projects_from[$row['id_project']]['removed']++;
				$project_to['new']++;
			}
			
			// Put the entry in the queue.
			$mids[] = $row['id_entry'];
			
			// Log the move for this entry.
			fxt_logAction('move', $row['id_entry'], 0, $row['id_project'], $p);
		}
	}
	$smcFunc['db_free_result']($result);
	
	// No entries? :(
	if (empty($mids))
		return;
	
	// Move the entries.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}bugtracker_entries
		SET id_project = {int:newp}
		WHERE id_entry IN ({array_int:ids})',
		array(
			'newp' => $p,
			'ids' => $mids
		));
	
	// And update the counts.
	foreach ($projects_from as $id => $counts)
	{
		// Grab the latest entry for this project.
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
					num_entries = num_issues - {int:total},
					num_open_entries = num_open_issues - {int:open},
					id_last_entry = {int:lei}
				WHERE id_project = {int:pid}',
				array(
					'pid' => $id,
					'total' => $counts['removed'],
					'open' => $counts['min_opened'],
					'lei' => $eid
				));
		else
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}bugtracker_projects
				SET
					num_entries = num_entries - {int:total},
					num_open_entries = num_open_entries - {int:open},
					id_last_entry = 0
				WHERE id_project = {int:pid}',
				array(
					'pid' => $id,
					'total' => $counts['removed'],
					'open' => $counts['min_opened']
				));
		
	}
	
	// Then do the same for the target.
	$lrequest = $smcFunc['db_query']('', '
		SELECT id_entry
		FROM {db_prefix}bugtracker_entries
		WHERE id_project = {int:pid}
		ORDER BY id_entry DESC
		LIMIT 1',
		array('pid' => $p));
		
	list ($eid) = $smcFunc['db_fetch_row']($lrequest);
	$smcFunc['db_free_result']($lrequest);
		
	// Update the project.
	if (!empty($eid))
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}bugtracker_projects
			SET
				num_entries = num_entries + {int:total},
				num_open_entries = num_open_entries + {int:open},
				id_last_entry = {int:lei}
			WHERE id_project = {int:pid}',
			array(
				'pid' => $p,
				'total' => $project_to['new'],
				'open' => $project_to['open'],
				'lei' => $eid
			));
	else
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}bugtracker_projects
			SET
				num_entries = num_entries - {int:total},
				num_open_entries = num_open_entries - {int:open},
				id_last_entry = 0
			WHERE id_project = {int:pid}',
			array(
				'pid' => $id,
				'total' => $counts['removed'],
				'open' => $counts['min_opened']
			));
	
	// And we're done!
	return true;
}

/**
 * Deletes a single or multiple entries with their notes.
 * @param mixed $ids   The entry/ies to delete. Pass as array for multiple.
 * @param bool  $trash If false, deletes the entry without trashing it first. Default: true
 */
function fxt_deleteEntry($ids = null, $trash = true)
{
        global $smcFunc, $context, $modSettings;

        if (empty($ids) || (!is_array($ids) && !is_numeric($ids)))
                return;

	// Are we handling multiple IDs?
        $multiple = is_array($ids);

        // Sanitise them if we are.
        if ($multiple)
                $ids = fxt_sanitiseIDs($ids);

        // Else it must be a single ID...
        else
                $ids = array((int) $ids);

	// Check it again.
        if (empty($ids))
                return;

        // Lets check if we have permission and that the entry/ies actually exist.
        $result = $smcFunc['db_query']('', '
                SELECT
			e.id_entry, e.id_project, e.status, e.in_trash, e.type, e.attention,
			fn.id_author
                FROM {db_prefix}bugtracker_entries AS e
			INNER JOIN {db_prefix}bugtracker_notes AS fn ON (fn.id_note = e.id_first_note)
                WHERE e.id_entry IN ({array_int:id})',
                array(
                        'id' => $ids
                ));

        // Try to fetch any entries which do exist.
        $toDelete = array();
        $toTrash = array();
        $projects = array();
        $decrementOpen = array();
        $decrementTotal = array();
        $issues = 0;
        $features = 0;
        $important = 0;
	$can_any = allowedTo('bt_remove_any');
	$can_own = allowedTo('bt_remove_own');
        while ($row = $smcFunc['db_fetch_assoc']($result))
        {
                if ($can_any || ($can_own && $context['user']['id'] == $row['id_author']))
                {
                        if (!in_array($row['id_project'], $projects))
                        {
                                $projects[] = $row['id_project'];
                                $decrementOpen[$row['id_project']] = 0;
                                $decrementTotal[$row['id_project']] = 0;
                        }

                        if (!$trash || $row['in_trash'])
                                $toDelete[] = $row['id_entry'];
                        else
                        {
                                $toTrash[] = $row['id_entry'];

                                $decrementTotal[$row['id_project']]++;

                                if (in_array($row['status'], array('new', 'wip')))
                                        $decrementOpen[$row['id_project']]++;
                                        
                                if ($row['type'] == 'issue')
                                	++$issues;
                                elseif ($row['type'] == 'feature')
                                	++$features;
                                if (!empty($row['attention']))
                                	++$important;
                                	
                                // Log this action in the Action Log.
                                fxt_logAction('trash', $row['id_entry']);
                        }
                }
        }

        $smcFunc['db_free_result']($result);

        // If we got nothing to do, simply don't waste any time.
        if ((empty($toDelete) && empty($toTrash)) || empty($projects))
                return;

	// Anything to fully delete?
        if (!empty($toDelete))
        {
                // Delete any entries in the trash.
                $smcFunc['db_query']('', '
                        DELETE FROM {db_prefix}bugtracker_entries
                        WHERE id_entry IN ({array_int:ids})',
                        array(
                                'ids' => $toDelete
                        ));

                // Delete notes associated.
                $smcFunc['db_query']('', '
                        DELETE FROM {db_prefix}bugtracker_notes
                        WHERE id_entry IN ({array_int:ids})',
                        array(
                                'ids' => $toDelete
                        ));

                // Delete any have-reads.
                $smcFunc['db_query']('', '
                	DELETE FROM {db_prefix}bugtracker_log_entries
                	WHERE id_entry IN ({array_int:ids})',
                	array(
                		'ids' => $toDelete
                	));
                	
               	// And clean up the action log.
               	$smcFunc['db_query']('', '
               		DELETE FROM {db_prefix}bugtracker_log_actions
               		WHERE id_entry IN ({array_int:ids})',
               		array(
               			'ids' => $toDelete
               		));
        }

        // Is there anything to be put in the trash can?
        if (!empty($toTrash))
        {
                $smcFunc['db_query']('', '
                        UPDATE {db_prefix}bugtracker_entries
                        SET in_trash = 1
                        WHERE id_entry IN ({array_int:ids})',
                        array(
                                'ids' => $toTrash
                        ));
        }

        // Then, we need to update the projects.
        foreach ($projects as $pid)
        {
         	// Update the number of entries.
                $smcFunc['db_query']('', '
                        UPDATE {db_prefix}bugtracker_projects
                        SET
                                num_entries = num_entries - {raw:minus_total},
                                num_open_entries = num_open_entries - {raw:minus_open}
                        WHERE id_project = {int:pid}',
                        array(
                                'minus_total' => (int) $decrementTotal[$pid],
                                'minus_open' => (int) $decrementOpen[$pid],
                                'pid' => $pid
                        ));

          	// Grab the latest entry.
                $result = $smcFunc['db_query']('', '
                	SELECT e.id_entry
                	FROM {db_prefix}bugtracker_entries AS e
				INNER JOIN {db_prefix}bugtracker_notes AS fn ON (fn.id_note = e.id_first_note)
                	WHERE id_project = {int:pid} AND in_trash = 0
                	ORDER BY fn.posted_time DESC, e.id_entry DESC
                	LIMIT 1',
                	array(
                		'pid' => $pid
                	));

                $row = $smcFunc['db_fetch_assoc']($result);
                $smcFunc['db_free_result']($result);

                // Insert the latest entry details.
		if (!empty($row['id_entry']))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}bugtracker_projects
				SET
					id_last_entry = {int:id}
				WHERE id_project = {int:pid}',
				array(
					'id' => $row['id_entry'],
					'pid' => $pid
				));
        }
         
        // Update the overall counts.
        updateSettings(array(
         	'bt_total_entries' => $modSettings['bt_total_entries'] - count($toTrash),
         	'bt_total_issues' => $modSettings['bt_total_issues'] - $issues,
         	'bt_total_features' => $modSettings['bt_total_features'] - $features,
         	'bt_total_important' => $modSettings['bt_total_important'] - $important
        ));

        // We're done!
        return true;
}

/**
 * Restores entries from the trash cans of the project they reside in.
 * @param mixed $ids The entry/ies to restore. Pass as array for multiple.
 */
function fxt_restoreEntry($ids)
{
        global $smcFunc, $context, $modSettings;

        if (empty($ids) || (!is_array($ids) && !is_numeric($ids)))
                return;

	// Are we handling multiple IDs?
        $multiple = is_array($ids);

        // Sanitise them if we are.
        if ($multiple)
                $ids = fxt_sanitiseIDs($ids);

        // Else it must be a single ID...
        else
                $ids = array((int) $ids);

	// Check it again.
        if (empty($ids))
                return;
	
        // Lets check if we have permission and that the entry/ies actually exist.
        $result = $smcFunc['db_query']('', '
                SELECT
			e.id_entry, e.id_project, e.status, e.in_trash, e.type, e.attention,
			fn.id_author
                FROM {db_prefix}bugtracker_entries AS e
			INNER JOIN {db_prefix}bugtracker_notes AS fn ON (fn.id_note = e.id_first_note)
                WHERE e.id_entry IN ({array_int:id})',
                array(
                        'id' => $ids
                ));
	
	$projects = array();
	$mids = array();
	$can_any = allowedTo('bt_restore_any');
	$can_own = allowedTo('bt_restore_own');
	
	$total_new = 0;
	$total_issue = 0;
	$total_feature = 0;
	$total_att = 0;
        while ($row = $smcFunc['db_fetch_assoc']($result))
        {
		// Uh, if it is not in the trash we have no point in restoring it...
		if (empty($row['in_trash']))
			continue;
		
                if ($can_any || ($can_own && $context['user']['id'] == $row['tracker']))
                {
			if (!isset($projects[$row['id_project']]))
				$projects[$row['id_project']] = array('added' => 0, 'opened' => 0);
				
			// Is it open?
			if (in_array($row['status'], array('new', 'wip')))
				$projects[$row['id_project']]['opened']++;
					
			// Increase counts which always get changed.
			$projects[$row['id_project']]['added']++;
			$total_new++;
			
			if ($row['type'] == 'issue')
				$total_issue++;
			if ($row['type'] == 'feature')
				$total_feature++;
			if (!empty($row['attention']))
				$total_att++;
			
			// Put the entry in the queue.
			$mids[] = $row['id_entry'];
			
			// Log the move for this entry.
			fxt_logAction('restore', $row['id_entry'], 0);
		}
	}
	$smcFunc['db_free_result']($result);
	
	// No entries? :(
	if (empty($mids))
		return;
	
	// Move the entries.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}bugtracker_entries
		SET in_trash = 0
		WHERE id_entry IN ({array_int:ids})',
		array(
			'ids' => $mids
		));
	
	// And update the counts.
	foreach ($projects as $id => $counts)
	{
		// Grab the latest entry for this project.
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
					num_entries = num_entries + {int:total},
					num_open_entries = num_open_entries + {int:open},
					id_last_entry = {int:lei}
				WHERE id_project = {int:pid}',
				array(
					'pid' => $id,
					'total' => $counts['added'],
					'open' => $counts['opened'],
					'lei' => $eid
				));
		else
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}bugtracker_projects
				SET
					num_entries = num_entries + {int:total},
					num_open_entries = num_open_entries + {int:open}
				WHERE id_project = {int:pid}',
				array(
					'pid' => $id,
					'total' => $counts['added'],
					'open' => $counts['opened']
				));
	}
	
	// Update the overall counts.
        updateSettings(array(
         	'bt_total_entries' => $modSettings['bt_total_entries'] + $total_new,
         	'bt_total_issues' => $modSettings['bt_total_issues'] + $total_issue,
         	'bt_total_features' => $modSettings['bt_total_features'] + $total_feature,
         	'bt_total_important' => $modSettings['bt_total_important'] + $total_att
        ));
	
	// And we're done!
	return true;
}

/**
 * Changes the progress of the entries.
 * @param mixed $ids The entry/ies to change the progress of. Pass an array for multiple.
 * @param mixed $to  The value to change the progress to. You can also pass '+' or '-' to change it one step up or down respectively.
 *                   '+' and '-' are affected by the bt_entry_progress_steps setting.
 */
function fxt_changeProgress($ids, $to)
{
        global $smcFunc, $context, $modSettings;

	// Yes, ladies and gentleman, $to can be empty because it can be 0. $ids however can't be 0 because we start counting at 1 :)
        if (empty($ids) || (!is_array($ids) && !is_numeric($ids)) || (!is_numeric($to) && !in_array($to, array('+', '-'))))
                return;

	// Are we handling multiple IDs?
        $multiple = is_array($ids);

        // Sanitise them if we are.
        if ($multiple)
                $ids = fxt_sanitiseIDs($ids);

        // Else it must be a single ID...
        else
                $ids = array((int) $ids);

	// Check it again.
        if (empty($ids))
                return;
	
        // Lets check if we have permission and that the entry/ies actually exist.
        $result = $smcFunc['db_query']('', '
                SELECT
			e.id_entry, e.progress,
			n.id_author
                FROM {db_prefix}bugtracker_entries AS e
			INNER JOIN {db_prefix}bugtracker_notes AS n ON (e.id_first_note = n.id_note)
                WHERE e.id_entry IN ({array_int:id})',
                array(
                        'id' => $ids
                ));
	
	$can_any = allowedTo('bt_edit_any');
	$can_own = allowedTo('bt_edit_own');
	$plusmin = !empty($modSettings['bt_entry_progress_steps']) ? (int) $modSettings['bt_entry_progress_steps'] : 5;
	$mids = array();
        while ($row = $smcFunc['db_fetch_assoc']($result))
        {
		if ($can_any || ($can_own && $context['user']['id'] == $row['id_author']))
                {
			// We need to handle each entry separately if we increment or decrement in steps.
			if ($to === '-' || $to === '+')
			{
				switch ($to)
				{
					case '+':
						// Should be smaller than 100 if we want to change it.
						if ($row['progress'] < 100)
							$changeto = $row['progress'] + $plusmin;
						break;
					
					case '-':
						// Should be bigger than 0 if we want to change it.
						if ($row['progress'] > 0)
							$changeto = $row['progress'] - $plusmin;
						break;
				}
				
				// Wait.. We can't mark something as 105% done...
				if ($changeto > 100)
					$changeto = 100;
				
				// Nor can we mark it as -5% done...
				if ($changeto < 0)
					$changeto = 0;
				
				// Already the same? Pfft, got better stuff to do.
				if ($changeto == $row['progress'])
					continue;
				
				// Update this entry.
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}bugtracker_entries
					SET progress = {int:tochange}
					WHERE id_entry = {int:id}',
					array(
						'id' => $row['id_entry'],
						'tochange' => $changeto
					));
				
				// Did we hit 100%?
				if ($changeto == 100 && !empty($modSettings['bt_mark_automatic_completed']))
					fxt_markEntry($row['id_entry'], 'done');
			}
			else
			{
				$changeto = $to;
				if ($changeto == $row['progress'])
					continue;
				$mids[] = $row['id_entry'];
			}
			
			// Log the move for this entry.
			fxt_logAction('changeprogress', $row['id_entry'], 0, $row['progress'], $changeto);
		}
	}
	$smcFunc['db_free_result']($result);
	
	// Got mass updating to do? (everything is logged already, not bothering about it)
	if (!empty($mids))
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}bugtracker_entries
			SET progress = {int:tochange}
			WHERE id_entry IN ({array_int:ids})',
			array(
				'ids' => $mids,
				'tochange' => $to
			));
		
	// Did we hit 100%?
	if ($to == 100 && !empty($modSettings['bt_mark_automatic_completed']))
		fxt_markEntry($mids, 'done');
	
	// Done!
	return true;
}

/**
 * Sanitises an array of IDs.
 * @param array $ids The IDs to sanitise.
 */
function fxt_sanitiseIDs($ids = null)
{
	if (empty($ids))
		return;
        $sids = array();
        foreach ($ids as $tid)
        {
                $tsid = (int) $tid;
                if (empty($tsid))
                        continue;
                $sids[] = $tsid;
        }
        if (empty($sids))
                return;
        return $sids;
}


/**
 * Logs an action to the action log.
 * @param string $type  The type of the log entry.
 * @param int    $entry The entry ID.
 * @param int    $user  The user ID.
 * @param string $from  The changed data, before the change.
 * @param string $to    The changed data, after the change.
 */
function fxt_logAction($type, $entry, $user = null, $from = '', $to = '')
{
	global $context, $smcFunc, $user_profile;

	// Check the parameters.
	if ((empty($type) || !is_string($type)) || (empty($entry) || !is_numeric($entry)))
		return false;

	// Default back to the current user if there is no valid user ID.
	if (empty($user))
		$user = $context['user']['id'];
	else
		return false;

	// Try to see if this entry exists.
	$erequest = $smcFunc['db_query']('', '
		SELECT id_entry
		FROM {db_prefix}bugtracker_entries
		WHERE id_entry = {int:entry}',
		array(
			'entry' => $entry
		));

	// It doesn't if there are no rows returned.
	if ($smcFunc['db_num_rows']($erequest) == 0)
		return false;

	// Grab the data if it exists.
	$edata = $smcFunc['db_fetch_assoc']($erequest);

	$smcFunc['db_free_result']($erequest);

	// Insert it.
	$smcFunc['db_insert']('insert',
		'{db_prefix}bugtracker_log_actions',
		array(
			'id_entry' => 'int',
			'id_user' => 'int',
			'type' => 'string',
			'from' => 'string',
			'to' => 'string',
			'time' => 'int'
		),
		array(
			$entry,
			$user,
			$smcFunc['strtolower']($smcFunc['htmlspecialchars']($type)),
			$smcFunc['htmlspecialchars']($from),
			$smcFunc['htmlspecialchars']($to),
			time()
		),
		array());
		
	return true;
}
