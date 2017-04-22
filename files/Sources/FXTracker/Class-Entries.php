<?php

/*
 * FXTracker - A Bug Tracker for SMF
 * ------------------------------------------
 * @package   FXTracker
 * @author    Yoshi2889
 * @copyright Yoshi2889 2012-2013
 * @license   http://creativecommons.org/licenses/by-sa/3.0/deed.en_US CC-BY-SA
 */

 // This is the main class for grabbing multiple entries at once.
class FXTracker_Entries
{
	// This stores every piece used in $where, for further manipulation.
	// Example piece: 
	protected $pieces = array();
	protected $custom = array();
	protected $where = '';
	
	// This number stores how many entries we should get.
	// This is set to a default value upon construction of the class.
	protected $num = 0;
	
	// This item determines the order the entries are sorted in.
	protected $order = 'e.id_entry DESC';
	
	// This holds the entry data. Easy.
	// Should use entry_id => data format.
	protected $data = array();
	
	/**
	 * Set some default values.
	 */
	function __construct()
	{
		global $modSettings;
		
		$this->num = $modSettings['defaultMaxMessages'];
	}
	
	/**
	 * This function limits the scope in which $this->query and $this->createList will work.
	 * Neither will work unless a scope is set; this is to avoid grabbing too many entries at once.
	 * If you specify a scope that already exists, it will get overwritten.
	 * @param string $column The column to select data from. Don't forget prefixes!
	 * @param string $value  The value to compare to.
	 * @param bool   $raw    Is the value inserted a raw value? (NOT RECOMMENDED)
	 * @return bool          True or false depending on whether the operation succeeded.
	 */
	function scope($column, $value, $raw = false)
	{
		// Do we have anything set? Or do we already have an entry for this column (this isn't quantum computing, guys!)
		if (empty($column) || (empty($value) && $value !== '0') || !preg_match('/^[a-zA-Z0-9_.]+$/', $column))
			return false;
		
		// We need $smcFunc.
		global $smcFunc;
		
		// Throw it in.
		$this->pieces[$column] = !$raw ? $smcFunc['db_escape_string']($value) : $value;
		return true;
	}
	
	/**
	 * This function removes an item in the search scope.
	 * @param  string $column The ID of the piece to remove.
	 * @return bool           True or false depending on whether the operation succeeded.
	 */
	function scope_undo($column)
	{
		// Do we have anything to undo?
		if (empty($column) || !array_key_exists($column, $this->pieces))
			return false;
		
		// Unset it. That's all we need to do.
		unset($this->pieces[$column]);
		return true;
	}
	
	/**
	 * Allows you to insert custom scopes as RAW SQL in here, with support for sprintf for escaping values.
	 * @param  string   $raw     The raw SQL data.
	 * @param  bool     $sprintf Should vsprintf() be used?
	 * @param  string[] $values  The values that should be escaped (and passed to vsprintf())
	 * @return bool              True or false depending on whether the operation succeeded.
	 */
	function scope_custom($raw, $sprintf = false, $values = array())
	{
		if (empty($raw) || (!empty($sprintf) && empty($values)))
			return false;
		
		global $smcFunc;
		
		// Are we going to use vsprintf?
		if (!empty($sprintf) && !empty($values))
		{
			// First sanitise any values we may have.
			foreach ($values as $key => $value)
			{
				$values[$key] = $smcFunc['db_escape_string']($value);
			}
			
			// Then vsprintf the string together, and handle it like normal.
			$raw = vsprintf($raw, $values);
		}
		
		// Just throw in the raw data. I have faith!
		$this->custom[] = $raw;

		// And give back the ID.
		return end(array_keys($this->custom));
	}

	/**
	 * This function removes an item in the custom search scope.
	 * @param  int  $id The ID of the piece to remove.
	 * @return bool     True or false depending on whether the operation succeeded.
	 */
	function scope_custom_undo($id)
	{
		// Do we have anything to undo?
		if (!array_key_exists((int) $id, $this->custom))
			return false;
		
		// Unset it. That's all we need to do.
		unset($this->pieces[(int) $id]);
		return true;
	}
	
	/**
	 * This function sets the amount of entries that should be loaded.
	 * @return bool True or false depending on whether the operation succeeded.
	 */
	function limit($num)
	{
		if (empty((int) $num))
			return false;
		
		// Pour it over.
		$this->num = (int) $num;
		return true;
	}
	
