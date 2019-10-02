<?php

function _themename_switch_theme() {
	switch_theme( WP_DEFAULT_THEME );
	unset( $_GET['activated'] );
	add_action( 'admin_notices', '_themename_upgrade_notice' );
}
add_action( 'after_switch_theme', '_themename_switch_theme' );

function _themename_upgrade_notice() {
	$message = sprintf( __( 'This theme requires at least WordPress version 4.7. You are running version %s. Please upgrade and try again.', '_themename' ), $GLOBALS['wp_version'] );
	printf( '<div class="error"><p>%s</p></div>', $message );
}

function _themename_customize() {
	wp_die(
		sprintf(
			__( 'This theme requires at least WordPress version 4.7. You are running version %s. Please upgrade and try again.', '_themename' ),
			$GLOBALS['wp_version']
		),
		'',
		array(
			'back_link' => true,
		)
	);
}
add_action( 'load-customize.php', '_themename_customize' );

function _themename_preview() {
	if ( isset( $_GET['preview'] ) ) {
		wp_die( sprintf( __( 'This theme requires at least WordPress version 4.7. You are running version %s. Please upgrade and try again.', '_themename' ), $GLOBALS['wp_version'] ) );
	}
}
add_action( 'template_redirect', '_themename_preview' );
