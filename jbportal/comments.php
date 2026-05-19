<?php
/**
 * Comments template.
 *
 * @package jbportal
 */

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="jb-comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="jb-comments-title">
			<?php
			$count = get_comments_number();
			printf( esc_html( _n( '%s comment', '%s comments', $count, 'jbportal' ) ), number_format_i18n( $count ) );
			?>
		</h2>
		<ol class="jb-comment-list">
			<?php wp_list_comments( array( 'style' => 'ol', 'short_ping' => true ) ); ?>
		</ol>
		<?php the_comments_pagination(); ?>
	<?php endif; ?>

	<?php comment_form( array(
		'title_reply' => __( 'Leave a comment', 'jbportal' ),
		'class_form'  => 'jb-form jb-comment-form',
	) ); ?>
</div>
