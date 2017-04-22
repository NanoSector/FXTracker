<?php

/* FXTracker Language File - English */

/* ============= General strings ============= */
$txt['bugtracker'] = 'Bug Tracker';
$txt['project'] = 'Project';
$txt['type'] = 'Type';
$txt['feature'] = 'Feature';
$txt['issue'] = 'Issue';
$txt['na'] = 'N/A';
$txt['entries'] = 'Entries';

/* ============= Admin panel ============= */
$txt['bt_acp_button'] = 'FXTracker';
$txt['bt_acp_projects'] = 'Manage Projects';
$txt['bt_acp_settings'] = 'Settings';
$txt['bt_acp_settings_title'] = 'FXTracker Settings';
$txt['bt_acp_addsettings'] = 'Additional Features';


/* ============= Home page ============= */
// Welcome.
$txt['welcome_to_fxt'] = 'Welcome to FXTracker!';
$txt['welcome_to_fxt_desc'] = 'Thank you for installing FXTracker. We hope you enjoy using it as much as we enjoyed creating it.<br />
Here are some steps to get you started:';
$txt['w_add_proj'] = 'Add a new project';
$txt['w_settings'] = 'Adjust the settings for FXTracker';
$txt['w_features'] = 'Enable or disable additional features';
$txt['w_note'] = 'Happy tracking!';

// General.
$txt['bt_index'] = 'Tracker Index';
$txt['bt_latest'] = 'Latest Entries';
$txt['latest_issues'] = 'Latest Issues';
$txt['latest_features'] = 'Latest Features';
$txt['projects'] = 'Projects';

// Info Centre.
$txt['info_centre'] = 'Info Center';
$txt['total_entries_chart'] = 'Total entries per status (%d entries total)';
$txt['total_entries'] = 'Total entries';
$txt['total_projects'] = 'Total projects:';
$txt['total_issues'] = 'Total issues:';
$txt['total_features'] = 'Total features:';
$txt['total_attention'] = 'Total requiring attention:';

// No data messages.
$txt['no_projects'] = 'There are no projects.';
$txt['no_latest_entries'] = 'There are no latest entries.';
$txt['no_items_attention'] = 'There are no important entries.';


/* ============= Quick Moderate ============= */
$txt['select_action'] = 'Select an action...';
$txt['move_to'] = 'Move entry';
$txt['move_dr'] = 'Move selected to';
$txt['qm_restore'] = 'Restore selected';
$txt['quick_progress_up'] = 'Quickly increase progress';
$txt['quick_progress_down'] = 'Quickly decrease progress';

// Errors
$txt['cant_progress'] = 'Changing the progress of the entries failed.';

/* ============= Project display ============= */
$txt['no_items'] = 'There are no entries to display.';
$txt['issues'] = '%s issues';
$txt['features'] = '%s features';
$txt['items_attention'] = 'Item(s) requiring attention';
$txt['view_all_lc'] = 'view all';
$txt['view_all'] = 'View all of kind "%s"';
$txt['tracked_by_guest'] = 'Tracked by Guest on %s';
$txt['tracked_by_user'] = 'Tracked by <a href="%2$s">%3$s</a> on %1$s';
$txt['tracked_by_guest_notime'] = 'Tracked by Guest';
$txt['tracked_by_user_notime'] = 'Tracked by <a href="%1$s">%2$s</a>';
$txt['tracked_by_user_notime_nolink'] = 'Tracked by %s';
$txt['entry_count'] = '%1$s entries<br/>
%2$s are open';
$txt['last_entry_on_by'] = '<strong>Last entry:</strong> %1$s<br />
by %2$s on %3$s';

// Errors.
$txt['project_no_exist'] = 'The requested project does not exist (anymore).';

// Notices.
$txt['no_entries_in_project'] = 'There are no entries in this project.';

// Filters.
$txt['filter'] = 'Filter';
$txt['filter_help'] = 'The filter system in FXTracker allows you to only show entries meeting the specified criteria. Check the checkboxes of the items you want to show in the current project, click Submit and FXTracker will gather every entry meeting the criteria for you.<br><br>

The filter system allows you to keep a filter applied for the rest of the browsing session. If you decide to use this functionality, check the option when submitting the form.<br>
If you want to disable this functionality, either uncheck the option to submit the form again or click on Reset filters.';
$txt['filter_desc'] = 'Mark the items you want to show.';
$txt['filter_results'] = 'Results for the selected filters:';
$txt['filter_attention'] = 'Only entries requiring attention';
$txt['filter_trash'] = 'Include entries in the trash';
$txt['nothing_set'] = 'You need to at least set one status and type.';
$txt['filter_err_reset'] = 'Your filters have been reset.';
$txt['filter_reset'] = 'Reset filters';
$txt['simple_filter_desc'] = 'Alphanumeric characters allowed. You can search by issue ID or by title.';
$txt['fulltext_filter_desc'] = 'All characters except single quotes allowed. You can search by issue ID, title or body text.';
$txt['keep_filter'] = 'Keep this filter during this browsing session';
$txt['filter_advanced_search'] = 'Use advanced search';
$txt['filter_boolean_mode'] = 'This allows you to perform advanced searches with operators, and may return more results.<br /><br />

