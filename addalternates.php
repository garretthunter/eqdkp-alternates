<?php
/******************************
 * EQdkp
 * Copyright 2002-2006
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * mm_managealternates.php
 * Began: Fri August 4, 2006
 * 
 * $Id: mm_addalternates.php,v 1.2 2006/09/04 02:40:40 garrett Exp $
 * 
 ******************************/
 
// EQdkp required files/vars
define('EQDKP_INC', true);
define('IN_ADMIN', true);
define('PLUGIN', 'alernates');

$eqdkp_root_path = './../../';

include_once($eqdkp_root_path . 'common.php');
include_once('config.php');

$raidgroups = $pm->get_plugin('alternates');

if ( !$pm->check(PLUGIN_INSTALLED, 'alternates') )
{
    message_die('The Raid Groups plugin is not installed.');
}

$user->check_auth('a_members_man');

class Add_Alternates extends EQdkp_Admin
{
	// methods..
	
    function Add_Alternates()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;
        
        parent::eqdkp_admin();

        $this->add_alternates = array(
            'member_id' => post_or_db('member_id'),
            'alternates'   => post_or_db('alternates')
        );
		        
        $this->assoc_buttons(array(
            'proces' => array(
                'name'    => 'process',
                'process' => 'process_addalternates',
                'check'   => 'a_members_man'),
            'form' => array(
                'name'    => 'display_form',
                'process' => 'display_form',
                'check'   => 'a_members_man'),
        ));
	
    }
	
   function error_check()
    {
        global $user;

        if ( (!isset($_POST['member_id'])) || ($_POST['member_id'] == '') ) {
            $this->fv->errors['ca_missing_main'] = $user->lang['fv_missing_main'];
        } 
		if ( (!isset($_POST['alternates'])) || (!is_array($_POST['alternates'])) ) {
            $this->fv->errors['ca_missing_alt'] = $user->lang['fv_missing_alt'];
        } elseif (array_search($_POST['member_id'],$_POST['alternates']) !== FALSE) {
            $this->fv->errors['ca_main_alt_same'] = $user->lang['fv_main_alt_same'];
		}        
        $this->add_alternates = array(
            'member_id' => post_or_db('member_id'),
            'alternates' => post_or_db('alternates')
        );
		               
        return $this->fv->is_error();
    }
    
    // ---------------------------------------------------------
    // Display form (Step 1)
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;
      
		// if nothing is selected in the list of mains alternates is returned as a single value & we always want it to be an array
		if (!is_array($this->add_alternates['alternates'])) {
			$this->add_alternates['alternates'] = array($this->add_alternates['alternates']);
		}

        // Generate the list main characters
        $sql =   'SELECT member_id, member_name
                    FROM ' . MEMBERS_TABLE . '
				   WHERE member_main_id IS NULL
                ORDER BY member_name';
        $result = $db->query($sql);

		$count_of_mains = $db->num_rows($result);
        while ( $row = $db->fetch_record($result) )
        {
            $tpl->assign_block_vars('main_member_row', array(
                'VALUE'    => $row['member_id'],
                'SELECTED' => ( $this->add_alternates['member_id'] == $row['member_id'] ) ? ' selected="selected"' : '',
                'OPTION'   => $row['member_name'])
            );
        }
        $db->free_result($result);
        
        // Generate the list of available alternates
		// Available alternates are mains w/o alternates
        $sql =   'SELECT ma.member_name, ma.member_id, ma.member_main_id
                    FROM ' . MEMBERS_TABLE . ' ma
				   WHERE ma.member_main_id IS NULL
				     AND ma.member_id NOT IN (
				          SELECT mb.member_main_id
            				FROM '.MEMBERS_TABLE.' mb
                           WHERE mb.member_main_id = ma.member_id)
                ORDER BY ma.member_name';
