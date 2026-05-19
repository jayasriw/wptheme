<?php
/**
 * Template helpers and query filters.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Salary formatting for a job listing.
 */
function jbportal_get_salary( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$min     = get_post_meta( $post_id, '_job_salary_min', true );
	$max     = get_post_meta( $post_id, '_job_salary_max', true );
	$cur     = get_post_meta( $post_id, '_job_salary_currency', true ) ?: '$';
	$period  = get_post_meta( $post_id, '_job_salary_period', true ) ?: 'year';

	if ( ! $min && ! $max ) {
		return '';
	}
	if ( $min && $max ) {
		return sprintf( '%s%s – %s%s / %s', $cur, number_format_i18n( $min ), $cur, number_format_i18n( $max ), $period );
	}
	$amount = $min ?: $max;
	return sprintf( '%s%s / %s', $cur, number_format_i18n( $amount ), $period );
}

function jbportal_get_job_location( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$meta    = get_post_meta( $post_id, '_job_location', true );
	if ( $meta ) {
		return $meta;
	}
	$terms = wp_get_post_terms( $post_id, 'job_location', array( 'fields' => 'names' ) );
	return $terms ? implode( ', ', $terms ) : '';
}

function jbportal_get_job_company( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$name    = get_post_meta( $post_id, '_job_company', true );
	$cid     = (int) get_post_meta( $post_id, '_job_company_id', true );
	if ( $cid ) {
		$name = $name ?: get_the_title( $cid );
		return array( 'name' => $name, 'id' => $cid, 'url' => get_permalink( $cid ) );
	}
	return array( 'name' => $name, 'id' => 0, 'url' => '' );
}

function jbportal_job_type_badge( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$types   = get_the_terms( $post_id, 'job_type' );
	if ( ! $types || is_wp_error( $types ) ) {
		return '';
	}
	$out = '';
	foreach ( $types as $t ) {
		$slug = sanitize_html_class( $t->slug );
		$out .= sprintf( '<a href="%s" class="jb-badge jb-badge-%s">%s</a>', esc_url( get_term_link( $t ) ), esc_attr( $slug ), esc_html( $t->name ) );
	}
	return $out;
}

function jbportal_is_featured( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	return (bool) get_post_meta( $post_id, '_job_featured', true );
}

function jbportal_count_posts( $type, $status = 'publish' ) {
	$counts = wp_count_posts( $type );
	return isset( $counts->{$status} ) ? (int) $counts->{$status} : 0;
}

/**
 * Modify the main jobs archive query to honor filters.
 */
function jbportal_filter_jobs_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'job_listing' ) || $query->is_tax( array( 'job_category', 'job_type', 'job_location', 'job_tag', 'skill' ) ) ) {
		$per_page = (int) get_theme_mod( 'jbportal_jobs_per_page', 10 );
		$query->set( 'posts_per_page', $per_page > 0 ? $per_page : 10 );

		$meta_query = array( 'relation' => 'AND' );
		$tax_query  = array( 'relation' => 'AND' );

		if ( ! empty( $_GET['keyword'] ) ) {
			$query->set( 's', sanitize_text_field( wp_unslash( $_GET['keyword'] ) ) );
		}
		if ( ! empty( $_GET['location'] ) ) {
			$meta_query[] = array(
				'key'     => '_job_location',
				'value'   => sanitize_text_field( wp_unslash( $_GET['location'] ) ),
				'compare' => 'LIKE',
			);
		}
		if ( ! empty( $_GET['type'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'job_type',
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', (array) wp_unslash( $_GET['type'] ) ),
			);
		}
		if ( ! empty( $_GET['category'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'job_category',
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', (array) wp_unslash( $_GET['category'] ) ),
			);
		}
		if ( ! empty( $_GET['remote'] ) ) {
			$meta_query[] = array( 'key' => '_job_remote', 'value' => '1' );
		}
		if ( ! empty( $_GET['salary_min'] ) ) {
			$meta_query[] = array(
				'key'     => '_job_salary_min',
				'value'   => (int) $_GET['salary_min'],
				'type'    => 'NUMERIC',
				'compare' => '>=',
			);
		}

		if ( count( $meta_query ) > 1 ) {
			$query->set( 'meta_query', $meta_query );
		}
		if ( count( $tax_query ) > 1 ) {
			$query->set( 'tax_query', $tax_query );
		}

		// Sort featured first.
		$query->set( 'meta_key', '_job_featured' );
		$query->set( 'orderby', array( 'meta_value' => 'DESC', 'date' => 'DESC' ) );
	}
}
add_action( 'pre_get_posts', 'jbportal_filter_jobs_query' );

/**
 * Body classes.
 */
function jbportal_body_classes( $classes ) {
	if ( is_singular( 'job_listing' ) ) {
		$classes[] = 'jb-single-job';
	}
	if ( is_post_type_archive( 'job_listing' ) ) {
		$classes[] = 'jb-jobs-archive';
	}
	return $classes;
}
add_filter( 'body_class', 'jbportal_body_classes' );

function jbportal_excerpt_more( $more ) {
	return '…';
}
add_filter( 'excerpt_more', 'jbportal_excerpt_more' );

/**
 * Bookmark helpers.
 */
function jbportal_get_user_bookmarks( $user_id ) {
	$ids = get_user_meta( $user_id, 'jbportal_bookmarks', true );
	return is_array( $ids ) ? array_map( 'intval', $ids ) : array();
}

/**
 * Get application count for a job.
 */
function jbportal_get_application_count( $job_id ) {
	$q = new WP_Query( array(
		'post_type'      => 'job_application',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => '_application_job_id',
		'meta_value'     => $job_id,
		'no_found_rows'  => true,
	) );
	return (int) $q->found_posts;
}
