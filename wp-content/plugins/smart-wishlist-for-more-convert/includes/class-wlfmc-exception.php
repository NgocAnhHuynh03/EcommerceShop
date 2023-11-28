<?php
/**
 * Smart Wishlist Exception
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Exception' ) ) {
	/**
	 * WooCommerce Wishlist Exception
	 */
	class WLFMC_Exception extends Exception {
		/**
		 * Error codes
		 *
		 * @var array $error_codes
		 */
		private $error_codes = array(
			0 => 'error',
			1 => 'exists',
		);

		/**
		 * Get text code
		 *
		 * @return mixed|string
		 */
		public function getTextualCode() {
			$code = $this->getCode();

			if ( array_key_exists( $code, $this->error_codes ) ) {
				return $this->error_codes[ $code ];
			}

			return 'error';
		}
	}
}
