<?php

/**
 * Stay clear of very old WP versions
 */
if ( version_compare( $GLOBALS['wp_version'], '4.7', '<' ) ) {
    require get_template_directory() . '/inc/back-compat.php';
	return;
}

/**
 * Setup some theme features
 */
if ( ! function_exists( '_themename_setup' ) ) :
    function _themename_setup() {
        load_theme_textdomain( '_themename', get_template_directory() . '/languages' );

        /**
         * Choose between features that you need with this project
         */
        // add_theme_support( 'post-formats', [ 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' ] );
        // add_theme_support( 'post-thumbnails', [ 'post ] );
        // set_post_thumbnail_size( 1568, 9999 );
        // add_theme_support( 'custom-background' );
        // add_theme_support( 'custom-header' );
        // add_theme_support( 'custom-logo' );
        // add_theme_support( 'automatic-feed-links' );
        // add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );
        // add_theme_support( 'title-tag' );
        // add_theme_support( 'customize-selective-refresh-widgets' );
        // add_theme_support( 'align-wide' );
        // add_theme_support( 'editor-styles' );
        // add_editor_style( 'style-editor.css' );
        // add_theme_support( 'wp-block-styles' );
        // add_theme_support( 'editor-font-sizes' );
        // add_theme_support( 'editor-color-palette' );
        // add_theme_support( 'responsive-embeds' );

        register_nav_menus(
			array(
				'menu-1' => __( 'Primary', '_themename' ),
				'footer' => __( 'Footer Menu', '_themename' ),
				'social' => __( 'Social Links Menu', '_themename' ),
			)
		);
    }
endif;
add_action( 'after_setup_theme', '_themename_setup' );

/**
 * Register widget areas
 */
function _themename_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Footer', '_themename' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Add widgets here to appear in your footer.', '_themename' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', '_themename_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function _themename_assets() {
    wp_enqueue_style( '_themename-stylesheet', get_template_directory_uri() . '/dist/css/bundle.css', [], filemtime( get_theme_file_path( '/dist/css/bundle.css' ) ), 'all' );

    wp_enqueue_script( '_themename-scripts', get_template_directory_uri() . '/dist/js/bundle.js', [], filemtime( get_theme_file_path( '/dist/js/bundle.js' ) ), true );
}
add_action('wp_enqueue_scripts', '_themename_assets');

/**
 * Remove admin menu items
 */
function remove_menus() {
	remove_menu_page( 'edit-comments.php' ); // Comments.
}
add_action( 'admin_menu', 'remove_menus' );

/**
 * Fix skip link focus in IE11.
 *
 * This does not enqueue the script because it is tiny and because it is only for IE11,
 * thus it does not warrant having an entire dedicated blocking script being loaded.
 *
 * @link https://git.io/vWdr2
 */
function twentynineteen_skip_link_focus_fix() {
	// The following is minified via `terser --compress --mangle -- js/skip-link-focus-fix.js`.
	?>
	<script>
	/(trident|msie)/i.test(navigator.userAgent)&&document.getElementById&&window.addEventListener&&window.addEventListener("hashchange",function(){var t,e=location.hash.substring(1);/^[A-z0-9_-]+$/.test(e)&&(t=document.getElementById(e))&&(/^(?:a|select|input|button|textarea)$/i.test(t.tagName)||(t.tabIndex=-1),t.focus())},!1);
	</script>
	<?php
}
add_action( 'wp_print_footer_scripts', 'twentynineteen_skip_link_focus_fix' );
