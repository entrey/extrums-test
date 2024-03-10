<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Extrums_Test
 * @subpackage Extrums_Test/admin
 * @author     Roman Peniaz <roman.peniaz@gmail.com>
 */
class Extrums_Test_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since  1.0.0
	 * @param  string    $plugin_name       The name of this plugin.
	 * @param  string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/extrums-test-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/extrums-test-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function add_admin_menu_item() {
		add_menu_page(
			__( 'Extrums Form', 'extrums-test' ),
			__( 'Extrums Form', 'extrums-test' ),
			'manage_options',
			'extrums-form',
			[ $this, 'render_form_page' ],
			'dashicons-media-spreadsheet',
			3
		);
	}

	public function render_form_page() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/extrums-test-admin-display.php';
	}

	public function query_posts() {
		global $wpdb;

		$keyword = $_POST['keyword'] ?? '';

		if ( ! $keyword ) {
			return;
		}

		$seo_table_name = $wpdb->prefix . 'yoast_indexable';
		$seo_table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$seo_table_name'" ) !== null;

		$select_clause = 'p.ID, p.post_title, p.post_content';
		$join_clause = '';
		if ( $seo_table_exists ) {
			$select_clause .= ', y.title, y.description';
			$join_clause = "LEFT JOIN $seo_table_name AS y ON p.ID = y.object_id";
		}

		$query = "SELECT $select_clause
			FROM {$wpdb->prefix}posts AS p
			$join_clause
			WHERE p.post_type = %s
			AND (
				p.post_title LIKE %s
				OR p.post_content LIKE %s";

		if ( $seo_table_exists ) {
			$query .= " OR y.title LIKE %s OR y.description LIKE %s";
		}

		$query .= " )";

		$like_keyword = '%' . $wpdb->esc_like( $keyword ) . '%';
		$args = [
			'post',
			$like_keyword,
			$like_keyword
		];
		if ( $seo_table_exists ) {
			$args[] = $like_keyword;
			$args[] = $like_keyword;
		}

		$query = $wpdb->prepare( $query, $args );
		$posts = $wpdb->get_results( $query );
		return wp_send_json( $posts );
	}

}
