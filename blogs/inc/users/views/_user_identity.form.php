<?php
/**
 * This file implements the UI view for the user properties.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2009 by Francois PLANQUE - {@link http://fplanque.net/}
 * Parts of this file are copyright (c)2004-2006 by Daniel HAHLER - {@link http://thequod.de/contact}.
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
 *
 * Daniel HAHLER grants Francois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package admin
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author efy-maxim: Evo Factory / Maxim.
 * @author fplanque: Francois PLANQUE
 * @author blueyed: Daniel HAHLER
 *
 * @version $Id$
 */

if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

load_class( 'regional/model/_country.class.php', 'Country' );

/**
 * @var instance of GeneralSettings class
 */
global $Settings;
/**
 * @var instance of UserSettings class
 */
global $UserSettings;
/**
 * @var instance of User class
 */
global $edited_User;
/**
 * @var instance of User class
 */
global $current_User;
/**
 * @var current action
 */
global $action;
/**
 * @var user permission, if user is only allowed to edit his profile
 */
global $user_profile_only;


// Begin payload block:
$this->disp_payload_begin();

$Form = & new Form( NULL, 'user_checkchanges' );

if( !$user_profile_only )
{
	$Form->global_icon( T_('Compose message'), 'comments', '?ctrl=threads&action=new&user_login='.$edited_User->login );
	$Form->global_icon( ( $action != 'view' ? T_('Cancel editing!') : T_('Close user profile!') ), 'close', regenerate_url( 'user_ID,action,ctrl', 'ctrl=users' ) );
}

if( $edited_User->ID == 0 )
{	// Creating new user:
	$Form->begin_form( 'fform', T_('Edit user identity') );
}
else
{	// Editing existing user:
	$Form->begin_form( 'fform', sprintf( T_('Edit %s identity'), $edited_User->dget('fullname').' ['.$edited_User->dget('login').']' ) );
}

$Form->hidden_ctrl();
$Form->hidden( 'user_tab', 'identity' );
$Form->hidden( 'identity_form', '1' );

$Form->hidden( 'user_ID', $edited_User->ID );

	/***************  User permissions  **************/

$Form->begin_fieldset( T_('User permissions'), array( 'class'=>'fieldset clear' ) );

$edited_User->get_Group();

$has_full_access = $current_User->check_perm( 'users', 'edit' );

if( $edited_User->ID != 1 && $has_full_access )
{	// This is not Admin and we're not restricted: we're allowed to change the user group:
	$chosengroup = ( $edited_User->Group === NULL ) ? $Settings->get('newusers_grp_ID') : $edited_User->Group->ID;
	$GroupCache = & get_GroupCache();
	$Form->select_object( 'edited_user_grp_ID', $chosengroup, $GroupCache, T_('User group') );
}
else
{
	echo '<input type="hidden" name="edited_user_grp_ID" value="'.$edited_User->Group->ID.'" />';
	$Form->info( T_('User group'), $edited_User->Group->dget('name') );
}

$field_note = '[0 - 10] '.sprintf( T_('See <a %s>online manual</a> for details.'), 'href="http://manual.b2evolution.net/User_levels"' );
if( $action != 'view' && $has_full_access )
{
	$Form->text_input( 'edited_user_level', $edited_User->get('level'), 2, T_('User level'), $field_note, array( 'required' => true ) );
}
else
{
	$Form->info_field( T_('User level'), $edited_User->get('level'), array( 'note' => $field_note ) );
}

$Form->end_fieldset();

	/***************  Email communications  **************/

$Form->begin_fieldset( T_('Email communications') );

$email_fieldnote = '<a href="mailto:'.$edited_User->get('email').'">'.get_icon( 'email', 'imgtag', array('title'=>T_('Send an email')) ).'</a>';

