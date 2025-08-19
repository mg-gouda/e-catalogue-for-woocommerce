<?php
/**
 * HTML template for the PDF catalog content.
 *
 * This file constructs the main HTML content for the PDF, iterating through products.
 *
 * @package ECFW
 * @subpackage Templates
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure $products_data and $settings are available from ECFW_PDF_Generator.
if ( ! isset( $products_data ) || ! isset( $settings ) ) {
	echo '<h1>Error: Product data or settings not available.</h1>';
	return;
}

// Extract styling settings.
$font_family     = ! empty( $settings['font_family'] ) ? esc_attr( $settings['font_family'] ) : 'DejaVu Sans, sans-serif';
$base_font_size  = ! empty( $settings['base_font_size'] ) ? absint( $settings['base_font_size'] ) : 12;
$primary_color   = ! empty( $settings['primary_color'] ) ? esc_attr( $settings['primary_color'] ) : '#333333';
$secondary_color = ! empty( $settings['secondary_color'] ) ? esc_attr( $settings['secondary_color'] ) : '#666666';

// Extract content selection.
$content_selection = isset( $settings['content_selection'] ) ? (array) $settings['content_selection'] : array();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( $settings['header_text'] ); ?></title>
    <style>
        @page {
            margin: 100px 50px; /* Top, Right, Bottom, Left */
        }
        body {
            font-family: <?php echo esc_html( $font_family ); ?>;
            font-size: <?php echo esc_html( $base_font_size ); ?>px;
            color: <?php echo esc_html( $secondary_color ); ?>;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* Header & Footer */
        header {
            position: fixed;
            top: -70px; /* Adjust based on margin-top */
            left: 0px;
            right: 0px;
            height: 50px;
            text-align: center;
            line-height: 35px;
            border-bottom: 1px solid #eee;
            font-size: <?php echo esc_html( $base_font_size * 1.2 ); ?>px;
            color: <?php echo esc_html( $primary_color ); ?>;
        }
        footer {
            position: fixed;
            bottom: -70px; /* Adjust based on margin-bottom */
            left: 0px;
            right: 0px;
            height: 50px;
            text-align: center;
            line-height: 35px;
            border-top: 1px solid #eee;
            font-size: <?php echo esc_html( $base_font_size * 0.9 ); ?>px;
            color: <?php echo esc_html( $secondary_color ); ?>;
        }

        /* Product Listing Styles */
        .product-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .product-item {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
            display: flex;
            align-items: flex-start;
            page-break-inside: avoid; /* Keep product items on one page */
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .product-image {
            flex: 0 0 150px; /* Fixed width for image column */
            margin-right: 20px;
            text-align: center;
        }
        .product-image img {
            max-width: 150px; /* Max width for images */
            height: auto;
            border: 1px solid #eee;
            padding: 5px;
            background: #fff;
        }
        .product-details {
            flex-grow: 1;
        }
        .product-title {
            color: <?php echo esc_html( $primary_color ); ?>;
            font-size: <?php echo esc_html( $base_font_size * 1.5 ); ?>px;
            margin-top: 0;
            margin-bottom: 5px;
        }
        .product-price {
            font-size: <?php echo esc_html( $base_font_size * 1.3 ); ?>px;
            font-weight: bold;
            color: #007bff; /* A distinct color for price */
            margin-bottom: 10px;
            display: block;
        }
        .product-sku,
        .product-categories {
            font-size: <?php echo esc_html( $base_font_size * 0.9 ); ?>px;
            color: <?php echo esc_html( $secondary_color ); ?>;
            margin-bottom: 5px;
        }
        .product-description {
            font-size: <?php echo esc_html( $base_font_size ); ?>px;
            margin-top: 10px;
        }
        .product-short-description {
            font-style: italic;
            margin-bottom: 10px;
        }

        /* Responsive-ish design for PDF (flexbox is supported by Dompdf) */
        @media print {
            .product-item {
                flex-direction: row; /* Ensure row layout for print */
            }
        }
    </style>
</head>
<body>
    <header>
        <?php echo esc_html( $settings['header_text'] ); ?>
    </header>
    <footer>
        <?php
        // Replace placeholders for page numbers
        $footer_text = str_replace( '{PAGE_NUM}', '<span class="page-number"></span>', esc_html( $settings['footer_text'] ) );
        $footer_text = str_replace( '{TOTAL_PAGES}', '<span class="total-pages"></span>', $footer_text );
        echo $footer_text;
        ?>
    </footer>

    <main>
        <ul class="product-list">
            <?php foreach ( $products_data as $product ) : ?>
                <li class="product-item">
                    <?php if ( ! empty( $product['image_url'] ) ) : ?>
                        <div class="product-image">
                            <img src="<?php echo esc_url( $product['image_url'] ); ?>" alt="<?php echo esc_attr( $product['name'] ); ?>">
                        </div>
                    <?php endif; ?>
                    <div class="product-details">
                        <h2 class="product-title"><?php echo esc_html( $product['name'] ); ?></h2>
                        <?php if ( ! empty( $product['price'] ) ) : ?>
                            <span class="product-price"><?php echo wp_kses_post( $product['price'] ); ?></span>
                        <?php endif; ?>
                        <?php if ( in_array( 'sku', $content_selection, true ) && ! empty( $product['sku'] ) ) : ?>
                            <div class="product-sku"><strong><?php esc_html_e( 'SKU:', 'ecfw' ); ?></strong> <?php echo esc_html( $product['sku'] ); ?></div>
                        <?php endif; ?>
                        <?php if ( in_array( 'categories', $content_selection, true ) && ! empty( $product['categories'] ) ) : ?>
                            <div class="product-categories"><strong><?php esc_html_e( 'Categories:', 'ecfw' ); ?></strong> <?php echo wp_kses_post( $product['categories'] ); ?></div>
                        <?php endif; ?>
                        <?php if ( in_array( 'short_description', $content_selection, true ) && ! empty( $product['short_description'] ) ) : ?>
                            <div class="product-short-description"><?php echo wp_kses_post( $product['short_description'] ); ?></div>
                        <?php endif; ?>
                        <?php if ( in_array( 'long_description', $content_selection, true ) && ! empty( $product['description'] ) ) : ?>
                            <div class="product-description"><?php echo wp_kses_post( $product['description'] ); ?></div>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>
</html>