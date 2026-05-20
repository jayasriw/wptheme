<?php
/**
 * Analytics dashboard — admin page with Chart.js graphs.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'jbportal_analytics_menu' );
function jbportal_analytics_menu() {
	add_submenu_page(
		'edit.php?post_type=job_listing',
		esc_html__( 'Analytics', 'jbportal' ),
		esc_html__( 'Analytics', 'jbportal' ),
		'manage_options',
		'jbportal-analytics',
		'jbportal_analytics_page'
	);
}

add_action( 'admin_enqueue_scripts', 'jbportal_analytics_assets' );
function jbportal_analytics_assets( $hook ) {
	if ( strpos( $hook, 'jbportal-analytics' ) === false ) {
		return;
	}
	wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true );
	wp_enqueue_style( 'jbportal-admin' );
}

/**
 * Return daily view counts for a CPT over the past N days.
 *
 * Views are stored as `_jb_views` total, not per-day; we approximate a
 * distribution by reading `_jb_daily_views_{Y-m-d}` which jbportal_track_views()
 * also writes, falling back to 0 when absent.
 */
function jbportal_analytics_daily_views( $post_type, $days = 30 ) {
	$labels = array();
	$data   = array();

	global $wpdb;
	for ( $i = $days - 1; $i >= 0; $i-- ) {
		$date     = date( 'Y-m-d', strtotime( "-{$i} days" ) );
		$labels[] = date( 'M j', strtotime( $date ) );
		$key      = '_jb_daily_views_' . $date;
		$sum      = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM( CAST( meta_value AS UNSIGNED ) )
			 FROM {$wpdb->postmeta} pm
			 JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'",
			$key,
			$post_type
		) );
		$data[] = $sum;
	}

	return array( 'labels' => $labels, 'data' => $data );
}

/**
 * Application counts grouped by status.
 */
function jbportal_analytics_app_statuses() {
	global $wpdb;
	$statuses = jbportal_application_statuses();
	$counts   = array();
	foreach ( $statuses as $key => $label ) {
		$n = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_application_status' AND meta_value = %s",
			$key
		) );
		$counts[ $label ] = $n;
	}
	return $counts;
}

/**
 * Jobs posted per month (last 6 months).
 */
function jbportal_analytics_jobs_per_month() {
	global $wpdb;
	$labels = array();
	$data   = array();
	for ( $i = 5; $i >= 0; $i-- ) {
		$month    = date( 'Y-m', strtotime( "-{$i} months" ) );
		$labels[] = date( 'M Y', strtotime( $month . '-01' ) );
		$count    = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_type = 'job_listing' AND post_status = 'publish'
			 AND DATE_FORMAT( post_date, '%%Y-%%m' ) = %s",
			$month
		) );
		$data[] = $count;
	}
	return array( 'labels' => $labels, 'data' => $data );
}

/**
 * Render the analytics admin page.
 */
