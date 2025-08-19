<?php
/**
 * HTML template for the PDF catalog cover page.
 *
 * @package ECFW
 * @subpackage Templates
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure $settings are available from ECFW_PDF_Generator.
if ( ! isset( $settings ) ) {
	echo '<h1>Error: Settings not available.</h1>';
	return;
}

// Extract styling settings.
$font_family     = ! empty( $settings['font_family'] ) ? esc_attr( $settings['font_family'] ) : 'DejaVu Sans, sans-serif';
$primary_color   = ! empty( $settings['primary_color'] ) ? esc_attr( $settings['primary_color'] ) : '#333333';
$secondary_color = ! empty( $settings['secondary_color'] ) ? esc_attr( $settings['secondary_color'] ) : '#666666';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( $settings['cover_title'] ); ?></title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 0;
        }
        body {
            font-family: <?php echo esc_html( $font_family ); ?>;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 50px; /* Padding inside the page */
            box-sizing: border-box;
            color: <?php echo esc_html( $secondary_color ); ?>;
            page-break-after: always; /* Ensure a page break after the cover */
        }
        .cover-container {
            width: 100%;
            max-width: 800px;
            margin: auto;
        }
        .cover-image {
            margin-bottom: 40px;
            text-align: center;
        }
        .cover-image img {
            max-width: 100%;
            max-height: 500px; /* Limit image height */
            height: auto;
            display: block;
            margin: 0 auto;
            border: 1px solid #eee;
            padding: 10px;
        }
        .cover-title {
            color: <?php echo esc_html( $primary_color ); ?>;
            font-size: 3em; /* Larger font size for title */
            margin-top: 0;
            margin-bottom: 20px;
        }
        .cover-date {
            font-size: 1.2em;
            color: <?php echo esc_html( $secondary_color ); ?>;
        }
    </style>
</head>
<body>
    <div class="cover-container">
        <?php if ( ! empty( $settings['cover_image_url'] ) ) : ?>
            <div class="cover-image">
                <img src="<?php echo esc_url( ECFW_Helper::get_image_url_for_dompdf( $settings['cover_image_url'] ) ); ?>" alt="<?php echo esc_attr( $settings['cover_title'] ); ?>">
            </div>
        <?php endif; ?>
        <h1 class="cover-title"><?php echo esc_html( $settings['cover_title'] ); ?></h1>
        <p class="cover-date"><?php echo date_i18n( get_option( 'date_format' ) ); ?></p>
    </div>
</body>
</html>