<?php
/******************************
 * [EQDKP Plugin] Raid Groups
 * Copyright 2006, Garrett Hunter, info@raidpoints.net
 * Licensed under the GNU GPL.
 * ------------------
 * $Id: index.php,v 1.1 2006/09/23 20:17:27 garrett Exp $
 *
 ******************************/

// EQdkp required files/vars
define('EQDKP_INC', true);
define('IN_ADMIN', true);
define('PLUGIN', 'alternates');

$eqdkp_root_path = './../../';

include_once($eqdkp_root_path . 'common.php');
include_once('config.php');

$alternates = $pm->get_plugin('alternates');

if ( !$pm->check(PLUGIN_INSTALLED, 'alternates') )
{
    message_die($user->lang['alternates_plugin_not_installed']);
}

$user->check_auth('a_members_man');

class List_Alternates extends EQdkp_Admin
{
    function List_Alternates()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;
        
        parent::eqdkp_admin();
        
        $this->assoc_buttons(array(
            'deletealternates' => array(
                'name'    => 'deletealternates',
                'process' => 'process_delete_alternates',
                'check'   => 'a_members_man'),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_members_man')
        ));
    }

    // ---------------------------------------------------------
    // Process Delete Alternates
    // ---------------------------------------------------------
    function process_delete_alternates()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        $success_message = $user->lang['admin_delete_alternates_no_action'];

        if (isset($_POST[URI_ALTERNATE_ID])) {

            $alternate_id_list = "(".implode(",",$_POST[URI_ALTERNATE_ID]).")";

            //
            // Detach alternates (any alts attached are set to be a main)
            //
            $query = $db->build_query('UPDATE', array(
                'member_main_id'      => NULL)
            );
            $db->query('UPDATE ' . MEMBERS_TABLE . ' SET ' . $query . " WHERE member_id IN ".$alternate_id_list);

			// Get alternates' name for the log
			$sql = "SELECT member_name
			          FROM ".MEMBERS_TABLE."
					 WHERE member_id IN ".$alternate_id_list;
			$result = $db->query($sql);

			$alt_names = array();			
			while ($alt_row = $db->fetch_record($result) ) {
                $alt_names[] = $alt_row['member_name'];
            } 
            $db->free_result($result);
            $alternate_name_list = implode(", ",$alt_names);

	        //
            // Logging
            //
            $log_action = array(
                'header'         => '{L_ACTION_ALTERNATE_DELETED}',
                '{L_MEMBER}'     => $_POST['member_name'],
                '{L_ALTERNATE}'  => $alternate_name_list);
            $this->log_insert(array(
                'log_type'   => $log_action['header'],
                'log_action' => $log_action)
            );

			//
			// Success message
			//
            $success_message = sprintf($user->lang['admin_delete_alternates_success'],$alternate_name_list,$_POST['member_name']);
        }
        $link_list = array(
            $user->lang['list_alternates'] => 'index.php' . $SID);
        $this->admin_die($success_message, $link_list);
    }

    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        //
        // Build list of alternates
        //
		$sql = "SELECT member_id,
				  	   member_name, 
					   member_main_id,
					   class_name AS member_class
				  FROM " . MEMBERS_TABLE . ", " . CLASS_TABLE . "
				 WHERE class_id = member_class_id
				   AND member_main_id IS NOT NULL
		      ORDER BY member_name";
		if ( !($alternates_result = $db->query($sql)) )
		{
			message_die('Could not obtain member information', '', __FILE__, __LINE__, $sql);
		}

		$alternate_count = 0;		
		while ( $row = $db->fetch_record($alternates_result) ) {
		
			$alt_count++;
			
			//
			// Get main's info
			//
			$sql = "SELECT member_name, 
						   class_name AS member_class
					  FROM " . MEMBERS_TABLE . ", " . CLASS_TABLE . " 
					 WHERE class_id = member_class_id 
					   AND member_id = ".$row['member_main_id'];
			$main_result = $db->query($sql);
			$main = array();
			while ( $main_row = $db->fetch_record($main_result) ) {
				$main['member_name']  = $main_row['member_name'];
				$main['member_class'] = $main_row['member_class'];
			}
			$db->free_result($main_result);
			
			$tpl->assign_block_vars('alternates_row', array(
					'ROW_CLASS'     => $eqdkp->switch_row_class(),
					'ID'     		=> $row['member_id'],
					'COUNT'         => $alt_count,
					'ALT_NAME'      => $row['member_name'],
					'ALT_CLASS'		=> $row['member_class'],
					'MAIN_NAME'	    => $main['member_name'],
					'MAIN_CLASS'	=> $main['member_class']
			));
		}
		$footcount_text = sprintf($user->lang['listalternates_footcount'], $alt_count);
		$db->free_result($alternates_result);
		
        $tpl->assign_vars(array(
           
            // Language
			'L_ALTERNATE'	=> $user->lang['alternate'],
			'L_MAIN'		=> $user->lang['alternates_main'],
			'L_NAME'		=> $user->lang['name'],
			'L_CLASS'		=> $user->lang['class'],

			'BUTTON_NAME' => 'deletealternates',
			'BUTTON_VALUE' => $user->lang['delete_alternates'],

            'F_DELETE_ALTERNATES' => 'manage_members.php' . $SID . '&amp;mode=deletealternates',

			'LISTALTERNATES_FOOTCOUNT' => $footcount_text
            
        ));

		$eqdkp->set_vars(array(
			'page_title'    => sprintf($user->lang['title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']).': '.$user->lang['is_title_alternates'],
			'template_path' => $pm->get_data('alternates', 'template_path'),
			'template_file' => 'listalternates.html',
			'display'       => true)
		);
    } // end display_form
	
}

$List_Alternates = new List_Alternates;
$List_Alternates->process();
	
?>