function jbportal_analytics_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Forbidden', 'jbportal' ) );
	}

	$views_job   = jbportal_analytics_daily_views( 'job_listing', 30 );
	$views_comp  = jbportal_analytics_daily_views( 'company', 30 );
	$app_status  = jbportal_analytics_app_statuses();
	$jobs_month  = jbportal_analytics_jobs_per_month();

	$total_jobs  = wp_count_posts( 'job_listing' )->publish ?? 0;
	$total_comp  = wp_count_posts( 'company' )->publish ?? 0;
	$total_cand  = wp_count_posts( 'candidate' )->publish ?? 0;
	$total_apps  = wp_count_posts( 'job_application' )->publish ?? 0;

	$jbv = wp_json_encode;
	?>
	<div class="wrap jb-analytics-wrap">
		<h1><?php esc_html_e( 'jbportal Analytics', 'jbportal' ); ?></h1>

		<div class="jb-admin-stat-row">
			<div class="jb-admin-stat-card">
				<span class="jb-admin-stat-num"><?php echo (int) $total_jobs; ?></span>
				<span><?php esc_html_e( 'Live Jobs', 'jbportal' ); ?></span>
			</div>
			<div class="jb-admin-stat-card">
				<span class="jb-admin-stat-num"><?php echo (int) $total_comp; ?></span>
				<span><?php esc_html_e( 'Companies', 'jbportal' ); ?></span>
			</div>
			<div class="jb-admin-stat-card">
				<span class="jb-admin-stat-num"><?php echo (int) $total_cand; ?></span>
				<span><?php esc_html_e( 'Candidates', 'jbportal' ); ?></span>
			</div>
			<div class="jb-admin-stat-card">
				<span class="jb-admin-stat-num"><?php echo (int) $total_apps; ?></span>
				<span><?php esc_html_e( 'Applications', 'jbportal' ); ?></span>
			</div>
		</div>

		<div class="jb-admin-charts-grid">
			<div class="jb-admin-chart-card">
				<h2><?php esc_html_e( 'Job Views — last 30 days', 'jbportal' ); ?></h2>
				<canvas id="jb-chart-job-views" height="120"></canvas>
			</div>
			<div class="jb-admin-chart-card">
				<h2><?php esc_html_e( 'Company Views — last 30 days', 'jbportal' ); ?></h2>
				<canvas id="jb-chart-comp-views" height="120"></canvas>
			</div>
			<div class="jb-admin-chart-card">
				<h2><?php esc_html_e( 'Application Pipeline', 'jbportal' ); ?></h2>
				<canvas id="jb-chart-app-status" height="120"></canvas>
			</div>
			<div class="jb-admin-chart-card">
				<h2><?php esc_html_e( 'Jobs Posted — last 6 months', 'jbportal' ); ?></h2>
				<canvas id="jb-chart-jobs-month" height="120"></canvas>
			</div>
		</div>
	</div>

	<style>
	.jb-analytics-wrap { max-width: 1200px; }
	.jb-admin-stat-row { display: flex; gap: 16px; margin: 20px 0; flex-wrap: wrap; }
	.jb-admin-stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px 28px; min-width: 150px; text-align: center; }
	.jb-admin-stat-num { display: block; font-size: 2rem; font-weight: 700; color: #0d9488; }
	.jb-admin-charts-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(480px,1fr)); gap: 20px; }
	.jb-admin-chart-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; }
	.jb-admin-chart-card h2 { margin: 0 0 16px; font-size: 1rem; color: #374151; }
	</style>

	<script>
	(function(){
		var primary  = '#0d9488';
		var coral    = '#fb7185';
		var amber    = '#f59e0b';
		var slate    = '#64748b';
		var defaults = { responsive: true, plugins: { legend: { display: false } } };

		/* Job views line chart */
		new Chart(document.getElementById('jb-chart-job-views'), {
			type: 'line',
			data: {
				labels: <?php echo wp_json_encode( $views_job['labels'] ); ?>,
				datasets: [{ label: '<?php esc_html_e( 'Views', 'jbportal' ); ?>', data: <?php echo wp_json_encode( $views_job['data'] ); ?>, borderColor: primary, backgroundColor: primary + '22', fill: true, tension: 0.3, pointRadius: 2 }]
			},
			options: defaults
		});

		/* Company views line chart */
		new Chart(document.getElementById('jb-chart-comp-views'), {
			type: 'line',
			data: {
				labels: <?php echo wp_json_encode( $views_comp['labels'] ); ?>,
				datasets: [{ label: '<?php esc_html_e( 'Views', 'jbportal' ); ?>', data: <?php echo wp_json_encode( $views_comp['data'] ); ?>, borderColor: coral, backgroundColor: coral + '22', fill: true, tension: 0.3, pointRadius: 2 }]
			},
			options: defaults
		});

		/* Application pipeline doughnut */
		var appLabels = <?php echo wp_json_encode( array_keys( $app_status ) ); ?>;
		var appData   = <?php echo wp_json_encode( array_values( $app_status ) ); ?>;
		new Chart(document.getElementById('jb-chart-app-status'), {
			type: 'doughnut',
			data: {
				labels: appLabels,
				datasets: [{ data: appData, backgroundColor: [primary, coral, amber, slate, '#10b981', '#ef4444'] }]
			},
			options: { responsive: true, plugins: { legend: { position: 'right' } } }
		});

		/* Jobs per month bar chart */
		new Chart(document.getElementById('jb-chart-jobs-month'), {
			type: 'bar',
			data: {
				labels: <?php echo wp_json_encode( $jobs_month['labels'] ); ?>,
				datasets: [{ label: '<?php esc_html_e( 'Jobs posted', 'jbportal' ); ?>', data: <?php echo wp_json_encode( $jobs_month['data'] ); ?>, backgroundColor: primary + 'cc', borderRadius: 4 }]
			},
			options: defaults
		});
	})();
	</script>
	<?php
}

/**
 * Also write per-day view increments alongside the total in views-counter.php.
 * Hook added here so it stays co-located with analytics.
 */
add_action( 'jbportal_view_tracked', 'jbportal_analytics_record_daily_view', 10, 1 );
function jbportal_analytics_record_daily_view( $post_id ) {
	$key     = '_jb_daily_views_' . date( 'Y-m-d' );
	$current = (int) get_post_meta( $post_id, $key, true );
	update_post_meta( $post_id, $key, $current + 1 );
}
