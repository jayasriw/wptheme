<?php
/**
 * Shortcodes.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * [jbportal_jobs limit="6" featured="1" category="engineering"]
 */
function jbportal_shortcode_jobs( $atts ) {
	$atts = shortcode_atts( array(
		'limit'    => 6,
		'featured' => 0,
		'category' => '',
		'type'     => '',
	), $atts, 'jbportal_jobs' );

	$args = array(
		'post_type'      => 'job_listing',
		'posts_per_page' => (int) $atts['limit'],
		'no_found_rows'  => true,
	);
	if ( $atts['featured'] ) {
		$args['meta_query'] = array( array( 'key' => '_job_featured', 'value' => '1' ) );
	}
	if ( $atts['category'] || $atts['type'] ) {
		$tax_query = array( 'relation' => 'AND' );
		if ( $atts['category'] ) {
			$tax_query[] = array( 'taxonomy' => 'job_category', 'field' => 'slug', 'terms' => array_map( 'trim', explode( ',', $atts['category'] ) ) );
		}
		if ( $atts['type'] ) {
			$tax_query[] = array( 'taxonomy' => 'job_type', 'field' => 'slug', 'terms' => array_map( 'trim', explode( ',', $atts['type'] ) ) );
		}
		$args['tax_query'] = $tax_query;
	}

	$q = new WP_Query( $args );
	ob_start();
	if ( $q->have_posts() ) {
		echo '<div class="jb-jobs-grid">';
		while ( $q->have_posts() ) {
			$q->the_post();
			get_template_part( 'template-parts/content', 'job' );
		}
		echo '</div>';
		wp_reset_postdata();
	} else {
		echo '<p>' . esc_html__( 'No jobs found.', 'jbportal' ) . '</p>';
	}
	return ob_get_clean();
}
add_shortcode( 'jbportal_jobs', 'jbportal_shortcode_jobs' );

/**
 * [jbportal_companies limit="8"]
 */
function jbportal_shortcode_companies( $atts ) {
	$atts = shortcode_atts( array( 'limit' => 8 ), $atts, 'jbportal_companies' );
	$q = new WP_Query( array(
		'post_type'      => 'company',
		'posts_per_page' => (int) $atts['limit'],
		'no_found_rows'  => true,
	) );
	ob_start();
	if ( $q->have_posts() ) {
		echo '<div class="jb-companies-grid">';
		while ( $q->have_posts() ) {
			$q->the_post();
			get_template_part( 'template-parts/content', 'company' );
		}
		echo '</div>';
		wp_reset_postdata();
	}
	return ob_get_clean();
}
add_shortcode( 'jbportal_companies', 'jbportal_shortcode_companies' );

/**
 * [jbportal_categories limit="8"]
 */
function jbportal_shortcode_categories( $atts ) {
	$atts  = shortcode_atts( array( 'limit' => 8 ), $atts, 'jbportal_categories' );
	$terms = get_terms( array( 'taxonomy' => 'job_category', 'hide_empty' => false, 'number' => (int) $atts['limit'] ) );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}
	$icons = array( 'briefcase', 'palette', 'code', 'megaphone', 'cart', 'wallet', 'heart-pulse', 'graduation-cap' );
	ob_start();
	echo '<div class="jb-categories-grid">';
	$i = 0;
	foreach ( $terms as $t ) {
		$icon = $icons[ $i % count( $icons ) ];
		printf(
			'<a class="jb-category" href="%1$s"><span class="jb-cat-icon" data-icon="%4$s"></span><span class="jb-cat-name">%2$s</span><span class="jb-cat-count">%3$d %5$s</span></a>',
			esc_url( get_term_link( $t ) ),
			esc_html( $t->name ),
			(int) $t->count,
			esc_attr( $icon ),
			esc_html__( 'jobs', 'jbportal' )
		);
		$i++;
	}
	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'jbportal_categories', 'jbportal_shortcode_categories' );

/**
 * [jbportal_job_search]
 */
function jbportal_shortcode_search( $atts ) {
	ob_start();
	get_template_part( 'template-parts/job-search' );
	return ob_get_clean();
}
add_shortcode( 'jbportal_job_search', 'jbportal_shortcode_search' );

/**
 * [jbportal_pricing]
 */