//echo "<pre>".$sql."</pre>"; echo $main_name;//gehDEBUG
        $result = $db->query($sql);

        while ( $row = $db->fetch_record($result) )
        {
            $tpl->assign_block_vars('available_mains_row', array(
                'VALUE'    => $row['member_id'],
                'SELECTED' => ( array_search($row['member_id'],$this->add_alternates['alternates']) !== FALSE ) ? ' selected="selected"' : '',
                'OPTION'   => $row['member_name'])
            );
        }
        $db->free_result($result);
        
        $tpl->assign_vars(array(
            // Form vars
            'F_ADD_ALTERNATES' => 'addalternates.php' . $SID . '&amp;mode=addalternates',
            
            // Language
            'L_ADD_ALTERNATES'        => $user->lang['add_alternates'],
			'L_ADD_ALTERNATE_DESCRIPTION' => $user->lang['add_alternate_description'],
			'L_MEMBER'                => $user->lang['member'],
			'L_POSSIBLE_ALTERNATES'    => $user->lang['possible_alternates'],
            'L_SELECT_1_OF_X_MAINS' => sprintf($user->lang['select_1ofx_mains'], $count_of_mains),
            
            // Form validation
            'FV_ADD_ALTS_MISSING_MAIN' => $this->fv->generate_error('ca_missing_main'),
            'FV_ADD_ALTS_MISSING_ALT' => $this->fv->generate_error('ca_missing_alt'),
            'FV_ADD_ALTS_MAIN_ALT_SAME' => $this->fv->generate_error('ca_main_alt_same')
        ));
        
		$eqdkp->set_vars(array(
			'page_title'    => sprintf($user->lang['title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']).': '.$user->lang['is_title_raidgroups'],
			'template_path' => $pm->get_data('alternates', 'template_path'),
			'template_file' => 'addalternates.html',
			'display'       => true)
		);
    }

    // ---------------------------------------------------------
    // Process add alternates
    // ---------------------------------------------------------
    function process_addalternates()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

		// Create an SQL IN clause with all alt ids
		$alt_list_sql_in = "(".implode(",",$this->add_alternates['alternates']).")";

 		//
		// Update each member_main_id with the new member_id
		//
		$query = $db->build_query('UPDATE', array(
			'member_main_id'      => $this->add_alternates['member_id'])
		);
		$db->query('UPDATE ' . MEMBERS_TABLE . ' SET ' . $query . " WHERE member_id IN ".$alt_list_sql_in);

		// -----------------------
		// Get logging information 
		// -----------------------

		// get main's name
        $sql =   'SELECT member_name
                    FROM ' . MEMBERS_TABLE . '
				   WHERE member_id = '.$this->add_alternates['member_id'];
        $member_name = $db->query_first($sql);

		// get alternates' names
        $sql =   'SELECT member_name
                    FROM ' . MEMBERS_TABLE . '
				   WHERE member_id IN '.$alt_list_sql_in;

        $alt_name_result = $db->query($sql);

		$alt_name_arr = array();
        while ( $row = $db->fetch_record($alt_name_result) )
        {
			$alt_name_arr[] = $row['member_name'];
		}
		$alt_name_list = implode(", ",$alt_name_arr);

        //
        // Write the log event
        //
        $log_action = array(
            'header'   => '{L_ACTION_ALTERNATE_ADDED}',
            '{L_MEMBER}' => $member_name,
            '{L_ALTERNATE}'   => $alt_name_list);
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action)
        );
		        
        $success_message = sprintf($user->lang['admin_add_alternates_success'], $alt_name_list, $member_name);
        $link_list = array(
            $user->lang['add_alternates']  => 'addalternates.php' . $SID . '&amp;mode=addalternates',
            $user->lang['list_alternates'] => 'index.php' . $SID . '&amp;mode=list');
        $this->admin_die($success_message, $link_list);
    }
	
}

$Add_Alternates = new Add_Alternates;
$Add_Alternates->process();

?>