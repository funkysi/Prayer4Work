<?php 
/* @package WordPress
 * @subpackage Desk
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head  profile="http://gmpg.org/xfn/11">
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />

<title><?php bloginfo('name'); ?><?php wp_title(); ?></title>
<meta content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" http-equiv="Content-Type" />

<!-- leave this for stats please -->
<link rel="shortcut icon" type="image/x-icon" href="<?php bloginfo('template_url'); ?>/favicon.ico" />
<link href="<?php bloginfo('stylesheet_url'); ?>" media="screen" rel="stylesheet" type="text/css" />

<?php if (is_numeric(get_option('desk_headerfontsize'))){?> 
<style type="text/css">
#header h1, #header h1 a{
	font-size: <?php echo(get_option('desk_headerfontsize')); ?>px;
}
</style>
<?php } ?>

	<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
	
<?php if (get_option('desk_header_code') != ''){ echo stripslashes(get_option('desk_header_code')); }?>	
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>


<div id="container">
<div id="navdiv">


		
					<?php 
			$margs = array(
			'depth' => '2',
			'menu_class' => 'nav',
			'container_id' => 'navdiv2');
	
		
			wp_nav_menu($margs);
			?>

	<?php if (get_option('desk_hiderss') == '') { ?>
	<div id="rss">
	
		<a href="<?php bloginfo('rss_url'); ?>" title="rss feed"><img alt="feed icon" height="28" src="<?php bloginfo('template_url'); ?>/images/feed-icon.png" width="28" /></a>
	</div><!-- /rss --> <?php } ?>
	<div class="clear"></div><!-- /clear -->
<!-- /navwrap -->
</div><!-- /navdiv -->

<div class="push"></div>

<div id="wrapper">
	<div id="header">
		<h1><a href="<?php echo home_url();  ?>"><img src="http://www.prayer4work.com/wp-content/uploads/work5.gif" width="150px" alt="<?php bloginfo('name'); ?> Prayer for Work"><img src="http://www.prayer4work.com/wp-content/uploads/Untitled.png" alt="<?php bloginfo('name'); ?> Prayer for Work"><!----></a></h1>
		<h2><?php bloginfo('description'); ?></h2>
	</div>
	<div id="content">
