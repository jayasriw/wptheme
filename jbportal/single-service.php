<?php
/**
 * Single freelance service.
 *
 * @package jbportal
 */

get_header();
while ( have_posts() ) :
	the_post();
	$sid      = get_the_ID();
	$price    = (float) get_post_meta( $sid, '_service_price', true );
	$delivery = (int)   get_post_meta( $sid, '_service_delivery', true );
	$featured = (bool)  get_post_meta( $sid, '_service_featured', true );
	$seller   = get_userdata( get_the_author_meta( 'ID' ) );
	$ok       = get_transient( 'jbportal_order_ok_' . get_current_user_id() );
	$err      = get_transient( 'jbportal_order_err_' . get_current_user_id() );
	if ( $ok  ) { delete_transient( 'jbportal_order_ok_'  . get_current_user_id() ); }
	if ( $err ) { delete_transient( 'jbportal_order_err_' . get_current_user_id() ); }
	?>

	<div class="jb-container jb-layout-2col" style="padding-top:2rem">
		<article class="jb-content">
			<?php if ( $ok  ) : ?><div class="jb-notice jb-notice-success"><?php echo esc_html( $ok ); ?></div><?php endif; ?>
			<?php if ( $err ) : ?><div class="jb-notice jb-notice-error"><?php echo esc_html( $err ); ?></div><?php endif; ?>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="jb-service-hero"><?php the_post_thumbnail( array( 780, 440 ) ); ?></div>
			<?php endif; ?>

			<h1><?php the_title(); ?></h1>

			<?php if ( $seller ) : ?>
				<div class="jb-service-seller-row">
					<?php echo get_avatar( $seller->ID, 40 ); ?>
					<span><?php echo esc_html( $seller->display_name ); ?></span>
					<?php if ( $featured ) : ?>
						<span class="jb-badge jb-badge-featured"><?php esc_html_e( 'Featured', 'jbportal' ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="jb-service-description" style="margin-top:1.5rem">
				<?php the_content(); ?>
			</div>
		</article>

		<aside class="jb-sidebar">
			<div class="jb-card jb-service-order-card">
				<div class="jb-service-price-display">$<?php echo esc_html( number_format( $price, 2 ) ); ?></div>
				<div class="jb-service-delivery-display">
					⏱ <?php printf( esc_html( _n( 'Delivers in %d day', 'Delivers in %d days', $delivery, 'jbportal' ) ), (int) $delivery ); ?>
				</div>

				<?php if ( is_user_logged_in() && get_current_user_id() !== $seller->ID ) : ?>
					<form class="jb-form" method="post" style="margin-top:1rem">
						<?php wp_nonce_field( 'jbportal_purchase_service', 'jbportal_service_purchase_nonce' ); ?>
						<input type="hidden" name="service_id" value="<?php echo esc_attr( $sid ); ?>">
						<button class="jb-btn jb-btn-primary jb-btn-full" type="submit"><?php esc_html_e( 'Order Now', 'jbportal' ); ?></button>
					</form>
				<?php elseif ( ! is_user_logged_in() ) : ?>
					<a class="jb-btn jb-btn-primary jb-btn-full" style="margin-top:1rem" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign in to Order', 'jbportal' ); ?></a>
				<?php else : ?>
					<p style="margin-top:1rem;font-size:0.875rem;color:var(--jb-muted)"><?php esc_html_e( 'This is your service.', 'jbportal' ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $seller ) : ?>
				<div class="jb-card">
					<h3><?php esc_html_e( 'About the seller', 'jbportal' ); ?></h3>
					<?php echo get_avatar( $seller->ID, 60 ); ?>
					<p><strong><?php echo esc_html( $seller->display_name ); ?></strong></p>
					<?php
					$cands = get_posts( array( 'post_type' => 'candidate', 'author' => $seller->ID, 'posts_per_page' => 1 ) );
					if ( $cands ) : ?>
						<a class="jb-btn jb-btn-ghost jb-btn-sm" href="<?php echo esc_url( get_permalink( $cands[0]->ID ) ); ?>"><?php esc_html_e( 'View Profile', 'jbportal' ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</aside>
	</div>

<?php endwhile;
get_footer();
