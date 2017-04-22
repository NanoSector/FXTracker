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
 * Shows the form for editing entries.
 * No parameters - called by action.
 */
function BugTrackerEdit()
{
	global $context, $smcFunc, $txt, $sourcedir, $scripturl;

	// Are we using a valid entry id?
	$result = $smcFunc['db_query']('', '
		SELECT
			e.id_entry, e.title, e.type, e.id_project, e.attention,
			e.status, e.progress, e.id_first_note,
			fn.id_author, fn.note, 
			p.id_project, p.title AS project_title
		FROM {db_prefix}bugtracker_entries AS e
			INNER JOIN {db_prefix}bugtracker_projects AS p ON (e.id_project = p.id_project)
			INNER JOIN {db_prefix}bugtracker_notes AS fn ON (e.id_first_note = fn.id_note)
		WHERE e.id_entry = {int:entry}
		LIMIT 1',
		array(
			'entry' => $_GET['entry'],
		)
	);

	// No or multiple entries?
	if ($smcFunc['db_num_rows']($result) == 0)
		fatal_lang_error('entry_no_exist');

	// So we should have just one...
	$entry = $smcFunc['db_fetch_assoc']($result);
        $smcFunc['db_free_result']($result);

	// Not ours, and we have no permission to edit someone elses entry?
	if (!allowedTo('bt_edit_any') && (allowedTo('bt_edit_own') && $context['user']['id'] != $entry['id_author']))
		fatal_lang_error('edit_entry_else_noaccess');
		
	// Grab Subs-Post.php.
	require($sourcedir . '/Subs-Post.php');
		
	if (!empty($_POST['is_fxt']))
	{
		// Pour over these variables, so they can be altered and done with.
		$entry = array(
			'title' => (string) $_POST['entry_title'],
			'type' => (string) $_POST['entry_type'],
			'note' => (string) $_POST['entry_desc'],
			'status' => (string) $_POST['entry_mark'],
			'attention' => !empty($_POST['entry_attention']),
			'progress' => (int) $_POST['entry_progress'],
			'id_entry' => (int) $_POST['entry_id'],
			'project_id' => $entry['id_project'],
			'project_name' => $entry['project_title'],
			'id_fn' => $entry['id_first_note'],
		);
	
		// No entry? Obviously we can't save it.
		if (empty($entry['id_entry']))
			fatal_lang_error('save_failed');
	
		// Gather any errors which may have occured.
		$context['errors_occured'] = array();
	
		// Check if the title is empty.
		if (empty($entry['title']))
			$context['errors_occured']['title'] = $txt['no_title'];
	
		// Type... Also check if it is valid.
		if (empty($entry['type']) || !in_array($entry['type'], array('issue', 'feature')))
			$context['errors_occured']['type'] = $txt['no_type'];
	
		// And description.
		if (empty($entry['note']))
		{
			$context['errors_occured']['desc'] = $txt['no_description'];
			$context['post_error']['no_message'] = true;
		}
		else
		{
			preparsecode($entry['note']);
			$entry['note'] = preg_replace('~<br ?/?' . '>~i', "\n", $entry['note']);
		}
	
		// Are we submitting a valid mark? (rare condition)
		if (!in_array($entry['status'], array('new', 'wip', 'done', 'reject')))
			fatal_lang_error('save_failed');
	
		// No errors occured, yay!
		// !!! AJAX?
		if (empty($context['errors_occured']))
		{
			// Okay, lets prepare the entry data itself! Create an array of the available types.
			$fentry = array(
				'title' => $smcFunc['htmlspecialchars']($entry['title'], ENT_QUOTES),
				'type' => $smcFunc['strtolower']($entry['type']),
				'note' => $entry['note'],
				'status' => $smcFunc['strtolower']($entry['status']),
				'attention' => (int) $entry['attention'],
				'progress' => (int) $_POST['entry_progress'],
				'id' => (int) $entry['id_entry'],
				'id_fn' => $entry['id_fn'],
				'time' => time()
			);
	
			// Assuming we have everything ready now, update!
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}bugtracker_entries
				SET
					title = {string:title},
					type = {string:type},
					status = {string:status},
					attention = {int:attention},
					progress = {int:progress}
				WHERE id_entry = {int:id}',
				$fentry
			);
			
			// And update the note.
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}bugtracker_notes
				SET
					note = {string:note},
					updated_time = {int:time}
				WHERE id_note = {int:id_fn}',
				$fentry
			);
	
			// Edited, mark it as un-read again for everyone except the editor.
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}bugtracker_log_entries
				WHERE id_entry = {int:entry} AND id_member != {int:user}',
				array(
					'entry' => $fentry['id'],
					'user' => $context['user']['id']
				));
	
			// Then we're ready to opt-out!
			redirectexit($scripturl . '?action=bugtracker;sa=view;entry=' . $fentry['id']);
		}
	}

	// Load the template...
	loadTemplate('fxt/Edit');

	// We want the default SMF WYSIWYG editor and Subs-Post.php to make stuff look SMF-ish.
	require($sourcedir . '/Subs-Editor.php');

	// Make it so that the BBCode version will be returned, not the HTML one.
	un_preparsecode($entry['note']);

	// Some settings for the text editor...
	$editorOptions = array(
		'id' => 'entry_desc',
		'value' => $entry['note'],
		'height' => '275px',
		'width' => '100%',
		'preview_type' => 2,
	);
	create_control_richedit($editorOptions);

	// Store the ID. Might need it later on.
	$context['post_box_name'] = $editorOptions['id'];

	// Set up the edit page.
	$context['btform'] = array(
		'entry_name' => $entry['title'],
		'entry_status' => $entry['status'],
		'entry_type' => $entry['type'],
		'entry_attention' => $entry['attention'],
		'entry_progress' => $entry['progress'],

		'url' => $scripturl . '?action=bugtracker;sa=edit;entry=' . $entry['id_entry'],

		'extra' => array(
			'is_fxt' => array(
				'type' => 'hidden',
				'name' => 'is_fxt',
				'defaultvalue' => true,
			),
			'entry_id' => array(
				'type' => 'hidden',
				'name' => 'entry_id',
				'defaultvalue' => $entry['id_entry'],
			)
		),
	);

	$context['page_title'] = $txt['entry_edit'];

	// Set up the linktree. First up the project name.
	$context['linktree'][] = array(
		'name' => $entry['project_title'],
		'url' => $scripturl . '?action=bugtracker;sa=projectindex;project=' . $entry['id_project']
	);
	// Then show the entry we are editing.
	$context['linktree'][] = array(
		'name' => sprintf($txt['entry_edit_lt'], $entry['title']),
		'url' => $scripturl . '?action=bugtracker;sa=edit;entry=' . $entry['id_entry']
	);

	// And set the sub template.
	$context['sub_template'] = 'BugTrackerEdit';
}

