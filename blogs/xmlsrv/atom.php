<?php
  /*
   * This template generates an Atom feed for the requested blog's latest posts
   * (http://www.mnot.net/drafts/draft-nottingham-atom-format-02.html)
   */
  $skin = '';                   // We don't want this do be displayed in a skin !
	$show_statuses = array();     // Restrict to published posts
	$timestamp_min = '';					// Show past
	$timestamp_max = 'now';				// Hide future
  require dirname(__FILE__)."/../b2evocore/_blog_main.php";
  header("Content-type: application/atom+xml");
  echo '<?xml version="1.0" encoding="utf-8"?'.'>';
?>
<feed version="0.3" xml:lang="<?php bloginfo( 'lang', 'xml' )?>" xmlns="http://purl.org/atom/ns#">
	<title><?php bloginfo( 'name', 'xml' ) ?></title>
	<link rel="alternate" type="text/html" href="<?php bloginfo( 'blogurl', 'xml' ) ?>" />
	<tagline><?php bloginfo( 'shortdesc', 'xml' ) ?></tagline>
	<generator url="http://b2evolution.net/" version="<?php echo $b2_version ?>">b2evolution</generator>
	<modified><?php echo gmdate('Y-m-d\TH:i:s\Z'); ?></modified>
	<?php while( $MainList->get_item() ) {  ?>
	<entry>
		<title type="text/plain" mode="xml"><?php the_title( '', '', false, 'xml' ) ?></title>
		<link rel="alternate" type="text/html" href="<?php permalink_single() ?>" />
		<author>
			<name><?php the_author( 'xml' ) ?></name>
			<url><?php the_author_url( 'xml' ) ?></url>
		</author>
		<id><?php permalink_single() ?></id>
		<modified><?php the_time('Y-m-d\TH:i:s\Z',1,1); ?></modified>
		<issued><?php the_time('Y-m-d\TH:i:s\Z',1,1); ?></issued>
		<content type="text/html" mode="escaped"><![CDATA[<?php
			the_link( '<p>', '</p>' );
			the_content()
		?>]]></content>
	</entry>
	<?php } ?>
</feed>
<?php log_hit(); // log the hit on this page ?>