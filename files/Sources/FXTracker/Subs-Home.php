<?php

/*
 * FXTracker - A Bug Tracker for SMF
 * ------------------------------------------
 * @package   FXTracker
 * @author    Yoshi2889
 * @copyright Yoshi2889 2012-2013
 * @license   http://creativecommons.org/licenses/by-sa/3.0/deed.en_US CC-BY-SA
 */

/*
 * Grabs the recent entries that meet the criteria specified.
 * @param int  $number   The number of entries to gather.
 * @param bool $sortasfi Switch to split feature requests and issues.
 */
function getRecentEntries($number = 5, $sortasfi = true, $project = 0)
{
        global $context, $smcFunc;

        if (empty($number) || !is_numeric($number))
                $number = 5;

        if (!is_bool($sortasfi))
                $sortasfi = true;
                
        $project = (int) $project;
                
        if ($sortasfi)
        {
        	$sql = '
        		SELECT
		                id, name, project, type
		        FROM {db_prefix}bugtracker_entries
		        WHERE type = {string:type} AND in_trash = 0' . (!empty($project) ? ' AND project = {int:pid}' : '') . '
		        ORDER BY startedon DESC, id DESC
		        LIMIT {int:limit}';
		
		// Grab issues.
        	$request = $smcFunc['db_query']('', 
        		$sql,
		        array(
		        	'type' => 'issue',
		                'limit' => $number,
		                'pid' => $project
		        ));
		
                // Fetch them.
                $latest_issues = array();
                while ($row = $smcFunc['db_fetch_assoc']($request))
                {
                	$latest_issues[] = $row;
                }
                
                // Grab the features.
                $request = $smcFunc['db_query']('', 
                	$sql,
		        array(
		        	'type' => 'feature',
		                'limit' => $number,
		                'pid' => $project
		        ));
		
                // Fetch them.
                $latest_features = array();
                while ($row = $smcFunc['db_fetch_assoc']($request))
                {
                	$latest_features[] = $row;
                }
                
                // And return!
                return array('issues' => $latest_issues, 'features' => $latest_features);
        }
        else
        {
        	// Request 'em.
		$request = $smcFunc['db_query']('', '
		        SELECT
		                id, name, project, type
		        FROM {db_prefix}bugtracker_entries
		        WHERE in_trash = 0' . (!empty($project) ? ' AND project = {int:pid}' : '') . '
		        ORDER BY startedon DESC, id DESC
		        LIMIT {int:limit}',
		        array(
		                'limit' => $number,
		                'pid' => $project
		        ));
		        
		// Fetch 'em.
                $latest_entries = array();
                while ($row = $smcFunc['db_fetch_assoc']($request))
                {
                        $latest_entries[] = $row;
                }

		// Free 'em.
                $smcFunc['db_free_result']($result);

		// Return 'em.
                return $latest_entries;
        }

        return false;
}