if( $action != 'view' )
{ // We can edit the values:

	$Form->text_input( 'edited_user_email', $edited_User->email, 30, T_('Email'), $email_fieldnote, array( 'maxlength' => 100, 'required' => true ) );
	if( $has_full_access )
	{ // user has "edit users" perms:
		$Form->checkbox( 'edited_user_validated', $edited_User->get('validated'), T_('Validated email'), T_('Has this email address been validated (through confirmation email)?') );
	}
	else
	{ // info only:
		$Form->info( T_('Validated email'), ( $edited_User->get('validated') ? T_('yes') : T_('no') ), T_('Has this email address been validated (through confirmation email)?') );
	}
	$Form->checkbox( 'edited_user_allow_msgform', $edited_User->get('allow_msgform'), T_('Message form'), T_('Check this to allow receiving emails through a message form.') );
	$Form->checkbox( 'edited_user_notify', $edited_User->get('notify'), T_('Notifications'), T_('Check this to receive a notification whenever someone else comments on one of <strong>your</strong> posts.') );

}
else
{ // display only

	$Form->info( T_('Email'), $edited_User->get('email'), $email_fieldnote );
	$Form->info( T_('Validated email'), ( $edited_User->get('validated') ? T_('yes') : T_('no') ), T_('Has this email address been validated (through confirmation email)?') );
	$Form->info( T_('Message form'), ($edited_User->get('allow_msgform') ? T_('yes') : T_('no')) );
	$Form->info( T_('Notifications'), ($edited_User->get('notify') ? T_('yes') : T_('no')) );

  }

$Form->end_fieldset();

	/***************  Identity  **************/

$Form->begin_fieldset( T_('Identity') );

if( $action != 'view' )
{   // We can edit the values:

	$Form->text_input( 'edited_user_login', $edited_User->login, 20, T_('Login'), '', array( 'required' => true ) );
	$Form->text_input( 'edited_user_firstname', $edited_User->firstname, 20, T_('First name'), '', array( 'maxlength' => 50 ) );
	$Form->text_input( 'edited_user_lastname', $edited_User->lastname, 20, T_('Last name'), '', array( 'maxlength' => 50 ) );

	$nickname_editing = $Settings->get( 'nickname_editing' );
	if( ( $nickname_editing == 'edited-user' && $edited_User->ID == $current_User->ID ) || ( $nickname_editing != 'hidden' && $has_full_access ) )
	{
		$Form->text_input( 'edited_user_nickname', $edited_User->nickname, 20, T_('Nickname'), '', array( 'maxlength' => 50, 'required' => true ) );
	}
	else
	{
		$Form->hidden( 'edited_user_nickname', $edited_User->nickname );
	}

	$Form->select( 'edited_user_idmode', $edited_User->get( 'idmode' ), array( &$edited_User, 'callback_optionsForIdMode' ), T_('Identity shown') );

	$CountryCache = & get_CountryCache();
	$Form->select_input_object( 'edited_user_ctry_ID', $edited_User->ctry_ID, $CountryCache, 'Country', array( 'required' => !$has_full_access, 'allow_none' => $has_full_access ) );

	$Form->checkbox( 'edited_user_showonline', $edited_User->get('showonline'), T_('Show online'), T_('Check this to be displayed as online when visiting the site.') );
}
else
{ // display only

	$Form->info( T_('Avatar'), $edited_User->get_avatar_imgtag() );

	$Form->info( T_('Login'), $edited_User->get('login') );
	$Form->info( T_('First name'), $edited_User->get('firstname') );
	$Form->info( T_('Last name'), $edited_User->get('lastname') );
	$Form->info( T_('Nickname'), $edited_User->get('nickname') );
	$Form->info( T_('Identity shown'), $edited_User->get('preferredname') );
	$Form->info( T_('Country'), $edited_User->get_country_name() );
	$Form->info( T_('Show online'), ($edited_User->get('showonline')) ? T_('yes') : T_('no') );
	$Form->info( T_('Multiple sessions'), ($UserSettings->get('login_multiple_sessions', $edited_User->ID) ? T_('Allowed') : T_('Forbidden')) );
}

$Form->end_fieldset();

	/***************  Password  **************/

if( empty( $edited_User->ID ) && $action != 'view' )
{ // We can edit the values:

	$Form->begin_fieldset( T_('Password') );
		$Form->password_input( 'edited_user_pass1', '', 20, T_('New password'), array( 'maxlength' => 50, 'required' => true, 'autocomplete'=>'off' ) );
		$Form->password_input( 'edited_user_pass2', '', 20, T_('Confirm new password'), array( 'note'=>sprintf( T_('Minimum length: %d characters.'), $Settings->get('user_minpwdlen') ), 'maxlength' => 50, 'required' => true, 'autocomplete'=>'off' ) );
	$Form->end_fieldset();
}

	/***************  Multiple sessions  **************/

