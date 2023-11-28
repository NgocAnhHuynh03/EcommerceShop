<?php
/**
 * Smart Wishlist Email Automation Admin
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'WLFMC_Automation_Admin' ) ) {
	/**
	 * WooCommerce Wishlist Automation Admin
	 */
	class WLFMC_Automation_Admin {


		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Automation_Admin
		 */
		protected static $instance;

		/**
		 * Automation table
		 *
		 * @var WP_List_Table
		 */
		public $automation;

		/**
		 * Automation item table
		 *
		 * @var WP_List_Table
		 */
		public $automation_item;

		/**
		 * Current Automation
		 *
		 * @var WLFMC_Automation
		 */
		protected $current_automation;

		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {
			//phpcs:disable WordPress.Security.NonceVerification
			if ( isset( $_GET['page'] ) && 'mc-email-automations' === $_GET['page'] ) {

				if ( isset( $_GET['tools-action'] ) && in_array(
					$_GET['tools-action'],
					array(
						'edit',
						'view',
					),
					true
				) && isset( $_GET['automation_id'] ) && 0 < absint( $_GET['automation_id'] ) ) {
					$this->current_automation = new WLFMC_Automation( absint( $_GET['automation_id'] ) );
					if ( 'plain' === $this->current_automation->get_mail_type() && 'edit' === $_GET['tools-action'] ) {
						add_action(
							'admin_notices',
							function() {
								WLFMC_Admin_Notice()->styles();
								?>
							<div id="wlfmc-us-core-admin-notice" class="notice wlfmc-notice wlfmc-notice-error">
								<h2 class="error-text"><?php echo wp_kses_post( __( 'Plain email text is outdated.', 'wc-wlfmc-wishlist' ) ); ?></h2>
								<p>
									<?php esc_html_e( 'Consider switching Plain to alternative email formats for your automation.', 'wc-wlfmc-wishlist' ); ?>
								</p>
							</div>
								<?php
							}
						);
					}
				}

				if ( isset( $_GET['tools-action'] ) && 'view' === $_GET['tools-action'] && $this->current_automation && $this->current_automation->get_id() > 0 ) {

					add_filter( 'set-screen-option', array( $this, 'set_automation_item_screen_option' ), 11, 3 );

				} elseif ( ! isset( $_GET['tools-action'] ) ) {

					add_filter( 'set-screen-option', array( $this, 'set_automation_screen_option' ), 11, 3 );

				}
			}
			//phpcs:enable WordPress.Security.NonceVerification
			add_action( 'wlfmc_email_automation_admin_settings', array( $this, 'display_email_automation_page' ), 10 );
			add_action( 'load_submenu_mc_wishlist', array( $this, 'add_screen_options' ), 1 );
			add_filter( 'mct_get_option', array( $this, 'get_option' ), 10, 2 );
			add_filter( 'removable_query_args', array( $this, 'removable_query_args' ) );
		}

		/**
		 * Remove query args
		 *
		 * @param array $removable_query_args Query vars.
		 *
		 * @return array
		 */
		public function removable_query_args( array $removable_query_args ): array {
			return array_merge(
				$removable_query_args,
				array(
					'cant-delete',
					'duplicated',
					'cant-duplicated',
					'recipients-deleted',
					'cant-delete-recipients',
				)
			);
		}

		/**
		 * Enqueue script and styles in admin side.
		 *
		 * @retrun void
		 */
		public function admin_enqueue_scripts() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_register_style( 'wlfmc-automation-admin', MC_WLFMC_URL . 'assets/backend/css/automation' . $suffix . '.css', array(), WLFMC_VERSION );
			wp_enqueue_style( 'wlfmc-automation-admin' );

		}


		/**
		 * Get automation options.
		 *
		 * @param mixed  $option_values all options.
		 * @param string $option_id option id.
		 *
		 * @return mixed
		 */
		public function get_option( $option_values, string $option_id ) {
			if ( 'wlfmc_automation_options' === $option_id && $this->current_automation && $this->current_automation->get_id() > 0 ) {

				$values['wlfmc_automation_options'] = $this->current_automation->get_options();

				return $values;
			}

			return $option_values;
		}


		/**
		 * Add screen options.
		 *
		 * @param array $admin_menus admin menus.
		 *
		 * @return void
		 */
		public function add_screen_options( $admin_menus ) {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( isset( $_GET['page'] ) && 'mc-email-automations' === $_GET['page'] && isset( $admin_menus['email-automations'] ) ) {
				add_action( 'load-' . $admin_menus['email-automations'], array( $this, 'admin_enqueue_scripts' ) );

				if ( isset( $_GET['tools-action'] ) && 'view' === $_GET['tools-action'] && $this->current_automation && $this->current_automation->get_id() > 0 ) {

					add_action(
						'load-' . $admin_menus['email-automations'],
						array(
							$this,
							'add_automation_item_screen_options',
						)
					);

				} elseif ( ! isset( $_GET['tools-action'] ) ) {

					add_action(
						'load-' . $admin_menus['email-automations'],
						array(
							$this,
							'add_automation_screen_options',
						)
					);

				}
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}


		/**
		 * Catch automation_per_page.
		 *
		 * @param string $status Unused.
		 * @param string $option The option name where the value is set for.
		 * @param string $value The new value for the screen option.
		 *
		 * @return string|void
		 */
		public function set_automation_screen_option( $status, $option, $value ) {
			if ( 'automation_per_page' === $option ) {
				return $value;
			}
		}


		/**
		 * Catch automation_item_per_page.
		 *
		 * @param string $status Unused.
		 * @param string $option The option name where the value is set for.
		 * @param string $value The new value for the screen option.
		 *
		 * @return string|void
		 */
		public function set_automation_item_screen_option( $status, $option, $value ) {
			if ( 'automation_item_per_page' === $option ) {
				return $value;
			}
		}

		/**
		 * Setup screen options.
		 *
		 * @return void
		 */
		public function add_automation_screen_options() {

			add_screen_option(
				'per_page',
				array(
					'default' => 20,
					'option'  => 'automation_per_page',
				)
			);

			$this->automation = new WLFMC_Automation_Table();

		}

		/**
		 * Setup screen options.
		 *
		 * @return void
		 */
		public function add_automation_item_screen_options() {

			add_screen_option(
				'per_page',
				array(
					'default' => 20,
					'option'  => 'automation_item_per_page',
				)
			);

			$this->automation_item = new WLFMC_Automation_Item_Table();

		}

		/**
		 * Output all Automation pages.
		 */
		public function output() {

			$action = isset( $_GET['tools-action'] ) && '' !== $_GET['tools-action'] ? sanitize_text_field( wp_unslash( $_GET['tools-action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			if ( '' === $action ) {

				$this->display_automation_lists();

			} elseif ( 'edit' === $action && $this->current_automation && $this->current_automation->get_id() > 0 ) {

				/**
				 * ُ create new automation Or edit automation .
				 */
				$options = new MCT_Options( 'wlfmc_options' );
				$fields  = new MCT_Fields(
					array(
						'steps'          => apply_filters(
							'wlfmc_admin_automation_options',
							array(
								'step-0' => array(
									'steptitle' => esc_html__( 'General', 'wc-wlfmc-wishlist' ),
									'title'     => esc_html__( 'General options', 'wc-wlfmc-wishlist' ),
									'doc'       => 'https://moreconvert.com/p4tn',
									'fields'    => array(
										'automation_id'   => array(
											'type'    => 'hidden',
											'default' => isset( $_GET['automation_id'] ) ? absint( $_GET['automation_id'] ) : '', // phpcs:ignore WordPress.Security.NonceVerification
										),
										'automation_name' => array(
											'label' => esc_html__( 'Automation name', 'wc-wlfmc-wishlist' ),
											'type'  => 'text',
										),
										'is_active'       => array(
											'label' => esc_html__( 'Is active ?', 'wc-wlfmc-wishlist' ),
											'type'  => 'switch',
											'desc'  => esc_html__( 'At the moment, only one email automation can be active, and by deactivating one automation or activating another one, all emails in the queue will be automatically deleted.', 'wc-wlfmc-wishlist' ),
										),
										'email-from-name' => array(
											'label'   => __( 'From "name"', 'wc-wlfmc-wishlist' ),
											'type'    => 'text',
											'default' => $options->get_option( 'email-from-name', wp_specialchars_decode( get_option( 'woocommerce_email_from_name' ), ENT_QUOTES ) ),
										),
										'email-from-address' => array(
											'label'   => __( 'From "Email address"', 'wc-wlfmc-wishlist' ),
											'type'    => 'email',
											'default' => $options->get_option( 'email-from-address', sanitize_email( get_option( 'woocommerce_email_from_address' ) ) ),
										),
									),
									'buttons'   => array(
										'back' => array(
											'btn_class' => 'btn-text',
											'btn_label' => __( 'Back', 'wc-wlfmc-wishlist' ),
											'btn_url'   => add_query_arg(
												array(
													'page' => 'mc-email-automations',
												),
												admin_url( 'admin.php' )
											),
										),
										'next' => array(
											'btn_class' => 'btn-primary green-btn inverse-btn ico-btn check-btn next-step',
											'btn_label' => __( 'Next', 'wc-wlfmc-wishlist' ),
										),
									),
								),
								'step-1' => array(
									'steptitle' => __( 'Conditions', 'wc-wlfmc-wishlist' ),
									'title'     => __( 'Condition options', 'wc-wlfmc-wishlist' ),
									'subtitle'  => __( 'In this section you can specify under what conditions automatic emails will be sent to your users.', 'wc-wlfmc-wishlist' ),
									'doc'       => 'https://moreconvert.com/xzxg',
									'fields'    => array(
										'period-days'     => array(
											'label'   => __( 'Sending cycle', 'wc-wlfmc-wishlist' ),
											'desc'    => __( 'Based on the number of days', 'wc-wlfmc-wishlist' ),
											'type'    => 'number',
											'default' => 90,
											'help'    => __( 'This marketing will resume after these days only if a customer adds a new product to the wishlist.Otherwise, customers won\'t receive any automatic emails.For users who previously defined another sending cycle for any automation, it will be implemented for them based on the previous cycle rule once, and after the previous cycle is executed, the new cycle rule will be implemented.', 'wc-wlfmc-wishlist' ),
										),
										'minimum-wishlist-total' => array(
											'label'   => __( 'Minimum price of total Wishlist items', 'wc-wlfmc-wishlist' ),
											'type'    => 'text',
											'default' => 1000,
											'desc'    => wp_sprintf( '%s: %s', __( 'Currency', 'wc-wlfmc-wishlist' ), get_woocommerce_currency() ),
											'help'    => __( 'Your user\'s wishlist must have the minimum total price entered. If the total price of the user\'s list is less than this number, emails won\'t be sent to the user.', 'wc-wlfmc-wishlist' ),
										),
										'minimum-wishlist-count' => array(
											'label'   => __( 'Minimum number of products on the Wishlist', 'wc-wlfmc-wishlist' ),
											'type'    => 'number',
											'default' => 3,
											'help'    => __( 'The number of products in your user\'s wishlist must be equal to / greater than your choice number to send emails to. For example, for wishlists containing 5 products and more', 'wc-wlfmc-wishlist' ),
										),
										'include-product' => array(
											'label' => __( 'Include at least one of these products', 'wc-wlfmc-wishlist' ),
											'type'  => 'search-product',
											'data'  => array(
												'action' => 'woocommerce_json_search_products_and_variations',
											),
											'help'  => __( 'Your user\'s wishlist must include at least one of the selected products to emails will be sent.', 'wc-wlfmc-wishlist' ),
										),
									),
									'buttons'   => array(
										'back' => array(
											'btn_class' => 'btn-text back-step',
											'btn_label' => __( 'Back', 'wc-wlfmc-wishlist' ),
										),
										'next' => array(
											'btn_class' => 'btn-primary green-btn inverse-btn ico-btn check-btn next-step',
											'btn_label' => __( 'Next', 'wc-wlfmc-wishlist' ),
										),
									),
								),
								'step-2' => array(
									'steptitle' => __( 'Offer', 'wc-wlfmc-wishlist' ),
									'title'     => __( 'Coupon options', 'wc-wlfmc-wishlist' ),
									'subtitle'  => __( 'Creating an offer is optional; to activate the coupon in automation, use the <code>{coupon_code}</code> shortcode in the email content', 'wc-wlfmc-wishlist' ),
									'doc'       => 'https://moreconvert.com/g084',
									'buttons'   => array(
										'back' => array(
											'btn_class' => 'btn-text back-step',
											'btn_label' => __( 'Back', 'wc-wlfmc-wishlist' ),
										),
										'next' => array(
											'btn_class' => 'btn-primary green-btn inverse-btn ico-btn check-btn next-step',
											'btn_label' => __( 'Next', 'wc-wlfmc-wishlist' ),
										),
									),
									'fields'    => array(
										'discount-type'    => array(
											'label'   => __( 'Discount type', 'wc-wlfmc-wishlist' ),
											'type'    => 'select',
											'class'   => 'select2-trigger',
											'options' => wc_get_coupon_types(),
											'default' => 'percent',
											'help'    => __( 'Specify the type of discount you offer in terms of percentage or fixed price.', 'wc-wlfmc-wishlist' ),
										),
										'coupon-amount'    => array(
											'label'       => __( 'Coupon amount', 'wc-wlfmc-wishlist' ),
											'placeholder' => wc_format_localized_price( 0 ),
											'type'        => 'text',
											'desc'        => __( 'Value of the coupon.', 'wc-wlfmc-wishlist' ),
											'default'     => 15,
											'help'        => __( 'Specify the amount of discount in this section. Notice that this discount applies to wishlist. In percentage mode, enter the percentage instead of the amount.', 'wc-wlfmc-wishlist' ),
										),
										'free-shipping'    => array(
											'label' => __( 'Allow free shipping', 'wc-wlfmc-wishlist' ),
											'type'  => 'checkbox',
											/* translators: %s: woocommerce document url */
											'desc'  => sprintf( __( 'Check this box if the coupon grants free shipping. A <a href="%s" target="_blank">free shipping method</a> must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'wc-wlfmc-wishlist' ), 'https://docs.woocommerce.com/document/free-shipping/' ),
											'help'  => __( 'If you choose this option, shipping the purchased products will be free and no cost will be deducted from the customer.', 'wc-wlfmc-wishlist' ),
										),
										'individual-use'   => array(
											'label' => __( 'Individual use only', 'woocommerce' ),
											'desc'  => __( 'Check this box if the coupon cannot be used in conjunction with other coupons.', 'woocommerce' ),
											'type'  => 'checkbox',
										),
										'exclude-sale-items' => array(
											'label' => __( 'Exclude sale items', 'woocommerce' ),
											'desc'  => __( 'Check this box if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are items in the cart that are not on sale.', 'woocommerce' ),
											'type'  => 'checkbox',
										),
										'expiry-date'      => array(
											'label'   => __( 'Coupon expiry after days', 'wc-wlfmc-wishlist' ),
											'desc'    => __( 'The coupon will expire after custom days.', 'wc-wlfmc-wishlist' ),
											'type'    => 'number',
											'default' => 5,
											'help'    => __( 'If you want this offer to expire after a certain number of days, enter the number of days you want.', 'wc-wlfmc-wishlist' ),
										),
										'user-restriction' => array(
											'label'   => __( 'User restriction', 'wc-wlfmc-wishlist' ),
											'desc'    => __( 'Coupon work just for their account email', 'wc-wlfmc-wishlist' ),
											'type'    => 'checkbox',
											'default' => '1',
										),
										'delete-after-expired' => array(
											'label'   => __( 'Delete the Coupon', 'wc-wlfmc-wishlist' ),
											'desc'    => __( 'Delete the coupon after the expiry', 'wc-wlfmc-wishlist' ),
											'type'    => 'checkbox',
											'default' => '1',
											'help'    => __( 'If you choose this option, this discount will be automatically deleted after the expiration and there is no need to delete it manually.', 'wc-wlfmc-wishlist' ),
										),
									),
								),
								'step-3' => array(
									'steptitle' => __( 'Email', 'wc-wlfmc-wishlist' ),
									'title'     => __( 'Email Design Options', 'wc-wlfmc-wishlist' ),
									'subtitle'  => __( 'In this section you can see information about each of the emails. Click the Manage button if you want to change the content of each email.', 'wc-wlfmc-wishlist' ),
									'doc'       => 'https://moreconvert.com/97cf',
									'buttons'   => array(

										'back' => array(
											'btn_class' => 'btn-text back-step',
											'btn_label' => __( 'Back', 'wc-wlfmc-wishlist' ),
										),
										'save' => array(
											'btn_class' => 'btn-primary green-btn inverse-btn ico-btn check-btn save-automation',
											'btn_label' => __( 'Save', 'wc-wlfmc-wishlist' ),
										),
									),
									'fields'    => array(
										'mail-type'    => array(
											'label'   => __( 'Email template', 'wc-wlfmc-wishlist' ),
											'desc'    => __( 'after change , Set and check emails content again', 'wc-wlfmc-wishlist' ),
											'default' => $options->get_option( 'mail-type', 'html' ),
											'type'    => 'select',
											'class'   => 'select2-trigger',
											'options' => array(
												// 'plain' => __( 'Plain', 'wc-wlfmc-wishlist' ),
												'simple-template' => __( 'Simple Template', 'wc-wlfmc-wishlist' ),
												'html' => __( 'HTML Woocommerce', 'wc-wlfmc-wishlist' ),
												'mc-template' => __( 'MC Template', 'wc-wlfmc-wishlist' ),
											),
											'help'    => __( 'Choose which type of email to send.', 'wc-wlfmc-wishlist' ),

										),
										'start-article-template' => array(
											'type'         => 'start',
											'title'        => __( 'Email MC template', 'wc-wlfmc-wishlist' ),
											'desc'         => __( 'You can change the details of the custom email template from here.', 'wc-wlfmc-wishlist' ),
											'dependencies' => array(
												'id'    => 'mail-type',
												'value' => 'mc-template',
											),
										),
										'email-template-columns-start' => array(
											'type'    => 'columns-start',
											'columns' => 2,
										),
										'email-template-column-1-start' => array(
											'type'  => 'column-start',
											'class' => 'flexible-rows',
										),
										'email-template-column-1-child-start' => array(
											'type'    => 'columns-start',
											'columns' => 2,
										),
										'email-template-column-1-child-1-start' => array(
											'type'  => 'column-start',
											'class' => 'flexible-rows',
										),
										'email-template-logo' => array(
											'label'   => __( 'Logo', 'wc-wlfmc-wishlist' ),
											'type'    => 'upload-image',
											'default' => $options->get_option( 'email-template-logo', '' ),
											'help'    => __( 'Upload your site logo or select it from your host', 'wc-wlfmc-wishlist' ),
										),
										'email-template-column-1-child-1-end' => array(
											'type' => 'column-end',
										),
										'email-template-column-1-child-2-start' => array(
											'type'  => 'column-start',
											'class' => 'flexible-rows',
										),
										'email-template-avatar' => array(
											'label'   => __( 'Avatar', 'wc-wlfmc-wishlist' ),
											'type'    => 'upload-image',
											'default' => $options->get_option( 'email-template-avatar', '' ),
											'help'    => __( 'Upload an image of your email sender to show on the email', 'wc-wlfmc-wishlist' ),
										),
										'email-template-column-1-child-2-end' => array(
											'type' => 'column-end',
										),
										'email-template-column-1-child-end' => array(
											'type' => 'column-end',
										),
										'email-template-column-1-child-3-start' => array(
											'type'    => 'columns-start',
											'columns' => 1,
										),
										'email-template-column-1-child-3-1-start' => array(
											'type'  => 'column-start',
											'class' => 'flexible-rows',
										),
										'email-template-customer-name' => array(
											'label'   => __( 'Email sender full name', 'wc-wlfmc-wishlist' ),
											'type'    => 'text',
											'default' => $options->get_option( 'email-template-customer-name', __( 'Ana Ride', 'wc-wlfmc-wishlist' ) ),
										),
										'email-template-customer-job' => array(
											'label'   => __( 'Role of email sender', 'wc-wlfmc-wishlist' ),
											'type'    => 'text',
											'default' => $options->get_option( 'email-template-customer-job', __( 'Customer Manager', 'wc-wlfmc-wishlist' ) ),
										),
										'email-template-column-1-child-3-1-end' => array(
											'type' => 'column-end',
										),
										'email-template-column-1-child-3-end' => array(
											'type' => 'column-end',
										),
										'email-template-column-1-end' => array(
											'type' => 'column-end',
										),
										'email-template-column-2-start' => array(
											'type'  => 'column-start',
											'class' => 'flexible-rows',
										),
										'email-template-socials' => array(
											'label'   => __( 'Social links', 'wc-wlfmc-wishlist' ),
											'type'    => 'repeater',
											'add_new_label' => __( 'Add another social link', 'wc-wlfmc-wishlist' ),
											'limit'   => 5,
											'repeater_fields' => array(
												'social-name' => array(
													'label' => __( 'Name', 'wc-wlfmc-wishlist' ),
													'type' => 'select',
													'class' => 'select2-trigger',
													'options' => array(
														'instagram' => __( 'Instagram', 'wc-wlfmc-wishlist' ),
														'telegram' => __( 'Telegram', 'wc-wlfmc-wishlist' ),
														'reddit'   => __( 'Reddit', 'wc-wlfmc-wishlist' ),
														'whatsapp' => __( 'whatsapp', 'wc-wlfmc-wishlist' ),
														'dribbble' => __( 'dribbble', 'wc-wlfmc-wishlist' ),
														'amazon'   => __( 'amazon', 'wc-wlfmc-wishlist' ),
														'spotify'  => __( 'spotify', 'wc-wlfmc-wishlist' ),
														'behance'  => __( 'behance', 'wc-wlfmc-wishlist' ),
														'location' => __( 'location', 'wc-wlfmc-wishlist' ),
														'tumblr'   => __( 'tumblr', 'wc-wlfmc-wishlist' ),
														'pinterest' => __( 'pinterest', 'wc-wlfmc-wishlist' ),
														'youtube'  => __( 'youtube', 'wc-wlfmc-wishlist' ),
														'linkedin' => __( 'linkedin', 'wc-wlfmc-wishlist' ),
														'twitter'  => __( 'twitter', 'wc-wlfmc-wishlist' ),
														'facebook' => __( 'facebook', 'wc-wlfmc-wishlist' ),

													),
												),
												'social-url'  => array(
													'label' => __( 'URL', 'wc-wlfmc-wishlist' ),
													'type' => 'url',
												),
											),
											'default' => $options->get_option( 'email-template-socials', array() ),
										),
										'email-template-social-style' => array(
											'label'  => __( 'Social links style', 'wc-wlfmc-wishlist' ),
											'type'   => 'group-fields',
											'fields' => array(
												'email-template-social-shape' => array(
													'label' => __( 'Image Shape', 'wc-wlfmc-wishlist' ),
													'type' => 'select',
													'class' => 'select2-trigger',
													'options' => array(
														'default' => __( 'Default', 'wc-wlfmc-wishlist' ),
														'circle'  => __( 'Circle', 'wc-wlfmc-wishlist' ),
														'outlined_circle' => __( 'Outlined circle', 'wc-wlfmc-wishlist' ),
														'outlined_square' => __( 'Outlined square', 'wc-wlfmc-wishlist' ),
														'square'  => __( 'Square', 'wc-wlfmc-wishlist' ),
														'square_rounded' => __( 'Square rounded', 'wc-wlfmc-wishlist' ),
													),
													'default' => $options->get_option( 'email-template-social-shape', 'default' ),
												),
												'email-template-social-size'  => array(
													'label' => __( 'Image size', 'wc-wlfmc-wishlist' ),
													'type' => 'number',
													'custom_attributes' => array(
														'style' => 'width:80px',
													),
													'default' => $options->get_option( 'email-template-social-size', '34' ),
												),
												'email-template-social-color' => array(
													'label' => __( 'Image color', 'wc-wlfmc-wishlist' ),
													'type' => 'select',
													'class' => 'select2-trigger',
													'options' => array(
														'black' => __( 'Black', 'wc-wlfmc-wishlist' ),
														'color' => __( 'Color', 'wc-wlfmc-wishlist' ),
														'grey' => __( 'Grey', 'wc-wlfmc-wishlist' ),
													),
													'default' => $options->get_option( 'email-template-social-color', 'color' ),
												),

											),
										),
										'email-template-social-open-in-new-tab' => array(
											'label'   => '',
											'type'    => 'checkbox',
											'desc'    => __( 'Open links in new tab', 'wc-wlfmc-wishlist' ),
											'default' => $options->get_option( 'email-template-social-open-in-new-tab', '0' ),
										),
										'email-template-column-2-end' => array(
											'type' => 'column-end',
										),
										'email-template-columns-end' => array(
											'type' => 'columns-end',
										),
										'end-article-template' => array(
											'type' => 'end',
										),
										'offer_emails' => array(
											'type'         => 'manage',
											'count'        => 5,
											/* translators: %s: do not translated ,this is a placeholder */
											'row-title'    => __( 'Reminder email %s', 'wc-wlfmc-wishlist' ),
											'row-desc'     => __( 'Specify sending time and content of email here.', 'wc-wlfmc-wishlist' ),
											'table-fields' => array(
												'enable_email' => array(
													'label' => __( 'Activation', 'wc-wlfmc-wishlist' ),
													'type' => 'switch',
													'help' => __( 'enable email. So the ability to send automatic email is activated.', 'wc-wlfmc-wishlist' ),
												),
												'mail_subject' => array(
													'label' => __( 'Email subject', 'wc-wlfmc-wishlist' ),
													'type' => 'value',
												),
												'send_after_days' => array(
													'label' => __( 'Send after', 'wc-wlfmc-wishlist' ),
													'type' => 'value',
													/* translators: %s: do not translated ,this is a placeholder */
													'value_format' => __( '%s day(s)', 'wc-wlfmc-wishlist' ),
												),
												'queue' => array(
													'label' => __( 'Queue', 'wc-wlfmc-wishlist' ),
													'type' => 'value',
													'default' => 0,
													'value_class' => array(
														$this->current_automation,
														'get_count_send_queue_by_email_key',
													),
													'value_depend' => 'row',
												),
											),
											'table-action' => array(
												'title' => __( 'Test email', 'wc-wlfmc-wishlist' ),
												'class' => ' ico-btn email-btn btn-primary min-width-btn small-btn wlfmc-send-offer-email-test',
											),
											'fields'       => array(
												'offer_columns_start' => array(
													'type' => 'columns-start',
													'columns' => 2,
												),
												'offer_column_one_start' => array(
													'type' => 'column-start',
													'class' => 'flexible-rows',
												),
												'send_after_days' => array(
													'label' => __( 'Send this email after days', 'wc-wlfmc-wishlist' ),
													'type' => 'number',
													'custom_attributes' => array(
														'min' => 0,
													),
													'desc' => __( 'Zero means send the email immediately after conditions  happen', 'wc-wlfmc-wishlist' ),
													'help' => __( 'After how many days will this email be sent? Enter the number of days. If you want the email to be sent as soon as products are added to the wishlist, enter zero (Usually for the first email).', 'wc-wlfmc-wishlist' ),
												),
												'mail_heading' => array(
													'label' => __( 'Email heading', 'wc-wlfmc-wishlist' ),
													'class' => 'mail_heading',
													'desc' => __( 'Enter the title for the email notification. Leave blank to use the default heading: "<i>There is a deal for you!</i>". You can use the following placeholders: <code>{username}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code> <code>{site_name}</code> <code>{site_description}</code>', 'wc-wlfmc-wishlist' ),
													'type' => 'text',
												),
												'mail_subject' => array(
													'label' => __( 'Email subject', 'wc-wlfmc-wishlist' ),
													'desc' => __( 'Enter the email subject line. Leave blank to use the default subject: "<i>A product of your Wishlist is on sale</i>". You can use the following placeholders: <code>{username}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code> <code>{site_name}</code> <code>{site_description}</code>', 'wc-wlfmc-wishlist' ),
													'default' => '',
													'type' => 'text',
												),
												'html_content' => array(
													'label' => __( 'Email html content', 'wc-wlfmc-wishlist' ),
													'desc' => __( 'This field lets you modify the main content of the HTML email. You can use the following placeholders: <code>{username}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code> <code>{wishlist_url}</code> <code>{checkout_url}</code> <code>{shop_url}</code> <code>{site_name}</code> <code>{site_description}</code> <code>{unsubscribe_url}</code> <code>{wishlist_items}</code>', 'wc-wlfmc-wishlist' ),
													'class' => 'html_content',
													'type' => 'wp-editor',
													'default' => $this->current_automation ? $this->current_automation::get_default_content( 'html' ) : '',
													'editor_height' => 300,
													'parent_dependencies' => array(
														'id' => 'mail-type',
														'value' => 'html,mc-template,simple-template',
													),
												),
												'text_content' => array(
													'label' => __( 'Email plain content', 'wc-wlfmc-wishlist' ),
													'desc' => __( 'This field lets you modify the main content of the text email. You can use the following placeholders: <code>{username}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code> <code>{wishlist_url}</code> <code>{checkout_url}</code> <code>{shop_url}</code> <code>{site_name}</code> <code>{site_description}</code> <code>{unsubscribe_url}</code> <code>{wishlist_items}</code>', 'wc-wlfmc-wishlist' ),
													'type' => 'textarea',
													'default' => $this->current_automation ? $this->current_automation::get_default_content( 'plain' ) : '',
													'class' => 'resizeable text_content',
													'custom_attributes' => array(
														'cols' => '120',
														'rows' => '10',
													),
													'parent_dependencies' => array(
														'id' => 'mail-type',
														'value' => 'plain',
													),
												),
												'html_footer'  => array(
													'label' => __( 'Email html footer', 'wc-wlfmc-wishlist' ),
													'desc' => __( 'This field lets you modify the footer content of the HTML email. You can use the following placeholders: <code>{username}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code> <code>{wishlist_url}</code> <code>{checkout_url}</code> <code>{shop_url}</code> <code>{site_name}</code> <code>{site_description}</code> <code>{unsubscribe_url}</code>', 'wc-wlfmc-wishlist' ),
													'class' => 'html_footer',
													'type' => 'wp-editor',
													'default' => __( '<a href="{unsubscribe_url}">Unsubscribe</a>', 'wc-wlfmc-wishlist' ),
													'editor_height' => 150,
													'parent_dependencies' => array(
														'id' => 'mail-type',
														'value' => 'html,mc-template,simple-template',
													),
												),
												'text_footer'  => array(
													'label' => __( 'Email plain footer', 'wc-wlfmc-wishlist' ),
													'desc' => __( 'This field lets you modify the footer content of the text email. You can use the following placeholders: <code>{username}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code> <code>{wishlist_url}</code> <code>{checkout_url}</code> <code>{shop_url}</code> <code>{site_name}</code> <code>{site_description}</code> <code>{unsubscribe_url}</code>', 'wc-wlfmc-wishlist' ),
													'type' => 'textarea',
													'default' => __( 'unsubscribe:{unsubscribe_url}', 'wc-wlfmc-wishlist' ),
													'class' => 'resizeable text_footer',
													'custom_attributes' => array(
														'cols' => '120',
														'rows' => '5',
													),
													'parent_dependencies' => array(
														'id' => 'mail-type',
														'value' => 'plain',
													),
												),
												'offer_column_one-end' => array(
													'type' => 'column-end',
												),
												'offer_column_two_start' => array(
													'type' => 'column-start',
													'custom_attributes' => array(
														'height' => '100%',
													),
												),
												'offer_preview_email' => array(
													'title' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
													'type' => 'iframe',
													'class' => 'preview_iframe_wrapper',
													'src'  => 'about:blank',
													'custom_attributes' => array(
														'width'  => '100%',
														'height' => '100%',
														'style'  => is_rtl() ? 'max-width:calc( 100% - 40px );padding:0 20px;border-right:1px solid #f2f2f2;max-height:calc( 100% - 100px );min-height:500px' : 'max-width:calc( 100% - 40px );padding:0 20px;border-left:1px solid #f2f2f2;max-height:calc( 100% - 100px );min-height:500px;',
													),
												),
												'offer_column_two_end' => array(
													'type' => 'column-end',
												),
												'offer_columns_end' => array(
													'type' => 'columns-end',
												),
											),
											'default'      => array(
												array(
													'enable_email' => '1',
													'send_after_days' => '1',
													'mail_heading' => __( 'Check it out, {user_first_name}', 'wc-wlfmc-wishlist' ),
													'mail_subject' => __( 'Check it out, {user_first_name}', 'wc-wlfmc-wishlist' ),
													'html_content' => $this->current_automation ? $this->current_automation::get_default_content( 'html', 1 ) : '',
													'text_content' => $this->current_automation ? $this->current_automation::get_default_content( 'plain', 1 ) : '',
													'text_footer'  => __( 'unsubscribe:{unsubscribe_url}', 'wc-wlfmc-wishlist' ),
													'html_footer'  => __( '<a href="{unsubscribe_url}">Unsubscribe</a>', 'wc-wlfmc-wishlist' ),
												),
												array(
													'enable_email' => '1',
													'send_after_days' => '3',
													'mail_heading' => __( 'Deals you’ve been waiting for!', 'wc-wlfmc-wishlist' ),
													'mail_subject' => __( 'Deals you’ve been waiting for!', 'wc-wlfmc-wishlist' ),
													'html_content' => $this->current_automation ? $this->current_automation::get_default_content( 'html', 2 ) : '',
													'text_content' => $this->current_automation ? $this->current_automation::get_default_content( 'plain', 2 ) : '',
													'text_footer'  => __( 'unsubscribe:{unsubscribe_url}', 'wc-wlfmc-wishlist' ),
													'html_footer'  => __( '<a href="{unsubscribe_url}">Unsubscribe</a>', 'wc-wlfmc-wishlist' ),
												),
												array(
													'enable_email' => '1',
													'send_after_days' => '5',
													'mail_heading' => __( 'Got {coupon_amount} off your favorites?', 'wc-wlfmc-wishlist' ),
													'mail_subject' => __( 'Got {coupon_amount} off your favorites?', 'wc-wlfmc-wishlist' ),
													'html_content' => $this->current_automation ? $this->current_automation::get_default_content( 'html', 3 ) : '',
													'text_content' => $this->current_automation ? $this->current_automation::get_default_content( 'plain', 3 ) : '',
													'text_footer'  => __( 'unsubscribe:{unsubscribe_url}', 'wc-wlfmc-wishlist' ),
													'html_footer'  => __( '<a href="{unsubscribe_url}">Unsubscribe</a>', 'wc-wlfmc-wishlist' ),
												),
												array(
													'enable_email' => '1',
													'send_after_days' => '7',
													'mail_heading' => __( 'The item on your wishlist is almost sold out!', 'wc-wlfmc-wishlist' ),
													'mail_subject' => __( 'The item on your wishlist is almost sold out!', 'wc-wlfmc-wishlist' ),
													'html_content' => $this->current_automation ? $this->current_automation::get_default_content( 'html', 4 ) : '',
													'text_content' => $this->current_automation ? $this->current_automation::get_default_content( 'plain', 4 ) : '',
													'text_footer'  => __( 'unsubscribe:{unsubscribe_url}', 'wc-wlfmc-wishlist' ),
													'html_footer'  => __( '<a href="{unsubscribe_url}">Unsubscribe</a>', 'wc-wlfmc-wishlist' ),
												),
												array(
													'enable_email' => '1',
													'send_after_days' => '9',
													'mail_heading' => __( 'Just one day for your wishlist', 'wc-wlfmc-wishlist' ),
													'mail_subject' => __( 'Just one day for your wishlist', 'wc-wlfmc-wishlist' ),
													'html_content' => $this->current_automation ? $this->current_automation::get_default_content( 'html', 5 ) : '',
													'text_content' => $this->current_automation ? $this->current_automation::get_default_content( 'plain', 5 ) : '',
													'text_footer'  => __( 'unsubscribe:{unsubscribe_url}', 'wc-wlfmc-wishlist' ),
													'html_footer'  => __( '<a href="{unsubscribe_url}">Unsubscribe</a>', 'wc-wlfmc-wishlist' ),
												),
											),
										),
									),

								),
							)
						),
						'type'           => 'ajax-wizard-type',
						'id'             => 'wlfmc_automation_options',
						'footer_buttons' => array(
							array(
								'btn_label'  => __( 'Back to Email Automation', 'wc-wlfmc-wishlist' ),
								'btn_class'  => 'btn-secondary',
								'btn_url'    => add_query_arg(
									array(
										'page' => 'mc-email-automations',
									),
									admin_url( 'admin.php' )
								),
								'btn_target' => '_self',
							),
						),
					)
				);
				$fields->output();

			} elseif ( 'view' === $action && $this->current_automation && $this->current_automation->get_id() > 0 ) {

				$this->display_automation_item_lists();

			} else {

				$this->display_404_page();
			}
			?>
			<?php
		}

		/**
		 * Display the list of automations.
		 *
		 * @return void
		 */
		public function display_automation_lists() {
			global $wpdb;

			$automation_count = (int) $wpdb->get_var( "SELECT  COUNT(ID) as count FROM $wpdb->wlfmc_wishlist_automations WHERE is_pro = 0" ); // phpcs:ignore WordPress.DB
			?>
			<style>

				.mct-modal .modal-large {
					max-width: 840px;
				}

				.mct-modal .modal-large .modal-body {
					padding: 30px 30px 10px 30px;
				}

				.mct-modal .modal-large .modal-image {
					padding: 0 20px;
				}

				.mct-modal .modal-large .modal-image img {
					max-width: 400px;
				}

				.mct-modal .modal-large .modal-content {
					text-align: left;
				}

				.mct-modal .modal-large .modal-content h2 {
					font-size: 22px;
					margin: 0 0 15px;
					line-height: 30px;
				}

				.mct-modal .modal-large .features-card {
					padding: 20px;
					border: 1px solid rgba(112, 112, 112, 0.062745098);
					-webkit-border-radius: 20px;
					border-radius: 20px;
					background-color: rgba(255, 238, 229, 0.062745098);
				}

				.mct-modal .modal-large .features {
					display: -webkit-box;
					display: -webkit-flex;
					display: -ms-flexbox;
					display: flex;
					-webkit-box-orient: vertical;
					-webkit-box-direction: normal;
					-webkit-flex-direction: column;
					-ms-flex-direction: column;
					flex-direction: column;
					gap: 10px;
					font-weight: 500;
				}

				.mct-modal .modal-large .feature-title {
					font-weight: 600;
				}

				.mct-modal .modal-large .feature-title + .features {
					padding: 0 15px;
				}

				.mct-modal .modal-large .modal-buttons {
					-webkit-box-orient: horizontal;
					-webkit-box-direction: normal;
					-webkit-flex-direction: row;
					-ms-flex-direction: row;
					flex-direction: row;
					gap: 10px;
				}

				.mct-modal .modal-large .modal-footer {
					padding: 0 30px 30px;
				}

				.mct-modal .modal-large .modal-footer .features {
					-webkit-box-orient: horizontal;
					-webkit-box-direction: normal;
					-webkit-flex-direction: row;
					-ms-flex-direction: row;
					flex-direction: row;
					-webkit-flex-wrap: wrap;
					-ms-flex-wrap: wrap;
					flex-wrap: wrap;
					width: 100%;
				}

				.mct-modal .modal-large .modal-footer .features .column {
					width: 48%;
					text-align: left;
				}

				.rtl .mct-modal .modal-large .modal-content {
					text-align: right;
				}

				.rtl .mct-modal .modal-footer .features .column {
					text-align: right;
				}
				.btn-flat {
					padding: 5px;
					-webkit-border-radius: 6px;
					border-radius: 6px;
					background: #ffeee5;
					line-height: 20px;
					color: #FD5D00 !important;
					display: -webkit-inline-flex;
					display: -ms-inline-flexbox;
					display: inline-flex;
					-webkit-box-align: center;
					-webkit-align-items: center;
					-ms-flex-align: center;
					align-items: center;
					gap: 5px;
					outline: none;
					-webkit-box-shadow: none !important;
					box-shadow: none !important;
					font-weight: 600;
					font-size: 11px;
					-webkit-box-pack: center;
					-webkit-justify-content: center;
					-ms-flex-pack: center;
					justify-content: center;
					-webkit-flex-wrap: nowrap;
					-ms-flex-wrap: nowrap;
					flex-wrap: nowrap;
					-webkit-box-orient: horizontal;
					-webkit-box-direction: normal;
					-webkit-flex-direction: row;
					-ms-flex-direction: row;
					flex-direction: row;
				}

				.btn-flat span {
					padding: 0 10px;
				}

				.btn-flat.btn-orange {
					background: #fd5d00;
					-webkit-filter: drop-shadow(0px 5px 10px rgba(253, 93, 0, 0.25));
					filter: drop-shadow(0px 5px 10px rgba(253, 93, 0, 0.25));
					color: #fff !important;
				}

				@media only screen and (max-width: 992px) {
					.mct-modal .modal-large .modal-body .modal-image img {
						max-width: 300px;
					}
				}

				@media only screen and (max-width: 767px) {
					.mct-modal .modal-large .modal-body .modal-image {
						display: none;
					}
				}

				@media only screen and (max-width: 600px) {

					.mct-modal .modal-large .modal-footer .features .column {
						width: 100%;
					}
				}
			</style>
			<div class="mct-article">
				<div class="article-title">
					<div class="d-flex space-between f-center">
						<h2 class="">
							<span><?php esc_html_e( 'Email Automations', 'wc-wlfmc-wishlist' ); ?></span>
							<small class="mct-need-help-label"><?php esc_attr_e( 'Need help?', 'mct-options' ); ?></small>
							<a href="<?php echo esc_url( 'https://moreconvert.com/x673' ); ?>" target="_blank" class="d-inline-flex mct-youtube-url">
								<span class="dashicons dashicons-youtube"></span>
							</a>
							<!-- MCT Document -->
							<a href="<?php echo esc_url( 'https://moreconvert.com/8vkq' ); ?>" target="_blank" class="d-inline-flex mct-doc-url">
								<span class="dashicons dashicons-book"></span>
							</a>
						</h2>
						<a href="#" data-modal="modal_new-automation" class="btn-primary center-align small-btn <?php echo $automation_count > 0 ? 'modal-toggle' : 'new-automation'; ?>  "><?php esc_html_e( 'New Automation', 'wc-wlfmc-wishlist' ); ?></a>
						<div id="modal_new-automation" class="mct-modal modal_new-automation">
							<div class="modal-overlay modal-toggle" data-modal="modal_new-automation"></div>
							<div class="modal-wrapper modal-transition modal-large">
								<button class="modal-close modal-toggle" data-modal="modal_new-automation">
									<span class="dashicons dashicons-no-alt"></span>
								</button>
								<div class="modal-body">
									<div class="modal-image">
										<a href="https://moreconvert.com/aqxv" target="_blank">
											<img src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/automation/automation.gif" alt="automation" />
										</a>
									</div>
									<div class="modal-content">
										<img src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/crown.svg" width="72" height="72" alt="crown" />
										<h2><?php esc_attr_e( 'Unlock Now!', 'wc-wlfmc-wishlist' ); ?><br><span style="color: #5EC4FF"><?php esc_attr_e( 'Sequential Email Automation', 'wc-wlfmc-wishlist' ); ?></span></h2>
										<p class="desc"><?php esc_attr_e( 'Active automated emails for users to receive invitations to buy products they like and add to their list with highly personalized emails and increase sales.', 'wc-wlfmc-wishlist' ); ?></p>
										<div class="modal-buttons">
											<a data-modal="modal_new-automation" class="btn-flat btn-orange" href="https://moreconvert.com/2l9q" target="_blank">
												<span><?php esc_attr_e( 'Unlock Now', 'wc-wlfmc-wishlist' ); ?></span>
												<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
													<g transform="translate(-375 -149)">
														<rect width="24" height="24" rx="6" transform="translate(375 149)" fill="#fff"></rect>
														<path d="M8.537,3a.615.615,0,0,1,.615.615V8.9l1.411-1.411a.615.615,0,0,1,.87.87L8.972,10.818a.615.615,0,0,1-.87,0L5.641,8.357a.615.615,0,1,1,.87-.87L7.922,8.9V3.615A.615.615,0,0,1,8.537,3ZM3.615,9.768a.615.615,0,0,1,.615.615,5.837,5.837,0,0,0,.035.975,1.846,1.846,0,0,0,1.45,1.45,5.838,5.838,0,0,0,.975.035h3.692a5.839,5.839,0,0,0,.975-.035,1.846,1.846,0,0,0,1.45-1.45,5.839,5.839,0,0,0,.035-.975.615.615,0,1,1,1.231,0v.071a6.158,6.158,0,0,1-.059,1.144A3.076,3.076,0,0,1,11.6,14.016a6.157,6.157,0,0,1-1.144.059H6.62a6.157,6.157,0,0,1-1.144-.059A3.076,3.076,0,0,1,3.059,11.6,6.161,6.161,0,0,1,3,10.454c0-.023,0-.047,0-.071A.615.615,0,0,1,3.615,9.768Z" transform="translate(378.463 152.463)" fill="#fd5d00" fill-rule="evenodd"></path>
													</g>
												</svg>
											</a>
											<a data-modal="modal_new-automation" class="btn-flat btn-get-start" href="https://moreconvert.com/aqxv" target="_blank">
												<span><?php esc_attr_e( 'Learn More', 'wc-wlfmc-wishlist' ); ?></span>
												<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
													<rect width="24" height="24" rx="6" fill="#fd5d00"></rect>
													<path d="M6.891,5.181a.71.71,0,0,0-1,0L4.259,6.809V.71A.71.71,0,1,0,2.84.71v6.1L1.212,5.181a.71.71,0,0,0-1,1L3.049,9.025a.71.71,0,0,0,1.006,0L6.9,6.185A.714.714,0,0,0,6.891,5.181Z" transform="translate(7.383 15.55) rotate(-90)" fill="#fff" fill-rule="evenodd"></path>
												</svg>
											</a>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<div class="features-card">
										<div class="features">
											<div class="column d-flex f-center gap-5">
												<img src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/automation/feature-2.svg" width="40" height="40" alt="features" />
												<span><?php esc_attr_e( 'Increase customer retention', 'wc-wlfmc-wishlist' ); ?></span>
											</div>
											<div class="column d-flex f-center gap-5">
												<img src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/automation/feature-4.svg" width="40" height="40" alt="features" />
												<span><?php esc_attr_e( 'Save 20 hours every week', 'wc-wlfmc-wishlist' ); ?></span>
											</div>
											<div class="column d-flex f-center gap-5">
												<img src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/automation/feature-3.svg" width="40" height="40" alt="features" />
												<span><?php esc_attr_e( 'Increase customer loyalty', 'wc-wlfmc-wishlist' ); ?></span>
											</div>
											<div class="column d-flex f-center gap-5">
												<img src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/automation/feature-1.svg" width="40" height="40" alt="features" />
												<span><?php esc_attr_e( 'Real-time tracking', 'wc-wlfmc-wishlist' ); ?></span>
											</div>
											<div class="column d-flex f-center gap-5">
												<img src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/automation/feature-5.svg" width="40" height="40" alt="features" />
												<span><?php esc_attr_e( 'Better targeting', 'wc-wlfmc-wishlist' ); ?></span>
											</div>
											<div class="column d-flex f-center gap-5">
												<img src="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/dashboard/automation/feature-6.svg" width="40" height="40" alt="features" />
												<span><?php esc_attr_e( 'Gain valuable insights', 'wc-wlfmc-wishlist' ); ?></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<p class="description"><?php esc_html_e( 'Manage your email automations to create highest profit', 'wc-wlfmc-wishlist' ); ?></p>
				</div>
				<div class="ajax-message-holder"></div>
				<?php $this->automation->views(); ?>
				<form method="post">
					<input type="hidden" name="page" value="mc-email-automations" />
					<?php
					$this->automation->message();
					$this->automation->prepare_items();
					$this->automation->search_box( __( 'Search', 'wc-wlfmc-wishlist' ), 'automation-search' );
					$this->automation->display();
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Display the list of automation items.
		 *
		 * @return void
		 */
		public function display_automation_item_lists() {
			$automations_url      = add_query_arg(
				array(
					'page' => 'mc-email-automations',
				),
				admin_url( 'admin.php' )
			);
			$edit_automations_url = add_query_arg(
				array(
					'page'          => 'mc-email-automations',
					'tools-action'  => 'edit',
					'automation_id' => $this->current_automation->get_id(),
				),
				admin_url( 'admin.php' )
			);
			?>
			<div class="mct-article mar-bot-20">
				<div class="article-title">
					<div class="d-flex space-between f-center">
						<h2 class="">
							<span>
								<?php
								/* translators: %s: automation report name */
								printf( esc_html__( '%s Automation Reports', 'wc-wlfmc-wishlist' ), esc_attr( $this->current_automation->get_name() ) );
								?>
							</span>
						</h2>
						<div class="buttons margin-bet">
							<a href="<?php echo esc_url( $automations_url ); ?>" class="btn-secondary center-align small-btn "><?php esc_html_e( 'Back', 'wc-wlfmc-wishlist' ); ?></a>
							<a href="<?php echo esc_url( $edit_automations_url ); ?>" class="btn-secondary center-align small-btn "><?php esc_html_e( 'Manage', 'wc-wlfmc-wishlist' ); ?></a>
						</div>
					</div>
				</div>
				<ul class="mct-tools">
					<li>
						<a>
							<svg class="icon">
								<use xlink:href="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/sprite.svg#email-campaign"></use>
							</svg>
							<strong><?php echo esc_attr( $this->current_automation->get_recipients_total() ); ?></strong>
							<span class="tool-title"><?php esc_html_e( 'Total Recipient', 'wc-wlfmc-wishlist' ); ?></span>
						</a>

					</li>
					<li>
						<a>
							<svg class="icon">
								<use xlink:href="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/sprite.svg#smart-sidebar"></use>
							</svg>
							<strong><?php echo esc_attr( $this->current_automation->get_send_queue() ); ?></strong>
							<span class="tool-title"><?php esc_html_e( 'Send Queue', 'wc-wlfmc-wishlist' ); ?></span>
						</a>
					</li>
					<li>
						<a>
							<svg class="icon">
								<use xlink:href="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/sprite.svg#warm-call"></use>
							</svg>
							<strong><?php echo esc_attr( $this->current_automation->get_open_rate() ); ?></strong>
							<span class="tool-title"><?php esc_html_e( 'Open Rate', 'wc-wlfmc-wishlist' ); ?></span>
						</a>
					</li>
					<li>
						<a>
							<svg class="icon">
								<use xlink:href="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/sprite.svg#smart-stickybar"></use>
							</svg>
							<strong><?php echo esc_attr( $this->current_automation->get_click_rate() ); ?></strong>
							<span class="tool-title"><?php esc_html_e( 'Click Rate', 'wc-wlfmc-wishlist' ); ?></span>
						</a>
					</li>
					<li>
						<a>
							<svg class="icon">
								<use xlink:href="<?php echo esc_url( MC_WLFMC_URL ); ?>assets/backend/images/sprite.svg#smart-popup"></use>
							</svg>
							<strong><?php echo wp_kses_post( $this->current_automation->get_net_total() ); ?></strong>
							<span class="tool-title"><?php esc_html_e( 'Revenue', 'wc-wlfmc-wishlist' ); ?></span>
						</a>
					</li>
				</ul>
			</div>
			<?php
			$saved_options = $this->current_automation->get_options();
			if ( isset( $saved_options['offer_emails'] ) && ! empty( $saved_options['offer_emails'] ) ) :
				?>

				<div class="mct-article mar-bot-20 ">

					<table class="mct-border-table mct-manages mct-responsive-table">
						<thead>
						<tr>
							<th><?php esc_html_e( 'Row', 'wc-wlfmc-wishlist' ); ?></th>
							<th><?php esc_html_e( 'Recipients', 'wc-wlfmc-wishlist' ); ?></th>
							<th><?php esc_html_e( 'Open Rate', 'wc-wlfmc-wishlist' ); ?></th>
							<th><?php esc_html_e( 'Click Rate', 'wc-wlfmc-wishlist' ); ?></th>
							<th><?php esc_html_e( 'Net', 'wc-wlfmc-wishlist' ); ?></th>
							<th><?php esc_attr_e( 'Filter', 'wc-wlfmc-wishlist' ); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ( $saved_options['offer_emails'] as $k => $email ) : ?>
							<?php
							$row            = $this->current_automation->get_email_key_states( $k );
							$current_status = ( ! empty( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : 'all' );
							$current_key    = ( ! empty( $_REQUEST['email_key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['email_key'] ) ) : '' );

							$filter_url        = add_query_arg(
								array(
									'page'          => 'mc-email-automations',
									'tools'         => 'email-automation',
									'tools-action'  => 'view',
									'automation_id' => $this->current_automation->get_id(),
									'email_key'     => $k,
									'status'        => $current_status,
								),
								admin_url( 'admin.php' )
							);
							$remove_filter_url = add_query_arg(
								array(
									'page'          => 'mc-email-automations',
									'tools'         => 'email-automation',
									'tools-action'  => 'view',
									'automation_id' => $this->current_automation->get_id(),
									'status'        => $current_status,
								),
								admin_url( 'admin.php' )
							);
							?>
							<?php if ( $row ) : ?>
								<tr>
									<td data-title="<?php esc_html_e( 'Row', 'wc-wlfmc-wishlist' ); ?>">
										Email-0<?php echo esc_attr( $k + 1 ); ?></td>
									<td data-title="<?php esc_html_e( 'Recipients', 'wc-wlfmc-wishlist' ); ?>"><?php echo number_format( (float) $row['recipients'] ); ?></td>
									<td data-title="<?php esc_html_e( 'Open Rate', 'wc-wlfmc-wishlist' ); ?>"><?php echo number_format( (float) $row['open_rate'] ) . '%'; ?></td>
									<td data-title="<?php esc_html_e( 'Click Rate', 'wc-wlfmc-wishlist' ); ?>"><?php echo number_format( (float) $row['click_rate'] ) . '%'; ?></td>
									<td data-title="<?php esc_html_e( 'Net', 'wc-wlfmc-wishlist' ); ?>"><?php echo wc_price( $row['net'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td data-title="<?php esc_attr_e( 'Filter', 'wc-wlfmc-wishlist' ); ?>"><a class="btn-secondary small-btn <?php echo absint( $current_key ) === absint( $k ) ? 'current' : ''; ?>" href="<?php echo absint( $current_key ) === absint( $k ) ? esc_url( $remove_filter_url ) : esc_url( $filter_url ); ?>"><?php echo absint( $current_key ) === absint( $k ) ? esc_attr__( 'Remove Filter', 'wc-wlfmc-wishlist' ) : esc_attr__( 'Filter', 'wc-wlfmc-wishlist' ); ?></a></td>
								</tr>
							<?php else : ?>
								<tr>
									<td data-title="<?php esc_html_e( 'Row', 'wc-wlfmc-wishlist' ); ?>">
										Email-0<?php echo esc_attr( $k + 1 ); ?></td>
									<td data-title="<?php esc_html_e( 'Recipients', 'wc-wlfmc-wishlist' ); ?>">0</td>
									<td data-title="<?php esc_html_e( 'Open Rate', 'wc-wlfmc-wishlist' ); ?>">-</td>
									<td data-title="<?php esc_html_e( 'Click Rate', 'wc-wlfmc-wishlist' ); ?>">-</td>
									<td data-title="<?php esc_html_e( 'Net', 'wc-wlfmc-wishlist' ); ?>">-</td>
									<td data-title="<?php esc_attr_e( 'Filter', 'wc-wlfmc-wishlist' ); ?>">-</td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
						</tbody>
					</table>

				</div>
			<?php endif; ?>
			<div class="mct-article">
				<?php $this->automation_item->views(); ?>
				<form method="post">
					<input type="hidden" name="page" value="mc-email-automations" />
					<input type="hidden" name="tools-action" value="view" />
					<input type="hidden" name="automation_id" value="<?php echo isset( $_GET['automation_id'] ) ? absint( $_GET['automation_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification ?>" />
					<?php
					$this->automation_item->message();
					$this->automation_item->prepare_items();
					$this->automation_item->search_box( __( 'Search', 'wc-wlfmc-wishlist' ), 'automation-search' );
					$this->automation_item->display();
					?>
				</form>
			</div>
			<script>
				(function ($) {

					$( document ).ready(
						function () {
							$( document.body ).on(
								'click',
								'.modal_preview_email-toggle',
								function() {
									var t = $('#' + $(this).data('modal') ).find("iframe[data-src]");
									t.attr("src", t.attr("data-src"));
									t.removeAttr("data-src");
									t.addClass('block-loading');
									t.on('load', function() {
										$(this).removeClass('block-loading');
									});
								}
							);
						}
					);
				})( jQuery );
			</script>
			<?php
		}

		/**
		 * Display 404 page.
		 *
		 * @return void
		 */
		public function display_404_page() {
			$fields = new MCT_Fields(
				array(
					'title'        => __( 'MoreConvert', 'wc-wlfmc-wishlist' ),
					'logo'         => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/logo.svg" width="45" height="40"  alt="logo" />',
					'type'         => 'page-404-type',
					'subtitle'     => __( 'Uh Oh...', 'wc-wlfmc-wishlist' ),
					'desc'         => __( 'There Isn\'t What You Want, Let\'s Check The Address Or Start From First Step!', 'wc-wlfmc-wishlist' ),
					'page_buttons' => array(
						array(
							'btn_label' => __( 'View Documentation', 'wc-wlfmc-wishlist' ),
							'btn_class' => ' btn-primary',
							'btn_url'   => 'https://moreconvert.com/v4nf',
						),
						array(
							'btn_label' => __( 'Request Support', 'wc-wlfmc-wishlist' ),
							'btn_class' => 'btn-primary purplelight-btn',
							'btn_url'   => 'https://moreconvert.com/m6z4',
						),
					),

				)
			);
			$fields->output();
		}


		/**
		 * Display email automation settings.
		 */
		public function display_email_automation_page() {

			$fields = new MCT_Fields(
				array(
					'title'          => __( 'MoreConvert', 'wc-wlfmc-wishlist' ),
					'logo'           => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/logo.svg" width="45" height="40"  alt="logo" />',
					'type'           => 'class-type',
					'header_buttons' => wlfmc_get_admin_header_buttons(),
					'header_menu'    => wlfmc_get_admin_header_menu(),
					'id'             => 'wlfmc_automation_options',
					'class_array'    => array( $this, 'output' ),
				)
			);
			$fields->output();

		}


		/**
		 * Returns single instance of the class.
		 *
		 * @access public
		 *
		 * @return WLFMC_Automation_Admin
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
 * Unique access to instance of WLFMC_Automation_Admin class.
 *
 * @return WLFMC_Automation_Admin_Premium|WLFMC_Automation_Admin
 */
function WLFMC_Automation_Admin() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid

	if ( defined( 'MC_WLFMC_PREMIUM' ) ) {

		return WLFMC_Automation_Admin_Premium::get_instance();

	} else {

		return WLFMC_Automation_Admin::get_instance();

	}

}
