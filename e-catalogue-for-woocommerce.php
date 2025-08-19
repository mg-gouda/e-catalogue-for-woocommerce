<?php
/**
 * Plugin Name: E-Catalogue For Woocommerce
 * Plugin URI: https://inspirebs.onmicrosoft.com/e-catalogue-for-woocommerce/
 * Description: Create a customizable e-catalogue for WooCommerce Products.
 * Version: 1.0.0
 * Author: Mohamed Gouda
 * Author URI: https://inspirebs.onmicrosoft.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ecfw
 * Domain Path: /languages
 *
 * @package ECFW
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'ECFW_VERSION', '1.0.0' );
define( 'ECFW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ECFW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check if WooCommerce is active.
 * We need this to ensure our plugin doesn't break if WooCommerce isn't installed.
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	/**
	 * Deactivates the plugin if WooCommerce is not active.
	 */
	function ecfw_deactivate_if_woocommerce_not_active() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'ecfw_woocommerce_inactive_notice' );
		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}
	register_activation_hook( __FILE__, 'ecfw_deactivate_if_woocommerce_not_active' );

	/**
	 * Displays an admin notice if WooCommerce is not active.
	 */
	function ecfw_woocommerce_inactive_notice() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					/* translators: %s: Plugin name. */
					esc_html__( '%s requires WooCommerce to be installed and active.', 'ecfw' ),
					'<strong>E-Catalogue For Woocommerce</strong>'
				);
				?>
			</p>
		</div>
		<?php
	}
	return; // Stop execution if WooCommerce is not active.
}

/**
 * Autoload Dompdf library.
 * The user must place the Dompdf library files inside includes/lib/dompdf/
 */
if ( file_exists( ECFW_PLUGIN_DIR . 'includes/lib/dompdf/autoload.inc.php' ) ) {
	require_once ECFW_PLUGIN_DIR . 'includes/lib/dompdf/autoload.inc.php';
} else {
	/**
	 * Admin notice if Dompdf is not found.
	 */
	function ecfw_dompdf_missing_notice() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					/* translators: 1: Plugin name, 2: Directory path. */
					esc_html__( '%1$s: The Dompdf library is missing. Please download Dompdf (e.g., from its GitHub releases or Packagist) and extract its contents into %2$s so that `autoload.inc.php` is present.', 'ecfw' ),
					'<strong>E-Catalogue For Woocommerce</strong>',
					'<code>' . esc_html( 'e-catalogue-for-woocommerce/includes/lib/dompdf/' ) . '</code>'
				);
				?>
			</p>
		</div>
		<?php
	}
	add_action( 'admin_notices', 'ecfw_dompdf_missing_notice' );
	return; // Stop execution if Dompdf is missing.
}


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing hooks.
 */
require_once ECFW_PLUGIN_DIR . 'includes/class-ecfw-settings.php';
require_once ECFW_PLUGIN_DIR . 'includes/class-ecfw-helper.php';
require_once ECFW_PLUGIN_DIR . 'includes/class-ecfw-pdf-generator.php';
require_once ECFW_PLUGIN_DIR . 'admin/class-ecfw-admin.php';
require_once ECFW_PLUGIN_DIR . 'public/class-ecfw-public.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then there isn't a need to explicitly call any action or filter hook.
 *
 * @since    1.0.0
 */
function run_ecfw() {
	$settings       = new ECFW_Settings();
	$pdf_generator  = new ECFW_PDF_Generator( $settings );
	$admin          = new ECFW_Admin( $settings, $pdf_generator );
	$public         = new ECFW_Public( $settings, $pdf_generator );

	$admin->run();
	$public->run();
}
run_ecfw();