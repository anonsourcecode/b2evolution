<?php
/**
 * This is the template that displays the calendar
 *
 * THIS FILE IS DEPRECATED. IT IS LEFT AS A STUB FOR OLDER SKINS COMPATIBILITY.
 *
 * This file is not meant to be called directly.
 * It is meant to be called by an include in the _main.php template.
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2005 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package evoskins
 *
 * @author fplanque: Francois PLANQUE.
 * @author gorgeb: Bertrand GORGE / EPISTEMA
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

$Debuglog->add( 'Call to deprecated skin helper _calendar.php', 'deprecated' );

// Call the Calendar plugin WITH BASIC CONFIG:
$Plugins->call_by_code( 'evo_Calr', array(	// Params follow:
		'block_start'=>'',
		'block_end'=>'',
		'title'=>'',			// No title.
	) );


/*
 * $Log$
 * Revision 1.17  2006/04/11 21:22:26  fplanque
 * partial cleanup
 *
 */
?>