<?php
/**
 * One-click demo data importer (Tools → jbportal Demo Data).
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_demo_admin_menu() {
	add_management_page(
		__( 'jbportal Demo Data', 'jbportal' ),
		__( 'jbportal Demo Data', 'jbportal' ),
		'manage_options',
		'jbportal-demo',
		'jbportal_demo_admin_page'
	);
}
add_action( 'admin_menu', 'jbportal_demo_admin_menu' );

function jbportal_demo_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$done = false;
	if ( ! empty( $_POST['jbportal_demo_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_demo_nonce'] ) ), 'jbportal_demo' ) ) {
		jbportal_seed_demo_content();
		$done = true;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'jbportal Demo Data', 'jbportal' ); ?></h1>
		<?php if ( $done ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Demo data installed.', 'jbportal' ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Installs sample companies, jobs, candidates and pages so you can preview the theme.', 'jbportal' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'jbportal_demo', 'jbportal_demo_nonce' ); ?>
			<p><button class="button button-primary" type="submit"><?php esc_html_e( 'Install demo content', 'jbportal' ); ?></button></p>
		</form>
	</div>
	<?php
}

function jbportal_seed_demo_content() {
	if ( get_option( 'jbportal_demo_seeded' ) ) {
		return;
	}

	// Pages.
	$pages = array(
		'post-a-job' => array( 'Post a Job', 'template-post-job.php' ),
		'dashboard'  => array( 'Dashboard', 'template-dashboard.php' ),
		'pricing'    => array( 'Pricing', 'template-pricing.php' ),
		'contact'    => array( 'Contact', 'template-contact.php' ),
		'employer-profile'  => array( 'Edit Company Profile', 'template-employer-profile.php' ),
		'candidate-profile' => array( 'Edit Candidate Profile', 'template-candidate-profile.php' ),
		'resumes'    => array( 'Resumes', 'template-resumes.php' ),
	);
	foreach ( $pages as $slug => $data ) {
		if ( ! get_page_by_path( $slug ) ) {
			$pid = wp_insert_post( array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => $data[0],
				'post_name'   => $slug,
			) );
			if ( $pid && ! is_wp_error( $pid ) ) {
				update_post_meta( $pid, '_wp_page_template', $data[1] );
			}
		}
	}

	// Companies.
	$companies = array(
		array( 'NorthBeam',      'Engineering team building reliable data platforms.', 'Engineering',  '50-200', 2018 ),
		array( 'Brightline',     'A consumer product studio shipping delightful apps.', 'Design',       '11-50',  2020 ),
		array( 'Helix Health',   'Modern healthcare for everyone.',                     'Healthcare',   '200-500', 2015 ),
		array( 'Stratus Cloud',  'Infrastructure for the next billion users.',          'Engineering',  '500+',   2012 ),
		array( 'Kindred Books',  'An independent publisher of literary fiction.',       'Media',        '11-50',  2009 ),
		array( 'Acorn Finance',  'Modern banking for small businesses.',                'Finance',      '50-200', 2017 ),
	);
	$company_ids = array();
	foreach ( $companies as $c ) {
		$id = wp_insert_post( array(
			'post_type'    => 'company',
			'post_status'  => 'publish',
			'post_title'   => $c[0],
			'post_content' => $c[1],
			'post_excerpt' => $c[1],
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_company_size', $c[3] );
			update_post_meta( $id, '_company_founded', $c[4] );
			update_post_meta( $id, '_company_website', 'https://example.com' );
			update_post_meta( $id, '_company_verified', 1 );
			$company_ids[ $c[0] ] = $id;
		}
	}

	// Jobs.
	$jobs = array(
		array( 'Senior Frontend Engineer', 'NorthBeam',     'Engineering', 'Full Time',  'San Francisco, CA', 130000, 175000, 1, 1, 0 ),
		array( 'Product Designer',         'Brightline',    'Design',      'Full Time',  'Remote',            90000,  130000, 1, 0, 1 ),
		array( 'Backend Developer (Go)',   'Stratus Cloud', 'Engineering', 'Full Time',  'Berlin, Germany',  120000, 160000, 0, 0, 0 ),
		array( 'Marketing Manager',        'Acorn Finance', 'Marketing',   'Full Time',  'New York, NY',     95000,  130000, 0, 0, 0 ),
		array( 'Customer Success Lead',    'Brightline',    'Customer Service', 'Full Time', 'Remote',       70000,  95000,  0, 0, 1 ),
		array( 'Clinical Operations Lead', 'Helix Health',  'Healthcare',  'Full Time',  'Boston, MA',       110000, 145000, 1, 1, 0 ),
		array( 'Editorial Intern',         'Kindred Books', 'Education',   'Internship', 'London, UK',       25000,  30000,  0, 0, 0 ),
		array( 'Data Scientist',           'Stratus Cloud', 'Engineering', 'Contract',   'Remote',           700,    900,    1, 0, 1 ),
	);
	foreach ( $jobs as $j ) {
		list( $title, $company, $cat, $type, $loc, $smin, $smax, $featured, $urgent, $remote ) = $j;
		$id = wp_insert_post( array(
			'post_type'    => 'job_listing',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => "We're looking for an experienced $title to join $company. You'll work on high-impact problems with a small, collaborative team.",
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_job_company', $company );
			if ( ! empty( $company_ids[ $company ] ) ) { update_post_meta( $id, '_job_company_id', $company_ids[ $company ] ); }
			update_post_meta( $id, '_job_location', $loc );
			update_post_meta( $id, '_job_salary_min', $smin );
			update_post_meta( $id, '_job_salary_max', $smax );
			update_post_meta( $id, '_job_salary_currency', '$' );
			update_post_meta( $id, '_job_salary_period', 'year' );
			if ( $featured ) { update_post_meta( $id, '_job_featured', 1 ); }
			if ( $urgent )   { update_post_meta( $id, '_job_urgent', 1 ); }
			if ( $remote )   { update_post_meta( $id, '_job_remote', 1 ); }
			wp_set_object_terms( $id, $type, 'job_type' );
			wp_set_object_terms( $id, $cat, 'job_category' );
		}
	}

	// Candidates.
	$candidates = array(
		array( 'Priya Raman',  'Senior Frontend Developer', 'London, UK',   6, 1 ),
		array( 'Diego Salinas','Product Manager',           'Mexico City',  8, 0 ),
		array( 'Amara Okafor', 'HR Operations Lead',        'Lagos, NG',    10, 1 ),
		array( 'Mei Tanaka',   'UX Researcher',             'Tokyo, JP',    5, 1 ),
	);
	foreach ( $candidates as $c ) {
		$id = wp_insert_post( array(
			'post_type'    => 'candidate',
			'post_status'  => 'publish',
			'post_title'   => $c[0],
			'post_content' => $c[1] . ' open to remote roles.',
			'post_excerpt' => $c[1],
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_candidate_title', $c[1] );
			update_post_meta( $id, '_candidate_location', $c[2] );
			update_post_meta( $id, '_candidate_experience', $c[3] );
			update_post_meta( $id, '_candidate_available', $c[4] );
		}
	}

	update_option( 'jbportal_demo_seeded', 1 );
}