/**
 * Allows the marking of a note.
 * No parameters - called by action.
 */
function BugTrackerMarkEntry()
{
	// Globalizing...
	global $context, $scripturl, $smcFunc, $sourcedir;

	// Attempt to mark it.
	require_once($sourcedir . '/FXTracker/Subs-Edit.php');
	$result = fxt_markEntry((int) $_GET['entry'], (string) $_GET['as']);

	// Failed? Well bummer.
	if (!$result)
		fatal_lang_error('entry_mark_failed');

	// And redirect us back.
	redirectexit($scripturl . '?action=bugtracker;sa=view;entry=' . $_GET['entry']);
}

/**
 * Shows the form for editing notes.
 * No parameters - called by action.
 */
function BugTrackerEditNote()
{
        // Need some stuff.
        global $context, $smcFunc, $user_profile, $sourcedir, $txt, $scripturl, $modSettings;

	// No notes? :(
	if (empty($modSettings['bt_enable_notes']))
		fatal_lang_error('notes_disabled');

        // Try to grab the note.
        $result = $smcFunc['db_query']('', '
                SELECT
                        id_note, id_entry, id_author, note, posted_time
                FROM {db_prefix}bugtracker_notes
                WHERE id_note = {int:id}',
                array(
                        'id' => $_GET['note']
                )
        );

        if ($smcFunc['db_num_rows']($result) == 0)
                fatal_lang_error('note_no_exist');

        // Load the note itself
        $data = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

        // Are we allowed to edit this note?
        if (allowedTo('bt_edit_note_any') || (allowedTo('bt_edit_note_own') && $data['id_author'] == $context['user']['id']))
        {
                loadMemberData($data['id_author']);

                // We want the default SMF WYSIWYG editor and Subs-Post.php to make stuff look SMF-ish.
                require_once($sourcedir . '/Subs-Editor.php');
                include($sourcedir . '/Subs-Post.php');

                // Do this...
                un_preparsecode($data['note']);

                // Some settings for it...
                $editorOptions = array(
                        'id' => 'note_text',
                        'value' => $data['note'],
                        'height' => '275px',
                        'width' => '100%',
                        // XML preview.
                        'preview_type' => 2,
                );
                create_control_richedit($editorOptions);

                // Store the ID. Might need it later on.
                $context['post_box_name'] = $editorOptions['id'];

                // Okay, lets set it up.
                $context['bugtracker']['note'] = array(
                        'id' => $data['id_note'],
                        'author' => $user_profile[$data['id_author']],
                        'time' => $data['posted_time'],
                        'note' => $data['note'],
                );

                // Page title, too.
                $context['page_title'] = $txt['edit_note'];

                // And built on the link tree.
                $context['linktree'][] = array(
                        'name' => $txt['edit_note'],
                        'url' => $scripturl . '?action=bugtracker;sa=editnote;note=' . $data['id_note'],
                );

                // And the sub-template...
                loadTemplate('fxt/Notes');
                $context['sub_template'] = 'TrackerEditNote';
        }
        else
                fatal_lang_error('note_edit_notyours');
}