function jbportal_shortcode_pricing( $atts ) {
	$plans = array(
		array( 'name' => __( 'Starter', 'jbportal' ), 'price' => '$0', 'period' => __( '/ month', 'jbportal' ), 'features' => array( '1 active job post', 'Standard listing', '14-day listing', 'Email support' ), 'cta' => __( 'Get Started', 'jbportal' ), 'featured' => false ),
		array( 'name' => __( 'Growth', 'jbportal' ), 'price' => '$49', 'period' => __( '/ month', 'jbportal' ), 'features' => array( '10 active job posts', 'Highlighted listings', '30-day listings', 'Candidate dashboard access', 'Priority support' ), 'cta' => __( 'Start Free Trial', 'jbportal' ), 'featured' => true ),
		array( 'name' => __( 'Enterprise', 'jbportal' ), 'price' => '$199', 'period' => __( '/ month', 'jbportal' ), 'features' => array( 'Unlimited job posts', 'Featured listings', 'Branded company page', 'Resume database access', 'Dedicated manager' ), 'cta' => __( 'Contact Sales', 'jbportal' ), 'featured' => false ),
	);
	ob_start();
	echo '<div class="jb-pricing-grid">';
	foreach ( $plans as $p ) {
		$class = $p['featured'] ? 'jb-plan jb-plan-featured' : 'jb-plan';
		echo '<div class="' . esc_attr( $class ) . '">';
		echo '<h3>' . esc_html( $p['name'] ) . '</h3>';
		echo '<div class="jb-plan-price"><span class="jb-plan-amount">' . esc_html( $p['price'] ) . '</span><span class="jb-plan-period">' . esc_html( $p['period'] ) . '</span></div>';
		echo '<ul class="jb-plan-features">';
		foreach ( $p['features'] as $f ) {
			echo '<li>' . esc_html( $f ) . '</li>';
		}
		echo '</ul>';
		echo '<a href="#" class="jb-btn jb-btn-primary">' . esc_html( $p['cta'] ) . '</a>';
		echo '</div>';
	}
	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'jbportal_pricing', 'jbportal_shortcode_pricing' );

/**
 * [jbportal_testimonials]
 */
function jbportal_shortcode_testimonials( $atts ) {
	$items = array(
		array( 'name' => 'Amara Okafor', 'role' => 'HR Lead, NorthBeam', 'quote' => 'jbportal cut our time-to-hire in half. The candidate quality is excellent.' ),
		array( 'name' => 'Diego Salinas', 'role' => 'Founder, Brightline', 'quote' => 'Posting jobs is effortless and the dashboard keeps everything in one place.' ),
		array( 'name' => 'Priya Raman', 'role' => 'Senior Engineer', 'quote' => 'I found a remote role I love in less than two weeks. Highly recommended.' ),
	);
	ob_start();
	echo '<div class="jb-testimonials">';
	foreach ( $items as $t ) {
		echo '<figure class="jb-testimonial"><blockquote>“' . esc_html( $t['quote'] ) . '”</blockquote>';
		echo '<figcaption><strong>' . esc_html( $t['name'] ) . '</strong><span>' . esc_html( $t['role'] ) . '</span></figcaption></figure>';
	}
	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'jbportal_testimonials', 'jbportal_shortcode_testimonials' );

/**
 * [jbportal_stats]
 */
function jbportal_shortcode_stats( $atts ) {
	$jobs      = jbportal_count_posts( 'job_listing' );
	$companies = jbportal_count_posts( 'company' );
	$cands     = jbportal_count_posts( 'candidate' );
	$cats      = wp_count_terms( array( 'taxonomy' => 'job_category', 'hide_empty' => false ) );
	$cats      = is_wp_error( $cats ) ? 0 : (int) $cats;

	ob_start();
	echo '<div class="jb-stats">';
	printf( '<div class="jb-stat"><span class="jb-stat-num">%d</span><span class="jb-stat-label">%s</span></div>', $jobs, esc_html__( 'Open Jobs', 'jbportal' ) );
	printf( '<div class="jb-stat"><span class="jb-stat-num">%d</span><span class="jb-stat-label">%s</span></div>', $companies, esc_html__( 'Companies', 'jbportal' ) );
	printf( '<div class="jb-stat"><span class="jb-stat-num">%d</span><span class="jb-stat-label">%s</span></div>', $cands, esc_html__( 'Candidates', 'jbportal' ) );
	printf( '<div class="jb-stat"><span class="jb-stat-num">%d</span><span class="jb-stat-label">%s</span></div>', $cats, esc_html__( 'Categories', 'jbportal' ) );
	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'jbportal_stats', 'jbportal_shortcode_stats' );