if( empty( $edited_User->ID ) && $action != 'view' )
{	// New user will be created with default multiple_session setting

	$multiple_sessions = $Settings->get( 'multiple_sessions' );
	if( $multiple_sessions == 'userset_default_yes' || ( $has_full_access && $multiple_sessions == 'adminset_default_yes' ) )
	{
		$Form->hidden( 'edited_user_set_login_multiple_sessions', 1 );
	}
	else
	{
		$Form->hidden( 'edited_user_set_login_multiple_sessions', 0 );
	}
}

	/***************  Additional info  **************/


$Form->begin_fieldset( T_('Additional info') );

if( $edited_User->ID != 0 )
{ // We're NOT creating a new user:
	$Form->info_field( T_('ID'), $edited_User->ID );

	$Form->info_field( T_('Posts'), $edited_User->get_num_posts() );

	$Form->info_field( T_('Created on'), $edited_User->dget('datecreated') );
	$Form->info_field( T_('From IP'), $edited_User->dget('ip') );
	$Form->info_field( T_('From Domain'), $edited_User->dget('domain') );
	$Form->info_field( T_('With Browser'), $edited_User->dget('browser') );
}

if( ($url = $edited_User->get('url')) != '' )
{
	if( !preg_match('#://#', $url) )
	{
		$url = 'http://'.$url;
	}
	$url_fieldnote = '<a href="'.$url.'" target="_blank">'.get_icon( 'play', 'imgtag', array('title'=>T_('Visit the site')) ).'</a>';
}
else
	$url_fieldnote = '';

if( $edited_User->get('icq') != 0 )
	$icq_fieldnote = '<a href="http://wwp.icq.com/scripts/search.dll?to='.$edited_User->get('icq').'" target="_blank">'.get_icon( 'play', 'imgtag', array('title'=>T_('Search on ICQ.com')) ).'</a>';
else
	$icq_fieldnote = '';

if( $edited_User->get('aim') != '' )
	$aim_fieldnote = '<a href="aim:goim?screenname='.$edited_User->get('aim').'&amp;message=Hello">'.get_icon( 'play', 'imgtag', array('title'=>T_('Instant Message to user')) ).'</a>';
else
	$aim_fieldnote = '';


if( $action != 'view' )
{ // We can edit the values:

	$Form->text_input( 'edited_user_url', $edited_User->url, 30, T_('URL'), $url_fieldnote, array( 'maxlength' => 100 ) );
	$Form->text_input( 'edited_user_icq', $edited_User->icq, 30, T_('ICQ'), $icq_fieldnote, array( 'maxlength' => 10 ) );
	$Form->text_input( 'edited_user_aim', $edited_User->aim, 30, T_('AIM'), $aim_fieldnote, array( 'maxlength' => 50 ) );
	$Form->text_input( 'edited_user_msn', $edited_User->msn, 30, T_('MSN IM'), '', array( 'maxlength' => 100 ) );
	$Form->text_input( 'edited_user_yim', $edited_User->yim, 30, T_('YahooIM'), '', array( 'maxlength' => 50 ) );

}
else
{ // display only

	$Form->info( T_('URL'), $edited_User->get('url'), $url_fieldnote );
	$Form->info( T_('ICQ'), $edited_User->get('icq', 'formvalue'), $icq_fieldnote );
	$Form->info( T_('AIM'), $edited_User->get('aim'), $aim_fieldnote );
	$Form->info( T_('MSN IM'), $edited_User->get('msn') );
	$Form->info( T_('YahooIM'), $edited_User->get('yim') );

  }

$Form->end_fieldset();

	/***************  Experimental  **************/

$Form->begin_fieldset( T_('Experimental') );

// This totally needs to move into User object
global $DB;