// TODO: Merge this with BugTrackerEdit.
/**
 * Saves an edited note.
 * No parameters - called by action.
 */
function BugTrackerEditNote2()
{
        global $context, $smcFunc, $sourcedir, $scripturl, $modSettings;

	// Can't submit an edit when the notes functionality is disabled ay...
	if (empty($modSettings['bt_enable_notes']))
		fatal_lang_error('notes_disabled');

        // Okay. See if we have submitted the data!
        if (!isset($_POST['is_fxt']) || $_POST['is_fxt'] != true)
                fatal_lang_error('note_save_failed');

        // Missing some data? :S
        if (empty($_POST['note_id']))
                fatal_lang_error('note_save_failed');

        if (empty($_POST['note_text']))
                fatal_lang_error('note_empty');

        // So we have submitted something. Grab the data to here.
        $pnote = array(
                'id' => $_POST['note_id'],
                'text' => $_POST['note_text'],
        );

        // Load the note data.
        $result = $smcFunc['db_query']('', '
                SELECT
                        n.id_note, n.id_entry, n.id_author,
			m.real_name
                FROM {db_prefix}bugtracker_notes AS n
			INNER JOIN {db_prefix}members AS m ON (m.id_member = n.id_author)
                WHERE n.id_note = {int:id}',
                array(
                        'id' => $pnote['id'],
                )
        );

        // No note? :(
        if ($smcFunc['db_num_rows']($result) == 0)
                fatal_lang_error('note_no_exist');

        // Then grab the note.
        $tnote = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

        // Not allowed to edit *this* note?
        if (!allowedTo('bt_edit_note_any') && (allowedTo('bt_edit_note_own') && $context['user']['id'] != $tnote['id_author']))
                fatal_lang_error('note_edit_notyours');

        // Need Subs-Post.php
        include($sourcedir . '/Subs-Post.php');
        include($sourcedir . '/FXTracker/Subs-Edit.php');

        // Preparse the message.
        preparsecode($pnote['text']);
	$pnote['text'] = preg_replace('~<br ?/?' . '>~i', "\n", $pnote['text']);

        // And save it...
        $smcFunc['db_query']('', '
                UPDATE {db_prefix}bugtracker_notes
                SET note = {string:note}
                WHERE id_note = {int:id}',
                array(
                        'id' => $tnote['id_note'],
                        'note' => $pnote['text'],
                )
        );
        
        // And log the action.
        fxt_logAction('edit_note', $tnote['id_entry'], 0, $tnote['real_name'], $tnote['id_note']);

        redirectexit($scripturl . '?action=bugtracker;sa=view;entry=' . $tnote['id_entry']);
}

/**
 * Shows the form for adding a new entry.
 * No parameters - called by action.
 */
