<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * mm_listmembers.php
 * Began: Thu January 30 2003
 *
 * $Id: mm_listmembers.php,v 1.3 2006/08/07 03:37:29 garrett Exp $
 *
 ******************************/

// Shows a list of members, basically just an admin-themed version of
// /listmembers.php

if ( !defined('EQDKP_INC') )
{
    die('Hacking attempt');
}

class MM_Listmembers extends EQdkp_Admin {

    function mm_listmembers()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        parent::eqdkp_admin();
		
        $this->assoc_buttons(array(
            'delete' => array(
                'name'    => 'delete',
                'process' => 'process_delete',
                'check'   => 'a_members_man'),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_members_man'))
        );
	}
	
    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
	function display_form() {

        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

		$sort_order = array(
			0 => array('member_name', 'member_name desc'),
			1 => array('member_earned desc', 'member_earned'),
			2 => array('member_spent desc', 'member_spent'),
			3 => array('member_adjustment desc', 'member_adjustment'),
			4 => array('member_current desc', 'member_current'),
			5 => array('member_lastraid desc', 'member_lastraid'),
			6 => array('member_level desc', 'member_level'),
			7 => array('member_class', 'member_class desc'),
			8 => array('rank_name', 'rank_name desc'),
			9 => array('class_armor_type', 'class_armor_type desc')
		
		);
		
		$current_order = switch_order($sort_order);      

        //
        // Generate list of members
        //
		$sql = 'SELECT m.*, 
					   c.class_name AS member_class, 
					   r.race_name AS member_race
				  FROM ' . MEMBERS_TABLE . ' m, ' . CLASS_TABLE . ' c, ' . RACE_TABLE . " r
				 WHERE r.race_id = m.member_race_id
				   AND c.class_id = m.member_class_id
		      ORDER BY m.member_name";
		if ( !($members_result = $db->query($sql)) )
		{
			message_die('Could not obtain member information', '', __FILE__, __LINE__, $sql);
		}

		$member_count = 0;		
		while ( $row = $db->fetch_record($members_result) ) {

			$alternate_count++;
			
			//
			// Find name of main if alt
			//
			$main_name = '';
			if ($row['member_main_id'] != '') {
				$sql = "SELECT member_name
				          FROM ".MEMBERS_TABLE."
						 WHERE member_id = ".$row['member_main_id'];
				$main_name = $db->query_first($sql);
			}
//echo "<pre>".$sql."</pre>"; echo $main_name;//gehDEBUG
			$tpl->assign_block_vars('members_row', array(
					'ID'     		=> $row['member_id'],
					'ROW_CLASS'     => $eqdkp->switch_row_class(),
					'NAME'          => $row['member_name'],
					'COUNT'         => $member_count,
					'S_MAIN'		=> ( $main_name != '' ) ? true : false,
					'MAIN'		    => $main_name,
					'U_VIEW_MAIN'   => 'manage_members.php'.$SID . '&amp;mode=addmember&amp;' . URI_NAME . '='.$main_name,
					'CLASS'			=> $row['member_class'],
					'RACE'			=> $row['member_race'],
					'U_VIEW_MEMBER' => 'manage_members.php'.$SID . '&amp;mode=addmember&amp;' . URI_NAME . '='.$row['member_name']
			));

		}
		$db->free_result($members_result);

		$tpl->assign_vars(array(
			'F_MEMBERS' => 'manage_members.php' . $SID . '&amp;mode=addmember',
		
			'L_NAME' => $user->lang['name'],
			'L_MAIN' => $user->lang['main'],
			'L_CLASS' => $user->lang['class'],
			'L_RACE' => $user->lang['race'],
			'L_EARNED' => $user->lang['earned'],
			'L_SPENT' => $user->lang['spent'],
			'L_ADJUSTMENT' => $user->lang['adjustment'],
			'L_CURRENT' => $user->lang['current'],
			'L_LASTRAID' => $user->lang['lastraid'],
			'BUTTON_NAME' => 'delete',
			'BUTTON_VALUE' => $user->lang['delete_selected_members'],
		
			'O_NAME' => $current_order['uri'][0],
			'O_RANK' => $current_order['uri'][8],
			'O_LEVEL' => $current_order['uri'][6],
			'O_CLASS' => $current_order['uri'][7],
			'O_ARMOR'      => $current_order['uri'][9],
			'O_EARNED' => $current_order['uri'][1],
			'O_SPENT' => $current_order['uri'][2],
			'O_ADJUSTMENT' => $current_order['uri'][3],
			'O_CURRENT' => $current_order['uri'][4],
			'O_LASTRAID' => $current_order['uri'][5],
		
			'U_LIST_MEMBERS' => 'manage_members.php'.$SID.'&amp;mode=list&amp;',
		
			'S_COMPARE' => false,
			'S_NOTMM' => false,
		
			'LISTMEMBERS_FOOTCOUNT' => $footcount_text)
		);
		
		$eqdkp->set_vars(array(
			'page_title'    => sprintf($user->lang['title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']).': '.$user->lang['listmembers_title'],
			'template_file' => 'admin/mm_listmembers.html',
			'display'       => true)
		);
	}

}

?>
