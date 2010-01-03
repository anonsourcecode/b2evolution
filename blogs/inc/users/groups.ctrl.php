<?php
/**
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2009 by Francois PLANQUE - {@link http://fplanque.net/}
 * Parts of this file are copyright (c)2009 by The Evo Factory - {@link http://www.evofactory.com/}.
 *
 * {@internal License choice
 * - If you have received this file as part of a package, please find the license.txt file in
 *   the same folder or the closest folder above for complete license terms.
 * - If you have received this file individually (e-g: from http://evocms.cvs.sourceforge.net/)
 *   then you must choose one of the following licenses before using the file:
 *   - GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *   - Mozilla Public License 1.1 (MPL) - http://www.opensource.org/licenses/mozilla1.1.php
 * }}
 *
 * {@internal Open Source relicensing agreement:
 * The Evo Factory grants Francois PLANQUE the right to license
 * The Evo Factory's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author efy-bogdan: Evo Factory / Bogdan.
 * @author fplanque: Francois PLANQUE
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * @var AdminUI_general
 */
global $AdminUI;

$AdminUI->set_path( 'users', 'users' );

param_action('list');

param( 'grp_ID', 'integer', NULL );		// Note: should NOT be memorized:    -- " --

/**
 * @global boolean true, if user is only allowed to view group
 */
$user_view_group_only = ! $current_User->check_perm( 'users', 'edit' );

if( $user_view_group_only )
{ // User has no permissions to view: he can only edit his profile

	if( isset($grp_ID) )
	{ // User is trying to edit something he should not: add error message (Should be prevented by UI)
		$Messages->add( T_('You have no permission to edit groups!'), 'error' );
	}

	// Make sure the user only edits himself:

	$grp_ID = NULL;
	if( ! in_array( $action, array( 'new', 'view') ) )
	{
		$action = 'view';
	}
}

/*
 * Load editable objects and set $action (while checking permissions)
 */

$UserCache  = & get_UserCache();
$GroupCache = & get_GroupCache();

if( $grp_ID !== NULL )
{ // Group selected
	if( $action == 'update' && $grp_ID == 0 )
	{ // New Group:
		$edited_Group = new Group();
	}
	elseif( ($edited_Group = & $GroupCache->get_by_ID( $grp_ID, false )) === false )
	{ // We could not find the Group to edit:
		unset( $edited_Group );
		forget_param( 'grp_ID' );
		$Messages->add( sprintf( T_('Requested &laquo;%s&raquo; object does not exist any longer.'), T_('Group') ), 'error' );
		$action = 'list';
	}
	elseif( $action == 'list' )
	{ // 'list' is default, $grp_ID given
		if( $current_User->check_perm( 'users', 'edit' ) )
		{
			$action = 'edit';
		}
		else
		{
			$action = 'view';
		}
	}

	if( $action != 'view' && $action != 'list' )
	{ // check edit permissions
		if( !$current_User->check_perm( 'users', 'edit' ) )
		{
			$Messages->add( T_('You have no permission to edit groups!'), 'error' );
			$action = 'view';
		}
		elseif( $demo_mode  )
		{ // Additional checks for demo mode: no changes to admin's and demouser's group allowed
			$admin_User = & $UserCache->get_by_ID(1);
			$demo_User = & $UserCache->get_by_login('demouser');
			if( $edited_Group->ID == $admin_User->Group->ID
					|| $edited_Group->ID == $demo_User->group_ID )
			{
				$Messages->add( T_('You cannot edit the groups of user &laquo;admin&raquo; or &laquo;demouser&raquo; in demo mode!'), 'error' );
				$action = 'view';
			}
		}
	}
}

