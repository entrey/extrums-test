<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package Extrums_Test/includes
 * @author  Roman Peniaz <roman.peniaz@gmail.com>
 * @since   1.0.0
 */
class Extrums_Test {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since 1.0.0
	 * @var   Extrums_Test_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.0.0
	 * @var   string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since 1.0.0
	 * @var   string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( defined( 'EXTRUMS_TEST_VERSION' ) ) {
			$this->version = EXTRUMS_TEST_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'extrums-test';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {

		/** Actions and filters. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-extrums-test-loader.php';

		/** Internationalization functionality. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-extrums-test-i18n.php';

		/** Actions of admin area. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-extrums-test-admin.php';

		/** Actions of public-facing side. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-extrums-test-public.php';

		$this->loader = new Extrums_Test_Loader();

	}

	private function set_locale() {

		$plugin_i18n = new Extrums_Test_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	private function define_admin_hooks() {
		$plugin_admin = new Extrums_Test_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu_item' );

		if ( wp_doing_ajax() ) {
			$this->loader->add_action( 'wp_ajax_extrums_query_posts', $plugin_admin, 'query_posts' );
		}
	}

	private function define_public_hooks() {
		$plugin_public = new Extrums_Test_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

}
