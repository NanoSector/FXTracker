<?php
/* ============= Buttons and titles ============= */
$txt['bt_acp_button'] = 'FXTracker';
global $settings;
$txt['fxt_logo'] = '<img src="' . $settings['images_url'] . '/bugtracker/btlogo.png" alt="">';
$txt['bt_acp_projects'] = 'Manage Projects';
$txt['bt_acp_settings'] = 'Settings';
$txt['bt_acp_settings_title'] = 'FXTracker Settings';
$txt['bt_acp_settings_desc'] = 'From this screen, you can toggle various general settings of the bug tracker.';
$txt['bt_acp_addsettings'] = 'Additional Features';
$txt['bt_acp_addsettings_desc'] = 'From these screens, you can enable and tweak additional features FXTracker has to offer.';

// Project Manager
$txt['project_id'] = '#';
$txt['project_name'] = 'Project Name';
$txt['project_issues'] = 'Issues';
$txt['project_features'] = 'Features';
$txt['project_desc'] = 'Project Description';
$txt['project_delete'] = 'Delete';
$txt['project_really_delete'] = 'Really delete this project, including all it\'s entries and notes? This cannot be undone, and entries won\'t be moved to the trash can!';
// End Project Manager

$txt['fxt_ver'] = 'FXTracker Release 0.1 Alpha';
$txt['fxt_recount_stats'] = 'Recount all bug tracker stats';

$txt['fxt_general'] = 'General Settings';
$txt['bt_enable'] = 'Enable the Bug Tracker
<div class="smalltext">Turning this off will deny anyone access to the tracker, even administrators; you can enable it again later.</div>';
$txt['bt_show_button_important'] = 'Show the number of entries that require attention in the menu button
<div class="smalltext">The first page load may be slow, as the data is being processed and cached.</div>';
$txt['bt_show_button_advanced'] = 'Show the entries that require attention as sub-menus';

$txt['fxt_home'] = 'Home Page';
$txt['bt_num_latest'] = 'The number of Latest Entries to show
<div class="smalltext">Set to 0 to disable this feature</div>';
$txt['bt_show_attention_home'] = 'Show entries requiring attention on the Home page';

$txt['fxt_ppage'] = 'Project Pages';
$txt['bt_hide_done_button'] = 'Hide the "View Resolved Entries" button from Project view
<div class="smalltext">Please note that this will also show all the Resolved entries in the project index!</div>';
$txt['bt_hide_reject_button'] = 'Hide the "View Rejected Entries" button from Project view
<div class="smalltext">Please note that this will also show all the Rejected entries in the project index!</div>';
$txt['bt_show_description_ppage'] = 'Show the project\'s description on the Project Index.';

$txt['fxt_notes_desc'] = 'Allows users to post notes to entries. Changing this setting does <strong>not</strong> affect existing notes!';
$txt['bt_enable_notes'] = 'Enable Notes';
$txt['bt_enable_alerts'] = 'Enable Alerts for Notes';
$txt['bt_alerts_help'] = 'New in SMF 2.1 is the Alerts system. FXTracker can use this system to send out an alert to the creator of the entry any time a user replies to it.';
$txt['bt_quicknote'] = 'Enable Quick Note';
$txt['bt_quicknote_help'] = 'Quick Note allows users to easily react on an entry without having to load a new page.';
$txt['bt_quicknote_primary'] = 'Make Quick Note the primary way to post notes';
$txt['bt_quicknote_primary_help'] = 'Quick Note must be enabled, if it is not it will be enabled. Sets Quick Note as the only way to post notes, thus disabling the regular Add Note screen.';
$txt['bt_notes_order'] = 'Order in which Notes are displayed';
$txt['bt_no_asc'] = 'Ascending (first to latest)';
$txt['bt_no_desc'] = 'Descending (latest to first)';

$txt['bt_entries'] = 'Entries';
$txt['bt_entry_progress_allow'] = 'Allow progress to be set on entries';
$txt['bt_entry_progress_steps'] = 'Steps in which Progress can be set';
$txt['bt_eps_per5'] = 'Per 5 (5, 10, 15, etc.)';
$txt['bt_eps_per10'] = 'Per 10 (10, 20, 30, etc.)';

