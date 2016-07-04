<?php
/**
 * Project:     Alternates [EQdkp Plugin]
 * License:     http://opensource.org/licenses/gpl-license.php
 * -----------------------------------------------------------------------
 * File:        lang_main.php
 * Began:       9/1/2006
 * -----------------------------------------------------------------------
 * @author      Garrett Hunter <loganfive@blacktower.com>
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

// Initialize the language array if it isn't already
if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// %1\$<type> prevents a possible error in strings caused
//      by another language re-ordering the variables
// $s is a string, $d is an integer, $f is a float

$lang = array_merge($lang, array(
// Page Titles
    'is_title_alternates' = "Alternates",

// Labels
    'alternate'                 = "Alternate",
    'alternates'                = "Alternates",
    'alternate_display'         = "Display",
    'alternate_display_order'   = "Order",
    'add_alternates'            = "Add Alternate",
    'update_alternate'          = "Update Alternate",
    'delete_alternates'         = "Unlink Alternates",
    'list_alternates'           = "List Alternates",
    'possible_alternates'       = 'Possible Alternates',
    'alternates_main'           = 'Main',
    'listalternates_footcount'  = "... found %1\$d alternates",
    'add_alternate_description' = 'This assigns one or more existing members to be the alternate of another member.',
    'select_1ofx_mains'         = "Select 1 of %1\$d mains...",

// Log Actions
    'action_alternate_added'    = "Alternate Added",
    'action_alternate_deleted'  = "Alternate Unlinked",
    'action_alternate_updated'  = "Alternate Updated",
    'alternate_before'          = "Alternate Before",
    'rg_raid_names_before'      = 'alternate_raid_names' . " Before",
    'rg_display_before'         = "Display Before",
    'alternate_after'           = "Alternate After",

// Verbose Log Messages
    'vlog_alternate_added'      = "%1\$s converted member(s) %2\$s to be alternate(s) for member %3\$s.",
    'vlog_alternate_deleted'        = "%1\$s unlinked alternate(s) %2\$s from their main(s).",

// System Messages
    'admin_delete_alternates_success'    = "Unlinked alternate(s) %1\$s from their main(s).",
    'admin_add_alternates_success'       = "%1\$s added as alternate(s) for member %2\$s.",
    'admin_delete_alternates_no_action'  = "No alternates were selected for deletion.",
    'confirm_delete_alternate'          = "Are you sure you want to delete this alternate?",
    'duplicate_alternate_name'          = "Cannot add alternate. Member %1\$s already has an alternate named %2\$s",
    'alternate_name_conflict'           = "Cannot add alternate. %1\$s is the name of an existing member",
    'no_active_members_found'           = "No active members found",
    'alternates_plugin_not_installed'   = "The Alternates plugin is not installed.",

// Form error messages
    'fv_missing_main'  = 'You must select a main character.',
    'fv_missing_alt'   = 'You must select at least one alternate.',
    'fv_main_alt_same' = 'Alternate and main cannot be the same.',
));