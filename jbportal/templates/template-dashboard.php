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
		$tab        = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'overview';
		$user       = wp_get_current_user();
		$my_jobs    = jbportal_get_my_jobs();
		$my_apps    = jbportal_get_my_applications();
		$bookmarks  = jbportal_get_user_bookmarks( $user->ID );
		$is_emp     = jbportal_user_is_employer( $user->ID );
		$is_cand    = jbportal_user_is_candidate( $user->ID );
		$unread     = jbportal_get_unread_count( $user->ID );
		$ok_msg     = get_transient( 'jbportal_msg_ok_' . $user->ID );
		if ( $ok_msg ) { delete_transient( 'jbportal_msg_ok_' . $user->ID ); }
		?>
		<header class="jb-dashboard-header">
			<div>
				<h1><?php printf( esc_html__( 'Welcome back, %s', 'jbportal' ), esc_html( $user->display_name ) ); ?></h1>
				<p>
					<?php if ( $is_emp )  : ?><span class="jb-badge jb-badge-featured"><?php esc_html_e( 'Employer', 'jbportal' ); ?></span><?php endif; ?>
					<?php if ( $is_cand ) : ?><span class="jb-badge jb-badge-available"><?php esc_html_e( 'Candidate', 'jbportal' ); ?></span><?php endif; ?>
					<?php esc_html_e( 'Manage your jobs, applications, messages and profile.', 'jbportal' ); ?>
				</p>
			</div>
			<div>
				<?php if ( $is_emp ) : ?>
					<a class="jb-btn jb-btn-primary" href="<?php echo esc_url( home_url( '/post-a-job/' ) ); ?>"><?php esc_html_e( '+ Post a Job', 'jbportal' ); ?></a>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( $ok_msg ) : ?><div class="jb-notice jb-notice-success"><?php echo esc_html( $ok_msg ); ?></div><?php endif; ?>

		<nav class="jb-dashboard-tabs">
			<?php
			$tabs = array( 'overview' => __( 'Overview', 'jbportal' ) );
			if ( $is_emp ) {
				$tabs['jobs']             = __( 'My Jobs', 'jbportal' );
				$tabs['received']         = __( 'Received Applications', 'jbportal' );
				$tabs['meetings']         = __( 'Meetings', 'jbportal' );
				$tabs['invitations']      = __( 'Invitations', 'jbportal' );
				$tabs['employer-profile'] = __( 'Company Profile', 'jbportal' );
			}
			if ( $is_cand || ! $is_emp ) {
				$tabs['applications']      = __( 'My Applications', 'jbportal' );
				$tabs['bookmarks']         = __( 'Bookmarks', 'jbportal' );
				$tabs['alerts']            = __( 'Job Alerts', 'jbportal' );
				$tabs['meetings']          = __( 'Interviews', 'jbportal' );
				$tabs['invitations']       = __( 'Invitations', 'jbportal' );
				$tabs['candidate-profile'] = __( 'Candidate Profile', 'jbportal' );
			}
			$tabs['messages'] = __( 'Messages', 'jbportal' ) . ( $unread ? ' (' . (int) $unread . ')' : '' );
			$tabs['profile']  = __( 'Account', 'jbportal' );

			foreach ( $tabs as $key => $label ) {
				$url   = add_query_arg( 'tab', $key, get_permalink() );
				$class = $tab === $key ? 'is-active' : '';
				printf( '<a class="%s" href="%s">%s</a>', esc_attr( $class ), esc_url( $url ), esc_html( $label ) );
			}
			?>
		</nav>

		<div class="jb-dashboard-body">
		<?php if ( 'overview' === $tab ) :
			$received_total = 0;
			foreach ( $my_jobs as $j ) { $received_total += jbportal_get_application_count( $j->ID ); }
			?>
			<div class="jb-stats jb-stats-dashboard">
				<div class="jb-stat"><span class="jb-stat-num"><?php echo count( $my_jobs ); ?></span><span class="jb-stat-label"><?php esc_html_e( 'Jobs Posted', 'jbportal' ); ?></span></div>
				<div class="jb-stat"><span class="jb-stat-num"><?php echo (int) $received_total; ?></span><span class="jb-stat-label"><?php esc_html_e( 'Applications Received', 'jbportal' ); ?></span></div>
				<div class="jb-stat"><span class="jb-stat-num"><?php echo count( $my_apps ); ?></span><span class="jb-stat-label"><?php esc_html_e( 'Applications Sent', 'jbportal' ); ?></span></div>
				<div class="jb-stat"><span class="jb-stat-num"><?php echo count( $bookmarks ); ?></span><span class="jb-stat-label"><?php esc_html_e( 'Bookmarks', 'jbportal' ); ?></span></div>
				<div class="jb-stat"><span class="jb-stat-num"><?php echo (int) $unread; ?></span><span class="jb-stat-label"><?php esc_html_e( 'Unread Messages', 'jbportal' ); ?></span></div>
				<?php if ( jbportal_wc_active() ) :
					$credits = (int) get_user_meta( $user->ID, 'jb_job_credits', true ); ?>
					<div class="jb-stat"><span class="jb-stat-num"><?php echo (int) $credits; ?></span><span class="jb-stat-label"><?php esc_html_e( 'Job Credits', 'jbportal' ); ?></span></div>
				<?php endif; ?>
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
						$count      = jbportal_get_application_count( $j->ID );
						$filled     = get_post_meta( $j->ID, '_job_filled', true );
						$del_url    = wp_nonce_url( add_query_arg( array( 'jbportal_action' => 'delete_job', 'job_id' => $j->ID ) ), 'jbportal_dashboard_' . $j->ID );
						$toggle_url = wp_nonce_url( add_query_arg( array( 'jbportal_action' => $filled ? 'mark_open' : 'mark_filled', 'job_id' => $j->ID ) ), 'jbportal_dashboard_' . $j->ID );
						?>
						<tr>
							<td><a href="<?php echo esc_url( get_permalink( $j ) ); ?>"><?php echo esc_html( $j->post_title ); ?></a></td>
							<td><span class="jb-status jb-status-<?php echo esc_attr( $j->post_status ); ?>"><?php echo esc_html( $j->post_status ); ?></span></td>
							<td><a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'received', 'job' => $j->ID ), get_permalink() ) ); ?>"><?php echo (int) $count; ?></a></td>
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

		<?php elseif ( 'received' === $tab ) :
			$job_ids = wp_list_pluck( $my_jobs, 'ID' );
			$filter  = isset( $_GET['job'] ) ? (int) $_GET['job'] : 0;
			$args = array(
				'post_type'      => 'job_application',
				'posts_per_page' => -1,
				'post_status'    => 'private',
				'meta_query'     => array(
					array( 'key' => '_application_job_id', 'value' => $filter ? array( $filter ) : $job_ids, 'compare' => 'IN' ),
				),
			);
			$apps    = $job_ids ? get_posts( $args ) : array();
			$statuses = jbportal_application_statuses();
			?>
			<?php if ( $apps ) : ?>
				<table class="jb-table">
					<thead><tr>
						<th><?php esc_html_e( 'Applicant', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Job', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Submitted', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Status', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Resume', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Contact', 'jbportal' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $apps as $a ) :
						$name   = get_post_meta( $a->ID, '_application_name', true );
						$email  = get_post_meta( $a->ID, '_application_email', true );
						$resume = get_post_meta( $a->ID, '_application_resume_url', true );
						$status = get_post_meta( $a->ID, '_application_status', true ) ?: 'new';
						$job_id = (int) get_post_meta( $a->ID, '_application_job_id', true );
						?>
						<tr>
							<td><?php echo esc_html( $name ); ?></td>
							<td><a href="<?php echo esc_url( get_permalink( $job_id ) ); ?>"><?php echo esc_html( get_the_title( $job_id ) ); ?></a></td>
							<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $a->post_date ) ); ?></td>
							<td>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
									<input type="hidden" name="action" value="jbportal_update_app_status">
									<input type="hidden" name="app_id" value="<?php echo esc_attr( $a->ID ); ?>">
									<?php wp_nonce_field( 'jbportal_app_status_' . $a->ID ); ?>
									<select name="status" onchange="this.form.submit()">
										<?php foreach ( $statuses as $k => $l ) { printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $status, $k, false ), esc_html( $l ) ); } ?>
									</select>
								</form>
							</td>
							<td><?php echo $resume ? '<a href="' . esc_url( $resume ) . '" target="_blank" rel="noopener">' . esc_html__( 'View', 'jbportal' ) . '</a>' : '—'; ?></td>
							<td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'No applications received yet.', 'jbportal' ); ?></p>
			<?php endif; ?>

		<?php elseif ( 'applications' === $tab ) : ?>
			<?php if ( $my_apps ) :
				$statuses = jbportal_application_statuses();
				?>
				<table class="jb-table">
					<thead><tr>
						<th><?php esc_html_e( 'Job', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Submitted', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Status', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Resume', 'jbportal' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $my_apps as $a ) :
						$job_id = (int) get_post_meta( $a->ID, '_application_job_id', true );
						$res    = get_post_meta( $a->ID, '_application_resume_url', true );
						$status = get_post_meta( $a->ID, '_application_status', true ) ?: 'new';
						?>
						<tr>
							<td><a href="<?php echo esc_url( get_permalink( $job_id ) ); ?>"><?php echo esc_html( get_the_title( $job_id ) ); ?></a></td>
							<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $a->post_date ) ); ?></td>
							<td><span class="jb-status jb-status-app jb-status-app-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $statuses[ $status ] ?? $status ); ?></span></td>
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

		<?php elseif ( 'alerts' === $tab ) :
			$alerts = get_posts( array( 'post_type' => 'job_alert', 'posts_per_page' => -1, 'author' => $user->ID ) ); ?>
			<div class="jb-card">
				<h3><?php esc_html_e( 'Create a new job alert', 'jbportal' ); ?></h3>
				<?php jbportal_render_alert_form(); ?>
			</div>
			<?php if ( $alerts ) : ?>
				<table class="jb-table">
					<thead><tr><th><?php esc_html_e( 'Keyword', 'jbportal' ); ?></th><th><?php esc_html_e( 'Location', 'jbportal' ); ?></th><th><?php esc_html_e( 'Email', 'jbportal' ); ?></th><th></th></tr></thead>
					<tbody>
					<?php foreach ( $alerts as $al ) :
						$token = get_post_meta( $al->ID, '_alert_token', true );
						$unsub = add_query_arg( array( 'jbportal_unsubscribe' => 1, 'id' => $al->ID, 'token' => $token ), home_url( '/' ) );
						?>
						<tr>
							<td><?php echo esc_html( get_post_meta( $al->ID, '_alert_keyword', true ) ?: __( 'Any', 'jbportal' ) ); ?></td>
							<td><?php echo esc_html( get_post_meta( $al->ID, '_alert_location', true ) ?: '—' ); ?></td>
							<td><?php echo esc_html( get_post_meta( $al->ID, '_alert_email', true ) ); ?></td>
							<td><a href="<?php echo esc_url( $unsub ); ?>" class="jb-danger"><?php esc_html_e( 'Delete', 'jbportal' ); ?></a></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

		<?php elseif ( 'messages' === $tab ) :
			$messages = jbportal_get_user_messages( $user->ID );
			?>
			<?php if ( $messages ) : ?>
				<ul class="jb-message-list">
					<?php foreach ( $messages as $m ) :
						$from   = (int) get_post_meta( $m->ID, '_msg_from', true );
						$to     = (int) get_post_meta( $m->ID, '_msg_to', true );
						$other  = $from === $user->ID ? $to : $from;
						$peer   = get_userdata( $other );
						$is_in  = $to === $user->ID;
						if ( $is_in ) { update_post_meta( $m->ID, '_msg_read', 1 ); }
						?>
						<li class="jb-message <?php echo $is_in ? 'jb-message-in' : 'jb-message-out'; ?>">
							<div class="jb-message-head">
								<strong><?php echo esc_html( $peer ? $peer->display_name : __( 'Unknown', 'jbportal' ) ); ?></strong>
								<span><?php echo esc_html( human_time_diff( get_the_time( 'U', $m ), current_time( 'timestamp' ) ) ); ?> <?php esc_html_e( 'ago', 'jbportal' ); ?></span>
							</div>
							<div class="jb-message-subject"><?php echo esc_html( $m->post_title ); ?></div>
							<p><?php echo esc_html( $m->post_content ); ?></p>
							<?php if ( $is_in && $peer ) : ?>
								<details>
									<summary><?php esc_html_e( 'Reply', 'jbportal' ); ?></summary>
									<form class="jb-form" method="post">
										<?php wp_nonce_field( 'jbportal_send_message', 'jbportal_message_nonce' ); ?>
										<input type="hidden" name="to_user" value="<?php echo esc_attr( $peer->ID ); ?>">
										<input type="hidden" name="thread_id" value="<?php echo esc_attr( get_post_meta( $m->ID, '_msg_thread', true ) ); ?>">
										<input type="hidden" name="msg_subject" value="Re: <?php echo esc_attr( $m->post_title ); ?>">
										<textarea name="msg_body" rows="3" required></textarea>
										<button class="jb-btn jb-btn-primary jb-btn-sm" type="submit"><?php esc_html_e( 'Send reply', 'jbportal' ); ?></button>
									</form>
								</details>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e( 'No messages yet.', 'jbportal' ); ?></p>
			<?php endif; ?>

		<?php elseif ( 'meetings' === $tab ) :
			$meetings     = jbportal_get_user_meetings( $user->ID );
			$meeting_ok   = get_transient( 'jbportal_meeting_ok_' . $user->ID );
			if ( $meeting_ok ) { delete_transient( 'jbportal_meeting_ok_' . $user->ID ); }
			?>
			<?php if ( $meeting_ok ) : ?><div class="jb-notice jb-notice-success"><?php echo esc_html( $meeting_ok ); ?></div><?php endif; ?>

			<?php if ( $is_emp ) : ?>
				<div class="jb-card" style="margin-bottom:2rem">
					<h3><?php esc_html_e( 'Schedule a meeting', 'jbportal' ); ?></h3>
					<form class="jb-form" method="post">
						<?php wp_nonce_field( 'jbportal_create_meeting', 'jbportal_meeting_nonce' ); ?>
						<label><?php esc_html_e( 'Candidate (user ID)', 'jbportal' ); ?><input type="number" name="meeting_with" required min="1"></label>
						<label><?php esc_html_e( 'Subject', 'jbportal' ); ?><input type="text" name="meeting_subject" required></label>
						<label><?php esc_html_e( 'Date &amp; Time', 'jbportal' ); ?><input type="datetime-local" name="meeting_when" required></label>
						<label><?php esc_html_e( 'Meeting link (Zoom / Meet)', 'jbportal' ); ?><input type="url" name="meeting_url" placeholder="https://"></label>
						<label><?php esc_html_e( 'Notes', 'jbportal' ); ?><textarea name="meeting_notes" rows="3"></textarea></label>
						<button class="jb-btn jb-btn-primary" type="submit"><?php esc_html_e( 'Schedule &amp; Send invite', 'jbportal' ); ?></button>
					</form>
				</div>
			<?php endif; ?>

			<?php if ( $meetings ) : ?>
				<table class="jb-table">
					<thead><tr>
						<th><?php esc_html_e( 'Subject', 'jbportal' ); ?></th>
						<th><?php esc_html_e( $is_emp ? 'With' : 'Organiser', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'When', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Link', 'jbportal' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $meetings as $m ) :
						$organizer  = (int) get_post_meta( $m->ID, '_meeting_organizer', true );
						$with       = (int) get_post_meta( $m->ID, '_meeting_with', true );
						$peer_id    = $organizer === $user->ID ? $with : $organizer;
						$peer       = get_userdata( $peer_id );
						$when       = get_post_meta( $m->ID, '_meeting_when', true );
						$url        = get_post_meta( $m->ID, '_meeting_url', true );
						?>
						<tr>
							<td><?php echo esc_html( $m->post_title ); ?></td>
							<td><?php echo esc_html( $peer ? $peer->display_name : '—' ); ?></td>
							<td><?php echo esc_html( $when ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $when ) ) : '—' ); ?></td>
							<td><?php echo $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Join', 'jbportal' ) . '</a>' : '—'; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'No meetings scheduled yet.', 'jbportal' ); ?></p>
			<?php endif; ?>

		<?php elseif ( 'invitations' === $tab ) :
			$invite_ok = get_transient( 'jbportal_invite_ok_' . $user->ID );
			if ( $invite_ok ) { delete_transient( 'jbportal_invite_ok_' . $user->ID ); }
			?>
			<?php if ( $invite_ok ) : ?><div class="jb-notice jb-notice-success"><?php echo esc_html( $invite_ok ); ?></div><?php endif; ?>

			<?php if ( $is_emp ) :
				$sent_invitations = get_posts( array(
					'post_type'      => 'jb_invitation',
					'posts_per_page' => -1,
					'post_status'    => 'private',
					'author'         => $user->ID,
				) );
				?>
				<div class="jb-card" style="margin-bottom:2rem">
					<h3><?php esc_html_e( 'Invite a candidate', 'jbportal' ); ?></h3>
					<form class="jb-form" method="post">
						<?php wp_nonce_field( 'jbportal_invite', 'jbportal_invite_nonce' ); ?>
						<label><?php esc_html_e( 'Candidate post ID', 'jbportal' ); ?><input type="number" name="candidate_post_id" required min="1"></label>
						<label><?php esc_html_e( 'Job', 'jbportal' ); ?>
							<select name="invite_job_id" required>
								<option value=""><?php esc_html_e( '— select a job —', 'jbportal' ); ?></option>
								<?php foreach ( $my_jobs as $j ) : ?>
									<option value="<?php echo esc_attr( $j->ID ); ?>"><?php echo esc_html( $j->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<label><?php esc_html_e( 'Message', 'jbportal' ); ?><textarea name="invite_message" rows="4"></textarea></label>
						<button class="jb-btn jb-btn-primary" type="submit"><?php esc_html_e( 'Send Invitation', 'jbportal' ); ?></button>
					</form>
				</div>
				<?php if ( $sent_invitations ) : ?>
					<h3><?php esc_html_e( 'Sent invitations', 'jbportal' ); ?></h3>
					<table class="jb-table">
						<thead><tr>
							<th><?php esc_html_e( 'Job', 'jbportal' ); ?></th>
							<th><?php esc_html_e( 'Candidate', 'jbportal' ); ?></th>
							<th><?php esc_html_e( 'Status', 'jbportal' ); ?></th>
							<th><?php esc_html_e( 'Sent', 'jbportal' ); ?></th>
						</tr></thead>
						<tbody>
						<?php foreach ( $sent_invitations as $inv ) :
							$cand_post = (int) get_post_meta( $inv->ID, '_invite_candidate_post', true );
							$job_inv   = (int) get_post_meta( $inv->ID, '_invite_job_id', true );
							$inv_stat  = get_post_meta( $inv->ID, '_invite_status', true ) ?: 'sent';
							?>
							<tr>
								<td><?php echo esc_html( get_the_title( $job_inv ) ); ?></td>
								<td><?php echo $cand_post ? '<a href="' . esc_url( get_permalink( $cand_post ) ) . '">' . esc_html( get_the_title( $cand_post ) ) . '</a>' : '—'; ?></td>
								<td><span class="jb-badge"><?php echo esc_html( $inv_stat ); ?></span></td>
								<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $inv->post_date ) ); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php else :
				$received_invitations = jbportal_get_user_invitations( $user->ID );
				?>
				<?php if ( $received_invitations ) : ?>
					<table class="jb-table">
						<thead><tr>
							<th><?php esc_html_e( 'Job', 'jbportal' ); ?></th>
							<th><?php esc_html_e( 'From', 'jbportal' ); ?></th>
							<th><?php esc_html_e( 'Message', 'jbportal' ); ?></th>
							<th><?php esc_html_e( 'Status', 'jbportal' ); ?></th>
							<th><?php esc_html_e( 'Received', 'jbportal' ); ?></th>
							<th></th>
						</tr></thead>
						<tbody>
						<?php foreach ( $received_invitations as $inv ) :
							$job_inv   = (int) get_post_meta( $inv->ID, '_invite_job_id', true );
							$from_user = get_userdata( $inv->post_author );
							$inv_stat  = get_post_meta( $inv->ID, '_invite_status', true ) ?: 'sent';
							?>
							<tr>
								<td><?php echo $job_inv ? '<a href="' . esc_url( get_permalink( $job_inv ) ) . '">' . esc_html( get_the_title( $job_inv ) ) . '</a>' : '—'; ?></td>
								<td><?php echo esc_html( $from_user ? $from_user->display_name : '—' ); ?></td>
								<td><?php echo esc_html( $inv->post_content ); ?></td>
								<td><span class="jb-badge"><?php echo esc_html( $inv_stat ); ?></span></td>
								<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $inv->post_date ) ); ?></td>
								<td>
									<?php if ( 'sent' === $inv_stat && $job_inv ) : ?>
										<a class="jb-btn jb-btn-primary jb-btn-sm" href="<?php echo esc_url( get_permalink( $job_inv ) ); ?>"><?php esc_html_e( 'Apply', 'jbportal' ); ?></a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php esc_html_e( 'No invitations received yet.', 'jbportal' ); ?></p>
				<?php endif; ?>
			<?php endif; ?>

		<?php elseif ( 'employer-profile' === $tab ) : ?>
			<p><a class="jb-btn jb-btn-primary" href="<?php echo esc_url( home_url( '/employer-profile/' ) ); ?>"><?php esc_html_e( 'Edit your company profile →', 'jbportal' ); ?></a></p>

		<?php elseif ( 'candidate-profile' === $tab ) : ?>
			<p><a class="jb-btn jb-btn-primary" href="<?php echo esc_url( home_url( '/candidate-profile/' ) ); ?>"><?php esc_html_e( 'Edit your candidate profile →', 'jbportal' ); ?></a></p>

		<?php elseif ( 'profile' === $tab ) : ?>
			<div class="jb-card">
				<h2><?php esc_html_e( 'Your account', 'jbportal' ); ?></h2>
				<ul class="jb-info-list">
					<li><strong><?php esc_html_e( 'Username', 'jbportal' ); ?></strong><span><?php echo esc_html( $user->user_login ); ?></span></li>
					<li><strong><?php esc_html_e( 'Email', 'jbportal' ); ?></strong><span><?php echo esc_html( $user->user_email ); ?></span></li>
					<li><strong><?php esc_html_e( 'Role', 'jbportal' ); ?></strong><span><?php echo esc_html( implode( ', ', $user->roles ) ); ?></span></li>
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
