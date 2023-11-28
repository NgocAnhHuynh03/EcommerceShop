<?php
/**
 * Plugin Name: MC Woocommerce Wishlist
 * Plugin URI: https://moreconvert.com/smart-wishlist-for-more-convert
 * Description: With the MC Wishlist plugin, your website users can add their favorite products to the wishlist. Then you can persuade them to buy products on their wishlist through the magic of our Marketing Toolkits.
 * Version: 1.7.0
 * Author: MoreConvert
 * Author URI: https://moreconvert.com
 * Text Domain: wc-wlfmc-wishlist
 * Domain Path: /languages/
 * Requires PHP: 7.1
 * WC requires at least: 5.7
 * WC tested up to: 8.2.2
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.7.0
 */

/**
 * Copyright 2020  Your Inspiration Solutions (email : info@moreconvert.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! defined( 'MC_WLFMC_URL' ) ) {
	define( 'MC_WLFMC_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'MC_WLFMC_MAIN_FILE' ) ) {
	define( 'MC_WLFMC_MAIN_FILE', __FILE__ );
}

if ( ! defined( 'MC_WLFMC_DIR' ) ) {
	define( 'MC_WLFMC_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'MC_WLFMC_INC' ) ) {
	define( 'MC_WLFMC_INC', MC_WLFMC_DIR . 'includes/' );
}

add_action( 'plugins_loaded', 'wlfmc_wishlist_install', 11 );
add_action( 'wlfmc_init', 'wlfmc_load' );
register_activation_hook( __FILE__, 'wlfmc_activation_function' );

/***
 * Plugin install
 *
 * @return void
 * @version 1.3.3
 */
function wlfmc_wishlist_install() {

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'wlfmc_install_woocommerce_admin_notice' );
	} else {

		require_once MC_WLFMC_INC . 'functions.php';

		do_action( 'wlfmc_init' );
	}
}

