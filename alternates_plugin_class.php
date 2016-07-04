<?php
/**
 * Project:     Alternates [EQdkp Plugin]
 * License:     http://opensource.org/licenses/gpl-license.php
 * -----------------------------------------------------------------------
 * File:        alternates_plugin_class.php
 * Began:       9/1/2006
 * -----------------------------------------------------------------------
 * @author 		Garrett Hunter <loganfive@blacktower.com>
 * @copyright   2008 Garrett Hunter
 * @link        http://code.google.com/p/eqdkp-alternates/
 * @package     Alternates
 * @version     $id$
 */

if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

/**
 * alternates_Plugin_Class to handle installation & uninstallation of the plugin 
 * @subpackage Installation
 */
class Alternates_Plugin_Class extends EQdkp_Plugin
{

    function alternates_plugin_class($pm)
    {
        global $eqdkp_root_path, $user, $db;

        $this->eqdkp_plugin($pm);
        $this->pm->get_language_pack('alternates');

		/**
		 * Log Events
		 */
		$this->add_log_action('{L_ACTION_ALTERNATE_ADDED}', $user->lang['action_alternate_added']); //gehTODO - Do I need to add the $user->lang[] string here? seemded redundant based on code review of logs.php
		$this->add_log_action('{L_ACTION_ALTERNATE_DELETED}', $user->lang['action_alternate_deleted']);
		$this->add_log_action('{L_ACTION_ALTERNATE_UPDATED}', $user->lang['action_alternate_updated']);

        $this->add_data(array(
            'name'          => 'Alternates',
            'code'          => 'alternates',
            'path'          => 'alternates',
            'contact'       => 'info@raidpoints.net',
            'template_path' => 'plugins/alternates/templates/',
            'version'       => '1.0.0')
        );

        $this->add_menu('admin_menu', $this->gen_admin_menu());

        // Define installation
        // -----------------------------------------------------
        $this->add_sql(SQL_INSTALL, "CREATE TABLE IF NOT EXISTS __alternates_alts (
                                              `alternate_id` smallint(5) unsigned NOT NULL auto_increment,
                                              `member_id` smallint(5) unsigned NOT NULL,
                                              `member_main_id` smallint(5) unsigned NOT NULL,
                                              PRIMARY KEY  (`alternate_id`)
                                            );");
	
        /**
         * Define uninstallation
         */
        $this->add_sql(SQL_UNINSTALL, "DROP TABLE IF EXISTS __alternates_alts;");
    }

    function gen_admin_menu()
    {
        if ( $this->pm->check(PLUGIN_INSTALLED, 'alternates') )
        {
            global $db, $user, $eqdkp;
            $admin_menu = array(
                    'alternates' => array(
                    0 => $user->lang['alternates'],
                    1 => array('link' => path_default('plugins/' . $this->get_data('path') . '/addalternates.php'), 
                               'text' => $user->lang['add'], 
                               'check' => 'a_members_man'),
                    2 => array('link' => path_default('plugins/' . $this->get_data('path') . '/index.php'),   
                               'text' => $user->lang['list'],  
                               'check' => 'a_members_man'),
                )
             );

            return $admin_menu;
        }
        return;
    }

}
?>