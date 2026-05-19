<?php
/**
 * Company reviews (1-5 stars + comment).
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_register_review_cpt() {
	register_post_type( 'company_review', array(
		'labels' => array(
			'name'          => __( 'Reviews', 'jbportal' ),
			'singular_name' => __( 'Review', 'jbportal' ),
			'menu_name'     => __( 'Reviews', 'jbportal' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=company',
		'supports'        => array( 'title', 'editor', 'author' ),
		'capability_type' => 'post',
	) );
	register_post_type( 'candidate_review', array(
		'labels' => array(
			'name'          => __( 'Candidate Reviews', 'jbportal' ),
			'singular_name' => __( 'Candidate Review', 'jbportal' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=candidate',
		'supports'        => array( 'title', 'editor', 'author' ),
		'capability_type' => 'post',
	) );
}
add_action( 'init', 'jbportal_register_review_cpt' );

function jbportal_handle_review_submit() {
	if ( empty( $_POST['jbportal_review_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_review_nonce'] ) ), 'jbportal_review' ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	$company_id = (int) ( $_POST['company_id'] ?? 0 );
	$rating     = max( 1, min( 5, (int) ( $_POST['rating'] ?? 0 ) ) );
	$title      = sanitize_text_field( wp_unslash( $_POST['review_title'] ?? '' ) );
	$content    = sanitize_textarea_field( wp_unslash( $_POST['review_content'] ?? '' ) );
	if ( ! $company_id || ! $rating || 'company' !== get_post_type( $company_id ) ) {
		return;
	}

	$rid = wp_insert_post( array(
		'post_type'    => 'company_review',
		'post_status'  => 'publish',
		'post_title'   => $title ?: sprintf( __( 'Review of %s', 'jbportal' ), get_the_title( $company_id ) ),
		'post_content' => $content,
		'post_author'  => get_current_user_id(),
	) );
	if ( $rid && ! is_wp_error( $rid ) ) {
		update_post_meta( $rid, '_review_company_id', $company_id );
		update_post_meta( $rid, '_review_rating', $rating );
		set_transient( 'jbportal_review_ok_' . $company_id, __( 'Thanks for your review!', 'jbportal' ), 60 );
	}
	wp_safe_redirect( get_permalink( $company_id ) . '#reviews' );
	exit;
}
add_action( 'template_redirect', 'jbportal_handle_review_submit' );

function jbportal_get_company_reviews( $company_id ) {
	return get_posts( array(
		'post_type'      => 'company_review',
		'posts_per_page' => -1,
		'meta_key'       => '_review_company_id',
		'meta_value'     => $company_id,
	) );
}

function jbportal_get_company_avg_rating( $company_id ) {
	$reviews = jbportal_get_company_reviews( $company_id );
	if ( ! $reviews ) {
		return 0;
	}
	$total = 0;
	foreach ( $reviews as $r ) {
		$total += (int) get_post_meta( $r->ID, '_review_rating', true );
	}
	return round( $total / count( $reviews ), 1 );
}

function jbportal_render_stars( $rating, $size = 1 ) {
	$rating = (float) $rating;
	$out    = '<span class="jb-stars" style="--star-size:' . (int) $size . 'em" aria-label="' . esc_attr( sprintf( __( '%s out of 5 stars', 'jbportal' ), number_format_i18n( $rating, 1 ) ) ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$out .= '<span class="jb-star' . ( $rating >= $i ? ' is-full' : ( $rating >= $i - 0.5 ? ' is-half' : '' ) ) . '">★</span>';
	}
	$out .= '</span>';
	return $out;
}

function jbportal_render_company_reviews( $company_id ) {
	$reviews = jbportal_get_company_reviews( $company_id );
	$avg     = jbportal_get_company_avg_rating( $company_id );
	$ok      = get_transient( 'jbportal_review_ok_' . $company_id );
	if ( $ok ) { delete_transient( 'jbportal_review_ok_' . $company_id ); }
	?>
	<section id="reviews" class="jb-reviews">
		<header class="jb-reviews-head">
			<h2><?php printf( esc_html__( 'Reviews (%d)', 'jbportal' ), count( $reviews ) ); ?></h2>
			<?php if ( $avg ) : ?>
				<div class="jb-reviews-avg">
					<?php echo jbportal_render_stars( $avg, 1.1 ); // phpcs:ignore ?>
					<strong><?php echo esc_html( number_format_i18n( $avg, 1 ) ); ?></strong>
				</div>
			<?php endif; ?>
		</header>

		<?php if ( $ok ) : ?><div class="jb-notice jb-notice-success"><?php echo esc_html( $ok ); ?></div><?php endif; ?>

		<?php if ( $reviews ) : ?>
			<ul class="jb-review-list">
				<?php foreach ( $reviews as $r ) :
					$rating = (int) get_post_meta( $r->ID, '_review_rating', true );
					$author = get_userdata( $r->post_author );
					?>
					<li class="jb-review">
						<header>
							<strong><?php echo esc_html( $r->post_title ); ?></strong>
							<?php echo jbportal_render_stars( $rating ); // phpcs:ignore ?>
						</header>
						<p><?php echo esc_html( $r->post_content ); ?></p>
						<footer><?php echo esc_html( $author ? $author->display_name : __( 'Anonymous', 'jbportal' ) ); ?> · <?php echo esc_html( mysql2date( get_option( 'date_format' ), $r->post_date ) ); ?></footer>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p><?php esc_html_e( 'No reviews yet — be the first to leave one.', 'jbportal' ); ?></p>
		<?php endif; ?>

		<?php if ( is_user_logged_in() ) : ?>
			<form class="jb-form jb-review-form" method="post">
				<?php wp_nonce_field( 'jbportal_review', 'jbportal_review_nonce' ); ?>
				<input type="hidden" name="company_id" value="<?php echo esc_attr( $company_id ); ?>">
				<h3><?php esc_html_e( 'Leave a review', 'jbportal' ); ?></h3>
				<div class="jb-star-picker" role="radiogroup" aria-label="<?php esc_attr_e( 'Rating', 'jbportal' ); ?>">
					<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
						<input type="radio" id="jb-r-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
						<label for="jb-r-<?php echo $i; ?>" aria-label="<?php echo esc_attr( sprintf( __( '%d stars', 'jbportal' ), $i ) ); ?>">★</label>
					<?php endfor; ?>
				</div>
				<label><?php esc_html_e( 'Title', 'jbportal' ); ?><input type="text" name="review_title" required></label>
				<label><?php esc_html_e( 'Your experience', 'jbportal' ); ?><textarea name="review_content" rows="5" required></textarea></label>
				<button type="submit" class="jb-btn jb-btn-primary"><?php esc_html_e( 'Submit Review', 'jbportal' ); ?></button>
			</form>
		<?php else : ?>
			<p><a href="<?php echo esc_url( wp_login_url( get_permalink( $company_id ) ) ); ?>"><?php esc_html_e( 'Sign in to leave a review.', 'jbportal' ); ?></a></p>
		<?php endif; ?>
	</section>
	<?php
}