// Filter settings
$txt['bt_filter_desc'] = 'The filter system allows users to find entries in a project based on various filters.';
$txt['bt_enable_filter'] = 'Enable the filter system';
$txt['bt_filter_search'] = 'Search mode to use';
$txt['bt_filter_search_help'] = 'This changes the way searches are performed on entries. Fulltext search is MySQL only and will NOT work correctly otherwise.';
$txt['bt_simple_search'] = 'Simple (faster but less reliable)';
$txt['bt_fulltext_search'] = 'Fulltext (slower, more reliable but MySQL only)';
$txt['bt_filter_search_boolean_mode'] = 'Allow boolean mode for searches (fulltext search mode only)';

/**** PROJECT MANAGER ****/
$txt['fxt_pmanager'] = 'FXTracker Project Manager';
$txt['no_projects'] = 'There are no projects; <a href="%s">add one</a> and start tracking!';
$txt['bt_add_project'] = 'Add New Project';
$txt['bugtracker_projects_desc'] = 'In this screen, you can edit, add and remove projects from the Bug Tracker. To edit a project, select its name. To remove one, select the red icon at the end of its row. To add one, select the "Add New Project" button.<br />
<strong>Please note:</strong> The number of issues and features shown here is <em>NOT</em> equal to the numbers shown on the Tracker Index. The Tracker Index shows the number of <em>open</em> entries, while this screen shows the <em>total</em> number of entries.';
$txt['p_save_failed'] = 'Failed to save the project.';
$txt['p_save_success'] = 'Project has been saved!';
$txt['pedit_title'] = 'Editing project "%s"';
$txt['padd_title'] = 'Adding new Project';
$txt['project_id'] = '#';
$txt['project_name'] = 'Project Name';
$txt['project_issues'] = 'Issues';
$txt['project_features'] = 'Features';
$txt['project_desc'] = 'Project Description';
$txt['project_actions'] = 'Actions';
$txt['project_delete'] = 'Delete this project';
$txt['project_edit'] = 'Edit this project';
$txt['project_really_delete'] = 'Really delete this project, including all its entries and notes? This cannot be undone, and entries won\'t be moved to the trash can!';
$txt['pedit_no_title'] = 'You didn\'t give this project a name!';
$txt['pedit_no_desc'] = 'This project is description-less!';
$txt['original_values'] = 'The original values were restored.';
$txt['oneormoreerrors'] = 'One or more errors occured while saving this project';

$txt['pedit_submit'] = 'Sumbit Changes';

$txt['add_settings_info'] = 'In this screen you can enable various additional features for FXTracker. Once a feature is enabled, you can change various settings for it.';

$txt['bt_posttopic_enable'] = 'Post topic when new entry is posted';
$txt['bt_posttopic_help'] = 'This function posts a topic in the specified board, containing the message set below.';
$txt['bt_posttopic_board'] = 'Place the topic in (please select only one!)';
$txt['bt_posttopic_board_limit'] = 'You cannot select more than one board to post topics in. Please select only one and try again.';
$txt['bt_posttopic_prefix'] = 'Set the prefix as';
$txt['bt_posttopic_prefix_none'] = 'Do not set a prefix';
$txt['bt_posttopic_lock'] = 'Lock topic after posting';
$txt['bt_posttopic_message'] = 'Topic message';
$txt['bt_posttopic_message_help'] = 'Specify a message to put in the topic here. Use %1$s for the link to the entry, %2$s for the author\'s username and %3$s for the entry description. You can use BBCode.';
$txt['fxt_posttopic_info'] = 'This will post a topic in the selected board. The topic will <strong>not</strong> be updated when the entry is edited, nor when a new note is made to the entry, and <strong>neither when the type is changed</strong>.';

$txt['bt_mark_automatic_unimportant'] = 'Automatically mark an entry as unimportant when it gets marked as Completed or Resolved';
$txt['bt_mark_automatic_completed'] = 'Automatically mark an entry as Completed when it hits 100% progress';
