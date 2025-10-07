<?php
/**
 * WP-CLI command for Price History.
 *
 * @package Nvm\Price_History
 */

namespace Nvm\Price_History;

use Nvm\Price_History\Woo_History_Price as Woo_Price;

/**
 * Check that the file is not accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

/**
 * WP-CLI commands for managing WooCommerce Price History.
 */
class Price_History_CLI {

	/**
	 * Process all products and update their price history.
	 *
	 * ## EXAMPLES
	 *
	 *     wp price-history process
	 *
	 * @when after_wp_load
	 */
	public function process() {
		$products = wc_get_products( [
			'limit' => -1,
		] );

		$total = count( $products );
		WP_CLI::log( sprintf( 'Processing %d products...', $total ) );

		$woo_price = new Woo_Price();
		$processed = 0;
		$errors = 0;

		foreach ( $products as $product ) {
			try {
				$woo_price->process_product_price_data( $product );
				$processed++;
				WP_CLI::log( sprintf( 'Processed: %s (ID: %d)', $product->get_name(), $product->get_id() ) );
			} catch ( \Exception $e ) {
				$errors++;
				WP_CLI::warning( sprintf( 'Failed to process product ID %d: %s', $product->get_id(), $e->getMessage() ) );
			}
		}

		WP_CLI::success( sprintf( 'Processed %d of %d products. Errors: %d', $processed, $total, $errors ) );
	}
}
