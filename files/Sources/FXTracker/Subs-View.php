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
 * Grabs a list of the entries for createList.
 * @param int    $start            The number of entries to skip.
 * @param int    $items_per_page   The number of entries to show per page.
 * @param string $sort             The way of sorting the entries.
 * @param array  $where            What to search for in the query.
 * @param array  $queryparams      The query parameters.
 * @param array  $hideRejectClosed The mode for hiding or showing the rejected and closed entries.
 */
/*function viewGetEntries($start, $items_per_page, $sort, $where = array(), $queryparams = array(), $hideRejectClosed = array(), $showBells = true, $readonly = false)
{
	global $context, $smcFunc, $settings, $scripturl, $txt, $user_profile, $modSettings;
		
	// Viewing rejected entries or resolved ones? Don't do this when viewing important entries.
	if (!in_array('attention = 1', $where) && !in_array('in_trash = 1', $where))
	{
		$showR = empty($hideRejectClosed) || !in_array('reject', $hideRejectClosed);
		$showC = empty($hideRejectClosed) || !in_array('closed', $hideRejectClosed);
		
		if ($showR && $showC)
			$where[] = '(status = \'reject\' OR status = \'done\')';
			
		else
		{
			if (!$showR && !$showC)
				$where[] = 'status != \'reject\'';
			elseif (!$showC)
				$where[] = 'status = \'reject\'';
				
			if (!$showC && !$showR)
				$where[] = 'status != \'done\'';
			elseif (!$showR)
				$where[] = 'status = \'done\'';
		}
	}
	
	// Get our WHERE statement ready.
	$fwhere = 'WHERE ' . implode(' AND ', $where);

	// And fix up our query.
	$result = $smcFunc['db_query']('', '
		SELECT
			e.id_entry, e.title, n.note AS description, e.type,
			 e.status, e.attention, e.id_project, e.progress,
			n.id_author, n.posted_time,
			m.real_name AS member_name,
			p.title AS project_title
		FROM {db_prefix}bugtracker_entries AS e
			INNER JOIN {db_prefix}bugtracker_notes AS n ON (n.id_note = e.id_first_note)
			INNER JOIN {db_prefix}bugtracker_projects AS p ON (p.id_project = e.id_project)
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = n.id_author)
		' . (!empty($where) ? $fwhere : '') . '
		ORDER BY {raw:order}
		LIMIT {int:start}, {int:items}',
		array_merge(
			$queryparams,
			array(
				'start' => $start,
				'order' => $sort,
				'items' => $items_per_page
			)
		)
	);

	// Fetch 'em.
	$entries = array();
	$earr = array();
	while ($entry = $smcFunc['db_fetch_assoc']($result))
	{
		// Set up the member link.
		$user_link = $scripturl . '?action=profile;u=' . $entry['id_author'];

		// The image of the type.
		$typeimgsrc = '<img src="' . $settings['images_url'] . '/bugtracker/types/' . $entry['type'] . '.png" alt="" />';

		// And the status.
		switch ($entry['status'])
		{
			// WIP entries have an animated icon.
			case 'wip':
				$statusimg = 'wip.gif';
				break;
				
			// All other images.
			default:
				$statusimg = $entry['status'] . '.png';

				break;
		}
		
		// Now get the actual status images done.
		// First up; does the entry require attention?
		$attention = $entry['attention'] && $showBells ? '<img src="' . $settings['images_url'] . '/bugtracker/attention.png" alt="" />' : '';
		
		// Then the actual status. Append it to the attention icon, if we can.
		$statusimgsrc = $attention . '<img src="' . $settings['images_url'] . '/bugtracker/' . $statusimg . '" alt="" />';

		// Set the URL to the project.
		$projecturl = '
			<a href="' . $scripturl . '?action=bugtracker;sa=projectindex;project=' . $entry['id_project'] . '">
				' . $entry['project_title'] . '
			</a>';

		// Fix up the tracker name and text, and a link if possible.
		if ($entry['id_author'] == 0)
			$nametext = sprintf($txt['tracked_by_guest'], timeformat($entry['posted_time']));
		else
			$nametext = sprintf($txt['tracked_by_user'], timeformat($entry['posted_time']), $user_link, $entry['member_name']);


		// It is new if:
		// 1. The user is not a guest.
		// 2. The has_read array is not empty.
		// 3. The has_read variable is empty for this entry.
		$is_new = !$context['user']['is_guest'] && !empty($context['bugtracker']['has_read']) && empty($context['bugtracker']['has_read'][$entry['id']]);

		// Now fix up this.
		$entries[$entry['id_entry']] = array(
			'id' => $entry['id_entry'],
			'typeimg' => $typeimgsrc,
			'statusimg' => $statusimgsrc,
			'name' => '
			<a href="' . $scripturl . '?action=bugtracker;sa=view;entry=' . $entry['id_entry'] . '">
				' . $entry['title'] . ' ' . ($entry['status'] == 'wip' ? '<span class="smalltext" style="color:#E00000">(' . $entry['progress'] . '%)</span>' : '') . '
				[NEWIMG]
			</a>
			<div class="smalltext">
				' . $nametext . '
			</div>',

			'statusurl' => '
			<a href="' . $scripturl . '?action=bugtracker;sa=viewstatus;status=' . $entry['status'] . '">
				' . $txt['status_' . $entry['status']] . '
			</a>',

			'typeurl' => '
			<a href="' . $scripturl . '?action=bugtracker;sa=viewtype;type=' . $entry['type'] . '">
				' . $txt[$entry['type']] . '
			</a>',

			'projecturl' => $projecturl,
			'quickmod' => '
			<input type="checkbox" name="fxtqm[]" value="' . $entry['id_entry'] . '" />',
		);
		
		$earr[] = $entry['id_entry'];
	}

	$smcFunc['db_free_result']($result);
	
	if (!empty($earr))
	{
		$reads = hasReadEntry($earr);
		//echo var_dump($earr, $reads);
		foreach ($reads as $id => $hasread)
		{
			$entries[$id]['read'] = $hasread;
			$entries[$id]['name'] = str_replace('[NEWIMG]', (empty($hasread) ? '&nbsp;<span class="new_posts">' . $txt['new'] . '</span>' : ''), $entries[$id]['name']);
		}
	}

	return $entries;
}*/

