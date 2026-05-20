<?php
/**
 * Elementor native widgets for jbportal.
 *
 * Registers widgets only when Elementor is active. Each widget exposes
 * controls mapped to the matching shortcode or query so designers can
 * configure them visually inside the Elementor editor.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'elementor/widgets/register', 'jbportal_register_elementor_widgets' );
function jbportal_register_elementor_widgets( $manager ) {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}

	$widgets = array(
		'JBPortal_Jobs_Widget',
		'JBPortal_Companies_Widget',
		'JBPortal_Categories_Widget',
		'JBPortal_Stats_Widget',
		'JBPortal_Pricing_Widget',
		'JBPortal_Testimonials_Widget',
		'JBPortal_Job_Search_Widget',
		'JBPortal_Job_Alert_Widget',
	);
	foreach ( $widgets as $class ) {
		$manager->register( new $class() );
	}
}

/* ============================================================
   Base class: shortcode-backed widget
   ============================================================ */
if ( ! class_exists( 'JBPortal_Base_Widget' ) && class_exists( '\Elementor\Widget_Base' ) ) :

abstract class JBPortal_Base_Widget extends \Elementor\Widget_Base {
	public function get_categories() {
		return array( 'jbportal' );
	}
}

endif;

/* Register a custom Elementor category */
add_action( 'elementor/elements/categories_registered', function( $manager ) {
	$manager->add_category( 'jbportal', array( 'title' => 'jbportal', 'icon' => 'fa fa-briefcase' ) );
} );

/* ============================================================
   1. Jobs listing widget
   ============================================================ */
if ( ! class_exists( 'JBPortal_Jobs_Widget' ) && class_exists( '\Elementor\Widget_Base' ) ) :

