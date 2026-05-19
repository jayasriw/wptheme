<?php
/**
 * jbportal theme functions
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JBPORTAL_VERSION', '1.0.0' );
define( 'JBPORTAL_DIR', trailingslashit( get_template_directory() ) );
define( 'JBPORTAL_URI', trailingslashit( get_template_directory_uri() ) );

/**
 * Theme setup: supports, menus, image sizes, text domain.
 */
function jbportal_setup() {
	load_theme_textdomain( 'jbportal', JBPORTAL_DIR . 'languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 60,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'custom-background' );
	add_theme_support( 'custom-header' );
	add_theme_support( 'woocommerce' );

	add_image_size( 'jbportal-job-thumb', 120, 120, true );
	add_image_size( 'jbportal-company-logo', 240, 160, true );
	add_image_size( 'jbportal-hero', 1920, 720, true );

	register_nav_menus( array(
		'primary'   => esc_html__( 'Primary Menu', 'jbportal' ),
		'footer'    => esc_html__( 'Footer Menu', 'jbportal' ),
		'dashboard' => esc_html__( 'Dashboard Menu', 'jbportal' ),
	) );
}
add_action( 'after_setup_theme', 'jbportal_setup' );

/**
 * Content width.
 */
function jbportal_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'jbportal_content_width', 1200 );
}
add_action( 'after_setup_theme', 'jbportal_content_width', 0 );

/**
 * Sidebars and footer widget areas.
 */
function jbportal_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Main Sidebar', 'jbportal' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Default sidebar shown on blog and pages.', 'jbportal' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Jobs Sidebar', 'jbportal' ),
		'id'            => 'sidebar-jobs',
		'description'   => esc_html__( 'Sidebar shown on the jobs archive and single job pages.', 'jbportal' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	for ( $i = 1; $i <= 4; $i++ ) {
		register_sidebar( array(
			/* translators: %d: footer widget area number */
			'name'          => sprintf( esc_html__( 'Footer Column %d', 'jbportal' ), $i ),
			'id'            => 'footer-' . $i,
			'before_widget' => '<section id="%1$s" class="widget footer-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		) );
	}
}
add_action( 'widgets_init', 'jbportal_widgets_init' );

require JBPORTAL_DIR . 'inc/enqueue.php';
require JBPORTAL_DIR . 'inc/post-types.php';
require JBPORTAL_DIR . 'inc/taxonomies.php';
require JBPORTAL_DIR . 'inc/meta-boxes.php';
require JBPORTAL_DIR . 'inc/customizer.php';
require JBPORTAL_DIR . 'inc/template-functions.php';
require JBPORTAL_DIR . 'inc/widgets.php';
require JBPORTAL_DIR . 'inc/shortcodes.php';
require JBPORTAL_DIR . 'inc/ajax.php';
require JBPORTAL_DIR . 'inc/dashboard.php';
require JBPORTAL_DIR . 'inc/applications.php';
