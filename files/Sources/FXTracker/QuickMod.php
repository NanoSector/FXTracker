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
 * Performs the actions required for Quick Mod.
 * No parameters - called by action.
 */
function BugTrackerDoQuickMod()
{
        global $sourcedir;
        
        $type = (string) $_POST['fxtqmtype'];
        $ids = $_POST['fxtqm'];
        
        //die( var_dump($type, $ids));

        require($sourcedir . '/FXTracker/Subs-Edit.php');
        $ids = is_array($ids) ? fxt_sanitiseIDs($ids) : (int) $ids;
        if (!empty($ids))
                switch ($type)
                {
                        case 'remove':
                                fxt_deleteEntry($ids);
                                break;
                        
                        case 'restore':
                                fxt_restoreEntry($ids);
                                break;
        
                        case 'mark_att':
                                fxt_markEntry($ids, 'attention');
                                break;
        
                        case 'mark_not_att':
                                fxt_markEntry($ids, 'attention_undo');
                                break;
        
                        case 'mark_new':
                                fxt_markEntry($ids, 'new');
                                break;
        
                        case 'mark_wip':
                                fxt_markEntry($ids, 'wip');
                                break;
        
                        case 'mark_done':
                                fxt_markEntry($ids, 'done');
                                break;
        
                        case 'mark_reject':
                                fxt_markEntry($ids, 'reject');
                                break;
                        
                        case 'move':
                                if (empty($_POST['fxtqm_proj']))
                                        fatal_lang_error('entry_move_failed');
                                        
                                $proj = (int) $_POST['fxtqm_proj'];
                                fxt_moveEntry($ids, $proj);
                                break;

                        case 'progress_up':
                                fxt_changeProgress($ids, '+');
                                break;
                        
                        case 'progress_down':
                                fxt_changeProgress($ids, '-');
                                break;
                        
                        case 'progress_num':
                                if (empty($_POST['fxtqm_prog']) && !($_POST['fxtqm_prog'] === '0'))
                                        fatal_lang_error('entry_move_failed');

                                $num = (int) $_POST['fxtqm_prog'];
                                fxt_changeProgress($ids, $num);
                                break;
                }
        redirectexit($_SERVER['HTTP_REFERER']);
}
