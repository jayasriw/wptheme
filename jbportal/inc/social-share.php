<?php
/**
 * Social sharing buttons for jobs and companies.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_render_share_buttons( $url = '', $title = '' ) {
	$url   = $url ?: get_permalink();
	$title = $title ?: get_the_title();
	$enc_url   = rawurlencode( $url );
	$enc_title = rawurlencode( $title );

	$networks = array(
		'twitter'  => array( 'X',         "https://twitter.com/intent/tweet?url={$enc_url}&text={$enc_title}" ),
		'facebook' => array( 'f',         "https://www.facebook.com/sharer/sharer.php?u={$enc_url}" ),
		'linkedin' => array( 'in',        "https://www.linkedin.com/sharing/share-offsite/?url={$enc_url}" ),
		'whatsapp' => array( '✆',         "https://api.whatsapp.com/send?text={$enc_title}%20{$enc_url}" ),
		'email'    => array( '✉',         "mailto:?subject={$enc_title}&body={$enc_url}" ),
	);
	?>
	<div class="jb-share">
		<span class="jb-share-label"><?php esc_html_e( 'Share:', 'jbportal' ); ?></span>
		<?php foreach ( $networks as $key => $data ) : ?>
			<a class="jb-share-btn jb-share-<?php echo esc_attr( $key ); ?>" href="<?php echo esc_url( $data[1] ); ?>" target="_blank" rel="noopener nofollow" aria-label="<?php echo esc_attr( ucfirst( $key ) ); ?>"><?php echo esc_html( $data[0] ); ?></a>
		<?php endforeach; ?>
		<button class="jb-share-btn jb-share-copy" type="button" data-url="<?php echo esc_url( $url ); ?>" aria-label="<?php esc_attr_e( 'Copy link', 'jbportal' ); ?>">⧉</button>
	</div>
	<?php
}
