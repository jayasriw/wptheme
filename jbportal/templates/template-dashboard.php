<?php
/**
 * Template Name: Dashboard
 *
 * @package jbportal
 */

get_header();
?>
<div class="jb-container jb-dashboard">
	<?php if ( ! is_user_logged_in() ) : ?>
		<div class="jb-card">
			<h1><?php esc_html_e( 'Dashboard', 'jbportal' ); ?></h1>
			<p><?php esc_html_e( 'Please sign in to view your dashboard.', 'jbportal' ); ?></p>
			<a class="jb-btn jb-btn-primary" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign in', 'jbportal' ); ?></a>
		</div>
	<?php else :
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'overview';
		$user = wp_get_current_user();
		$my_jobs = jbportal_get_my_jobs();
		$my_apps = jbportal_get_my_applications();
		$bookmarks = jbportal_get_user_bookmarks( $user->ID );
		?>
		<header class="jb-dashboard-header">
			<div>
				<h1><?php printf( esc_html__( 'Welcome back, %s', 'jbportal' ), esc_html( $user->display_name ) ); ?></h1>
				<p><?php esc_html_e( 'Manage your jobs, applications, and saved listings.', 'jbportal' ); ?></p>
			</div>
			<div>
				<a class="jb-btn jb-btn-primary" href="<?php echo esc_url( home_url( '/post-a-job/' ) ); ?>"><?php esc_html_e( '+ Post a Job', 'jbportal' ); ?></a>
			</div>
		</header>

		<nav class="jb-dashboard-tabs">
			<?php
			$tabs = array(
				'overview'     => __( 'Overview', 'jbportal' ),
				'jobs'         => __( 'My Jobs', 'jbportal' ),
				'applications' => __( 'Applications', 'jbportal' ),
				'bookmarks'    => __( 'Bookmarks', 'jbportal' ),
				'profile'      => __( 'Profile', 'jbportal' ),
			);
			foreach ( $tabs as $key => $label ) {
				$url   = add_query_arg( 'tab', $key, get_permalink() );
				$class = $tab === $key ? 'is-active' : '';
				printf( '<a class="%s" href="%s">%s</a>', esc_attr( $class ), esc_url( $url ), esc_html( $label ) );
			}
			?>
		</nav>

		<div class="jb-dashboard-body">
		<?php if ( 'overview' === $tab ) : ?>
			<div class="jb-stats jb-stats-dashboard">
				<div class="jb-stat"><span class="jb-stat-num"><?php echo count( $my_jobs ); ?></span><span class="jb-stat-label"><?php esc_html_e( 'My Jobs', 'jbportal' ); ?></span></div>
				<div class="jb-stat"><span class="jb-stat-num"><?php echo count( $my_apps ); ?></span><span class="jb-stat-label"><?php esc_html_e( 'Applications Sent', 'jbportal' ); ?></span></div>
				<div class="jb-stat"><span class="jb-stat-num"><?php echo count( $bookmarks ); ?></span><span class="jb-stat-label"><?php esc_html_e( 'Bookmarks', 'jbportal' ); ?></span></div>
				<?php
				$total_apps = 0;
				foreach ( $my_jobs as $j ) { $total_apps += jbportal_get_application_count( $j->ID ); }
				?>
				<div class="jb-stat"><span class="jb-stat-num"><?php echo (int) $total_apps; ?></span><span class="jb-stat-label"><?php esc_html_e( 'Applications Received', 'jbportal' ); ?></span></div>
			</div>

		<?php elseif ( 'jobs' === $tab ) : ?>
			<?php if ( $my_jobs ) : ?>
				<table class="jb-table">
					<thead><tr>
						<th><?php esc_html_e( 'Title', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Status', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Applications', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Posted', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'jbportal' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $my_jobs as $j ) :
						$count = jbportal_get_application_count( $j->ID );
						$del_url    = wp_nonce_url( add_query_arg( array( 'jbportal_action' => 'delete_job', 'job_id' => $j->ID ) ), 'jbportal_dashboard_' . $j->ID );
						$filled     = get_post_meta( $j->ID, '_job_filled', true );
						$toggle_url = wp_nonce_url( add_query_arg( array( 'jbportal_action' => $filled ? 'mark_open' : 'mark_filled', 'job_id' => $j->ID ) ), 'jbportal_dashboard_' . $j->ID );
						?>
						<tr>
							<td><a href="<?php echo esc_url( get_permalink( $j ) ); ?>"><?php echo esc_html( $j->post_title ); ?></a></td>
							<td><span class="jb-status jb-status-<?php echo esc_attr( $j->post_status ); ?>"><?php echo esc_html( $j->post_status ); ?></span></td>
							<td><?php echo (int) $count; ?></td>
							<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $j->post_date ) ); ?></td>
							<td class="jb-row-actions">
								<a href="<?php echo esc_url( get_edit_post_link( $j->ID ) ); ?>"><?php esc_html_e( 'Edit', 'jbportal' ); ?></a>
								<a href="<?php echo esc_url( $toggle_url ); ?>"><?php echo $filled ? esc_html__( 'Mark Open', 'jbportal' ) : esc_html__( 'Mark Filled', 'jbportal' ); ?></a>
								<a href="<?php echo esc_url( $del_url ); ?>" class="jb-danger" onclick="return confirm('<?php esc_attr_e( 'Move this job to trash?', 'jbportal' ); ?>')"><?php esc_html_e( 'Delete', 'jbportal' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'You haven\'t posted any jobs yet.', 'jbportal' ); ?></p>
				<a class="jb-btn jb-btn-primary" href="<?php echo esc_url( home_url( '/post-a-job/' ) ); ?>"><?php esc_html_e( 'Post your first job', 'jbportal' ); ?></a>
			<?php endif; ?>

		<?php elseif ( 'applications' === $tab ) : ?>
			<?php if ( $my_apps ) : ?>
				<table class="jb-table">
					<thead><tr>
						<th><?php esc_html_e( 'Job', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Submitted', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Resume', 'jbportal' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $my_apps as $a ) :
						$job_id = (int) get_post_meta( $a->ID, '_application_job_id', true );
						$res    = get_post_meta( $a->ID, '_application_resume_url', true );
						?>
						<tr>
							<td><a href="<?php echo esc_url( get_permalink( $job_id ) ); ?>"><?php echo esc_html( get_the_title( $job_id ) ); ?></a></td>
							<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $a->post_date ) ); ?></td>
							<td><?php echo $res ? '<a href="' . esc_url( $res ) . '" target="_blank" rel="noopener">' . esc_html__( 'View', 'jbportal' ) . '</a>' : '—'; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'You haven\'t applied to any jobs yet.', 'jbportal' ); ?></p>
			<?php endif; ?>

		<?php elseif ( 'bookmarks' === $tab ) : ?>
			<?php if ( $bookmarks ) :
				$q = new WP_Query( array( 'post_type' => 'job_listing', 'post__in' => $bookmarks, 'orderby' => 'post__in', 'posts_per_page' => -1 ) );
				?>
				<div class="jb-jobs-list">
					<?php while ( $q->have_posts() ) : $q->the_post(); get_template_part( 'template-parts/content', 'job' ); endwhile; wp_reset_postdata(); ?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'Save jobs from any listing to keep track of opportunities.', 'jbportal' ); ?></p>
			<?php endif; ?>

		<?php elseif ( 'profile' === $tab ) : ?>
			<div class="jb-card">
				<h2><?php esc_html_e( 'Your account', 'jbportal' ); ?></h2>
				<ul class="jb-info-list">
					<li><strong><?php esc_html_e( 'Username', 'jbportal' ); ?></strong><span><?php echo esc_html( $user->user_login ); ?></span></li>
					<li><strong><?php esc_html_e( 'Email', 'jbportal' ); ?></strong><span><?php echo esc_html( $user->user_email ); ?></span></li>
					<li><strong><?php esc_html_e( 'Member since', 'jbportal' ); ?></strong><span><?php echo esc_html( mysql2date( get_option( 'date_format' ), $user->user_registered ) ); ?></span></li>
				</ul>
				<a class="jb-btn jb-btn-ghost" href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>"><?php esc_html_e( 'Edit profile', 'jbportal' ); ?></a>
				<a class="jb-btn jb-btn-ghost" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log out', 'jbportal' ); ?></a>
			</div>
		<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
