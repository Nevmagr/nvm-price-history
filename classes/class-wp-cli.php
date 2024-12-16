<?php

/**
 * Set namespace.
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
class Price_History_CLI extends WP_CLI_Command {

    /**
     * Registers the WP-CLI commands.
     */
    public function __construct() {
        $this->register_commands();
    }

    /**
     * Registers the WP-CLI commands.
     */
    private function register_commands() {
        WP_CLI::add_command( 'price-history', [ $this, 'price_history' ] );
    }

    // get all products and update price history
    public function price_history() {
        $products = wc_get_products( [
            'limit' => -1,
        ] );

        foreach ( $products as $product ) {
            $this->process_product_price_data( $product );
        }
    }

    /**
     * Processes the price data for a product and updates relevant metadata.
     *
     * @param WC_Product $product The WooCommerce product object.
     * @return void
     */
    private function process_product_price_data( $product ) {

        $min_price = $product->get_meta( '_nvm_min_price_30' );

		if ( ! $min_price ) {

			if ( ! $product instanceof WC_Product ) {
				return null; // Ensure the input is a valid product.
			}

            $woo_price = new Woo_Price();
			$woo_price->process_product_price_data( $product );

			$min_price = $product->get_meta( '_nvm_min_price_30' );
		}
    }
}