/**
 * Grabs an amount of entries for createList.
 * @param array  $where            What to search for in the query.
 * @param array  $queryparams      The query parameters.
 * @param array  $hideRejectClosed The mode for hiding or showing the rejected and closed entries.
 */
/*function viewGetEntriesCount($hideRejectClosed = array(), $where = array(), $queryparams = array(), $readonly = false)
{
	global $smcFunc, $modSettings;
	
	// Viewing rejected entries or resolved ones? Don't do this when viewing important entries.
	if (!in_array('attention = 1', $where) && !in_array('in_trash = 1', $where))
	{
		$showR = empty($hideRejectClosed) || !in_array('reject', $hideRejectClosed);
		$showC = empty($hideRejectClosed) || !in_array('closed', $hideRejectClosed);
		
		if ($showR && $showC)
			$where[] = '(status = \'reject\' OR status = \'done\')';
			
		else
		{
			if (!$showR && !$showC)
				$where[] = 'status != \'reject\'';
			elseif (!$showC)
				$where[] = 'status = \'reject\'';
				
			if (!$showC && !$showR)
				$where[] = 'status != \'done\'';
			elseif (!$showR)
				$where[] = 'status = \'done\'';
		}
	}

	// Mix up our WHERE statement to something usable.
	$fwhere = 'WHERE ' . implode(' AND ', $where);
	
	// Just do it.
	$result = $smcFunc['db_query']('', '
		SELECT count(id_entry)
		FROM {db_prefix}bugtracker_entries AS e
		' . (!empty($where) ? $fwhere : ''),
		$queryparams
	);

	list ($count) = $smcFunc['db_fetch_row']($result);

	$smcFunc['db_free_result']($result);

	return $count;
}*/

