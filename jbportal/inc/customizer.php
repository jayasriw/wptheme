<?php
/**
 * Customizer settings.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_customize_register( $wp_customize ) {
	$wp_customize->add_panel( 'jbportal_panel', array(
		'title'    => __( 'jbportal Options', 'jbportal' ),
		'priority' => 30,
	) );

	// Colors section.
	$wp_customize->add_section( 'jbportal_colors', array(
		'title' => __( 'Colors', 'jbportal' ),
		'panel' => 'jbportal_panel',
	) );

	$colors = array(
		'jbportal_color_primary'   => array( 'Primary', '#0d9488' ),
		'jbportal_color_secondary' => array( 'Secondary', '#fb7185' ),
		'jbportal_color_dark'      => array( 'Dark', '#0f172a' ),
		'jbportal_color_accent'    => array( 'Accent', '#f59e0b' ),
	);

	foreach ( $colors as $id => $data ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $data[1],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array(
			'label'   => $data[0],
			'section' => 'jbportal_colors',
		) ) );
	}

	// Hero section.
	$wp_customize->add_section( 'jbportal_hero', array(
		'title' => __( 'Homepage Hero', 'jbportal' ),
		'panel' => 'jbportal_panel',
	) );

	$hero_fields = array(
		'jbportal_hero_eyebrow' => array( 'Eyebrow Text', __( 'The smarter way to hire', 'jbportal' ) ),
		'jbportal_hero_title'   => array( 'Hero Title', __( 'Find the job that fits your life.', 'jbportal' ) ),
		'jbportal_hero_subtitle' => array( 'Hero Subtitle', __( 'Discover thousands of open positions across every industry and city.', 'jbportal' ) ),
		'jbportal_hero_cta_text' => array( 'CTA Text', __( 'Post a Job', 'jbportal' ) ),
		'jbportal_hero_cta_url'  => array( 'CTA URL', '#' ),
		'jbportal_hero_stat_1'   => array( 'Stat 1', '25,000+ Open Jobs' ),
		'jbportal_hero_stat_2'   => array( 'Stat 2', '12,500+ Companies' ),
		'jbportal_hero_stat_3'   => array( 'Stat 3', '180,000+ Candidates' ),
	);

	foreach ( $hero_fields as $id => $data ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $data[1],
			'sanitize_callback' => 'wp_kses_post',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $data[0],
			'section' => 'jbportal_hero',
			'type'    => 'text',
		) );
	}

	// Layout.
	$wp_customize->add_section( 'jbportal_layout', array(
		'title' => __( 'Layout', 'jbportal' ),
		'panel' => 'jbportal_panel',
	) );

	$wp_customize->add_setting( 'jbportal_jobs_per_page', array(
		'default'           => 10,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'jbportal_jobs_per_page', array(
		'label'   => __( 'Jobs per page', 'jbportal' ),
		'section' => 'jbportal_layout',
		'type'    => 'number',
	) );

	$wp_customize->add_setting( 'jbportal_footer_copy', array(
		'default'           => sprintf( __( '© %s jbportal. All rights reserved.', 'jbportal' ), gmdate( 'Y' ) ),
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'jbportal_footer_copy', array(
		'label'   => __( 'Footer Copyright', 'jbportal' ),
		'section' => 'jbportal_layout',
		'type'    => 'text',
	) );

	// Contact details.
	$wp_customize->add_section( 'jbportal_contact', array(
		'title' => __( 'Contact', 'jbportal' ),
		'panel' => 'jbportal_panel',
	) );
	foreach ( array(
		'jbportal_contact_phone' => array( 'Phone', '+1 (415) 555-0100' ),
		'jbportal_contact_email' => array( 'Email', 'hello@jbportal.test' ),
		'jbportal_contact_address' => array( 'Address', '500 Market St, San Francisco, CA' ),
	) as $id => $data ) {
		$wp_customize->add_setting( $id, array( 'default' => $data[1], 'sanitize_callback' => 'wp_kses_post' ) );
		$wp_customize->add_control( $id, array( 'label' => $data[0], 'section' => 'jbportal_contact', 'type' => 'text' ) );
	}
}
add_action( 'customize_register', 'jbportal_customize_register' );

/**
 * Output CSS variables based on customizer colors.
 */
function jbportal_inline_css_vars() {
	$primary   = get_theme_mod( 'jbportal_color_primary', '#0d9488' );
	$secondary = get_theme_mod( 'jbportal_color_secondary', '#fb7185' );
	$dark      = get_theme_mod( 'jbportal_color_dark', '#0f172a' );
	$accent    = get_theme_mod( 'jbportal_color_accent', '#f59e0b' );

	$css = ":root{--jb-primary:{$primary};--jb-secondary:{$secondary};--jb-dark:{$dark};--jb-accent:{$accent};}";
	wp_add_inline_style( 'jbportal-main', $css );
}
add_action( 'wp_enqueue_scripts', 'jbportal_inline_css_vars', 20 );
