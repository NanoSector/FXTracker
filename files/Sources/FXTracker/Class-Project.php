<?php

/*
 * FXTracker - A Bug Tracker for SMF
 * ------------------------------------------
 * @package   FXTracker
 * @author    Yoshi2889
 * @copyright Yoshi2889 2012-2013
 * @license   http://creativecommons.org/licenses/by-sa/3.0/deed.en_US CC-BY-SA
 */

class FXTracker_Project
{
	// This holds the data of our project.
	protected $data = array();
	
	/**
	 * Sets up the project context, stores it in the class, and hands it back.
	 * @return null Nothing. (catch the class instance instead)
	 */
        function __construct($project_id)
	{
		global $smcFunc, $scripturl;
		
		$result = $smcFunc['db_query']('', '
			SELECT
				p.id_project, p.title, p.description, p.num_entries, p.num_open_entries,
				l.id_entry, l.title AS entry_title,
				fn.posted_time,
				m.real_name, m.id_member
			FROM {db_prefix}bugtracker_projects AS p
				LEFT JOIN {db_prefix}bugtracker_entries AS l ON (p.id_last_entry = l.id_entry)
				LEFT JOIN {db_prefix}bugtracker_notes AS fn ON (l.id_first_note = fn.id_note)
				LEFT JOIN {db_prefix}members AS m ON (fn.id_author = m.id_member)
			WHERE p.id_project = {int:pid}',
			array(
			      'pid' => (int) $project_id
			));
		
		if ($smcFunc['db_num_rows']($result) === 0)
			fatal_lang_error('project_no_exist');
	
		// It's only one. Don't bother with a while.
		$row = $smcFunc['db_fetch_assoc']($result);
		
		// And we got it.
		$this->data = array_merge($row,
			array(
				// Figure out how much of this project is completed.
				// If there are no entries, this project will be 100% complete. (to prevent errors.)
				'percent_completed' => $row['num_entries'] != 0 ? round(100 - ($row['num_open_entries'] / $row['num_entries'] * 100)) : 100,
				
				// Amount of closed entries:
				'num_closed_entries' => $row['num_entries'] - $row['num_open_entries'],
				
				// The link to the latest entry.
				'last_entry' => array(
					'entry_link' => '<a href="' . $scripturl . '?action=bugtracker;sa=view;entry=' . $row['id_entry'] . '">' . $row['entry_title'] . '</a>',
					'member_link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
					'time' => timeformat($row['posted_time'])
				)
			)
		);
		
		// Amount of important entries.
		$this->data['num_important'] = $this->getCount('important');
		$smcFunc['db_free_result']($result);
	}
	
	/**
	 * Gets the project data and returns it.
	 * @return string[] The project data.
	 */
	function get()
	{
		return $this->data;
	}
	
	/**
	 * Gets the amount entries in the project. Set to a random value (kittens?) if you want to include everything.
	 * @return mixed The result; false if failed.
	 */
	function getCount($type = 'all_but_trash')
	{
		if (empty($type))
			return false;
		
		global $smcFunc;
	
		$sql = '
			SELECT count(id_entry)
			FROM {db_prefix}bugtracker_entries
			WHERE id_project = {int:pid}';
		
		switch ($type)
		{
			case 'important':
				$sql .= ' AND attention = 1 AND in_trash = 0';
				break;
			
			case 'trash':
				$sql .= ' AND in_trash = 1';
				break;
			
			case 'issue':
				$sql .= ' AND type = \'issue\'';
				break;
			
			case 'feature':
				$sql .= ' AND type = \'feature\'';
				break;
			
			case 'new':
				$sql .= ' AND status = \'new\'';
				break;
			
			case 'wip':
				$sql .= ' AND status = \'wip\'';
				break;
			
			case 'done':
				$sql .= ' AND status = \'done\'';
				break;
			
			case 'reject':
				$sql .= ' AND status = \'reject\'';
				break;
			
			case 'all_but_trash':
				$sql .= ' AND in_trash = 0';
				break;
			
			default:
				break;
		}
		
		// Run the query.
		$result = $smcFunc['db_query']('', $sql, array('pid' => $this->data['id_project']));
		list ($count) = $smcFunc['db_fetch_row']($result);
		
		return $count;
	}
}