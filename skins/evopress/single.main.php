<?php
/**
 * This is the main/default page template.
 *
 * For a quick explanation of b2evo 2.0 skins, please start here:
 * {@link http://b2evolution.net/man/skin-structure}
 *
 * The main page template is used to display the blog when no specific page template is available
 * to handle the request (based on $disp).
 *
 * @package evoskins
 * @subpackage evopress
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

if( version_compare( $app_version, '4.0.0-dev' ) < 0 )
{ // Older 2.x skins work on newer 2.x b2evo versions, but newer 2.x skins may not work on older 2.x b2evo versions.
	die( 'This skin is designed for b2evolution 4.0.0 and above. Please <a href="http://b2evolution.net/downloads/index.html">upgrade your b2evolution</a>.' );
}

// This is the main template; it may be used to display very different things.
// Do inits depending on current $disp:
skin_init( $disp );


// -------------------------- HTML HEADER INCLUDED HERE --------------------------
add_headline( <<<HEREDOC
<style type="text/css" media="screen">
	#page.page-left, #page.page-right{background-image:none}
</style>
HEREDOC
);
skin_include( '_html_header.inc.php' );
// Note: You can customize the default HTML header by copying the generic
// /skins/_html_header.inc.php file into the current skin folder.
// -------------------------------- END OF HEADER --------------------------------
?>


<?php
// ------------------------- BODY HEADER INCLUDED HERE --------------------------
skin_include( '_body_header.inc.php' );
// Note: You can customize the default BODY header by copying the generic
// /skins/_body_header.inc.php file into the current skin folder.
// ------------------------------- END OF HEADER --------------------------------
?>


<div class="top_menu">
	<ul>
	<?php
		// ------------------------- "Menu" CONTAINER EMBEDDED HERE --------------------------
		// Display container and contents:
		skin_container( NT_('Menu'), array(
				// The following params will be used as defaults for widgets included in this container:
				'block_start'         => '',
				'block_end'           => '',
				'block_display_title' => false,
				'list_start'          => '',
				'list_end'            => '',
				'item_start'          => '<li>',
				'item_end'            => '</li>',
				'item_title_before'   => '',
				'item_title_after'    => '',
			) );
		// ----------------------------- END OF "Menu" CONTAINER -----------------------------
	?>
	</ul>
</div>


<div id="content" class="widecolumn">


<?php
	// ------------------------- MESSAGES GENERATED FROM ACTIONS -------------------------
	messages( array(
			'block_start' => '<div class="action_messages">',
			'block_end'   => '</div>',
		) );
	// --------------------------------- END OF MESSAGES ---------------------------------
?>


<?php
	// ------------------- PREV/NEXT POST LINKS (SINGLE POST MODE) -------------------
	item_prevnext_links( array(
			'block_start' => '<table class="prevnext_post"><tr>',
			'prev_start'  => '<td>',
			'prev_end'    => '</td>',
			'next_start'  => '<td class="right">',
			'next_end'    => '</td>',
			'block_end'   => '</tr></table>',
		) );
	// ------------------------- END OF PREV/NEXT POST LINKS -------------------------
?>


<?php
// Display message if no post:
display_if_empty();

echo '<div id="styled_content_block">'; // Beginning of posts display
while( $Item = & mainlist_get_item() )
{	// For each blog post, do everything below up to the closing curly brace "}"
	?>

	<?php
		$Item->locale_temp_switch(); // Temporarily switch to post locale (useful for multilingual blogs)
	?>

	<div id="<?php $Item->anchor_id() ?>" class="post post<?php $Item->status_raw() ?>" lang="<?php $Item->lang() ?>">
		<?php
		if( $Item->status != 'published' )
		{
			$Item->status( array( 'format' => 'styled' ) );
		}
		?>
		<h2><?php
			$Item->title( array(
					'link_type' => 'permalink'
				) );
		?></h2>

		<?php
			// ---------------------- POST CONTENT INCLUDED HERE ----------------------
			skin_include( '_item_content.inc.php', array(
					'image_size' => 'fit-400x320',
				) );
			// Note: You can customize the default item content by copying the generic
			// /skins/_item_content.inc.php file into the current skin folder.
			// -------------------------- END OF POST CONTENT -------------------------
		?>

    <?php
      // ------------------------- "Item - Single" CONTAINER EMBEDDED HERE --------------------------
      // WARNING: EXPERIMENTAL -- NOT RECOMMENDED FOR PRODUCTION -- MAY CHANGE DRAMATICALLY BEFORE RELEASE.
      // Display container contents:
      skin_container( /* TRANS: Widget container name */ NT_('Item Single'), array(
          // The following (optional) params will be used as defaults for widgets included in this container:
          // This will enclose each widget in a block:
          'block_start' => '<div class="widget $wi_class$">',
          'block_end' => '</div>',
          // This will enclose the title of each widget:
          'block_title_start' => '<h3>',
          'block_title_end' => '</h3>',
          // If a widget displays a list, this will enclose that list:
          'list_start' => '<ul>',
          'list_end' => '</ul>',
          // This will enclose each item in a list:
          'item_start' => '<li>',
          'item_end' => '</li>',
          // This will enclose sub-lists in a list:
          'group_start' => '<ul>',
          'group_end' => '</ul>',
          // This will enclose (foot)notes:
          'notes_start' => '<div class="notes">',
          'notes_end' => '</div>',
        ) );
      // ----------------------------- END OF "Sidebar" CONTAINER -----------------------------
    ?>

		<p class="postmetadata alt">
			<small>
				<?php
					$Item->author( array(
							'link_text'    => 'only_avatar',
							'link_rel'     => 'nofollow',
							'thumb_size'   => 'crop-top-32x32',
							'thumb_class'  => 'leftmargin',
						) );
				?>
				<?php
					if( $Skin->get_setting( 'display_post_date' ) )
					{	// We want to display the post date:
						$Item->issue_time( array(
								'before'      => /* TRANS: date */ T_('This entry was posted on '),
								'time_format' => 'F jS, Y',
							) );
						$Item->issue_time( array(
								'before'      => /* TRANS: time */ T_('at '),
								'time_format' => '#short_time',
							) );
						$Item->author( array(
								'before'    => T_('by '),
								'link_text' => 'preferredname',
							) );
					}
					else
					{
						$Item->author( array(
								'before'    => T_('This entry was posted by '),
								'link_text' => 'preferredname',
							) );
					}
				?>
				<?php
					$Item->categories( array(
						'before'          => ' '.T_('and is filed under').' ',
						'after'           => '.',
						'include_main'    => true,
						'include_other'   => true,
						'include_external'=> true,
						'link_categories' => true,
					) );
				?>

				<?php
					// List all tags attached to this post:
					$Item->tags( array(
							'before' =>         ' '.T_('Tags').': ',
							'after' =>          ' ',
							'separator' =>      ', ',
						) );
				?>

				<!-- You can follow any responses to this entry through the RSS feed. -->
				<?php
					$Item->edit_link( array( // Link to backoffice for editing
							'before'    => '',
							'after'     => '',
						) );
				?>
			</small>
		</p>

	</div>


	<?php
		// ------------------ FEEDBACK (COMMENTS/TRACKBACKS) INCLUDED HERE ------------------
		skin_include( '_item_feedback.inc.php', array(
				'before_section_title' => '<h3>',
				'after_section_title'  => '</h3>',
				'author_link_text' => 'preferredname',
			) );
		// Note: You can customize the default item feedback by copying the generic
		// /skins/_item_feedback.inc.php file into the current skin folder.
		// ---------------------- END OF FEEDBACK (COMMENTS/TRACKBACKS) ---------------------
	?>

	<?php
	locale_restore_previous();	// Restore previous locale (Blog locale)
}
echo '</div>'; // End of posts display
?>

</div>


<?php
// ------------------------- BODY FOOTER INCLUDED HERE --------------------------
skin_include( '_body_footer.inc.php' );
// Note: You can customize the default BODY footer by copying the
// _body_footer.inc.php file into the current skin folder.
// ------------------------------- END OF FOOTER --------------------------------
?>


<?php
// ------------------------- HTML FOOTER INCLUDED HERE --------------------------
skin_include( '_html_footer.inc.php' );
// Note: You can customize the default HTML footer by copying the
// _html_footer.inc.php file into the current skin folder.
// ------------------------------- END OF FOOTER --------------------------------
?>