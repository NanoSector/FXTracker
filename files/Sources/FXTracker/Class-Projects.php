<?php

/*
 * FXTracker - A Bug Tracker for SMF
 * ------------------------------------------
 * @package   FXTracker
 * @author    Yoshi2889
 * @copyright Yoshi2889 2012-2013
 * @license   http://creativecommons.org/licenses/by-sa/3.0/deed.en_US CC-BY-SA
 */

class FXTracker_ProjectList
{
	private $projectList;
	
	/**
	 * Gets all available projects and their data, including the latest available entry.
	 * Puts them in $this->projectList.
	 */
	function __construct()
	{
		global $smcFunc, $scripturl;
		
		$results = $smcFunc['db_query']('', '
			SELECT
				p.id_project, p.title, p.description, p.num_entries, p.num_open_entries,
				l.id_entry, l.title AS entry_title,
				fn.posted_time,
				m.real_name, m.id_member
			FROM {db_prefix}bugtracker_projects AS p
				LEFT JOIN {db_prefix}bugtracker_entries AS l ON (p.id_last_entry = l.id_entry)
				LEFT JOIN {db_prefix}bugtracker_notes AS fn ON (l.id_first_note = fn.id_note)
				LEFT JOIN {db_prefix}members AS m ON (fn.id_author = m.id_member)');
	
		// We got 'em.
		while ($row = $smcFunc['db_fetch_assoc']($results))
		{
			$this->projectList[$row['id_project']] = array_merge($row,
				array(
					// Figure out how much of this project is completed.
					// If there are no entries, this project will be 100% complete. (to prevent errors.)
					'percent_completed' => $row['num_entries'] != 0 ? round(100 - ($row['num_open_entries'] / $row['num_entries'] * 100)) : 100,
					
					// The link to the latest entry.
					'last_entry' => array(
						'entry_link' => '<a href="' . $scripturl . '?action=bugtracker;sa=view;entry=' . $row['id_entry'] . '">' . $row['entry_title'] . '</a>',
						'member_link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
						'time' => timeformat($row['posted_time'])
					)
				)
			);
		}
		$smcFunc['db_free_result']($results);
	}
	
	/**
	 * Simply returns all the projects we have.
	 * @return string[] The projects.
	 */
	function getAll()
	{
		return $this->projectList;
	}
}
