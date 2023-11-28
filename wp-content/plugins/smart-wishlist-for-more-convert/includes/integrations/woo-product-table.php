<?php
/**
 * WLFMC wishlist integration with Product Table for WooCommerce by codeAstrology (WooproductTable) plugin
 *
 * @plugin_name Product Table for WooCommerce by codeAstrology (WooproductTable)
 * @version 3.3.1
 * @slug woo-product-table
 * @url https://wordpress.org/plugins/woo-product-table/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_filter( 'wpto_template_loc_item_wishlist', 'wlfmc_woo_product_table_integrate', 10 );

/**
 * Integration with Product Table for WooCommerce by codeAstrology (WooproductTable) plugin
 *
 * @return string
 */
function wlfmc_woo_product_table_integrate(): string {
	return MC_WLFMC_INC . 'integrations/woo-product-table/wishlist.php';

}