Possible operators:<br />
+: AND<br />
-: NOT<br />
*: Part of<br />
(none): OR<br />
Every word can hold an operator.<br />
You can find more operators and their description <a href="http://dev.mysql.com/doc/refman/5.1/en/fulltext-boolean.html" target="_BLANK">here</a>.<br /><br />

Examples:<br />
Three entries exist: "FXTracker Test", "FXTracker Bug" and "Bug Tracker Test"<br /><br />

Scenario 1:<br />
Search string: "Test"<br />
Results: "FXTracker Test" and "Bug Tracker Test"<br />
Why: "FXTracker Bug" does not contain "Test"<br /><br />

Scenario 2:<br />
Search string: "Test FXTracker" (Test OR FXTracker)<br />
Results: "FXTracker Test", "FXTracker Bug" and "Bug Tracker Test"<br />
Why: "FXTracker Test" and "Bug Tracker Test" contain "Test", and "FXTracker Bug" contains "FXTracker"<br /><br />

Scenario 3:<br />
Search string: "Test -FXTracker" (Test AND NOT FXTracker)<br />
Results: "Bug Tracker Test"<br />
Why: "FXTracker Test" and "FXTracker Bug" contain "FXTracker"<br /><br />

Scenario 4:<br />
Search string: "Test +FXTracker" (Test AND FXTracker)<br />
Results: "FXTracker Test"<br />
Why: it is the only entry containing both "Test" and "FXTracker")';

// Project details
$txt['project_details'] = 'Project Details';
$txt['stats'] = 'Statistics';
$txt['closed_entries'] = 'Closed entries:';
$txt['closed_entries_help'] = 'Closed entries are entries which are either marked as Resolved or Rejected.';
$txt['num_entries'] = 'Total entries:';
$txt['entries_remaining'] = 'Open entries remaining:';
$txt['entries_important'] = 'Of which important:';
$txt['entries_important_help'] = 'Important entries are those which are supposed to be urgent, and are shown on the home page when first opening the bug tracker. They usually indicate an issue which must be resolved urgently, such as a bug which breaks critical functionality.';
$txt['project_progress'] = 'Project progress:';

// Trash.
$txt['view_trash'] = 'Viewing trash can of project %s';
$txt['view_trash_noproj'] = 'Viewing trash can';
$txt['return_proj'] = 'Return to Project';
$txt['empty_trash'] = 'Empty Trash';
$txt['restore_all'] = 'Restore All';
$txt['view_trash_proj'] = 'Open Trash';
$txt['restore'] = 'Restore';
$txt['really_delete'] = 'Really delete this entry? This cannot be undone!';

// Trash - Success.
$txt['trash_moved'] = 'The entry has successfully been moved to the trash can.';
$txt['trash_deleted'] = 'The entry has successfully been deleted.';

// Trash - Errors.
$txt['remove_entry_noaccess'] = 'You do not have permission to remove this entry.';

/* ============= Entry display =============- */
$txt['entry_details'] = 'Entry details';
$txt['desc_left'] = '<strong>%s</strong> left the following details:';
$txt['shortdesc'] = 'Short Description';
$txt['description'] = 'Description';
$txt['tracker'] = 'Tracker';
$txt['added_on'] = 'Added';
$txt['last_updated'] = 'Last Updated';

// Sprintfs.
$txt['view_title'] = '#%s - Bug Tracker';
$txt['entrytitle'] = 'Entry no. #%d - %s';
$txt['created_by'] = 'started by %s';

$txt['go_notes'] = 'Go to notes';
$txt['go_actions'] = 'Go to action log';

$txt['editentry'] = 'Edit Entry';
$txt['removeentry'] = 'Remove Entry';

// Available types.
$txt['bt_types'] = array(
	'issue' => 'Issue',
	'feature' => 'Feature',
);

// Available statuses.
$txt['bt_statuses'] = array(
	'new' => 'New',
	'wip' => 'Work in Progress',
	'done' => 'Completed',
	'reject' => 'Rejected',
	'attention' => 'Important',
	'no_attention' => 'Not important',
);

// Status.
$txt['status'] = 'Status';
$txt['status_new'] = 'Unassigned';
$txt['status_wip'] = 'Work In Progress';
$txt['status_done'] = 'Resolved';
$txt['status_reject'] = 'Rejected';
$txt['status_attention'] = 'Important';
$txt['status_no_attention'] = 'Not Important';

