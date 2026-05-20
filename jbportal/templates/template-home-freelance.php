<?php
/**
 * Template Name: Home — Freelance
 * Gig-economy and freelance-work focused layout.
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-hero jb-hero-freelance">
	<div class="jb-container jb-hero-inner">
		<div class="jb-hero-copy">
			<span class="jb-eyebrow"><?php esc_html_e( 'Remote-first · Freelance · Contract', 'jbportal' ); ?></span>
			<h1 class="jb-hero-title"><?php esc_html_e( 'Build your independent career.', 'jbportal' ); ?></h1>
			<p class="jb-hero-subtitle"><?php esc_html_e( 'Freelance gigs and contract roles with top companies.', 'jbportal' ); ?></p>
			<?php get_template_part( 'template-parts/job-search' ); ?>
		</div>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head">
			<h2><?php esc_html_e( 'Contract &amp; Freelance roles', 'jbportal' ); ?></h2>
			<a class="jb-section-link" href="<?php echo esc_url( get_post_type_archive_link( 'job_listing' ) ); ?>"><?php esc_html_e( 'See all →', 'jbportal' ); ?></a>
		</header>
		<?php
		$args = array(
			'post_type'      => 'job_listing',
			'posts_per_page' => 9,
			'tax_query'      => array(
				array(
					'taxonomy' => 'job_type',
					'field'    => 'slug',
					'terms'    => array( 'freelance', 'contract' ),
					'operator' => 'IN',
				),
			),
		);
		$q = new WP_Query( $args );
		if ( $q->have_posts() ) {
			echo '<div class="jb-jobs-grid">';
			while ( $q->have_posts() ) { $q->the_post(); get_template_part( 'template-parts/content', 'job' ); }
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
		<header class="jb-section-head"><h2><?php esc_html_e( 'Skills in demand', 'jbportal' ); ?></h2></header>
		<div class="jb-type-pills">
			<?php
			$skills = get_terms( array( 'taxonomy' => 'skill', 'number' => 16, 'hide_empty' => true ) );
			if ( $skills && ! is_wp_error( $skills ) ) {
				foreach ( $skills as $s ) {
					echo '<a class="jb-pill" href="' . esc_url( get_term_link( $s ) ) . '">' . esc_html( $s->name ) . '</a>';
				}
			}
			?>
		</div>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Browse by category', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_categories limit="10"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-cta">
	<div class="jb-container jb-cta-inner">
		<div>
			<h2><?php esc_html_e( 'Post a contract role', 'jbportal' ); ?></h2>
			<p><?php esc_html_e( 'Access our pool of vetted freelance professionals.', 'jbportal' ); ?></p>
		</div>
		<a class="jb-btn jb-btn-primary jb-btn-lg" href="<?php echo esc_url( home_url( '/post-a-job/' ) ); ?>"><?php esc_html_e( 'Post a Role', 'jbportal' ); ?></a>
	</div>
</section>

<?php get_footer(); ?>
