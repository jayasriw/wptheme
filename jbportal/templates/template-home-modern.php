<?php
/**
 * Template Name: Home — Modern
 * Full-width video/gradient hero, horizontally scrolling job cards.
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-hero jb-hero-modern">
	<div class="jb-hero-modern-overlay"></div>
	<div class="jb-container jb-hero-modern-inner">
		<span class="jb-eyebrow"><?php esc_html_e( '10,000+ live opportunities', 'jbportal' ); ?></span>
		<h1 class="jb-hero-title"><?php esc_html_e( 'The future of work is here.', 'jbportal' ); ?></h1>
		<p class="jb-hero-subtitle"><?php esc_html_e( 'Join companies shaping tomorrow. Find your role.', 'jbportal' ); ?></p>
		<?php get_template_part( 'template-parts/job-search' ); ?>
		<p class="jb-hero-hint"><?php esc_html_e( 'Popular:', 'jbportal' ); ?>
			<?php
			$terms = get_terms( array( 'taxonomy' => 'job_category', 'number' => 6, 'hide_empty' => true ) );
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $t ) {
					echo '<a class="jb-hero-tag" href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a> ';
				}
			}
			?>
		</p>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head">
			<h2><?php esc_html_e( 'Trending right now', 'jbportal' ); ?></h2>
			<a class="jb-section-link" href="<?php echo esc_url( get_post_type_archive_link( 'job_listing' ) ); ?>"><?php esc_html_e( 'All jobs →', 'jbportal' ); ?></a>
		</header>
		<div class="jb-scroll-row">
			<?php
			$jobs = new WP_Query( array( 'post_type' => 'job_listing', 'posts_per_page' => 8, 'orderby' => 'meta_value_num', 'meta_key' => '_jb_views' ) );
			while ( $jobs->have_posts() ) { $jobs->the_post(); get_template_part( 'template-parts/content', 'job' ); }
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>

<section class="jb-section jb-section-alt">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Explore categories', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_categories limit="10"]' ); ?>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Companies you\'ll love', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_companies limit="6"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-cta">
	<div class="jb-container jb-cta-inner">
		<div>
			<h2><?php esc_html_e( 'Talent found here.', 'jbportal' ); ?></h2>
			<p><?php esc_html_e( 'Post your opening in minutes and reach thousands of qualified candidates.', 'jbportal' ); ?></p>
		</div>
		<div class="jb-cta-actions">
			<a class="jb-btn jb-btn-primary jb-btn-lg" href="<?php echo esc_url( home_url( '/post-a-job/' ) ); ?>"><?php esc_html_e( 'Post a Job', 'jbportal' ); ?></a>
			<a class="jb-btn jb-btn-ghost jb-btn-lg" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'View Pricing', 'jbportal' ); ?></a>
		</div>
	</div>
</section>

<?php get_footer(); ?>
