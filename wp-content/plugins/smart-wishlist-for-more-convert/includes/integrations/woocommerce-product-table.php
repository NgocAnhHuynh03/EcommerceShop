<?php
/**
 * WLFMC wishlist integration with WooCommerce Product Table plugin
 *
 * @plugin_name WooCommerce Product Table
 * @version 2.9.5
 * @slug woocommerce-product-table
 * @url https://barn2.co.uk/wordpress-plugins/woocommerce-product-table/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Barn2\Plugin\WC_Product_Table\Data\Abstract_Product_Data' ) ) {
	/**
	 * Gets data for the 'wishlist' column to use in the product table.
	 */
	class WLFMC_Product_Table_Data_Wishlist extends \Barn2\Plugin\WC_Product_Table\Data\Abstract_Product_Data {

		/**
		 * Get wishlist column data
		 *
		 * @return mixed|void
		 */
		public function get_data() {
			return apply_filters( 'wc_product_table_data_wishlist', do_shortcode( '[wlfmc_add_to_wishlist position="shortcode" is_single=""]' ), $this->product );
		}

	}

	add_filter(
		'wc_product_table_custom_table_data_wishlist',
		function ( $data_obj, $product, $args ) {
			return new WLFMC_Product_Table_Data_Wishlist( $product );
		},
		10,
		3
	);
}