function BugTrackerNewEntry()
{
	global $context, $smcFunc, $txt, $scripturl, $sourcedir;

	// Are we allowed to create new entries?
	isAllowedTo('bt_add');

	// Load the project data.
	$result = $smcFunc['db_query']('', '
		SELECT
			id_project, title
		FROM {db_prefix}bugtracker_projects
		WHERE id_project = {int:project}',
		array(
			'project' => $_GET['project']
		)
	);

	// Wait.... There is no project like this? Or there's more with the *same* ID? :O
	if ($smcFunc['db_num_rows']($result) == 0 || $smcFunc['db_num_rows']($result) > 1)
		fatal_lang_error('project_no_exist');

	// Load the template for this.
	loadTemplate('fxt/Edit');

	// So we have just one...
	$project = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	// Validate the stuff.
	$context['bugtracker']['project'] = array(
		'id' => (int) $project['id_project'],
		'name' => $project['title']
	);

	// We want the default SMF WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Some settings for it...
	$editorOptions = array(
		'id' => 'entry_desc',
		'value' => '',
		'height' => '275px',
		'width' => '100%',
		// XML preview.
		'preview_type' => 2,
	);
	create_control_richedit($editorOptions);

	// Store the ID.
	$context['post_box_name'] = $editorOptions['id'];

	// Set up the edit page.
	$context['btform'] = array(
		'entry_name' => '',
		'entry_status' => 'new',
		'entry_type' => 'issue',
		'entry_attention' => false,
		'entry_progress' => 0,

		'url' => $scripturl . '?action=bugtracker;sa=new2',

		'extra' => array(
			'is_fxt' => array(
				'type' => 'hidden',
				'name' => 'is_fxt',
				'defaultvalue' => true,
			),
			'entry_projectid' => array(
				'type' => 'hidden',
				'name' => 'entry_projectid',
				'defaultvalue' => $project['id_project'],
			)
		),
	);

	// Setup the page title...
	$context['page_title'] = $txt['new_entry'];

	// Set up the linktree, too...
	$context['linktree'][] = array(
		'name' => $project['title'],
		'url' => $scripturl . '?action=bugtracker;sa=projectindex;project=' . $project['id_project']
	);
	$context['linktree'][] = array(
		'name' => $txt['new_entry'],
		'url' => $scripturl . '?action=bugtracker;sa=new;project=' . $project['id_project']
	);

	// Then, set what template we should use!
	$context['sub_template'] = 'BugTrackerEdit';
}

/**
 * Saves a submitted new entry.
 * No parameters - called by action.
 */
