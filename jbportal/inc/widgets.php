<?php
/**
 * Custom widgets.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JBPortal_Recent_Jobs_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( 'jbportal_recent_jobs', __( 'jbportal: Recent Jobs', 'jbportal' ), array(
			'description' => __( 'Show a list of the latest jobs.', 'jbportal' ),
		) );
	}

	public function widget( $args, $instance ) {
		$title  = isset( $instance['title'] ) ? $instance['title'] : __( 'Recent Jobs', 'jbportal' );
		$number = isset( $instance['number'] ) ? (int) $instance['number'] : 5;

		echo $args['before_widget']; // phpcs:ignore
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore
		}

		$q = new WP_Query( array(
			'post_type'      => 'job_listing',
			'posts_per_page' => $number,
			'no_found_rows'  => true,
		) );

		if ( $q->have_posts() ) {
			echo '<ul class="jb-widget-jobs">';
			while ( $q->have_posts() ) {
				$q->the_post();
				$company = jbportal_get_job_company();
				printf(
					'<li><a href="%1$s"><span class="jb-w-title">%2$s</span><span class="jb-w-meta">%3$s · %4$s</span></a></li>',
					esc_url( get_permalink() ),
					esc_html( get_the_title() ),
					esc_html( $company['name'] ),
					esc_html( jbportal_get_job_location() )
				);
			}
			echo '</ul>';
			wp_reset_postdata();
		}

		echo $args['after_widget']; // phpcs:ignore
	}

	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? $instance['title'] : '';
		$number = isset( $instance['number'] ) ? (int) $instance['number'] : 5;
		?>
		<p><label><?php esc_html_e( 'Title:', 'jbportal' ); ?>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"></label></p>
		<p><label><?php esc_html_e( 'Number to show:', 'jbportal' ); ?>
			<input class="tiny-text" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" min="1" max="20" value="<?php echo esc_attr( $number ); ?>"></label></p>
		<?php
	}

	public function update( $new, $old ) {
		return array(
			'title'  => sanitize_text_field( $new['title'] ?? '' ),
			'number' => max( 1, (int) ( $new['number'] ?? 5 ) ),
		);
	}
}

class JBPortal_Job_Filter_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( 'jbportal_job_filter', __( 'jbportal: Job Filter', 'jbportal' ), array(
			'description' => __( 'Sidebar filter for job archives.', 'jbportal' ),
		) );
	}

	public function widget( $args, $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Filter Jobs', 'jbportal' );
		echo $args['before_widget']; // phpcs:ignore
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore
		}
		jbportal_render_job_filter_form();
		echo $args['after_widget']; // phpcs:ignore
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p><label><?php esc_html_e( 'Title:', 'jbportal' ); ?>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"></label></p>
		<?php
	}

	public function update( $new, $old ) {
		return array( 'title' => sanitize_text_field( $new['title'] ?? '' ) );
	}
}

class JBPortal_Featured_Companies_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( 'jbportal_featured_companies', __( 'jbportal: Featured Companies', 'jbportal' ), array(
			'description' => __( 'Show a list of featured companies.', 'jbportal' ),
		) );
	}

	public function widget( $args, $instance ) {
		$title  = isset( $instance['title'] ) ? $instance['title'] : __( 'Featured Companies', 'jbportal' );
		$number = isset( $instance['number'] ) ? (int) $instance['number'] : 5;
		echo $args['before_widget']; // phpcs:ignore
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore
		}
		$q = new WP_Query( array(
			'post_type'      => 'company',
			'posts_per_page' => $number,
			'meta_key'       => '_company_verified',
			'orderby'        => 'meta_value',
			'order'          => 'DESC',
		) );
		if ( $q->have_posts() ) {
			echo '<ul class="jb-widget-companies">';
			while ( $q->have_posts() ) {
				$q->the_post();
				printf(
					'<li><a href="%s"><span class="jb-w-thumb">%s</span><span class="jb-w-title">%s</span></a></li>',
					esc_url( get_permalink() ),
					get_the_post_thumbnail( get_the_ID(), array( 48, 48 ) ),
					esc_html( get_the_title() )
				);
			}
			echo '</ul>';
			wp_reset_postdata();
		}
		echo $args['after_widget']; // phpcs:ignore
	}

	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? $instance['title'] : '';
		$number = isset( $instance['number'] ) ? (int) $instance['number'] : 5;
		?>
		<p><label><?php esc_html_e( 'Title:', 'jbportal' ); ?>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"></label></p>
		<p><label><?php esc_html_e( 'Number to show:', 'jbportal' ); ?>
			<input class="tiny-text" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" min="1" max="20" value="<?php echo esc_attr( $number ); ?>"></label></p>
		<?php
	}

	public function update( $new, $old ) {
		return array(
			'title'  => sanitize_text_field( $new['title'] ?? '' ),
			'number' => max( 1, (int) ( $new['number'] ?? 5 ) ),
		);
	}
}

class JBPortal_Related_Jobs_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( 'jbportal_related_jobs', __( 'jbportal: Related Jobs', 'jbportal' ), array(
			'description' => __( 'Show related jobs on single job pages.', 'jbportal' ),
		) );
	}

	public function widget( $args, $instance ) {
		if ( ! is_singular( 'job_listing' ) ) {
			return;
		}
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Related Jobs', 'jbportal' );
		$limit = isset( $instance['limit'] ) ? (int) $instance['limit'] : 5;

		$post_id = get_the_ID();
		$cats    = wp_get_post_terms( $post_id, 'job_category', array( 'fields' => 'ids' ) );
		$types   = wp_get_post_terms( $post_id, 'job_type', array( 'fields' => 'ids' ) );

		$tax_query = array( 'relation' => 'OR' );
		if ( $cats && ! is_wp_error( $cats ) ) {
			$tax_query[] = array( 'taxonomy' => 'job_category', 'field' => 'term_id', 'terms' => $cats );
		}
		if ( $types && ! is_wp_error( $types ) ) {
			$tax_query[] = array( 'taxonomy' => 'job_type', 'field' => 'term_id', 'terms' => $types );
		}
		if ( count( $tax_query ) <= 1 ) {
			return;
		}

		$q = new WP_Query( array(
			'post_type'      => 'job_listing',
			'posts_per_page' => $limit,
			'post__not_in'   => array( $post_id ),
			'no_found_rows'  => true,
			'tax_query'      => $tax_query,
		) );

		if ( ! $q->have_posts() ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore
		}
		echo '<ul class="jb-widget-jobs">';
		while ( $q->have_posts() ) {
			$q->the_post();
			$company   = jbportal_get_job_company();
			$type_terms = get_the_terms( get_the_ID(), 'job_type' );
			$type_name  = ( $type_terms && ! is_wp_error( $type_terms ) ) ? $type_terms[0]->name : '';
			printf(
				'<li><a href="%1$s"><span class="jb-w-title">%2$s</span><span class="jb-w-meta">%3$s%4$s</span></a></li>',
				esc_url( get_permalink() ),
				esc_html( get_the_title() ),
				esc_html( $company['name'] ),
				$type_name ? ' &middot; <span class="jb-badge jb-badge-type">' . esc_html( $type_name ) . '</span>' : ''
			);
		}
		echo '</ul>';
		wp_reset_postdata();
		echo $args['after_widget']; // phpcs:ignore
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$limit = isset( $instance['limit'] ) ? (int) $instance['limit'] : 5;
		?>
		<p><label><?php esc_html_e( 'Title:', 'jbportal' ); ?>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"></label></p>
		<p><label><?php esc_html_e( 'Limit:', 'jbportal' ); ?>
			<input class="tiny-text" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="number" min="1" max="10" value="<?php echo esc_attr( $limit ); ?>"></label></p>
		<?php
	}

	public function update( $new, $old ) {
		return array(
			'title' => sanitize_text_field( $new['title'] ?? '' ),
			'limit' => max( 1, (int) ( $new['limit'] ?? 5 ) ),
		);
	}
}

function jbportal_register_widgets() {
	register_widget( 'JBPortal_Recent_Jobs_Widget' );
	register_widget( 'JBPortal_Job_Filter_Widget' );
	register_widget( 'JBPortal_Featured_Companies_Widget' );
	register_widget( 'JBPortal_Related_Jobs_Widget' );
}
add_action( 'widgets_init', 'jbportal_register_widgets' );

/**
 * Filter form used by widget and archive page.
 */
