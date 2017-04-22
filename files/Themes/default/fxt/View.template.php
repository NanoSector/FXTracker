<?php

/* FXTracker View Template */

function template_TrackerView()
{
	global $context, $txt, $scripturl, $settings, $modSettings;

	// Is this new?
	if (isset($_GET['new']))
		echo '
	<div class="infobox">', $txt['entry_added'], '</div>';
	
	if (isset($_GET['restored']))
		echo '
	<div class="infobox">', $txt['entry_restored'], '</div>';
	
	if (isset($_GET['trashed']))
		echo '
	<div class="infobox">', $txt['trash_moved'], '</div>';
	
	// Allow people to jump to the notes?
	$button = array(
		'gotonotes' => array(
			'text' => 'go_notes',
			'url' => '#notes',
			'lang' => true
		),
		'gotoactions' => array(
			'text' => 'go_actions',
			'url' => '#actions',
			'lang' => true
		)
	);
	
	template_button_strip($button, 'left');

	$buttons = array();
	// Are we allowed to reply to this entry?
	if (($context['can_bt_add_note_any'] || $context['can_bt_add_note_own']) && !empty($modSettings['bt_enable_notes']) && empty($modSettings['bt_quicknote_primary']))
		$buttons['addnote'] = array(
			'text' => 'add_note',
			'url' => $scripturl . '?action=bugtracker;sa=addnote;entry=' . $context['bugtracker']['entry']['id'],
			'lang' => true,
			'active' => true,
		);
	
	// Are we allowed to edit this entry?
	if ($context['can_bt_edit_any'] || $context['can_bt_edit_own'])
		$buttons['edit'] = array(
			'text' => 'editentry',
			'url' => $scripturl . '?action=bugtracker;sa=edit;entry=' . $context['bugtracker']['entry']['id'],
			'lang' => true,
		);

	// Or allowed to remove it?
	if ($context['can_bt_remove_any'] || $context['can_bt_remove_own'])
		$buttons['remove'] = array(
			'text' => 'removeentry',
			'url' => $scripturl . '?action=bugtracker;sa=remove;entry=' . $context['bugtracker']['entry']['id'],
			'custom' => !empty($context['bugtracker']['entry']['in_trash']) ? 'onclick="return confirm(' . javascriptescape($txt['really_delete']) . ')"' : '',
			'lang' => true,
		);
		
	if (!empty($context['bugtracker']['entry']['in_trash']))
		$buttons['restore'] = array(
			'text' => 'restore',
			'url' => $scripturl . '?action=bugtracker;sa=restore;entry=' . $context['bugtracker']['entry']['id'],
			'lang' => true
		);
		
	if ($context['can_bt_mark_attention_any'] || $context['can_bt_mark_attention_own'])
	{
		$buttons['imp'] = array(
			'text' => $context['bugtracker']['entry']['attention'] ? 'mark_attention_undo' : 'mark_attention',
			'url' => $scripturl . '?action=bugtracker;sa=mark;as=attention' . ($context['bugtracker']['entry']['attention'] ? '_undo' : '') . ';entry=' . $context['bugtracker']['entry']['id'],
			'lang' => true
		);
		
		if ($context['bugtracker']['entry']['attention'])
			$buttons['imp']['mark_attention']['active'] = true;
	}
		
	template_button_strip($buttons, 'right');

	// Show the actual entry.
	echo '<br class="clear">
	<div id="entry_details" class="floatleft">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['entry_details'], '
			</h3>
		</div>
		<div class="information">
			<table class="fullwidth">';
		
	// Entry type
	echo '
				<tr>
					<td style="width:95px">
						<strong>', $txt['type'], ':</strong>
					</td>
					<td>
						<a href="', $scripturl, '?action=bugtracker;sa=viewtype;type=', $context['bugtracker']['entry']['type']['id'], '">
							', $context['bugtracker']['entry']['type']['title'], '
						</a>
					</td>
				</tr>';
				
	// Tracker.
	echo '
				<tr>
					<td style="width:95px">
						<strong>', $txt['tracker'], ':</strong>
					</td>
					<td>
						<a href="', $scripturl, '?action=profile;u=', $context['bugtracker']['entry']['tracker']['id'], '">
							', $context['bugtracker']['entry']['tracker']['name'], '</a> (', $context['bugtracker']['entry']['tracker']['group'], ')
					</td>
				</tr>';
					
	// Status.
	echo '
				<tr>
					<td style="width:95px">			
						<strong>', $txt['status'], ':</strong>
					</td>
					<td>';
	
	// Allowed to mark?
	if ($context['bt_can_mark'])
	{
		echo '
						<form action="', $scripturl, '?action=bugtracker;sa=qmod" method="post">
							<select name="fxtqmtype" onchange="this.form.submit();" style="width: 100%">';
							
		foreach ($txt['bt_statuses'] as $id => $title)
		{
			// We can't mark attention like this.
			if ($id == 'attention' || $id == 'no_attention')
				continue;
			
			echo '
								<option value="mark_', $id, '"', $context['bugtracker']['entry']['status']['id'] == $id ? ' selected="selected"' : '', '>', $title, '</option>';
		}
		
		echo '
							</select>
							<input type="hidden" name="fxtqm" value="', $context['bugtracker']['entry']['id'], '" />
						</form>';
	}
	else
		echo '
					
						<a href="', $scripturl, '?action=bugtracker;sa=viewstatus;status=', $context['bugtracker']['entry']['status']['id'], '">
							', $context['bugtracker']['entry']['status']['title'],'
						</a>' . ($context['bugtracker']['entry']['attention'] ? ' <strong>(' . $txt['status_attention'] . ')</strong>' : '');
						
	echo '
					</td>
				</tr>';
	
	// Project
	echo '
				<tr>
					<td style="width:95px">
						<strong>', $txt['project'], ':</strong>
					</td>
					<td>
						<a href="', $scripturl, '?action=bugtracker;sa=projectindex;project=', $context['bugtracker']['entry']['project']['id'], '">
							', $context['bugtracker']['entry']['project']['name'], '
						</a>
					</td>
				</tr>';
				
	// Progress.
	if ($context['bugtracker']['entry']['status']['id'] == 'wip')
	{
		echo '
				<tr>
					<td style="width:95px">
						<strong>', $txt['entry_progress'], ':</strong>
					</td>
					<td>';
					
		if ($context['can_bt_edit_any'] || $context['can_bt_edit_own'])
		{
			if ($context['bugtracker']['entry']['progress'] != 0)
				echo '
						<form name="progDown" style="float: left;padding-top:2px;padding-right:5px" action="', $scripturl, '?action=bugtracker;sa=qmod" method="post">
							<input type="hidden" name="fxtqmtype" value="progress_down" />
							<input type="hidden" name="fxtqm" value="', $context['bugtracker']['entry']['id'], '" />
							<a href="#" title="', $txt['quick_progress_up'], '" onclick="document.progDown.submit()">[-]</a>
						</form>';
						
			echo '
						<form name="progChange" style="float:left" action="', $scripturl, '?action=bugtracker;sa=qmod" method="post">
							<input type="hidden" name="fxtqmtype" value="progress_num" />
							<input type="hidden" name="fxtqm" value="', $context['bugtracker']['entry']['id'], '" />
							<select name="fxtqm_prog" onchange="document.progChange.submit()">';
						
			if (empty($modSettings['bt_entry_progress_steps']) || $modSettings['bt_entry_progress_steps'] == 5)
				$progvalues = array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100);
			else
				$progvalues = array(0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100);
	
			foreach ($progvalues as $prog)
			{
				echo '
								<option value="', $prog, '"', $context['bugtracker']['entry']['progress'] == $prog ? ' selected="selected"' : '', '>', $prog, '%</option>';
			}
			
			echo '
							</select>
						</form>';
						
			if ($context['bugtracker']['entry']['progress'] != 100)
				echo '
						<form name="progUp" style="float: left;padding-top:2px;padding-left:5px" action="', $scripturl, '?action=bugtracker;sa=qmod" method="post">
							<input type="hidden" name="fxtqmtype" value="progress_up" />
							<input type="hidden" name="fxtqm" value="', $context['bugtracker']['entry']['id'], '" />
							<a href="#" title="', $txt['quick_progress_up'], '" onclick="document.progUp.submit()">[+]</a>
						</form>';
		}
						
		else
			echo $context['bugtracker']['entry']['progress'];
			
		echo '
					</td>
				</tr>';
	}
				
	// Time posted.
	echo '
				<tr>
					<td style="width:95px">
						<strong>', $txt['added_on'], ':</strong>
					</td>
					<td>
						', $context['bugtracker']['entry']['started'], '
					</td>
				</tr>';
	
	if ($context['bugtracker']['entry']['is_updated'])
		echo '
				<tr>
					<td style="width:95px">
						<strong>', $txt['last_updated'], '</strong>
					</td>
					<td>
						', $context['bugtracker']['entry']['updated'], '
					</td>
				</tr>';
			
	echo '
			</table>';
			
	// Project.
	if (!empty($context['bugtracker']['projects']))
	{
		echo '
			<hr />
			<table>
				<tr>
					
					<td style="width:95px">
						<strong>', $txt['move_to'], ':</strong>
					</td>
					<td>
						<form action="', $scripturl, '?action=bugtracker;sa=qmod" method="post">
							<select name="fxtqm_proj" style="width: calc(100% - 45px)">';
								
		foreach ($context['bugtracker']['projects'] as $id => $title)
		{
			echo '
								<option value="', $id, '">', $title, '</option>';
		}
		
		echo '
							</select>
							<input type="hidden" name="fxtqm" value="', $context['bugtracker']['entry']['id'], '" />
							<input type="hidden" name="fxtqmtype" value="move" />
							<input type="submit" class="button_submit" value="', $txt['go'], '" />
						</form>
					</td>
				</tr>
			</table>';
	}
	
	echo '
		</div>
	</div>';
	
	// Entry description...
	echo '
	<div id="entry_desc" class="floatright">
		<div class="cat_bar">
			<h3 class="catbg">
				<img class="icon" src="', $context['bugtracker']['entry']['type']['icon'], '" alt="" />
				', sprintf($txt['entrytitle'], $context['bugtracker']['entry']['id'], $context['bugtracker']['entry']['name']), '
			</h3>
		</div>
		<div class="information">
			', sprintf($txt['desc_left'], $context['bugtracker']['entry']['tracker']['name']), '
			<hr />
			', $context['bugtracker']['entry']['desc'], '
		</div>
	</div>
	<br class="clear" />';
	
	// The Notes of this entry...
	if (!empty($modSettings['bt_enable_notes']))
	{
		echo '
	<div class="cat_bar" id="notesToggle" name="notes">
		<h3 class="catbg">
			<img class="icon" src="', $settings['images_url'], '/bugtracker/notes.png" alt="" />
			<a href="#notes" name="notes">', $txt['notes'], '</a> (', count($context['bugtracker']['entry']['notes']), ')
		</h3>
	</div>';
		
	// Do we have any notes?
		if (!empty($context['bugtracker']['entry']['notes']))
		{
			echo '
	<div id="notes">';
			foreach ($context['bugtracker']['entry']['notes'] as $note)
			{
				// Start the note.
				echo '
		<div class="windowbg" name="note_', $note['id_note'], '">';
	
				// Build the array of buttons.
				$buttons = array();
			
				// Edit it?
				if ($context['can_bt_edit_note_any'] || $context['can_bt_edit_note_own'])
					$buttons[] = '<a href="' . $scripturl . '?action=bugtracker;sa=editnote;note=' . $note['id_note'] . '">' . $txt['edit_note'] . '</a>';
		
				// Can we remove this note?
				if ($context['can_bt_remove_note_any'] || $context['can_bt_remove_note_own'])
					$buttons[] = '<a onclick="return confirm(' . javascriptescape($txt['really_delete_note']) . ')" href="' . $scripturl . '?action=bugtracker;sa=removenote;note=' . $note['id_note'] . '">' . $txt['remove_note'] . '</a>';
			
				// If we have buttons, show them.
				if (!empty($buttons))
					echo '
			<div class="floatright">
				' . implode(' | ', $buttons) . '
			</div>';
			
			// Then show the note itself.
				echo '
			<a></a>';
		
			if ($note['id_author'] == 0)
				echo sprintf($txt['note_by_guest'], $note['time']);
			else
				echo sprintf($txt['note_by'], $note['real_name'], $note['posted_time'], $scripturl . '?action=profile;u=' . $note['id_author']);
			
			echo '
			<hr />
			', $note['note'], '
		</div>';
		
			}
			
			echo '
	</div><br />';
		}
	// Aww, we have no notes?
		else
			echo '
	<div class="information centertext">
		<strong>', $txt['no_notes'], '</strong>
	</div><br />';
	
	// Show Quick Note, if we can.
		if ($context['can_bt_add_note_any'] || $context['can_bt_add_note_own'] && !empty($modSettings['bt_quicknote']))
			echo '
	<div class="cat_bar" id="quickNoteToggle">
		<h3 class="catbg">
			<img class="icon" src="', $settings['images_url'], '/bugtracker/quicknote.png" alt="" /> ', $txt['quick_note'], '
		</h3>
	</div>
	<div class="information" id="quickNote">
		<div class="peditbox">
			<form action="', $scripturl, '?action=bugtracker;sa=addnote2" method="post">
				<input type="hidden" name="entry_id" value="', $context['bugtracker']['entry']['id'], '" />
				<input type="hidden" name="is_fxt" value="true" />
				', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '
				<br class="clear_right">
				', template_control_richedit_buttons($context['post_box_name']), '
			</form>
		</div>
	</div><br />';
	}
	
	// Now show the action log.
	echo '
	<div class="cat_bar" id="actionsToggle" name="actions">
		<h3 class="catbg">
			<img class="icon" src="', $settings['images_url'], '/bugtracker/actions.png" alt="" />
			<a href="#actions" name="actions">', $txt['actions'], ' (', count($context['bugtracker']['entry']['log']), ')</a>
		</h3>
	</div>';
	
	if (!empty($context['bugtracker']['entry']['log']))
	{
		echo '
	<table id="actionslist" class="table_grid">
		<thead>
			<tr class="title_bar">
				<th scope="col" class="first_th action_id">#</th>
				<th scope="col" class="action_time">', $txt['time'], '</th>
				<th scope="col" style="width:7.5%">', $txt['user'], '</th>
				<th scope="col" class="last_th" style="width:75%">', $txt['action'], '</th>
			</tr>
		</thead>
		<tbody>';
	
		$last = 0;
		$i = 1;
		foreach ($context['bugtracker']['entry']['log'] as $act)
		{
			echo '
			<tr class="windowbg">
				<td class="action_id">', $i, '</td>';
		
			// Get the string ready.
			$text = sprintf($txt['action_' . $act['type']],
				$act['from'],
				$act['to']);
				
			echo '
				<td class="action_time">', timeformat($act['time']), '</td>
				<td>', ($act['id_member'] == 0 ? $act['real_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $act['id_member'] . '">' . $act['real_name'] . '</a>'), '</td>
				<td>', $text, '</td>';
			
			// Then finish off.
			echo '
			</tr>';
		
			$last = !$last;
			$i++;
		}
		echo '
		</tbody>
	</table>';
	}
	else
		echo '<div class="information">', $txt['no_actions'], '</div>';
	
	echo '
	<br class="clear" />';
}

?>
