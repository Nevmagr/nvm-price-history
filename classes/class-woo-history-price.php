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

	private $service_url;
	private $consumer_key;
	private $consumer_secret;

	public function __construct() {

		$this->service_url     = get_site_url();
		$this->consumer_key    = sanitize_text_field( get_option( 'nvm_woo_user' ) );
		$this->consumer_secret = sanitize_text_field( get_option( 'nvm_woo_pass' ) );

		// checkif the creds are ok
		if ( empty( $this->consumer_key ) || empty( $this->consumer_secret ) ) {

			nvm_error_log( 'Empty WooCommerce API credentials.' );

			return null;
		}

		// check if the creds are working by testing the connection
		$api_connection = $this->test_api_connection();

		if ( ! $api_connection ) {
			return null;
		}
		return true;
	}
}

