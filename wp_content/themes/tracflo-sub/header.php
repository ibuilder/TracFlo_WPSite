<!doctype html>
<html dir="ltr" lang="en-US">

<head>
	<title><?php wp_title(); ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

	<?php wp_head(); ?>

<script async src="https://www.googletagmanager.com/gtag/js?id=UA-124285097-2"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'UA-124285097-2');
</script>
</head>

<body <?php body_class(); ?>>
	<div id="container"><?php
		if ( is_user_logged_in() && ! $wp_query->ticket_public_view ) :
			include( locate_template( 'components/PageHeader/PageHeader.php' ) );
			do_action( 'sewn/notifications/show' );
		endif;
?>
