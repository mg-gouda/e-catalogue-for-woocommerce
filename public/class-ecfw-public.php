<?php
/**
 * Public class for E-Catalogue For Woocommerce.
 *
 * @package ECFW
 * @subpackage Public
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and examples of hooking a function into the public-facing side of WordPress.
 *
 * @since    1.0.0
 */
class ECFW_Public {

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
		$this->define_public_hooks();
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
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, ECFW_PLUGIN_URL . 'public/assets/css/public-styles.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, ECFW_PLUGIN_URL . 'public/assets/js/public-scripts.js', array( 'jquery' ), $this->version, true );
		wp_localize_script(
			$this->plugin_name,
			'ecfw_public_ajax',
			array(
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'download_nonce'      => wp_create_nonce( 'ecfw_download_pdf_nonce' ),
				'share_nonce'         => wp_create_nonce( 'ecfw_share_pdf_nonce' ),
				'modal_title'         => esc_html__( 'Share PDF Catalog', 'ecfw' ),
				'recipient_label'     => esc_html__( 'Recipient Email(s)', 'ecfw' ),
				'subject_label'       => esc_html__( 'Subject', 'ecfw' ),
				'message_label'       => esc_html__( 'Message', 'ecfw' ),
				'send_button_text'    => esc_html__( 'Send Email', 'ecfw' ),
				'close_button_text'   => esc_html__( 'Close', 'ecfw' ),
				'sending_text'        => esc_html__( 'Sending...', 'ecfw' ),
				'success_message'     => esc_html__( 'Email sent successfully!', 'ecfw' ),
				'error_message'       => esc_html__( 'Failed to send email. Please try again.', 'ecfw' ),
				'invalid_email_error' => esc_html__( 'Please enter a valid email address.', 'ecfw' ),
			)
		);
	}

	/**
	 * Add the PDF download button to single product pages based on settings.
	 *
	 * @since    1.0.0
	 */
	public function add_download_button_to_product_page() {
		$settings = $this->settings->get_settings();

		// Check if button is enabled for product pages.
		if ( ! isset( $settings['button_placement']['single_product'] ) || 'off' === $settings['button_placement']['single_product'] ) {
			return;
		}

		// Check conditional logic.
		$apply_conditional_logic = isset( $settings['conditional_logic_enabled'] ) && 'on' === $settings['conditional_logic_enabled'];
		$allowed_categories      = isset( $settings['conditional_categories'] ) ? (array) $settings['conditional_categories'] : array();

		if ( $apply_conditional_logic && ! empty( $allowed_categories ) ) {
			global $product;
			if ( ! $product ) {
				return;
			}
			$product_id = $product->get_id();
			$product_categories = wc_get_product_term_counts( $product_id, 'product_cat' );
			$product_category_ids = array_keys( $product_categories );

			$intersection = array_intersect( $allowed_categories, $product_category_ids );
			if ( empty( $intersection ) ) {
				return; // Product does not belong to any allowed categories.
			}
		}

		$product_id = get_the_ID();
		if ( ! $product_id ) {
			return;
		}

		$button_text = ! empty( $settings['button_text'] ) ? esc_html( $settings['button_text'] ) : esc_html__( 'Download Catalog PDF', 'ecfw' );
		$button_class = apply_filters( 'ecfw_download_button_class', 'ecfw-download-pdf-button button alt' );

		echo '<div class="ecfw-download-button-wrapper">';
		echo '<a href="#" class="' . esc_attr( $button_class ) . '" data-product-id="' . esc_attr( $product_id ) . '">' . $button_text . '</a>';

		// Add share button if enabled.
		if ( isset( $settings['share_pdf_button'] ) && 'on' === $settings['share_pdf_button'] ) {
			$share_button_text = ! empty( $settings['share_button_text'] ) ? esc_html( $settings['share_button_text'] ) : esc_html__( 'Share PDF', 'ecfw' );
			$share_button_class = apply_filters( 'ecfw_share_button_class', 'ecfw-share-pdf-button button' );
			echo '<a href="#" class="' . esc_attr( $share_button_class ) . '" data-product-id="' . esc_attr( $product_id ) . '" style="margin-left: 10px;">' . $share_button_text . '</a>';
		}
		echo '</div>';
	}

	/**
	 * Shortcode to display the PDF download button.
	 * Example: `[ecfw_pdf_download product_id="123" button_text="Get PDF"]`
	 *
	 * @since    1.0.0
	 * @param    array $atts Shortcode attributes.
	 * @return   string HTML for the button.
	 */
	public function ecfw_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'product_id'  => 0,
				'button_text' => esc_html__( 'Download Catalog PDF', 'ecfw' ),
				'show_share'  => 'false', // 'true' or 'false'
			),
			$atts,
			'ecfw_pdf_download'
		);

		$product_id = absint( $atts['product_id'] );
		$settings   = $this->settings->get_settings();

		// If no product_id is specified in shortcode, and it's a product page, try to get current product ID.
		if ( 0 === $product_id && is_product() ) {
			global $product;
			if ( $product ) {
				$product_id = $product->get_id();
			}
		}

		if ( 0 === $product_id ) {
			return '<p style="color:red;">' . esc_html__( 'E-Catalogue shortcode requires a valid product_id attribute or must be used on a single product page.', 'ecfw' ) . '</p>';
		}

		// Check conditional logic for shortcode if enabled globally.
		$apply_conditional_logic = isset( $settings['conditional_logic_enabled'] ) && 'on' === $settings['conditional_logic_enabled'];
		$allowed_categories      = isset( $settings['conditional_categories'] ) ? (array) $settings['conditional_categories'] : array();

		if ( $apply_conditional_logic && ! empty( $allowed_categories ) ) {
			$product_categories = wc_get_product_term_counts( $product_id, 'product_cat' );
			$product_category_ids = array_keys( $product_categories );

			$intersection = array_intersect( $allowed_categories, $product_category_ids );
			if ( empty( $intersection ) ) {
				return ''; // Product does not belong to any allowed categories, so hide button.
			}
		}

		ob_start();
		$button_class = apply_filters( 'ecfw_download_button_class', 'ecfw-download-pdf-button button alt' );
		?>
		<div class="ecfw-download-button-wrapper">
			<a href="#" class="<?php echo esc_attr( $button_class ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>">
				<?php echo esc_html( $atts['button_text'] ); ?>
			</a>
			<?php
			if ( 'true' === $atts['show_share'] && isset( $settings['share_pdf_button'] ) && 'on' === $settings['share_pdf_button'] ) {
				$share_button_text = ! empty( $settings['share_button_text'] ) ? esc_html( $settings['share_button_text'] ) : esc_html__( 'Share PDF', 'ecfw' );
				$share_button_class = apply_filters( 'ecfw_share_button_class', 'ecfw-share-pdf-button button' );
				?>
				<a href="#" class="<?php echo esc_attr( $share_button_class ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>" style="margin-left: 10px;">
					<?php echo esc_html( $share_button_text ); ?>
				</a>
				<?php
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * AJAX handler for PDF download.
	 *
	 * @since    1.0.0
	 */
	public function ajax_download_pdf() {
		check_ajax_referer( 'ecfw_download_pdf_nonce', 'nonce' );

		$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( 0 === $product_id ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid product ID.', 'ecfw' ) ) );
		}

		$filename = 'woocommerce-catalog-product-' . $product_id . '.pdf';
		$this->pdf_generator->generate_pdf( array( $product_id ), $filename, true ); // True for direct output.
		exit; // Terminate script after PDF generation.
	}

	/**
	 * AJAX handler for PDF sharing via email.
	 *
	 * @since    1.0.0
	 */
	public function ajax_share_pdf_via_email() {
		check_ajax_referer( 'ecfw_share_pdf_nonce', 'nonce' );

		$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$recipients = isset( $_POST['recipients'] ) ? sanitize_text_field( wp_unslash( $_POST['recipients'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$subject    = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$message    = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( 0 === $product_id || empty( $recipients ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid product ID or recipient email.', 'ecfw' ) ) );
		}

		$recipient_emails = array_map( 'sanitize_email', explode( ',', $recipients ) );
		$recipient_emails = array_filter( $recipient_emails, 'is_email' );

		if ( empty( $recipient_emails ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'No valid recipient email addresses provided.', 'ecfw' ) ) );
		}

		// Generate PDF content (not output directly).
		$filename   = 'woocommerce-catalog-product-' . $product_id . '.pdf';
		$pdf_content = $this->pdf_generator->generate_pdf( array( $product_id ), $filename, false ); // False for string output.

		if ( ! $pdf_content ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Failed to generate PDF.', 'ecfw' ) ) );
		}

		// Save PDF temporarily to attach.
		$upload_dir   = wp_upload_dir();
		$temp_dir     = trailingslashit( $upload_dir['basedir'] ) . 'ecfw-temp-pdfs/';
		if ( ! file_exists( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );
		}

		$temp_file_path = $temp_dir . $filename;
		file_put_contents( $temp_file_path, $pdf_content ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents

		// Send email with attachment.
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		// Set default subject if empty.
		if ( empty( $subject ) ) {
			$product = wc_get_product( $product_id );
			$subject = sprintf( esc_html__( 'Product Catalog for %s', 'ecfw' ), $product ? $product->get_name() : esc_html__( 'Selected Product(s)', 'ecfw' ) );
		}

		// Set default message if empty.
		if ( empty( $message ) ) {
			$message = esc_html__( 'Please find attached the product catalog.', 'ecfw' );
		}

		$mail_sent = wp_mail( $recipient_emails, $subject, wpautop( $message ), $headers, array( $temp_file_path ) );

		// Delete temporary file.
		if ( file_exists( $temp_file_path ) ) {
			unlink( $temp_file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_unlink
		}

		if ( $mail_sent ) {
			wp_send_json_success( array( 'message' => esc_html__( 'Email sent successfully!', 'ecfw' ) ) );
		} else {
			wp_send_json_error( array( 'message' => esc_html__( 'Failed to send email. Please check your mail settings.', 'ecfw' ) ) );
		}
	}

	/**
	 * Display the PDF sharing email modal.
	 *
	 * @since    1.0.0
	 */
	public function display_email_share_modal() {
		$settings = $this->settings->get_settings();
		if ( isset( $settings['share_pdf_button'] ) && 'on' === $settings['share_pdf_button'] ) {
			include_once ECFW_PLUGIN_DIR . 'templates/share-email-modal.php';
		}
	}

	/**
	 * Define the public hooks that register and enqueue scripts and styles.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add button to single product page.
		add_action( 'woocommerce_single_product_summary', array( $this, 'add_download_button_to_product_page' ), 35 );

		// Register shortcode.
		add_shortcode( 'ecfw_pdf_download', array( $this, 'ecfw_shortcode' ) );

		// AJAX hooks for PDF download and email sharing.
		add_action( 'wp_ajax_ecfw_download_pdf', array( $this, 'ajax_download_pdf' ) );
		add_action( 'wp_ajax_nopriv_ecfw_download_pdf', array( $this, 'ajax_download_pdf' ) ); // If you want logged out users to download.

		add_action( 'wp_ajax_ecfw_share_pdf_via_email', array( $this, 'ajax_share_pdf_via_email' ) );
		add_action( 'wp_ajax_nopriv_ecfw_share_pdf_via_email', array( $this, 'ajax_share_pdf_via_email' ) ); // If you want logged out users to share.

		add_action( 'wp_footer', array( $this, 'display_email_share_modal' ) );
	}

	/**
	 * Run the public class.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		// All hooks are added in the constructor's define_public_hooks() method.
	}
}