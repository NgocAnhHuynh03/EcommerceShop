<?php
/**
 * WLFMC wishlist integration with UpSolution Core plugin ( Impreza & Zephyr themes )
 *
 * @plugin_name UpSolution Core
 * @version 8.15.1
 * @slug us-core
 * @url  https://help.us-themes.com/impreza/us-core/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
add_action( 'admin_notices', 'wlfmc_us_core_admin_notice' );
add_action( 'wp_ajax_wlfmc_us_core_dismiss', 'wlfmc_us_core_admin_notice_dismiss' );

/**
 * UpSolution Core admin notice
 *
 * @return void
 */
function wlfmc_us_core_admin_notice() {
	global $pagenow;
	$current_screen = get_current_screen();
	$is_dashboard   = ! empty( $current_screen ) && $current_screen->base && 'dashboard' === $current_screen->base;
	if ( ( ( $is_dashboard || 'plugins.php' === $pagenow ) && defined( 'US_CORE_VERSION' ) ) || ( 'admin.php' === $pagenow && defined( 'US_CORE_VERSION' ) && isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], array( 'mc-wishlist-dashboard', 'mc-email-automations', 'mc-email-campaigns', 'mc-analytics', 'mc-global-settings', 'mc-wishlist-settings', 'mc-multi-list', 'mc-save-for-later', 'mc-ask-for-estimate' ), true ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$dismissed = get_option( 'wlfmc_us_core_dismissed' );
		if ( ! $dismissed ) {
			if ( true === apply_filters( 'wlfmc_enable_us_core_admin_notice', true ) ) {
				$nonce = wp_create_nonce( 'wlfmc_us_core_dismiss' );
				WLFMC_Admin_Notice()->styles();
				?>
				<div id="wlfmc-us-core-admin-notice" class="notice wlfmc-notice wlfmc-notice-error">
					<h2 class="error-text"><?php echo wp_kses_post( __( 'Integrate UpSolution Core with MoreConvert', 'wc-wlfmc-wishlist' ) ); ?></h2>
					<a href="#" class="dismiss-btn" onclick="WlfmcUsCoreDismissNotice('<?php echo esc_attr( $nonce ); ?>')">
						<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11">
							<path id="close" d="M13.076,12.244,8.832,8l4.244-4.244a.588.588,0,1,0-.832-.832h0L8,7.168,3.756,2.924a.588.588,0,0,0-.832.832h0L7.168,8,2.924,12.244a.588.588,0,1,0,.832.832h0L8,8.832l4.244,4.244a.588.588,0,0,0,.832-.832Z" transform="translate(-2.752 -2.752)" fill="#616161"/>
						</svg>
					</a>
					<p>
						<?php esc_html_e( 'To activate the wishlist feature on WooCommerce archive pages using the UpSolution Core plugin (specifically for Impreza & Zephyr themes), you would need to follow the same steps as mentioned earlier: use the grid layout and add a custom HTML element with the content [wlfmc_add_to_wishlist is_single=""].', 'wc-wlfmc-wishlist' ); ?>
					</p>
				</div>
				<script>
					function WlfmcUsCoreDismissNotice(nonce){
						var xhttp = new XMLHttpRequest();
						xhttp.onreadystatechange = function() {
							if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
								document.getElementById("wlfmc-us-core-admin-notice").style.display = "none";
							}
						};
						xhttp.open("POST", "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>", true);
						xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
						xhttp.send("action=wlfmc_us_core_dismiss&nonce=" + nonce);
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
function wlfmc_us_core_admin_notice_dismiss() {
	check_ajax_referer( 'wlfmc_us_core_dismiss', 'nonce' );
	update_option( 'wlfmc_us_core_dismissed', 'true' );
	wp_send_json_success();
}