// Get existing userfields:
$userfields = $DB->get_results( '
	SELECT uf_ID, ufdf_ID, ufdf_type, ufdf_name, uf_varchar
		FROM T_users__fields LEFT JOIN T_users__fielddefs ON uf_ufdf_ID = ufdf_ID
	 WHERE uf_user_ID = '.$edited_User->ID.'
	 ORDER BY uf_ID' );

foreach( $userfields as $userfield )
{
	switch( $userfield->ufdf_ID )
	{
		case 10200:
			$field_note = '<a href="aim:goim?screenname='.$userfield->uf_varchar.'&amp;message=Hello">'.get_icon( 'play', 'imgtag', array('title'=>T_('Instant Message to user')) ).'</a>';
			break;

		case 10300:
			$field_note = '<a href="http://wwp.icq.com/scripts/search.dll?to='.$userfield->uf_varchar.'" target="_blank">'.get_icon( 'play', 'imgtag', array('title'=>T_('Search on ICQ.com')) ).'</a>';
			break;

		default:
			if( $userfield->ufdf_ID >= 100000 && $userfield->ufdf_ID < 200000 )
			{
				$url = $userfield->uf_varchar;
				if( !preg_match('#://#', $url) )
				{
					$url = 'http://'.$url;
				}
				$field_note = '<a href="'.$url.'" target="_blank">'.get_icon( 'play', 'imgtag', array('title'=>T_('Visit the site')) ).'</a>';
			}
			else
			{
				$field_note = '';
			}
	}

	$uf_val = param( 'uf_'.$userfield->uf_ID, 'string', NULL );
	if( is_null( $uf_val ) )
	{	// No value submitted yet, get DB val:
		$uf_val = $userfield->uf_varchar;
	}

	// Display existing field:
	$Form->text_input( 'uf_'.$userfield->uf_ID, $uf_val, 50, $userfield->ufdf_name, $field_note, array( 'maxlength' => 255 ) );
}

// Get list of possible field types:
// TODO: use userfield manipulation functions
$userfielddefs = $DB->get_results( '
	SELECT ufdf_ID, ufdf_type, ufdf_name
		FROM T_users__fielddefs
	 ORDER BY ufdf_ID' );
// New fields:
// TODO: JS for adding more than 3 at a time.
for( $i=1; $i<=3; $i++ )
{
	$label = '<select name="new_uf_type_'.$i.'"><option value="">Add field...</option><optgroup label="Instant Messaging">';
	foreach( $userfielddefs as $fielddef )
	{
		// check for group header:
		switch( $fielddef->ufdf_ID )
		{
			case 50000:
				$label .= "\n".'</optgroup><optgroup label="Phone">';
				break;
			case 100000:
				$label .= "\n".'</optgroup><optgroup label="Web">';
				break;
			case 200000:
				$label .= "\n".'</optgroup><optgroup label="Organization">';
				break;
			case 300000:
				$label .= "\n".'</optgroup><optgroup label="Address">';
				break;
		}
		$label .= "\n".'<option value="'.$fielddef->ufdf_ID.'"';
		if( param( 'new_uf_type_'.$i, 'string', '' ) == $fielddef->ufdf_ID )
		{	// We had selected this type before getting an error:
			$label .= ' selected="selected"';
		}
		$label .= '>'.$fielddef->ufdf_name.'</option>';
	}
	$label .= '</optgroup></select>';

	$Form->text_input( 'new_uf_val_'.$i, param( 'new_uf_val_'.$i, 'string', '' ), 50, $label, '', array('maxlength' => 255, 'clickable_label'=>false) );
}

$Form->end_fieldset();

	/***************  Buttons  **************/

if( $action != 'view' )
{ // Edit buttons
	$Form->buttons( array(
		array( '', 'actionArray[update]', T_('Save !'), 'SaveButton' ),
		array( 'reset', '', T_('Reset'), 'ResetButton' ),
		// dh> TODO: Non-Javascript-confirm before trashing all settings with a misplaced click.
		array( 'type' => 'submit', 'name' => 'actionArray[default_settings]', 'value' => T_('Restore defaults'), 'class' => 'ResetButton',
			'onclick' => "return confirm('".TS_('This will reset all your user settings.').'\n'.TS_('This cannot be undone.').'\n'.TS_('Are you sure?')."');" ),
	) );
}


$Form->end_form();

// End payload block:
$this->disp_payload_end();


/*
 * $Log$
 * Revision 1.5  2009/11/21 13:39:05  efy-maxim
 * 'Cancel editing' fix
 *
 * Revision 1.4  2009/11/21 13:31:59  efy-maxim
 * 1. users controller has been refactored to users and user controllers
 * 2. avatar tab
 * 3. jQuery to show/hide custom duration
 *
 * Revision 1.3  2009/10/28 14:26:24  efy-maxim
 * allow selection of None/NULL for country
 *
 * Revision 1.2  2009/10/28 13:41:58  efy-maxim
 * default multiple sessions settings
 *
 * Revision 1.1  2009/10/28 10:02:42  efy-maxim
 * rename some php files
 *
 */
?>