<?php

function template_bt_manage_projects_index()
{
        global $context, $txt, $scripturl;
        
        template_button_strip(array(
                'addnew' => array('text' => 'bt_add_project', 'url' => $scripturl . '?action=admin;area=projects;sa=add', 'active' => true, 'lang' => true),
        ));
        
        echo '<br />';
        
        // Just show the list already
        template_show_list('fxt_projects');
}

function template_bt_manage_projects_edit()
{
        global $context, $scripturl, $txt;
        
        if (isset($context['success']) && $context['success'] == true)
                echo '
        <div class="information">
                <strong>', $txt['p_save_success'], '</strong>
        </div>';
        
        if (isset($context['errors']) && is_array($context['errors']))
                echo '
        <div class="errorbox">
                <h3>', $txt['oneormoreerrors'], '</h3>
                ', implode('<br />', $context['errors']), '
                <hr />
                <strong>', $txt['original_values'], '</strong>
                </div>';
        
        echo '
        <form action="', $context['editpage']['url'], '" method="post">
                <div class="cat_bar">
                        <h3 class="catbg">
                                ', $context['editpage']['title'], '
                        </h3>
                </div>
                <span class="upperframe"><span></span></span>
		<div class="roundframe">
                        <div class="peditbox">
                                <label for="proj_name"><strong>', $txt['project_name'], ': </strong></label>
                                <input class="input_text" type="text" name="proj_name" value="', $context['editpage']['name'], '" maxlength="80" size="80" />
                                <hr />
                                
                                <div id="bbcBox_message"></div>
                                <div id="smileyBox_message"></div>
                                ', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message') . '<br />';
	foreach ($context['editpage']['extra'] as $element)
	{
		switch ($element['type'])
		{
			case 'hidden':
				echo '
			<input type="hidden" name="', $element['name'], '"', (!empty($element['defaultvalue']) ? ' value="' . $element['defaultvalue'] . '"' : ''), ' />';
				
				break;
			
			case 'text':
				echo $element['label'] . ':
				<input type="text" name="', $element['name'], '" />';
				
				break;
		}
	}
        echo '
                                <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
                                <input type="submit" value="', $txt['pedit_submit'], '" class="floatright" />
                                <br class="clear" />
                        </div>
                </div>
		<span class="lowerframe"><span></span></span>
        </form>
        <br class="clear" />';
}