// Errors.
$txt['no_entry_specified'] = 'The required entry ID is not set, there is no entry to load.';
$txt['entry_no_exist'] = 'The requested entry does not exist (anymore).';
$txt['entry_is_private'] = 'The requested entry is marked as private, and thus you can\'t see it.';

// Rare errors.
$txt['entry_no_project'] = 'The requested entry does not have an associated project. Please ask the administrator to repair the installation of FXTracker.';
$txt['entry_author_fail'] = 'The author data of the requested entry is either corrupt or doesn\'t exist.';

/* ============= Adding entries ============= */
$txt['new_entry'] = 'New entry';

// Errors.
$txt['add_entry_noaccess'] = 'You do not have permission to add new entries.';

/* ============= Editing entries ============= */
$txt['entry_edit'] = 'Edit entry';
$txt['entry_edit_lt'] = 'Editing entry "%s"';

// Marking entries.
$txt['mark_new'] = 'Mark as unassigned';
$txt['mark_wip'] = 'Mark as Work In Progress';
$txt['mark_done'] = 'Mark as resolved';
$txt['mark_reject'] = 'Mark as rejected';
$txt['mark_attention'] = 'Mark as important';
$txt['mark_attention_undo'] = 'Mark as not important';

// Moving entries
$txt['move'] = 'Move entry';

// Restoring entries.
$txt['restore'] = 'Restore';
$txt['entry_restored'] = 'The entry has successfully been restored.';

// restoring entries - Errors.
$txt['restore_failed'] = 'Restoring the selected entry/ies failed.';

// Form fields.
$txt['entry_title'] = 'Entry title';
$txt['entry_progress'] = 'Progress';
$txt['entry_progress_optional'] = 'Progress (optional)';
$txt['entry_type'] = 'Entry type';
$txt['entry_desc'] = 'Entry description';
$txt['entry_private'] = 'This entry is private';
$txt['entry_mark_optional'] = 'Mark this entry (optional)';
$txt['entry_submit'] = 'Submit';

$txt['entry_posted_in'] = 'This entry will be posted in <strong>%s</strong>';

// Errors.
$txt['save_failed'] = 'Failed to save the given data.';
$txt['no_title'] = 'You have not specified an entry title!';
$txt['no_description'] = 'You have not entered a description!';
$txt['no_type'] = 'You have not selected a type!';
$txt['entry_added'] = 'The entry has been successfully added!';
$txt['additional_options'] = 'Additional Options';
$txt['errors_occured'] = 'One or more errors occured, please review them below and try again.';

$txt['entry_unable_mark'] = 'You cannot mark this entry.';
$txt['entry_mark_failed'] = 'Failed to mark entry.';

$txt['no_such_project'] = 'There is no such project';
$txt['edit_entry_noaccess'] = 'You do not have permission to edit this entry.';
$txt['edit_entry_else_noaccess'] = 'You do not have permission to edit someone else\'s entry.';

$txt['entry_unable_move'] = 'You cannot move this entry.';
$txt['entry_move_failed'] = 'Failed to move this entry.';

/* ============= Notes - General ============= */
$txt['notes'] = 'Notes';
$txt['add_note'] = 'Add note';
$txt['quick_note'] = 'Quick Note';

// Errors.
$txt['note_no_exist'] = 'This note doesn\'t exist (anymore).';
$txt['no_notes'] = 'There are no notes to display.';
$txt['cannot_add_note'] = 'You cannot add a note to this entry.';

/* ============= Notes - Display ============= */
// 1: user name, 2: date, 3: url to user profile
$txt['note_by'] = 'Note left by <a href="%3$s"><strong>%1$s</strong></a> on %2$s';
$txt['note_by_guest'] = 'Note left by <strong>Guest</strong> on %s';

/* ============= Edit Notes ============= */
$txt['edit_note'] = 'Edit note';

// Errors.
$txt['note_save_failed'] = 'An error occured while saving the note. Please try submitting it again.';
$txt['note_edit_notyours'] = 'You cannot edit someone else\'s notes.';
$txt['note_empty'] = 'You didn\'t enter a note!';

/* ============= Deleting notes ============= */
$txt['remove_note'] = 'Delete note';
$txt['really_delete_note'] = 'Really delete this note? This cannot be undone!';

// Errors.
$txt['note_delete_failed'] = 'An error occured while removing the note.';
$txt['note_delete_cannot'] = 'You are not permitted to remove this note.';
$txt['note_delete_notyours'] = 'You cannot remove someone else\'s notes.';

/* ============= Notes - Notifications ============= */
$txt['alert_entry_reply'] = '{member_link} replied to <a href="{content_link}">your entry</a> in the bug tracker';