function BugTrackerSubmitNewEntry()
{
	global $smcFunc, $context, $sourcedir, $scripturl, $txt, $modSettings;

	// Start with checking if we can add new stuff...
	isAllowedTo('bt_add');

	// Load Subs-Post.php, will need that!
	include($sourcedir . '/Subs-Post.php');
	require($sourcedir . '/FXTracker/Subs-Edit.php');

	// Then, is the required is_fxt POST set?
	if (!isset($_POST['is_fxt']) || empty($_POST['is_fxt']))
		fatal_lang_error('save_failed');

	// Pour over these variables, so they can be altered and done with.
	$entry = array(
		'title' => $_POST['entry_title'],
		'type' => $_POST['entry_type'],
		'description' => $_POST['entry_desc'],
		'mark' => $_POST['entry_mark'],
		'attention' => !empty($_POST['entry_attention']),
		'progress' => $_POST['entry_progress'],
		'project' => $_POST['entry_projectid']
	);

	$context['errors_occured'] = array();

	// Check if the title, the type or the description are empty.
	if (empty($entry['title']))
		$context['errors_occured']['title'] = $txt['no_title'];

	// Type...
	if (empty($entry['type']) || !in_array($entry['type'], array('issue', 'feature')))
		$context['errors_occured']['type'] = $txt['no_type'];

	// And description.
	if (empty($entry['description']))
		$context['errors_occured'][] = $txt['no_description'];

	// Are we submitting a valid mark? (rare condition)
	if (!in_array($entry['mark'], array('new', 'wip', 'done', 'reject')))
		fatal_lang_error('save_failed');

	// Check if the project exists.
	$result = $smcFunc['db_query']('', '
		SELECT
			id_project, title
		FROM {db_prefix}bugtracker_projects
		WHERE id_project = {int:project}',
		array(
			'project' => $entry['project'],
		)
	);

	// The "real" check ;)
	if ($smcFunc['db_num_rows']($result) == 0)
		fatal_lang_error('project_no_exist');

	$pdata = $smcFunc['db_fetch_assoc']($result);

	$smcFunc['db_free_result']($result);

	// Preparse the message.
	preparsecode($entry['description']);
	$entry['description'] = preg_replace('~<br ?/?' . '>~i', "\n", $entry['description']);

	if (!empty($context['errors_occured']))
	{
		// We want the default SMF WYSIWYG editor.
		require_once($sourcedir . '/Subs-Editor.php');
		
		un_preparsecode($entry['description']);

		// Some settings for it...
		$editorOptions = array(
			'id' => 'entry_desc',
			'value' => $entry['description'],
			'height' => '175px',
			'width' => '100%',
			// XML preview.
			'preview_type' => 2,
		);
		create_control_richedit($editorOptions);

		// Store the ID.
		$context['post_box_name'] = $editorOptions['id'];

		// Set up the edit page.
		$context['btform'] = array(
			'entry_name' => $entry['title'],
			'entry_status' => $entry['mark'],
			'entry_type' => $entry['type'],
			'entry_attention' => $entry['attention'],
			'entry_progress' => $entry['progress'],

			'url' => $scripturl . '?action=bugtracker;sa=new2',

			'extra' => array(
				'is_fxt' => array(
					'type' => 'hidden',
					'name' => 'is_fxt',
					'defaultvalue' => true,
				),
				'entry_projectid' => array(
					'type' => 'hidden',
					'name' => 'entry_projectid',
					'defaultvalue' => $pdata['id_project'],
				)
			),
		);

		// Setup the page title...
		$context['page_title'] = $txt['new_entry'];

		// Set up the linktree, too...
		$context['linktree'][] = array(
			'name' => $pdata['title'],
			'url' => $scripturl . '?action=bugtracker;sa=projectindex;project=' . $pdata['id_project']
		);
		$context['linktree'][] = array(
			'name' => $txt['new_entry'],
			'url' => $scripturl . '?action=bugtracker;sa=new;project=' . $pdata['id_project']
		);

		// Then, set what template we should use!
		loadTemplate('fxt/Edit');
		$context['sub_template'] = 'BugTrackerEdit';
	}
	else
	{
		// Okay, lets prepare the entry data itself! Create an array of the available types.
		$fentry = array(
			'title' => $smcFunc['htmlspecialchars']($entry['title'], ENT_QUOTES),
			'type' => $smcFunc['strtolower']($entry['type']),
			'description' => $entry['description'],
			'mark' => $smcFunc['strtolower']($entry['mark']),
			'attention' => (int) $entry['attention'],
			'progress' => (int) $entry['progress'],
			'project' => (int) $entry['project'],
		);

		// Get the time.
		$postedtime = time();
		
		// Insert the first note first.
		$smcFunc['db_insert']('insert',
			'{db_prefix}bugtracker_notes',
			array(
				'id_entry' => 'int',
				'id_author' => 'int',
				'posted_time' => 'int',
				'note' => 'string',
			),
			array(
				0,
				$context['user']['id'],
				$postedtime,
				$fentry['description'],
			),
			array());
		
		// Get the note ID.
		$note = $smcFunc['db_insert_id']('{db_prefix}bugtracker_notes', 'id_note');

		// Assuming we have everything ready now, lets do this! Insert this stuff first.
		$smcFunc['db_insert']('insert',
			'{db_prefix}bugtracker_entries',
			array(
				'id_first_note' => 'int',
				'id_last_note' => 'int',
				'id_project' => 'int',
				'title' => 'string',
				'status' => 'string',
				'type' => 'string',
				'attention' => 'int',
				'progress' => 'int'
			),
			array(
				$note,
				$note,
				$fentry['project'],
				$fentry['title'],
				$fentry['mark'],
				$fentry['type'],
				$fentry['attention'],
				$fentry['progress']
			),
			array()
		);

		// Grab the ID of the entry just inserted.
		$entryid = $smcFunc['db_insert_id']('{db_prefix}bugtracker_entries', 'id');
		
		// Update the note with the correct entry ID...
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}bugtracker_notes
			SET id_entry = {int:entry}
			WHERE id_note = {int:note}',
			array(
				'entry' => $entryid,
				'note' => $note,
			));
		
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
				$entryid,
				$note
			),
			array());

		$smcFunc['db_query']('', '
		        UPDATE {db_prefix}bugtracker_projects
		        SET
				num_entries = num_entries + 1
				' . (in_array($fentry['mark'], array('new', 'wip')) ? ', num_open_entries = num_open_entries + 1' : '') . ',
				id_last_entry = {int:led}
		        WHERE id_project = {int:pid}',
		        array(
		                'pid' => $fentry['project'],
		                'led' => $entryid
		        ));

		// Should we create a topic?
		if (!empty($modSettings['bt_posttopic_enable']) && !empty($modSettings['bt_posttopic_message']) && !empty($modSettings['bt_posttopic_board']))
		{
			switch ($modSettings['bt_posttopic_prefix'])
			{
				case 'type1':
					$subject = '[' . $txt['bt_types'][$fentry['type']] . '] ';
					break;
				case 'type2':
					$subject = $txt['bt_types'][$fentry['type']] . ': ';
					break;
				case 'type3':
					$subject = strtoupper($txt['bt_types'][$fentry['type']]) . ': ';
					break;
				default:
					$subject = '';
			}

			$subject .= $fentry['title'];

			$link = $scripturl . '?action=bugtracker;sa=view;entry=' . $entryid;
			$body = sprintf($modSettings['bt_posttopic_message'], $link, $context['user']['name'], $fentry['description']);

			$msgOptions = array(
				'subject' => $subject,
				'body' => $body,
			);

			$topicOptions = array(
				'board' => $modSettings['bt_posttopic_board'],
				'mark_as_read' => true,
				'is_approved' => true,
				'lock_mode' => (int) !empty($modSettings['bt_posttopic_lock'])
			);

			$posterOptions = array(
				'id' => 0,
				'name' => 'FXTracker',
				'ip' => '127.0.0.1',
				'email' => 'info@fxtracker',
			);

			createPost(
				$msgOptions,
				$topicOptions,
				$posterOptions
			);
		}
		
		// Log this action!
		fxt_logAction('create', $entryid);

		// Then we're ready to opt-out!
		redirectexit($scripturl . '?action=bugtracker;sa=view;entry=' . $entryid . ';new=' . $postedtime);
	}
}

