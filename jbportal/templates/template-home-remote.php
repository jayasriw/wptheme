<?php
/**
 * Template Name: Home — Remote
 * Remote-work-first homepage emphasising location independence.
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-hero jb-hero-remote">
	<div class="jb-container jb-hero-inner">
		<div class="jb-hero-copy">
			<span class="jb-badge jb-badge-remote" style="font-size:1rem;margin-bottom:.75rem;display:inline-block">🌍 <?php esc_html_e( 'Work from anywhere', 'jbportal' ); ?></span>
			<h1 class="jb-hero-title"><?php esc_html_e( 'Remote jobs that fit your life.', 'jbportal' ); ?></h1>
			<p class="jb-hero-subtitle"><?php esc_html_e( 'Location-independent roles at companies that get it.', 'jbportal' ); ?></p>
			<?php get_template_part( 'template-parts/job-search' ); ?>
		</div>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head">
			<h2><?php esc_html_e( 'Remote positions', 'jbportal' ); ?></h2>
			<a class="jb-section-link" href="<?php echo esc_url( add_query_arg( 'remote', '1', get_post_type_archive_link( 'job_listing' ) ) ); ?>"><?php esc_html_e( 'All remote →', 'jbportal' ); ?></a>
		</header>
		<?php
		$r = new WP_Query( array(
			'post_type'      => 'job_listing',
			'posts_per_page' => 9,
			'meta_query'     => array( array( 'key' => '_job_remote', 'value' => '1' ) ),
		) );
		if ( $r->have_posts() ) {
			echo '<div class="jb-jobs-grid">';
			while ( $r->have_posts() ) { $r->the_post(); get_template_part( 'template-parts/content', 'job' ); }
			echo '</div>';
			wp_reset_postdata();
		} else {
			echo do_shortcode( '[jbportal_jobs limit="9"]' );
		}
		?>
	</div>
</section>

<section class="jb-section jb-section-alt">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Remote-friendly companies', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_companies limit="8"]' ); ?>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Remote by category', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_categories limit="10"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-cta">
	<div class="jb-container jb-cta-inner">
		<div>
			<h2><?php esc_html_e( 'Hire a distributed team', 'jbportal' ); ?></h2>
			<p><?php esc_html_e( 'Post remote roles and find top global talent instantly.', 'jbportal' ); ?></p>
		</div>
		<a class="jb-btn jb-btn-primary jb-btn-lg" href="<?php echo esc_url( home_url( '/post-a-job/' ) ); ?>"><?php esc_html_e( 'Post a Remote Job', 'jbportal' ); ?></a>
	</div>
</section>

<?php get_footer(); ?>