/**
 * Grabs an array of projects.
 * @param int $specific The ID of a specific project to grab.
 */
function grabProjects($specific = null)
{
	global $smcFunc, $context;

	// Double work?
	if (isset($context['bugtracker']['projects']))
		return $context['bugtracker']['projects'];

	$result = $smcFunc['db_query']('', '
		SELECT
			id_project, title, description
		FROM {db_prefix}bugtracker_projects'
	);

	$projects = array();
	while ($project = $smcFunc['db_fetch_assoc']($result))
	{
		$projects[$project['id_project']] = array(
			'id' => $project['id_project'],
			'name' => $smcFunc['htmlspecialchars']($project['title']),
			'description' => $smcFunc['htmlspecialchars']($project['description'])
		);
	}

	$smcFunc['db_free_result']($result);

	// Anything specific, sir?
	if (!empty($specific) && isset($projects[$specific]))
		return $projects[$specific];
	else
		return $projects;
}

/**
 * Function to check if an entry exists.
 * @param  mixed $id The entry ID(s) to check.
 * @return array An array with the entries which do exist. False if none were found.
 */
function checkEntryExists($id)
{
	global $smcFunc, $sourcedir;

	if (is_array($id))
	{
		require_once($sourcedir . '/FXTracker/Subs-Edit.php');
		$id = fxt_sanitiseIDs($id);
	}
	else
		$id = array((int) $id);
	
	// No ids? :(
	if (empty($id))
		return false;

	// Okay, lets get started.
	$result = $smcFunc['db_query']('', '
		SELECT id_entry
		FROM {db_prefix}bugtracker_entries
		WHERE id_entry IN ({array_int:id})',
		array(
			'id' => $id
		)
	);

	$exist = array();
	while ($row = $smcFunc['db_fetch_row']($result))
	{
		$exist[] = $row[0];
	}

	if (count($exist) >= 1)
		return $exist;
	else
		return false;
}

/**
 * Checks if the current user has read the specified entry.
 * @param mixed $id The entry ID(s). If left null, won't be checked.
 */
function hasReadEntry($id = null)
{
	global $context, $smcFunc, $sourcedir;
	
	if (is_array($id))
	{
		require_once($sourcedir . '/FXTracker/Subs-Edit.php');
		$id = fxt_sanitiseIDs($id);
	}
	else
		$id = array((int) $id);
		
	$check = checkEntryExists($id);
	
	// Uh, the entry does not exist? D:
	if (empty($check))
		return false;
	
	// Are we logged in? Guests have read everything.
	if ($context['user']['is_guest'])
	{
		$read = array();
		foreach ($check as $id)
			$read[$id] = true;
		return $read;
	}
	
	// Try to grab 'em all.
	$results = $smcFunc['db_query']('', '
		SELECT e.id_entry, IFNULL(lr.id_note, 0) AS id_last_note_read, e.id_last_note
		FROM {db_prefix}bugtracker_entries AS e
			LEFT JOIN {db_prefix}bugtracker_log_entries AS lr ON (lr.id_entry = e.id_entry AND lr.id_member = {int:user_id})
		WHERE e.id_entry IN ({array_int:ids})
		ORDER BY e.id_entry DESC',
		array(
			'ids' => $id,
			'user_id' => $context['user']['id'],
		));
	
	$read = array();
	while ($row = $smcFunc['db_fetch_assoc']($results))
	{
		$read[$row['id_entry']] = !empty($row['id_last_note_read']) && $row['id_last_note_read'] == $row['id_last_note'];
	}
	$smcFunc['db_free_result']($results);
	
	return $read;
}
