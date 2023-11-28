<?php
/**
 * Automation emails class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'WLFMC_Automation_Emails' ) ) {
	/**
	 * WooCommerce Wishlist Automation Emails
	 */
	class WLFMC_Automation_Emails extends WLFMC_Email {
		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Automation_Emails
		 */
		protected static $instance;

		/**
		 * Woocommerce email template footer text
		 *
		 * @var string $email_footer footer text.
		 */
		public $email_footer;

		/**
		 * Constructor method
		 */
		public function __construct() {
			parent::__construct();
			// Cron.
			add_filter( 'wlfmc_wishlist_crons', array( $this, 'cron' ), 10 );

			// Add automation emails.
			add_action( 'wlfmc_load_automations', array( $this, 'add_emails' ), 10, 4 );

			// Send automation email.
			add_action( 'wlfmc_send_automation_mail', array( $this, 'send_email' ), 10, 2 );

			// Check coupon used.
			add_action( 'woocommerce_payment_complete', array( $this, 'check_coupon_used' ), 10 );
			add_action( 'woocommerce_order_status_completed', array( $this, 'check_coupon_used' ), 10 );

			// Track emails.
			add_action( 'parse_request', array( $this, 'track_emails' ), 0 );
			add_filter( 'query_vars', array( $this, 'query_vars' ) );
		}


		/**
		 * Add email tracking query vars.
		 *
		 * @param array $public_query_vars Query vars.
		 *
		 * @return array
		 */
		public function query_vars( array $public_query_vars ): array {
			$public_query_vars[] = 'wlfmc_ae_track_c'; // clicked.
			$public_query_vars[] = 'wlfmc_ae_track_o'; // opened.

			return $public_query_vars;
		}

		/**
		 * Tracking emails.
		 *
		 * @return void
		 */
		public function track_emails() {
			global $wpdb ,$wp;

			if ( array_key_exists( 'wlfmc_ae_track_c', $wp->query_vars ) ) {
				$key = sanitize_text_field( $wp->query_vars['wlfmc_ae_track_c'] );

				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_offers SET status='clicked' WHERE track_code=%s AND status IN ('opened' ,'sent') ", $key ) );// phpcs:ignore WordPress.DB

				$url = html_entity_decode( remove_query_arg( 'wlfmc_ae_track_c' ) );

				header( "location: $url" );
				die;

			}
			if ( array_key_exists( 'wlfmc_ae_track_o', $wp->query_vars ) ) {
				$key = sanitize_text_field( $wp->query_vars['wlfmc_ae_track_o'] );
				$wpdb->update( // phpcs:ignore WordPress.DB
					$wpdb->wlfmc_wishlist_offers,
					array( 'status' => 'opened' ),
					array(
						'track_code' => $key,
						'status'     => 'sent',
					),
					array( '%s' ),
					array( '%s', '%s' )
				);
				header( 'Content-Type: image/png' );
				header( 'Pragma-directive: no-cache' );
				header( 'Cache-directive: no-cache' );
				header( 'Cache-control: no-cache' );
				header( 'Pragma: no-cache' );
				header( 'Expires: 0' );
				header( 'Content-Length: ' . 921 );
				echo base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAFoEvQfAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NUQ4RTg0RkQxQjZBMTFFM0EyMjZEMEI1RDQxQTNEODgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NUQ4RTg0RkUxQjZBMTFFM0EyMjZEMEI1RDQxQTNEODgiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo1RDhFODRGQjFCNkExMUUzQTIyNkQwQjVENDFBM0Q4OCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo1RDhFODRGQzFCNkExMUUzQTIyNkQwQjVENDFBM0Q4OCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmpagdgAAAANSURBVHjaY/7//z8DAAkLAwFJ9B4LAAAAAElFTkSuQmCC' );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
				die;
			}

		}

		/**
		 * Replace email links with tracking params.
		 *
		 * @param string $email_content email content.
		 * @param string $code tracking code.
		 *
		 * @return string
		 */
		private function replace_links( string $email_content, string $code ): string {

			$regex = "/<a.*?href=[\"']?(?!javascript:|#)([^>'\"]+)[\"']?[^>]*?>(.+?)<\\/a>/i";
			preg_match_all( $regex, $email_content, $matches );
			$all_links = $matches[1];
			// Set populateSchema for table link master.
			foreach ( $all_links as $link ) {

				// generate new link.
				$new_link = add_query_arg( 'wlfmc_ae_track_c', $code, $link );

				// Exact match required and these are twice required.
				$email_content = str_replace( "'" . $link . "'", "'" . $new_link . "'", $email_content );
				$email_content = str_replace( '"' . $link . '"', '"' . $new_link . '"', $email_content );
			}

			return stripslashes( $email_content );
		}


		/**
		 * Add campaign cron in wishlist crons.
		 *
		 * @param array $crons Crons.
		 *
		 * @return array
		 */
		public function cron( array $crons ): array {
			$crons['wlfmc_send_automation_emails'] =
				array(
					'schedule' => 'hourly',
					'callback' => array( $this, 'send_emails' ),
				);

			return $crons;
		}

		/**
		 * Send offer emails.
		 */
		public function send_emails() {
			global $wpdb;
			try {
				$automation_id = $wpdb->get_var( "SELECT ID FROM $wpdb->wlfmc_wishlist_automations WHERE is_pro = 0 AND is_active = 1 LIMIT 1" ); // phpcs:ignore WordPress.DB
				if ( ! $automation_id ) {
					return;
				}

				$automation      = new WLFMC_Automation( $automation_id );
				$wlfmc_options   = new MCT_Options( 'wlfmc_options' );
				$execution_limit = apply_filters( 'wlfmc_automation_email_execution_limit', $wlfmc_options->get_option( 'email_per_hours', '20' ) );
				$options         = $automation->get_options();
				$queue           = $automation->get_email_queue( $execution_limit );
				$unsubscribed    = get_option( 'wlfmc_unsubscribed_users', array() );
				if ( ! empty( $queue ) ) {
					foreach ( $queue as $item ) {
						$customer = wlfmc_get_customer( $item->customer_id );
						if ( ! $customer ) {
							$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_offers SET status = 'canceled' WHERE customer_id = %d AND status IN ('sending' ,'not-send')", $item->customer_id ) ); // phpcs:ignore WordPress.DB
							continue;
						}
						if ( $customer->is_unsubscribed() || in_array( $customer->get_email(), $unsubscribed, true ) ) {
							WLFMC_Wishlist_Factory::unsubscribe_customer( $customer );
							continue;
						}

						$is_canceled = true;

						if ( ! empty( $options ) ) {

							$wishlist = wlfmc_get_wishlist( $item->wishlist_id );

							if ( $wishlist ) {

								// check offer email exists and conditions is true or not.
								if ( isset( $options['min_count'] ) && $options['min_count'] <= $wishlist->count_items() && isset( $options['min_total'] ) && $options['min_total'] <= $wishlist->get_total() ) {

									$is_canceled = false;

									// Checked wishlist have one of the  product included or not.
									if ( isset( $options['include_products'] ) && ! empty( $options['include_products'] ) && is_array( $options['include_products'] ) ) {
										$exists = false;
										foreach ( $options['include_products'] as $product_id ) {
											$product_id = wlfmc_object_id( $product_id, 'product', true, 'default' );

											if ( array_key_exists( $product_id, $wishlist->get_items() ) ) {
												$exists = true;
												break;
											}
										}
										if ( false === $exists ) {
											$is_canceled = true;
										}
									}
								}
							}
						}

						if ( true === $is_canceled ) {

							$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_offers SET status = 'canceled' WHERE customer_id = %d AND status IN ('sending' ,'not-send')", $item->customer_id ) ); // phpcs:ignore WordPress.DB

							continue;
						}

						do_action( 'wlfmc_send_automation_mail', $automation, $item );

					}
				}
			} catch ( Exception $e ) {
				return;
			}
		}

		/**
		 * Add queue email to DB
		 *
		 * @param int    $prod_id Product id.
		 * @param int    $wishlist_id Wishlist id.
		 * @param int    $customer_id customer id.
		 * @param string $current_list_type list type.
		 *
		 * @version 1.6.2
		 */
		public function add_emails( $prod_id, $wishlist_id, $customer_id, $current_list_type ) {
			global $wpdb;

			$customer = wlfmc_get_customer( $customer_id );
			if ( ! $customer ) {
				return;
			}

			if ( $customer->is_session_based() && ! $customer->is_email_verified() ) {
				return;
			}

			$user_data = WLFMC_Wishlist_Factory::get_customer_data( $customer );

			if ( ! $user_data ) {
				return;
			}

			$user_meta = $customer->get_customer_meta();
			$user_meta = is_array( $user_meta ) ? $user_meta : array();
			$wishlist  = wlfmc_get_wishlist( $wishlist_id );

			if ( ! $wishlist ) {
				return;
			}

			$automation_id = $wpdb->get_var( "SELECT ID FROM $wpdb->wlfmc_wishlist_automations WHERE is_pro = 0 AND is_active = 1  LIMIT 1" );// phpcs:ignore WordPress.DB
			if ( ! $automation_id ) {
				return;
			}

			$automation         = new WLFMC_Automation( $automation_id );
			$options            = $automation->get_options();
			$min_total          = round( (float) $options['minimum-wishlist-total'], 3 );
			$min_count          = (int) $options['minimum-wishlist-count'];
			$email_type         = $options['mail-type'];
			$include_products   = $options['include-product'];
			$period_days        = (int) $options['period-days'];
			$offer_emails       = $options['offer_emails'];
			$current_time       = strtotime( current_time( 'mysql' ) );
			$last_time_added    = is_array( $user_meta ) ? ( $user_meta['last_period_days'] ?? '' ) : '';
			$exists             = true;
			$can_add_after_date = '' === $last_time_added ? $current_time : strtotime( '+' . $period_days . ' days', $last_time_added );
			$most_days          = 0;
			$need_coupon        = false;
			$coupon_amount      = $options['coupon-amount'];
			// check offer email exists and conditions is true or not.
			if ( ! is_array( $offer_emails ) || empty( $offer_emails ) || $min_count > $wishlist->count_items() || $min_total > $wishlist->get_total() ) {
				return;
			}

			// Checked wishlist have one of the  product included or not.
			if ( is_array( $include_products ) && ! empty( $include_products ) ) {
				$exists = false;
				foreach ( $include_products as $product_id ) {
					$product_id = wlfmc_object_id( $product_id, 'product', true, 'default' );
					if ( $product_id === (int) $prod_id ) {
						$exists = true;
						break;
					}
				}
			}
			if ( false === $exists || $can_add_after_date > $current_time ) {
				return;
			}

			foreach ( $offer_emails as $email_option ) {
				if ( isset( $email_option['enable_email'] ) && ( wlfmc_is_true( $email_option['enable_email'] ) ) &&
					( intval( $email_option['send_after_days'] ) > 0 ) &&
					( ( 'plain' === $email_type && wlfmc_str_contains( $email_option['text_content'], '{coupon_code}' ) ) ||
					( in_array(
						$email_type,
						array(
							'html',
							'simple-template',
							'mc-template',
						),
						true
					) && wlfmc_str_contains( $email_option['html_content'], '{coupon_code}' ) ) )
				) {
					$most_days = max( intval( $email_option['send_after_days'] ), $most_days );
				}
				if ( ( 'plain' === $email_type && wlfmc_str_contains( $email_option['text_content'], '{coupon_code}' ) ) || ( in_array(
					$email_type,
					array(
						'html',
						'simple-template',
						'mc-template',
					),
					true
				) && wlfmc_str_contains( $email_option['html_content'], '{coupon_code}' ) ) ) {
					$need_coupon = true;
				}
			}
			if ( ! $coupon_amount || ! floatval( $coupon_amount ) > 0 ) {
				$need_coupon = false;
			}

			$current_user_email = $user_data['user_email'];
			$expires_after_days = intval( $options['expiry-date'] ) + $most_days;
			$date_expires       = strtotime( '+' . $expires_after_days . ' days', $current_time );
			$coupon_args        = array(
				'code'                 => $this->generate_coupon_code(),
				'discount_type'        => $options['discount-type'],
				'amount'               => $coupon_amount,
				'date_expires'         => $date_expires,
				'individual_use'       => wlfmc_is_true( $options['individual-use'] ),
				'exclude_sale_items'   => wlfmc_is_true( $options['exclude-sale-items'] ),
				'free_shipping'        => wlfmc_is_true( $options['free-shipping'] ),
				'email_restrictions'   => wlfmc_is_true( $options['user-restriction'] ) ? $current_user_email : '',
				'delete_after_expired' => wlfmc_is_true( $options['delete-after-expired'] ) ? 'yes' : 'no',
			);
			$coupon_id          = $need_coupon ? $this->add_coupon( $coupon_args ) : null;
			foreach ( $offer_emails as $k => $email_option ) {
				if ( isset( $email_option['enable_email'] ) && wlfmc_is_true( $email_option['enable_email'] ) ) {
					$days       = intval( $email_option['send_after_days'] );
					$datesend   = $days > 0 ? strtotime( '+' . $days . ' days', $current_time ) : $current_time;
					$has_coupon = ( ( 'plain' === $email_type && wlfmc_str_contains( $email_option['text_content'], '{coupon_code}' ) ) || ( in_array(
						$email_type,
						array(
							'html',
							'simple-template',
							'mc-template',
						),
						true
					) && wlfmc_str_contains( $email_option['html_content'], '{coupon_code}' ) ) ) ? 1 : 0;
					$this->insert_email(
						array(
							'customer_id'   => $customer_id,
							'wishlist_id'   => $wishlist_id,
							'automation_id' => $automation_id,
							'has_coupon'    => $has_coupon,
							'coupon_id'     => $has_coupon ? $coupon_id : null,
							'product_id'    => $prod_id,
							'days'          => $days,
							'track_code'    => md5( microtime() . wp_rand() ),
							'email_key'     => $k,
							'email_options' => array(
								'mail_type'    => $email_type,
								'mail_heading' => $email_option['mail_heading'],
								'mail_subject' => $email_option['mail_subject'],
								'mail_content' => ( 'plain' === $email_type ) ? $email_option['text_content'] : $email_option['html_content'],
								'mail_footer'  => ( 'plain' === $email_type ) ? $email_option['text_footer'] : $email_option['html_footer'],
							),
							'datesend'      => gmdate( 'Y-m-d H:i:s', $datesend ),
						)
					);
				}
			}
			$user_meta['last_period_days'] = $current_time;
			$customer->set_customer_meta( $user_meta );
			$customer->save();
		}

		/**
		 * Send automation email
		 *
		 * @param WLFMC_Automation $automation Automation.
		 * @param object           $email_row Email data.
		 *
		 * @throws Exception When sending email.
		 * @version 1.6.2
		 */
		public function send_email( $automation, $email_row ) {

			$user = WLFMC_Wishlist_Factory::get_customer_data( intval( $email_row->customer_id ) );
			if ( ! $user ) {
				return;
			}
			$mailer                 = WC()->mailer();
			$to                     = $user['user_email'];
			$email_options          = maybe_unserialize( $email_row->email_options );
			$email_content          = $email_options['mail_content'];
			$email_footer           = $email_options['mail_footer'] ?? '';
			$coupon_code            = '';
			$coupon_amount          = '';
			$coupon_expiry_date     = '';
			$options                = $automation->get_options();
			$mc_options             = array();
			$item_template          = 'email-list-items.php';
			$email_socials          = isset( $options['email-template-socials'] ) ? wp_unslash( $options['email-template-socials'] ) : array();// phpcs:ignore
			$social_shape           = isset( $options['email-template-social-shape'] ) ? sanitize_text_field( wp_unslash( $options['email-template-social-shape'] ) ) : 'default';
			$social_size            = isset( $options['email-template-social-size'] ) && 0 < absint( $options['email-template-social-size'] ) ? absint( wp_unslash( $options['email-template-social-size'] ) ) : 32;
			$social_color           = isset( $options['email-template-social-color'] ) ? sanitize_text_field( wp_unslash( $options['email-template-social-color'] ) ) : 'color';
			$social_link_in_new_tab = isset( $options['email-template-social-open-in-new-tab'] ) ? absint( wp_unslash( $options['email-template-social-open-in-new-tab'] ) ) : 0;
			$socials                = array();
			if ( ! empty( $email_socials ) ) {
				foreach ( $email_socials as $social ) {
					if ( '' !== esc_url( $social['social-url'] ) ) {
						$socials[ $social['social-name'] ] = array(
							'url'   => esc_url( $social['social-url'] ),
							'image' => MC_WLFMC_URL . 'assets/frontend/images/social/' . $social_shape . '/' . $social_color . '/' . $social['social-name'] . '.png',
						);
					}
				}
			} else {
				if ( isset( $options['email-template-facebook'] ) ) {
					$socials['facebook'] = array(
						'url'   => esc_url( $options['email-template-facebook'] ),
						'image' => MC_WLFMC_URL . 'assets/frontend/images/social/' . $social_shape . '/' . $social_color . '/facebook.png',
					);
				}
				if ( isset( $options['email-template-linkedin'] ) ) {
					$socials['linkedin'] = array(
						'url'   => esc_url( $options['email-template-linkedin'] ),
						'image' => MC_WLFMC_URL . 'assets/frontend/images/social/' . $social_shape . '/' . $social_color . '/linkedin.png',
					);
				}
				if ( isset( $options['email-template-instagram'] ) ) {
					$socials['instagram'] = array(
						'url'   => esc_url( $options['email-template-instagram'] ),
						'image' => MC_WLFMC_URL . 'assets/frontend/images/social/' . $social_shape . '/' . $social_color . '/instagram.png',
					);
				}
			}
			switch ( $email_options['mail_type'] ) {
				case 'mc-template':
					$content_type = 'text/html';
					$template     = 'mc-template.php';
					add_filter(
						'woocommerce_email_styles',
						function() {
							ob_start();
							wlfmc_get_template( 'emails/mc-styles.php' );
							return ob_get_clean();
						}
					);
					$logo       = wp_get_attachment_image_src( $options['email-template-logo'] );
					$avatar     = wp_get_attachment_image_src( $options['email-template-avatar'] );
					$mc_options = array(
						'logo'                   => $logo ? $logo : '',
						'avatar'                 => $avatar ? $avatar : '',
						'customer-name'          => $options['email-template-customer-name'],
						'customer-job'           => $automation['email-template-customer-job'],
						'social-size'            => $social_size,
						'social-link-in-new-tab' => $social_link_in_new_tab,
						'socials'                => $socials,
					);

					break;
				case 'simple-template':
					add_filter( 'woocommerce_email_styles', '__return_false' );
					$content_type = 'text/html';
					$template     = 'simple-template.php';

					break;
				case 'html':
					$content_type = 'text/html';
					$template     = 'offer.php';

					break;
				case 'plain':
				default:
					$content_type  = 'text/plain';
					$template      = 'plain/offer.php';
					$item_template = 'plain/email-list-items.php';
					break;
			}
			$headers       = "Content-Type: $content_type\r\n";
			$email_content = $this->prepare_content( $email_content, $email_options['mail_type'] );
			$email_footer  = $this->prepare_content( $email_footer, $email_options['mail_type'] );

			if ( 'plain' !== $email_options['mail_type'] ) {
				$email_content .= '<br><img alt="" src="' . esc_url( add_query_arg( 'wlfmc_ae_track_o', $email_row->track_code, home_url() ) ) . '" width="1" height="1"/>';
			}

			if ( wlfmc_is_true( $email_row->has_coupon ) ) {
				$coupon_object = new WC_Coupon( $email_row->coupon_id );
				$discounts     = new WC_Discounts();
				// check coupon valid.
				if ( $discounts->is_coupon_valid( $coupon_object ) && wlfmc_str_contains( $email_content, '{coupon_code}' ) ) {
					$coupon_code        = $coupon_object->get_code();
					$coupon_amount      = $coupon_object->get_amount();
					$coupon_expiry_date = $coupon_object->get_date_expires();
				}
			}
			$wishlist       = WLFMC_Wishlist_Factory::get_wishlist( $email_row->wishlist_id );
			$wishlist_url   = $wishlist ? $wishlist->get_share_url() : '';
			$shop_url       = get_permalink( wc_get_page_id( 'shop' ) );
			$checkout_url   = add_query_arg(
				array(
					'add_all_to_cart' => 'true',
					'wishlist_id'     => $email_row->wishlist_id,
				),
				$wishlist_url
			);
			$wishlist_items = '';
			if ( wlfmc_str_contains( $email_content, '{wishlist_items}' ) ) {
				$items          = $wishlist && $wishlist->has_items() ? $wishlist->get_items() : array();
				$wishlist_items = wlfmc_get_template(
					'emails/' . $item_template,
					array(
						'items' => $items,
					),
					true
				);
			}
			if ( $coupon_expiry_date ) {
				$coupon_expiry_date = is_int( $coupon_expiry_date ) ? date_i18n( apply_filters( 'wlfmc_coupon_expiry_date_format', 'F j, Y, h:i A' ), $coupon_expiry_date ) : $coupon_expiry_date->date_i18n( apply_filters( 'wlfmc_coupon_expiry_date_format', 'F j, Y, h:i A' ) );
			}
			$placeholders = array(
				'{username}'         => $user['user_name'],
				'{user_name}'        => $user['user_name'],
				'{user_email}'       => $user['user_email'],
				'{user_first_name}'  => $user['first_name'],
				'{user_last_name}'   => $user['last_name'],
				'{coupon_code}'      => $coupon_code,
				'{coupon_amount}'    => $coupon_amount,
				'{expiry_date}'      => $coupon_expiry_date,
				'{shop_url}'         => esc_url( $shop_url ),
				'{checkout_url}'     => esc_url( $checkout_url ),
				'{wishlist_url}'     => esc_url( $wishlist_url ),
				'{wishlist_items}'   => $wishlist_items,
				'{site_name}'        => get_bloginfo( 'name' ),
				'{site_description}' => get_bloginfo( 'description' ),
				'{unsubscribe_url}'  => WLFMC()->get_unsubscribe_url( $email_row->customer_id ),
			);

			$email_content = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_content );
			$email_content = $this->replace_links( $email_content, $email_row->track_code );

			$email_footer = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_footer );
			$email_footer = $this->replace_links( $email_footer, $email_row->track_code );

			unset( $placeholders['{shop_url}'] );
			unset( $placeholders['{checkout_url}'] );
			unset( $placeholders['{wishlist_url}'] );
			unset( $placeholders['{unsubscribe_url}'] );

			$email_heading = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_options['mail_heading'] );
			$email_subject = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_options['mail_subject'] );

			$this->email_footer = $email_footer;

			add_filter( 'woocommerce_email_footer_text', array( $this, 'add_footer' ) );

			$message = wlfmc_get_template(
				'emails/' . $template,
				array(
					'email'         => $mailer,
					'email_heading' => apply_filters( 'wlfmc_automation_email_heading', $email_heading ),
					'email_content' => apply_filters( 'wlfmc_automation_email_content', $email_content ),
					'email_footer'  => apply_filters( 'wlfmc_automation_email_footer', $email_footer ),
					'mc_options'    => $mc_options,
					'wishlist_url'  => $wishlist_url,
				),
				true
			);

			remove_filter( 'woocommerce_email_footer_text', array( $this, 'add_footer' ) );

			add_filter( 'woocommerce_email_from_name', array( $automation, 'get_from_name' ), 10 );
			add_filter( 'woocommerce_email_from_address', array( $automation, 'get_from_address' ), 10 );
			add_filter( 'woocommerce_email_content_type', array( $automation, 'get_content_type' ), 10 );

			$send_state = $mailer->send( $to, apply_filters( 'wlfmc_automation_email_subject', $email_subject ), $message, $headers, '' );

			remove_filter( 'woocommerce_email_from_name', array( $automation, 'get_from_name' ), 10 );
			remove_filter( 'woocommerce_email_from_address', array( $automation, 'get_from_address' ), 10 );
			remove_filter( 'woocommerce_email_content_type', array( $automation, 'get_content_type' ), 10 );

			if ( $send_state ) {
				$this->set_sent( $email_row->ID );
			} else {
				$this->set_notsent( $email_row->ID );
			}

		}


		/**
		 * Woocommerce Email footer fixed
		 *
		 * @return string
		 */
		public function add_footer(): string {
			return $this->email_footer;
		}

		/**
		 * Insert Email to DB
		 *
		 * @param array $args argument for add email.
		 *
		 * @return int
		 * @version 1.6.2
		 */
		public function insert_email( $args ): int {
			global $wpdb;

			$wpdb->insert( // phpcs:ignore WordPress.DB
				$wpdb->wlfmc_wishlist_offers,
				array(
					'customer_id'   => $args['customer_id'],
					'wishlist_id'   => $args['wishlist_id'],
					'automation_id' => $args['automation_id'],
					'has_coupon'    => $args['has_coupon'],
					'coupon_id'     => $args['coupon_id'],
					'product_id'    => $args['product_id'],
					'email_options' => serialize( $args['email_options'] ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
					'datesend'      => $args['datesend'],
					'days'          => $args['days'],
					'email_key'     => $args['email_key'],
					'status'        => 'sending',
					'track_code'    => $args['track_code'],
				),
				array(
					'%d',
					'%d',
					'%d',
					'%d',
					'%d',
					'%d',
					'%s',
					'%s',
					'%d',
					'%d',
					'%s',
					'%s',
				)
			);

			return $wpdb->insert_id;

		}

		/**
		 * Generate Random coupon code
		 *
		 * @return string
		 */
		public function generate_coupon_code(): string {
			global $wpdb;

			$sql = "SELECT COUNT(*) as count FROM $wpdb->posts WHERE `post_title` = %s AND `post_status` = 'publish' AND  `post_type` = 'shop_coupon'";

			do {
				$dictionary = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
				$nchars     = 8;
				$code       = '';

				for ( $i = 0; $i <= $nchars - 1; $i ++ ) {
					$code .= $dictionary[ wp_rand( 0, strlen( $dictionary ) - 1 ) ];
				}
				$count = $wpdb->get_var( $wpdb->prepare( $sql, $code ) ); // phpcs:disable WordPress.DB
			} while ( $count );

			return $code;
		}

		/**
		 * Add new coupon code
		 *
		 * @param array $args argument for create coupon.
		 *
		 * @return int
		 * @version 1.4.0
		 */
		public function add_coupon( $args ): int {

			$args = wp_parse_args(
				$args,
				array(
					'code'                        => '',
					'discount_type'               => 'fixed_cart',
					'amount'                      => '',
					'date_expires'                => null,
					'free_shipping'               => false,
					'email_restrictions'          => '',
					'usage_limit'                 => 1,
					'usage_limit_per_user'        => 1,
					'delete_after_expired'        => 'no',
					'individual_use'              => false,
					'product_ids'                 => array(),
					'excluded_product_ids'        => array(),
					'product_categories'          => array(),
					'excluded_product_categories' => array(),
					'exclude_sale_items'          => false,
					'minimum_amount'              => '',
					'maximum_amount'              => '',
				)
			);
			$args = apply_filters( 'wlfmc_add_coupon_offer_args', $args );

			$coupon = new WC_Coupon();

			$coupon->set_code( $args['code'] );

			// the coupon discount type can be 'fixed_cart', 'percent' or 'fixed_product', defaults to 'fixed_cart'.
			$coupon->set_discount_type( $args['discount_type'] );

			// the discount amount, defaults to zero.
			$coupon->set_amount( $args['amount'] );

			// the coupon's expiration date defaults to null.
			$coupon->set_date_expires( $args['date_expires'] );

			// determines if the coupon can only be used by an individual, defaults to false.
			$coupon->set_individual_use( $args['individual_use'] );

			// the individual products that the discount will apply to, default to an empty array.
			$coupon->set_product_ids( $args['product_ids'] );

			// the individual products that are excluded from the discount, default to an empty array.
			$coupon->set_excluded_product_ids( $args['excluded_product_ids'] );

			// the times the coupon can be used, defaults to zero.
			$coupon->set_usage_limit( $args['usage_limit'] );

			// the times the coupon can be used per user, defaults to zero.
			$coupon->set_usage_limit_per_user( $args['usage_limit_per_user'] );

			// whether the coupon awards free shipping, defaults to false.
			$coupon->set_free_shipping( $args['free_shipping'] );

			// the product categories included in the promotion, defaults to an empty array.
			$coupon->set_product_categories( $args['product_categories'] );

			// the product categories excluded from the promotion, defaults to an empty array.
			$coupon->set_excluded_product_categories( $args['excluded_product_categories'] );

			// whether sale items are excluded from the coupon, defaults to false.
			$coupon->set_exclude_sale_items( $args['exclude_sale_items'] );

			// the minimum amount of spend required to make the coupon active, defaults to an empty string.
			$coupon->set_minimum_amount( $args['minimum_amount'] );

			// the maximum amount of spend required to make the coupon active, defaults to an empty string.
			$coupon->set_maximum_amount( $args['maximum_amount'] );

			// a list of email addresses, the coupon will only be applied if the customer is linked to one of the listed emails, defaults to an empty array.
			$coupon->set_email_restrictions( $args['email_restrictions'] );

			// add custom meta for remove coupon after expired.
			if ( 'yes' === $args['delete_after_expired'] ) {
				$coupon->update_meta_data( 'delete_after_expired', 'yes' );
			}

			// add custom meta for tracking in analytics.
			$coupon->update_meta_data( 'wlfmc_analytics', 'automation' );

			// save the coupon.
			$coupon->save();

			return $coupon->get_id();

		}


		/**
		 * Set email offer to Sent
		 *
		 * @param int $id email row id.
		 */
		public function set_sent( $id ) {
			global $wpdb;

			$wpdb->update( // phpcs:disable WordPress.DB
				$wpdb->wlfmc_wishlist_offers,
				array(
					'status'   => 'sent',
					'datesent' => gmdate( 'y-m-d H:i:s' ),
				),
				array( 'ID' => $id ),
				array( '%s', '%s' ),
				array( '%d' )
			);
		}

		/**
		 * Set email offer to NotSent
		 *
		 * @param int $id email row id.
		 */
		public function set_notsent( $id ) {
			global $wpdb;

			$wpdb->update( // phpcs:disable WordPress.DB
				$wpdb->wlfmc_wishlist_offers,
				array( 'status' => 'not-send' ),
				array( 'ID' => $id ),
				array( '%s' ),
				array( '%d' )
			);
		}


		/**
		 * Check coupon used in orders and remove offers if coupon used.
		 *
		 * @param int $order_id Order ID.
		 */
		public function check_coupon_used( int $order_id ) {

			global $wpdb;
			$order        = wc_get_order( $order_id );
			$coupon_codes = $order->get_coupon_codes();

			$coupon_ids = is_array( $coupon_codes ) && ! empty( $coupon_codes ) ? array_map( 'wc_get_coupon_id_by_code', $coupon_codes ) : array();

			if ( ! empty( $coupon_ids ) ) {
				// phpcs:disable WordPress.DB
				$coupon_ids_sql = implode( ', ', array_filter( $coupon_ids, 'esc_sql' ) );
				if ( ! empty( $coupon_ids_sql ) ) {
					$wpdb->query( "DELETE FROM $wpdb->wlfmc_wishlist_offers WHERE status in ('sending' , 'not-send') AND has_coupon = 1 AND coupon_id IN( " . $coupon_ids_sql . ' )  ' );

					$row_id = $wpdb->get_var( "SELECT ID FROM $wpdb->wlfmc_wishlist_offers WHERE status = 'clicked' AND has_coupon = 1 AND coupon_id IN( " . $coupon_ids_sql . ' ) ORDER BY days DESC LIMIT 1' );

					if ( ! $row_id ) {
						$row_id = $wpdb->get_var( "SELECT ID FROM $wpdb->wlfmc_wishlist_offers WHERE status = 'opened' AND has_coupon = 1 AND coupon_id IN( " . $coupon_ids_sql . ' ) ORDER BY days DESC LIMIT 1' );

					}
					if ( ! $row_id ) {
						$row_id = $wpdb->get_var( "SELECT ID FROM $wpdb->wlfmc_wishlist_offers WHERE status = 'sent' AND has_coupon = 1 AND coupon_id IN( " . $coupon_ids_sql . ' ) ORDER BY days DESC LIMIT 1' );

					}
					if ( $row_id ) {
						$wpdb->update(
							$wpdb->wlfmc_wishlist_offers,
							array(
								'status' => 'coupon-used',
								'net'    => $order->get_total(),
							),
							array( 'ID' => $row_id )
						);
					}
				}
				// phpcs:enable WordPress.DB
			}

		}


		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC_Automation_Emails
		 */
		public static function get_instance(): WLFMC_Automation_Emails {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

	}
}
/**
 * Unique access to instance of WLFMC_Automation_Emails class
 *
 * @return WLFMC_Automation_Emails_Premium|WLFMC_Automation_Emails
 */
function WLFMC_Automation_Emails() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid

	if ( defined( 'MC_WLFMC_PREMIUM' ) ) {

		return WLFMC_Automation_Emails_Premium::get_instance();

	} else {

		return WLFMC_Automation_Emails::get_instance();

	}

}
