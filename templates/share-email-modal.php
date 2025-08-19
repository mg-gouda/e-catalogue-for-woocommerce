<?php
/**
 * HTML template for the PDF sharing email modal.
 *
 * @package ECFW
 * @subpackage Templates
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="ecfw-share-email-modal" class="ecfw-modal-overlay">
    <div class="ecfw-modal-content">
        <h3><?php esc_html_e( 'Share PDF Catalog', 'ecfw' ); ?></h3>
        <form id="ecfw-share-email-form">
            <input type="hidden" id="ecfw-share-product-id" name="product_id" value="">

            <label for="ecfw-recipient-emails"><?php esc_html_e( 'Recipient Email(s):', 'ecfw' ); ?></label>
            <input type="text" id="ecfw-recipient-emails" name="recipients" placeholder="e.g., email@example.com, another@example.com" required>
            <p class="description"><?php esc_html_e( 'Separate multiple emails with commas.', 'ecfw' ); ?></p>

            <label for="ecfw-email-subject"><?php esc_html_e( 'Subject:', 'ecfw' ); ?></label>
            <input type="text" id="ecfw-email-subject" name="subject" placeholder="<?php esc_attr_e( 'Product Catalog from Your Site', 'ecfw' ); ?>">

            <label for="ecfw-email-message"><?php esc_html_e( 'Message:', 'ecfw' ); ?></label>
            <textarea id="ecfw-email-message" name="message" rows="5" placeholder="<?php esc_attr_e( 'Please find attached our product catalog.', 'ecfw' ); ?>"></textarea>

            <div class="ecfw-modal-message"></div>

            <div class="ecfw-modal-actions">
                <button type="submit" class="ecfw-send-email-button"><?php esc_html_e( 'Send Email', 'ecfw' ); ?></button>
                <button type="button" class="ecfw-close-modal-button"><?php esc_html_e( 'Close', 'ecfw' ); ?></button>
            </div>
        </form>
    </div>
</div>