<?php 

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @since      1.0.0
 *
 * @package    Genesis_Home_Page_Builder
 * @subpackage Genesis_Home_Page_Builder/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Genesis_Home_Page_Builder
 * @subpackage Genesis_Home_Page_Builder/includes
 */
 
class Genesis_Home_Page_Builder {

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if( is_admin() ) {
			add_action( 'admin_init', array( $this, 'save_options' ) );
			add_action( 'after_setup_theme', array( $this, 'add_page_builder_support' ) );
			add_action( 'load-appearance_page_so_panels_home_page', array( $this, 'add_meta_boxes' ) );
			add_action( 'admin_footer-appearance_page_so_panels_home_page', array( $this, 'admin_footer' ) );
		}
		else {
			add_filter( 'genesis_pre_get_option_site_layout', array( $this, 'force_layout' ), 50 );
			add_action( 'genesis_before', array( $this, 'setup_loop' ) );
			add_action( 'wp_head', array( $this, 'style' ), 100 );
		}
	}
	
	/**
	 * Perform activation checks
	 *
	 * @since    1.0.0
	 */
	static public function activation_hook() {
		// Check for Genesis presence
		if ( 'genesis' != basename( TEMPLATEPATH ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) ); 
			exit( sprintf( __( 'Sorry, to activate the Genesis Home Page Builder plugin you should have installed a <a target="_blank" href="%s">Genesis</a> theme', 'genesis-home-page-builder' ), 'http://www.studiopress.com/themes/genesis' ) );
		}
		// Check for Page Builder presence
		if ( ! defined( 'SITEORIGIN_PANELS_VERSION' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) ); 
			exit( sprintf( __( 'Sorry, to activate the Genesis Home Page Builder plugin you should have installed the <a target="_blank" href="%s">Page Builder by SiteOrigin</a> plugin', 'genesis-home-page-builder' ), 'http://wordpress.org/plugins/siteorigin-panels/' ) );
		}
	}
	
	/**
	 * Add Page Builder support to theme
	 *
	 * @since    1.0.0
	 */
	public function add_page_builder_support() {
		add_theme_support( 'siteorigin-panels', array(
			'home-page' => true,
			'margin-bottom' => 35,
			'home-page-default' => 'default-home',
			'home-demo-template' => 'home-panels.php',
			'responsive' => true,
		) );
	}
	
	/**
	 * Add meta box for options
	 *
	 * @since    1.0.0
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'genesis-home-page-builder-settings', 
			__( 'Genesis styles adjustments', 'genesis-home-page-builder' ),
			array( $this, 'render_meta_box' ),
			'appearance_page_so_panels_home_page',
			'advanced', 
			'high'
		);
	}
	
	/**
	 * Display meta box in footer
	 *
	 * @since    1.0.0
	 */
	public function admin_footer() {
		include plugin_dir_path( dirname( __FILE__ ) ) . 'partials/admin-footer.php';
	}

	/**
	 * Render meta box options
	 *
	 * @since    1.0.0
	 * @param    WP_Post|null    $post The object for the current post/page.
	 */
	public function render_meta_box( $post = null ) {
		$settings = get_option( 'genesis-home-page-builder-settings', 0 );
		include plugin_dir_path( dirname( __FILE__ ) ) . 'partials/admin-settings.php';
	}
	
	/**
	 * Save plugin options
	 *
	 * @since    1.0.0
	 */
	public function save_options() {
		if( ! isset( $_POST['_sopanels_home_nonce'] ) || ! wp_verify_nonce( $_POST['_sopanels_home_nonce'], 'save' ) ) return;
		if ( isset( $_POST['genesis-home-page-builder-settings'] ) ) {
			$new_settings = array_map( 'absint', $_POST['genesis-home-page-builder-settings'] );
			update_option( 'genesis-home-page-builder-settings', $new_settings );
		}
	}
	
	/**
	 * Include frontend styles
	 *
	 * @since    1.0.0
	 */
	public function style() {
		if ( is_front_page() ) {
			$settings = get_option( 'genesis-home-page-builder-settings', 0 );
			if( ! empty( $settings['reset-content-padding'] ) || ! empty( $settings['reset-overflow-hidden'] ) ) {
				include plugin_dir_path( dirname( __FILE__ ) ) . 'partials/public-style.php';
			}
		}
	}

	/**
	 * Force fullwidth layout
	 *
	 * @since    1.0.0
	 * @param    string    $layout
	 * @return   string
	 */
	public function force_layout( $layout ) {
		if ( is_front_page() ) {
			$layout = 'full-width-content';
		}
		return $layout;
	}
	
	/**
	 * Setup custom loog in home page
	 *
	 * @since    1.0.0
	 */
	public function setup_loop() {
		if ( is_front_page() ) {
			remove_action( 'genesis_loop', 'genesis_do_loop' );
			add_action( 'genesis_loop', array( $this, 'loop' ) );
		}
	}

	/**
	 * Render home page
	 *
	 * @since    1.0.0
	 */
	public function loop() {
		if( function_exists( 'siteorigin_panels_render' ) ) {
			echo siteorigin_panels_render( 'home' ); 
		} else {
			genesis_do_loop();
		}
	}
	
}
