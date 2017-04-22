<?php

/* FXTracker ViewType Template */

function template_TrackerViewType()
{
	global $context, $scripturl, $txt, $settings;

	// Headers and starting the table; nothing interesting
	template_show_list('fxt_view');

	// Lil' space.
	echo '
	<br />';
	
	// And important entries.
	template_show_list('fxt_important');

	echo '
	<br class="clear" />';
}

?>