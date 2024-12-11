<?php //phpcs:ignore - \r\n issue

/**
 * Set namespace.
 */
namespace Nvm\History_Price;

/**
 * Check that the file is not accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

/**
 * Class Admin_Menu.
 */
class Woo_History_Price {

	public function track_price_changes_inline( $post_id, $post, $update ) {
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

		$current_price = $product->get_regular_price();
		$price_history = get_post_meta( $post_id, '_nvm_price_history', true );

		if ( ! is_array( $price_history ) ) {
			$price_history = [];
		}

		// Get the last recorded price.
		$last_entry = end( $price_history );
		$last_price = $last_entry ? $last_entry['price'] : null;

		// Only record if the price has changed.
		if ( $last_price !== null && floatval( $last_price ) === floatval( $current_price ) ) {
			return;
		}

		// Add new price to the history.
		$price_history[] = [
			'price' => $current_price,
			'date'  => current_time( 'mysql' ),
		];

		// Save updated history.
		update_post_meta( $post_id, '_nvm_price_history', $price_history );
	}

	function display_price_history_metabox( $post ) {
		$price_history = get_post_meta( $post->ID, '_nvm_price_history', true );

		if ( ! is_array( $price_history ) || empty( $price_history ) ) {
			echo '<p>' . __( 'No price changes recorded.', 'nvm-product-price-history-inline' ) . '</p>';
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