if ( ! function_exists( 'wlfmc_load' ) ) {
	/**
	 * Load Wishlist For More Convert.
	 *
	 * @return void
	 *
	 * @verison 1.6.7
	 */
	function wlfmc_load() {
		wlfmc_set_locale();

		require_once MC_WLFMC_DIR . 'options/init.php';
		mct_option_plugin_loader( MC_WLFMC_DIR );

		// Load required classes and functions.
		require_once MC_WLFMC_INC . 'data-stores/class-wlfmc-customer-data-store.php';
		require_once MC_WLFMC_INC . 'data-stores/class-wlfmc-wishlist-data-store.php';
		require_once MC_WLFMC_INC . 'data-stores/class-wlfmc-wishlist-item-data-store.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-email.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-exception.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-form-handler.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-ajax-handler.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-session.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-cron.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-integration-cache.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-customer.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-wishlist.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-wishlist-item.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-wishlist-factory.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-api.php';
		require_once MC_WLFMC_INC . 'class-wlfmc.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-frontend.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-install.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-shortcode.php';
		require_once MC_WLFMC_INC . 'marketing-toolkits/analytics/class-wlfmc-analytics.php';

		require_once MC_WLFMC_INC . 'elementor/class-wlfmc-elementor.php';
		require_once MC_WLFMC_INC . 'gutenberg/class-wlfmc-gutenberg.php';
		require_once MC_WLFMC_INC . 'widgets/class-wlfmc-counter-widget.php';

		require_once MC_WLFMC_INC . 'marketing-toolkits/email-automation/class-wlfmc-automation.php';
		require_once MC_WLFMC_INC . 'marketing-toolkits/email-automation/class-wlfmc-automation-emails.php';
		if ( is_admin() ) {
			require_once MC_WLFMC_INC . 'class-wlfmc-admin.php';
			require_once MC_WLFMC_INC . 'class-wlfmc-admin-notice.php';
			require_once MC_WLFMC_INC . 'marketing-toolkits/email-automation/class-wlfmc-automation-admin.php';
			require_once MC_WLFMC_INC . 'marketing-toolkits/email-automation/class-wlfmc-automation-ajax-handler.php';
			require_once MC_WLFMC_INC . 'marketing-toolkits/email-automation/class-wlfmc-automation-table.php';
			require_once MC_WLFMC_INC . 'marketing-toolkits/email-automation/class-wlfmc-automation-item-table.php';
		}

		$active_theme = get_stylesheet();
		$theme        = wp_get_theme();
		if ( $theme->parent() ) {
			$theme = wp_get_theme()->parent();
		}
		$theme_name = $theme instanceof WP_Theme ? $theme->get( 'Name' ) : false;

		// Integration themes.
		if ( in_array( $theme_name, array( 'Astra', 'Astra Child' ), true ) || in_array( $active_theme, array( 'astra', 'astra-child' ), true ) ) {
			require_once MC_WLFMC_INC . 'integrations/astra.php';
		}
		if ( in_array( $theme_name, array( 'Woostify', 'Woostify Child' ), true ) || in_array( $active_theme, array( 'woostify', 'woostify-child' ), true ) ) {
			require_once MC_WLFMC_INC . 'integrations/woostify.php';
		}
		require_once MC_WLFMC_INC . 'integrations/botiga.php';
		require_once MC_WLFMC_INC . 'integrations/divi.php';
		require_once MC_WLFMC_INC . 'integrations/flatsome.php';
		require_once MC_WLFMC_INC . 'integrations/kallyas.php';
		require_once MC_WLFMC_INC . 'integrations/metro.php';
		require_once MC_WLFMC_INC . 'integrations/neve.php';
		require_once MC_WLFMC_INC . 'integrations/oceanwp.php';
		require_once MC_WLFMC_INC . 'integrations/porto.php';
		require_once MC_WLFMC_INC . 'integrations/rehub.php';
		require_once MC_WLFMC_INC . 'integrations/storefront.php';
		require_once MC_WLFMC_INC . 'integrations/shoptimizer.php';
		require_once MC_WLFMC_INC . 'integrations/woodmart.php';
		require_once MC_WLFMC_INC . 'integrations/kadence.php';
		require_once MC_WLFMC_INC . 'integrations/blocksy.php';
		require_once MC_WLFMC_INC . 'integrations/go.php';
		require_once MC_WLFMC_INC . 'integrations/generatepress.php';
		require_once MC_WLFMC_INC . 'integrations/pro.php';

		// Integration plugins.
		require_once MC_WLFMC_INC . 'integrations/woolentor-addons.php';
		require_once MC_WLFMC_INC . 'integrations/jet-woo-builder.php';
		require_once MC_WLFMC_INC . 'integrations/qi-addons-for-elementor.php';
		require_once MC_WLFMC_INC . 'integrations/powerpack-elements.php';
		require_once MC_WLFMC_INC . 'integrations/us-core.php';
		require_once MC_WLFMC_INC . 'integrations/woo-variation-gallery.php';
		require_once MC_WLFMC_INC . 'integrations/woolementor.php';
		require_once MC_WLFMC_INC . 'integrations/premium-addons-for-elementor.php';
		require_once MC_WLFMC_INC . 'integrations/essential-addons-elementor.php';
		require_once MC_WLFMC_INC . 'integrations/shopengine.php';
		require_once MC_WLFMC_INC . 'integrations/yith-woocommerce-quick-view.php';
		require_once MC_WLFMC_INC . 'integrations/woo-custom-product-addons.php';
		require_once MC_WLFMC_INC . 'integrations/woocommerce-booking.php';
		require_once MC_WLFMC_INC . 'integrations/woocommerce-bookings.php';
		require_once MC_WLFMC_INC . 'integrations/woocommerce-product-bundles.php';
		require_once MC_WLFMC_INC . 'integrations/woocommerce-composite-products.php';
		require_once MC_WLFMC_INC . 'integrations/woocommerce-product-addons.php';
		require_once MC_WLFMC_INC . 'integrations/woocommerce-gravityforms-product-addons.php';
		require_once MC_WLFMC_INC . 'integrations/woocommerce-tm-extra-product-options.php';
		require_once MC_WLFMC_INC . 'integrations/woocommerce-extra-product-options-pro.php';
		require_once MC_WLFMC_INC . 'integrations/buy-now-button-for-woocommerce.php';
		require_once MC_WLFMC_INC . 'integrations/cleantalk-spam-protect.php';
		require_once MC_WLFMC_INC . 'integrations/duracelltomi-google-tag-manager.php';
		require_once MC_WLFMC_INC . 'integrations/wpc-variations-radio-buttons.php';
		require_once MC_WLFMC_INC . 'integrations/woocommerce-square.php';
		require_once MC_WLFMC_INC . 'integrations/woocommerce-waitlist.php';
		require_once MC_WLFMC_INC . 'integrations/woo-variation-swatches-pro.php';
		require_once MC_WLFMC_INC . 'integrations/woo-payment-gateway.php';
		require_once MC_WLFMC_INC . 'integrations/nasa-core.php';
		require_once MC_WLFMC_INC . 'integrations/elex-woocommerce-catalog-mode.php';
		require_once MC_WLFMC_INC . 'integrations/clever-swatches.php';
		require_once MC_WLFMC_INC . 'integrations/woocommerce-product-table.php';
		require_once MC_WLFMC_INC . 'integrations/show-single-variations-premium.php';
		require_once MC_WLFMC_INC . 'integrations/product-quantity-for-woocommerce.php';
		require_once MC_WLFMC_INC . 'integrations/pw-woocommerce-gift-cards.php';
		require_once MC_WLFMC_INC . 'integrations/woo-product-table.php';
		require_once MC_WLFMC_INC . 'integrations/improved-variable-product-attributes.php';
		require_once MC_WLFMC_INC . 'integrations/hide-price-add-to-cart-button.php';
		require_once MC_WLFMC_INC . 'integrations/woocommerce-mix-and-match-products.php';
		require_once MC_WLFMC_INC . 'integrations/woo-product-bundle.php';
		require_once MC_WLFMC_INC . 'integrations/advanced-product-fields-for-woocommerce.php';
		require_once MC_WLFMC_INC . 'integrations/advanced-product-fields-for-woocommerce-pro.php';
		require_once MC_WLFMC_INC . 'integrations/woocommerce-product-addon.php';
		require_once MC_WLFMC_INC . 'integrations/yith-woocommerce-product-add-ons.php';
		require_once MC_WLFMC_INC . 'integrations/yith-woocommerce-product-bundles.php';
		require_once MC_WLFMC_INC . 'integrations/product-extras-for-woocommerce.php';
		require_once MC_WLFMC_INC . 'integrations/wp-grid-builder.php';
		require_once MC_WLFMC_INC . 'integrations/ultimate-elementor.php';
		require_once MC_WLFMC_INC . 'integrations/variation-swatches-woo.php';
		require_once MC_WLFMC_INC . 'integrations/flexible-product-fields.php';
		require_once MC_WLFMC_INC . 'integrations/yayextra.php';

		// Let's start!

		WLFMC();

	}
}

