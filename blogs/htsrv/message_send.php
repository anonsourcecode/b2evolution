<?php
/**
 * This file sends an email to the user!
 *
 * It's the form action for {@link _msgform.php}.
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2005 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package htsrv
 * @author Jeff Bearer - {@link http://www.jeffbearer.com/} + blueyed, fplanque
 *
 * @todo Plugin hook.
 * @todo Respect/provide user profile setting if he wants to be available for e-mail through msgform.
 */

/**
 * Includes
 */
require_once dirname(__FILE__).'/../conf/_config.php';

require_once $inc_path.'_main.inc.php';


// TODO: Flood protection (Use Hit class to prevent mass mailings to members..)

if( param( 'optout_cmt_email', 'string', '' ) )
{ // an anonymous commentator wants to opt-out from receiving mails through a message form:

	if( param( 'req_ID', 'string', '' ) )
	{ // clicked on link from e-mail
		if( $req_ID == $Session->get( 'core.msgform.optout_cmt_reqID' )
		    && $optout_cmt_email == $Session->get( 'core.msgform.optout_cmt_email' ) )
		{
			$DB->query( '
				UPDATE T_comments
				   SET comment_allow_msgform = 0
				 WHERE comment_author_email = '.$DB->quote($optout_cmt_email) );

			$Messages->add( T_('All your comments have been marked not to allow emailing you through a message form.'), 'success' );

			$Session->delete('core.msgform.optout_cmt_email');
		}
		else
		{
			$Messages->add( T_('The request not to receive emails through a message form for your comments failed.'), 'error' );
		}

		$Messages->display();

		debug_info();
		exit;
	}

	$req_ID = generate_random_key(32);
	$Session->set( 'core.msgform.optout_cmt_email', $optout_cmt_email );
	$Session->set( 'core.msgform.optout_cmt_reqID', $req_ID );

	$message = sprintf( T_("We have received a request that you do not want to receive emails through\na message form on your comments anymore.\n\nTo confirm that this request is from you, please click on the following link:") )
		."\n\n"
		.$htsrv_url.'message_send.php?optout_cmt_email='.$optout_cmt_email.'&req_ID='.$req_ID
		."\n\n"
		.T_('Please note:')
		.' '.T_('For security reasons the link is only valid for your current session (by means of your session cookie).')
		."\n\n"
		.T_('If it was not you that requested this, simply ignore this mail.');

	send_mail( $optout_cmt_email, T_('Confirm opt-out for emails through message form'), $message );

	echo T_('An email has been sent to you, with a link to confirm your request not to receive emails through the comments you have made on this blog.');

	debug_info();
	exit;
}


// Getting GET or POST parameters:
param( 'blog', 'integer', '' );
param( 'recipient_id', 'integer', '' );
param( 'post_id', 'integer', '' );
param( 'comment_id', 'integer', '' );
param( 'sender_name', 'string', '' );
param( 'sender_address', 'string', '' );
param( 'subject', 'string', '' );
param( 'message', 'string', '' );

// Getting current blog info:
$Blog = Blog_get_by_ID( $blog ); /* TMP: */ $blogparams = get_blogparams_by_ID( $blog );

// Prevent register_globals injection!
$recipient_address = '';
$recipient_User = NULL;
$Comment = NULL;

if( ! empty( $recipient_id ) )
{ // Get the email address for the recipient if a member.
	$recipient_User = & $UserCache->get_by_ID( $recipient_id );

	if( empty($recipient_User->allow_msgform) )
	{ // should be prevented by UI
		debug_die( 'Invalid recipient!' );
	}

	$recipient_address = trim($recipient_User->get('preferredname')) . ' <' . $recipient_User->get('email') . '>';
	// Change the locale so the email is in the recipients language
	locale_temp_switch($recipient_User->locale);
}
elseif( ! empty( $comment_id ) )
{ // Get the email address for the recipient if a visiting commenter.
	$row = $DB->get_row(
		'SELECT *
		   FROM T_comments
		  WHERE comment_ID = '.$comment_id, ARRAY_A );
	$Comment = new Comment( $row );

	if( empty($Comment->allow_msgform) )
	{ // should be prevented by UI
		debug_die( 'Invalid recipient!' );
	}

	$recipient_address = trim($Comment->author) . ' <' . $Comment->author_email . '>';
}

if( empty($recipient_address) )
{ // should be prevented by UI
	debug_die( 'No recipient specified!' );
}


// Build message footer:
$message_footer = '';
if( !empty( $comment_id ) )
{
	$message_footer .= T_('Message sent from your comment:') . "\n"
		.url_add_param( $Blog->get('url'), 'p='.$post_id.'&c=1&tb=1&pb=1#'.$comment_id, '&' )
		."\n\n";
}
elseif( !empty( $post_id ) )
{
	$message_footer .= T_('Message sent from your post:') . "\n"
		.url_add_param( $Blog->get('url'), 'p='.$post_id.'&c=1&tb=1&pb=1', '&' )
		."\n\n";
}

// opt-out links:
if( $recipient_User )
{
	$message_footer .= T_("You can edit your profile to not reveive mails through a form:")
		."\n".url_add_param( str_replace( '&amp;', '&', $Blog->get('url') ), 'disp=profile', '&' );
}
elseif( $Comment )
{
	$message_footer .= T_("Click on the following link to not receive e-mails on your comments\nfor this e-mail address anymore:")
		."\n".$htsrv_url.'message_send.php?optout_cmt_email='.rawurlencode($Comment->author_email);
}


// Trigger event: a Plugin could add a $category="error" message here..
$Plugins->trigger_event( 'MessageFormSent', array(
	'recipient_ID' => & $recipient_id, 'item_ID' => $post_id, 'comment_ID' => $comment_id,
	'message' => & $message,
	'message_footer' => & $message_footer,
	) );


$Messages->display();

if( $Messages->count( 'error' ) )
{
	return;
}


$message = $message
	."\n\n-- \n"
	.sprintf( T_('This message was sent via the messaging system on %s.'), $Blog->name ).".\n"
	.$Blog->get('url') . "\n\n"
	.$message_footer;


// Send mail
send_mail( $recipient_address, $subject, $message, "$sender_name <$sender_address>");

if( isset($recipient_User) )
{
	// restore the locale to the readers language
	locale_restore_previous();
}


// Set Messages into user's session, so they get restored on the next page (after redirect):
// fp>> TODO: this was better called $Messages !
$action_Log = new Log();
$action_Log->add( T_('Your message has been sent as email to the user.'), 'success' );
$Session->set( 'Messages', $action_Log );

// Header redirection
header_nocache();
header_redirect();


// Plugins should cleanup their temporary data here:
$Plugins->trigger_event( 'MessageFormSentCleanup' );

/*
 * $Log$
 * Revision 1.25  2006/04/11 21:22:25  fplanque
 * partial cleanup
 *
 */
?>