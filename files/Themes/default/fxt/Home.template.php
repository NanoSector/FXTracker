<?php

/* FXTracker Home Template */

function template_TrackerHome()
{
	// Global $context and other stuff.
	global $context, $txt, $scripturl, $settings, $modSettings;

	// TODO: Move to language files.
	if (empty($context['projects']) && $context['user']['is_admin'])
		echo '
        <div class="cat_bar">
		<h3 class="catbg">', $txt['welcome_to_fxt'], '</h3>
        </div>
	<div class="information">
		', $txt['welcome_to_fxt_desc'], '<br /><br />
		- <a href="', $scripturl, '?action=admin;area=projects;sa=add">', $txt['w_add_proj'], '</a><br />
		- <a href="', $scripturl, '?action=admin;area=fxtsettings">', $txt['w_settings'], '</a><br />
		- <a href="', $scripturl, '?action=admin;area=fxtaddsettings">', $txt['w_features'], '</a><br /><br />
		<i>', $txt['w_note'], '</i>
	</div><br />';

	// Our latest issues and features.
	if (!empty($modSettings['bt_num_latest']))
	{
		echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<img src="', $settings['images_url'], '/bugtracker/latest.png" class="icon" alt="" />', $txt['bt_latest'], '
		</h3>
	</div>';

		// These are the latest headers. Title bars, to be exact.
		echo '
	<div class="floatleft half_content">
		<div class="title_bar notopmargin">
			<h4 class="titlebg">
				', $txt['latest_issues'], '
			</h4>
		</div>
		<div class="information">';

		// Load the list of entries from the latest issues, and display them in a list.
		if (!empty($context['recent']['issue']))
		{
			// Instead of doing this ourselves, lets have <ol> do the numbering for us.
			echo '
			<ol class="reset"><!-- style="margin:0;padding:0 0 0 15px"-->';

			foreach ($context['recent']['issue'] as $entry)
			{
				// Add the project link...
				echo '
				<li>
					<strong>[', $entry['project_link'], ']</strong>';

				// And the name.
				echo '
					#', $entry['id_entry'], ':
					<a href="', $scripturl, '?action=bugtracker;sa=view;entry=', $entry['id_entry'], '">', $entry['title'], '</a>
				</li>';
			}

			echo '
			</ol>';
		}
		else
			echo $txt['no_latest_entries'];

		echo '
		</div>
	</div>
	<div class="floatright half_content">
		<div class="title_bar notopmargin">
			<h4 class="titlebg">
				', $txt['latest_features'], '
			</h4>
		</div>
		<div class="information">';

		// Load the list of entries from the latest features. Make a nice list of 'em!
		if (!empty($context['recent']['feature']))
		{
			// Again have <ol> do the work for us. That'll work better.
			echo '
			<ol class="reset">';

			foreach ($context['recent']['feature'] as $entry)
			{
				// Add the project link...
				echo '
				<li>
					<strong>[', $entry['project_link'], ']</strong>';

				// And the name.
				echo '
					#', $entry['id_entry'], ':
					<a href="', $scripturl, '?action=bugtracker;sa=view;entry=', $entry['id_entry'], '">', $entry['title'], '</a>
				</li>';
			}

			echo '
			</ol>';
		}
		else
			echo $txt['no_latest_entries'];

		echo '
		</div>
	</div>
	<br class="clear" />';
	}

	echo '
	<div class="main_container" id="projectsHeader">
		<div class="cat_bar">
			<h3 class="catbg">
				<img src="', $settings['images_url'], '/bugtracker/projects.png" class="icon" alt="" />', $txt['projects'], '
			</h3>
		</div>';

	if (!empty($context['projects']))
	{
		foreach ($context['projects'] as $id => $project)
		{
			// Project icon. TODO: Icon change depending on status.
			echo '
		<div class="up_contain">
			<div class="icon">
				<span class="board_on"></span>
			</div>';
			
			// Project description and name.
			echo '
			<div class="info">
				<a class="subject" href="', $scripturl, '?action=bugtracker;sa=projectindex;project=', $id, '">', $project['title'], '</a>
				<p>', $project['description'], '</p>
			</div>';
			
			// Project statistics...
			echo '
			<div class="stats">
				<p>', sprintf($txt['entry_count'], $project['num_entries'], $project['num_open_entries']), '</p>
			</div>';
			
			// And the last post, please.
			echo '
			<div class="lastpost">
				<p>';
			
			if (!empty($project['id_entry']))
				echo sprintf($txt['last_entry_on_by'],
					$project['last_entry']['entry_link'],
					$project['last_entry']['member_link'],
					$project['last_entry']['time']);
			else
				echo $txt['no_entries_in_project'];
				
			echo '
				</p>
			</div>
		</div>';
		}
	}
	else
		echo '
		<div class="up_contain centertext">
			<p>', $txt['no_projects'], '</p>
		</div>';

	echo '
	</div>';

	if (!empty($modSettings['bt_show_attention_home']))
	{
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['items_attention'], '</h3>
	</div>';
		template_show_list('fxt_important');
	}

	// And our last batch of HTML.
	echo '
	<br class="clear" />';
}

?>