/**
 * Shows the form for adding a new note.
 * No parameters - called by action.
 */
function BugTrackerAddNote()
{
        global $context, $smcFunc, $sourcedir, $txt, $scripturl, $modSettings;

	// Notes disabled? :(
	if (empty($modSettings['bt_enable_notes']))
		fatal_lang_error('notes_disabled');

	// Or just the Add Note screen?
	if (!empty($modSettings['bt_quicknote_primary']))
		fatal_lang_error('addnote_disabled');

        // Is the entry set?
        if (empty($_GET['entry']))
                fatal_lang_error('entry_no_exist', false);

        // Grab this entry, check if it exists.
        $result = $smcFunc['db_query']('', '
                SELECT
                        e.id_entry, e.title,
			fn.id_author
                FROM {db_prefix}bugtracker_entries AS e
			INNER JOIN {db_prefix}bugtracker_notes AS fn ON (e.id_first_note = fn.id_note)
                WHERE e.id_entry = {int:id}',
                array(
                        'id' => (int) $_GET['entry'],
                )
        );

        // No entry? No note either!!
        if ($smcFunc['db_num_rows']($result) == 0)
                fatal_lang_error('entry_no_exists', false);

        // Data fetching, please.
        $data = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

        // Are we, like, allowed to add notes to any entry or just our own?
        if (!allowedTo('bt_add_note_any') && (allowedTo('bt_add_note_own') && $context['user']['id'] != $data['tracker']))
                fatal_lang_error('cannot_add_note', false);

        // Okay. Set up the $context variable.
        $context['bugtracker']['note'] = array(
                'id' => $data['id_entry'],
                'name' => $data['title'],
        );

        // We want the default SMF WYSIWYG editor and Subs-Post.php to make stuff look SMF-ish.
        require_once($sourcedir . '/Subs-Editor.php');

        // Some settings for it...
        $editorOptions = array(
                'id' => 'note_text',
                'value' => '',
                'height' => '175px',
                'width' => '100%',
                // XML preview.
                'preview_type' => 2,
        );
        create_control_richedit($editorOptions);

        // Store the ID. Might need it later on.
	$context['post_box_name'] = $editorOptions['id'];

        // Page title, too.
        $context['page_title'] = $txt['add_note'];

        // And the linktree, of course.
        $context['linktree'][] = array(
                'name' => $txt['add_note'],
                'url' => $scripturl . '?action=bugtracker;sa=addnote;entry=' . $data['id_entry'],
        );

        // Set the sub template.
        loadTemplate('fxt/Notes');
        $context['sub_template'] = 'TrackerAddNote';
}