switch ( $action )
{
	case 'new':
		// We want to create a new group:
		if( isset( $edited_Group ) )
		{ // We want to use a template
			$new_Group = $edited_Group; // Copy !
			$new_Group->set( 'ID', 0 );
			$edited_Group = & $new_Group;
		}
		else
		{ // We use an empty group:
			$edited_Group = & new Group();
		}

		break;


	case 'update':
		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'group' );

		if( empty($edited_Group) || !is_object($edited_Group) )
		{
			$Messages->add( 'No group set!' ); // Needs no translation, should be prevented by UI.
			$action = 'list';
			break;
		}

		if( $edited_Group->load_from_Request() )
		{

			// check if the group name already exists for another group
			$query = 'SELECT grp_ID FROM T_groups
			           WHERE grp_name = '.$DB->quote($edited_grp_name).'
			             AND grp_ID != '.$edited_Group->ID;
			if( $q = $DB->get_var( $query ) )
			{
				param_error( 'edited_grp_name',
					sprintf( T_('This group name already exists! Do you want to <a %s>edit the existing group</a>?'),
						'href="?ctrl=users&amp;grp_ID='.$q.'"' ) );
			}

			if( $edited_Group->ID != 1 )
			{ // Groups others than #1 can be prevented from logging in or editing users
				$edited_Group->set( 'perm_admin', param( 'edited_grp_perm_admin', 'string', true ) );
				$edited_Group->set( 'perm_users', param( 'edited_grp_perm_users', 'string', true ) );
			}
		}

		if( $Messages->count( 'error' ) )
		{	// We have found validation errors:
			$action = 'edit';
			break;
		}

		if( $edited_Group->ID == 0 )
		{ // Insert into the DB:
			$edited_Group->dbinsert();
			$Messages->add( T_('New group created.'), 'success' );
		}
		else
		{ // Commit update to the DB:
			$edited_Group->dbupdate();
			$Messages->add( T_('Group updated.'), 'success' );
		}

		// Commit changes in cache:
		$GroupCache->add( $edited_Group );

		// Redirect so that a reload doesn't write to the DB twice:
		header_redirect( '?ctrl=users', 303 ); // Will EXIT
		// We have EXITed already at this point!!
		break;


	case 'delete':
		/*
		 * Delete group
		 */
		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'group' );

		if( !isset($edited_Group) )
		{
			debug_die( 'no Group set' );
		}

		if( $edited_Group->ID == 1 )
		{
			$Messages->add( T_('You can\'t delete Group #1!'), 'error' );
			$action = 'view';
			break;
		}
		if( $edited_Group->ID == $Settings->get('newusers_grp_ID' ) )
		{
			$Messages->add( T_('You can\'t delete the default group for new users!'), 'error' );
			$action = 'view';
			break;
		}

		if( param( 'confirm', 'integer', 0 ) )
		{ // confirmed, Delete from DB:
			$msg = sprintf( T_('Group &laquo;%s&raquo; deleted.'), $edited_Group->dget( 'name' ) );
			$edited_Group->dbdelete( $Messages );
			unset($edited_Group);
			forget_param('grp_ID');
			$Messages->add( $msg, 'success' );

			// Redirect so that a reload doesn't write to the DB twice:
			header_redirect( '?ctrl=users', 303 ); // Will EXIT
			// We have EXITed already at this point!!
		}
		else
		{	// not confirmed, Check for restrictions:
			memorize_param( 'grp_ID', 'integer', true );
			if( ! $edited_Group->check_delete( sprintf( T_('Cannot delete Group &laquo;%s&raquo;'), $edited_Group->dget( 'name' ) ) ) )
			{	// There are restrictions:
				$action = 'view';
			}
		}
		break;
}


$AdminUI->breadcrumbpath_init( false );  // fp> I'm playing with the idea of keeping the current blog in the path here...
$AdminUI->breadcrumbpath_add( T_('User groups'), '?ctrl=users' );
$AdminUI->breadcrumbpath_add( $edited_Group->dget('name'), '?ctrl=groups&amp;group_ID='.$edited_Group->ID );

// Display <html><head>...</head> section! (Note: should be done early if actions do not redirect)
$AdminUI->disp_html_head();

// Display title, menu, messages, etc. (Note: messages MUST be displayed AFTER the actions)
$AdminUI->disp_body_top();

// Begin payload block:
$AdminUI->disp_payload_begin();

// Display VIEW:
switch( $action )
{
	case 'nil':
		// Do nothing
		break;
	case 'delete':
			// We need to ask for confirmation:
			$edited_Group->confirm_delete(
					sprintf( T_('Delete group &laquo;%s&raquo;?'), $edited_Group->dget( 'name' ) ),
					'group', $action, get_memorized( 'action' ) );
	default:
		$AdminUI->disp_view( 'users/views/_group.form.php' );
}

// End payload block:
$AdminUI->disp_payload_end();

// Display body bottom, debug info and close </html>:
$AdminUI->disp_global_footer();

/*
 * $Log$
 * Revision 1.7  2010/01/03 17:45:21  fplanque
 * crumbs & stuff
 *
 * Revision 1.6  2010/01/03 12:03:17  fplanque
 * More crumbs...
 *
 * Revision 1.5  2009/12/06 22:55:19  fplanque
 * Started breadcrumbs feature in admin.
 * Work in progress. Help welcome ;)
 * Also move file settings to Files tab and made FM always enabled
 *
 * Revision 1.4  2009/09/26 12:00:43  tblue246
 * Minor/coding style
 *
 * Revision 1.3  2009/09/25 07:33:14  efy-cantor
 * replace get_cache to get_*cache
 *
 * Revision 1.2  2009/09/24 21:05:38  fplanque
 * no message
 *
 *
 */
?>