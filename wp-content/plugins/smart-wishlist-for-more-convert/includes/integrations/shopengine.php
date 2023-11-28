<?php
/**
 * WLFMC wishlist integration with shopengine plugin
 *
 * @plugin_name ShopEngine
 * @version 3.0.0
 * @slug shopengine
 * @url  https://wpmet.com/plugin/shopengine
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.3
 */

use ShopEngine\Core\Register\Model;
use ShopEngine\Core\Register\Module_List;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_shopengine_integrate' );
add_action( 'admin_notices', 'wlfmc_shopengine_admin_notice' );
add_action( 'wp_ajax_wlfmc_shopengine_dismiss', 'wlfmc_shopengine_admin_notice_dismiss' );
add_action( 'wp_ajax_wlfmc_shopengine_wishlist_deactivate', 'wlfmc_shopengine_wishlist_deactivate' );

/**
 * Shopengine admin notice
 *
 * @return void
 */
function wlfmc_shopengine_admin_notice() {
	global $pagenow;
	$current_screen = get_current_screen();
	$is_dashboard   = ! empty( $current_screen ) && $current_screen->base && 'dashboard' === $current_screen->base;
	if ( ( ( $is_dashboard || 'plugins.php' === $pagenow ) && class_exists( 'ShopEngine' ) ) || ( 'admin.php' === $pagenow && class_exists( 'ShopEngine' ) && isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], array( 'mc-wishlist-dashboard', 'mc-email-automations', 'mc-email-campaigns', 'mc-analytics', 'mc-global-settings', 'mc-wishlist-settings', 'mc-multi-list', 'mc-save-for-later', 'mc-ask-for-estimate' ), true ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$user_id = get_current_user_id();
		if ( ! get_user_meta( $user_id, 'wlfmc_shopengine_dismissed' ) ) {
			$m_settings = Model::source( 'settings' )->get_option( 'modules', array() );
			if ( ! empty( $m_settings['wishlist']['status'] ) && 'active' === $m_settings['wishlist']['status'] && true === apply_filters( 'wlfmc_override_shopengine_wishlist', true ) ) {
				$nonce = wp_create_nonce( 'wlfmc_shopengine_dismiss' );
				WLFMC_Admin_Notice()->styles();
				?>
				<div id="wlfmc-shopengine-admin-notice" class="notice wlfmc-notice wlfmc-notice-error">
					<h2 class="error-text"><?php echo wp_kses_post( __( 'MC List Users Should Disable ShopEngine\'s Wishlist for Optimal Experience', 'wc-wlfmc-wishlist' ) ); ?></h2>
					<a href="#" class="dismiss-btn" onclick="WlfmcShopEngineDismissNotice('<?php echo esc_attr( $nonce ); ?>')">
						<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11">
							<path id="close" d="M13.076,12.244,8.832,8l4.244-4.244a.588.588,0,1,0-.832-.832h0L8,7.168,3.756,2.924a.588.588,0,0,0-.832.832h0L7.168,8,2.924,12.244a.588.588,0,1,0,.832.832h0L8,8.832l4.244,4.244a.588.588,0,0,0,.832-.832Z" transform="translate(-2.752 -2.752)" fill="#616161"/>
						</svg>
					</a>
					<p>
						<?php esc_html_e( 'For the best experience using MoreConvert, we recommend disabling the ShopEngine\'s Wishlist. Also, if you need ShopEngine\'s Wishlist to be active, the position of MC Wishlist buttons should be set to shortcode mode to prevent displaying both plugins buttons.', 'wc-wlfmc-wishlist' ); ?>
					</p>
					<p class="wlfmc-notice-action-wrapper">
						<a href="#" class="btn-notice-2" onclick="WlfmcShopEngineWishlistDeactivate('<?php echo esc_attr( $nonce ); ?>')">
							<?php esc_html_e( 'Deactivate ShopEngine\'s Wishlist', 'wc-wlfmc-wishlist' ); ?>
						</a>
					</p>
				</div>
				<script>
					function WlfmcShopEngineDismissNotice(nonce){
						var xhttp = new XMLHttpRequest();
						xhttp.onreadystatechange = function() {
							if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
								document.getElementById("wlfmc-shopengine-admin-notice").style.display = "none";
							}
						};
						xhttp.open("POST", "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>", true);
						xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
						xhttp.send("action=wlfmc_shopengine_dismiss&nonce=" + nonce);
					}
					function WlfmcShopEngineWishlistDeactivate(nonce){
						var xhttp = new XMLHttpRequest();
						xhttp.onreadystatechange = function() {
							if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
								document.getElementById("wlfmc-shopengine-admin-notice").style.display = "none";
							}
						};
						xhttp.open("POST", "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>", true);
						xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
						xhttp.send("action=wlfmc_shopengine_wishlist_deactivate&nonce=" + nonce);
					}
				</script>
				<?php
			}
		}
	}
}

/**
 * Dismissed notice
 *
 * @return void
 */
function wlfmc_shopengine_admin_notice_dismiss() {
	check_ajax_referer( 'wlfmc_shopengine_dismiss', 'nonce' );
	$user_id = get_current_user_id();
	add_user_meta( $user_id, 'wlfmc_shopengine_dismissed', 'true', true );
	wp_send_json_success();
}

/**
 * Dismissed notice
 *
 * @return void
 */
function wlfmc_shopengine_wishlist_deactivate() {
	check_ajax_referer( 'wlfmc_shopengine_dismiss', 'nonce' );
	$user_id = get_current_user_id();
	add_user_meta( $user_id, 'wlfmc_shopengine_dismissed', 'true', true );

	if ( class_exists( 'ShopEngine' ) ) {
		$m_settings                       = Model::source( 'settings' )->get_option( 'modules', array() );
		$m_settings['wishlist']['status'] = 'inactive';
		Model::source( 'settings' )->set_option( 'modules', $m_settings );

	}

	wp_send_json_success();
}

