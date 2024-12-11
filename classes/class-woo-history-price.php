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
class Woo_History_Price extends \WC_Product {

	public function track_price_changes( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) || 'product' !== $post->post_type ) {
		return;
		}

		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return;
		}

		// Handle Simple Products, external products, grouped products, etc.
		if ( $product->get_type() !== 'variable' ) {
			$this->handle_simple_product( $product );
		}

		// Handle Variable Products
		if ( $product->get_type() === 'variable' ) {
			$this->handle_variable_product( $product );
		}
	}

	private function handle_simple_product( $product ) {
		$this->handle_single_product( $product );
	}

	private function handle_single_product( $product ) {
		$regular_price = $product->get_regular_price();
		$sale_price    = $product->get_price();
		$price_history = $product->get_meta( '_nvm_price_history' );

		if ( ! is_array( $price_history ) ) {
			$price_history = [];
		}

		$last_entry = end( $price_history );
		$last_price = $last_entry ? $last_entry['sale_price'] : null;

		if ( $last_price !== null && floatval( $last_price ) === floatval( $sale_price ) ) {
			return;
		}

		$price_history[] = [
			'regular_price' => $regular_price,
			'sale_price'    => $sale_price,
			'date'          => current_time( 'mysql' ),
		];

		$price_history = $this->keep_track_100_days( $price_history );
		$product->update_meta_data( '_nvm_price_history', $price_history );

		$min_price = $this->get_min_price_gr( $price_history );
		$product->update_meta_data( '_nvm_min_price_30', $min_price );

		$product->save_meta_data();
	}

	private function handle_variable_product( $product ) {
		$children      = $product->get_children();
		$min_price = [];

		foreach ( $children as $child_id ) {
			$child = wc_get_product( $child_id );
			if ( ! $child || ! $child->is_in_stock() ) {
				continue;
			}

			$this->handle_single_product( $child );

			$min_price[] = $product->get_meta( '_nvm_price_history' );
		}

		$min_price = $this->get_min_price_gr( $min_price );
		$product->update_meta_data( '_nvm_min_price_30', $min_price );
	}

	public function keep_track_100_days( $price_history ) {

		// Get the timestamp for 100 days ago
		$one_hundred_days_ago = strtotime( '-100 days' );

		// Loop through the price history array
		foreach ( $price_history as $id => $entry ) {
			// Convert the entry's date to a timestamp
			$entry_date = strtotime( $entry['date'] );

			// Check if the entry date is older than 100 days
			if ( $entry_date < $one_hundred_days_ago ) {
				unset( $price_history[ $id ] );
			}
		}

		return $price_history;
	}

	/**
	 * Keep only the lowest sale price from the last 30 days.
	 *
	 * @param array $price_history The price history.
	 * @return float|null The lowest sale price in the last 30 days, or null if no valid prices exist.
	 */
	public function get_min_price_gr( $price_history ) {
		$min_price = null;
		$thirty_days_ago = strtotime( '-30 days' );

		foreach ( $price_history as $entry ) {
			// Parse entry date
			$entry_date = strtotime( $entry['date'] );

			// Skip entries older than 30 days
			if ( $entry_date < $thirty_days_ago ) {
				continue;
			}

			// Skip entries with no sale price or empty sale price
			if ( ! isset( $entry['sale_price'] ) || empty( $entry['sale_price']) ) {
				continue;
			}

			// Compare and update the minimum sale price
			if ( $entry['sale_price'] < $min_price ) {
				$min_price = $entry['sale_price'];
			}
		}

		// Return the minimum price found or null if no entries match the criteria
		return $min_price;
	}

	function display_price_history_metabox() {
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

