<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes('xhtml'); ?>>
<head lang="en">
	<meta charset="UTF-8">
	<title><?php bloginfo('name'); ?><?php wp_title(); ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php wp_get_archives('type=monthly&format=link&limit=12'); ?>
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/style.css">
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/style1.css">
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/media.css">
	<?php wp_head(); ?>
</head>
<body>
<div class="container-fluid">
	<div class="top-header">
		<div class="row">
			<p>EXPLODING ON THE SCENE JANUARY 2017 &#45; NOW IN DEALER <span>PRE-ENROLLMENT</span></p>
		</div>
	</div>
	<div id="menu">
		<div class="row">
			<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
				<div class="logo text-right">
					<a href="<?php bloginfo('home'); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/logo.png" alt=""></a>
				</div>
			</div>
			<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
				<div class="navbar">
					<div class="navbar-inner">
						<?php
						if (function_exists('wp_nav_menu')) {
							wp_nav_menu(array('theme_location' => 'wpj-main-menu', 'menu_id' => 'nav', 'fallback_cb' => 'wpj_default_menu'));
						}
						else {
							wpj_default_menu();
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>

