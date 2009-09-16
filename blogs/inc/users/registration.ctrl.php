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

// Check minimum permission:
$current_User->check_perm( 'options', 'view', true );

$AdminUI->set_path( 'users', 'registration' );

param_action();

switch ( $action )
{
	case 'update':
		// Check permission:
		$current_User->check_perm( 'options', 'edit', true );
		
		// UPDATE general settings:
		param( 'newusers_canregister', 'integer', 0 );
		
		param( 'newusers_grp_ID', 'integer', true );
		
		param_integer_range( 'newusers_level', 0, 9, T_('User level must be between %d and %d.') );
		
		param( 'newusers_mustvalidate', 'integer', 0 );
		
		param( 'newusers_revalidate_emailchg', 'integer', 0 );
		
		param_integer_range( 'user_minpwdlen', 1, 32, T_('Minimum password length must be between %d and %d.') );
		
		param( 'js_passwd_hashing', 'integer', 0 );
		
		$Settings->set_array( array(
									 array( 'newusers_canregister', $newusers_canregister),
									 
									 array( 'newusers_grp_ID', $newusers_grp_ID),
									 
									 array( 'newusers_level', $newusers_level),
									 
									 array( 'newusers_mustvalidate', $newusers_mustvalidate),
									 
		                             array( 'newusers_revalidate_emailchg', $newusers_revalidate_emailchg),
		                             
									 array( 'user_minpwdlen', $user_minpwdlen),
									 
									 array( 'js_passwd_hashing', $js_passwd_hashing) ) );
		
		if( ! $Messages->count('error') )
		{
			if( $Settings->dbupdate() )
			{
				$Messages->add( T_('General settings updated.'), 'success' );
			}
		}
	
		break;
}

// Display <html><head>...</head> section! (Note: should be done early if actions do not redirect)
$AdminUI->disp_html_head();

// Display title, menu, messages, etc. (Note: messages MUST be displayed AFTER the actions)
$AdminUI->disp_body_top();

// Begin payload block:
$AdminUI->disp_payload_begin();

// Display VIEW:
$AdminUI->disp_view( 'users/views/_registration.form.php' );

// End payload block:
$AdminUI->disp_payload_end();

// Display body bottom, debug info and close </html>:
$AdminUI->disp_global_footer();

/*
 * $Log$
 * Revision 1.4  2009/09/15 12:11:23  efy-bogdan
 * Clean structure
 *
 * Revision 1.3  2009/09/15 09:20:49  efy-bogdan
 * Moved the "email validation" and the "security options" blocks to the Users -> Registration tab
 *
 * Revision 1.2  2009/09/15 02:43:35  fplanque
 * doc
 *
 * Revision 1.1  2009/09/14 12:01:00  efy-bogdan
 * User Registration tab
 *
 */
?>