/* ============= Action Log ============= */
$txt['actions'] = 'Action Log';
$txt['no_actions'] = 'There are no actions to display for this entry.';
$txt['time'] = 'Time';
$txt['action'] = 'Action';

// Actions.
$txt['action_create'] = 'Created this entry.';
$txt['action_edit'] = 'Edited this entry.';
$txt['action_mark'] = 'Changed status from %1$s to %2$s.';
$txt['action_reply'] = 'Posted <a href="#note_%1$s">a note</a> to this entry.';
$txt['action_edit_note'] = 'Edited <a href="#note_%2$s">a note</a> made by %1$s.';
$txt['action_delete_note'] = 'Deleted a note made by %1$s.';
$txt['action_trash'] = 'Put this entry in the trash.';
$txt['action_restore'] = 'Pulled this entry from the trash.';
$txt['action_move'] = 'Moved this entry from %1$s to %2$s';
$txt['action_changeprogress'] = 'Changed the progress from %1$s%% to %2$s%%';

/* ============= Permission groups ============= */
$txt['permissiongroup_fxt_classic'] = 'FXTracker Permissions';
$txt['permissiongroup_simple_fxt_simple'] = 'FXTracker Permissions';

/* ============= Permissions ============= */
$txt['permissionname_bt_view'] = 'View the bug tracker';
$txt['permissionname_bt_viewprivate'] = 'View private entries';
$txt['permissionname_bt_add'] = 'Add new entries';
$txt['permissionname_bt_edit_any'] = 'Edit any entry';
$txt['permissionname_bt_edit_own'] = 'Edit own entry';
$txt['permissionname_bt_remove_any'] = 'Remove any entry';
$txt['permissionname_bt_remove_own'] = 'Remove own entry';
$txt['permissionname_bt_restore_any'] = 'Restore any entry';
$txt['permissionname_bt_restore_own'] = 'Restore own entry';
$txt['permissionname_bt_move_any'] = 'Move any entry';
$txt['permissionname_bt_move_own'] = 'Move own entry';
$txt['permissionname_bt_mark_any'] = 'Mark any entry (master permission for marking)';
$txt['permissionname_bt_mark_own'] = 'Mark own entry (master permission for marking)';
$txt['permissionname_bt_mark_new_any'] = 'Mark any entry as new';
$txt['permissionname_bt_mark_new_own'] = 'Mark own entry as new';
$txt['permissionname_bt_mark_wip_any'] = 'Mark any entry as Work In Progress';
$txt['permissionname_bt_mark_wip_own'] = 'Mark own entry as Work In Progress';
$txt['permissionname_bt_mark_done_any'] = 'Mark any entry as resolved';
$txt['permissionname_bt_mark_done_own'] = 'Mark own entry as resolved';
$txt['permissionname_bt_mark_reject_any'] = 'Mark any entry as rejected';
$txt['permissionname_bt_mark_reject_own'] = 'Mark own entry as rejected';
$txt['permissionname_bt_mark_attention_any'] = 'Mark any entry as requiring attention';
$txt['permissionname_bt_mark_attention_own'] = 'Mark own entry as requiring attention';
$txt['permissionname_bt_add_note_any'] = 'Add a note to any entry';
$txt['permissionname_bt_add_note_own'] = 'Add a note to own entry';
$txt['permissionname_bt_edit_note_any'] = 'Edit any note';
$txt['permissionname_bt_edit_note_own'] = 'Edit own note';
$txt['permissionname_bt_remove_note_any'] = 'Remove any note';
$txt['permissionname_bt_remove_note_own'] = 'Remove own note';

/* ============= Error messages ============= */
$txt['bt_disabled'] = 'Sorry, the bug tracker is disabled at the moment. Contact the Administrator for more details.';
$txt['notes_disabled'] = 'Sorry, notes are disabled.';
$txt['addnote_disabled'] = 'Sorry, the regular Add Note screen is disabled. Please use Quick Note instead.';
$txt['cannot_bt_view'] = 'Sorry, but you do not have permission to view the bug tracker.';
$txt['cannot_bt_add'] = 'Sorry, you are not allowed to add new entries to the bug tracker.';
$txt['cannot_bt_add_note'] = 'Sorry, you are not allowed to add notes to entries in the bug tracker.';
$txt['cannot_do_guest'] = 'Sorry, you cannot edit, remove or mark entries if you are a guest. Please register and have the issue assigned to your account.';

// DO NOT CHANGE THIS STRING OR NO SUPPORT WILL BE GIVEN //
$txt['fxt_ver'] = 'FXTracker 0.1 Alpha';
global $fxt_copytext;
$fxt_copytext = '<div class="centertext smalltext">%s &copy; 2014 Yoshi2889</div>';

?>