/**
 * Integration with shopengine plugin
 *
 * @return void
 */
function wlfmc_shopengine_integrate() {
	if ( class_exists( 'ShopEngine' ) ) {

		$m_settings = Model::source( 'settings' )->get_option( 'modules', array() );
		if ( ! empty( $m_settings['wishlist']['status'] ) && 'active' === $m_settings['wishlist']['status'] && true === apply_filters( 'wlfmc_override_shopengine_wishlist', true ) ) {
			wlfmc_remove_filters( 'woocommerce_account_menu_items', 'ShopEngine\Modules\Wishlist\Wishlist', 'add_to_menu', 40 );
			remove_all_actions( 'woocommerce_account_wishlist_endpoint' );

			$wishlist_settings = Module_List::instance()->get_settings( 'wishlist' );
			$is_show_in_single = ! isset( $wishlist_settings['show_on_single_page']['value'] ) || 'yes' === $wishlist_settings['show_on_single_page']['value'];
			$is_show           = ! isset( $wishlist_settings['show_on_archive_page']['value'] ) || 'yes' === $wishlist_settings['show_on_archive_page']['value'];
			$show_in_archive   = apply_filters( 'shopengine/module/wishlist/show_icon_in_shop_page', $is_show );// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
			$position          = ! empty( $wishlist_settings['show_icon_where_to']['value'] ) ? $wishlist_settings['show_icon_where_to']['value'] : 'before';
			$position          = apply_filters( 'shopengine/module/wishlist/put_icon_in_side', $position );// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

			if ( true === $is_show_in_single ) {
				wlfmc_remove_filters( 'woocommerce_after_add_to_cart_button', 'ShopEngine\Modules\Wishlist\Wishlist', 'print_wish_button', 10 );
				wlfmc_remove_filters( 'woocommerce_before_add_to_cart_button', 'ShopEngine\Modules\Wishlist\Wishlist', 'print_wish_button', 10 );
				if ( 'after' === $position ) {
					add_action( 'woocommerce_after_add_to_cart_button', 'wlfmc_shopengine_print_button_in_single', 10, 0 );

				} else {
					add_action( 'woocommerce_before_add_to_cart_button', 'wlfmc_shopengine_print_button_in_single', 10, 0 );

				}
			}
			if ( true === $show_in_archive ) {
				wlfmc_remove_filters( 'woocommerce_loop_add_to_cart_link', 'ShopEngine\Modules\Wishlist\Wishlist', 'print_button_in_shop', 10 );
				if ( 'after' === $position ) {
					add_filter( 'woocommerce_loop_add_to_cart_link', 'wlfmc_shopengine_print_button_in_shop_after', 10, 2 );

				} else {
					add_filter( 'woocommerce_loop_add_to_cart_link', 'wlfmc_shopengine_print_button_in_shop_before', 10, 2 );

				}
			}
		}
	}
}

/**
 * Wishlist loop add to wishlist link.
 *
 * @param string $add_to_cart_html    link html.
 * @param string $product product object.
 *
 * @return string|void
 */
function wlfmc_shopengine_print_button_in_shop( $add_to_cart_html, $product ) {
	$m_settings = Model::source( 'settings' )->get_option( 'modules', array() );

	if ( ! empty( $m_settings['wishlist']['status'] ) && 'active' === $m_settings['wishlist']['status'] && true === apply_filters( 'wlfmc_override_shopengine_wishlist', true ) ) {

		$wishlist_settings = Module_List::instance()->get_settings( 'wishlist' );
		$is_show           = ! isset( $wishlist_settings['show_on_archive_page']['value'] ) || 'yes' === $wishlist_settings['show_on_archive_page']['value'];
		$show_in_archive   = apply_filters( 'shopengine/module/wishlist/show_icon_in_shop_page', $is_show );// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		$position          = ! empty( $wishlist_settings['show_icon_where_to']['value'] ) ? $wishlist_settings['show_icon_where_to']['value'] : 'before';
		$position          = apply_filters( 'shopengine/module/wishlist/put_icon_in_side', $position );// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		if ( true === $show_in_archive ) {
			if ( 'after' === $position ) {
				$before = '';
				$after  = do_shortcode( '[wlfmc_add_to_wishlist is_single="false"]' );
			} else {
				$before = do_shortcode( '[wlfmc_add_to_wishlist is_single="false"]' );
				$after  = '';
			}
			return $before . $add_to_cart_html . $after;
		}
	}

}


/**
 * Wishlist loop add to wishlist link.
 *
 * @param string $add_to_cart_html    link html.
 * @param string $product product object.
 *
 * @return string
 */
function wlfmc_shopengine_print_button_in_shop_after( $add_to_cart_html, $product ): string {

	$after = do_shortcode( '[wlfmc_add_to_wishlist is_single="false"]' );
	return $add_to_cart_html . $after;

}
/**
 * Wishlist loop add to wishlist link.
 *
 * @param string $add_to_cart_html    link html.
 * @param string $product product object.
 *
 * @return string
 */
function wlfmc_shopengine_print_button_in_shop_before( $add_to_cart_html, $product ): string {
	$before = do_shortcode( '[wlfmc_add_to_wishlist is_single="false"]' );
	return $before . $add_to_cart_html;

}


/**
 * Wishlist single add to wishlist link.
 *
 * @return void
 */
function wlfmc_shopengine_print_button_in_single() {
	echo do_shortcode( '[wlfmc_add_to_wishlist is_single="true"]' );
}