/**
 * Saves a submitted note.
 * No parameters - called by action.
 */
function BugTrackerAddNote2()
{
        global $context, $smcFunc, $sourcedir, $scripturl, $txt, $modSettings;

	// Can't add notes if that's disabled
	if (empty($modSettings['bt_enable_notes']))
		fatal_lang_error('notes_disabled');

        // Okay. See if we have submitted the data!
        if (!isset($_POST['is_fxt']) || $_POST['is_fxt'] != true)
                fatal_lang_error('note_save_failed');

	// Oh noes, no entry?
	if (!isset($_POST['entry_id']) || empty($_POST['entry_id']))
		fatal_lang_error('note_save_failed');

        // Description empty?
        if (empty($_POST['note_text']))
                fatal_lang_error('note_empty');

        $note = array(
                'id' => (int) $_POST['entry_id'],
                'note' => $_POST['note_text'],
        );

        // Try to load the entry.
        $result = $smcFunc['db_query']('', '
                SELECT
                        e.id_entry, e.title,
			fn.id_author, m.real_name
                FROM {db_prefix}bugtracker_entries AS e
			INNER JOIN {db_prefix}bugtracker_notes AS fn ON (e.id_first_note = fn.id_note)
			INNER JOIN {db_prefix}members AS m ON (fn.id_author = m.id_member)
                WHERE e.id_entry = {int:id}',
                array(
                        'id' => $note['id'],
                )
        );

        // None? :(
        if ($smcFunc['db_num_rows']($result) == 0)
                fatal_lang_error('entry_no_exist');

        // Then, fetch the data.
        $data = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

        // Are we allowed to add notes to any entry or just our own?
        if (!allowedTo('bt_add_note_any') && (allowedTo('bt_add_note_own') && $context['user']['id'] != $data['tracker']))
                fatal_lang_error('cannot_add_note', false);

        // Need Subs-Post.php
        include($sourcedir . '/Subs-Post.php');
        include($sourcedir . '/FXTracker/Subs-Edit.php');

        // Then, preparse the note.
	$note['note'] = $smcFunc['htmlspecialchars']($note['note']);
        preparsecode($note['note']);

	// Get the time.
	$postedtime = time();

        // And save!
        $smcFunc['db_insert']('insert',
		'{db_prefix}bugtracker_notes',
		array(
			'id_author' => 'int',
			'id_entry' => 'int',
			'posted_time' => 'int',
                        'note' => 'string'
		),
		array(
			$context['user']['id'],
			$note['id'],
			$postedtime,
			$note['note']
		),
		array()
	);
	
	$noteid = $smcFunc['db_insert_id']('{db_prefix}bugtracker_notes', 'id');
	
	// Send out an alert.
	if ($context['user']['id'] != $data['id_author'] && !empty($modSettings['bt_enable_alerts']))
	{
		$alert_rows = array(
			'alert_time' => time(),
			'id_member' => $data['id_author'],
			'id_member_started' => $context['user']['id'],
			'member_name' => $context['user']['name'],
			'content_type' => 'entry',
			'content_id' => $data['id_entry'],
			'content_action' => 'reply',
			'is_read' => 0,
			'extra' => serialize(array(
				'content_link' => $scripturl . '?action=bugtracker;sa=view;entry=' . $data['id_entry'] . '#note_' . $note['id'],
			)),
		);
		updateMemberData($data['id_author'], array('alerts' => '+'));
		
		$smcFunc['db_insert']('',
			'{db_prefix}user_alerts',
			array('alert_time' => 'int', 'id_member' => 'int', 'id_member_started' => 'int', 'member_name' => 'string',
				'content_type' => 'string', 'content_id' => 'int', 'content_action' => 'string', 'is_read' => 'int', 'extra' => 'string'),
			$alert_rows,
			array()
		);
	}
        
        fxt_logAction('reply', $note['id'], 0, $noteid);
	
	// And update the last note ID for this entry.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}bugtracker_entries
		SET id_last_note = {int:lni}
		WHERE id_entry = {int:eid}',
		array(
			'eid' => $note['id'],
			'lni' => $noteid
		));

        // And done!
        redirectexit($scripturl . '?action=bugtracker;sa=view;entry=' . $note['id']);
}

/**
 * Removes entries.
 * No parameters - called by action.
 */
