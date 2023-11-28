<?php
/**
 * Smart Wishlist Admin Notice
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.2.0
 * @version 1.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Admin_Notice' ) ) {
	/**
	 * This class handles admin_notice for wishlist plugin
	 */
	class WLFMC_Admin_Notice {

		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Admin_Notice
		 */
		protected static $instance;

		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {

			add_action( 'admin_init', array( $this, 'dismiss_notice' ) );
			add_action( 'admin_notices', array( $this, 'after_skip_wizard' ) );
			add_action( 'admin_notices', array( $this, 'after_finish_wizard' ) );
			add_action( 'admin_notices', array( $this, 'update_tables' ) );
			add_action( 'admin_notices', array( $this, 'wpml_notice' ) );
		}

		/**
		 * Add admin delayed notice.
		 *
		 * @since 1.2.1
		 * @version 1.2.1
		 */
		public function notice() {

			if ( ! is_super_admin() || true === wlfmc_is_true( get_option( 'wlfmc_notice_disabled' ) ) ) {
				return;
			}
			$activate_date = get_option( 'wlfmc_wishlist_activation_date' );
			$today         = strtotime( gmdate( 'Y-m-d' ) );
			if ( ! $activate_date ) {
				update_option( 'wlfmc_wishlist_activation_date', $today );
				$activate_date = $today;
			} else {
				$activate_date = strtotime( $activate_date );
			}

			$args = array(
				array(
					'title'       => '<span class="green-text">' . esc_html__( 'Need More Power for MC Wishlist?', 'wc-wlfmc-wishlist' ) . '</span>',
					'content'     => esc_html__( 'If you need a stronger lists, you can sync it with more plugins and get more powerful sales tools; I suggest you check out the premium version page', 'wc-wlfmc-wishlist' ),
					'btn_title'   => esc_html__( 'Check it Now', 'wc-wlfmc-wishlist' ),
					'btn_class'   => 'btn-notice green-btn',
					'btn_url'     => 'https://moreconvert.com/ag7w',
					'btn_target'  => '_blank',
					'dismiss_url' => '',
				),
				array(
					'title'       => '<span class="orange-text">' . esc_html__( 'Upgrade your website\'s potential with a FREE premium tour', 'wc-wlfmc-wishlist' ) . ' </span>' . esc_html__( 'Your gift for installing MC Wishlist plugin', 'wc-wlfmc-wishlist' ),
					'content'     => esc_html__( 'Click below to demo the exclusive features, and witness the profitable and stunning transformation. Ready to elevate your website game?', 'wc-wlfmc-wishlist' ),
					'btn_title'   => esc_html__( 'Try It Now', 'wc-wlfmc-wishlist' ),
					'btn_class'   => 'btn-notice orange-btn inverse-btn',
					'btn_url'     => 'https://moreconvert.com/11s9',
					'btn_target'  => '_blank',
					'dismiss_url' => '',
				),

				array(
					'image'           => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/growth-profit.gif" class="" width="175px" height="156px" />',
					'title'           => '<span class="orange-text">' . esc_html__( 'Great News!', 'wc-wlfmc-wishlist' ) . '&nbsp;</span>' . esc_html__( 'You\'ve earned a bonus of', 'wc-wlfmc-wishlist' ) . '&nbsp;<span class="orange-text">' . esc_html__( '$74.50', 'wc-wlfmc-wishlist' ) . ' </span>',
					'content'         => esc_html__( 'join our Rewards Club and receive up to $74.50 off the MoreConvert Premium Plugin. With this offer, you\'ll be able to boost your sales and make your store more beautiful in no time.', 'wc-wlfmc-wishlist' ),
					'btn_title'       => '<span class="dashicons dashicons-saved"></span>&nbsp;' . esc_html__( 'Lets Go', 'wc-wlfmc-wishlist' ),
					'btn_class'       => 'btn-notice-2',
					'btn_desc'        => esc_html__( 'Take advantage of this incredible deal right now!', 'wc-wlfmc-wishlist' ),
					'btn_url'         => 'https://moreconvert.com/irxj',
					'btn_target'      => '_blank',
					'timer'           => 48,
					'disable_for_pro' => true,
					'dismiss_url'     => '',
				),
			);
			$notice_after_days = array(
				'3'  => $args[0],
				'7'  => $args[2],
				'10' => $args[1],
				'15' => $args[0],
				'16' => $args[1],
				'30' => $args[0],
				'35' => $args[1],
			);
			foreach ( $notice_after_days as $day => $notice_args ) {
				$day = intval( $day );
				if ( ! wlfmc_is_true( get_option( 'wlfmc-disable-notice-after-' . $day . '-days' ) ) ) {

					if ( strtotime( '+' . $day . ' days', $activate_date ) <= $today ) {
						$dismiss_nonce = wp_create_nonce( 'wlfmc-after-days-notice' );
						update_option( 'wlfmc_wishlist_activation_date', gmdate( 'y-m-d H:i:s', strtotime( '-' . $day . ' days', $today ) ) );
						$notice_args['dismiss_url'] = add_query_arg(
							array(
								'wlfmc-notice-after-' . $day . '-days-dismiss' => '1',
								'_wpnonce' => $dismiss_nonce,
							),
							$this->clean_url()
						);
						// Disable notice for premium users.
						if ( isset( $notice_args['disable_for_pro'] ) && true === $notice_args['disable_for_pro'] && ( defined( 'MC_WLFMC_PREMIUM' ) || get_option( 'wlfmc_premium_version' ) ) ) {
							break;
						}
						// Disable notice if it has a timer and expired.
						if ( isset( $notice_args['timer'] ) ) {
							$expired = get_option( 'wlfmc-expired-notice-after-' . $day . '-days' );
							if ( ! $expired ) {
								update_option( 'wlfmc-expired-notice-after-' . $day . '-days', time() + ( intval( $notice_args['timer'] ) * 60 * 60 ) );
							} elseif ( $expired < time() ) {
								update_option( 'wlfmc-disable-notice-after-' . $day . '-days', true );
								break;
							}
						}

						$this->output( $notice_args, $day );
					}
					break;
				}
			}
		}

		/**
		 * Display a message of access denial when attempting to make changes if the default language is different from the current language
		 *
		 * @return void
		 */
		public function wpml_notice() {
			$screen = get_current_screen();
			if ( $screen && in_array( $screen->parent_base, array( 'mc-wishlist-setup', 'mc-wishlist-dashboard', 'mc-global-settings', 'mc-multi-list', 'mc-wishlist-settings', 'mc-save-for-later', 'mc-analytics', 'mac-automations', 'mc-campaigns' ), true ) ) {
				if ( function_exists( 'wpml_object_id_filter' ) || function_exists( 'icl_get_languages' ) ) {
					if ( apply_filters( 'wpml_current_language', null ) !== apply_filters( 'wpml_default_language', null ) ) {
						$this->styles();
						?>
						<div id="wlfmc-not-access-admin-notice" class="notice wlfmc-notice wlfmc-notice-error">
							<h2 class="error-text"><?php echo wp_kses_post( __( 'Changes are not saved in the MoreConvert plugin!', 'wc-wlfmc-wishlist' ) ); ?></h2>
							<p><?php echo wp_kses_post( __( 'To modify settings, campaigns, and automations, we must revert to the website\'s default language to save the settings without any issues.', 'wc-wlfmc-wishlist' ) ); ?></p>
						</div>
						<?php
					}
				}
			}
		}

		/**
		 * Dismiss Notice.
		 */
		public function dismiss_notice() {

			if ( isset( $_GET['mct-wlfmc_options-wizard-skip'] ) && '1' === $_GET['mct-wlfmc_options-wizard-skip'] ) {

				update_option( 'wlfmc-skip-wizard-notice', false );

			}

			if ( isset( $_GET['mct-wlfmc_options-wizard-finish'] ) && '1' === $_GET['mct-wlfmc_options-wizard-finish'] ) {

				update_option( 'wlfmc-finish-wizard-notice', false );

			}

			if ( isset( $_GET['wlfmc-skip-wizard-dismiss'] ) && '1' === $_GET['wlfmc-skip-wizard-dismiss'] ) {

				update_option( 'wlfmc-skip-wizard-notice', true );

			}

			if ( isset( $_GET['wlfmc-finish-wizard-dismiss'] ) && '1' === $_GET['wlfmc-finish-wizard-dismiss'] ) {

				update_option( 'wlfmc-finish-wizard-notice', true );

			}
			if ( isset( $_GET['wlfmc-show-tracking-notice'] ) && '1' === $_GET['wlfmc-show-tracking-notice'] ) {

				delete_option( 'smart-wishlist-for-more-convert_tracking_notice' );

			}

			$notice_after_days = array( '3', '7', '10', '15', '16', '30', '35' );
			$nonce             = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

			foreach ( $notice_after_days as $day ) {

				if ( isset( $_GET[ 'wlfmc-notice-after-' . $day . '-days-dismiss' ] ) && '1' === $_GET[ 'wlfmc-notice-after-' . $day . '-days-dismiss' ] && wp_verify_nonce( $nonce, 'wlfmc-after-days-notice' ) ) {

					update_option( 'wlfmc-disable-notice-after-' . $day . '-days', true );

				}
			}
		}

		/**
		 * Update tables
		 *
		 * @return void
		 */
		public function update_tables() {
			if ( ! is_super_admin() ) {
				return;
			}

			$version = get_option( 'wlfmc_need_update_tables' );
			if ( '1.6.3' === $version || '1.7.0' === $version ) {
				$message = '';
				if ( '1.6.3' === $version ) {
					$options = new MCT_Options( 'wlfmc_options' );
					$options->update_option( 'wishlist_enable', '0' );
					/* translators: %s version */
					$message = sprintf( esc_html__( 'To ensure proper functionality of the MoreConvert Wishlist plugin version %s and above, it is necessary to update the database tables. Please note that both the premium and free versions of MoreConvert are temporarily unavailable at this time.', 'wc-wlfmc-wishlist' ), esc_attr( $version ) );
				}
				if ( '1.7.0' === $version ) {
					/* translators: %s version */
					$message = sprintf( esc_html__( 'In our previous version, certain bilingual websites experienced interference issues. To ensure smooth operation of the MoreConvert (former MC Wishlist) plugin, version %s and above, it\'s essential to update the database tables.', 'wc-wlfmc-wishlist' ), esc_attr( $version ) );
				}
				$this->styles();
				?>
				<div id="wlfmc-update-tables-admin-notice" class="notice wlfmc-notice wlfmc-notice-error">
					<h2 class="error-text"><?php echo wp_kses_post( __( 'Attention: Important Plugin Update Required!', 'wc-wlfmc-wishlist' ) ); ?></h2>
					<p><?php echo esc_attr( $message ); ?></p>
					<p class="wlfmc-notice-action-wrapper">
						<a href="#" class="wlfmc-update-tables btn-notice-2" data-updated="<?php esc_html_e( 'Updated!', 'wc-wlfmc-wishlist' ); ?>" data-updating="<?php esc_html_e( 'Updating ...', 'wc-wlfmc-wishlist' ); ?>" data-label="<?php esc_html_e( 'Update tables', 'wc-wlfmc-wishlist' ); ?>" data-version="<?php echo esc_attr( $version ); ?>">
							<?php esc_html_e( 'Update tables', 'wc-wlfmc-wishlist' ); ?>
						</a>
					</p>
				</div>
				<script>
					jQuery(
						function ($) {
							var UpdateTables = function ( offset , element ) {
								$.ajax(
									{
										url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
										data: {
											action : 'wlfmc_update_table_database',
											key: '<?php echo esc_attr( wp_create_nonce( 'ajax-nonce' ) ); ?>',
											offset: offset,
											version: element.data('version')
										},
										method: 'post',
										success: function (response) {
											if (response.success) {

												element.text( response.data.percentage + '%' + ' ' + element.data( 'updating' ) );

												if ( 100 <= parseInt( response.data.percentage ) ) {
													element.text( element.data( 'updated' ) );
													location.reload();
												} else {
													UpdateTables( parseInt( response.data.offset ), element );
												}
											} else {
												element.text( element.data( 'label' ) );
												element.prop( 'disabled', false );
												window.alert( response.data.message );
											}

										}
									}
								).fail(
									function (response) {
										element.text( element.data( 'label' ) );
										window.console.log( response );
									}
								);
							};
							$( document.body ).on(
								'click',
								'.wlfmc-update-tables',
								function (e) {
									var element = $( this );
									e.preventDefault();
									element.text( element.data( 'updating' ) );
									UpdateTables( 0 , element )
									return false;
								}
							);
						}
					);

				</script>
				<?php
			}
		}

		/**
		 * Add admin notice after skip wizard.
		 */
		public function after_skip_wizard() {

			if ( ! is_super_admin() || wlfmc_is_true( get_option( 'wlfmc-skip-wizard-notice' ) ) ) {
				return;
			}
			$args = array(
				'wrapper_class' => 'notice-skip-wizard',
				'title'         => esc_html__( 'Thanks for installing MC Wishlist!', 'wc-wlfmc-wishlist' ),
				'content'       => esc_html__( 'It is easy to use the MC Wishlist. Please use the setup wizard to quick start setup.', 'wc-wlfmc-wishlist' ),
				'btn_title'     => esc_html__( 'Start Wizard', 'wc-wlfmc-wishlist' ),
				'btn_class'     => 'btn-notice blue-btn',
				'btn_url'       => add_query_arg(
					array(
						'wlfmc-skip-wizard-dismiss'  => 1,
						'wlfmc-show-tracking-notice' => 1,
					),
					get_admin_url() . 'admin.php?page=mc-wishlist-setup'
				),
				'btn_target'    => '_self',
				'dismiss_url'   => add_query_arg(
					array(
						'wlfmc-skip-wizard-dismiss'  => 1,
						'wlfmc-show-tracking-notice' => 1,
					),
					$this->clean_url()
				),
			);

			$this->output( $args );

		}

		/**
		 * Add admin notice after finish wizard.
		 */
		public function after_finish_wizard() {
			if ( ! is_super_admin() || wlfmc_is_true( get_option( 'wlfmc-finish-wizard-notice' ) ) ) {
				return;
			}
			$args = array(
				'wrapper_class' => 'notice-finish-wizard',
				'title'         => esc_html__( 'Together we will increase your sales.', 'wc-wlfmc-wishlist' ),
				'content'       => esc_html__( 'Thank you for installing MC Wishlist plugin. Our main goal and challenge is to increase your site sales effortlessly, without the need for more traffic. If you would like to join us in this challenge, check the marketing settings and documentation right now.', 'wc-wlfmc-wishlist' ),
				'btn_title'     => esc_html__( "Let's Go Marketing", 'wc-wlfmc-wishlist' ),
				'btn_class'     => 'btn-notice',
				'btn_url'       => add_query_arg(
					array(
						'wlfmc-finish-wizard-dismiss' => 1,
						'page'                        => 'mc-email-automations',
					),
					admin_url( 'admin.php' )
				),
				'btn_target'    => '_self',
				'dismiss_url'   => add_query_arg( 'wlfmc-finish-wizard-dismiss', 1, $this->clean_url() ),
			);

			$this->output( $args );

		}

		/**
		 * Print admin notice
		 *
		 * @param array       $args Array of arguments.
		 * @param string|null $day the day it will be implemented.
		 */
		public function output( $args, $day = null ) {
			$this->styles();
			if ( isset( $args['timer'] ) && '' !== $args['timer'] ) {
				$expired = get_option( 'wlfmc-expired-notice-after-' . $day . '-days' );
				if ( ! $expired ) {
					update_option( 'wlfmc-expired-notice-after-' . $day . '-days', time() + ( intval( $args['timer'] ) * 60 * 60 ) );
				}
				$expired = get_option( 'wlfmc-expired-notice-after-' . $day . '-days' );
				$timer   = $args['timer'];
				?>
				<script>
					(function() {
						document.addEventListener("DOMContentLoaded", function() {
							var timerDuration = <?php echo esc_attr( $timer ); ?>;
							var timerExpiry = <?php echo esc_attr( $expired ); ?>;

							// Check if the timer expiry is valid and calculate the remaining time
							if (timerExpiry && (timerExpiry + timerDuration > new Date().getTime() / 1000)) {
								var remainingTime = timerExpiry + timerDuration - new Date().getTime() / 1000;

								// Display the remaining time in separate hour, minute, and second tags
								var hours = Math.floor(remainingTime / 3600);
								var minutes = Math.floor((remainingTime % 3600) / 60);
								var seconds = Math.floor(remainingTime % 60);
								document.getElementById("wlfmc-notice-timer-hours").textContent = hours+'h';
								document.getElementById("wlfmc-notice-timer-minutes").textContent = minutes+'m';
								document.getElementById("wlfmc-notice-timer-seconds").textContent = seconds+'s';

								// Update the timer every second
								setInterval(function() {
									remainingTime--;
									if (remainingTime <= 0) {
										// Hide the notice when the timer expires
										document.querySelector("#wlfmc-notice-<?php echo esc_attr( $day ); ?>").style.display = "none";
									} else {
										// Update the hour, minute, and second tags
										var hours = Math.floor(remainingTime / 3600);
										var minutes = Math.floor((remainingTime % 3600) / 60);
										var seconds = Math.floor(remainingTime % 60);
										document.getElementById("wlfmc-notice-timer-hours").textContent = hours+'h';
										document.getElementById("wlfmc-notice-timer-minutes").textContent = minutes+'m';
										document.getElementById("wlfmc-notice-timer-seconds").textContent = seconds+'s';
									}
								}, 1000);
							} else {
								// Hide the notice if the timer expiry is invalid
								document.querySelector("#wlfmc-notice-<?php echo esc_attr( $day ); ?>").style.display = "none";
							}
						});
					})();
				</script>
				<?php
			}
			?>
			<div id="wlfmc-notice-<?php echo $day ? esc_attr( $day ) : esc_attr( wp_unique_id() ); ?>"
				class="notice wlfmc-notice <?php echo isset( $args['wrapper_class'] ) ? esc_attr( $args['wrapper_class'] ) : ''; ?>">
				<?php if ( isset( $args['image'] ) && '' !== $args['image'] ) : ?>
					<div class="wlfmc-notice-with-image">
						<div class="wlfmc-image-wrapper">
							<?php echo wp_kses_post( $args['image'] ); ?>
						</div>
						<div class="wlfmc-content-wrapper">
				<?php endif; ?>
							<h2><?php echo wp_kses_post( $args['title'] ); ?></h2>
							<a href="<?php echo esc_url( $args['dismiss_url'] ); ?>" class="dismiss-btn">
								<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11">
									<path id="close" d="M13.076,12.244,8.832,8l4.244-4.244a.588.588,0,1,0-.832-.832h0L8,7.168,3.756,2.924a.588.588,0,0,0-.832.832h0L7.168,8,2.924,12.244a.588.588,0,1,0,.832.832h0L8,8.832l4.244,4.244a.588.588,0,0,0,.832-.832Z" transform="translate(-2.752 -2.752)" fill="#616161"/>
								</svg>
							</a>
							<p>
								<?php echo wp_kses_post( $args['content'] ); ?>
							</p>
							<p class="wlfmc-notice-action-wrapper">
								<?php if ( isset( $args['btn_desc'] ) && '' !== $args['btn_desc'] ) : ?>
									<span class="wlfmc-btn-wrapper">
										<a href="<?php echo esc_url( $args['btn_url'] ); ?>" class="<?php echo esc_attr( $args['btn_class'] ); ?>" target="<?php echo esc_attr( $args['btn_target'] ); ?>">
											<?php echo wp_kses_post( $args['btn_title'] ); ?>
										</a>
										<small><?php echo wp_kses_post( $args['btn_desc'] ); ?></small>
									</span>
								<?php else : ?>
									<a href="<?php echo esc_url( $args['btn_url'] ); ?>" class="<?php echo esc_attr( $args['btn_class'] ); ?>" target="<?php echo esc_attr( $args['btn_target'] ); ?>">
										<?php echo wp_kses_post( $args['btn_title'] ); ?>
									</a>
								<?php endif; ?>
								<?php if ( isset( $args['timer'] ) && '' !== $args['timer'] ) : ?>
									<span class="wlfmc-timer">
										<span id="wlfmc-notice-timer-hours"></span>&nbsp;:&nbsp;<span id="wlfmc-notice-timer-minutes"></span>&nbsp;:&nbsp;<span id="wlfmc-notice-timer-seconds"></span>
									</span>
								<?php endif; ?>
							</p>
				<?php if ( isset( $args['image'] ) && '' !== $args['image'] ) : ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 * Print styles
		 *
		 * @return void
		 */
		public function styles() {
			?>
			<style>
				@font-face {
					font-family: "Montserrat";
					font-weight: 400;
					font-style: normal;
					src: url("<?php echo esc_url( MC_WLFMC_URL ); ?>options/assets/fonts/Montserrat-Regular.woff2") format("woff2"), url("<?php echo esc_url( MC_WLFMC_URL ); ?>options/assets/fonts/Montserrat-Regular.woff") format("woff");
				}

				@font-face {
					font-family: "Montserrat";
					font-weight: 600;
					font-style: normal;
					src: url("<?php echo esc_url( MC_WLFMC_URL ); ?>options/assets/fonts/Montserrat-SemiBold.woff2") format("woff2"), url("<?php echo esc_url( MC_WLFMC_URL ); ?>options/assets/fonts/Montserrat-SemiBold.woff") format("woff");
				}

				.mct-wizard-page .wlfmc-notice.notice-finish-wizard, .mct-wizard-page .wlfmc-notice.notice-skip-wizard {
					display: none !important;
				}

				.wlfmc-notice {
					padding: 20px !important;
					-webkit-border-radius: 8px;
					border-radius: 8px;
					background: #fff;
					border: 1px solid #e4dbd0;
					-webkit-box-shadow: 0 6px 6px rgba(0, 0, 0, 0.08);
					box-shadow: 0 6px 6px rgba(0, 0, 0, 0.08);
					position: relative;
					font-family: Montserrat, "Helvetica Neue", sans-serif;
					font-weight: normal;
				}

				.wlfmc-notice h2 {
					font-size: 24px;
					font-weight: 600;
					margin-top: 0;
				}

				.wlfmc-notice .dismiss-btn {
					font-size: 14px;
					line-height: 21px;
					color: #302a23;
					position: absolute;
					right: 20px;
					top: 20px;
					text-decoration: none;
				}

				.wlfmc-notice p {
					font-size: 13px;
					line-height: 21px;
					color: #302a23;
				}

				.wlfmc-notice .btn-notice , .wlfmc-notice .btn-notice-2{
					border: none !important;
					color: #fff;
					height: 40px;
					line-height: 40px;
					min-width: 40px;
					-webkit-border-radius: 8px;
					border-radius: 8px;
					position: relative;
					font-size: 14px;
					font-weight: normal;
					-webkit-transition: all .3s ease-in-out;
					-o-transition: all .3s ease-in-out;
					transition: all .3s ease-in-out;
					display: inline-block;
					text-decoration: none;
					padding: 0 10px 0 40px;
					cursor: pointer;
					-webkit-appearance: none;
					white-space: nowrap;
					-webkit-box-sizing: border-box;
					box-sizing: border-box;
					vertical-align: top;
					overflow: hidden;
					background-color: white;
					-webkit-box-shadow: 0 2px 4px rgba(0, 0, 0, 0.16);
					box-shadow: 0 2px 4px rgba(0, 0, 0, 0.16);
					z-index: 1;
				}
				.wlfmc-notice .btn-notice .dashicons {
					line-height: 40px;
				}

				.wlfmc-notice .btn-notice:hover {
					color: #fff;
				}

				.wlfmc-notice .btn-notice:focus {
					-webkit-box-shadow: 0 2px 4px rgba(0, 0, 0, 0.26);
					box-shadow: 0 2px 4px rgba(0, 0, 0, 0.26);
				}

				.wlfmc-notice .btn-notice:before {
					font-family: Dashicons;
					font-weight: 400;
					font-variant: normal;
					text-transform: none;
					-webkit-font-smoothing: antialiased;
					margin: 0;
					text-indent: 0;
					position: absolute;
					top: 0;
					left: 10px;
					text-align: center;
					font-size: 19px;
					line-height: inherit;
					content: "\f147";
					color: #ea7a0b;
				}

				.wlfmc-notice .btn-notice:after {
					position: absolute;
					-webkit-transition: .3s;
					-o-transition: .3s;
					transition: .3s;
					content: '';
					width: 100%;
					bottom: 0;
					height: 100%;
					right: -35px;
					-webkit-transform: skewX(15deg);
					-ms-transform: skewX(15deg);
					transform: skewX(15deg);
					z-index: -1;
					background-color: #ea7a0b;
					-webkit-border-top-left-radius: 2px;
					border-top-left-radius: 2px;
				}
				.wlfmc-notice .btn-notice:hover {
					color: #ea7a0b;
				}

				.wlfmc-notice .btn-notice-2 {
					background-color: #FD5D00;
					min-width:140px;
					display: flex;
					justify-content: center;
					align-items: center;
					padding:0 10px;
				}
				.wlfmc-notice .btn-notice-2:hover{
					color: #fff;
				}

				.wlfmc-notice .btn-notice:hover:after {
					right: -120%;
				}

				.wlfmc-notice .btn-notice.green-btn:before,
				.wlfmc-notice .btn-notice.green-btn:hover {
					color: #91ca45 !important;
				}

				.wlfmc-notice .btn-notice.green-btn:after {
					background-color: #91ca45;
				}

				.wlfmc-notice .btn-notice.purple-btn:before,
				.wlfmc-notice .btn-notice.purple-btn:hover {
					color: #a45eff !important;
				}

				.wlfmc-notice .btn-notice.purple-btn:after {
					background-color: #a45eff;
				}

				.wlfmc-notice .btn-notice.blue-btn:before,
				.wlfmc-notice .btn-notice.blue-btn:hover {
					color: #5e94ff !important;
				}

				.wlfmc-notice .btn-notice.blue-btn:after {
					background-color: #5e94ff;
				}

				.wlfmc-notice .btn-notice.inverse-btn {
					border: 1px solid #ea7a0b !important;
					color: #ea7a0b;
					-webkit-box-shadow: none !important;
					box-shadow: none !important;
				}

				.wlfmc-notice .btn-notice.inverse-btn:before {
					color: #fff !important;
				}

				.wlfmc-notice .btn-notice.inverse-btn:after {
					left: -webkit-calc(-100% + 35px);
					left: calc(-100% + 35px);
					right: auto;
					-webkit-transform: skewX(15deg);
					-ms-transform: skewX(15deg);
					transform: skewX(15deg);
				}

				.wlfmc-notice .btn-notice.inverse-btn:hover {
					color: #fff !important;
				}

				.wlfmc-notice .btn-notice.inverse-btn:hover:after {
					right: auto;
					left: 0;
					-webkit-transform: skewX(0deg);
					-ms-transform: skewX(0deg);
					transform: skewX(0deg);
				}

				.wlfmc-notice .btn-notice.inverse-btn.green-btn {
					border-color: #91ca45 !important;
					color: #91ca45 !important;
				}

				.wlfmc-notice .btn-notice.inverse-btn.green-btn:after {
					background-color: #91ca45 !important;
				}

				.wlfmc-notice .btn-notice.inverse-btn.green-btn:hover {
					color: #fff !important;
				}

				.wlfmc-notice .btn-notice.inverse-btn.purple-btn {
					border-color: #a45eff !important;
					color: #a45eff !important;
				}

				.wlfmc-notice .btn-notice.inverse-btn.purple-btn:after {
					background-color: #a45eff !important;
				}

				.wlfmc-notice .btn-notice.inverse-btn.purple-btn:hover {
					color: #fff !important;
				}

				.wlfmc-notice .btn-notice.inverse-btn.blue-btn {
					border-color: #5e94ff !important;
					color: #5e94ff !important;
				}

				.wlfmc-notice .btn-notice.inverse-btn.blue-btn:after {
					background-color: #5e94ff !important;
				}

				.wlfmc-notice .btn-notice.inverse-btn.blue-btn:hover {
					color: #fff !important;
				}

				.wlfmc-notice .green-text {
					color: #91ca45
				}

				.wlfmc-notice .orange-text {
					color: #ea7a0b;
				}

				.wlfmc-notice .info-text {
					color: #ea7a0b;
				}
				.wlfmc-notice .success-text {
					color: #34c240;
				}
				.wlfmc-notice .alert-text {
					color: #fa9f47;
				}
				.wlfmc-notice .error-text {
					color: #d64242;
				}
				.wlfmc-notice-with-image {
					display:flex;
					align-items: center;
					gap:20px;
				}
				.wlfmc-notice-action-wrapper {
					display:flex;
					justify-content: space-between;
					flex-direction: row-reverse;
					gap:10px;
					margin-bottom:0;
				}
				.wlfmc-notice-action-wrapper .wlfmc-btn-wrapper {
					display: flex;
					flex-direction: column;
					justify-content: center;
					align-items: center;
				}
				.wlfmc-timer {
					display: flex;
					align-items: center;
					font-weight: 500;
					font-size: 14px;
					line-height: 21px;
					color: #fd5d00;
				}
				.wlfmc-timer span {
					width: 48px;
					height: 48px;
					border-radius: 8px;
					background: rgba(253, 93, 0, 0.15);
					display: inline-flex;
					align-items: center;
					justify-content: center;
				}
				.wlfmc-content-wrapper {
					width:100%;
				}

				.wlfmc-notice.wlfmc-notice-success , .wlfmc-notice.wlfmc-notice-error , .wlfmc-notice.wlfmc-notice-info, .wlfmc-notice.wlfmc-notice-alert {
					padding-left:70px !important;
					box-shadow: 0 5px 5px rgba(110, 55, 0, 0.1) !important;
				}
				.wlfmc-notice.wlfmc-notice-error {
					border: 1px solid #d64242 !important;
				}
				.wlfmc-notice.wlfmc-notice-success {
					border: 1px solid #34c240 !important;
				}
				.wlfmc-notice.wlfmc-notice-info {
					border: 1px solid #0090e0 !important;
				}
				.wlfmc-notice.wlfmc-notice-alert {
					border: 1px solid #fa9f47  !important;
				}
				.wlfmc-notice-success::before, .wlfmc-notice-error::before,.wlfmc-notice-info::before,.wlfmc-notice-alert::before {
					content: '';
					position: absolute;
					top: 0;
					left: 0;
					width: 132px;
					height: 100%;
					border-radius: 6px;
					opacity: 0.15;
				}
				.wlfmc-notice-success::after, .wlfmc-notice-error::after,.wlfmc-notice-info::after,.wlfmc-notice-alert::after {
					content: '';
					position: absolute;
					top: 0;
					left: 0;
					width: 70px;
					height: 100%;
					background-position: center;
					background-repeat: no-repeat;
				}

				.wlfmc-notice-error::before {
					background: linear-gradient(90deg,#d64242 0%, #fff 100%);
				}
				.wlfmc-notice-success::before {
					background: linear-gradient(90deg,#34c240 0%, #fff 100%);
				}
				.wlfmc-notice-info::before {
					background: linear-gradient(90deg,#0090e0 0%, #fff 100%);
				}
				.wlfmc-notice-alert::before {
					background: linear-gradient(90deg,#fa9f47 0%, #fff 100%);
				}
				.wlfmc-notice-error::after{
					background-image: url("data:image/svg+xml,%3Csvg id='icon-alert' xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Crect id='Rectangle_357' data-name='Rectangle 357' width='32' height='32' fill='none'/%3E%3Cpath id='close-circle-svgrepo-com' d='M16,2A14,14,0,1,0,30,16,14.023,14.023,0,0,0,16,2Zm4.7,17.22a1.056,1.056,0,0,1,0,1.484,1.048,1.048,0,0,1-1.484,0L16,17.484,12.78,20.7a1.048,1.048,0,0,1-1.484,0,1.056,1.056,0,0,1,0-1.484L14.516,16,11.3,12.78A1.049,1.049,0,0,1,12.78,11.3L16,14.516l3.22-3.22A1.049,1.049,0,1,1,20.7,12.78L17.484,16Z' fill='%23d64242'/%3E%3C/svg%3E%0A");
				}
				.wlfmc-notice-success::after{
					background-image: url("data:image/svg+xml,%3Csvg id='icon-alert' xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Crect id='Rectangle_357' data-name='Rectangle 357' width='32' height='32' fill='none'/%3E%3Cpath id='tick-circle-svgrepo-com_1_' data-name='tick-circle-svgrepo-com (1)' d='M16,2A14,14,0,1,0,30,16,14.023,14.023,0,0,0,16,2Zm6.692,10.78-7.938,7.938a1.048,1.048,0,0,1-1.484,0L9.308,16.756a1.049,1.049,0,1,1,1.484-1.484l3.22,3.22,7.2-7.2a1.049,1.049,0,0,1,1.484,1.484Z' fill='%2334c240'/%3E%3C/svg%3E%0A");
				}
				.wlfmc-notice-info::after{
					background-image: url("data:image/svg+xml,%3Csvg id='icon-alert' xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Crect id='Rectangle_357' data-name='Rectangle 357' width='32' height='32' fill='none'/%3E%3Cpath id='Path_1407' data-name='Path 1407' d='M14.6,17.4a1.4,1.4,0,1,0,2.8,0v-7a1.4,1.4,0,0,0-2.8,0Zm2.8,4.184a1.4,1.4,0,0,0-2.8,0V21.6a1.4,1.4,0,0,0,2.8,0ZM9.35,2.543A31.526,31.526,0,0,1,16,2a31.525,31.525,0,0,1,6.65.543,8.913,8.913,0,0,1,4.531,2.276A8.914,8.914,0,0,1,29.457,9.35,31.528,31.528,0,0,1,30,16a31.527,31.527,0,0,1-.543,6.65,8.914,8.914,0,0,1-2.276,4.531,8.914,8.914,0,0,1-4.531,2.276A31.527,31.527,0,0,1,16,30a31.528,31.528,0,0,1-6.65-.543,8.914,8.914,0,0,1-4.531-2.276A8.913,8.913,0,0,1,2.543,22.65,31.525,31.525,0,0,1,2,16a31.526,31.526,0,0,1,.543-6.65A8.913,8.913,0,0,1,4.819,4.819,8.913,8.913,0,0,1,9.35,2.543Z' fill='%230090e0' fill-rule='evenodd'/%3E%3C/svg%3E%0A");
				}
				.wlfmc-notice-alert::after{
					background-image: url("data:image/svg+xml,%3Csvg id='icon-alert' xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Crect id='Rectangle_357' data-name='Rectangle 357' width='32' height='32' fill='none'/%3E%3Cpath id='Path_1406' data-name='Path 1406' d='M14.584,17.421a1.417,1.417,0,1,0,2.833,0V13.079a1.417,1.417,0,1,0-2.833,0Zm2.833,4.326a1.417,1.417,0,1,0-2.833,0v.016a1.417,1.417,0,1,0,2.833,0Zm-5.131-16.4a4.206,4.206,0,0,1,7.429,0l9.35,17.2A4.342,4.342,0,0,1,25.35,29H6.65a4.342,4.342,0,0,1-3.715-6.451Z' transform='translate(0 0)' fill='%23fa9f47' fill-rule='evenodd'/%3E%3C/svg%3E%0A");
				}
				.rtl .wlfmc-notice.wlfmc-notice-success ,.rtl  .wlfmc-notice.wlfmc-notice-error ,.rtl  .wlfmc-notice.wlfmc-notice-info,.rtl  .wlfmc-notice.wlfmc-notice-alert {
					padding-left:20px !important;
					padding-right:70px !important;
				}
				.rtl .wlfmc-notice-success::before,.rtl  .wlfmc-notice-error::before,.rtl .wlfmc-notice-info::before,.rtl .wlfmc-notice-alert::before,
				.rtl .wlfmc-notice-success::after,.rtl  .wlfmc-notice-error::after,.rtl .wlfmc-notice-info::after,.rtl .wlfmc-notice-alert::after {
					right:0;
					left:auto;
				}
				.rtl .wlfmc-notice-error::before {
					background: linear-gradient(-90deg,#d64242 0%, #fff 100%);
				}
				.rtl .wlfmc-notice-success::before {
					background: linear-gradient(-90deg,#34c240 0%, #fff 100%);
				}
				.rtl .wlfmc-notice-info::before {
					background: linear-gradient(-90deg,#0090e0 0%, #fff 100%);
				}
				.rtl .wlfmc-notice-alert::before {
					background: linear-gradient(-90deg,#fa9f47 0%, #fff 100%);
				}

				.rtl .wlfmc-notice .dismiss-btn {
					left: 20px;
					right: auto !important;
				}


				.rtl .wlfmc-notice .btn-notice {
					padding-left: 20px !important;
					padding-right: 55px;
				}


				.rtl .wlfmc-notice .btn-notice:before {
					left: auto;
					right: 10px;
				}

				.rtl .wlfmc-notice .btn-notice:after {
					right: auto;
					left: -40px;
					-webkit-transform: skewX(15deg);
					-ms-transform: skewX(15deg);
					transform: skewX(15deg);
					-webkit-border-top-right-radius: 2px;
					border-top-right-radius: 2px;
					-webkit-border-top-left-radius: 0;
					border-top-left-radius: 0;
				}

				.rtl .wlfmc-notice .btn-notice:hover:after {
					right: auto;
					left: -120%;
				}

				.rtl .wlfmc-notice .btn-notice.inverse-btn:after {
					right: -webkit-calc(-100% + 40px);
					right: calc(-100% + 40px);
					left: auto;
					-webkit-transform: skewX(15deg);
					-ms-transform: skewX(15deg);
					transform: skewX(15deg);
				}

				.rtl .wlfmc-notice .btn-notice.inverse-btn:hover:after {
					left: auto;
					right: 0;
					-webkit-transform: skewX(0deg);
					-ms-transform: skewX(0deg);
					transform: skewX(0deg);
				}
				@media (max-width: 768px) {
					.wlfmc-notice-with-image {
						display: flex;
						align-items: center;
						gap: 10px;
						flex-wrap: wrap;
						justify-content: center;
					}
					.wlfmc-timer {
						display: flex;
						align-items: center;
						justify-content: center;
					}
					.wlfmc-notice-action-wrapper {
						flex-wrap: wrap;
						justify-content: center;
						flex-direction: column-reverse;
					}
				}
			</style>
			<?php
		}

		/**
		 * Get clean url from admin notices
		 *
		 * @return false|string
		 * @since 1.2.4
		 */
		private function clean_url() {
			$params = array(
				'wlfmc-skip-wizard-dismiss',
				'mct-wlfmc_options-wizard-skip',
				'mct-wlfmc_options-wizard-finish',
				'wlfmc-show-tracking-notice',
				'wlfmc-finish-wizard-dismiss',
				'wlfmc-notice-after-3-days-dismiss',
				'wlfmc-notice-after-7-days-dismiss',
				'wlfmc-notice-after-10-days-dismiss',
				'wlfmc-notice-after-15-days-dismiss',
				'wlfmc-notice-after-16-days-dismiss',
				'wlfmc-notice-after-30-days-dismiss',
				'wlfmc-notice-after-35-days-dismiss',
			);

			return remove_query_arg( $params );
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC_Admin_Notice
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}
/**
 * Unique access to instance of WLFMC_Admin_Notice class
 *
 * @return WLFMC_Admin_Notice
 */
function WLFMC_Admin_Notice() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WLFMC_Admin_Notice::get_instance();
}
