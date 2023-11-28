<?php
/**
 * Static class that will handle all ajax calls for the list
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WLFMC_Automation_Ajax_Handler' ) ) {
	/**
	 * Woocommerce Smart Wishlist Ajax Handler
	 */
	class WLFMC_Automation_Ajax_Handler {

		/**
		 * Woocommerce email template footer text
		 *
		 * @var string $email_footer footer text.
		 */
		public static $email_footer;

		/**
		 * Performs all required add_actions to handle forms
		 *
		 * @return void
		 */
		public static function init() {
			add_action(
				'wp_ajax_wlfmc_reset_sending_cycles_automation',
				array(
					'WLFMC_Automation_Ajax_Handler',
					'reset_sending_cycles',
				)
			);
			add_action( 'wp_ajax_wlfmc_save_automation', array( 'WLFMC_Automation_Ajax_Handler', 'save_automation' ) );
			add_action( 'wp_ajax_wlfmc_new_automation', array( 'WLFMC_Automation_Ajax_Handler', 'new_automation' ) );
			add_action(
				'wp_ajax_wlfmc_send_offer_email_test',
				array(
					'WLFMC_Automation_Ajax_Handler',
					'test_callback',
				)
			);

			add_action(
				'wp_ajax_wlfmc_preview_automation_template',
				array(
					'WLFMC_Automation_Ajax_Handler',
					'preview_template',
				)
			);

			// preview email.
			add_action( 'admin_init', array( 'WLFMC_Automation_Ajax_Handler', 'preview_email' ) );
		}

		/**
		 * Reset sending cycles
		 *
		 * @return void
		 */
		public function reset_sending_cycles() {
			global $wpdb;

			check_ajax_referer( 'ajax-nonce', 'key' );

			if ( ! apply_filters( 'wlfmc_can_reset_sending_cycle', true ) ) {
				wp_send_json(
					array(
						'message' => __( 'You cannot access to reset sending cycle', 'wc-wlfmc-wishlist' ),
						'success' => false,
					)
				);
			}

			$updated = $wpdb->update( $wpdb->wlfmc_wishlist_customers, array( 'customer_meta' => null ), array() );// phpcs:ignore WordPress.DB

			if ( ! is_wp_error( $updated ) ) {

				wp_cache_flush_group( 'wlfmc-customers' );

				echo wp_json_encode(
					array(
						'message' => __( 'All sending cycles reset.', 'wc-wlfmc-wishlist' ),
						'success' => true,
					)
				);
			} else {
				echo wp_json_encode(
					array(
						'message' => __( 'Problem on reset sending cycle, try again later', 'wc-wlfmc-wishlist' ),
						'success' => false,
					)
				);
			}

			exit;
		}

		/**
		 * Send offer email for testing
		 */
		public static function test_callback() {
			check_ajax_referer( 'ajax-nonce', 'key' );

			if ( ! apply_filters( 'wlfmc_can_send_test_mail', true ) ) {
				wp_send_json(
					array(
						'message' => __( 'You cannot access to send test mail', 'wc-wlfmc-wishlist' ),
						'success' => false,
					)
				);
			}

			$id            = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : '';
			$automation_id = isset( $_POST['automation_id'] ) ? absint( $_POST['automation_id'] ) : '';

			if ( '' === $automation_id || '' === $id ) {
				exit;
			}

			$automation = new WLFMC_Automation( $automation_id );

			$options = $automation->get_options();

			$email_type = $options['mail-type'] ?? '';
			$offers     = $options['offer_emails'] ?? '';

			if ( ! $offers || ! isset( $offers[ $id ] ) ) {

				echo wp_json_encode(
					array(
						'message' => __( 'Save the email settings first, then try again', 'wc-wlfmc-wishlist' ),
						'success' => false,
					)
				);
				exit;
			}

			$mailer                 = WC()->mailer();
			$wlfmc_email            = new WLFMC_Email();
			$user                   = get_userdata( get_current_user_id() );
			$to                     = $user->user_email;
			$email_options          = $offers[ $id ];
			$email_content          = '';
			$email_footer           = '';
			$content_type           = '';
			$mc_options             = array();
			$coupon_amount          = $options['coupon-amount'];
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
			$template = '';

			switch ( $email_type ) {
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
					$email_footer  = $email_options['html_footer'] ?? '';
					$email_content = ( '' === $email_options['html_content'] ) ? $automation::get_default_content( 'html', $id ) : $email_options['html_content'];
					$logo          = wp_get_attachment_image_src( $options['email-template-logo'] );
					$avatar        = wp_get_attachment_image_src( $options['email-template-avatar'] );

					$mc_options = array(
						'logo'                   => $logo ? $logo : '',
						'avatar'                 => $avatar ? $avatar : '',
						'customer-name'          => $options['email-template-customer-name'],
						'customer-job'           => $options['email-template-customer-job'],
						'social-size'            => $social_size,
						'social-link-in-new-tab' => $social_link_in_new_tab,
						'socials'                => $socials,
					);
					break;
				case 'simple-template':
					add_filter( 'woocommerce_email_styles', '__return_false' );
					$content_type  = 'text/html';
					$template      = 'simple-template.php';
					$email_content = ( '' === $email_options['text_content'] ) ? $automation::get_default_content( 'html', $id ) : $email_options['html_content'];
					$email_footer  = $email_options['html_footer'] ?? '';
					break;
				case 'html':
					$content_type  = 'text/html';
					$template      = 'offer.php';
					$email_content = ( '' === $email_options['text_content'] ) ? $automation::get_default_content( 'html', $id ) : $email_options['html_content'];
					$email_footer  = $email_options['html_footer'] ?? '';
					break;
				case 'plain':
					$content_type  = 'text/plain';
					$template      = 'plain/offer.php';
					$item_template = 'plain/email-list-items.php';
					$email_content = ( '' === $email_options['text_content'] ) ? $automation::get_default_content( 'plain', $id ) : $email_options['text_content'];
					$email_footer  = $email_options['text_footer'] ?? '';
					break;
			}

			$wishlist       = WLFMC_Wishlist_Factory::get_default_wishlist();
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
			$email_content = $wlfmc_email->prepare_content( $email_content, $email_type );
			$email_footer  = $wlfmc_email->prepare_content( $email_footer, $email_type );

			$headers = "Content-Type: $content_type\r\n";

			$placeholders  = array(
				'{username}'         => $user->user_login,
				'{user_name}'        => $user->user_login,
				'{user_email}'       => $user->user_email,
				'{user_first_name}'  => $user->first_name,
				'{user_last_name}'   => $user->last_name,
				'{site_name}'        => get_bloginfo( 'name' ),
				'{coupon_amount}'    => $coupon_amount,
				'{site_description}' => get_bloginfo( 'description' ),
				'{wishlist_items}'   => $wishlist_items,
			);
			$email_content = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_content );
			$email_heading = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_options['mail_heading'] );
			$email_subject = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_options['mail_subject'] );
			$email_footer  = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_footer );

			self::$email_footer = $email_footer;

			add_filter( 'woocommerce_email_footer_text', array( 'WLFMC_Automation_Ajax_Handler', 'add_footer' ) );
			$message = wlfmc_get_template(
				'emails/' . $template,
				array(
					'email'         => $mailer,
					'email_heading' => apply_filters( 'wlfmc_automation_email_heading', $email_heading ),
					'email_content' => apply_filters( 'wlfmc_automation_email_content', $email_content ),
					'email_footer'  => apply_filters( 'wlfmc_automation_email_footer', $email_footer ),
					'mc_options'    => $mc_options,
					'wishlist_url'  => '#',
				),
				true
			);
			remove_filter( 'woocommerce_email_footer_text', array( 'WLFMC_Automation_Ajax_Handler', 'add_footer' ) );

			add_filter( 'woocommerce_email_from_name', array( $automation, 'get_from_name' ), 10 );
			add_filter( 'woocommerce_email_from_address', array( $automation, 'get_from_address' ), 10 );
			add_filter( 'woocommerce_email_content_type', array( $automation, 'get_content_type' ), 10 );

			$send_state = $mailer->send( $to, apply_filters( 'wlfmc_automation_email_subject_test', $email_subject ), $message, $headers, '' );

			remove_filter( 'woocommerce_email_from_name', array( $automation, 'get_from_name' ), 10 );
			remove_filter( 'woocommerce_email_from_address', array( $automation, 'get_from_address' ), 10 );
			remove_filter( 'woocommerce_email_content_type', array( $automation, 'get_content_type' ), 10 );

			if ( $send_state ) {
				echo wp_json_encode(
					array(
						/* translators: %s: email address */
						'message' => sprintf( __( 'Email sent to %s.', 'wc-wlfmc-wishlist' ), $to ),
						'success' => true,
					)
				);
			} else {
				echo wp_json_encode(
					array(
						'message' => __( 'There was a problem sending the email.', 'wc-wlfmc-wishlist' ),
						'success' => false,
					)
				);
			}

			exit;
		}

		/**
		 * Preview template
		 *
		 * @return void
		 */
		public static function preview_template() {
			check_ajax_referer( 'ajax-nonce', 'key' );
			$email_type             = isset( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : '';
			$email_heading          = isset( $_POST['heading'] ) ? sanitize_text_field( wp_unslash( $_POST['heading'] ) ) : '';
			$email_content          = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';
			$email_footer           = isset( $_POST['footer'] ) ? wp_kses_post( wp_unslash( $_POST['footer'] ) ) : '';
			$email_logo             = isset( $_POST['email-template-logo'] ) ? sanitize_text_field( wp_unslash( $_POST['email-template-logo'] ) ) : '';
			$email_avatar           = isset( $_POST['email-template-avatar'] ) ? sanitize_text_field( wp_unslash( $_POST['email-template-avatar'] ) ) : '';
			$email_customer_name    = isset( $_POST['email-template-customer-name'] ) ? sanitize_text_field( wp_unslash( $_POST['email-template-customer-name'] ) ) : '';
			$email_customer_job     = isset( $_POST['email-template-customer-job'] ) ? sanitize_text_field( wp_unslash( $_POST['email-template-customer-job'] ) ) : '';
			$email_socials          = isset( $_POST['email-template-socials'] ) ? wp_unslash( $_POST['email-template-socials'] ) : array();// phpcs:ignore
			$social_shape           = isset( $_POST['email-template-social-shape'] ) ? sanitize_text_field( wp_unslash( $_POST['email-template-social-shape'] ) ) : 'default';
			$social_size            = isset( $_POST['email-template-social-size'] ) && 0 < absint( $_POST['email-template-social-size'] ) ? absint( wp_unslash( $_POST['email-template-social-size'] ) ) : 32;
			$social_color           = isset( $_POST['email-template-social-color'] ) ? sanitize_text_field( wp_unslash( $_POST['email-template-social-color'] ) ) : 'color';
			$social_link_in_new_tab = isset( $_POST['email-template-social-open-in-new-tab'] ) ? absint( wp_unslash( $_POST['email-template-social-open-in-new-tab'] ) ) : 0;
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
			}

			$mailer        = WC()->mailer();
			$wlfmc_email   = new WLFMC_Email();
			$user          = get_userdata( get_current_user_id() );
			$mc_options    = array();
			$template      = '';
			$item_template = 'email-list-items.php';
			switch ( $email_type ) {
				case 'mc-template':
					$template = 'mc-template.php';
					$logo     = wp_get_attachment_image_src( $email_logo );
					$avatar   = wp_get_attachment_image_src( $email_avatar );
					add_filter(
						'woocommerce_email_styles',
						function() {
							ob_start();
							wlfmc_get_template( 'emails/mc-styles.php' );
							return ob_get_clean();
						}
					);
					$mc_options = array(
						'logo'                   => $logo ? $logo : '',
						'avatar'                 => $avatar ? $avatar : '',
						'customer-name'          => $email_customer_name,
						'customer-job'           => $email_customer_job,
						'social-size'            => $social_size,
						'social-link-in-new-tab' => $social_link_in_new_tab,
						'socials'                => $socials,
					);
					break;
				case 'simple-template':
					add_filter( 'woocommerce_email_styles', '__return_false' );
					$template = 'simple-template.php';
					break;
				case 'html':
					$template = 'offer.php';
					break;
				case 'plain':
					$template      = 'plain/offer.php';
					$item_template = 'plain/email-list-items.php';
					break;
			}

			$wishlist       = WLFMC_Wishlist_Factory::get_default_wishlist();
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

			$email_content = $wlfmc_email->prepare_content( $email_content, $email_type );
			$email_footer  = $wlfmc_email->prepare_content( $email_footer, $email_type );
			$placeholders  = array(
				'{username}'         => $user->user_login,
				'{user_name}'        => $user->user_login,
				'{user_email}'       => $user->user_email,
				'{user_first_name}'  => $user->first_name,
				'{user_last_name}'   => $user->last_name,
				'{site_name}'        => get_bloginfo( 'name' ),
				'{site_description}' => get_bloginfo( 'description' ),
				'{wishlist_items}'   => $wishlist_items,

			);
			$email_content      = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_content );
			$email_heading      = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_heading );
			$email_footer       = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_footer );
			self::$email_footer = $email_footer;

			add_filter( 'woocommerce_email_footer_text', array( 'WLFMC_Automation_Ajax_Handler', 'add_footer' ) );

			$message = wlfmc_get_template(
				'emails/' . $template,
				array(
					'email'         => $mailer,
					'email_heading' => apply_filters( 'wlfmc_automation_email_heading', $email_heading ),
					'email_content' => apply_filters( 'wlfmc_automation_email_content', $email_content ),
					'email_footer'  => apply_filters( 'wlfmc_automation_email_footer', $email_footer ),
					'mc_options'    => $mc_options,
					'wishlist_url'  => '#',
				),
				true
			);

			remove_filter( 'woocommerce_email_footer_text', array( 'WLFMC_Automation_Ajax_Handler', 'add_footer' ) );

			if ( 'plain' === $email_type ) {
				$message = nl2br( $message );
			}

			$message = $wlfmc_email->style_inline( $message );

			wp_send_json_success( array( 'html' => $message ) );
		}

		/**
		 * Woocommerce Email footer fixed
		 *
		 * @return string
		 */
		public static function add_footer(): string {
			return self::$email_footer;
		}

		/**
		 * Create new automation
		 *
		 * @return void
		 */
		public static function new_automation() {

			global $wpdb;

			check_ajax_referer( 'ajax-nonce', 'key' );

			if ( ! apply_filters( 'wlfmc_can_create_automation', true ) ) {
				wp_send_json(
					array(
						'errors' => __( 'You cannot access to create new automation', 'wc-wlfmc-wishlist' ),
						'status' => false,
					)
				);
			}
			$data = array(
				'status'         => false,
				'errors'         => array(),
				'show_pro_popup' => false,
			);

			$automation_count = (int) $wpdb->get_var( "SELECT  COUNT(ID) as count FROM $wpdb->wlfmc_wishlist_automations WHERE is_pro = 0" );// phpcs:ignore WordPress.DB

			if ( $automation_count > 0 ) {
				$data['show_pro_popup'] = true;
			}

			$options = apply_filters(
				'wlfmc_ajax_automation_options',
				array(
					'automation_name' => '',
					'is_active'       => 0,
					'options'         => array(),
				)
			);

			if ( ! empty( $options ) && 0 === $automation_count ) {
				$automation = new WLFMC_Automation();
				$automation->set_name( $options['automation_name'] );
				$automation->set_status( $options['is_active'] );
				$automation->set_options( $options );
				// insert.
				$insert = $automation->insert();

				if ( $insert ) {
					$data['status'] = true;
					$data['url']    = add_query_arg(
						array(
							'page'          => 'mc-email-automations',
							'tools-action'  => 'edit',
							'automation_id' => $automation->get_id(),
						),
						admin_url( 'admin.php' )
					);
				}
			}
			wp_send_json( apply_filters( 'wlfmc_ajax_new_automation', $data, $options ) );

		}

		/**
		 * Save automation as draft
		 *
		 * @return void
		 */
		public static function save_automation() {

			check_ajax_referer( 'ajax-nonce', 'key' );

			if ( ! isset( $_POST['options'] ) ) {
				return;
			}
			if ( ! apply_filters( 'wlfmc_can_update_automation', true ) ) {
				wp_send_json(
					array(
						'errors' => __( 'You cannot access to update automation', 'wc-wlfmc-wishlist' ),
						'status' => false,
					)
				);
			}

			parse_str( wp_unslash( $_POST['options'] ), $options ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$data    = array(
				'status' => false,
				'errors' => array(),
			);
			$options = wp_parse_args(
				$options,
				apply_filters(
					'wlfmc_ajax_automation_options',
					array(
						'automation_name' => '',
						'is_active'       => 0,
					)
				)
			);
			// Handle required fields.
			$required_fields = apply_filters(
				'wlfmc_save_automation_required_fields',
				array(
					'automation_name'    => __( 'Automation name', 'wc-wlfmc-wishlist' ),
					'email-from-name'    => __( 'From "name"', 'wc-wlfmc-wishlist' ),
					'email-from-address' => __( 'From "Email address"', 'wc-wlfmc-wishlist' ),
				)
			);

			foreach ( $required_fields as $field_key => $field_name ) {
				if ( empty( $options[ $field_key ] ) ) {
					/* translators: %s: field name'*/
					$data['errors'][] = sprintf( __( '%s is a required field.', 'wc-wlfmc-wishlist' ), '<strong>' . esc_html( $field_name ) . '</strong>' );
				}
			}
			if ( ! empty( $options ) && empty( $data['errors'] ) ) {

				$automation_id = isset( $options['automation_id'] ) ? absint( $options['automation_id'] ) : 0;
				unset( $options['automation_id'] );
				$automation = new WLFMC_Automation( $automation_id );

				if ( 0 < $automation->get_id() ) {
					// update.
					$automation->set_name( $options['automation_name'] );
					$automation->set_status( $options['is_active'] );
					$automation->set_options( $options );
					$automation->save();
					$data['status'] = true;
					$data['errors'] = __( 'All changes are saved.', 'wc-wlfmc-wishlist' );
				} else {
					$data['errors'][] = __( 'This automation not exists.', 'wc-wlfmc-wishlist' );

				}
			}
			wp_send_json( apply_filters( 'wlfmc_ajax_save_automation', $data, $options ) );

		}

		/**
		 * Preview Email
		 *
		 * @return void
		 * @throws Exception Exception.
		 */
		public static function preview_email() {
			global $wpdb;
			if ( ! isset( $_REQUEST['wlfmc_preview_offer_id'] ) || ! check_admin_referer( 'wlfmc_preview_email' ) ) {
				return;
			}
			$offer_id = absint( wp_unslash( $_REQUEST['wlfmc_preview_offer_id'] ) );
			if ( $offer_id ) {
				$email_row  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->wlfmc_wishlist_offers WHERE ID=%d ", $offer_id ) ); // phpcs:ignore WordPress.DB
				$automation = new WLFMC_Automation( $email_row->automation_id );
				$user       = WLFMC_Wishlist_Factory::get_customer_data( intval( $email_row->customer_id ) );
				if ( ! $user || ! 0 < $automation->get_id() ) {
					return;
				}
				$mailer                 = WC()->mailer();
				$wlfmc_email            = new WLFMC_Email();
				$email_options          = maybe_unserialize( $email_row->email_options );
				$email_content          = $email_options['mail_content'] ?? '';
				$email_footer           = $email_options['mail_footer'] ?? '';
				$email_heading          = $email_options['mail_heading'] ?? '';
				$saved_placeholders     = $email_options['placeholders'] ?? array();
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
						$template = 'mc-template.php';
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
							'customer-job'           => $options['email-template-customer-job'],
							'social-size'            => $social_size,
							'social-link-in-new-tab' => $social_link_in_new_tab,
							'socials'                => $socials,
						);

						break;
					case 'simple-template':
						add_filter( 'woocommerce_email_styles', '__return_false' );
						$template = 'simple-template.php';

						break;
					case 'html':
						$template = 'offer.php';
						break;
					case 'plain':
					default:
						$template      = 'plain/offer.php';
						$item_template = 'plain/email-list-items.php';
						break;
				}

				$email_content = $wlfmc_email->prepare_content( $email_content, $email_options['mail_type'] );
				$email_footer  = $wlfmc_email->prepare_content( $email_footer, $email_options['mail_type'] );

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
				$wishlist_url   = $wishlist ? $wishlist->get_url() : '';
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
				if ( ! empty( $saved_placeholders ) && is_array( $saved_placeholders ) ) {
					$placeholders = array_merge( $placeholders, $saved_placeholders );
				}

				$email_content = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_content );
				$email_footer  = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_footer );

				unset( $placeholders['{shop_url}'] );
				unset( $placeholders['{checkout_url}'] );
				unset( $placeholders['{wishlist_url}'] );
				unset( $placeholders['{unsubscribe_url}'] );

				$email_heading = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_heading );

				self::$email_footer = $email_footer;

				add_filter( 'woocommerce_email_footer_text', array( 'WLFMC_Automation_Ajax_Handler_Premium', 'add_footer' ) );

				wlfmc_get_template(
					'emails/' . $template,
					array(
						'email'         => $mailer,
						'email_heading' => apply_filters( 'wlfmc_automation_email_heading', $email_heading ),
						'email_content' => apply_filters( 'wlfmc_automation_email_content', $email_content ),
						'email_footer'  => apply_filters( 'wlfmc_automation_email_footer', $email_footer ),
						'mc_options'    => $mc_options,
						'wishlist_url'  => $wishlist_url,
					)
				);
				remove_filter( 'woocommerce_email_footer_text', array( 'WLFMC_Automation_Ajax_Handler_Premium', 'add_footer' ) );

				die();
			}
		}

	}
}

if ( defined( 'MC_WLFMC_PREMIUM' ) ) {

	WLFMC_Automation_Ajax_Handler_Premium::init();

} else {

	WLFMC_Automation_Ajax_Handler::init();

}