function BugTrackerRemoveEntry()
{
	global $context, $smcFunc, $scripturl, $sourcedir;

	if (empty($_GET['entry']) || !is_numeric($_GET['entry']))
		fatal_lang_error('entry_no_exist');

	// Then try to load the issue data.
	$result = $smcFunc['db_query']('', '
		SELECT
			id_entry, id_project, in_trash
		FROM {db_prefix}bugtracker_entries
		WHERE id_entry = {int:entry}',
		array(
			'entry' => (int)$_GET['entry']
		));

	// None? Or more then one?
	if ($smcFunc['db_num_rows']($result) == 0)
		fatal_lang_error('entry_no_exist');

	// Fetch the data.
	$data = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	// Hmm, okay. Are we allowed to remove this entry?
	require($sourcedir . '/FXTracker/Subs-Edit.php');
	if (!fxt_deleteEntry($data['id_entry']))
		fatal_lang_error('remove_entry_noaccess', false);
	else
		redirectexit($scripturl . '?action=bugtracker' . ($data['in_trash'] == 0 ? ';sa=view;entry=' . $data['id_entry'] .';trashed' : ';sa=projectindex;project=' . $data['id_project'] . ';deleted'));
}

/**
 * Removes notes.
 * No parameters - called by action.
 */
function BugTrackerRemoveNote()
{
	global $smcFunc, $context, $scripturl, $sourcedir;

	// Try to grab the note...
	$result = $smcFunc['db_query']('', '
                SELECT
                        n.id_note, n.id_author, n.id_entry,
			m.real_name
		FROM {db_prefix}bugtracker_notes AS n
			INNER JOIN {db_prefix}members AS m ON (m.id_member = n.id_author)
                WHERE n.id_note = {int:noteid}',
		array(
		      'noteid' => $_GET['note']
		));

	// None? That sucks...
	if ($smcFunc['db_num_rows']($result) == 0)
		fatal_lang_error('note_delete_failed');

	// Check if we can remove it -- wait, we need the data for that.
	$note = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	// Check if we can remove it, now.
	if (allowedTo('bt_remove_note_any') || (allowedTo('bt_remove_note_own') && $context['user']['id'] == $note['id_author']))
	{
		$smcFunc['db_query']('', '
                        DELETE
                                FROM {db_prefix}bugtracker_notes
                        WHERE id_note = {int:id}',
                        array(
                                'id' => $note['id_note']
                        ));
                        
                // Log the action.
                require($sourcedir . '/FXTracker/Subs-Edit.php');
                fxt_logAction('delete_note', $note['entryid'], 0, $note['real_name']);
		
		// And update the latest note ID for this entry.
		$result = $smcFunc['db_query']('', '
			SELECT id_note
			FROM {db_prefix}bugtracker_notes
			WHERE id_entry = {int:entry}
			ORDER BY id_note DESC
			LIMIT 1',
			array(
				'entry' => $note['id_entry']
			));
		
		list ($lnid) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);
		
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}bugtracker_entries
			SET id_last_note = {int:lnid}
			WHERE id_entry = {int:entry}',
			array(
				'lnid' => $lnid,
				'entry' => $note['id_entry']
			));

		// And redirect back to the entry.
		redirectexit($scripturl . '?action=bugtracker;sa=view;entry=' . $note['id_entry']);
	}
	else
		fatal_lang_error('note_delete_notyours');
}

/**
 * Restores entries.
 * No parameters - called by action.
 */
function BugTrackerRestoreEntry()
{
	global $context, $smcFunc, $scripturl, $sourcedir;

	if (empty($_GET['entry']) || !is_numeric($_GET['entry']))
		fatal_lang_error('entry_no_exist');

	// Then try to load the issue data.
	$result = $smcFunc['db_query']('', '
		SELECT
			id_entry
		FROM {db_prefix}bugtracker_entries
		WHERE id_entry = {int:entry}',
		array(
			'entry' => (int)$_GET['entry']
		));

	// None? Or more then one?
	if ($smcFunc['db_num_rows']($result) == 0)
		fatal_lang_error('entry_no_exist');

	// Fetch the data.
	$data = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	// Hmm, okay. Are we allowed to restore this entry?
	require($sourcedir . '/FXTracker/Subs-Edit.php');
	if (!fxt_restoreEntry($data['id_entry']))
		fatal_lang_error('restore_failed', false);
	else
		redirectexit($scripturl . '?action=bugtracker;sa=view;entry=' . $data['id_entry'] . ';restored');
}
