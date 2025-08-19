<?php
/**
 * Admin class for E-Catalogue For Woocommerce.
 *
 * @package ECFW
 * @subpackage Admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples of hooking a function into the admin area of WordPress.
 *
 * @since    1.0.0
 */
class ECFW_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Plugin settings instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      ECFW_Settings    $settings    The plugin settings object.
	 */
	private $settings;

	/**
	 * PDF Generator instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      ECFW_PDF_Generator    $pdf_generator    The PDF Generator object.
	 */
	private $pdf_generator;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    ECFW_Settings    $settings       The plugin settings object.
	 * @param    ECFW_PDF_Generator $pdf_generator  The PDF generator object.
	 */
	public function __construct( ECFW_Settings $settings, ECFW_PDF_Generator $pdf_generator ) {
		$this->plugin_name   = 'e-catalogue-for-woocommerce';
		$this->version       = ECFW_VERSION;
		$this->settings      = $settings;
		$this->pdf_generator = $pdf_generator;

		$this->load_dependencies();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this class.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		// No additional dependencies needed here beyond those loaded in the main plugin file.
	}

	/**
	 * Register the stylesheets and scripts for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, ECFW_PLUGIN_URL . 'admin/assets/css/admin-styles.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, ECFW_PLUGIN_URL . 'admin/assets/js/admin-scripts.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->plugin_name,
			'ecfw_admin_ajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ecfw_bulk_pdf_nonce' ),
			)
		);
	}

	/**
	 * Register the admin menu page.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			esc_html__( 'E-Catalogue Settings', 'ecfw' ),
			esc_html__( 'E-Catalogue', 'ecfw' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_setup_page' ),
			'dashicons-media-document',
			60
		);

		add_submenu_page(
			$this->plugin_name,
			esc_html__( 'Bulk PDF Generator', 'ecfw' ),
			esc_html__( 'Bulk PDF', 'ecfw' ),
			'manage_options',
			$this->plugin_name . '-bulk',
			array( $this, 'display_bulk_pdf_page' )
		);
	}

	/**
	 * Render the plugin settings page.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_setup_page() {
		include_once ECFW_PLUGIN_DIR . 'admin/partials/ecfw-admin-display.php';
	}

	/**
	 * Render the bulk PDF generator page.
	 *
	 * @since    1.0.0
	 */
	public function display_bulk_pdf_page() {
		// Handle bulk PDF generation logic if form is submitted.
		if ( isset( $_POST['ecfw_bulk_generate_pdf_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['ecfw_bulk_generate_pdf_nonce'] ) ), 'ecfw_bulk_generate_pdf' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
			$product_ids_raw  = isset( $_POST['ecfw_bulk_product_ids'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ecfw_bulk_product_ids'] ) ) : '';
			$selected_category_ids = isset( $_POST['ecfw_bulk_category_ids'] ) && is_array( $_POST['ecfw_bulk_category_ids'] ) ? array_map( 'absint', wp_unslash( $_POST['ecfw_bulk_category_ids'] ) ) : array();

			$product_ids_array = array_filter( array_map( 'absint', explode( ',', $product_ids_raw ) ) );

			$products_to_include = array();

			// If specific product IDs are provided, prioritize them.
			if ( ! empty( $product_ids_array ) ) {
				$products_to_include = $product_ids_array;
			} elseif ( ! empty( $selected_category_ids ) ) {
				// Otherwise, fetch products from selected categories.
				$args = array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
					'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.BadKeys
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'term_id',
							'terms'    => $selected_category_ids,
							'operator' => 'IN',
						),
					),
					'fields'         => 'ids', // Only get product IDs.
				);
				$products_to_include = get_posts( $args );
			}

			if ( ! empty( $products_to_include ) ) {
				$filename = 'woocommerce-catalog-bulk-' . date_i18n( 'Y-m-d' ) . '.pdf'; // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				$this->pdf_generator->generate_pdf( $products_to_include, $filename, true ); // True for direct output.
				exit; // Terminate script after PDF generation.
			} else {
				add_settings_error( 'ecfw_bulk_pdf', 'ecfw_empty_selection', esc_html__( 'No products found for the selected criteria.', 'ecfw' ), 'error' );
			}
		}

		$current_settings = $this->settings->get_settings();
		$categories       = ECFW_Helper::get_all_product_categories();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Bulk PDF Catalog Generator', 'ecfw' ); ?></h1>
			<form method="post" action="">
				<?php settings_errors(); ?>
				<?php wp_nonce_field( 'ecfw_bulk_generate_pdf', 'ecfw_bulk_generate_pdf_nonce' ); ?>

				<h2><?php esc_html_e( 'Select Products for Bulk PDF', 'ecfw' ); ?></h2>

				<p>
					<?php esc_html_e( 'Enter product IDs separated by commas (e.g., 101,102,105) OR select categories below. If product IDs are entered, category selection will be ignored.', 'ecfw' ); ?>
				</p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="ecfw_bulk_product_ids"><?php esc_html_e( 'Specific Product IDs', 'ecfw' ); ?></label></th>
						<td>
							<textarea id="ecfw_bulk_product_ids" name="ecfw_bulk_product_ids" rows="5" cols="50" class="regular-text"></textarea>
							<p class="description"><?php esc_html_e( 'Enter comma-separated WooCommerce product IDs.', 'ecfw' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="ecfw_bulk_category_ids"><?php esc_html_e( 'Product Categories', 'ecfw' ); ?></label></th>
						<td>
							<select id="ecfw_bulk_category_ids" name="ecfw_bulk_category_ids[]" multiple="multiple" style="min-width: 300px; height: 150px;">
								<?php
								if ( ! empty( $categories ) ) {
									foreach ( $categories as $category ) {
										echo '<option value="' . esc_attr( $category->term_id ) . '">' . esc_html( $category->name ) . '</option>';
									}
								} else {
									echo '<option value="">' . esc_html__( 'No categories found', 'ecfw' ) . '</option>';
								}
								?>
							</select>
							<p class="description"><?php esc_html_e( 'Select one or more categories to include products from. Hold CTRL/CMD to select multiple.', 'ecfw' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button( esc_html__( 'Generate Bulk PDF', 'ecfw' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Define the admin hooks that register and enqueue scripts and styles.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		add_action( 'admin_init', array( $this->settings, 'register_settings' ) ); // Register plugin settings.
	}

	/**
	 * Run the admin class.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		// All hooks are added in the constructor's define_admin_hooks() method.
	}
}