<?php
/**
 * Asset enqueue.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_scripts() {
	wp_enqueue_style(
		'jbportal-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'jbportal-style', get_stylesheet_uri(), array(), JBPORTAL_VERSION );
	wp_enqueue_style( 'jbportal-main', JBPORTAL_URI . 'assets/css/main.css', array( 'jbportal-style' ), JBPORTAL_VERSION );

	wp_enqueue_script( 'jbportal-main', JBPORTAL_URI . 'assets/js/main.js', array( 'jquery' ), JBPORTAL_VERSION, true );

	$script_data = array(
		'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'jbportal_nonce' ),
		'aiEnabled' => get_option( 'jbportal_ai_enabled' ) === '1',
		'i18n'      => array(
			'applying'      => esc_html__( 'Submitting…', 'jbportal' ),
			'applied'       => esc_html__( 'Application sent!', 'jbportal' ),
			'error'         => esc_html__( 'Something went wrong. Please try again.', 'jbportal' ),
			'saved'         => esc_html__( 'Saved to your bookmarks.', 'jbportal' ),
			'removed'       => esc_html__( 'Removed from bookmarks.', 'jbportal' ),
			'load_more'     => esc_html__( 'Load more jobs', 'jbportal' ),
			'loading'       => esc_html__( 'Loading…', 'jbportal' ),
			'no_more'       => esc_html__( 'All jobs loaded.', 'jbportal' ),
			'ai_generating' => esc_html__( 'Generating with AI…', 'jbportal' ),
			'ai_done'       => esc_html__( 'Done! Review and edit as needed.', 'jbportal' ),
			'ai_need_title' => esc_html__( 'Please enter a job title first.', 'jbportal' ),
		),
	);
	$script_data = apply_filters( 'jbportal_script_data', $script_data );
	wp_localize_script( 'jbportal-main', 'jbportal', $script_data );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'jbportal_scripts' );

function jbportal_admin_scripts( $hook ) {
	wp_enqueue_style( 'jbportal-admin', JBPORTAL_URI . 'assets/css/admin.css', array(), JBPORTAL_VERSION );
}
add_action( 'admin_enqueue_scripts', 'jbportal_admin_scripts' );
