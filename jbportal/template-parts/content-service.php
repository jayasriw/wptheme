<?php
/**
 * Template part: freelance service card.
 *
 * @package jbportal
 */

$sid      = get_the_ID();
$price    = (float) get_post_meta( $sid, '_service_price', true );
$delivery = (int)   get_post_meta( $sid, '_service_delivery', true );
$featured = (bool)  get_post_meta( $sid, '_service_featured', true );
$seller   = get_userdata( get_the_author_meta( 'ID' ) );
?>
<article class="jb-service-card<?php echo $featured ? ' jb-service-featured' : ''; ?>">
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="jb-service-thumb">
			<?php the_post_thumbnail( array( 380, 220 ) ); ?>
		</a>
	<?php endif; ?>
	<div class="jb-service-body">
		<?php if ( $featured ) : ?>
			<span class="jb-badge jb-badge-featured"><?php esc_html_e( 'Featured', 'jbportal' ); ?></span>
		<?php endif; ?>
		<h3 class="jb-service-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( $seller ) : ?>
			<p class="jb-service-seller"><?php echo esc_html( $seller->display_name ); ?></p>
		<?php endif; ?>
		<div class="jb-service-footer">
			<span class="jb-service-delivery">⏱ <?php echo esc_html( sprintf( _n( '%d day', '%d days', $delivery, 'jbportal' ), $delivery ) ); ?></span>
			<span class="jb-service-price">$<?php echo esc_html( number_format( $price, 2 ) ); ?></span>
		</div>
	</div>
</article>
