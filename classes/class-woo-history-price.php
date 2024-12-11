<?php //phpcs:ignore - \r\n issue

/**
 * Set namespace.
 */
namespace Nvm\Price_History;

/**
 * Check that the file is not accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

/**
 * Class Admin_Menu.
 */
class Woo_Prices_Changes extends \WC_Product {

	public function track_price_changes( $post_id, $post, $update ) {

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( 'product' !== $post->post_type ) {
			return;
		}

		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return;
		}

		$regular_price = $product->get_regular_price();
		$sale_price    = $product->get_price();
		$price_history = $product->get_meta( '_nvm_price_history' );

		if ( ! is_array( $price_history ) ) {
			$price_history = [];
		}

		// Get the last recorded price.
		$last_entry = end( $price_history );
		$last_price = $last_entry ? $last_entry['sale_price'] : null;

		// Only record if the price has changed.
		if ( $last_price !== null && floatval( $last_price ) === floatval( $sale_price ) ) {
			return;
		}

		// Add new price to the history.
		$price_history[] = [
			'regular_price' => $regular_price,
			'sale_price'    => $sale_price,
			'date'          => current_time( 'mysql' ),
		];

		// Save updated history.
		$product->update_meta_data( '_nvm_price_history', $price_history );
		$product->save_meta_data();
	}

	function display_price_history_metabox( ) {
		global $product;

		$price_history = $product->get_meta( '_nvm_price_history' );

		if ( ! is_array( $price_history ) || empty( $price_history ) ) {
			// echo '<p>' . __( 'No price changes recorded.', 'nvm-product-price-history-inline' ) . '</p>';
			return;
		}

		echo '<ul>';
		foreach ( array_reverse( $price_history ) as $entry ) {
			echo '<li>';
			echo '<strong>' . wc_price( $entry['price'] ) . '</strong>';
			echo ' - ' . esc_html( date( 'd M Y H:i', strtotime( $entry['date'] ) ) );
			echo '</li>';
		}
		echo '</ul>';
	}
}

