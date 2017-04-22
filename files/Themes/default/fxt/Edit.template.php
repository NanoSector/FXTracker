<?php

/* FXTracker Edit Template */

function template_BugTrackerEdit()
{
	// Globalling.
	global $context, $scripturl, $txt, $modSettings;
	
	// Cool, do we have errors? :D
	if (!empty($context['errors_occured']))
	{
		echo '
		<div class="errorbox">
			<strong>', $txt['errors_occured'], '</strong>
			<ul>';
			
		foreach ($context['errors_occured'] as $error)
			echo '
				<li>' . $error . '</li>';
				
		echo '
			</ul>
		</div>';
	}

	// Start our form.
	echo '
	<form action="', $context['btform']['url'], '" method="post">';

	// Then, for the general information.
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['entry_edit'], '
		</h3>
	</div>
	<div class="windowbg">
		<span class="topslice"><span></span></span>
		<div class="fullpadding peditbox">
			<table>';

	// The entry title. Lets start with that.
	echo '
				<tr>
					<td>
						<strong>', $txt['title'], '</strong>
					</td>
					<td>
						<input type="text" name="entry_title" size="80" maxlength="80" value="', $context['btform']['entry_name'], '"', (isset($context['errors_occured']['title']) ? ' style="border: 1px solid red"' : ''), ' />
					</td>
				</tr>';

	// What kind of thing is this? Set the type, please.
	echo '
				<tr>
					<td>
						<strong>', $txt['type'], '</strong>
					</td>
					<td>
						<select name="entry_type"', (isset($context['errors_occured']['type']) ? ' style="border: 1px solid red"' : ''), '>
							<option value="" disabled="disabled">', $txt['type'], '</option>
							<option value="issue"', ($context['btform']['entry_type'] == 'issue' ? ' selected="selected"' : ''), '>', $txt['issue'], '</option>
							<option value="feature"', ($context['btform']['entry_type'] == 'feature' ? ' selected="selected"' : ''), '>', $txt['feature'], '</option>
						</select>
					</td>
				</tr>';

	// Close everything. And start the editor.
	echo '
			</table>

			<hr />
			
			<div id="bbcBox_message"></div>
			<div id="smileyBox_message"></div>
			', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message') . '<br /><hr />
			
			<div class="cat_bar">
				<h4 class="catbg">
					', $txt['additional_options'], '
				</h4>
			</div>
			<div class="information">
			<table>';
			
	// To what should we mark this?
	echo '
				<tr>
					<td>
						<strong>', $txt['status'], '</strong>
					</td>
					<td>
						<select name="entry_mark">
							<option value="new"', ($context['btform']['entry_status'] == 'new' ? ' selected="selected"' : ''), '>', $txt['status_new'], '</option>
							<option value="wip"', ($context['btform']['entry_status'] == 'wip' ? ' selected="selected"' : ''), '>', $txt['status_wip'], '</option>
							<option value="done"', ($context['btform']['entry_status'] == 'done' ? ' selected="selected"' : ''), '>', $txt['status_done'], '</option>
							<option value="reject"', ($context['btform']['entry_status'] == 'reject' ? ' selected="selected"' : ''), '>', $txt['status_reject'], '</option>
						</select>
					</td>
				</tr>';
				
	// Gotta change the progress?
	echo '
				<tr>
					<td>
						<strong>', $txt['entry_progress'], '</strong>
					</td>
					<td>
						<select name="entry_progress">';
							
	// Do it this way, since that is *quite* a bit quicker.
	if (empty($modSettings['bt_entry_progress_steps']) || $modSettings['bt_entry_progress_steps'] == 5)
		$progvalues = array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100);
	else
		$progvalues = array(0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100);
	
	foreach ($progvalues as $prog)
	{
		echo '
							<option value="', $prog, '"', $context['btform']['entry_progress'] == $prog ? ' selected="selected"' : '', '>', $prog, '%</option>';
	}
	
	echo '
						</select>
					</td>
				</tr>';

	echo '
			</table>';

	// Or does it need attention?
	echo '
			<input type="checkbox" name="entry_attention" value="true"', ($context['btform']['entry_attention'] ? ' checked="checked"' : ''), ' /> ', $txt['mark_attention'], '<br />';

	// Extra stuff?
	foreach ($context['btform']['extra'] as $element)
	{
		switch ($element['type'])
		{
			case 'hidden':
				echo '
			<input type="hidden" name="', $element['name'], '"', (!empty($element['defaultvalue']) ? ' value="' . $element['defaultvalue'] . '"' : ''), ' />';
				
				break;
			
			case 'text':
				echo $element['label'] . ':
				<input type="text" name="', $element['name'], '" /><br />';
				
				break;
		}
	}

	// And our submit button and closing stuff.
	echo '
			</div><br />
			<div class="floatright">
				<input type="submit" value="', $txt['entry_submit'], '" class="button_submit" />
			</div>
			<br class="clear" />
		</div>		
		<span class="botslice"><span></span></span>
	</div>';

	// Close the form.
	echo '
	</form>';

	// Because content will break otherwise.
	echo '
	<br class="clear" />';
}

?>