<?php

/*
 * FXTracker - A Bug Tracker for SMF
 * ------------------------------------------
 * @package   FXTracker
 * @author    Yoshi2889
 * @copyright Yoshi2889 2012-2013
 * @license   http://creativecommons.org/licenses/by-sa/3.0/deed.en_US CC-BY-SA
 */

function template_TrackerViewProject()
{
	global $context, $scripturl, $txt, $settings, $modSettings;
	
	if (!empty($modSettings['bt_show_description_ppage']) && !empty($context['bugtracker']['project']))
		echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $context['bugtracker']['project']['title'], '
		</h3>
	</div>
	<div class="information">', parse_bbc($context['bugtracker']['project']['description']), '</div>';
	
	// Moved an entry to the trash can?
	if (isset($_GET['trashed']))
		echo '
	<div class="infobox">', $txt['trash_moved'], '</div>';
	
	if (isset($_GET['deleted']))
		echo '
	<div class="infobox">', $txt['trash_deleted'], '</div>';
	
	$buttons = array();

	// Are we allowed to add a new entry?
	if ($context['can_bt_add'])
		$buttons['add_entry'] = array(
			'text' => 'new_entry',
			'url' => $scripturl . '?action=bugtracker;sa=new;project=' . $context['bugtracker']['project']['id_project'],
			'lang' => true,
			'active' => true
		);
	
	if (!empty($context['bugtracker']['project']))	
		$buttons['view_trash'] = array(
			'text' => 'view_trash_proj',
			'url' => $scripturl . '?action=bugtracker;sa=trash;project=' . $context['bugtracker']['project']['id_project'],
			'lang' => true
		);
	if (isset($_GET['trashed']))
		$buttons['view_trash']['active'] = true;
			
	// Just headers.
	template_button_strip($buttons);
	
	// Show the form for filtering.
	echo '
	<div class="threequartercontent floatright">';
	
	if (isset($context['has_filter']))
		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['filter_results'], '</h3>
		</div>';
	
	// And our list!
	template_show_list('fxt_view');
	
	// Show a filter form?
	echo '
	</div>
	<div class="quartercontent floatleft">';
	
	if (!empty($modSettings['bt_enable_filter']))
	{
		echo '
		<div class="cat_bar" id="projectFilterToggle">
			<h3 class="catbg"><span class="generic_icons filter"></span>', $txt['filter'], '</h3>
		</div>
		<div class="information" id="projectFilter">';
		
		if (!empty($context['filter_error']))
			echo '
			<div class="errorbox">', $txt[$context['filter_error']], '<br>', $txt['filter_err_reset'], '</div>';
		
		echo '
			<form action="', $context['form_url'], '" method="post">
				<ul>
					<li>', $txt['filter_desc'], '</li>
					<li><strong>', $txt['status'], '</strong></li>';
	
		// Get the statuses lined up.
		foreach ($txt['bt_statuses'] as $status => $text)
			if ($status != 'attention' && $status != 'no_attention')
				echo '
					<li><label><input type="checkbox" name="status[]" value="', $status, '"', in_array($status, $context['filtered']) ? ' checked="checked"' : '', '>', $text, '</label></li>';
					
		echo '
					<li>&nbsp;</li>
					<li><label><input type="checkbox" name="attention"', in_array('attention', $context['filtered']) ? ' checked="checked"' : '', '>', $txt['filter_attention'], '</label></li>
					<li><label><input type="checkbox" name="in_trash"', in_array('in_trash', $context['filtered']) ? ' checked="checked"' : '', '>', $txt['filter_trash'], '</label></li>
					<li>&nbsp;</li>
					
					<li><strong>', $txt['type'], '</strong></li>';
	
		// Same for types.
		foreach ($txt['bt_types'] as $type => $text)
			echo '
					<li><label><input type="checkbox" name="type[]" value="', $type, '"', in_array($type, $context['filtered']) ? ' checked="checked"' : '', '>', $text, '</label></li>';
					
		echo '
					<li>&nbsp;</li>
					<li><strong>', $txt['search'], '</strong></li>
					<li><input type="text" name="search_title" class="fullwidth" value="', in_array('search', $context['filtered']) && !empty($context['fdata']['search_title']) ? $context['fdata']['search_title'] : '', '"></li>';
					
		if ($modSettings['bt_filter_search'] == 'fulltext' && !empty($modSettings['bt_filter_search_boolean_mode']))
		{
			echo '
					<li><a href="', $scripturl, '?action=helpadmin;help=filter_boolean_mode" onclick="return reqOverlayDiv(this.href);"><span class="generic_icons help"></span></a> <label><input type="checkbox" name="boolean_mode"', in_array('using_boolean', $context['filtered']) ? ' checked="checked"' : '', '>', $txt['filter_advanced_search'], '</label></li>';
		}
		
		echo '
					<li>', $txt[$modSettings['bt_filter_search'] . '_filter_desc'], '</li>';
		
		echo '
					<li>&nbsp;</li>
					
					<li><label><input type="checkbox" name="keep_filter"', !empty($context['session_filter']) ? ' checked="checked"' : '', '>', $txt['keep_filter'], '</label></li>
					<li>&nbsp;</li>
					
					<li class="">
						<input type="hidden" name="do_filter" value="1">
						<a href="', $context['form_url'], ';unsetfilters">', $txt['filter_reset'], '</a>
						<input type="submit" class="button_submit" value="', $txt['entry_submit'], '">
					</li>
					<li>&nbsp;</li>
					
					<li><a href="', $scripturl, '?action=helpadmin;help=filter_help" onclick="return reqOverlayDiv(this.href);"><span class="generic_icons help"></span> ', $txt['help'], '</a></li>
				</ul>
			</form>
		</div>';
	}
	echo '
		<div class="cat_bar">
			<h3 class="catbg"><span class="generic_icons boards"></span> ', $txt['project_details'], '</h3>
		</div>
		<div class="information">
			<ul>
				<li><strong>', $txt['entry_progress'], '</strong></li>
				<li><progress max="100" value="', $context['bugtracker']['project']['percent_completed'], '"></progress></li>
				<li>&nbsp;</li>
				
				<li><strong>', $txt['stats'], '</strong></li>
				<li>
					<table>
						<tr>
							<td></td>
							<td>', $txt['num_entries'], '</td>
							<td>', $context['bugtracker']['project']['num_entries'], '</td>
						</tr>
						<tr>
							<td><a href="', $scripturl, '?action=helpadmin;help=closed_entries_help" onclick="return reqOverlayDiv(this.href);"><span class="generic_icons help"></span></a></td>
							<td>', $txt['closed_entries'], '</td>
							<td>', $context['bugtracker']['project']['num_closed_entries'], '</td>
						</tr>
						<tr>
							<td></td>
							<td>', $txt['entries_remaining'], '</td>
							<td>', $context['bugtracker']['project']['num_open_entries'], '</td>
						</tr>
						<tr>
							<td><a href="', $scripturl, '?action=helpadmin;help=entries_important_help" onclick="return reqOverlayDiv(this.href);"><span class="generic_icons help"></span></a></td>
							<td>', $txt['entries_important'], '</td>
							<td>', $context['bugtracker']['project']['num_important'], '</td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td><hr></td>
						</tr>
						<tr>
							<td></td>
							<td>', $txt['project_progress'], '</td>
							<td>', $context['bugtracker']['project']['percent_completed'], '%</td>
						</tr>
					</table>
				</li>
			</ul>
		</div>
	</div>';
}

function template_TrackerViewTrash()
{
        global $context, $scripturl;

        if (isset($context['project']))
        {
                $button = array(
                        'return' => array(
				'text' => 'return_proj',
				'url' => $scripturl . '?action=bugtracker;sa=projectindex;project=' . $context['project']['id_project'],
				'lang' => true,
                                'active' => true
                        ),
                        'empty' => array(
                                'text' => 'empty_trash',
                                'url' => $scripturl . '?action=bugtracker;sa=trash;project=' . $context['project']['id_project'] . ';empty',
                                'lang' => true
                        ),
			'restore' => array(
				'text' => 'restore_all',
				'url' => $scripturl . '?action=bugtracker;sa=trash;project=' . $context['project']['id_project'] . ';restore',
				'lang' => true
			)
                );
                template_button_strip($button);
        }
	
	echo '
        <div class="cat_bar">
                <h3 class="catbg">
                        ', $context['trash_string'], '
                </h3>
        </div>';

        template_show_list('fxt_view');
}

?>
