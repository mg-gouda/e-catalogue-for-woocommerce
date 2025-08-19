<?php
/**
 * Provide a admin area view for the plugin.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @package ECFW
 * @subpackage Admin/Partials
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_settings = get_option( 'ecfw_settings', array() ); // Retrieve current settings.
$categories       = ECFW_Helper::get_all_product_categories();
?>

<div class="wrap">
	<h1><?php esc_html_e( 'E-Catalogue For WooCommerce Settings', 'ecfw' ); ?></h1>

	<form method="post" action="options.php">
		<?php
		settings_errors();
		settings_fields( 'ecfw_settings_group' );
		do_settings_sections( 'e-catalogue-for-woocommerce' );
		submit_button();
		?>
	</form>
</div>