class JBPortal_Jobs_Widget extends JBPortal_Base_Widget {
	public function get_name()  { return 'jbportal_jobs'; }
	public function get_title() { return esc_html__( 'Jobs Listing', 'jbportal' ); }
	public function get_icon()  { return 'eicon-posts-grid'; }

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array( 'label' => esc_html__( 'Settings', 'jbportal' ) ) );

		$this->add_control( 'limit', array(
			'label'   => esc_html__( 'Number of jobs', 'jbportal' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 6,
			'min'     => 1,
			'max'     => 50,
		) );

		$this->add_control( 'featured', array(
			'label'        => esc_html__( 'Featured only', 'jbportal' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'Yes', 'jbportal' ),
			'label_off'    => esc_html__( 'No', 'jbportal' ),
			'return_value' => '1',
			'default'      => '',
		) );

		$this->add_control( 'remote', array(
			'label'        => esc_html__( 'Remote only', 'jbportal' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'Yes', 'jbportal' ),
			'label_off'    => esc_html__( 'No', 'jbportal' ),
			'return_value' => '1',
			'default'      => '',
		) );

		$this->add_control( 'category', array(
			'label'       => esc_html__( 'Category slug', 'jbportal' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => 'engineering',
		) );

		$this->add_control( 'columns', array(
			'label'   => esc_html__( 'Columns', 'jbportal' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '3',
			'options' => array( '1' => '1', '2' => '2', '3' => '3' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$atts = array(
			'limit'    => (int) $s['limit'],
			'featured' => $s['featured'],
			'remote'   => $s['remote'],
			'category' => sanitize_text_field( $s['category'] ),
			'columns'  => (int) $s['columns'],
		);
		echo do_shortcode( '[jbportal_jobs ' . jbportal_atts_to_string( $atts ) . ']' );
	}
}

endif;

/* ============================================================
   2. Companies grid widget
   ============================================================ */
if ( ! class_exists( 'JBPortal_Companies_Widget' ) && class_exists( '\Elementor\Widget_Base' ) ) :

class JBPortal_Companies_Widget extends JBPortal_Base_Widget {
	public function get_name()  { return 'jbportal_companies'; }
	public function get_title() { return esc_html__( 'Companies Grid', 'jbportal' ); }
	public function get_icon()  { return 'eicon-archive-posts'; }

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array( 'label' => esc_html__( 'Settings', 'jbportal' ) ) );

		$this->add_control( 'limit', array(
			'label'   => esc_html__( 'Number of companies', 'jbportal' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 8,
			'min'     => 1,
			'max'     => 48,
		) );

		$this->add_control( 'industry', array(
			'label'       => esc_html__( 'Industry slug', 'jbportal' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => 'technology',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$atts = array(
			'limit'    => (int) $s['limit'],
			'industry' => sanitize_text_field( $s['industry'] ),
		);
		echo do_shortcode( '[jbportal_companies ' . jbportal_atts_to_string( $atts ) . ']' );
	}
}

endif;

/* ============================================================
   3. Categories widget
   ============================================================ */
if ( ! class_exists( 'JBPortal_Categories_Widget' ) && class_exists( '\Elementor\Widget_Base' ) ) :

class JBPortal_Categories_Widget extends JBPortal_Base_Widget {
	public function get_name()  { return 'jbportal_categories'; }
	public function get_title() { return esc_html__( 'Job Categories', 'jbportal' ); }
	public function get_icon()  { return 'eicon-tags'; }

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array( 'label' => esc_html__( 'Settings', 'jbportal' ) ) );

		$this->add_control( 'limit', array(
			'label'   => esc_html__( 'Number of categories', 'jbportal' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 12,
			'min'     => 1,
			'max'     => 48,
		) );

		$this->add_control( 'show_count', array(
			'label'        => esc_html__( 'Show job count', 'jbportal' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => '1',
			'default'      => '1',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$atts = array(
			'limit'      => (int) $s['limit'],
			'show_count' => $s['show_count'],
		);
		echo do_shortcode( '[jbportal_categories ' . jbportal_atts_to_string( $atts ) . ']' );
	}
}

endif;

/* ============================================================
   4. Stats counter widget
   ============================================================ */
if ( ! class_exists( 'JBPortal_Stats_Widget' ) && class_exists( '\Elementor\Widget_Base' ) ) :

class JBPortal_Stats_Widget extends JBPortal_Base_Widget {
	public function get_name()  { return 'jbportal_stats'; }
	public function get_title() { return esc_html__( 'Stats Counter', 'jbportal' ); }
	public function get_icon()  { return 'eicon-counter'; }

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array( 'label' => esc_html__( 'Display', 'jbportal' ) ) );
		$this->add_control( 'layout', array(
			'label'   => esc_html__( 'Layout', 'jbportal' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'inline',
			'options' => array( 'inline' => 'Inline', 'grid' => 'Grid' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo do_shortcode( '[jbportal_stats layout="' . esc_attr( $s['layout'] ) . '"]' );
	}
}

endif;

/* ============================================================
   5. Pricing table widget
   ============================================================ */
if ( ! class_exists( 'JBPortal_Pricing_Widget' ) && class_exists( '\Elementor\Widget_Base' ) ) :

class JBPortal_Pricing_Widget extends JBPortal_Base_Widget {
	public function get_name()  { return 'jbportal_pricing'; }
	public function get_title() { return esc_html__( 'Pricing Table', 'jbportal' ); }
	public function get_icon()  { return 'eicon-price-table'; }

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array( 'label' => esc_html__( 'Display', 'jbportal' ) ) );
		$this->add_control( 'highlight', array(
			'label'       => esc_html__( 'Highlighted plan ID', 'jbportal' ),
			'type'        => \Elementor\Controls_Manager::NUMBER,
			'description' => esc_html__( 'WooCommerce product ID to mark as "Popular"', 'jbportal' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$atts = array( 'highlight' => (int) $s['highlight'] );
		echo do_shortcode( '[jbportal_pricing ' . jbportal_atts_to_string( $atts ) . ']' );
	}
}

endif;

/* ============================================================
   6. Testimonials widget
   ============================================================ */
if ( ! class_exists( 'JBPortal_Testimonials_Widget' ) && class_exists( '\Elementor\Widget_Base' ) ) :

class JBPortal_Testimonials_Widget extends JBPortal_Base_Widget {
	public function get_name()  { return 'jbportal_testimonials'; }
	public function get_title() { return esc_html__( 'Testimonials', 'jbportal' ); }
	public function get_icon()  { return 'eicon-testimonial'; }

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array( 'label' => esc_html__( 'Settings', 'jbportal' ) ) );
		$this->add_control( 'limit', array(
			'label'   => esc_html__( 'Number', 'jbportal' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 3,
			'min'     => 1,
			'max'     => 12,
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo do_shortcode( '[jbportal_testimonials limit="' . (int) $s['limit'] . '"]' );
	}
}

endif;

/* ============================================================
   7. Job search bar widget
   ============================================================ */
if ( ! class_exists( 'JBPortal_Job_Search_Widget' ) && class_exists( '\Elementor\Widget_Base' ) ) :

class JBPortal_Job_Search_Widget extends JBPortal_Base_Widget {
	public function get_name()  { return 'jbportal_job_search'; }
	public function get_title() { return esc_html__( 'Job Search Bar', 'jbportal' ); }
	public function get_icon()  { return 'eicon-search'; }

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array( 'label' => esc_html__( 'Labels', 'jbportal' ) ) );
		$this->add_control( 'placeholder_keyword', array(
			'label'   => esc_html__( 'Keyword placeholder', 'jbportal' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'Job title or keyword', 'jbportal' ),
		) );
		$this->add_control( 'placeholder_location', array(
			'label'   => esc_html__( 'Location placeholder', 'jbportal' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'City, state or remote', 'jbportal' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		echo do_shortcode( '[jbportal_job_search]' );
	}
}

endif;

/* ============================================================
   8. Job alert form widget
   ============================================================ */
if ( ! class_exists( 'JBPortal_Job_Alert_Widget' ) && class_exists( '\Elementor\Widget_Base' ) ) :

class JBPortal_Job_Alert_Widget extends JBPortal_Base_Widget {
	public function get_name()  { return 'jbportal_job_alert'; }
	public function get_title() { return esc_html__( 'Job Alert Form', 'jbportal' ); }
	public function get_icon()  { return 'eicon-bell'; }

	protected function register_controls() {
		$this->start_controls_section( 'content_section', array( 'label' => esc_html__( 'Display', 'jbportal' ) ) );
		$this->add_control( 'heading', array(
			'label'   => esc_html__( 'Heading', 'jbportal' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'Get job alerts', 'jbportal' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		if ( $s['heading'] ) {
			echo '<h3 class="jb-widget-heading">' . esc_html( $s['heading'] ) . '</h3>';
		}
		echo do_shortcode( '[jbportal_job_alert]' );
	}
}

endif;

/* ============================================================
   Helper: convert array to shortcode attribute string
   ============================================================ */
if ( ! function_exists( 'jbportal_atts_to_string' ) ) {
	function jbportal_atts_to_string( array $atts ) {
		$parts = array();
		foreach ( $atts as $key => $val ) {
			if ( $val !== '' && $val !== null && $val !== 0 ) {
				$parts[] = esc_attr( $key ) . '="' . esc_attr( $val ) . '"';
			}
		}
		return implode( ' ', $parts );
	}
}