	/**
	 * This builds $this->where out of the available pieces, ready to insert into a query.
	 * There is no need to call this function yourself. It is done automatically in $this->query.
	 * @return bool True or false depending on whether the operation succeeded.
	 */
	protected function buildWhere()
	{
		// We can't build a WHERE with no pieces.
		if (empty($this->pieces))
			return false;

		// This is where part of the magic happens. Everything should be sanitised and ready...
		$where = 'WHERE ';
		
		// Loop through every piece.
		$lastPiece = end(array_keys($this->pieces));
		$haveCustom = !empty($this->custom);
		foreach ($this->pieces as $column => $value)
		{
			$where .= $column . ' = \'' . $value . '\'';
			
			if ($lastPiece != $column || $haveCustom)
				$where .= ' AND ';
		}
		
		// Paste on any custom pieces.
		$lastPiece = end(array_keys($this->custom));
		foreach ($this->custom as $id => $piece)
		{
			$where .= $piece;
			
			if ($lastPiece != $id)
				$where .= ' AND ';
		}
		
		// And set it!
		$this->where = $where;
		return true;
	}
	
	/**
	 * This performs the actual querying work. This will automatically be called when $this->data is empty,
	 * and either $this->get() or $this->createList() is called, so there is usually no need to separately
	 * call this function.
	 * @param int $start The entry # where we should start on. Defaults to 0.
	 * @return bool      True or false depending on whether the operation succeeded.
	 */
	function query($start = 0, $ipp = null, $order = null)
	{
		// Clear out our data.
		$this->data = array();
		
		// Try to build a where.
		$this->buildWhere();
		
		// There's still no where? There's no query.
		if (empty($this->where))
			return false;
		
		global $context, $smcFunc, $scripturl, $settings, $txt;

		// Fix up our query.
		$result = $smcFunc['db_query']('', '
			SELECT
				e.id_entry, e.title, n.note AS description, e.type, e.in_trash, 
				 e.status, e.attention, e.id_project, e.progress, e.id_last_note,
				n.id_author, n.posted_time,
				m.real_name AS member_name,
				p.title AS project_title' . ($context['user']['is_logged'] ? ', IFNULL(lr.id_note, 0) AS id_last_note_read' : '') . '
			FROM {db_prefix}bugtracker_entries AS e
				INNER JOIN {db_prefix}bugtracker_notes AS n ON (n.id_note = e.id_first_note)
				INNER JOIN {db_prefix}bugtracker_projects AS p ON (p.id_project = e.id_project)
				LEFT JOIN {db_prefix}members AS m ON (m.id_member = n.id_author)
				' . ($context['user']['is_logged'] ? 'LEFT JOIN {db_prefix}bugtracker_log_entries AS lr ON (lr.id_entry = e.id_entry AND lr.id_member = ' . (int) $context['user']['id'] . ')' : '') . '
			' . $this->where . '
			ORDER BY {raw:order}
			LIMIT {int:start}, {int:items}',
			array(
				'start' => (int) $start,
				'order' => !empty($order) ? $order : $this->order,
				'items' => !empty($ipp) ? (int) $ipp : $this->num
			)
		);
		
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			// Show the progress?
			if ($row['status'] == 'wip')
				$row['title'] .= ' <span class="smalltext progress">(' . $row['progress'] . '%)</span>';
				
			// Setup the icons.
			$attention = $row['attention'] && !array_key_exists('e.attention', $this->pieces) ? '<img src="' . $settings['images_url'] . '/bugtracker/attention.png" alt="" />' : '';
			$row['status_img'] = $attention . '<img src="' . $settings['images_url'] . '/bugtracker/' . ($row['status'] == 'wip' ? 'wip.gif' : $row['status'] . '.png') . '" alt="" />';
			
			// Trash icon?
			$trash = $row['in_trash'] && !array_key_exists('e.in_trash', $this->pieces) ? '<img src="' . $settings['images_url'] . '/bugtracker/trash.png" alt="" />' : '';
			$row['type_img'] = $trash . '<img src="' . $settings['images_url'] . '/bugtracker/types/' . $row['type'] . '.png" alt="" />';
			
			// Have we read this entry?
			$row['read'] = !empty($row['id_last_note_read']) && $row['id_last_note_read'] >= $row['id_last_note'];
			if ($context['user']['is_logged'] && !$row['read'])
				$row['title'] .= '&nbsp;<span class="new_posts">' . $txt['new'] . '</span>';
				
			// What should we display for the user data?
			if ($row['id_author'] == 0)
				$nametext = sprintf($txt['tracked_by_guest'], timeformat($row['posted_time']));
			else
				$nametext = sprintf($txt['tracked_by_user'], timeformat($row['posted_time']), $scripturl . '?action=profile;u=' . $row['id_author'], $row['member_name']);
			
			$this->data[$row['id_entry']] = array_merge($row,
				array(
					// Links to satisfy both ends of the world.
					'link' => '<a href="' . $scripturl . '?action=bugtracker;sa=view;entry=' . $row['id_entry'] . '">' . $row['title'] . '</a>',
					'link_author' => '<a href="' . $scripturl . '?action=bugtracker;sa=view;entry=' . $row['id_entry'] . '">' . $row['title'] . '</a>
					<div class="smalltext">' . $nametext . '</div>',
					
					// Project data.
					'id_project' => $row['id_project'],
					'project_name' => $row['project_title'],
					'project_link' => '<a href="' . $scripturl . '?action=bugtracker;sa=projectindex;project=' . $row['id_project'] . '">' . $row['project_title'] . '</a>',//$scripturl . '?action=bugtracker;sa=projectindex;project=' . $row['id_project'],
					
					// Type/status.
					'status' => $txt['bt_statuses'][$row['status']],
					'type' => $txt['bt_types'][$row['type']],
					
					// For quick moderation.
					'quickmod' => '
					<input type="checkbox" name="fxtqm[]" value="' . $row['id_entry'] . '" />',
				));
		}
		$smcFunc['db_free_result']($result);
		return true;
	}
	
	/**
	 * This function returns all entries which conform to the set scope.
	 * @param int $start    The number of entries we should start with.
	 * @param string $order The name of the column (and direction) we should order by.
	 * @param int $ipp      The items per page number.
	 * @param bool $force   Force a reload of all data.
	 * @return string[]   An array containing all entries which conform to the set scope.
	 */
	function get($start = 0, $order = null, $ipp = null)
	{
		// Try grabbing data (again) if we have none.
		$this->query($start, $order, $ipp);
		
		// Return it.
		return $this->data;
	}
	
	/**
	 * This function returns the amount of entries we should get if a query is run.
	 * No parameters.
	 * @return int The amount of entries found.
	 */
	function count()
	{
		// No where? Try to build it.
		$this->buildWhere();
		
		// There's still no where? There's no count.
		if (empty($this->where))
			return false;
		
		global $smcFunc, $scripturl;
		
		// Do the counting.
		$result = $smcFunc['db_query']('', '
			SELECT count(e.id_entry)
			FROM {db_prefix}bugtracker_entries AS e
				INNER JOIN {db_prefix}bugtracker_notes AS n ON (n.id_note = e.id_first_note)
			' . $this->where,
			array()
		);
		list ($count) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

		return $count;
	}
	
	/**
	 * This function builds the monster that's $listOptions, and attempts to create the list.
	 * @param string $basehref The URL this list resides at.
	 * @param string $id       The ID of this list, used in your template.
	 * @return bool            True or false depending on whether the operation succeeded.
	 */
	function createList($basehref, $id = 'fxt_view')
	{
		// No where? Try to build it.
		$this->buildWhere();
		
		// There's still no where? There's no count.
		if (empty($this->where))
			return false;
		
		// We want all this.
		global $context, $txt, $modSettings, $scripturl, $sourcedir;
		
		require_once($sourcedir . '/Subs-List.php');
		
		// Can we actually move projects?
		$canMove = allowedTo(array('bt_move_any', 'bt_move_own'));
		
		$projselect = '';
		if ($canMove)
		{
			// Get the project IDs and names.
		
			if (!empty($queryparams['project']))
				unset($projects[$queryparams['project']]);
				
			if (!empty($projects))
			{
				$projselect .= '
					<select name="fxtqm_proj">';
				foreach ($projects as $option)
				{
					$projselect .= '
						<option value="' . $option['id'] . '">' . $option['name'] . '</option>';
				}
				$projselect .= '
					</select>';
			}
		}
	
		// Figure out what we can and cannot do.
		$options = '';
		$sa = !empty($_GET['sa']) ? (string) $_GET['sa'] : '';
		if ($sa != 'trash' && allowedTo(array('bt_remove_any', 'bt_remove_own')))
			$options .= '<option value="remove">' . $txt['quick_mod_remove'] . '</option>';
			
		if ($sa == 'trash' && allowedTo(array('bt_restore_any', 'bt_restore_own')))
			$options .= '<option value="restore">' . $txt['qm_restore'] . '</option>';
			
		if (allowedTo(array('bt_mark_attention_any', 'bt_mark_attention_own')))
			$options .= '<option value="mark_att">' . $txt['mark_attention'] . '</option>
						 <option value="mark_not_att">' . $txt['mark_attention_undo'] . '</option>';
		
		if (allowedTo(array('bt_mark_new_any', 'bt_mark_new_own')))
			$options .= '<option value="mark_new">' . $txt['mark_new'] . '</option>';
			
		if (allowedTo(array('bt_mark_wip_any', 'bt_mark_wip_own')))
			$options .= '<option value="mark_wip">' . $txt['mark_wip'] . '</option>';
	
		if (allowedTo(array('bt_mark_done_any', 'bt_mark_done_own')))
			$options .= '<option value="mark_done">' . $txt['mark_done'] . '</option>';
			
		if (allowedTo(array('bt_mark_reject_any', 'bt_mark_reject_own')))
			$options .= '<option value="mark_reject">' . $txt['mark_reject'] . '</option>';
			
		if (!empty($projects) && $canMove)
			$options .= '<option value="move">' . $txt['move_dr'] . '</option>';
	
		$listOptions = array(
			'id' => $id,
			'items_per_page' => $modSettings['defaultMaxMessages'],
			'no_items_label' => $txt['no_items'],
			'base_href' => $basehref,
			'default_sort_col' => 'id',
			'default_sort_dir' => 'desc',
			'start_var_name' => 'mainstart',
			'request_vars' => array(
				'desc' => 'maindesc',
				'sort' => 'mainsort',
			),
			'get_items' => array(
				'function' => array($this, 'get'),
				'params' => array()
			),
			'get_count' => array(
				'function' => array($this, 'count'),
				'params' => array()
			),
			'columns' => array(
				'id' => array(
					'header' => array(
						'value' => 'ID',
					),
					'data' => array(
						'db' => 'id_entry',
						'class' => 'centertext',
						'style' => 'width: 33px', // No more!
					),
					'sort' => array(
						'default' => 'id_entry ASC',
						'reverse' => 'id_entry DESC'
					)
				),
				'typeimg' => array(
					'header' => array(
						'value' => '',
					),
					'data' => array(
						'db' => 'type_img',
						'class' => 'centertext',
						'style' => 'width: 2%',
					),
				),
				'statusimg' => array(
					'header' => array(
						'value' => ''
					),
					'data' => array(
						'db' => 'status_img',
						'class' => 'centertext',
						'style' => 'width:2%', // Else the attention icon won't look good
					),
				),
				'name' => array(
					'header' => array(
						'value' => $txt['title']
					),
					'data' => array(
						'db' => 'link_author',
						'class' => 'topic_table subject',
					),
					'sort' => array(
						'default' => 'title ASC',
						'reverse' => 'title DESC'
					)
				),
				'status' => array(
					'header' => array(
						'value' => $txt['status'],
						'class' => 'statusurl',
					),
					'data' => array(
						'db' => 'status',
						'class' => 'statusurl',
					),
					'sort' => array(
						'default' => 'status ASC',
						'reverse' => 'status DESC'
					)
				),
				'type' => array(
					'header' => array(
						'value' => $txt['type'],
						'class' => 'typeurl',
					),
					'data' => array(
						'db' => 'type',
						'class' => 'typeurl',
					),
					'sort' => array(
						'default' => 'type ASC',
						'reverse' => 'type DESC'
					)
				),
				'projecturl' => array(
					'header' => array(
						'value' => $txt['project'],
						'class' => 'projecturl',
					),
					'data' => array(
						'db' => 'project_link',
						'class' => 'projecturl',
					),
					'sort' => array(
						'default' => 'id_project ASC',
						'reverse' => 'id_project DESC'
					)
				),
				'quickmod' => array(
					'header' => array(
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form, \'fxtqm[]\')" class="input_check" />',
					),
					'data' => array(
						'db' => 'quickmod',
						'class' => 'centertext',
						'style' => 'width: 20px',
					)
				)
			),
		);
	
		if (!empty($options))
			$listOptions = array_merge($listOptions, array(
				'additional_rows' => array(
					array(
						'position' => 'below_table_data',
						'value' => '
						<div class="righttext" id="quick_actions">
							<select name="fxtqmtype">
								<option selected="selected" disabled="disabled">' . $txt['select_action'] . '</option>
								' . $options . '
							</select>' . $projselect . '
							<input type="submit" value="' . $txt['quick_mod_go'] . '" class="button_submit" />
						</div>',
					)
				),
				'form' => array(
					'href' => $scripturl . '?action=bugtracker;sa=qmod',
				),
			));
		createList($listOptions);
	}
}