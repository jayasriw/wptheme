<?php
/**
 * Template Name: Home — Startup
 * Bold hero aimed at startup culture; candidate-forward.
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-hero jb-hero-startup">
	<div class="jb-container jb-hero-startup-inner">
		<div class="jb-hero-startup-copy">
			<span class="jb-badge jb-badge-new"><?php esc_html_e( 'New this week', 'jbportal' ); ?></span>
			<h1 class="jb-hero-title"><?php esc_html_e( 'Work at a startup you\'ll be proud of.', 'jbportal' ); ?></h1>
			<p class="jb-hero-subtitle"><?php esc_html_e( 'Roles at early-stage companies backed by top investors.', 'jbportal' ); ?></p>
			<div class="jb-hero-ctas">
				<a class="jb-btn jb-btn-primary jb-btn-lg" href="<?php echo esc_url( get_post_type_archive_link( 'job_listing' ) ); ?>"><?php esc_html_e( 'Browse Jobs', 'jbportal' ); ?></a>
				<a class="jb-btn jb-btn-outline jb-btn-lg" href="<?php echo esc_url( home_url( '/post-a-job/' ) ); ?>"><?php esc_html_e( 'Hire Talent', 'jbportal' ); ?></a>
			</div>
		</div>
		<div class="jb-hero-startup-search">
			<?php get_template_part( 'template-parts/job-search' ); ?>
		</div>
	</div>
</section>

<section class="jb-section jb-section-stats">
	<div class="jb-container"><?php echo do_shortcode( '[jbportal_stats]' ); ?></div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head">
			<h2><?php esc_html_e( 'Urgent openings', 'jbportal' ); ?></h2>
			<a class="jb-section-link" href="<?php echo esc_url( get_post_type_archive_link( 'job_listing' ) ); ?>"><?php esc_html_e( 'See all →', 'jbportal' ); ?></a>
		</header>
		<?php echo do_shortcode( '[jbportal_jobs limit="6" urgent="1"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-alt">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Browse by type', 'jbportal' ); ?></h2></header>
		<div class="jb-type-pills">
			<?php
			$types = get_terms( array( 'taxonomy' => 'job_type', 'hide_empty' => false ) );
			if ( $types && ! is_wp_error( $types ) ) {
				foreach ( $types as $t ) {
					echo '<a class="jb-pill" href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . ' <span>(' . (int) $t->count . ')</span></a>';
				}
			}
			?>
		</div>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Testimonials', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_testimonials]' ); ?>
	</div>
</section>

<?php get_footer(); ?>