function jbportal_render_job_filter_form() {
	$keyword  = isset( $_GET['keyword'] ) ? sanitize_text_field( wp_unslash( $_GET['keyword'] ) ) : '';
	$location = isset( $_GET['location'] ) ? sanitize_text_field( wp_unslash( $_GET['location'] ) ) : '';
	$remote   = ! empty( $_GET['remote'] );
	$types_selected = isset( $_GET['type'] ) ? (array) $_GET['type'] : array();
	$cats_selected  = isset( $_GET['category'] ) ? (array) $_GET['category'] : array();

	$archive_url = get_post_type_archive_link( 'job_listing' );
	?>
	<form class="jb-filter" method="get" action="<?php echo esc_url( $archive_url ); ?>">
		<div class="jb-field">
			<label><?php esc_html_e( 'Keyword', 'jbportal' ); ?></label>
			<input type="search" name="keyword" value="<?php echo esc_attr( $keyword ); ?>" placeholder="<?php esc_attr_e( 'Job title or keyword', 'jbportal' ); ?>">
		</div>
		<div class="jb-field">
			<label><?php esc_html_e( 'Location', 'jbportal' ); ?></label>
			<input type="text" name="location" value="<?php echo esc_attr( $location ); ?>" placeholder="<?php esc_attr_e( 'City or country', 'jbportal' ); ?>">
		</div>
		<div class="jb-field">
			<label><?php esc_html_e( 'Job Type', 'jbportal' ); ?></label>
			<?php
			$types = get_terms( array( 'taxonomy' => 'job_type', 'hide_empty' => false ) );
			if ( $types && ! is_wp_error( $types ) ) :
				foreach ( $types as $t ) :
					$checked = in_array( $t->slug, array_map( 'sanitize_title', $types_selected ), true ) ? 'checked' : '';
					printf(
						'<label class="jb-check"><input type="checkbox" name="type[]" value="%s" %s> %s</label>',
						esc_attr( $t->slug ),
						$checked,
						esc_html( $t->name )
					);
				endforeach;
			endif;
			?>
		</div>
		<div class="jb-field">
			<label><?php esc_html_e( 'Category', 'jbportal' ); ?></label>
			<select name="category[]">
				<option value=""><?php esc_html_e( 'All Categories', 'jbportal' ); ?></option>
				<?php
				$cats = get_terms( array( 'taxonomy' => 'job_category', 'hide_empty' => false ) );
				if ( $cats && ! is_wp_error( $cats ) ) {
					foreach ( $cats as $c ) {
						$sel = in_array( $c->slug, array_map( 'sanitize_title', $cats_selected ), true ) ? 'selected' : '';
						printf( '<option value="%s" %s>%s</option>', esc_attr( $c->slug ), $sel, esc_html( $c->name ) );
					}
				}
				?>
			</select>
		</div>
		<div class="jb-field">
			<label class="jb-check"><input type="checkbox" name="remote" value="1" <?php checked( $remote ); ?>> <?php esc_html_e( 'Remote only', 'jbportal' ); ?></label>
		</div>
		<button type="submit" class="jb-btn jb-btn-primary"><?php esc_html_e( 'Search Jobs', 'jbportal' ); ?></button>
	</form>
	<?php
}
