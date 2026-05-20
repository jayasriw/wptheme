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

/**
 * Auto-create required pages on theme activation.
 */
function jbportal_create_pages() {
	$pages = array(
		array(
			'slug'     => 'dashboard',
			'title'    => 'Dashboard',
			'template' => 'templates/template-dashboard.php',
		),
		array(
			'slug'     => 'post-a-job',
			'title'    => 'Post a Job',
			'template' => 'templates/template-post-job.php',
		),
		array(
			'slug'     => 'register',
			'title'    => 'Register',
			'template' => 'templates/template-register.php',
		),
		array(
			'slug'     => 'employer-profile',
			'title'    => 'Employer Profile',
			'template' => 'templates/template-employer-profile.php',
		),
		array(
			'slug'     => 'candidate-profile',
			'title'    => 'Candidate Profile',
			'template' => 'templates/template-candidate-profile.php',
		),
		array(
			'slug'     => 'membership-plans',
			'title'    => 'Membership Plans',
			'content'  => '[jbportal_pricing]',
		),
	);

	foreach ( $pages as $page ) {
		$existing = get_page_by_path( $page['slug'] );
		if ( $existing ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => $page['title'],
			'post_name'   => $page['slug'],
			'post_content' => $page['content'] ?? '',
		) );
		if ( $id && ! is_wp_error( $id ) && ! empty( $page['template'] ) ) {
			update_post_meta( $id, '_wp_page_template', $page['template'] );
		}
	}

	// Flush rewrite rules so CPT and new page slugs resolve correctly.
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'jbportal_create_pages' );

// Also run once on init if pages are missing (handles zip installs where after_switch_theme already fired).
add_action( 'init', function () {
	if ( ! get_option( 'jbportal_pages_created' ) ) {
		jbportal_create_pages();
		update_option( 'jbportal_pages_created', '1' );
	}
}, 99 );

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
require JBPORTAL_DIR . 'inc/roles.php';
require JBPORTAL_DIR . 'inc/cron.php';
require JBPORTAL_DIR . 'inc/alerts.php';
require JBPORTAL_DIR . 'inc/reviews.php';
require JBPORTAL_DIR . 'inc/messaging.php';
require JBPORTAL_DIR . 'inc/woocommerce.php';
require JBPORTAL_DIR . 'inc/social-login.php';
require JBPORTAL_DIR . 'inc/blocks.php';
require JBPORTAL_DIR . 'inc/demo-data.php';
require JBPORTAL_DIR . 'inc/social-share.php';
require JBPORTAL_DIR . 'inc/follow.php';
require JBPORTAL_DIR . 'inc/meetings.php';
require JBPORTAL_DIR . 'inc/invitations.php';
require JBPORTAL_DIR . 'inc/views-counter.php';
require JBPORTAL_DIR . 'inc/email-templates.php';
require JBPORTAL_DIR . 'inc/cookie-notice.php';
require JBPORTAL_DIR . 'inc/mailchimp.php';
require JBPORTAL_DIR . 'inc/account-actions.php';
require JBPORTAL_DIR . 'inc/analytics.php';
require JBPORTAL_DIR . 'inc/elementor.php';
require JBPORTAL_DIR . 'inc/feed.php';
require JBPORTAL_DIR . 'inc/membership.php';
require JBPORTAL_DIR . 'inc/services.php';
require JBPORTAL_DIR . 'inc/wallet.php';
require JBPORTAL_DIR . 'inc/pdf-cv.php';
require JBPORTAL_DIR . 'inc/chatgpt.php';
require JBPORTAL_DIR . 'inc/company-claim.php';
require JBPORTAL_DIR . 'inc/notifications.php';
require JBPORTAL_DIR . 'inc/recaptcha.php';
require JBPORTAL_DIR . 'inc/saved-searches.php';
require JBPORTAL_DIR . 'inc/social-oauth.php';
