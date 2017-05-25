<?php
/* new way in avada-child theme? */
	function theme_enqueue_styles() {
	    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'avada-stylesheet' ) );
	}
	add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
	/**  ea_setup
	*  init stuff that we have to init after the main theme is setup.
	*
	*/
	add_action('after_setup_theme', 'ea_setup');
	function ea_setup() {
	 /* do stuff ehre. */
	}
				/*****  change the login screen logo ****/
	function my_login_logo() { ?>
		<style type="text/css">
			body.login div#login h1 a {
				background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/bhmLogo.png);
				padding-bottom: 30px;
				background-size: cover;
				margin-left: 0px;
				margin-bottom: 0px;
				margin-right: 0px;
				height: 105px;
				width: 100%;
			}
		</style>
	<?php }
	add_action( 'login_enqueue_scripts', 'my_login_logo' );
	/***** change admin favicon *****/
	/* add favicons for admin */
	add_action('login_head', 'add_favicon');
	add_action('admin_head', 'add_favicon');

	function add_favicon() {
		$favicon_url = get_stylesheet_directory_uri() . '/images/admin-favicon.ico';
		echo '<link rel="shortcut icon" href="' . $favicon_url . '" />';
	}

	/* function force_ssl($force_ssl, $id = 0) {
	// A list of posts that should be SSL
		$ssl_posts = array(742);

		if(in_array($id, $ssl_posts)) {
			$force_ssl = true;
		}
	    return $force_ssl;
	}
	add_filter('force_ssl' , 'force_ssl', 1, 3); */

?>
