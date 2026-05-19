<?php
/**
 * Single company.
 *
 * @package jbportal
 */

get_header();
while ( have_posts() ) :
	the_post();
	$cid       = get_the_ID();
	$website   = get_post_meta( $cid, '_company_website', true );
	$email     = get_post_meta( $cid, '_company_email', true );
	$phone     = get_post_meta( $cid, '_company_phone', true );
	$address   = get_post_meta( $cid, '_company_address', true );
	$size      = get_post_meta( $cid, '_company_size', true );
	$founded   = get_post_meta( $cid, '_company_founded', true );
	$twitter   = get_post_meta( $cid, '_company_twitter', true );
	$linkedin  = get_post_meta( $cid, '_company_linkedin', true );
	$facebook  = get_post_meta( $cid, '_company_facebook', true );
	$verified  = (bool) get_post_meta( $cid, '_company_verified', true );
	?>

	<section class="jb-company-banner">
		<div class="jb-container jb-company-banner-inner">
			<div class="jb-company-logo">
				<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'jbportal-company-logo' );
				} else {
					echo '<span class="jb-logo-fallback">' . esc_html( strtoupper( substr( get_the_title(), 0, 1 ) ) ) . '</span>';
				}
				?>
			</div>
			<div class="jb-company-headline">
				<h1>
					<?php the_title(); ?>
					<?php if ( $verified ) : ?><span class="jb-badge jb-badge-verified" title="<?php esc_attr_e( 'Verified', 'jbportal' ); ?>">✓</span><?php endif; ?>
				</h1>
				<div class="jb-company-meta">
					<?php if ( $size ) : ?><span>👥 <?php echo esc_html( $size ); ?> <?php esc_html_e( 'employees', 'jbportal' ); ?></span><?php endif; ?>
					<?php if ( $founded ) : ?><span>📅 <?php echo esc_html( sprintf( __( 'Founded %s', 'jbportal' ), $founded ) ); ?></span><?php endif; ?>
					<?php if ( $address ) : ?><span>📍 <?php echo esc_html( $address ); ?></span><?php endif; ?>
				</div>
				<div class="jb-company-actions" style="margin:1rem 0">
					<?php if ( is_user_logged_in() ) { jbportal_follow_button( $cid ); } ?>
					<?php jbportal_render_share_buttons(); ?>
					<?php if ( ! $verified && is_user_logged_in() ) : ?>
						<a class="jb-btn jb-btn-ghost jb-btn-sm" href="<?php echo esc_url( add_query_arg( array( 'jbportal_claim' => '1', 'company' => $cid ), get_permalink() ) ); ?>"><?php esc_html_e( 'Claim this company', 'jbportal' ); ?></a>
					<?php endif; ?>
				</div>
				<?php if ( $website || $twitter || $linkedin || $facebook ) : ?>
					<div class="jb-company-social">
						<?php if ( $website )  : ?><a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener">🌐</a><?php endif; ?>
						<?php if ( $twitter )  : ?><a href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener">𝕏</a><?php endif; ?>
						<?php if ( $linkedin ) : ?><a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener">in</a><?php endif; ?>
						<?php if ( $facebook ) : ?><a href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener">f</a><?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<div class="jb-container jb-layout-2col jb-company-page">
		<article class="jb-content">
			<div class="jb-company-description"><?php the_content(); ?></div>

			<?php
			$jobs = new WP_Query( array(
				'post_type'      => 'job_listing',
				'posts_per_page' => -1,
				'meta_query'     => array(
					'relation' => 'OR',
					array( 'key' => '_job_company_id', 'value' => $cid ),
					array( 'key' => '_job_company', 'value' => get_the_title(), 'compare' => '=' ),
				),
			) );
			?>
			<h2><?php printf( esc_html__( 'Open positions (%d)', 'jbportal' ), (int) $jobs->found_posts ); ?></h2>
			<?php if ( $jobs->have_posts() ) : ?>
				<div class="jb-jobs-grid">
					<?php while ( $jobs->have_posts() ) : $jobs->the_post(); get_template_part( 'template-parts/content', 'job' ); endwhile; ?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'This company has no open positions right now.', 'jbportal' ); ?></p>
			<?php endif;
			wp_reset_postdata(); ?>

			<?php jbportal_render_company_reviews( $cid ); ?>
		</article>

		<aside class="jb-sidebar">
			<?php if ( $address ) : ?>
				<div class="jb-card jb-map-card">
					<h3><?php esc_html_e( 'Location', 'jbportal' ); ?></h3>
					<iframe class="jb-map" loading="lazy" referrerpolicy="no-referrer" src="https://www.openstreetmap.org/export/embed.html?bbox=&layer=mapnik&marker=<?php echo esc_attr( urlencode( $address ) ); ?>" style="border:0;width:100%;height:220px;border-radius:8px"></iframe>
					<a class="jb-btn jb-btn-ghost jb-btn-sm" target="_blank" rel="noopener" href="https://www.openstreetmap.org/search?query=<?php echo esc_attr( urlencode( $address ) ); ?>"><?php esc_html_e( 'Open in map', 'jbportal' ); ?></a>
				</div>
			<?php endif; ?>
			<div class="jb-card">
				<h3><?php esc_html_e( 'Contact', 'jbportal' ); ?></h3>
				<?php
				$comp_viewer     = get_current_user_id();
				$comp_author     = (int) get_post_field( 'post_author', $cid );
				$can_see_comp    = $comp_viewer && (
					$comp_viewer === $comp_author
					|| current_user_can( 'manage_options' )
					|| ( jbportal_user_is_employer( $comp_viewer ) && jbportal_can_see_contact_info( $comp_viewer ) )
					|| jbportal_user_is_candidate( $comp_viewer )
				);
				?>
				<ul class="jb-info-list">
					<?php if ( $website ) : ?><li><strong><?php esc_html_e( 'Website', 'jbportal' ); ?></strong><span><a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener"><?php echo esc_html( preg_replace( '#^https?://#', '', $website ) ); ?></a></span></li><?php endif; ?>
					<?php if ( $email && $can_see_comp ) : ?><li><strong><?php esc_html_e( 'Email', 'jbportal' ); ?></strong><span><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></span></li><?php endif; ?>
					<?php if ( $phone && $can_see_comp ) : ?><li><strong><?php esc_html_e( 'Phone', 'jbportal' ); ?></strong><span><?php echo esc_html( $phone ); ?></span></li><?php endif; ?>
				</ul>
				<?php if ( ! $can_see_comp && ( $email || $phone ) ) : ?>
					<p class="jb-contact-gate"><?php esc_html_e( 'Sign in to view contact details.', 'jbportal' ); ?></p>
				<?php endif; ?>
			</div>
		</aside>
	</div>

<?php endwhile;
get_footer();