if ( ! function_exists( 'wlfmc_set_locale' ) ) {
	/**
	 * Set the locale for the plugin.
	 */
	function wlfmc_set_locale(): void {
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		}

		$locale = apply_filters( 'plugin_locale', $locale, 'smart-wishlist-for-more-convert' );

		$mofile  = sprintf( '%1$s-%2$s.mo', 'smart-wishlist-for-more-convert', $locale );
		$mofiles = array(
			WP_LANG_DIR . DIRECTORY_SEPARATOR . basename( MC_WLFMC_DIR ) . DIRECTORY_SEPARATOR . $mofile,
			WP_LANG_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $mofile,
		);
		foreach ( $mofiles as $mofile ) {
			if ( file_exists( $mofile ) && load_textdomain( 'wc-wlfmc-wishlist', $mofile ) ) {
				return;
			}
		}

		load_plugin_textdomain( 'wc-wlfmc-wishlist', false, basename( MC_WLFMC_DIR ) . DIRECTORY_SEPARATOR . 'languages' );
	}
}

/**
 * Shows admin notice when plugin is activated without WooCommerce
 *
 * @return void
 */
function wlfmc_install_woocommerce_admin_notice() {
	?>
	<div class="error">
		<p><?php echo esc_html( 'Smart WooCommerce Wishlist For More Convert ' . esc_html__( 'is enabled but not effective. It requires WooCommerce to work.', 'wc-wlfmc-wishlist' ) ); ?></p>
	</div>
	<?php
}

/**
 * Operation after activating the plugin.
 *
 * @return void
 * @version 1.2.0
 */
function wlfmc_activation_function() {
	flush_rewrite_rules();

	set_transient( '_wlfmc_wishlist_activation_redirect', true, 30 );

	update_option( 'smart-wishlist-for-more-convert_tracking_notice', 'hide' );

	update_option( 'wlfmc-skip-wizard-notice', true );

	update_option( 'wlfmc-finish-wizard-notice', true );

	// If our option doesn't exist already, we'll create it with today's timestamp.
	if ( ! get_option( 'wlfmc_wishlist_activation_date' ) ) {
		update_option( 'wlfmc_wishlist_activation_date', gmdate( 'Y-m-d' ) );
	}
}

if ( ! function_exists( 'log_me' ) ) {
	/**
	 * Manual Log
	 *
	 * @param Array|String|Object $message A message for log.
	 */
	function log_me( $message ): void {

		if ( is_array( $message ) || is_object( $message ) ) {
			error_log( print_r( $message, true ) ); // @codingStandardsIgnoreLine.
		} else {
			error_log( $message ); // @codingStandardsIgnoreLine.
		}

	}
}
if ( ! function_exists( 'wlfmc_log' ) ) {
	/**
	 * Wlfmc Log
	 *
	 * @param string              $log_name a name for error.
	 * @param Array|String|Object $log A log abject , array or string.
	 * @param string              $before A message for before log.
	 * @param string              $after A message for after log.
	 */
	function wlfmc_log( $log_name, $log, $before = '', $after = '' ) {
		if ( apply_filters( 'wlfmc_disable_log', true ) ) {
			return;
		}
		ob_start();

		if ( is_array( $log ) || is_object( $log ) ) {
            print_r( $log ); // @codingStandardsIgnoreLine.
		} else {
			echo wp_kses_post( $log ); // @codingStandardsIgnoreLine.
		}
		$log = ob_get_clean();

		log_me( PHP_EOL . '======================================================' . PHP_EOL . '[' . $log_name . ']:' . $before . PHP_EOL . $log . PHP_EOL . $after . PHP_EOL . '======================================================' );
	}
}


require __DIR__ . '/vendor/autoload.php';


/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_smart_wishlist_for_more_convert() {

	if ( ! class_exists( 'WLFMC_Appsero\Client' ) ) {
		require_once MC_WLFMC_DIR . 'lib/appsero/client/src/Client.php';
	}

	$client = new WLFMC_Appsero\Client( '4fd3ce9d-2f72-4d4d-9344-ac3b11fb6a9c', 'MC Woocommerce Wishlist Plugin', __FILE__ );

	// Active insights.
	$client->insights()->add_plugin_data()->init();

}

appsero_init_tracker_smart_wishlist_for_more_convert();

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__ );
		}
	}
);
