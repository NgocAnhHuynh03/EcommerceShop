<?php
/**
 * Smart Wishlist Admin
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 *
 * @version 1.6.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Admin' ) ) {
	/**
	 * This class handles admin for wishlist plugin
	 */
	class WLFMC_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Admin
		 */
		protected static $instance;

		/**
		 * Previous version
		 *
		 * @var string
		 */
		public $rollback_version = '1.6.9';

		/**
		 * Minimum pro version
		 *
		 * @var string
		 */
		public $minimum_pro_version = '1.6.7';

		/**
		 * Main panel
		 *
		 * @var MCT_Admin $main_panel Main panel.
		 */
		public $main_panel;

		/**
		 * Installed state
		 *
		 * @var bool
		 * @since 1.0.1
		 */
		public $installed;

		/**
		 * Global options
		 *
		 * @var array
		 */
		public $global_options;

		/**
		 * Wishlist options
		 *
		 * @var array
		 */
		public $wishlist_options;

		/**
		 * Wishlist panel
		 *
		 * @var MCT_Admin $wishlist_panel wishlist panel.
		 */
		public $wishlist_panel;

		/**
		 * Constructor
		 *
		 * @return void
		 *
		 * @version 1.6.8
		 */
		public function __construct() {
			// install plugin, or update from older versions.
			add_action( 'init', array( $this, 'install' ) );

			$this->installed = WLFMC_Install()->is_installed();

			if ( $this->installed ) {

				$this->global_options = array(
					'options'        => apply_filters(
						'wlfmc_admin_options',
						array(
							'global-settings' => array(
								'tabs'   => array(
									'general'    => __( 'General', 'wc-wlfmc-wishlist' ),
									'appearance' => __( 'Appearance', 'wc-wlfmc-wishlist' ),
									'share'      => __( 'Social Share', 'wc-wlfmc-wishlist' ),
									'marketing'  => __( 'Marketing', 'wc-wlfmc-wishlist' ),
								),
								'fields' => array(
									'general'    => apply_filters(
										'wlfmc_global_general_settings',
										array(
											'start-article-lists-actions-settings' => array(
												'type'  => 'start',
												'title' => __( 'Lists Actions Settings', 'wc-wlfmc-wishlist' ),
											),
											'remove_from_wishlist' => array(
												'label'   => __( 'Remove Condition', 'wc-wlfmc-wishlist' ),
												'type'    => 'select',
												'class'   => 'select2-trigger',
												'options' => array(
													'none' => __( 'Do not remove', 'wc-wlfmc-wishlist' ),
													'added-to-cart' => __( 'Added to the card', 'wc-wlfmc-wishlist' ),
													'completed' => __( 'Purchase completed', 'wc-wlfmc-wishlist' ),
													'processing' => __( 'Purchase processing', 'wc-wlfmc-wishlist' ),
												),
												'default' => 'none',
												'help'    => __( 'Remove from wishlist or any other lists based on this condition will happen.', 'wc-wlfmc-wishlist' ),
											),
											'redirect_after_add_to_cart' => array(
												'label'   => __( 'Redirect to the cart', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
												'help'    => __( 'It works for the "add all to cart" button and the "add to cart" option in the action button and "add to cart" button in the lists.', 'wc-wlfmc-wishlist' ),
											),
											'product_move' => array(
												'label'   => __( 'Product Move', 'wc-wlfmc-wishlist' ),
												'help'    => __( 'This feature enables users to seamlessly transfer products from one list to another. Please note that this functionality only can be used within wishlists and multi-lists.', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Just for premium users', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '0',
												'parent_class' => 'enable-for-pro',
												'custom_attributes' => array(
													'disabled' => 'true',
												),
											),
											'external_in_new_tab' => array(
												'label'   => __( 'Open New Tab for External Product Add to Cart', 'wc-wlfmc-wishlist' ),
												'help'    => __( 'It is essential for affiliate products to open in a new tab and keep users active on your website.', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Just for premium users', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '0',
												'parent_class' => 'enable-for-pro',
												'custom_attributes' => array(
													'disabled' => 'true',
												),
											),
											'end-article-lists-actions-settings' => array(
												'type' => 'end',
											),
											'start-article-wishlist-optimization-settings' => array(
												'type'  => 'start',
												'title' => __( 'Caching Optimization & Performance Improvement', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'To improve wishlist speed and functionality, adjust settings based on your cache plugin, hosting, and server configuration.', 'wc-wlfmc-wishlist' ),
											),
											'ajax_mode'    => array(
												'label'   => __( 'Type of ajax operations', 'wc-wlfmc-wishlist' ),
												'type'    => 'select',
												'class'   => 'select2-trigger',
												'options' => array(
													'wp_loaded' => __( 'wp loaded hook', 'wc-wlfmc-wishlist' ),
													'rest_api' => __( 'Wp rest api', 'wc-wlfmc-wishlist' ),
													'admin-ajax.php' => __( 'admin-ajax.php (Recommended)', 'wc-wlfmc-wishlist' ),
												),
												/* translators: admin url of permalink structure  */
												'desc'    => sprintf( __( 'If you select the "Wp rest api" option, the %s must be changed to "Post name".', 'wc-wlfmc-wishlist' ), sprintf( '<a href="%s" target="_blank">%s</a>', admin_url( 'options-permalink.php' ), __( 'permalink structure', 'wc-wlfmc-wishlist' ) ) ),
												'default' => 'admin-ajax.php',
											),
											'ajax_loading' => array(
												'label'   => __( 'Show loading Ajax operations', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
												'desc'    => __( 'If your site speed is slow, you should activate this feature.', 'wc-wlfmc-wishlist' ),
											),
											'is_cache_enabled' => array(
												'label'   => __( 'Cache protection', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
												'help'    => __( 'If a caching plugin or hosting caching is enabled on your website and the wishlist is not working properly, enable this option.', 'wc-wlfmc-wishlist' ),
											),
											'end-article-wishlist-optimization-settings' => array(
												'type' => 'end',
											),
											'start-article-wishlist-import-settings' => array(
												'type'  => 'start',
												'title' => __( 'Data Transfer Settings', 'wc-wlfmc-wishlist' ),
												'desc'  => '',
											),
											'export_settings' => array(
												'label'   => __( 'Export Settings', 'wc-wlfmc-wishlist' ),
												'type'    => 'button',
												'class'   => 'mct_export_file_button btn-secondary',
												'default' => __( 'Export', 'wc-wlfmc-wishlist' ),
												'help'    => __( 'This feature allows you to download a backup file of your current plugin settings. This can be useful if you want to transfer your settings to another website or keep a copy for safekeeping.', 'wc-wlfmc-wishlist' ),
												'custom_attributes' => array(
													'data-option_id' => 'wlfmc_options',
												),
											),
											'import_settings' => array(
												'label'   => __( 'Import Settings', 'wc-wlfmc-wishlist' ),
												'type'    => 'import',
												'class'   => 'wlfmc-export-settings',
												'default' => __( 'Import', 'wc-wlfmc-wishlist' ),
												'help'    => __( 'This feature allows you to upload a backup file of previously exported plugin settings. This can be useful if you need to restore your settings after updating the plugin or moving to a new website.', 'wc-wlfmc-wishlist' ),
												'custom_attributes' => array(
													'data-title'     => __( 'Select Json File', 'wc-wlfmc-wishlist' ),
													'data-button-text' => __( 'Select This File', 'wc-wlfmc-wishlist' ),
													'data-option_id' => 'wlfmc_options',
													'data-mimetypes' => 'application/json',
												),
											),
											'remove_all_data' => array(
												'label' => __( 'Remove all data', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'Uncheck , if you want to prevent data loss when deleting the plugin', 'wc-wlfmc-wishlist' ),
												'type'  => 'checkbox',
											),
											'end-article-wishlist-import-settings' => array(
												'type' => 'end',
											),
											'start-article-wishlist-advanced-settings' => array(
												'type'  => 'start',
												'doc'   => 'https://moreconvert.com/we2m',
												'title' => __( 'Advanced settings', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'These settings are not necessary and you can use them if you want.', 'wc-wlfmc-wishlist' ),
											),
											'login_url'    => array(
												'label'   => __( 'Login URL', 'wc-wlfmc-wishlist' ),
												'default' => '',
												'desc'    => __( 'This link is used in places where the user will be invited to login.', 'wc-wlfmc-wishlist' ),
												'type'    => 'url',
												'help'    => __( 'if you use default wordpress login page, Leave the field.', 'wc-wlfmc-wishlist' ),
												'custom_attributes' => array(
													'placeholder' => esc_url( wp_login_url() ),
												),
											),
											'signup_url'   => array(
												'label'   => __( 'Sign-up URL', 'wc-wlfmc-wishlist' ),
												'default' => '',
												'desc'    => __( 'This link is used in places where the user will be invited to Sign-up.', 'wc-wlfmc-wishlist' ),
												'type'    => 'url',
												'help'    => __( 'if you use default wordpress sign-up page, Leave the field.', 'wc-wlfmc-wishlist' ),
												'custom_attributes' => array(
													'placeholder' => esc_url( wp_registration_url() ),
												),
											),
											'live_chat'    => array(
												'label'   => __( 'Live support chat', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
												'help'    => __( 'With this option enabled, you can ask us your questions from the chat box inside the plugin, and we will solve your problem quickly', 'wc-wlfmc-wishlist' ),
											),
											'end-article-wishlist-advanced-settings' => array(
												'type' => 'end',
											),
										)
									),
									'appearance' => apply_filters(
										'wlfmc_global_style_settings',
										array(
											'start-article-popup-settings' => array(
												'type'  => 'start',
												'title' => __( 'Popup and Tooltip Global appearance', 'wc-wlfmc-wishlist' ),
											),
											'popup_position' => array(
												'label'   => __( 'Popup position', 'wc-wlfmc-wishlist' ),
												'type'    => 'select',
												'class'   => 'select2-trigger',
												'options' => array(
													'center-center' => __( 'Middle', 'wc-wlfmc-wishlist' ),
													'bottom-left'   => __( 'Down left', 'wc-wlfmc-wishlist' ),
													'bottom-right'  => __( 'Down right', 'wc-wlfmc-wishlist' ),
													'top-right'     => __( 'Top right', 'wc-wlfmc-wishlist' ),
													'top-left'      => __( 'Top left', 'wc-wlfmc-wishlist' ),
												),
												'default' => 'center-center',
												'help'    => __( 'Specify the position of the pop up on the website page. There are 5 modes for the pop up position. Middle mode is recommended.', 'wc-wlfmc-wishlist' ),
											),
											'popup_box_style' => array(
												'section' => 'global-settings',
												'label'   => __( 'Popup box style', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'popup_title_color' => array(
														'label' => __( 'Title color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#333',
													),
													'popup_content_color' => array(
														'label' => __( 'Content color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#333',
													),
													'popup_background_color' => array(
														'label' => __( 'Background color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#fff',
													),
													'popup_border_color'     => array(
														'label' => __( 'Border color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#c2c2c2',
													),
													'popup_border_radius'    => array(
														'label' => __( 'Border radius', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '8px',
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/border_radius.gif',
														'help' => __( 'You can define the radius for all 4 corners of the popup (look at the gif)', 'wc-wlfmc-wishlist' ),
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'popup_icon_color' => array(
														'label' => __( 'Icon color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#333',
													),
													'popup_icon_background_color' => array(
														'label' => __( 'Icon background color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#f2f2f2',
													),
												),
											),
											'tooltip_style' => array(
												'section' => 'global-settings',
												'label'   => __( 'tooltip styles', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'tooltip_custom_style'  => array(
														'label'   => __( 'Customize style', 'wc-wlfmc-wishlist' ),
														'type'    => 'switch',
														'default' => '0',
													),
													'tooltip_direction'        => array(
														'label' => __( 'direction', 'wc-wlfmc-wishlist' ),
														'type' => 'select',
														'default' => 'top',
														'options' => array(
															'top'    => __( 'Top', 'wc-wlfmc-wishlist' ),
															'bottom' => __( 'Bottom', 'wc-wlfmc-wishlist' ),
															'right'  => __( 'Right', 'wc-wlfmc-wishlist' ),
															'left'   => __( 'Left', 'wc-wlfmc-wishlist' ),
														),
														'dependencies' => array(
															'id' => 'tooltip_custom_style',
															'value' => '1',
														),
													),
													'tooltip_color'            => array(
														'label' => __( 'color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#fff',
														'dependencies' => array(
															'id' => 'tooltip_custom_style',
															'value' => '1',
														),
													),
													'tooltip_background_color' => array(
														'label' => __( 'Background color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'rgba(55, 64, 70, 0.9)',
														'dependencies' => array(
															'id' => 'tooltip_custom_style',
															'value' => '1',
														),
													),
													'tooltip_border_radius'    => array(
														'label' => __( 'border radius', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '6px',
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/border_radius.gif',
														'help' => __( 'You can define the radius for all 4 corners of the tooltip (look at the gif)', 'wc-wlfmc-wishlist' ),
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
														'dependencies' => array(
															'id' => 'tooltip_custom_style',
															'value' => '1',
														),
													),

												),
												'help'    => __( 'Tooltip gets text from button text', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc for border radius.', 'wc-wlfmc-wishlist' ),
											),
											'toast_style'  => array(
												'section' => 'global-settings',
												'label'   => __( 'Toast styles', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'enable_toast_style' => array(
														'section' => 'global-settings',
														'label'   => __( 'Toasts Style', 'wc-wlfmc-wishlist' ),
														'type'    => 'switch',
														'default' => '0',
														'parent_class'  => 'enable-for-pro',
														'custom_attributes' => array(
															'disabled' => 'true',
														),
													),
												),
												'help'    => __( 'When this setting is ON, you can customize the appearance of toast notifications for success and error messages, Otherwise, the default settings will be applied.', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Just for premium users', 'wc-wlfmc-wishlist' ),
											),
											'end-article-popup-settings' => array(
												'type' => 'end',
											),
											'start-article-text-settings' => array(
												'type'  => 'start',
												'title' => __( 'Global Text Management', 'wc-wlfmc-wishlist' ),
											),
											'action_label' => array(
												'label'   => __( '"Actions" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Actions', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'wishlist_under_table',
														'value' => 'actions',
													),
												),
											),
											'action_add_to_cart_label' => array(
												'label'   => __( '"Add to cart" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Add to cart', 'wc-wlfmc-wishlist' ),
											),
											'action_remove_label' => array(
												'label'   => __( '"Remove" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Remove', 'wc-wlfmc-wishlist' ),
											),
											'apply_label'  => array(
												'label'   => __( '"Apply" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Apply', 'wc-wlfmc-wishlist' ),
											),
											'all_add_to_cart_label' => array(
												'label'   => __( '"Add all to cart" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Add all to cart', 'wc-wlfmc-wishlist' ),
											),
											'end-article-text-settings' => array(
												'type' => 'end',
											),
											'start-article-custom-css-settings' => array(
												'type'  => 'start',
												'title' => __( 'Additional CSS', 'wc-wlfmc-wishlist' ),
											),
											'custom_css'   => array(
												'label' => __( 'Custom css', 'wc-wlfmc-wishlist' ),
												'help'  => __( 'This feature allows you to add your own custom CSS code to modify the appearance of your website. Use this feature if you want to make specific design changes that cannot be done through the plugin\'s existing styling options.', 'wc-wlfmc-wishlist' ),
												'type'  => 'css-editor',
											),
											'end-article-custom-css-settings' => array(
												'type' => 'end',
											),
										)
									),
									'share'      => apply_filters(
										'wlfmc_global_share_settings',
										array(
											'start-article-share-settings' => array(
												'type'  => 'start',
												'title' => __( 'Social Share', 'wc-wlfmc-wishlist' ),
											),
											'share_position' => array(
												'label'   => __( 'Share position', 'wc-wlfmc-wishlist' ),
												'default' => 'after_table',
												'type'    => 'select',
												'class'   => 'select2-trigger',
												'options' => array(
													'popup'     => __( 'Popup (pro)', 'wc-wlfmc-wishlist' ),
													'after_table'    => __( 'After table', 'wc-wlfmc-wishlist' ),
												),
												'disabled_options' => array(
													'popup',
												),
											),
											'share_items' => array(
												'label'   => __( 'Active share buttons', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Which social media icons show on the sharing bar?', 'wc-wlfmc-wishlist' ),
												'type'    => 'checkbox-group',
												'options' => array(
													'facebook' => __( 'Facebook', 'wc-wlfmc-wishlist' ),
													'messenger' => __( 'Facebook messenger', 'wc-wlfmc-wishlist' ),
													'twitter' => __( 'Twitter', 'wc-wlfmc-wishlist' ),
													'whatsapp' => __( 'Whatsapp', 'wc-wlfmc-wishlist' ),
													'telegram' => __( 'Telegram', 'wc-wlfmc-wishlist' ),
													'email' => __( 'Email', 'wc-wlfmc-wishlist' ),
													'copy' => __( 'Share link', 'wc-wlfmc-wishlist' ),
													'pdf'  => __( 'Download pdf', 'wc-wlfmc-wishlist' ),
												),
												'default' => array(
													'facebook',
													'messenger',
													'twitter',
													'whatsapp',
													'telegram',
													'email',
													'copy',
													'pdf',
												),
												'help'    => __( 'In what medias do you prefer your user to be able to share his / her wishlist?', 'wc-wlfmc-wishlist' ),
											),
											'product_copy' => array(
												'label'   => __( 'Product Copy', 'wc-wlfmc-wishlist' ),
												'help'    => __( 'This feature enables users to seamlessly transfer products from one list to own.', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Just for premium users', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '0',
												'parent_class' => 'enable-for-pro',
												'custom_attributes' => array(
													'disabled' => 'true',
												),
											),
											'sharing_style' => array(
												'section' => 'global-settings',
												'label'   => __( 'Sharing style', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'social_border_radius'       => array(
														'label' => __( 'Border radius', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '50%',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'social_border_color'       => array(
														'label' => __( 'Border color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'rgba(59,89,152,.1)',
													),
													'social_border_hover_color' => array(
														'label' => __( 'Border hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'rgba(59,89,152,.1)',
													),
												),
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'copy,messenger,whatsapp,telegram,twitter,facebook,email,pdf',
													),
												),
											),
											'social_color_style' => array(
												'section' => 'global-settings',
												'label'   => __( 'Sharing colors', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'facebook_color'       => array(
														'label' => __( 'Facebook', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#C71610',
														'dependencies' => array(
															array(
																'id' => 'share_items',
																'value' => 'facebook',
															),
														),
													),
													'twitter_color'       => array(
														'label' => __( 'Twitter', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#00ACEE',
														'dependencies' => array(
															array(
																'id' => 'share_items',
																'value' => 'twitter',
															),
														),
													),
													'messenger_color'       => array(
														'label' => __( 'Messenger', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#0077FF',
														'dependencies' => array(
															array(
																'id' => 'share_items',
																'value' => 'messenger',
															),
														),
													),
													'whatsapp_color'       => array(
														'label' => __( 'Whatsapp', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#4FCE5D',
														'dependencies' => array(
															array(
																'id' => 'share_items',
																'value' => 'whatsapp',
															),
														),
													),
													'telegram_color'       => array(
														'label' => __( 'Telegram', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#2AABEE',
														'dependencies' => array(
															array(
																'id' => 'share_items',
																'value' => 'telegram',
															),
														),
													),
													'email_color'       => array(
														'label' => __( 'Email', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#C71610',
														'dependencies' => array(
															array(
																'id' => 'share_items',
																'value' => 'email',
															),
														),
													),
													'pdf_color'       => array(
														'label' => __( 'Download pdf', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#FF2366',
														'dependencies' => array(
															array(
																'id' => 'share_items',
																'value' => 'pdf',
															),
														),
													),
													'copy_color'       => array(
														'label' => __( 'Copy', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#9162ff',
														'dependencies' => array(
															array(
																'id' => 'share_items',
																'value' => 'copy',
															),
														),
													),
												),
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'copy,messenger,whatsapp,telegram,twitter,facebook,email,pdf',
													),
												),
											),
											'copy_button_style' => array(
												'section' => 'global-settings',
												'label'   => __( 'Copy button colors', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'copy_button_color'       => array(
														'label' => __( 'Color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#333',
													),
													'copy_button_hover_color'       => array(
														'label' => __( 'Hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#333',
													),
													'copy_button_background_color'       => array(
														'label' => __( 'Background color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#ebebeb',
													),
													'copy_button_background_hover_color' => array(
														'label' => __( 'Background hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#ebebeb',
													),
													'copy_button_border_color'           => array(
														'label' => __( 'Border color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#ebebeb',
													),
													'copy_button_border_hover_color'     => array(
														'label' => __( 'Border hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'transparent',
													),

												),
												'dependencies' => array(
													array(
														'id' => 'share_position',
														'value' => 'popup',
													),
												),
											),
											'copy_field_style' => array(
												'section' => 'global-settings',
												'label'   => __( 'Copy field style', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'copy_field_color'       => array(
														'label' => __( 'Color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#333',
													),
													'copy_field_border_color'       => array(
														'label' => __( 'Border color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'rgba(59,89,152,.1)',
													),
													'copy_field_background_color'       => array(
														'label' => __( 'Background color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#fff',
													),
												),
												'dependencies' => array(
													array(
														'id' => 'share_position',
														'value' => 'popup',
													),
												),
											),
											'end-article-share-settings' => array(
												'type' => 'end',
											),
											// labels.
											'start-article-share-text' => array(
												'type'  => 'start',
												'title' => __( 'Share label and  tooltip custom text', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'It will show on the tooltip and labels text.', 'wc-wlfmc-wishlist' ),
											),
											'share_tooltip' => array(
												'label'   => __( 'Share tooltip text', 'wc-wlfmc-wishlist' ),
												'default' => __( 'Share', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
											),
											'socials_title' => array(
												'label'   => __( 'Sharing title', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Wishlist title used for sharing', 'wc-wlfmc-wishlist' ),
												/* translators: %s: site name */
												'default' => sprintf( __( 'My Wishlist on %s', 'wc-wlfmc-wishlist' ), get_bloginfo( 'name' ) ),
												'type'    => 'text',
												'translatable' => true,
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'twitter,facebook,email',
													),
												),
												'help'    => __( 'Enter the title that you would like to display as your ad when your user is sharing it.', 'wc-wlfmc-wishlist' ),
											),
											'share_popup_title' => array(
												'label'   => __( 'Sharing Popup Title', 'wc-wlfmc-wishlist' ),
												'default' => __( 'Share your list', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
											),
											'share_on_label' => array(
												'label'   => __( '"Share on:" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Share on:', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'copy,messenger,whatsapp,telegram,twitter,facebook,email,pdf',
													),
												),
											),
											'copy_field_label' => array(
												'label'   => __( 'Copy field label', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Or copy the link', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'copy',
													),
													array(
														'id' => 'share_position',
														'value' => 'popup',
													),
												),
											),
											'copy_button_text' => array(
												'label'   => __( '"Copy button" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Copy', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'copy',
													),
													array(
														'id' => 'share_position',
														'value' => 'popup',
													),
												),
											),
											'share_on_facebook_tooltip_label' => array(
												'label'   => __( '"Share on facebook" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Share on facebook', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'facebook',
													),
												),
											),
											'share_on_messenger_tooltip_label' => array(
												'label'   => __( '"Share with messenger" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Share with messenger', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'messenger',
													),
												),
											),
											'share_on_twitter_tooltip_label' => array(
												'label'   => __( '"Share on twitter" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Share on Twitter', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'twitter',
													),
												),
											),
											'share_on_whatsapp_tooltip_label' => array(
												'label'   => __( '"Share on whatsApp" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Share on whatsApp', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'whatsapp',
													),
												),
											),
											'share_on_telegram_tooltip_label' => array(
												'label'   => __( '"Share on Telegram" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Share on Telegram', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'telegram',
													),
												),
											),
											'share_on_email_tooltip_label' => array(
												'label'   => __( '"Share with email" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Share with email', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'email',
													),
												),
											),
											'share_on_copy_link_tooltip_label' => array(
												'label'   => __( '"Click to copy the link" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Click to copy the link', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'copy',
													),
													array(
														'id' => 'share_position',
														'value' => 'after_table',
													),
												),
											),
											'share_on_download_pdf_tooltip_label' => array(
												'label'   => __( '"Download pdf" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Download pdf', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'share_items',
														'value' => 'pdf',
													),
												),
											),
											'end-article-share-text' => array(
												'type' => 'end',
											),
										)
									),
									'marketing'  => apply_filters(
										'wlfmc_global_marketing_settings',
										array(
											'start-article-marketing-settings' => array(
												'type'  => 'start',
												'title' => __( 'Email Marketing Processing Management', 'wc-wlfmc-wishlist' ),
											),
											'email_per_hours' => array(
												'label'   => __( 'Email sent per hours', 'wc-wlfmc-wishlist' ),
												'default' => 20,
												'type'    => 'number',
												'help'    => __( 'By default, 20 emails from Wishlist email marketing options are sent to users every hour. If you want to change this number, ask your hosting support for the permissible Emails per Hour and consider emails sent from other plugins such as WooCommerce orders.', 'wc-wlfmc-wishlist' ),
											),
											'reset_sending_cycles' => array(
												'label'   => __( 'Reset Sending Cycles', 'wc-wlfmc-wishlist' ),
												'type'    => 'button',
												'class'   => 'wlfmc-reset-sending-cycles btn-secondary',
												'default' => __( 'Reset', 'wc-wlfmc-wishlist' ),
												'help'    => __( 'All previous cycle between complete automation and running a new automation will be cleared and all automation cycle rules will be cleared for all users.', 'wc-wlfmc-wishlist' ),
											),
											'end-article-marketing-settings' => array(
												'type' => 'end',
											),
											'start-article-email-settings' => array(
												'type'  => 'start',
												'title' => __( 'Email Marketing Defaults', 'wc-wlfmc-wishlist' ),
											),
											'email-from-name' => array(
												'label'   => __( 'From "name"', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'default' => wp_specialchars_decode( get_option( 'woocommerce_email_from_name' ), ENT_QUOTES ),
											),
											'email-from-address' => array(
												'label'   => __( 'From "Email address"', 'wc-wlfmc-wishlist' ),
												'type'    => 'email',
												'default' => sanitize_email( get_option( 'woocommerce_email_from_address' ) ),
											),
											'mail-type' => array(
												'label'   => __( 'Email template', 'wc-wlfmc-wishlist' ),
												'default' => 'html',
												'type'    => 'select',
												'class'   => 'select2-trigger',
												'options' => array(
													// 'plain' => __( 'Plain', 'wc-wlfmc-wishlist' ),
													'simple-template' => __( 'Simple Template', 'wc-wlfmc-wishlist' ),
													'html' => __( 'HTML Woocommerce', 'wc-wlfmc-wishlist' ),
													'mc-template' => __( 'MC Template', 'wc-wlfmc-wishlist' ),
												),

											),
											'start-article-template' => array(
												'type'  => 'start',
												'title' => __( 'Email MC template', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'You can change the details of the custom email template from here.', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													'id' => 'mail-type',
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
												'label' => __( 'Logo', 'wc-wlfmc-wishlist' ),
												'type'  => 'upload-image',
												'help'  => __( 'Upload your site logo or select it from your host', 'wc-wlfmc-wishlist' ),

											),
											'email-template-column-1-child-1-end' => array(
												'type' => 'column-end',
											),
											'email-template-column-1-child-2-start' => array(
												'type'  => 'column-start',
												'class' => 'flexible-rows',
											),
											'email-template-avatar' => array(
												'label' => __( 'Avatar', 'wc-wlfmc-wishlist' ),
												'type'  => 'upload-image',
												'help'  => __( 'Upload an image of your email sender to show on the email', 'wc-wlfmc-wishlist' ),
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
												'default' => __( 'Ana Ride', 'wc-wlfmc-wishlist' ),
											),
											'email-template-customer-job' => array(
												'label'   => __( 'Role of email sender', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'default' => __( 'Customer Manager', 'wc-wlfmc-wishlist' ),
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
												'label' => __( 'Social links', 'wc-wlfmc-wishlist' ),
												'type'  => 'repeater',
												'add_new_label' => __( 'Add another social link', 'wc-wlfmc-wishlist' ),
												'limit' => 5,
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
														'default' => 'default',
													),
													'email-template-social-size'  => array(
														'label' => __( 'Image size', 'wc-wlfmc-wishlist' ),
														'type' => 'number',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
														'default' => '34',
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
														'default' => 'color',
													),

												),
											),
											'email-template-social-open-in-new-tab' => array(
												'label' => '',
												'type'  => 'checkbox',
												'desc'  => __( 'Open links in new tab', 'wc-wlfmc-wishlist' ),
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
											'end-article-email-text' => array(
												'type' => 'end',
											),
										)
									),
								),
							),
						)
					),
					'title'          => __( 'MoreConvert', 'wc-wlfmc-wishlist' ),
					'logo'           => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/logo.svg" width="45" height="40"  alt="logo"/>',
					'header_buttons' => wlfmc_get_admin_header_buttons(),
					'header_menu'    => wlfmc_get_admin_header_menu(),
					'sidebar'        => wlfmc_get_admin_sidebar(),
					'type'           => 'setting-type',
					'ajax_saving'    => true,
					'id'             => 'wlfmc_options',
				);

				$this->wishlist_options = array(
					'options'        => apply_filters(
						'wlfmc_admin_options',
						array(
							'button-display' => array(
								'tabs'   => array(
									'general'       => __( 'General', 'wc-wlfmc-wishlist' ),
									'button'        => __( 'Wishlist Button', 'wc-wlfmc-wishlist' ),
									'page-settings' => __( 'Wishlist Page', 'wc-wlfmc-wishlist' ),
									'counter'       => __( 'Counter', 'wc-wlfmc-wishlist' ),
									'text'          => __( 'Text Customization', 'wc-wlfmc-wishlist' ),
								),
								'fields' => array(
									'general'       => apply_filters(
										'wlfmc_wishlist_general_settings',
										array(
											'start-article-display-settings' => array(
												'type'    => 'start',
												'title'   => __( 'Display Settings', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'You only need to set them once after installing the plugin.', 'wc-wlfmc-wishlist' ),
												'youtube' => 'https://moreconvert.com/rv5f',
												'doc'     => 'https://moreconvert.com/0dfs',
											),
											'wishlist_enable' => array(
												'label'   => __( 'Enable wishlist', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
											),
											'who_can_see_wishlist_options' => array(
												'label'   => __( 'Enable Wishlist for', 'wc-wlfmc-wishlist' ),
												'type'    => 'select',
												'class'   => 'select2-trigger',
												'help'    => __( 'Do you want the wishlist to be visible only to members or all users of your website', 'wc-wlfmc-wishlist' ),
												'options' => array(
													'all' => __( 'All users', 'wc-wlfmc-wishlist' ),
													'users' => __( 'Logged in users', 'wc-wlfmc-wishlist' ),
												),
												'default' => 'all',
												'dependencies' => array(
													'id' => 'wishlist_enable',
													'value' => '1',
												),
											),
											'force_user_to_login' => array(
												'label'   => __( 'Require login or register', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'If you want a comprehensive and accurate analytics section, make the login require.', 'wc-wlfmc-wishlist' ),
												'help'    => __( 'Selecting the user membership to use the wishlist is require or optional', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'dependencies' => array(
													array(
														'id' => 'who_can_see_wishlist_options',
														'value' => 'all',
													),
													array(
														'id' => 'wishlist_enable',
														'value' => '1',
													),

												),
												'default' => '0',

											),
											'click_wishlist_button_behavior' => array(
												'label'   => __( 'After clicking button', 'wc-wlfmc-wishlist' ),
												'type'    => 'select',
												'class'   => 'select2-trigger',
												'help'    => __( 'Choose what happens after your user clicks the Add to wishlist button', 'wc-wlfmc-wishlist' ),
												'options' => array(
													'open-popup'   => __( 'Open the pop up', 'wc-wlfmc-wishlist' ),
													'just-add'     => __( 'Just add it to Wishlist', 'wc-wlfmc-wishlist' ),
													'add-redirect' => __( 'Add and redirect to Wishlist page', 'wc-wlfmc-wishlist' ),
												),
												'default' => 'open-popup',
												'dependencies' => array(
													'id' => 'wishlist_enable',
													'value' => '1',
												),
											),
											'after_second_click' => array(
												'section' => 'button-display',
												'label'   => __( 'Second click action', 'wc-wlfmc-wishlist' ),
												'type'    => 'select',
												'class'   => 'select2-trigger',
												'options' => array(
													'wishlist' => __( 'Go to the wishlist page', 'wc-wlfmc-wishlist' ),
													'remove'   => __( 'Remove from wishlist', 'wc-wlfmc-wishlist' ),
													'error'   => __( 'Display the error', 'wc-wlfmc-wishlist' ),
												),
												'help'    => __( 'After the product is added to the wishlist, What should happen if the user clicks the wishlist button again?', 'wc-wlfmc-wishlist' ),
												'default' => 'remove',
												'dependencies' => array(
													'id' => 'wishlist_enable',
													'value' => '1',
												),
											),
											'enable_for_outofstock_product' => array(
												'label'   => __( 'Show for out-of-stock', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Enable the “ add to wishlist” button for out-of-stock products', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
												'help'    => __( 'By activating this option, the Add to wishlist button will be displayed in out of stock products.', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													'id' => 'wishlist_enable',
													'value' => '1',
												),
											),
											'end-article-display-settings' => array(
												'type' => 'end',
											),
											'start-article-popup-setting' => array(
												'type'  => 'start',
												'title' => __( 'Popup Settings', 'wc-wlfmc-wishlist' ),
												'doc'   => 'https://moreconvert.com/b6gi',
												'dependencies' => array(
													array(
														'id' => 'click_wishlist_button_behavior',
														'value' => 'open-popup',
													),
													array(
														'id' => 'wishlist_enable',
														'value' => '1',
													),

												),
												'desc'  => __( 'Customize the popup appearance and content when the user clicks the list button.', 'wc-wlfmc-wishlist' ),
											),
											'popup_size'  => array(
												'label'   => __( 'Popup size', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'If you want to use a photo in the pop up, choose large size for it.', 'wc-wlfmc-wishlist' ),
												'type'    => 'radio',
												'options' => array(
													'small' => __( 'Small', 'wc-wlfmc-wishlist' ),
													'large' => __( 'Large', 'wc-wlfmc-wishlist' ),
												),
												'dependencies' => array(
													'id' => 'click_wishlist_button_behavior',
													'value' => 'open-popup',
												),
												'default' => 'large',
												'help'    => __( 'Specify the size of the pop up. There are two modes, small and large. In large mode you can use the product photo for pop up.', 'wc-wlfmc-wishlist' ),
											),
											'use_featured_image' => array(
												'label'   => __( 'Use featured image for pop up', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Enable if use featured image for pop up', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'dependencies' => array(
													array(
														'id'    => 'popup_size',
														'value' => 'large',
													),
													array(
														'id'    => 'click_wishlist_button_behavior',
														'value' => 'open-popup',
													),
												),
												'default' => '0',
											),
											'popup_image' => array(
												'label' => __( 'Popup image', 'wc-wlfmc-wishlist' ),
												'type'  => 'upload-image',
												'dependencies' => array(
													array(
														'id'    => 'popup_size',
														'value' => 'large',
													),
													array(
														'id'    => 'click_wishlist_button_behavior',
														'value' => 'open-popup',
													),
													array(
														'id'    => 'use_featured_image',
														'value' => '0',
													),
												),
												'desc'  => __( 'The maximum image size should be 400 * 400 pixels.', 'wc-wlfmc-wishlist' ),
											),
											'popup_image_size' => array(
												'label'   => __( 'Popup image Size', 'wc-wlfmc-wishlist' ),
												'type'    => 'select',
												'class'   => 'select2-trigger',
												'options' => array(
													'thumbnail' => __( 'Thumbnail', 'wc-wlfmc-wishlist' ),
													'medium'    => __( 'Medium', 'wc-wlfmc-wishlist' ),
													'large'     => __( 'Large', 'wc-wlfmc-wishlist' ),
													'manual'    => __( 'Manual', 'wc-wlfmc-wishlist' ),
												),
												'dependencies' => array(
													array(
														'id'    => 'popup_size',
														'value' => 'large',
													),
													array(
														'id'    => 'click_wishlist_button_behavior',
														'value' => 'open-popup',
													),
												),
												'default' => 'medium',
												'help'    => __( 'The maximum image size should be 400 * 400 pixels.', 'wc-wlfmc-wishlist' ),
											),
											'popup_image_manual_sizes' => array(
												'section' => 'button-display',
												'label'   => __( 'Popup image sizes', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'popup_image_width'  => array(
														'label' => __( 'Image width', 'wc-wlfmc-wishlist' ),
														'type'  => 'number',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),

													),
													'popup_image_height' => array(
														'label' => __( 'Image height', 'wc-wlfmc-wishlist' ),
														'type'  => 'number',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),

													),

												),
												'dependencies' => array(
													array(
														'id'    => 'popup_size',
														'value' => 'large',
													),
													array(
														'id'    => 'click_wishlist_button_behavior',
														'value' => 'open-popup',
													),
													array(
														'id'    => 'popup_image_size',
														'value' => 'manual',
													),

												),

											),
											'popup_title' => array(
												'label'   => __( 'Popup title', 'wc-wlfmc-wishlist' ),
												'default' => __( 'Added to Wishlist', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'dependencies' => array(
													'id' => 'click_wishlist_button_behavior',
													'value' => 'open-popup',
												),
												'desc'    => __( 'You can use the following placeholders: <code>{product_name}</code>,<code>{product_price}</code>', 'wc-wlfmc-wishlist' ),
												'help'    => __( 'Select the title of the pop up. No need to change and you can use the default text.', 'wc-wlfmc-wishlist' ),
											),
											'popup_content' => array(
												'label'   => __( 'Popup content', 'wc-wlfmc-wishlist' ),
												'type'    => 'wp-editor',
												'translatable' => true,
												'desc'    => __( 'You can use the following placeholders: <code>{product_name}</code>,<code>{product_price}</code>', 'wc-wlfmc-wishlist' ),
												'default' => __( 'See your favorite product on Wishlist', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													'id' => 'click_wishlist_button_behavior',
													'value' => 'open-popup',
												),
												'custom_attributes' => array(
													'style' => 'max-width:100%;width:100%',
												),
												'help'    => __( 'Select the content of the pop up. No need to change and you can use the default text.', 'wc-wlfmc-wishlist' ),
											),
											'popup_buttons' => array(
												'label'   => __( 'Add pop up button', 'wc-wlfmc-wishlist' ),
												'type'    => 'add-button',
												'translatable' => true,
												'links'   => array(
													'back' => __( 'Close pop up', 'wc-wlfmc-wishlist' ),
													'signup-login' => __( 'Sign-up or login', 'wc-wlfmc-wishlist' ),
													'wishlist' => __( 'Wishlist', 'wc-wlfmc-wishlist' ),
													'custom-link' => __( 'Custom url', 'wc-wlfmc-wishlist' ),
												),
												'default' => array(
													array(
														'label'             => __( 'View My Wishlist', 'wc-wlfmc-wishlist' ),
														'background'        => '#555555',
														'background-hover'  => '#555555',
														'label-color'       => '#ffffff',
														'label-hover-color' => '#ffffff',
														'border-radius'     => '2px',
														'link'              => 'wishlist',
														'custom-link'       => '',
													),
													array(
														'label' => __( 'Close', 'wc-wlfmc-wishlist' ),
														'background' => 'rgba(0,0,0,0)',
														'background-hover' => 'rgba(0,0,0,0)',
														'label-color' => '#7e7e7e',
														'label-hover-color' => '#7e7e7e',
														'border-radius' => '2px',
														'link' => 'back',
														'custom-link' => '',
													),
												),
												'helps'   => array(
													'border-radius' => array(
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/border_radius.gif',
														'help' => __( 'You can define the radius for all 4 corners of the button (look at the gif)', 'wc-wlfmc-wishlist' ),
													),
												),
												'limit'   => 3,
												'dependencies' => array(
													'id' => 'click_wishlist_button_behavior',
													'value' => 'open-popup',
												),
												'help'    => __( 'These settings are related to the pop up button. You can consider more than one button for your pop up.', 'wc-wlfmc-wishlist' ),
											),
											'popup_appearance' => array(
												'label' => __( 'Popup Appearance', 'wc-wlfmc-wishlist' ),
												'type'  => 'html',
												'html'  => '<a class="btn-secondary" href="' . esc_url(
													add_query_arg(
														array(
															'page' => 'mc-global-settings',
															'tab'  => 'appearance',
														),
														admin_url( 'admin.php' )
													)
												) . '" target="_blank">' . __( 'Change appearance', 'wc-wlfmc-wishlist' ) . '</a>',
											),
											'end-article-popup-setting' => array(
												'type' => 'end',
											),
										)
									),
									'button'        => apply_filters(
										'wlfmc_wishlist_button_settings',
										array(
											// Products single.
											'start-article-single' => array(
												'type'    => 'start',
												'title'   => __( 'Single Product page', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Design your wishlist button on your product page.', 'wc-wlfmc-wishlist' ),
												'youtube' => 'https://moreconvert.com/6dhb',
												'doc'     => 'https://moreconvert.com/awhv',
												'dependencies' => array(
													'id' => 'wishlist_enable',
													'value' => '1',
												),
											),
											'wishlist_button_position' => array(
												'label'   => __( 'Button position', 'wc-wlfmc-wishlist' ),
												'type'    => 'select',
												'class'   => 'select2-trigger',
												'options' => apply_filters(
													'wlfmc_single_position_options',
													array(
														'before_add_to_cart' => __( 'Before "add to cart" form', 'wc-wlfmc-wishlist' ),
														'after_add_to_cart' => __( 'After "add to cart" form', 'wc-wlfmc-wishlist' ),
														'before_add_to_cart_button' => __( 'Before "add to cart" button', 'wc-wlfmc-wishlist' ),
														'after_add_to_cart_button' => __( 'After "add to cart" button', 'wc-wlfmc-wishlist' ),
														'image_top_left' => __( 'On image - top left', 'wc-wlfmc-wishlist' ),
														'image_top_right' => __( 'On image - top right', 'wc-wlfmc-wishlist' ),
														'image_bottom_left' => __( 'On image - bottom left', 'wc-wlfmc-wishlist' ),
														'image_bottom_right' => __( 'On image - bottom right', 'wc-wlfmc-wishlist' ),
														'summary' => __( 'After summary', 'wc-wlfmc-wishlist' ),
														'shortcode' => __( 'Use shortcode', 'wc-wlfmc-wishlist' ),
													)
												),
												'help'    => __( 'button position on the product page', 'wc-wlfmc-wishlist' ),
												'default' => 'image_top_left',
											),
											'shortcode_button' => array(
												'label'   => __( 'Shortcode button', 'wc-wlfmc-wishlist' ),
												'type'    => 'copy-text',
												'default' => '[wlfmc_add_to_wishlist]',
												'desc'    => '<a href="https://moreconvert.com/6zox" target="_blank">' . __( 'learn more on how to use it.', 'wc-wlfmc-wishlist' ) . '</a>',
												'dependencies' => array(
													'id' => 'wishlist_button_position',
													'value' => 'shortcode',
												),
												'help'    => __( 'Use this shortcode to specify a custom position. Just copy this shortcode wherever you want the button to be displayed.', 'wc-wlfmc-wishlist' ),
											),
											'button_type_single' => array(
												'label'   => __( 'Button type', 'wc-wlfmc-wishlist' ),
												'type'    => 'radio',
												'options' => array(
													'icon' => __( 'Icon only', 'wc-wlfmc-wishlist' ),
													'text' => __( 'Text only', 'wc-wlfmc-wishlist' ),
													'both' => __( 'Icon and text', 'wc-wlfmc-wishlist' ),
												),
												'default' => 'icon',
												'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/button_type.gif',
												'help'    => __( 'Select the button appearance. It is better to test different modes and choose the most compatible with the theme.', 'wc-wlfmc-wishlist' ),
											),
											'button_theme_single' => array(
												'label'   => __( 'Default button style', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Enable if use theme default styles for icon and text', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '0',
												'help'    => __( 'Do you want the button settings to be automatically aligned with your theme?', 'wc-wlfmc-wishlist' ),
											),
											'icon_name_single' => array(
												'label'   => __( 'Button icon', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'This icon is also used as a pop-up icon.', 'wc-wlfmc-wishlist' ),
												'type'    => 'select-icon',
												'class'   => 'select2-trigger',
												'options' => wlfmc_get_icon_names( 'wishlist', true, true ),
												'default' => 'heart',
											),
											'icon_svg_single' => array(
												'label' => __( 'Custom icon (svg)', 'wc-wlfmc-wishlist' ),
												'type'  => 'textarea',
												'custom_attributes' => array(
													'cols' => '120',
													'rows' => '3',
												),
												'dependencies' => array(
													array(
														'id' => 'icon_name_single',
														'value' => 'custom',
													),
												),
												'desc'  => __( 'Open your SVG file on a notepad and copy the code on this field.Please input the SVG code with your desired color.', 'wc-wlfmc-wishlist' ),
											),
											'icon_svg_added_single' => array(
												'label' => __( '"product added" custom icon (svg)', 'wc-wlfmc-wishlist' ),
												'type'  => 'textarea',
												'custom_attributes' => array(
													'cols' => '120',
													'rows' => '3',
												),
												'dependencies' => array(
													array(
														'id' => 'icon_name_single',
														'value' => 'custom',
													),
												),
												'desc'  => __( 'Open your SVG file on a notepad and copy the code on this field.Please input the SVG code with your desired color.', 'wc-wlfmc-wishlist' ),
											),
											'button_icon_single' => array(
												'section' => 'button-display',
												'label'   => __( 'Icon style', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'icon_font_size_single'   => array(
														'label' => __( 'Font size', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '15px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'icon_color_single'       => array(
														'label' => __( 'Color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'rgb(230,126,34)',
													),
													'icon_hover_color_single' => array(
														'label' => __( 'Hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'rgb(81,81,81)',
													),
												),
												'dependencies' => array(
													array(
														'id' => 'button_theme_single',
														'value' => '0',
													),
													array(
														'id' => 'button_type_single',
														'value' => 'icon,both',
													),
												),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc.', 'wc-wlfmc-wishlist' ),
											),
											'button_text_single' => array(
												'section' => 'button-display',
												'label'   => __( 'Text style', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(

													'text_font_size_single'   => array(
														'label' => __( 'Font size', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => 'inherit',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'text_color_single'       => array(
														'label' => __( 'Color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'rgb(230,126,34)',
													),
													'text_hover_color_single' => array(
														'label' => __( 'Hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'rgb(81,81,81)',
													),
												),
												'dependencies' => array(
													array(
														'id' => 'button_theme_single',
														'value' => '0',
													),
													array(
														'id' => 'button_type_single',
														'value' => 'text,both',
													),
												),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc.', 'wc-wlfmc-wishlist' ),

											),
											'button_colors_single' => array(
												'section' => 'button-display',
												'label'   => __( 'Button colors', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'button_background_color_single'       => array(
														'label' => __( 'Background color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'transparent',
													),
													'button_background_hover_color_single' => array(
														'label' => __( 'Background hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'transparent',
													),
													'button_border_color_single'           => array(
														'label' => __( 'Border color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'transparent',
													),
													'button_border_hover_color_single'     => array(
														'label' => __( 'Border hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'transparent',
													),

												),
												'dependencies' => array(
													array(
														'id' => 'button_theme_single',
														'value' => '0',
													),
												),
											),
											'button_sizes_single' => array(
												'section' => 'button-display',
												'label'   => __( 'Button sizes', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'button_border_width_single'  => array(
														'label' => __( 'Border width', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '1px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'button_width_single'         => array(
														'label' => __( 'Width', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '45px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
														'dependencies' => array(
															array(
																'id'    => 'button_type_single',
																'value' => 'icon',
															),
														),
													),
													'button_height_single'        => array(
														'label' => __( 'Height', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '45px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'button_border_radius_single' => array(
														'label' => __( 'Border radius', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '5px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/border_radius.gif',
														'help' => __( 'You can define the radius for all 4 corners of the button (look at the gif)', 'wc-wlfmc-wishlist' ),
													),
													'button_margin_single'        => array(
														'label' => __( 'Margin', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '0px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/wishlist-margin.gif',
														'help' => __( 'You can define the margin for all 4 side of the button. to move the button left, put the margin like 0px 0px 0px -15px', 'wc-wlfmc-wishlist' ),
													),

												),
												'dependencies' => array(
													array(
														'id' => 'button_theme_single',
														'value' => '0',
													),
												),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc.', 'wc-wlfmc-wishlist' ),
											),
											'separator_single' => array(
												'section' => 'button-display',
												'label'   => __( 'Separate icon and text', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'separate_icon_and_text_single' => array(
														'label' => __( 'Activation', 'wc-wlfmc-wishlist' ),
														'type' => 'switch',
														'default' => '0',
													),
													'separator_color_single'        => array(
														'label' => __( 'Separator color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'dependencies' => array(
															array(
																'id'    => 'button_type_single',
																'value' => 'both',
															),
															array(
																'id'    => 'separate_icon_and_text_single',
																'value' => '1',
															),
														),

													),

												),
												'desc'    => __( 'Separate icon and text boxes with a vertical line', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													'id' => 'button_type_single',
													'value' => 'both',
												),
											),
											'button_tooltip_single' => array(
												'section' => 'button-display',
												'label'   => __( 'Button tooltip styles', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'enable_tooltip_single'           => array(
														'label' => __( 'Activation', 'wc-wlfmc-wishlist' ),
														'type' => 'switch',
														'default' => '0',
													),
													'tooltip_direction_single'        => array(
														'label' => __( 'direction', 'wc-wlfmc-wishlist' ),
														'type' => 'select',
														'default' => 'top',
														'options' => array(
															'top'    => __( 'Top', 'wc-wlfmc-wishlist' ),
															'bottom' => __( 'Bottom', 'wc-wlfmc-wishlist' ),
															'right'  => __( 'Right', 'wc-wlfmc-wishlist' ),
															'left'   => __( 'Left', 'wc-wlfmc-wishlist' ),
														),
														'dependencies' => array(
															array(
																'id'    => 'enable_tooltip_single',
																'value' => '1',
															),
														),
													),

												),
												'dependencies' => array(
													array(
														'id' => 'button_type_single',
														'value' => 'icon',
													),
												),
												'help'    => __( 'Tooltip gets text from button text', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc for border radius.', 'wc-wlfmc-wishlist' ),
											),
											'end-article-single' => array(
												'type' => 'end',
											),
											'start-article-loop' => array(
												'type'  => 'start',
												'title' => __( 'Product Listing', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'Design your wishlist button on your shop page and other loops.', 'wc-wlfmc-wishlist' ),
												'doc'   => 'https://moreconvert.com/2798',
												'dependencies' => array(
													'id' => 'wishlist_enable',
													'value' => '1',
												),
											),
											// Products loop.
											'show_on_loop' => array(
												'label'   => __( 'Show "add to Wishlist" in listings', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Enable the "add to Wishlist" feature in WooCommerce products\' listing', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
												'help'    => __( 'By activating this option, the Add to wishlist button will be displayed in all lists.', 'wc-wlfmc-wishlist' ),
											),
											'loop_position' => array(
												'label'   => __( 'Button position in listings', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Choose where to show "Add to Wishlist" button or link in WooCommerce products\' listing.', 'wc-wlfmc-wishlist' ),
												'default' => 'image_top_right',
												'type'    => 'select',
												'class'   => 'select2-trigger',
												'options' => apply_filters(
													'wlfmc_loop_position_options',
													array(
														'image_top_left'     => __( 'On image - top left', 'wc-wlfmc-wishlist' ),
														'image_top_right'    => __( 'On image - top right', 'wc-wlfmc-wishlist' ),
														'before_add_to_cart' => __( 'Before "add to cart" button', 'wc-wlfmc-wishlist' ),
														'after_add_to_cart'  => __( 'After "add to cart" button', 'wc-wlfmc-wishlist' ),
														'shortcode'          => __( 'Use shortcode', 'wc-wlfmc-wishlist' ),
													)
												),
												'dependencies' => array(
													'id' => 'show_on_loop',
													'value' => '1',
												),
												'help'    => __( 'Select the button position to view in the lists. Preferably similar to the button position on the product page.', 'wc-wlfmc-wishlist' ),
											),
											'loop_shortcode_button' => array(
												'label'   => __( 'Shortcode button', 'wc-wlfmc-wishlist' ),
												'type'    => 'copy-text',
												'default' => '[wlfmc_add_to_wishlist]',
												'desc'    => '<a href="https://moreconvert.com/6zox" target="_blank">' . __( 'learn more on how to use it.', 'wc-wlfmc-wishlist' ) . '</a>',
												'dependencies' => array(
													array(
														'id' => 'show_on_loop',
														'value' => '1',
													),
													array(
														'id' => 'loop_position',
														'value' => 'shortcode',
													),
												),
											),
											'show_on_gutenberg' => array(
												'label'   => __( 'Show "add to Wishlist" in gutenberg listings', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Enable the "add to Wishlist" feature in WooCommerce products\' gutenberg listings', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
												'help'    => __( 'By activating this option, the Add to wishlist button will be displayed in all gutenberg lists.', 'wc-wlfmc-wishlist' ),
											),
											'gutenberg_position' => array(
												'label'   => __( 'Button position in gutenberg listings', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Choose where to show "Add to Wishlist" button or link in WooCommerce products\' gutenberg listings.', 'wc-wlfmc-wishlist' ),
												'default' => 'image_top_right',
												'type'    => 'select',
												'class'   => 'select2-trigger',
												'options' => array(
													'image_top_left'     => __( 'On image - top left', 'wc-wlfmc-wishlist' ),
													'image_top_right'    => __( 'On image - top right', 'wc-wlfmc-wishlist' ),
													'after_title'        => __( 'After title', 'wc-wlfmc-wishlist' ),
													'before_price'       => __( 'Before price', 'wc-wlfmc-wishlist' ),
													'after_price'        => __( 'After price', 'wc-wlfmc-wishlist' ),
													'before_add_to_cart' => __( 'Before "add to cart" button', 'wc-wlfmc-wishlist' ),
													'after_add_to_cart'  => __( 'After "add to cart" button', 'wc-wlfmc-wishlist' ),
												),
												'dependencies' => array(
													'id' => 'show_on_gutenberg',
													'value' => '1',
												),
												'help'    => __( 'Select the button position to view in the gutenberg lists. Preferably similar to the button position on the product page.', 'wc-wlfmc-wishlist' ),
											),
											'button_type_loop' => array(
												'label'   => __( 'Button type', 'wc-wlfmc-wishlist' ),
												'type'    => 'radio',
												'options' => array(
													'icon' => __( 'Icon only', 'wc-wlfmc-wishlist' ),
													'text' => __( 'Text only', 'wc-wlfmc-wishlist' ),
													'both' => __( 'Icon and text', 'wc-wlfmc-wishlist' ),
												),
												'default' => 'icon',
												'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/button_type.gif',
												'help'    => __( 'Select the button appearance. It is better to test different modes and choose the most compatible with the theme.', 'wc-wlfmc-wishlist' ),

											),
											'button_theme_loop' => array(
												'label'   => __( 'Default button style', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Enable if use theme default styles for icon and text', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '0',
											),
											'icon_name_loop' => array(
												'label'   => __( 'Button icon', 'wc-wlfmc-wishlist' ),
												'type'    => 'select-icon',
												'class'   => 'select2-trigger',
												'options' => wlfmc_get_icon_names( 'wishlist', true, true ),
												'default' => 'heart',
												'dependencies' => array(
													array(
														'id' => 'button_type_loop',
														'value' => 'icon,both',
													),
												),
											),
											'icon_svg_loop' => array(
												'label' => __( 'Custom icon (svg)', 'wc-wlfmc-wishlist' ),
												'type'  => 'textarea',
												'custom_attributes' => array(
													'cols' => '120',
													'rows' => '3',
												),
												'dependencies' => array(
													array(
														'id' => 'button_type_loop',
														'value' => 'icon,both',
													),
													array(
														'id' => 'icon_name_loop',
														'value' => 'custom',
													),
												),
												'desc'  => __( 'Open your SVG file on a notepad and copy the code on this field.Please input the SVG code with your desired color.', 'wc-wlfmc-wishlist' ),
											),
											'icon_svg_added_loop' => array(
												'label' => __( '"product added" custom icon (svg)', 'wc-wlfmc-wishlist' ),
												'type'  => 'textarea',
												'custom_attributes' => array(
													'cols' => '120',
													'rows' => '3',
												),
												'dependencies' => array(
													array(
														'id' => 'button_type_loop',
														'value' => 'icon,both',
													),
													array(
														'id' => 'icon_name_loop',
														'value' => 'custom',
													),
												),
												'desc'  => __( 'Open your SVG file on a notepad and copy the code on this field.Please input the SVG code with your desired color.', 'wc-wlfmc-wishlist' ),
											),
											'button_icon_loop' => array(
												'section' => 'button-display',
												'label'   => __( 'Icon style', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'icon_font_size_loop'   => array(
														'label' => __( 'Font size', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '15px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'icon_color_loop'       => array(
														'label' => __( 'Color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'rgb(230,126,34)',
													),
													'icon_hover_color_loop' => array(
														'label' => __( 'Hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'rgb(81,81,81)',
													),
												),
												'dependencies' => array(
													array(
														'id' => 'button_theme_loop',
														'value' => '0',
													),
													array(
														'id' => 'button_type_loop',
														'value' => 'icon,both',
													),
												),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc.', 'wc-wlfmc-wishlist' ),
											),
											'button_text_loop' => array(
												'section' => 'button-display',
												'label'   => __( 'Text style', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(

													'text_font_size_loop'   => array(
														'label' => __( 'Font size', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => 'inherit',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'text_color_loop'       => array(
														'label' => __( 'Color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'rgb(230,126,34)',
													),
													'text_hover_color_loop' => array(
														'label' => __( 'Hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'rgb(81,81,81)',
													),
												),
												'dependencies' => array(
													array(
														'id' => 'button_theme_loop',
														'value' => '0',
													),
													array(
														'id' => 'button_type_loop',
														'value' => 'text,both',
													),
												),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc.', 'wc-wlfmc-wishlist' ),

											),
											'button_colors_loop' => array(
												'section' => 'button-display',
												'label'   => __( 'Button color', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'button_background_color_loop'       => array(
														'label' => __( 'Background color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'transparent',
													),
													'button_background_hover_color_loop' => array(
														'label' => __( 'Background hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'transparent',
													),
													'button_border_color_loop'           => array(
														'label' => __( 'Border color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'transparent',
													),
													'button_border_hover_color_loop'     => array(
														'label' => __( 'Border hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'transparent',
													),

												),
												'dependencies' => array(
													array(
														'id' => 'button_theme_loop',
														'value' => '0',
													),
												),
											),
											'button_sizes_loop' => array(
												'section' => 'button-display',
												'label'   => __( 'Button sizes', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'button_border_width_loop'  => array(
														'label' => __( 'Border width', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '1px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'button_width_loop'         => array(
														'label' => __( 'Width', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '45px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
														'dependencies' => array(
															array(
																'id'    => 'button_type_loop',
																'value' => 'icon',
															),
														),
													),
													'button_height_loop'        => array(
														'label' => __( 'Height', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '45px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'button_border_radius_loop' => array(
														'label' => __( 'Border radius', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '5px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/border_radius.gif',
														'help' => __( 'You can define the radius for all 4 corners of the button (look at the gif)', 'wc-wlfmc-wishlist' ),
													),
													'button_margin_loop'        => array(
														'label' => __( 'Margin', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '0px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/wishlist-margin.gif',
														'help' => __( 'You can define the margin for all 4 side of the button. to move the button left, put the margin like 0px 0px 0px -15px', 'wc-wlfmc-wishlist' ),
													),

												),
												'dependencies' => array(
													array(
														'id' => 'button_theme_loop',
														'value' => '0',
													),
												),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc.', 'wc-wlfmc-wishlist' ),
											),
											'separator_loop' => array(
												'section' => 'button-display',
												'label'   => __( 'Separate icon and text', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'separate_icon_and_text_loop' => array(
														'label' => __( 'Activation', 'wc-wlfmc-wishlist' ),
														'type' => 'switch',
														'default' => '0',
													),
													'separator_color_loop'        => array(
														'label' => __( 'Separator color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'dependencies' => array(
															array(
																'id'    => 'button_type_loop',
																'value' => 'both',
															),
															array(
																'id'    => 'separate_icon_and_text_loop',
																'value' => '1',
															),
														),

													),

												),
												'desc'    => __( 'Separate icon and text boxes with a vertical line', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													'id' => 'button_type_loop',
													'value' => 'both',
												),
											),
											'button_tooltip_loop' => array(
												'section' => 'button-display',
												'label'   => __( 'Button tooltip', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'enable_tooltip_loop'           => array(
														'label' => __( 'Activation', 'wc-wlfmc-wishlist' ),
														'type' => 'switch',
														'default' => '0',
													),
													'tooltip_direction_loop'        => array(
														'label' => __( 'direction', 'wc-wlfmc-wishlist' ),
														'type' => 'select',
														'default' => 'top',
														'options' => array(
															'top'    => __( 'Top', 'wc-wlfmc-wishlist' ),
															'bottom' => __( 'Bottom', 'wc-wlfmc-wishlist' ),
															'right'  => __( 'Right', 'wc-wlfmc-wishlist' ),
															'left'   => __( 'Left', 'wc-wlfmc-wishlist' ),
														),
														'dependencies' => array(
															array(
																'id'    => 'enable_tooltip_loop',
																'value' => '1',
															),
														),
													),

												),
												'dependencies' => array(
													array(
														'id' => 'button_type_loop',
														'value' => 'icon',
													),
												),
												'help'    => __( 'Tooltip gets text from button text', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc for border radius.', 'wc-wlfmc-wishlist' ),
											),
											'end-article-loop' => array(
												'type' => 'end',
											),

										)
									),
									'page-settings' => apply_filters(
										'wlfmc_wishlist_page_settings',
										array(
											'start-article-page-settings' => array(
												'type'    => 'start',
												'title'   => __( 'Page Settings', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Choose details of your wishlist page.', 'wc-wlfmc-wishlist' ),
												'youtube' => 'https://moreconvert.com/g03y',
												'doc'     => 'https://moreconvert.com/uat1',
												'dependencies' => array(
													'id' => 'wishlist_enable',
													'value' => '1',
												),
											),
											'wishlist_user_page' => array(
												'label'   => __( 'User\'s Wishlist Page Options', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'it will show for site logged in users when they want to see their wishlists.', 'wc-wlfmc-wishlist' ),
												'type'    => 'select',
												'default' => 'myaccount-page',
												'options' => array(
													'myaccount-page' => __( 'Display on WooCommerce My Account', 'wc-wlfmc-wishlist' ),
													'quest-page' => __( 'Display on the Guest List Page', 'wc-wlfmc-wishlist' ),
													'custom-panel' => __( 'Display on a custom user panel', 'wc-wlfmc-wishlist' ),
												),
											),
											'wishlist_endpoint' => array(
												'label'   => __( 'WooCommerce My Account Wishlist page slug', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'default' => 'wlfmc-wishlist',
												'class'   => 'validate',
												'custom_attributes' => array(
													'pattern'    => '^[a-z0-9]+(?:-[a-z0-9]+)*$',
													'data-error' => __( 'Invalid format. Use letters, numbers, and hyphens (-) only, with no spaces or other special characters.', 'wc-wlfmc-wishlist' ),
												),
												'dependencies' => array(
													'id' => 'wishlist_user_page',
													'value' => 'myaccount-page',
												),
												/* translators: admin url of permalink structure */
												'desc'    => sprintf( __( 'Please update the %s if the requested page encounters a 404 error.', 'wc-wlfmc-wishlist' ), sprintf( '<a href="%s" target="_blank">%s</a>', admin_url( 'options-permalink.php' ), __( 'permalink structure', 'wc-wlfmc-wishlist' ) ) ),
											),
											'wishlist_custom_url' => array(
												'label' => __( 'Custom URL', 'wc-wlfmc-wishlist' ),
												'type'  => 'url',
												'class' => 'validate',
												'dependencies' => array(
													'id' => 'wishlist_user_page',
													'value' => 'custom-panel',
												),
											),
											'wishlist_enable_myaccount_link' => array(
												'label'   => __( 'Show Link to Wishlist in my account', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '',
												'dependencies' => array(
													'id' => 'wishlist_user_page',
													'value' => 'quest-page',
												),
											),
											'wishlist_page' => array(
												'label'   => __( 'Guest Wishlist page', 'wc-wlfmc-wishlist' ),
												/* translators: 1: create a page link  2: shortcode*/
												'desc'    => sprintf( __( '%1$s or Add %2$s shortcode to a any page you want.', 'wc-wlfmc-wishlist' ), '<a class="wlfmc-built-wishlist-page" href="#">' . __( 'click to build a new page', 'wc-wlfmc-wishlist' ) . '</a>', '<code>[wlfmc_wishlist]</code>' ) . '<a href="https://moreconvert.com/4rrt" target="_blank">' . __( 'learn more on how to use it.', 'wc-wlfmc-wishlist' ) . '</a>',
												'type'    => 'page-select',
												'show_links' => true,
												'exclude' => $this->get_all_wc_page_ids(),
												'class'   => 'select2-trigger',
												'default' => get_option( 'wlfmc_wishlist_page_id' ),
												'help'    => __( 'Wishlist page needs to be selected so the plugin knows where it is. You should choose it upon installation of the plugin or create it manually.', 'wc-wlfmc-wishlist' ),
											),
											'separator'    => array(
												'type' => 'separator',
											),
											'enable_share' => array(
												'label'   => __( 'Share Wishlist', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Enable this option to let users share their Wishlist on social media', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
											),
											'show_login_notice_for_guests' => array(
												'label'   => __( 'Login notice', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Show login notice for guests on wishlist page', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'dependencies' => array(
													'id' => 'who_can_see_wishlist_options',
													'value' => 'all',
												),
												'default' => '1',
												'help'    => __( 'It helps you to generate more leads.', 'wc-wlfmc-wishlist' ),
											),
											'social_share' => array(
												'label' => __( 'Social Share', 'wc-wlfmc-wishlist' ),
												'type'  => 'html',
												'html'  => '<a class="btn-secondary" href="' . esc_url(
													add_query_arg(
														array(
															'page' => 'mc-global-settings',
															'tab'  => 'share',
														),
														admin_url( 'admin.php' )
													)
												) . '" target="_blank">' . __( 'Change Settings', 'wc-wlfmc-wishlist' ) . '</a>',
												'dependencies' => array(
													'id' => 'enable_share',
													'value' => '1',
												),
											),
											'end-article-page-settings' => array(
												'type' => 'end',
											),
											'start-article-table-settings' => array(
												'type'  => 'start',
												'title' => __( 'Table settings', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'Choose options of your wishlist table.', 'wc-wlfmc-wishlist' ),
												'doc'   => 'https://moreconvert.com/fbdn',
												'dependencies' => array(
													'id' => 'wishlist_enable',
													'value' => '1',
												),
											),
											'empty_wishlist_title' => array(
												'label'   => __( 'Empty wishlist title', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',

												'translatable' => true,
												'default' => __( 'YOUR WISHLIST IS EMPTY!', 'wc-wlfmc-wishlist' ),
											),
											'empty_wishlist_content' => array(
												'label'   => __( 'Empty wishlist content', 'wc-wlfmc-wishlist' ),
												'type'    => 'wp-editor',
												'translatable' => true,
												'default' => __( 'You have not added any products to your wishlist.', 'wc-wlfmc-wishlist' ),
												'custom_attributes' => array(
													'style' => 'max-width:100%;width:100%',
												),
											),
											'wishlist_items_show' => array(
												'label'   => __( 'Items show for wishlist', 'wc-wlfmc-wishlist' ),
												'type'    => 'checkbox-group',
												'options' => array(
													'product-checkbox'     => __( 'Checkboxes', 'wc-wlfmc-wishlist' ),
													'product-name'         => __( 'Product name', 'wc-wlfmc-wishlist' ),
													'product-review'       => __( 'Product rating', 'wc-wlfmc-wishlist' ),
													'product-thumbnail'    => __( 'Product image', 'wc-wlfmc-wishlist' ),
													'product-variation'    => __( 'Product variations selected by the user (e.g. size or color)', 'wc-wlfmc-wishlist' ),
													'product-price'        => __( 'Product price', 'wc-wlfmc-wishlist' ),
													'product-quantity'     => __( 'Quantity', 'wc-wlfmc-wishlist' ),
													'product-stock-status' => __( 'Product stock (show if product is unavailable)', 'wc-wlfmc-wishlist' ),
													'product-date-added'   => __( 'Date on which the product was added to the wishlist', 'wc-wlfmc-wishlist' ),
													'product-add-to-cart'  => __( 'Add to cart option for each product', 'wc-wlfmc-wishlist' ),
													'product-remove'       => __( 'Button to remove product from wishlist', 'wc-wlfmc-wishlist' ),
												),
												'default' => array(
													'product-checkbox',
													'product-name',
													'product-thumbnail',
													'product-variation',
													'product-price',
													'product-add-to-cart',
													'product-remove',
												),
											),
											'wishlist_drag_n_drop' => array(
												'label'   => __( 'Enable Drag & Drop products', 'wc-wlfmc-wishlist' ),
												'help'    => __( 'Empower users to rearrange their product on list to reflect their priorities', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Just for premium users', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '0',
												'parent_class' => 'enable-for-pro',
												'custom_attributes' => array(
													'disabled' => 'true',
												),
											),
											'wishlist_under_table' => array(
												'label'   => __( 'Under wishlist table show', 'wc-wlfmc-wishlist' ),
												'type'    => 'checkbox-group',
												'options' => array(
													'actions' => __( 'All together Actions button', 'wc-wlfmc-wishlist' ),
													'add-all-to-cart' => __( '"Add All to Cart" button', 'wc-wlfmc-wishlist' ),
												),
												'default' => array(
													'actions',
													'add-all-to-cart',
												),
											),
											'wishlist_view_mode' => array(
												'label'   => __( 'Select view mode', 'wc-wlfmc-wishlist' ),
												'type'    => 'select',
												'default' => 'list',
												'options' => array(
													'list' => __( 'List', 'wc-wlfmc-wishlist' ),
													'grid' => __( 'Grid', 'wc-wlfmc-wishlist' ),
													'both-grid' => __( 'Both( Default Grid ) - pro', 'wc-wlfmc-wishlist' ),
													'both-list' => __( 'Both( Default List ) - pro', 'wc-wlfmc-wishlist' ),
												),
												'disabled_options' => array(
													'both-grid',
													'both-list',
												),
											),
											'wishlist_custom_template' => array(
												'label'   => __( 'Wishlist default template', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '0',
											),
											'wishlist_button_text' => array(
												'section' => 'button-display',
												'label'   => __( 'Wishlist button text', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'wishlist_button_color'       => array(
														'label' => __( 'Color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#515151',
													),
													'wishlist_button_hover_color' => array(
														'label' => __( 'Hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#fff',
													),
													'wishlist_button_font_size'   => array(
														'label' => __( 'Font size', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '14px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
												),
												'dependencies' => array(
													array(
														'id' => 'wishlist_custom_template',
														'value' => '0',
													),
												),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc.', 'wc-wlfmc-wishlist' ),
											),
											'wishlist_button_colors' => array(
												'section' => 'button-display',
												'label'   => __( 'Wishlist button colors', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'wishlist_button_background_color'       => array(
														'label' => __( 'Background color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#ebebeb',
													),
													'wishlist_button_background_hover_color' => array(
														'label' => __( 'Background hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#e67e22',
													),
													'wishlist_button_border_color'           => array(
														'label' => __( 'Border color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'transparent',
													),
													'wishlist_button_border_hover_color'     => array(
														'label' => __( 'Border hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'transparent',
													),

												),
												'dependencies' => array(
													array(
														'id' => 'wishlist_custom_template',
														'value' => '0',
													),
												),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc.', 'wc-wlfmc-wishlist' ),
											),
											'wishlist_button_sizes' => array(
												'section' => 'button-display',
												'label'   => __( 'Wishlist button sizes', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(

													'wishlist_button_border_radius' => array(
														'label' => __( 'Border radius', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '5px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/border_radius.gif',
														'help' => __( 'You can define the radius for all 4 corners of the button (look at the gif)', 'wc-wlfmc-wishlist' ),

													),
													'wishlist_button_border_width'  => array(
														'label' => __( 'Border width', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '1px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'wishlist_button_height'        => array(
														'label' => __( 'Height', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '36px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),

												),
												'dependencies' => array(
													array(
														'id' => 'wishlist_custom_template',
														'value' => '0',
													),
												),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc.', 'wc-wlfmc-wishlist' ),
											),
											'wishlist_button_add_to_cart_style' => array(
												'label'   => __( '"Add to Cart" Button Appearance', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Apply button style for add to cart button', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
												'dependencies' => array(
													array(
														'id' => 'wishlist_custom_template',
														'value' => '0',
													),
												),
											),
											'wishlist_disable_qty_padding' => array(
												'label'   => __( 'Disable Quantity Padding', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'If the quantity input field has a blank space and is not displaying anything, enable this.', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
											),
											'wishlist_qty_style' => array(
												'label'   => __( 'Quantity field style', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Apply styling to quantity field', 'wc-wlfmc-wishlist' ),
												'help'    => __( 'Ensure it is displayed correctly after setting; some themes are incompatible with this setting.', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
												'dependencies' => array(
													'id' => 'wishlist_custom_template',
													'value' => '0',
												),
											),
											'wishlist_pagination_style' => array(
												'label'   => __( 'Pagination style', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Apply wishlist page style for pagination bar', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
												'dependencies' => array(
													'id' => 'wishlist_custom_template',
													'value' => '0',
												),
												'help'    => __( 'Theme settings are used for pagination; by activating this section, coordinate it with the wishlist page style.', 'wc-wlfmc-wishlist' ),
											),
											'wishlist_table_style' => array(
												'section' => 'button-display',
												'label'   => __( 'Wishlist items style', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'wishlist_table_thumbnail_background'  => array(
														'label' => __( 'Thumbnail background color', 'wc-wlfmc-wishlist' ),
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/wishlist-thumbnail-background-color.gif',
														'help' => __( 'Your image must be SVG or png for this section to be effective..', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#f5f5f5',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'wishlist_table_grid_border_color'     => array(
														'label' => __( 'Border color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#ebebeb',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'wishlist_table_grid_border_radius'    => array(
														'label' => __( 'Border radius', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '6px',
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/border_radius.gif',
														'help' => __( 'You can define the radius for all 4 corners of the grid (look at the gif)', 'wc-wlfmc-wishlist' ),

														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'wishlist_table_item_background'       => array(
														'label' => __( 'Item background color', 'wc-wlfmc-wishlist' ),
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/wishlist-item-background-color.gif',
														'help'  => ' ',
														'type' => 'color',
														'default' => '#fff',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'wishlist_table_item_hover_background' => array(
														'label' => __( 'Item background hover color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#fff',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'wishlist_table_separator_color'       => array(
														'label' => __( 'Separator color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => 'transparent',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
												),
												'dependencies' => array(
													array(
														'id' => 'wishlist_custom_template',
														'value' => '0',
													),
												),

											),
											'end-article-table-settings' => array(
												'type' => 'end',
											),
											'start-article-login-notice' => array(
												'type'  => 'start',
												'title' => __( 'Login notice', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'Turn guest users into leads by inviting them to sign up on the wishlist page.', 'wc-wlfmc-wishlist' ),
												'doc'   => 'https://moreconvert.com/7f5g',
												'dependencies' => array(
													array(
														'id' => 'show_login_notice_for_guests',
														'value' => '1',
													),
													array(
														'id' => 'who_can_see_wishlist_options',
														'value' => 'all',
													),
													array(
														'id' => 'wishlist_enable',
														'value' => '1',
													),
												),
											),
											'login_notice_content' => array(
												'label'   => __( 'Content', 'wc-wlfmc-wishlist' ),
												'type'    => 'wp-editor',
												'translatable' => true,
												'help'    => __( 'Write a text for Notify that creates FOMO', 'wc-wlfmc-wishlist' ),
												'default' => '<p style="text-align: center;"><span style="color: #ff0000;"><strong>' . __( 'Warning! You Will Lose Your Wishlist', 'wc-wlfmc-wishlist' ) . '</strong></span></p><p style="text-align: center;">' . __( 'If you do not sign-up or log in to your account right now , you will probably lose your list after leaving the site.', 'wc-wlfmc-wishlist' ) . '</p>',
												'custom_attributes' => array(
													'style' => 'max-width:100%;width:100%',
												),
											),
											'login_notice_buttons' => array(
												'label'   => __( 'Add button', 'wc-wlfmc-wishlist' ),
												'type'    => 'add-button',
												'translatable' => true,
												'help'    => __( 'Adjust the text of the notification buttons to have the most impact', 'wc-wlfmc-wishlist' ),
												'links'   => array(
													'login'  => __( 'login', 'wc-wlfmc-wishlist' ),
													'signup' => __( 'Sign-up', 'wc-wlfmc-wishlist' ),
													'custom-link' => __( 'Custom url', 'wc-wlfmc-wishlist' ),
												),
												'helps'   => array(
													'border-radius' => array(
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/border_radius.gif',
														'help' => __( 'You can define the radius for all 4 corners of the button (look at the gif)', 'wc-wlfmc-wishlist' ),
													),
												),
												'default' => array(
													array(
														'label' => __( 'Login', 'wc-wlfmc-wishlist' ),
														'background' => '#555555',
														'background-hover' => '#555555',
														'label-color' => '#ffffff',
														'label-hover-color' => '#ffffff',
														'border-radius' => '2px',
														'link' => 'login',
														'custom-link' => '',
													),
													array(
														'label' => __( 'Sign-up', 'wc-wlfmc-wishlist' ),
														'background' => 'rgba(0,0,0,0)',
														'background-hover' => 'rgba(0,0,0,0)',
														'label-color' => '#7e7e7e',
														'label-hover-color' => '#7e7e7e',
														'border-radius' => '2px',
														'link' => 'signup',
														'custom-link' => '',
													),
												),
												'limit'   => 3,
											),
											'login_notice_background_color' => array(
												'label'   => __( 'Background color', 'wc-wlfmc-wishlist' ),
												'type'    => 'color',
												'help'    => __( 'We suggest you choose red, orange and yellow.', 'wc-wlfmc-wishlist' ),
												'default' => '#f6f6f6',
											),
											'end-article-login-notice' => array(
												'type' => 'end',
											),

										)
									),
									'counter'       => apply_filters(
										'wlfmc_wishlist_counter_settings',
										array(
											'start-article-wishlist-counter-settings' => array(
												'type'    => 'start',
												'title'   => __( 'Wishlist counter', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Display a counter icon that shows the total number of products in the user\'s wishlist, which can be placed in various locations such as the menu.', 'wc-wlfmc-wishlist' ),
												'help'    => __( 'Wishlist product counter', 'wc-wlfmc-wishlist' ),
												'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/counter.gif',
												'youtube' => 'https://moreconvert.com/qs5d',
												'doc'     => 'https://moreconvert.com/pj25',
												'dependencies' => array(
													'id' => 'wishlist_enable',
													'value' => '1',
												),
											),
											'counter_add_to_menu' => array(
												'label'   => __( 'Add counter to menu', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Use shortcode to place it in the desired location <code>[wlfmc_wishlist_counter]</code>', 'wc-wlfmc-wishlist' ) . '<a href="https://moreconvert.com/jtea" target="_blank">' . __( 'learn more on how to use it.', 'wc-wlfmc-wishlist' ) . '</a>',
												'default' => '',
												// 'type'    => 'select',
												'type'    => 'multi-select',
												'class'   => 'select2-trigger',
												'options' => $this->get_wordpress_menus(),
											),
											'counter_menu_position' => array(
												'label'   => __( 'Counter position (Menu item order)', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Define the position of the counter in the list of menu items.', 'wc-wlfmc-wishlist' ),
												'type'    => 'number',
												'default' => 100,
												'help'    => __( 'Allows you to add the wishlist counter as a menu item and apply its position.', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'counter_add_to_menu',
														'value' => count( $this->get_wordpress_menus() ) > 0 ? implode( ',', array_slice( array_keys( $this->get_wordpress_menus() ), 0 ) ) : 'disable',
													),
												),
											),
											'counter_icon' => array(
												'label'   => __( '"wishlist" counter icon', 'wc-wlfmc-wishlist' ),
												'type'    => 'select-icon',
												'class'   => 'select2-trigger',
												'options' => wlfmc_get_icon_names( 'wishlist', true, true ),
												'default' => 'heart',
											),
											'counter_icon_svg_zero' => array(
												'label' => __( 'Custom zero icon svg', 'wc-wlfmc-wishlist' ),
												'type'  => 'textarea',
												'custom_attributes' => array(
													'cols' => '120',
													'rows' => '3',
												),
												'dependencies' => array(
													array(
														'id' => 'counter_icon',
														'value' => 'custom',
													),
												),
												'help'  => __( 'Define a custom icon for when no product is added to the wishlist', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'Open your SVG file on a notepad and copy the code on this field.Please input the SVG code with your desired color.', 'wc-wlfmc-wishlist' ),
											),
											'counter_icon_svg_added' => array(
												'label' => __( 'Custom added icon svg', 'wc-wlfmc-wishlist' ),
												'type'  => 'textarea',
												'custom_attributes' => array(
													'cols' => '120',
													'rows' => '3',
												),
												'dependencies' => array(
													array(
														'id' => 'counter_icon',
														'value' => 'custom',
													),
												),
												'help'  => __( 'Define a custom icon for when at least one product is on the wishlist', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'Open your SVG file on a notepad and copy the code on this field.Please input the SVG code with your desired color.', 'wc-wlfmc-wishlist' ),
											),
											'counter_icon_width' => array(
												'label'   => __( 'Icon width', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'default' => '24px',
												'dependencies' => array(
													array(
														'id' => 'counter_icon',
														'value' => 'custom',
													),
												),
												'custom_attributes' => array(
													'style' => 'width:80px',
												),
											),
											'enable_counter_text' => array(
												'label'   => __( 'Show "wishlist" counter text', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '0',
											),
											'counter_text' => array(
												'label'   => __( '"wishlist" counter text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Wishlist - ', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'enable_counter_text',
														'value' => '1',
													),
												),
											),
											'enable_counter_products_number' => array(
												'label'   => __( 'Show number of products in counter', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
											),
											'hide_counter_zero_products_number' => array(
												'label'   => __( 'Hide zero number', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
												'dependencies' => array(
													array(
														'id' => 'enable_counter_products_number',
														'value' => '1',
													),
												),
											),
											'hide_counter_if_no_items' => array(
												'label'   => __( 'Hide Counter if No Products Found', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Just for premium users', 'wc-wlfmc-wishlist' ),
												'help'    => __( 'By enabling this option, if there are no products available in the list, the counter will be hidden completely.', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '0',
												'parent_class' => 'enable-for-pro',
												'custom_attributes' => array(
													'disabled' => 'true',
												),
											),
											'counter_products_number_position' => array(
												'label'   => __( 'Products number position', 'wc-wlfmc-wishlist' ),
												'default' => 'right',
												'type'    => 'select',
												'options' => array(
													'right' => __( 'Right side of the text', 'wc-wlfmc-wishlist' ),
													'left' => __( 'Left side of the text', 'wc-wlfmc-wishlist' ),
													'top-right' => __( 'On icon - top right', 'wc-wlfmc-wishlist' ),
													'top-left' => __( 'On icon - top left', 'wc-wlfmc-wishlist' ),
												),
												'dependencies' => array(
													array(
														'id' => 'enable_counter_products_number',
														'value' => '1',
													),
												),
											),
											'counter_style' => array(
												'section' => 'button-display',
												'label'   => __( 'Counter style', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'counter_color'                   => array(
														'label' => __( 'Icon color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#333',
														'dependencies' => array(
															array(
																'id'    => 'counter_icon',
																'value' => 'heart,tag,plus,gift,star,pin',
															),
														),
													),
													'counter_icon_font_size'          => array(
														'label' => __( 'Icon Font size', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => 'inherit',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
														'dependencies' => array(
															array(
																'id'    => 'counter_icon',
																'value' => 'heart,tag,plus,gift,star,pin',
															),
														),
													),
													'counter_font_weight'          => array(
														'label' => __( 'Font Weight', 'wc-wlfmc-wishlist' ),
														'type' => 'select',
														'class'   => 'select2-trigger',
														'default' => 'inherit',
														'options'   => array(
															'100'   => '100',
															'200'   => '200',
															'300'   => '300',
															'400'   => '400',
															'500'   => '500',
															'600'   => '600',
															'700'   => '700',
															'800'   => '800',
															'900'   => '900',
															'inherit'   => __( 'inherit', 'wc-wlfmc-wishlist' ),
														),
														'dependencies' => array(
															array(
																'id'    => 'enable_counter_text',
																'value' => '1',
															),
														),
													),
													'counter_number_background_color' => array(
														'label' => __( 'Number Background color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#e74c3c',
														'dependencies' => array(
															array(
																'id'    => 'enable_counter_products_number',
																'value' => '1',
															),
															array(
																'id'    => 'counter_products_number_position',
																'value' => 'top-right,top-left',
															),
														),
													),
													'counter_text_color'              => array(
														'label' => __( 'Text color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#333',
														'dependencies' => array(
															array(
																'id'    => 'enable_counter_text',
																'value' => '1',
															),
														),
													),
												),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc.', 'wc-wlfmc-wishlist' ),
											),
											'display_mini_wishlist_for_counter' => array(
												'label'   => __( 'Display mini wishlist for counter', 'wc-wlfmc-wishlist' ),
												'type'    => 'select',
												'options' => array(
													'counter-only'  => __( 'Disabled', 'wc-wlfmc-wishlist' ),
													'on-hover' => __( 'Show on hover', 'wc-wlfmc-wishlist' ),
													'on-click' => __( 'Show on click', 'wc-wlfmc-wishlist' ),
												),
												'default' => 'on-hover',
											),
											'enable_counter_add_link_title' => array(
												'label'   => __( 'Add Link For "Wishlist" Counter Title', 'wc-wlfmc-wishlist' ),
												'type'    => 'switch',
												'default' => '1',
												'dependencies' => array(
													'id' => 'display_mini_wishlist_for_counter',
													'value' => 'counter-only',
												),
											),
											'end-article-wishlist-counter-settings' => array(
												'type' => 'end',
											),
											'start-article-mini-wishlist-counter-settings' => array(
												'type'  => 'start',
												'title' => __( 'Mini-Wishlist', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'A preview of the user\'s wishlist that can be shown as a shortcut to the main wishlist, usually displayed in a dropdown or sidebar.', 'wc-wlfmc-wishlist' ),
												'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/wishlist-mini-wishlist.gif',
												'help'  => ' ',
												'dependencies' => array(
													'id' => 'display_mini_wishlist_for_counter',
													'value' => 'on-hover,on-click',
												),
											),
											'mini_wishlist_position_mode' => array(
												'label'   => __( 'Mini wishlist position mode', 'wc-wlfmc-wishlist' ),
												'type'    => 'select',
												'options' => array(
													'absolute' => __( 'Absolute position', 'wc-wlfmc-wishlist' ),
													'fixed' => __( 'Fixed position', 'wc-wlfmc-wishlist' ),
												),
												'default' => 'fixed',
											),
											'position_fixed_z_index' => array(
												'label'   => __( 'Z-index mini wishlist', 'wc-wlfmc-wishlist' ),
												'type'    => 'number',
												'default' => '997',
												'dependencies' => array(
													array(
														'id' => 'mini_wishlist_position_mode',
														'value' => 'fixed',
													),
												),
											),
											'counter_per_page_products_count' => array(
												'label'   => __( 'Maximum products in mini wishlist', 'wc-wlfmc-wishlist' ),
												'type'    => 'number',
												'default' => 4,
											),
											'counter_mini_wishlist_link_position' => array(
												'label'   => __( 'Wishlist button position in mini wishlist', 'wc-wlfmc-wishlist' ),
												'default' => 'after',
												'type'    => 'select',
												'options' => array(
													'after'  => __( 'After products', 'wc-wlfmc-wishlist' ),
													'before' => __( 'Before products', 'wc-wlfmc-wishlist' ),
												),
											),
											'counter_mini_wishlist_style' => array(
												'section' => 'button-display',
												'label'   => __( 'Mini wishlist style', 'wc-wlfmc-wishlist' ),
												'type'    => 'group-fields',
												'fields'  => array(
													'counter_background_color' => array(
														'label' => __( 'Background color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#fff',
													),
													'counter_border_color'     => array(
														'label' => __( 'Border color', 'wc-wlfmc-wishlist' ),
														'type' => 'color',
														'default' => '#f5f5f5',
													),
													'counter_border_radius'    => array(
														'label' => __( 'Border radius', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '5px',
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/border_radius.gif',
														'help' => __( 'You can define the radius for all 4 corners of the counter (look at the gif)', 'wc-wlfmc-wishlist' ),

														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
												),
												'desc'    => __( 'e.g. 10px, 15rem, 13em etc.', 'wc-wlfmc-wishlist' ),
											),
											'counter_button_colors' => array(
												'label'   => __( 'Mini wishlist button colors', 'wc-wlfmc-wishlist' ),
												'type'    => 'color-style',
												'default' => array(
													'color'  => '#515151',
													'color-hover' => '#fff',
													'background' => '#ebebeb',
													'background-hover' => '#e67e22',
													'border' => 'rgb(0,0,0,0)',
													'border-hover' => 'rgb(0,0,0,0)',
												),
											),
											'counter_button_sizes' => array(
												'label'  => __( 'Mini wishlist button sizes', 'wc-wlfmc-wishlist' ),
												'type'   => 'group-fields',
												'fields' => array(
													'counter_button_font_size'    => array(
														'label' => __( 'Font size', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '15px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'counter_button_height'       => array(
														'label' => __( 'Height', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '38px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),
													'counter_button_border_width' => array(
														'label' => __( 'Border width', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '1px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
													),

													'counter_button_border_radius' => array(
														'label' => __( 'border radius', 'wc-wlfmc-wishlist' ),
														'type' => 'text',
														'default' => '5px',
														'custom_attributes' => array(
															'style' => 'width:80px',
														),
														'help_image' => MC_WLFMC_URL . 'assets/backend/images/help/border_radius.gif',
														'help' => __( 'You can define the radius for all 4 corners of the button (look at the gif)', 'wc-wlfmc-wishlist' ),

													),
												),
												'desc'   => __( 'e.g. 10px, 15rem, 13em etc.', 'wc-wlfmc-wishlist' ),
											),
											'end-article-mini-wishlist-counter-settings' => array(
												'type' => 'end',
											),
										)
									),
									'text'          => apply_filters(
										'wlfmc_wishlist_text_settings',
										array(
											// labels.
											'start-article-labels-text' => array(
												'type'  => 'start',
												'title' => __( 'Button and tooltip custom text', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'It will show on the tooltip and button text.', 'wc-wlfmc-wishlist' ),
												'doc'   => 'https://moreconvert.com/hafu',
												'dependencies' => array(
													'id' => 'wishlist_enable',
													'value' => '1',
												),
											),
											'button_label_add' => array(
												'label'   => __( '"Add To Wishlist" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Add To Wishlist', 'wc-wlfmc-wishlist' ),
											),
											'button_label_view' => array(
												'label'   => __( '"View My Wishlist" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'View My Wishlist', 'wc-wlfmc-wishlist' ),
											),
											'button_label_remove' => array(
												'label'   => __( '"Remove From Wishlist" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Remove From Wishlist', 'wc-wlfmc-wishlist' ),
											),
											'button_label_exists' => array(
												'label'   => __( '"Already In Wishlist" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Already In Wishlist', 'wc-wlfmc-wishlist' ),
											),
											'end-article-labels-text' => array(
												'type' => 'end',
											),
											// notifications.
											'start-article-notification-text' => array(
												'type'  => 'start',
												'title' => __( 'Notification custom text', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'You can choose your desired text for the items below.', 'wc-wlfmc-wishlist' ),
												'doc'   => 'https://moreconvert.com/m08z',
												'dependencies' => array(
													'id' => 'wishlist_enable',
													'value' => '1',
												),
											),
											'product_added_text' => array(
												'label'   => __( '"Product added" text', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Enter the text of the message displayed when the user adds a product to the Wishlist. leave empty for disable message.', 'wc-wlfmc-wishlist' ),
												'default' => __( 'Product added!', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'help'    => __( 'After the user clicks on the Add to wishlist button, a notification bar will be displayed. You can display the default text or any other text on this notification bar.', 'wc-wlfmc-wishlist' ),
											),
											'already_in_wishlist_text' => array(
												'label'   => __( '"Product already in Wishlist" text', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Enter the text for the message displayed when the user will try to add a product that is already in the Wishlist,leave empty for disable message', 'wc-wlfmc-wishlist' ),
												'default' => __( 'The product is already in your Wishlist!', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'help'    => __( 'If the product already exists in the user\'s wishlist and she/he clicks the Add to wishlist button again, this text will be displayed.', 'wc-wlfmc-wishlist' ),
											),
											'product_removed_text' => array(
												'label'   => __( '"Product removed" text', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Enter the text of the message displayed when the user removed a product from the Wishlist,leave empty for disable message', 'wc-wlfmc-wishlist' ),
												'default' => __( 'Product Removed!', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'help'    => __( 'After the user clicks on the remove from wishlist button, a notification bar will be displayed. You can display the default text or any other text on this notification bar.', 'wc-wlfmc-wishlist' ),
											),
											'login_need_text' => array(
												'label'   => __( '"Force to login" text', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'You can use <code>{login_url}</code> or <code>{signup_url}</code> in the text.', 'wc-wlfmc-wishlist' ),
												'default' => __( 'to use your Wishlist: <br><a href="{login_url}">Login right now</a>', 'wc-wlfmc-wishlist' ),
												'type'    => 'textarea',
												'translatable' => true,
												'custom_attributes' => array(
													'cols' => '120',
													'rows' => '3',
												),
												'help'    => __( 'it will show when users have to log in to add to the wishlist', 'wc-wlfmc-wishlist' ),
											),
											'end-article-notification-text' => array(
												'type' => 'end',
											),
											// Mini Wishlist Texts.
											'start-article-mini-wishlist-text' => array(
												'type'  => 'start',
												'title' => __( 'Mini Wishlist Texts', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													'id' => 'wishlist_enable',
													'value' => '1',
												),
											),
											'counter_button_text' => array(
												'label'   => __( 'Mini wishlist button text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'View My Wishlist', 'wc-wlfmc-wishlist' ),
											),
											'counter_total_text' => array(
												'label'   => __( '"Total product" text', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'Total products', 'wc-wlfmc-wishlist' ),
											),
											'counter_empty_wishlist_content' => array(
												'label'   => __( 'Empty wishlist content', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
												'default' => __( 'You have not added any products to your wishlist.', 'wc-wlfmc-wishlist' ),
											),
											'end-article-mini-wishlist-text' => array(
												'type' => 'end',
											),
											// labels.
											'start-article-table-labels-text' => array(
												'type'  => 'start',
												'title' => __( 'Page custom text', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'It will show on the tooltip and label text.', 'wc-wlfmc-wishlist' ),
												'doc'   => 'https://moreconvert.com/nm16',
												'dependencies' => array(
													'id' => 'wishlist_enable',
													'value' => '1',
												),
											),
											'wishlist_page_title' => array(
												'label'   => __( 'Wishlist name', 'wc-wlfmc-wishlist' ),
												'help'    => __( 'Enter a name for the wishlist page. This is the wishlist that will be automatically generated for all users.', 'wc-wlfmc-wishlist' ),
												'default' => __( 'My Wishlist', 'wc-wlfmc-wishlist' ),
												'type'    => 'text',
												'translatable' => true,
											),
											'all_add_to_cart_tooltip_label' => array(
												'label'   => __( '"Add all to cart" tooltip text', 'wc-wlfmc-wishlist' ),
												'type'    => 'textarea',
												'translatable' => true,
												'custom_attributes' => array(
													'cols' => '120',
													'rows' => '3',
												),
												'default' => __( 'All products on the Wishlist will be added to the cart (except out-of-stock products and variable products without specifying the variable).', 'wc-wlfmc-wishlist' ),
												'dependencies' => array(
													array(
														'id' => 'wishlist_under_table',
														'value' => 'add-all-to-cart',
													),
												),
											),
											'text_management' => array(
												'label' => __( 'Other texts ?', 'wc-wlfmc-wishlist' ),
												'type'  => 'html',
												'html'  => '<a class="btn-secondary" href="' . esc_url(
													add_query_arg(
														array(
															'page' => 'mc-global-settings',
															'tab'  => 'appearance',
														),
														admin_url( 'admin.php' )
													)
												) . '" target="_blank">' . __( 'Change Global Texts', 'wc-wlfmc-wishlist' ) . '</a>',
											),
											'end-article-table-labels-text' => array(
												'type' => 'end',
											),
										)
									),
								),
							),
						)
					),
					'title'          => __( 'MoreConvert', 'wc-wlfmc-wishlist' ),
					'logo'           => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/logo.svg" width="45" height="40"  alt="logo"/>',
					'header_buttons' => wlfmc_get_admin_header_buttons(),
					'header_menu'    => wlfmc_get_admin_header_menu(),
					'sidebar'        => wlfmc_get_admin_sidebar(),
					'type'           => 'setting-type',
					'ajax_saving'    => true,
					'id'             => 'wlfmc_options',
				);

				$this->wishlist_panel = new MCT_Admin( $this->wishlist_options );

				$this->main_panel = new MCT_Admin( $this->global_options );

				// load default value to options.
				$global_options = $this->main_panel->get_options();
				// load default value to options.
				$wishlist_options = $this->wishlist_panel->get_options();

				if ( empty( $global_options ) || ! isset( $global_options['global-settings'] ) ) {
					$this->main_panel->set_default_options( 'global-settings' );
					if ( ! empty( $wishlist_options ) ) {
						$this->main_panel->move_options(
							'button-display',
							'global-settings',
							array(
								'ajax_mode',
								'ajax_loading',
								'is_cache_enabled',
								'login_url',
								'signup_url',
								'email_per_hours',
								'live_chat',
								'reset_sending_cycles',
								'remove_all_data',
							)
						);
					}
				}
				if ( empty( $wishlist_options ) || ! isset( $wishlist_options['button-display'] ) ) {
					$this->wishlist_panel->set_default_options( 'button-display' );
				}
			}

			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_action( 'admin_head', array( $this, 'remove_welcome_screen_menu' ) );
			add_action( 'admin_notices', array( $this, 'display_admin_messages' ) );
			add_action( 'admin_init', array( $this, 'handle_rollback' ) );
			add_action( 'admin_init', array( $this, 'welcome_screen_do_activation_redirect' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'global_admin_scripts' ) );
			// add a post display state for special WC pages.
			add_filter( 'display_post_states', array( $this, 'add_display_post_states' ), 1000, 2 );

			$plugin = plugin_basename( MC_WLFMC_MAIN_FILE );
			add_filter( "plugin_action_links_$plugin", array( $this, 'settings_link' ) );

			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

			add_action(
				'mct_panel_after_wlfmc_options_update',
				array(
					$this,
					'after_update_options',
				)
			);

			add_action(
				'mct_panel_after_wlfmc_options_ajax_update',
				array(
					$this,
					'after_ajax_update_options',
				)
			);

			add_filter( 'mct_wizard_wlfmc_options_value', array( $this, 'save_wizard_options' ) );

			// Add column to User table.
			add_filter( 'manage_users_custom_column', array( $this, 'items_in_list_user_table_row' ), 10, 3 );
			add_filter( 'manage_users_columns', array( $this, 'wishlist_items_user_table' ) );
			// Add column to Product table.
			add_action( 'manage_product_posts_custom_column', array( $this, 'items_in_list_product_table_row' ), 10, 2 );
			add_filter( 'manage_product_posts_columns', array( $this, 'wishlist_items_product_table' ) );
			add_action( 'admin_head', array( $this, 'set_wishlist_items_column_width' ) );
			// Add features to wizard.
			add_action( 'mct_wizard_before_step_field_wlfmc_options_step-5', array( $this, 'add_wizard_features' ) );
			add_action( 'mct_wizard_before_title_step_field_wlfmc_options_welcome', array( $this, 'add_welcome_video' ) );

			// Check minimum version.
			add_action( 'admin_init', array( $this, 'check_minimum_version' ), 100 );
		}

		/**
		 * Add added count column to user table
		 *
		 * @param array $column user columns.
		 *
		 * @return array
		 */
		public function wishlist_items_user_table( $column ) {
			$column['lists'] = __( 'Lists', 'wc-wlfmc-wishlist' );
			return $column;
		}

		/**
		 * Add added count to user table
		 *
		 * @param string $val column value.
		 * @param string $column_name column name.
		 * @param int    $user_id user id.
		 *
		 * @return string
		 */
		public function items_in_list_user_table_row( $val, $column_name, $user_id ) {
			switch ( $column_name ) {
				case 'lists':
					$count = WLFMC_Wishlist_Factory::get_wishlists_count(
						array(
							'user_id'    => $user_id,
							'show_empty' => false,
						)
					);
					$url   = defined( 'MC_WLFMC_PREMIUM' ) ? add_query_arg(
						array(
							'page'    => 'mc-analytics',
							'tab'     => 'lists',
							'type'    => 'class',
							'user_id' => $user_id,
						),
						admin_url( 'admin.php' )
					) : add_query_arg( 'page', 'mc-wishlist-dashboard', admin_url( 'admin.php' ) );
					return $count > 0 ? '<a href="' . esc_url( $url ) . '">' . esc_attr( $count ) . '</a>' : '0';
				default:
			}
			return $val;
		}

		/**
		 * Add added count column to woocommerce product table
		 *
		 * @param array $column columns.
		 *
		 * @return array
		 */
		public function wishlist_items_product_table( $column ) {
			$column['wishlist_counts'] = __( 'Added to list', 'wc-wlfmc-wishlist' );
			return $column;
		}

		/**
		 * Set width for wishlist counts column
		 *
		 * @return void
		 *
		 * @since 1.6.8
		 * @version 1.6.9
		 */
		public function set_wishlist_items_column_width() {
			echo '<style>.wp-list-table .column-wishlist_counts,.wp-list-table .column-product_insights { width: 5%; }</style>';
		}

		/**
		 * Add added count to woocommerce product table
		 *
		 * @param string $column_name Column name.
		 * @param int    $post_id Post id.
		 *
		 * @return void
		 */
		public function items_in_list_product_table_row( $column_name, $post_id ) {
			if ( 'wishlist_counts' === $column_name ) {
				$count = WLFMC()->parent_count_add_to_lists( $post_id );
				$url   = defined( 'MC_WLFMC_PREMIUM' ) ? esc_url(
					add_query_arg(
						array(
							'page'             => 'mc-analytics',
							'product-insights' => $post_id,
						),
						admin_url( 'admin.php' )
					)
				) : add_query_arg( 'page', 'mc-wishlist-dashboard', admin_url( 'admin.php' ) );
				echo $count > 0 ? '<a href="' . esc_url( $url ) . '">' . esc_attr( $count ) . '</a>' : '0';
			}
		}

		/**
		 * Save wizard options
		 *
		 * @param array $values saved options.
		 *
		 * @return array
		 *
		 * @version 1.5.9
		 * @since 1.4.5
		 */
		public function save_wizard_options( $values ): array {

			// phpcs:disable WordPress.Security.NonceVerification
			$this->after_update_options();

			if ( isset( $_POST['enable_track'] ) ) {

				$client = new WLFMC_Appsero\Client( '4fd3ce9d-2f72-4d4d-9344-ac3b11fb6a9c', 'MC Woocommerce Wishlist Plugin', __FILE__ );

				// optin insights.
				$client->insights()->optin();
			}
			// phpcs:enable WordPress.Security.NonceVerification
			update_option( 'wlfmc_wizard', '1' );

			return $values;

		}

		/**
		 * Get all WooCommerce page ids.
		 *
		 * @return array
		 * @since 1.2.0
		 */
		public function get_all_wc_page_ids() {
			$ids   = array();
			$ids[] = get_option( 'woocommerce_shop_page_id' );
			$ids[] = get_option( 'woocommerce_cart_page_id' );
			$ids[] = get_option( 'woocommerce_checkout_page_id' );
			$ids[] = get_option( 'woocommerce_myaccount_page_id' );
			$ids[] = get_option( 'woocommerce_terms_page_id' );

			return $ids;
		}

		/**
		 * Add a post display state for special WC pages in the page list table.
		 *
		 * @param array   $post_states An array of post display states.
		 * @param WP_Post $post The current post object.
		 *
		 * @return array
		 *
		 * @version 1.0.1
		 */
		public function add_display_post_states( $post_states, $post ) {
			if ( (int) get_option( 'wlfmc_wishlist_page_id' ) === $post->ID ) {
				$post_states[] = __( 'MC Wishlist Page', 'wc-wlfmc-wishlist' );
			}
			return $post_states;
		}

		/**
		 * Update page ids and remove data state after update plugin settings.
		 *
		 * @param array $new_options Mew options.
		 *
		 * @since 1.5.9
		 */
		public function after_ajax_update_options( $new_options ) {
			if ( isset( $new_options['global-settings'] ) ) {
				$state = isset( $new_options['global-settings']['remove_all_data'] ) ? sanitize_text_field( wp_unslash( $new_options['global-settings']['remove_all_data'] ) ) : '';
				update_option( 'wlfmc_remove_all_data', $state );
				$id = isset( $new_options['global-settings']['tabbed_page'] ) ? intval( wp_unslash( $new_options['global-settings']['tabbed_page'] ) ) : 0;
				if ( 0 < $id ) {
					update_option( 'wlfmc_tabbed_page_id', $id );
				}
			}
			if ( isset( $new_options['button-display'] ) ) {
				$id = isset( $new_options['button-display']['wishlist_page'] ) ? intval( wp_unslash( $new_options['button-display']['wishlist_page'] ) ) : 0;
				if ( 0 < $id ) {
					update_option( 'wlfmc_wishlist_page_id', $id );

				}
			}
			if ( isset( $new_options['waitlist'] ) ) {
				$id = isset( $new_options['waitlist']['waitlist_page'] ) ? intval( wp_unslash( $new_options['waitlist']['waitlist_page'] ) ) : 0;
				if ( 0 < $id ) {
					update_option( 'wlfmc_waitlist_page_id', $id );

				}
			}
			if ( isset( $new_options['multi-list'] ) ) {
				$id = isset( $new_options['multi-list']['multi_list_page'] ) ? intval( wp_unslash( $new_options['multi-list']['multi_list_page'] ) ) : 0;
				if ( 0 < $id ) {
					update_option( 'wlfmc_multi_list_page_id', $id );

				}
			}

		}

		/**
		 * Update page ids and remove data state after update plugin settings.
		 *
		 * @since 1.5.9
		 */
		public function after_update_options() {
            // phpcs:disable WordPress.Security
			if ( isset( $_POST['mct-action'] ) && 'global-settings' === $_POST['mct-action'] ) {
				$state = isset( $_POST['remove_all_data'] ) ? sanitize_text_field( wp_unslash( $_POST['remove_all_data'] ) ) : '';
				update_option( 'wlfmc_remove_all_data', $state );
			}
			if ( isset( $_POST['mct-action'] ) && 'button-display' === $_POST['mct-action'] ) {
				$id = isset( $_POST['wishlist_page'] ) ? intval( wp_unslash( $_POST['wishlist_page'] ) ) : 0;
				if ( 0 < $id ) {
					update_option( 'wlfmc_wishlist_page_id', $id );
				}
			}

			if ( isset( $_POST['mct-action'] ) && 'waitlist' === $_POST['mct-action'] ) {
				$id = isset( $_POST['waitlist_page'] ) ? intval( wp_unslash( $_POST['waitlist_page'] ) ) : 0;
				if ( 0 < $id ) {
					update_option( 'wlfmc_waitlist_page_id', $id );
				}
			}

			if ( isset( $_POST['mct-action'] ) && 'multi-list' === $_POST['mct-action'] ) {
				$id = isset( $_POST['multi_list_page'] ) ? intval( wp_unslash( $_POST['multi_list_page'] ) ) : 0;
				if ( 0 < $id ) {
					update_option( 'wlfmc_multi_list_page_id', $id );
				}
			}

			if ( isset( $_POST['mct-action'] ) && 'global-settings' === $_POST['mct-action'] ) {
				$id = isset( $_POST['tabbed_page'] ) ? intval( wp_unslash( $_POST['tabbed_page'] ) ) : 0;
				if ( 0 < $id ) {
					update_option( 'wlfmc_tabbed_page_id', $id );
				}
			}
			// phpcs:enable WordPress.Security
		}

		/**
		 * Rollback plugin.
		 *
		 * @since 1.5.5
		 */
		public function handle_rollback() {
			if ( isset( $_GET['rollback'] ) && 'true' === $_GET['rollback'] && check_admin_referer( 'rollback_wlfmc' ) ) {
				// Get the previous plugin version.
				if ( ! $this->rollback_version ) {
					add_settings_error( 'rollback_wlfmc_error', 'rollback_wlfmc_error', esc_html__( 'Previous version not found.', 'wc-wlfmc-wishlist' ), 'error' );
					return;
				}

				if ( 'disabled' === $this->rollback_version ) {
					add_settings_error( 'rollback_wlfmc_error', 'rollback_wlfmc_error', esc_html__( 'Rollback to previous version disabled.', 'wc-wlfmc-wishlist' ), 'error' );
					return;
				}
				// Rollback the plugin.
				if ( ! function_exists( 'get_plugins' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}
				if ( ! function_exists( 'validate_file' ) ) {
					require_once ABSPATH . 'wp-includes/functions.php';
				}
				if ( ! function_exists( 'wp_update_plugins' ) ) {
					require_once ABSPATH . 'wp-admin/includes/update.php';
				}

				$plugin_path = plugin_basename( MC_WLFMC_MAIN_FILE );
				$plugins     = get_plugins();
				if ( ! isset( $plugins[ $plugin_path ] ) ) {
					add_settings_error( 'rollback_wlfmc_error', 'rollback_wlfmc_error', esc_html__( 'Plugin not found.', 'wc-wlfmc-wishlist' ), 'error' );
					return;
				}
				$plugin_file     = WP_PLUGIN_DIR . '/' . $plugin_path;
				$new_plugin_file = validate_file( $plugin_file );
				if ( is_wp_error( $new_plugin_file ) ) {
					/**
					 * Variable
					 *
					 * @var WP_Error $new_plugin_file WordPress error.
					 */
					add_settings_error( 'rollback_wlfmc_error', 'rollback_wlfmc_error', $new_plugin_file->get_error_message(), 'error' );
					return;
				}

				$plugin_slug = dirname( $plugin_path );

				$update_plugins = get_site_transient( 'update_plugins' );
				if ( ! is_object( $update_plugins ) ) {
					$update_plugins = new stdClass();
				}

				$plugin_info              = new stdClass();
				$plugin_info->new_version = $this->rollback_version;
				$plugin_info->slug        = $plugin_slug;
				$plugin_info->package     = sprintf( 'https://downloads.wordpress.org/plugin/%s.%s.zip', $plugin_slug, $this->rollback_version );
				$plugin_info->url         = 'https://moreconvert.com/';

				$update_plugins->response[ $plugin_path ] = $plugin_info;

				set_site_transient( 'update_plugins', $update_plugins );

				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

				$upgrader_args = array(
					'url'    => 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( $plugin_path ),
					'plugin' => $plugin_path,
					'nonce'  => 'upgrade-plugin_' . $plugin_path,
					'title'  => esc_html__( 'Rollback to Previous Version', 'wc-wlfmc-wishlist' ),
				);
				$upgrader      = new Plugin_Upgrader( new Plugin_Upgrader_Skin( $upgrader_args ) );
				$upgrader->upgrade( $plugin_path );

				wp_die(
					'',
					esc_html__( 'MC Wishlist Rollback to previous version', 'wc-wlfmc-wishlist' ),
					array(
						'response' => 200,
					)
				);
			}
		}

		/**
		 * Display admin messages.
		 *
		 * @return void
		 */
		public function display_admin_messages() {
			settings_errors( 'rollback_wlfmc_error' );
		}

		/**
		 * Get WordPress menus
		 *
		 * @return array
		 *
		 * @since 1.3.0
		 * @version 1.6.6
		 */
		public function get_wordpress_menus() {
			$menus     = array();
			$get_menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
			foreach ( $get_menus as $menu ) {
				$menus[ $menu->term_id ] = $menu->name;
			}

			return $menus;
		}

		/**
		 * Add admin menu
		 *
		 * @return void
		 *
		 * @version 1.2.0
		 */
		public function add_admin_menu() {
            $remove_wizard = apply_filters( 'wlfmc_remove_wizard_menu', '1' === get_option( 'wlfmc_wizard' ) );
			$parent_slug   = $remove_wizard ? 'mc-wishlist-dashboard' : 'mc-wishlist-setup';
            $function      = 'mc-wishlist-setup' === $parent_slug ? 'show_welcome_screen' : 'show_dashboard';
			add_menu_page(
				apply_filters( 'wlfmc_menu_title', __( 'MC Wishlist', 'wc-wlfmc-wishlist' ) ),
				apply_filters( 'wlfmc_menu_title', __( 'MC Wishlist', 'wc-wlfmc-wishlist' ) ),
				apply_filters( 'wlfmc_capability', 'manage_options' ),
				$parent_slug,
				array( $this, $function ),
				apply_filters( 'wlfmc_menu_icon', 'dashicons-heart' ),
				54.7654
			);
			$welcome = add_submenu_page(
				$parent_slug,
				__( 'Setup Wizard', 'wc-wlfmc-wishlist' ),
				__( 'Setup Wizard', 'wc-wlfmc-wishlist' ),
				apply_filters( 'wlfmc_capability', 'manage_options' ),
				'mc-wishlist-setup',
				array( $this, 'show_welcome_screen' )
			);
			$dashboard = add_submenu_page(
				$parent_slug,
				__( 'Dashboard', 'wc-wlfmc-wishlist' ),
				__( 'Dashboard', 'wc-wlfmc-wishlist' ),
				apply_filters( 'wlfmc_capability', 'manage_options' ),
				'mc-wishlist-dashboard',
				array( $this, 'show_dashboard' )
			);

			$settings = add_submenu_page(
				$parent_slug,
				__( 'Global', 'wc-wlfmc-wishlist' ),
				__( 'Global', 'wc-wlfmc-wishlist' ),
				apply_filters( 'wlfmc_capability', 'manage_options' ),
				'mc-global-settings',
				array( $this, 'show_settings' )
			);

			$wishlist_settings = add_submenu_page(
				$parent_slug,
				__( 'Wishlist', 'wc-wlfmc-wishlist' ),
				__( 'Wishlist', 'wc-wlfmc-wishlist' ),
				apply_filters( 'wlfmc_capability', 'manage_options' ),
				'mc-wishlist-settings',
				array( $this, 'show_wishlist_settings' )
			);

			$automation_settings = add_submenu_page(
				$parent_slug,
				__( 'Email Automation', 'wc-wlfmc-wishlist' ),
				__( 'Email Automation', 'wc-wlfmc-wishlist' ),
				apply_filters( 'wlfmc_capability', 'manage_options' ),
				'mc-email-automations',
				array( $this, 'show_automation_settings' )
			);

			if ( ! defined( 'MC_WLFMC_PREMIUM' ) ) {
				add_submenu_page(
					$parent_slug,
					__( 'Unlock Premium', 'wc-wlfmc-wishlist' ),
					'<span style="color:#FD5D00">' . __( 'Unlock Premium', 'wc-wlfmc-wishlist' ) . '</span>',
					apply_filters( 'wlfmc_capability', 'manage_options' ),
					'https://moreconvert.com/vb79'
				);
			}

			/*$welcome = add_dashboard_page(
				apply_filters( 'wlfmc_menu_title', __( 'MC Wishlist', 'wc-wlfmc-wishlist' ) ),
				apply_filters( 'wlfmc_menu_title', __( 'MC Wishlist', 'wc-wlfmc-wishlist' ) ),
				apply_filters( 'wlfmc_capability', 'manage_options' ),
				'mc-wishlist-setup',
				array( $this, 'show_welcome_screen' )
			);*/

			$admin_pages = apply_filters(
				'wlfmc_admin_menus',
				array(
					'global'            => $settings,
					'wishlist'          => $wishlist_settings,
					'email-automations' => $automation_settings,
					'dashboard'         => $dashboard,
					'welcome-screen'    => $welcome,
				),
                $parent_slug
			);
			if ( ! empty( $admin_pages ) ) {
				foreach ( $admin_pages as $page ) {
					add_action( 'load-' . $page, array( $this, 'admin_enqueue_scripts' ) );
					if ( $welcome === $page ) {
						add_action( 'load-' . $welcome, array( $this->main_panel, 'wizard_init' ) );
					} else {
						add_action( 'load-' . $page, array( $this->main_panel, 'init' ) );
					}
				}
			}

			do_action( 'load_submenu_mc_wishlist', $admin_pages );
		}

		/**
		 * Redirect to welcome page after activate plugin
		 *
		 * @return void
		 * @since 1.2.0
		 */
		public function welcome_screen_do_activation_redirect() {

			if ( ! get_transient( '_wlfmc_wishlist_activation_redirect' ) ) {
				return;
			}

			if ( wp_doing_ajax() ) {
				return;
			}

			delete_transient( '_wlfmc_wishlist_activation_redirect' );

			if ( '1' === get_option( 'wlfmc_wizard' ) ) {
				return;
			}

			if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return;
			}

			wp_safe_redirect( add_query_arg( array( 'page' => 'mc-wishlist-setup' ), admin_url( 'admin.php' ) ) );
			exit;
		}

		/**
		 *  Remove welcome screen menu
		 *
		 * @since 1.2.0
		 */
		public function remove_welcome_screen_menu() {

            if ( apply_filters( 'wlfmc_remove_wizard_menu', '1' === get_option( 'wlfmc_wizard' ) )  ) {
				remove_submenu_page( 'mc-wishlist-dashboard', 'mc-wishlist-dashboard' );
				remove_submenu_page( 'mc-wishlist-setup', 'mc-wishlist-setup' );
				remove_submenu_page( 'mc-wishlist-dashboard', 'mc-wishlist-setup' );
            }

		}

		/**
		 * Add Settings Button Beside Plugin Detail.
		 *
		 * @param array $links Array of links.
		 *
		 * @return array
		 * @version 1.5.0
		 * @since 1.0.1
		 */
		public function settings_link( $links ) {

			$setting_links = array(
				'<a href="' . esc_url( add_query_arg( 'page', 'mc-global-settings', admin_url( 'admin.php' ) ) ) . '">' . __( 'Global Settings', 'wc-wlfmc-wishlist' ) . '</a>',
				'<a href="' . esc_url(
					add_query_arg(
						array(
							'page' => 'mc-wishlist-settings',
						),
						admin_url( 'admin.php' )
					)
				) . '">' . __( 'Wishlist Settings', 'wc-wlfmc-wishlist' ) . '</a>',
				'<a href="https://moreconvert.com/ohmq"  style="color: red;font-weight: bold;" target="_blank">' . __( 'Submit Bugs', 'wc-wlfmc-wishlist' ) . '</a>',
			);
			if ( ! defined( 'MC_WLFMC_PREMIUM' ) && $this->rollback_version && 'disabled' !== $this->rollback_version ) {
				/* translators: %s: old version */
				$setting_links[] = '<a style="color: red;font-weight: bold;" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'rollback' => 'true' ) ), 'rollback_wlfmc' ) ) . '"  onclick="return confirm(\'' . sprintf( __( 'Are you sure you want to revert to the previous version of the plugin (%s)? (in case there is an issue)', 'wc-wlfmc-wishlist' ), $this->rollback_version ) . '\')">' . __( 'Rollback', 'wc-wlfmc-wishlist' ) . '</a>';
			}
			return array_merge( $setting_links, $links );

		}

		/**
		 * Add Docs and Support Button Beside Plugin Detail.
		 *
		 * @param array  $links links.
		 * @param string $file file name.
		 *
		 * @return array
		 * @since 1.0.1
		 */
		public function plugin_row_meta( $links, $file ): array {
			$plugin = plugin_basename( MC_WLFMC_MAIN_FILE );

			if ( $plugin === $file ) {
				$row_meta = array(
					'docs'    => '<a href="' . esc_url( 'https://moreconvert.com/w5t1' ) . '" target="_blank" aria-label="' . esc_attr__( 'Documentation', 'wc-wlfmc-wishlist' ) . '" style="color:green;">' . esc_html__( 'Documentation', 'wc-wlfmc-wishlist' ) . '</a>',
					'support' => '<a href="' . esc_url( 'https://moreconvert.com/xmj7' ) . '" target="_blank" aria-label="' . esc_attr__( 'Support', 'wc-wlfmc-wishlist' ) . '" style="color:green;">' . esc_html__( 'Support', 'wc-wlfmc-wishlist' ) . '</a>',
					'pro'     => '<a href="' . esc_url( 'https://moreconvert.com/vxhv' ) . '" target="_blank" aria-label="' . esc_attr__( 'Get Pro', 'wc-wlfmc-wishlist' ) . '" style="color:orange;font-weight:bold">' . esc_html__( 'Get Pro', 'wc-wlfmc-wishlist' ) . '</a>',
				);
				if ( is_plugin_active( 'smart-wishlist-for-more-convert-premium/smart-wishlist-for-more-convert-premium.php' ) ) {
					unset( $row_meta['pro'] );
				}

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		/**
		 * Enqueue script and styles in admin side.
		 *
		 * @retrun void
		 *
		 * @version 1.2.2
		 */
		public function admin_enqueue_scripts() {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_register_script( 'wlfmc-admin', MC_WLFMC_URL . 'assets/backend/js/admin-panel' . $suffix . '.js', array( 'jquery' ), WLFMC_VERSION, true );
			wp_register_style( 'wlfmc-dashboard', MC_WLFMC_URL . 'assets/backend/css/dashboard' . $suffix . '.css', null, WLFMC_VERSION );

			$locale  = localeconv();
			$decimal = $locale['decimal_point'] ?? '.';

			$params = array(
				'ajax_url'                 => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'               => wp_create_nonce( 'ajax-nonce' ),
				'i18n_making_page'         => __( 'Making a page ...', 'wc-wlfmc-wishlist' ),
				'i18n_sending'             => __( 'Sending ...', 'wc-wlfmc-wishlist' ),
				'i18n_resetting'           => __( 'Resetting ...', 'wc-wlfmc-wishlist' ),
				'i18n_resetting_confirm'   => __( 'All previous cycle between complete automation and running a new automation will be cleared and all automation cycle rules will be cleared for all users.  Do you agree to delete this information?', 'wc-wlfmc-wishlist' ),
				/* translators: %s: decimal */
				'i18n_decimal_error'       => sprintf( __( 'Please enter with one decimal point (%s) without thousand separators.', 'wc-wlfmc-wishlist' ), $decimal ),
				/* translators: %s: price decimal separator */
				'i18n_mon_decimal_error'   => sprintf( __( 'Please enter with one monetary decimal point (%s) without thousand separators and currency symbols.', 'wc-wlfmc-wishlist' ), wc_get_price_decimal_separator() ),
				'i18n_percent_description' => __( 'Value of the coupon(percent).', 'wc-wlfmc-wishlist' ),
				/* translators: %s: currency symbol */
				'i18n_amount_description'  => __( 'Value of the coupon.', 'wc-wlfmc-wishlist' ),
				'decimal_point'            => $decimal,
				'mon_decimal_point'        => wc_get_price_decimal_separator(),
			);

			wp_localize_script( 'wlfmc-admin', 'wlfmc_wishlist_admin', $params );
			wp_enqueue_script( 'wlfmc-admin' );

			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );

			$options = new MCT_Options( 'wlfmc_options' );
			if ( wlfmc_is_true( $options->get_option( 'live_chat', '1' ) ) ) {
				wp_add_inline_script( 'wlfmc-admin', 'window.$crisp=[];window.CRISP_WEBSITE_ID="178de8d4-f389-4844-af87-fd40c311732c";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();' );
			}

		}


		/**
		 * Enqueue script and styles in admin side.
		 *
		 * @retrun void
		 *
		 * @since 1.7.0
		 */
		public function global_admin_scripts() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_style( 'wlfmc-admin', MC_WLFMC_URL . 'assets/backend/css/admin' . $suffix . '.css', null, WLFMC_VERSION );
			wp_enqueue_style( 'wlfmc-admin' );
        }

			/**
		 * Show admin dashboard panel.
		 *
		 * @return void
		 *
		 * @version 1.5.5
		 */
		public function show_dashboard() {
			?>
			<div id="wlfmc_options">
				<?php
				if ( $this->installed ) {

					$fields = new MCT_Fields(
						array(
							'title'          => __( 'MoreConvert', 'wc-wlfmc-wishlist' ),
							'subtitle'       => __( 'MoreConvert Setting', 'wc-wlfmc-wishlist' ),
							'desc'           => __( 'Gather leads and boost your sales with attention-grabbing lists for potential customers.', 'wc-wlfmc-wishlist' ),
							'logo'           => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/logo.svg" width="45" height="40"  alt="logo"/>',
							'id'             => 'wlfmc_dashboard_page',
							'header_buttons' => wlfmc_get_admin_header_buttons(),
							'header_menu'    => wlfmc_get_admin_header_menu(),
							'type'           => 'class-type',
							'class_array'    => array( $this, 'admin_dashboard_output' ),
						)
					);
					$fields->output();
				}
				?>
			</div>
			<div id="snackbar"></div>
			<?php

		}

		/**
		 * Admin dashboard output.
		 *
		 * @return void
		 */
		public function admin_dashboard_output() {
			global $wpdb;
			// phpcs:disable WordPress.DB.PreparedSQL, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$email_sent      = $wpdb->get_var( "SELECT sum(IF(b.status IN ('sent', 'opened','clicked' ,'coupon-used'), 1, 0)) sent FROM $wpdb->wlfmc_wishlist_offers as b" );
			$wishlist_count  = $wpdb->get_var( "SELECT COUNT(*) as count FROM $wpdb->wlfmc_wishlists" );
			$list_statistics = $wpdb->get_row(
				"SELECT
                    sum(IF(a.type IN ('buy-through-list', 'buy-through-coupon'), a.quantity, 0)) as product_solds,
                    sum(IF(a.type IN ('buy-through-list', 'buy-through-coupon'), ( a.quantity * a.price ), 0)) as total_sales,
                    (sum(IF(a.type IN ('add-to-list', 'buy-through-list'), 1, 0)) + (sum(IF(a.type = 'buy-through-coupon' AND a.wishlist_id = 0, 1, 0)))) as product_added
                    FROM $wpdb->wlfmc_wishlist_analytics as a "
			);
			// phpcs:enable WordPress.DB.PreparedSQL, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$show_quick_setup = ! wlfmc_is_true( get_option( 'wlfmc_wizard', false ) );
			$args             = array(
				'reports'          => apply_filters(
					'wlfmc_dashboard_reports',
					array(
						'created-lists'  => array(
							'title'      => __( 'Created Lists', 'wc-wlfmc-wishlist' ),
							'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/created-list.svg',
							'count'      => number_format( intval( $wishlist_count ) ),
							'link_class' => 'modal-toggle',
							'url'        => '',
						),
						'added-products' => array(
							'title'      => __( 'Added Products', 'wc-wlfmc-wishlist' ),
							'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/added-products.svg',
							'count'      => number_format( intval( $list_statistics->product_added ) ),
							'link_class' => 'modal-toggle',
							'url'        => add_query_arg(
								array(
									'page' => 'mc-analytics',
									'tab'  => 'products',
									'type' => 'class',
								),
								admin_url( 'admin.php' )
							),
						),
						'send-emails'    => array(
							'title'      => __( 'Send Emails', 'wc-wlfmc-wishlist' ),
							'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/send-emails.svg',
							'count'      => number_format( intval( $email_sent ) ),
							'link_class' => 'modal-toggle',
							'url'        => '',
						),
						'sales'          => array(
							'title'      => __( 'Sales', 'wc-wlfmc-wishlist' ),
							'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/sales.svg',
							'count'      => wc_price( $list_statistics->total_sales ),
							'link_class' => 'modal-toggle',
							'url'        => '',
						),
					)
				),
				'lists'            => apply_filters(
					'wlfmc_main_settings',
					array(
						'wishlist'       => array(
							'title'     => __( 'Wishlist', 'wc-wlfmc-wishlist' ),
							'image_url' => MC_WLFMC_URL . 'assets/backend/images/wishlist.svg',
							'url'       => add_query_arg( 'page', 'mc-wishlist-settings', admin_url( 'admin.php' ) ),
							'desc'      => __( 'Set a wishlist button to your store and find out your user\'s favorite items', 'wc-wlfmc-wishlist' ),
						),
						'save-for-later' => array(
							'title'           => __( 'Next Purchase Cart (Save for later)', 'wc-wlfmc-wishlist' ),
							'image_url'       => MC_WLFMC_URL . 'assets/backend/images/save-for-later.svg',
							'pro_label'       => __( 'PRO', 'wc-wlfmc-wishlist' ),
							'container_class' => 'orange-bordered',
							'popup'           => array(
								'class'      => 'modal-large',
								'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/save-for-later/save-for-later.gif',
								'image_link' => 'https://moreconvert.com/ox4p',
								'title_icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/crown.svg',
								'title'      => __( 'Unlock Now!', 'wc-wlfmc-wishlist' ) . '<br><span style="color: #71A926">' . __( 'MC Next Purchase Cart', 'wc-wlfmc-wishlist' ) . '</span>',
								'desc'       => __( 'Allow users to save products that they want to delete from their cart to buy later and active smart offers and recommendations based on users’ intentions.', 'wc-wlfmc-wishlist' ),
								'features'   => array(
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/save-for-later/feature-6.svg',
										'desc' => __( 'Increase Sales with Saved Carts', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/save-for-later/feature-2.svg',
										'desc' => __( 'Decrease Cart Abandonment', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/save-for-later/feature-3.svg',
										'desc' => __( 'Increase Average Order Value', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/save-for-later/feature-5.svg',
										'desc' => __( 'Cross-Selling Opportunities', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/save-for-later/feature-1.svg',
										'desc' => __( 'Stand out from competitors', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/save-for-later/feature-4.svg',
										'desc' => __( 'Email Marketing Opportunities', 'wc-wlfmc-wishlist' ),
									),
								),
								'buttons'    => array(
									array(
										'btn_label' => __( 'Upgrade Now', 'wc-wlfmc-wishlist' ),
										'btn_url'   => 'https://moreconvert.com/b9l9',
										'btn_class' => 'btn-flat btn-orange',
										'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g transform="translate(-375 -149)"><rect width="24" height="24" rx="6" transform="translate(375 149)" fill="#fff"></rect><path d="M8.537,3a.615.615,0,0,1,.615.615V8.9l1.411-1.411a.615.615,0,0,1,.87.87L8.972,10.818a.615.615,0,0,1-.87,0L5.641,8.357a.615.615,0,1,1,.87-.87L7.922,8.9V3.615A.615.615,0,0,1,8.537,3ZM3.615,9.768a.615.615,0,0,1,.615.615,5.837,5.837,0,0,0,.035.975,1.846,1.846,0,0,0,1.45,1.45,5.838,5.838,0,0,0,.975.035h3.692a5.839,5.839,0,0,0,.975-.035,1.846,1.846,0,0,0,1.45-1.45,5.839,5.839,0,0,0,.035-.975.615.615,0,1,1,1.231,0v.071a6.158,6.158,0,0,1-.059,1.144A3.076,3.076,0,0,1,11.6,14.016a6.157,6.157,0,0,1-1.144.059H6.62a6.157,6.157,0,0,1-1.144-.059A3.076,3.076,0,0,1,3.059,11.6,6.161,6.161,0,0,1,3,10.454c0-.023,0-.047,0-.071A.615.615,0,0,1,3.615,9.768Z" transform="translate(378.463 152.463)" fill="#fd5d00" fill-rule="evenodd"></path></g></svg>',
									),
									array(
										'btn_label' => __( 'Learn More', 'wc-wlfmc-wishlist' ),
										'btn_url'   => 'https://moreconvert.com/ox4p',
										'btn_class' => 'btn-flat btn-get-start',
										'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#fd5d00"></rect><path d="M6.891,5.181a.71.71,0,0,0-1,0L4.259,6.809V.71A.71.71,0,1,0,2.84.71v6.1L1.212,5.181a.71.71,0,0,0-1,1L3.049,9.025a.71.71,0,0,0,1.006,0L6.9,6.185A.714.714,0,0,0,6.891,5.181Z" transform="translate(7.383 15.55) rotate(-90)" fill="#fff" fill-rule="evenodd"></path></svg>',
									),
								),
							),
							'desc'            => __( 'Set a button for your users on their cart page and prevent them from deleting products when they aren\'t ready to buy.', 'wc-wlfmc-wishlist' ),
						),
						'multi-list'     => array(
							'title'           => __( 'Multi-list', 'wc-wlfmc-wishlist' ),
							'pro_label'       => __( 'PRO', 'wc-wlfmc-wishlist' ),
							'container_class' => 'orange-bordered',
							'image_url'       => MC_WLFMC_URL . 'assets/backend/images/multi-list.svg',
							'desc'            => __( 'Set a multi-list button to your store and allow to create of different lists depending on their needs', 'wc-wlfmc-wishlist' ),
							'popup'           => array(
								'class'      => 'modal-large',
								'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/multi-list/multi-list.gif',
								'image_link' => 'https://moreconvert.com/9m3g',
								'title_icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/crown.svg',
								'title'      => __( 'Unlock Now!', 'wc-wlfmc-wishlist' ) . '<br><span style="color: #5E94FF">' . __( 'MC Multi-List', 'wc-wlfmc-wishlist' ) . '</span>',
								'desc'       => __( 'With MC Multi-list, you allow your users to create multiple lists and share them everywhere, and you have a chance to offer them based on their lists automatically!', 'wc-wlfmc-wishlist' ),
								'features'   => array(
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/multi-list/feature-2.svg',
										'desc' => __( 'Efficient Product Categorization', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/multi-list/feature-1.svg',
										'desc' => __( 'Separate Lists for Different Occasions', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/multi-list/feature-3.svg',
										'desc' => __( 'List Sharing with Friends and Family', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/multi-list/feature-6.svg',
										'desc' => __( 'Creating Orders for Clients', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/multi-list/feature-4.svg',
										'desc' => __( 'Lists for B2B Sales', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/multi-list/feature-5.svg',
										'desc' => __( 'Personalized Email Campaigns', 'wc-wlfmc-wishlist' ),
									),
								),
								'buttons'    => array(
									array(
										'btn_label' => __( 'Upgrade Now', 'wc-wlfmc-wishlist' ),
										'btn_url'   => 'https://moreconvert.com/zhlm',
										'btn_class' => 'btn-flat btn-orange',
										'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g transform="translate(-375 -149)"><rect width="24" height="24" rx="6" transform="translate(375 149)" fill="#fff"></rect><path d="M8.537,3a.615.615,0,0,1,.615.615V8.9l1.411-1.411a.615.615,0,0,1,.87.87L8.972,10.818a.615.615,0,0,1-.87,0L5.641,8.357a.615.615,0,1,1,.87-.87L7.922,8.9V3.615A.615.615,0,0,1,8.537,3ZM3.615,9.768a.615.615,0,0,1,.615.615,5.837,5.837,0,0,0,.035.975,1.846,1.846,0,0,0,1.45,1.45,5.838,5.838,0,0,0,.975.035h3.692a5.839,5.839,0,0,0,.975-.035,1.846,1.846,0,0,0,1.45-1.45,5.839,5.839,0,0,0,.035-.975.615.615,0,1,1,1.231,0v.071a6.158,6.158,0,0,1-.059,1.144A3.076,3.076,0,0,1,11.6,14.016a6.157,6.157,0,0,1-1.144.059H6.62a6.157,6.157,0,0,1-1.144-.059A3.076,3.076,0,0,1,3.059,11.6,6.161,6.161,0,0,1,3,10.454c0-.023,0-.047,0-.071A.615.615,0,0,1,3.615,9.768Z" transform="translate(378.463 152.463)" fill="#fd5d00" fill-rule="evenodd"></path></g></svg>',
									),
									array(
										'btn_label' => __( 'Learn More', 'wc-wlfmc-wishlist' ),
										'btn_url'   => 'https://moreconvert.com/9m3g',
										'btn_class' => 'btn-flat btn-get-start',
										'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#fd5d00"></rect><path d="M6.891,5.181a.71.71,0,0,0-1,0L4.259,6.809V.71A.71.71,0,1,0,2.84.71v6.1L1.212,5.181a.71.71,0,0,0-1,1L3.049,9.025a.71.71,0,0,0,1.006,0L6.9,6.185A.714.714,0,0,0,6.891,5.181Z" transform="translate(7.383 15.55) rotate(-90)" fill="#fff" fill-rule="evenodd"></path></svg>',
									),
								),
							),

						),
						'waitlist'       => array(
							'title'           => __( 'Waitlist', 'wc-wlfmc-wishlist' ),
							'image_url'       => MC_WLFMC_URL . 'assets/backend/images/waitlist.svg',
							'pro_label'       => __( 'PRO', 'wc-wlfmc-wishlist' ), // PRO or GOLD .
							'container_class' => 'orange-bordered', // orange-bordered or gold-bordered.
							'popup'           => array(
								'class'      => 'modal-large',
								'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/waitlist.gif',
								'image_link' => 'https://moreconvert.com/g3ki',
								'title_icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/crown.svg',
								'title'      => __( 'Unlock Now!', 'wc-wlfmc-wishlist' ) . '<br><span style="color: #FF2366">' . __( 'Waitlist', 'wc-wlfmc-wishlist' ) . '</span>',
								'desc'       => __( 'Add a Customizable waitlist button, detailed waitlist page design, and counter option, you’ll never miss a sale again.', 'wc-wlfmc-wishlist' ),
								'features'   => array(
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/feature-3.svg',
										'desc' => __( 'Pre-launch Waitlist', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/feature-1.svg',
										'desc' => __( 'Price Change Alert', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/feature-2.svg',
										'desc' => __( 'Flash Sale Waitlist', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/feature-6.svg',
										'desc' => __( 'Low Stock Alert', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/feature-4.svg',
										'desc' => __( 'Back in Stock Notification for Similar Products', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/waitlist/feature-5.svg',
										'desc' => __( 'Integrated with other lists', 'wc-wlfmc-wishlist' ),
									),
								),
								'buttons'    => array(
									array(
										'btn_label' => __( 'Upgrade Now', 'wc-wlfmc-wishlist' ),
										'btn_url'   => 'https://moreconvert.com/8ljf',
										'btn_class' => 'btn-flat btn-orange',
										'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g transform="translate(-375 -149)"><rect width="24" height="24" rx="6" transform="translate(375 149)" fill="#fff"></rect><path d="M8.537,3a.615.615,0,0,1,.615.615V8.9l1.411-1.411a.615.615,0,0,1,.87.87L8.972,10.818a.615.615,0,0,1-.87,0L5.641,8.357a.615.615,0,1,1,.87-.87L7.922,8.9V3.615A.615.615,0,0,1,8.537,3ZM3.615,9.768a.615.615,0,0,1,.615.615,5.837,5.837,0,0,0,.035.975,1.846,1.846,0,0,0,1.45,1.45,5.838,5.838,0,0,0,.975.035h3.692a5.839,5.839,0,0,0,.975-.035,1.846,1.846,0,0,0,1.45-1.45,5.839,5.839,0,0,0,.035-.975.615.615,0,1,1,1.231,0v.071a6.158,6.158,0,0,1-.059,1.144A3.076,3.076,0,0,1,11.6,14.016a6.157,6.157,0,0,1-1.144.059H6.62a6.157,6.157,0,0,1-1.144-.059A3.076,3.076,0,0,1,3.059,11.6,6.161,6.161,0,0,1,3,10.454c0-.023,0-.047,0-.071A.615.615,0,0,1,3.615,9.768Z" transform="translate(378.463 152.463)" fill="#fd5d00" fill-rule="evenodd"></path></g></svg>',
									),
									array(
										'btn_label' => __( 'Learn More', 'wc-wlfmc-wishlist' ),
										'btn_url'   => 'https://moreconvert.com/g3ki',
										'btn_class' => 'btn-flat btn-get-start',
										'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#fd5d00"></rect><path d="M6.891,5.181a.71.71,0,0,0-1,0L4.259,6.809V.71A.71.71,0,1,0,2.84.71v6.1L1.212,5.181a.71.71,0,0,0-1,1L3.049,9.025a.71.71,0,0,0,1.006,0L6.9,6.185A.714.714,0,0,0,6.891,5.181Z" transform="translate(7.383 15.55) rotate(-90)" fill="#fff" fill-rule="evenodd"></path></svg>',
									),
								),
							),
							'desc'            => __( 'Set a button on your product page and help users to notify when a product comes back in stock or its price has changed.', 'wc-wlfmc-wishlist' ),
						),
						'abandoned-cart' => array(
							'title'           => __( 'Abandoned cart', 'wc-wlfmc-wishlist' ),
							'image_url'       => MC_WLFMC_URL . 'assets/backend/images/abandoned-cart.svg',
							'pro_label'       => __( 'SOON', 'wc-wlfmc-wishlist' ),
							'container_class' => 'orange-bordered',
							'popup'           => array(
								'image_url' => MC_WLFMC_URL . 'assets/backend/images/popup.svg',
								'title'     => __( 'More Options, More Income', 'wc-wlfmc-wishlist' ),
								'desc'      => __( 'Install the  premium version right now<br>to multiply your site revenue with<br>advanced and professional tools.', 'wc-wlfmc-wishlist' ),
								'buttons'   => array(
									array(
										'btn_label' => __( 'Get Now', 'wc-wlfmc-wishlist' ),
										'btn_url'   => 'https://moreconvert.com/pricing',
										'btn_class' => 'btn-primary orange-btn',
									),
									array(
										'btn_label' => __( 'Watch now', 'wc-wlfmc-wishlist' ),
										'btn_url'   => 'https://moreconvert.com/youtube',
										'btn_class' => 'orange-link',
									),
								),
							),
							'desc'            => __( 'set a button and allow them to ask for an estimate for some items and create a connection with users.', 'wc-wlfmc-wishlist' ),
						),
					)
				),
				'marketing'        => apply_filters(
					'wlfmc_marketing_toolkits',
					array(
						'email-automation' => array(
							'title'     => __( 'Sequential Email Automation', 'wc-wlfmc-wishlist' ),
							'image_url' => MC_WLFMC_URL . 'assets/backend/images/email-automation.svg',
							'url'       => add_query_arg( 'page', 'mc-email-automations', admin_url( 'admin.php' ) ),
							'desc'      => __( 'Set up once to send emails to users based on your terms', 'wc-wlfmc-wishlist' ),
						),
						'email-campaign'   => array(
							'title'           => __( 'One-Shot Email', 'wc-wlfmc-wishlist' ),
							'pro_label'       => __( 'PRO', 'wc-wlfmc-wishlist' ),
							'image_url'       => MC_WLFMC_URL . 'assets/backend/images/email-campaign.svg',
							'container_class' => 'orange-bordered',
							'popup'           => array(
								'class'      => 'modal-large',
								'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/campaign.gif',
								'image_link' => 'https://moreconvert.com/m1c9',
								'title_icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/crown.svg',
								'title'      => __( 'Unlock Now!', 'wc-wlfmc-wishlist' ) . '<br><span style="color: #E43B67">' . __( 'One-Shot Email', 'wc-wlfmc-wishlist' ) . '</span>',
								'desc'       => __( 'Select a specific group of users that add products to their lists and send them an email with dedicated coupons.', 'wc-wlfmc-wishlist' ),
								'features'   => array(
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-3.svg',
										'desc' => __( 'New Product Launch Announcement', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-1.svg',
										'desc' => __( 'Cross-Selling and Upselling', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-2.svg',
										'desc' => __( 'Seasonal Sales Promotion', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-6.svg',
										'desc' => __( 'Re-engagement Campaigns', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-4.svg',
										'desc' => __( 'Best-Selling Product Promotion', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/campaign/feature-5.svg',
										'desc' => __( 'One Time Personalized Recommendations', 'wc-wlfmc-wishlist' ),
									),
								),
								'buttons'    => array(
									array(
										'btn_label' => __( 'Upgrade Now', 'wc-wlfmc-wishlist' ),
										'btn_url'   => 'https://moreconvert.com/broj',
										'btn_class' => 'btn-flat btn-orange',
										'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g transform="translate(-375 -149)"><rect width="24" height="24" rx="6" transform="translate(375 149)" fill="#fff"></rect><path d="M8.537,3a.615.615,0,0,1,.615.615V8.9l1.411-1.411a.615.615,0,0,1,.87.87L8.972,10.818a.615.615,0,0,1-.87,0L5.641,8.357a.615.615,0,1,1,.87-.87L7.922,8.9V3.615A.615.615,0,0,1,8.537,3ZM3.615,9.768a.615.615,0,0,1,.615.615,5.837,5.837,0,0,0,.035.975,1.846,1.846,0,0,0,1.45,1.45,5.838,5.838,0,0,0,.975.035h3.692a5.839,5.839,0,0,0,.975-.035,1.846,1.846,0,0,0,1.45-1.45,5.839,5.839,0,0,0,.035-.975.615.615,0,1,1,1.231,0v.071a6.158,6.158,0,0,1-.059,1.144A3.076,3.076,0,0,1,11.6,14.016a6.157,6.157,0,0,1-1.144.059H6.62a6.157,6.157,0,0,1-1.144-.059A3.076,3.076,0,0,1,3.059,11.6,6.161,6.161,0,0,1,3,10.454c0-.023,0-.047,0-.071A.615.615,0,0,1,3.615,9.768Z" transform="translate(378.463 152.463)" fill="#fd5d00" fill-rule="evenodd"></path></g></svg>',
									),
									array(
										'btn_label' => __( 'Learn More', 'wc-wlfmc-wishlist' ),
										'btn_url'   => 'https://moreconvert.com/m1c9',
										'btn_class' => 'btn-flat btn-get-start',
										'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#fd5d00"></rect><path d="M6.891,5.181a.71.71,0,0,0-1,0L4.259,6.809V.71A.71.71,0,1,0,2.84.71v6.1L1.212,5.181a.71.71,0,0,0-1,1L3.049,9.025a.71.71,0,0,0,1.006,0L6.9,6.185A.714.714,0,0,0,6.891,5.181Z" transform="translate(7.383 15.55) rotate(-90)" fill="#fff" fill-rule="evenodd"></path></svg>',
									),
								),
							),
							'desc'            => __( 'Send emails to your selected subscribers', 'wc-wlfmc-wishlist' ),
						),
						'analytics'        => array(
							'title'           => __( 'Analytics', 'wc-wlfmc-wishlist' ),
							'pro_label'       => __( 'PRO', 'wc-wlfmc-wishlist' ),
							'image_url'       => MC_WLFMC_URL . 'assets/backend/images/analytics.svg',
							'container_class' => 'orange-bordered',
							'popup'           => array(
								'class'      => 'modal-large',
								'image_url'  => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/analytics.gif',
								'image_link' => 'https://moreconvert.com/cv2i',
								'title_icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/crown.svg',
								'title'      => __( 'Unlock Now!', 'wc-wlfmc-wishlist' ) . '<br><span style="color:#A45EFF">' . __( 'Analytics', 'wc-wlfmc-wishlist' ) . '</span>',
								'desc'       => __( 'By analyzing individual user’s lists, you can tailor your marketing strategies to be more specific and effective, resulting in increased sales.', 'wc-wlfmc-wishlist' ),
								'features'   => array(
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/feature-3.svg',
										'desc' => __( 'Segment your audience', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/feature-6.svg',
										'desc' => __( 'Personalize email content', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/feature-4.svg',
										'desc' => __( 'Track customer journey', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/feature-2.svg',
										'desc' => __( 'Increase customer retention', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/feature-5.svg',
										'desc' => __( 'Predict future sales', 'wc-wlfmc-wishlist' ),
									),
									array(
										'icon' => MC_WLFMC_URL . 'assets/backend/images/dashboard/analytics/feature-1.svg',
										'desc' => __( 'Identify popular categories', 'wc-wlfmc-wishlist' ),
									),
								),
								'buttons'    => array(
									array(
										'btn_label' => __( 'Upgrade Now', 'wc-wlfmc-wishlist' ),
										'btn_url'   => 'https://moreconvert.com/wmma',
										'btn_class' => 'btn-flat btn-orange',
										'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g transform="translate(-375 -149)"><rect width="24" height="24" rx="6" transform="translate(375 149)" fill="#fff"></rect><path d="M8.537,3a.615.615,0,0,1,.615.615V8.9l1.411-1.411a.615.615,0,0,1,.87.87L8.972,10.818a.615.615,0,0,1-.87,0L5.641,8.357a.615.615,0,1,1,.87-.87L7.922,8.9V3.615A.615.615,0,0,1,8.537,3ZM3.615,9.768a.615.615,0,0,1,.615.615,5.837,5.837,0,0,0,.035.975,1.846,1.846,0,0,0,1.45,1.45,5.838,5.838,0,0,0,.975.035h3.692a5.839,5.839,0,0,0,.975-.035,1.846,1.846,0,0,0,1.45-1.45,5.839,5.839,0,0,0,.035-.975.615.615,0,1,1,1.231,0v.071a6.158,6.158,0,0,1-.059,1.144A3.076,3.076,0,0,1,11.6,14.016a6.157,6.157,0,0,1-1.144.059H6.62a6.157,6.157,0,0,1-1.144-.059A3.076,3.076,0,0,1,3.059,11.6,6.161,6.161,0,0,1,3,10.454c0-.023,0-.047,0-.071A.615.615,0,0,1,3.615,9.768Z" transform="translate(378.463 152.463)" fill="#fd5d00" fill-rule="evenodd"></path></g></svg>',
									),
									array(
										'btn_label' => __( 'Learn More', 'wc-wlfmc-wishlist' ),
										'btn_url'   => 'https://moreconvert.com/cv2i',
										'btn_class' => 'btn-flat btn-get-start',
										'btn_svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#fd5d00"></rect><path d="M6.891,5.181a.71.71,0,0,0-1,0L4.259,6.809V.71A.71.71,0,1,0,2.84.71v6.1L1.212,5.181a.71.71,0,0,0-1,1L3.049,9.025a.71.71,0,0,0,1.006,0L6.9,6.185A.714.714,0,0,0,6.891,5.181Z" transform="translate(7.383 15.55) rotate(-90)" fill="#fff" fill-rule="evenodd"></path></svg>',
									),
								),
							),
							'desc'            => __( 'Find out the ways you can boost your sales based on users\' activities', 'wc-wlfmc-wishlist' ),
						),
					)
				),
				'blogs'            => apply_filters(
					'wlfmc_dashboard_blogs',
					array(
						'post-1' => array(
							'title'     => __( '4+ WordPress Retargeting Strategies for 2023', 'wc-wlfmc-wishlist' ),
							'image_url' => MC_WLFMC_URL . 'assets/backend/images/dashboard/blogs/post-4.png',
							'post_link' => 'https://moreconvert.com/pvg9',
						),
						'post-2' => array(
							'title'     => __( 'Ultimate Guide to Increase WooCommerce Sales | Step-by-Step', 'wc-wlfmc-wishlist' ),
							'image_url' => MC_WLFMC_URL . 'assets/backend/images/dashboard/blogs/post-2.png',
							'post_link' => 'https://moreconvert.com/evgg',
						),
						'post-3' => array(
							'title'     => __( 'Improve User Experience in WooCommerce [10 Unique Ways]', 'wc-wlfmc-wishlist' ),
							'image_url' => MC_WLFMC_URL . 'assets/backend/images/dashboard/blogs/post-3.png',
							'post_link' => 'https://moreconvert.com/sdwf',
						),
						'post-4' => array(
							'title'     => __( '5+ Magic Ways to Increase Online Sales with Wishlists', 'wc-wlfmc-wishlist' ),
							'image_url' => MC_WLFMC_URL . 'assets/backend/images/dashboard/blogs/post-1.png',
							'post_link' => 'https://moreconvert.com/shwu',
						),
					)
				),
				'global_settings'  => add_query_arg( 'page', 'mc-global-settings', admin_url( 'admin.php' ) ),
				'marketing_settings'  => add_query_arg( array(
                        'page' => 'mc-global-settings',
                        'tab'  => 'marketing',
                    )
                    , admin_url( 'admin.php' ) ),
				'marketing_tips'   => 'https://moreconvert.com/7rz2',
				'show_quick_setup' => $show_quick_setup,
			);

			wlfmc_get_template( 'admin/mc-dashboard.php', $args );
		}

		/**
		 * Show admin option panel.
		 *
		 * @return void
		 *
		 * @version 1.2.1
		 */
		public function show_settings() {
			?>
			<div id="wlfmc_options">
				<?php
				if ( $this->installed ) {

					$fields = new MCT_Fields( $this->global_options );
					$fields->output();
				}
				?>
			</div>
			<div id="snackbar"></div>
			<?php
		}

		/**
		 * Show admin wishlist option panel.
		 *
		 * @return void
		 *
		 * @since 1.5.5
		 */
		public function show_wishlist_settings() {
			?>
			<div id="wlfmc_options">
				<?php
				if ( $this->installed ) {

					$fields = new MCT_Fields( $this->wishlist_options );
					$fields->output();
				}
				?>
			</div>
			<div id="snackbar"></div>
			<?php
		}

		/**
		 * Show admin option panel.
		 *
		 * @return void
		 *
		 * @version 1.5.5
		 */
		public function show_automation_settings() {
			?>
			<div id="wlfmc_options">
				<?php
				if ( $this->installed ) {

					if ( has_action( 'wlfmc_email_automation_admin_settings' ) ) {

						/**
						 * Show marketing toolkit settings.
						 */
						do_action( 'wlfmc_email_automation_admin_settings' );

					}
				}
				?>
			</div>
			<div id="snackbar"></div>
			<?php
		}

		/**
		 * Show welcome screen.
		 *
		 * @since 1.2.0
		 */
		public function show_welcome_screen() {
			?>
			<div id="wlfmc_options">
				<?php
				if ( $this->installed ) {
					$global_url = add_query_arg(
						array(
							'page' => 'mc-global-settings',
						),
						admin_url( 'admin.php' )
					);
					$global_share_url = add_query_arg(
						array(
							'page' => 'mc-global-settings',
							'tab'  => 'share'
						),
						admin_url( 'admin.php' )
					);
					$wishlist_page_setting_url = add_query_arg(
						array(
							'page' => 'mc-wishlist-settings',
                            'tab'  => 'page-settings'
						),
						admin_url( 'admin.php' )
					);
					$wishlist_setting_url = add_query_arg(
						array(
							'page' => 'mc-wishlist-settings',
						),
						admin_url( 'admin.php' )
					);
                    $wishlist_button_url = add_query_arg(
	                    array(
		                    'page' => 'mc-wishlist-settings',
		                    'tab'  => 'button',
	                    ),
	                    admin_url( 'admin.php' )
                    );
					$steps_options = array(
						'steps'   => apply_filters(
							'wlfmc_admin_steps',
							array(
								'welcome' => array(
									'steptitle' => __( 'Welcome', 'wc-wlfmc-wishlist' ),
									//'before_title' => '<img width="90" height="90" alt="welcome" src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/welcome.gif" />',
									'title'     => __( 'Welcome To More Convert Challenge!', 'wc-wlfmc-wishlist' ),
									'subtitle'  => __( 'How to Sell More Without More Traffic?', 'wc-wlfmc-wishlist' ),
									'content'   => __( 'You made the best decision by choosing the MC Wishlist; our goal is you can sell more, not to entertain your user with some buttons and lists 😉 Ready to have some simple settings together?', 'wc-wlfmc-wishlist' ),
								),
								'step-1'  => array(
									'steptitle'   => __( 'Page Setup', 'wc-wlfmc-wishlist' ),
									'title'       => __( 'Page Setup Options', 'wc-wlfmc-wishlist' ),
									'top_desc'    => __( 'Enter a name for the Wishlist page. This name will be used for the user menu in the WooCommerce user panel.', 'wc-wlfmc-wishlist' ),
									'bottom_desc' => sprintf( __( 'All settings of this step and the next steps can be changed through the %s.', 'wc-wlfmc-wishlist' ), '<a href="'. esc_url( $wishlist_setting_url ) . '" target="_blank">'. __( 'MC Wishlist > wishlist', 'wc-wlfmc-wishlist' ) .'</a>' ),
									'fields'      => array(
										'wishlist_page_title' => array(
											'section' => 'button-display',
											'label'   => __( 'Wishlist page name', 'wc-wlfmc-wishlist' ),
											'default' => __( 'My Wishlist', 'wc-wlfmc-wishlist' ),
											'type'    => 'text',

										),
										'who_can_see_wishlist_options' => array(
											'section' => 'button-display',
											'label'   => __( 'Enable Wishlist for', 'wc-wlfmc-wishlist' ),
											'type'    => 'select',
											'class'   => 'select2-trigger',
											'options' => array(
												'all'   => __( 'All users', 'wc-wlfmc-wishlist' ),
												'users' => __( 'Logged in users', 'wc-wlfmc-wishlist' ),
											),
											'default' => 'all',
										),
									),
								),
								'step-2'  => array(
									'steptitle'   => __( 'Button', 'wc-wlfmc-wishlist' ),
									'title'       => __( 'Button Options', 'wc-wlfmc-wishlist' ),
									'top_desc'    => __( 'Choose where the Add to Wishlist button is displayed on the product page and the listings (like the shop page).', 'wc-wlfmc-wishlist' ),
									'bottom_desc' => sprintf( __( 'You can change the color and icon of the button later from %s', 'wc-wlfmc-wishlist' ), '<a href="'. esc_url( $wishlist_button_url ) . '" target="_blank">'. __( 'MC Wishlist > wishlist(button tab & text tab)', 'wc-wlfmc-wishlist' ) .'</a>' ),
									'fields'      => array(
										'wishlist_button_position' => array(
											'section' => 'button-display',
											'label'   => __( 'Button position on product page', 'wc-wlfmc-wishlist' ),
											'type'    => 'select',
											'class'   => 'select2-trigger',
											'desc'    => __( 'These settings are the default and may not sync with your Theme. In that case, you should use the plugin shortcodes.', 'wc-wlfmc-wishlist' ),
											'options' => array(
												'before_add_to_cart' => __( 'Before "add to cart" form', 'wc-wlfmc-wishlist' ),
												'after_add_to_cart' => __( 'After "add to cart" form', 'wc-wlfmc-wishlist' ),
												'before_add_to_cart_button' => __( 'Before "add to cart" button', 'wc-wlfmc-wishlist' ),
												'after_add_to_cart_button' => __( 'After "add to cart" button', 'wc-wlfmc-wishlist' ),
												'image_top_left' => __( 'On image - top left', 'wc-wlfmc-wishlist' ),
												'image_top_right' => __( 'On image - top right', 'wc-wlfmc-wishlist' ),
												'image_bottom_left' => __( 'On image - bottom left', 'wc-wlfmc-wishlist' ),
												'image_bottom_right' => __( 'On image - bottom right', 'wc-wlfmc-wishlist' ),
												'summary' => __( 'After summary', 'wc-wlfmc-wishlist' ),
												'shortcode' => __( 'Use shortcode', 'wc-wlfmc-wishlist' ),
											),
										),
										'shortcode_button' => array(
											'label'   => __( 'Shortcode button', 'wc-wlfmc-wishlist' ),
											'type'    => 'copy-text',
											'section' => 'button-display',
											'default' => '[wlfmc_add_to_wishlist is_single="true"]',
											'desc'    => '<a href="https://moreconvert.com/6zox" target="_blank">' . __( 'learn more on how to use it.', 'wc-wlfmc-wishlist' ) . '</a>',
											'dependencies' => array(
												'id' => 'wishlist_button_position',
												'value' => 'shortcode',
											),
											'help'    => __( 'Use this shortcode to specify a custom position. Just copy this shortcode wherever you want the button to be displayed.', 'wc-wlfmc-wishlist' ),
										),
										'single-position-inner-column-start' => array(
											'type'    => 'columns-start',
											'columns' => 1,
										),
										'single-position-inner-row-start' => array(
											'type'  => 'column-start',
											'class' => 'flexible-rows',
										),
										'single_summary' => array(
											'label' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
											'type'  => 'html',
											'html'  => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/single_summary.svg" alt="position" />',
											'dependencies' => array(
												'id' => 'wishlist_button_position',
												'value' => 'summary',
											),
										),
										'single_before_add_to_cart' => array(
											'label' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
											'type'  => 'html',
											'html'  => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/single_before_add_to_cart.svg" alt="position" />',
											'dependencies' => array(
												'id' => 'wishlist_button_position',
												'value' => 'before_add_to_cart',
											),
										),
										'single_after_add_to_cart' => array(
											'label' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
											'type'  => 'html',
											'html'  => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/single_after_add_to_cart.svg" alt="position" />',
											'dependencies' => array(
												'id' => 'wishlist_button_position',
												'value' => 'after_add_to_cart',
											),
										),
										'single_before_add_to_cart_button' => array(
											'label' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
											'type'  => 'html',
											'html'  => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/single_before_add_to_cart_button.svg" alt="position" />',
											'dependencies' => array(
												'id' => 'wishlist_button_position',
												'value' => 'before_add_to_cart_button',
											),
										),
										'single_after_add_to_cart_button' => array(
											'label' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
											'type'  => 'html',
											'html'  => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/single_after_add_to_cart_button.svg" alt="position" />',
											'dependencies' => array(
												'id' => 'wishlist_button_position',
												'value' => 'after_add_to_cart_button',
											),
										),
										'single_image_top_left' => array(
											'label' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
											'type'  => 'html',
											'html'  => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/single_image_top_left.svg" alt="position" />',
											'dependencies' => array(
												'id' => 'wishlist_button_position',
												'value' => 'image_top_left',
											),
										),
										'single_image_top_right' => array(
											'label' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
											'type'  => 'html',
											'html'  => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/single_image_top_right.svg" alt="position" />',
											'dependencies' => array(
												'id' => 'wishlist_button_position',
												'value' => 'image_top_right',
											),
										),
										'single_image_bottom_left' => array(
											'label' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
											'type'  => 'html',
											'html'  => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/single_image_bottom_left.svg" alt="position" />',
											'dependencies' => array(
												'id' => 'wishlist_button_position',
												'value' => 'image_bottom_left',
											),
										),
										'single_image_bottom_right' => array(
											'label' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
											'type'  => 'html',
											'html'  => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/single_image_bottom_right.svg" alt="position" />',
											'dependencies' => array(
												'id' => 'wishlist_button_position',
												'value' => 'image_bottom_right',
											),
										),
										'single-position-inner-row-end' => array(
											'type' => 'column-end',
										),
										'single-position-inner-column-end' => array(
											'type' => 'columns-end',
										),
										'loop_position'    => array(
											'section' => 'button-display',
											'label'   => __( 'Button position on listing', 'wc-wlfmc-wishlist' ),
                                            'desc'    => __( 'These settings are the default and may not sync with your Theme. In that case, you should use the plugin shortcodes.', 'wc-wlfmc-wishlist' ),
											'default' => 'after_add_to_cart',
											'type'    => 'select',
											'class'   => 'select2-trigger',
											'options' => array(
												'image_top_left' => __( 'On image - top left', 'wc-wlfmc-wishlist' ),
												'image_top_right' => __( 'On image - top right', 'wc-wlfmc-wishlist' ),
												'before_add_to_cart' => __( 'Before "add to cart" button', 'wc-wlfmc-wishlist' ),
												'after_add_to_cart' => __( 'After "add to cart" button', 'wc-wlfmc-wishlist' ),
												'shortcode' => __( 'Use shortcode', 'wc-wlfmc-wishlist' ),
											),
										),
										'loop_shortcode_button' => array(
											'label'   => __( 'Shortcode button', 'wc-wlfmc-wishlist' ),
											'type'    => 'copy-text',
											'section' => 'button-display',
											'default' => '[wlfmc_add_to_wishlist is_single=""]',
											'desc'    => '<a href="https://moreconvert.com/6zox" target="_blank">' . __( 'learn more on how to use it.', 'wc-wlfmc-wishlist' ) . '</a>',
											'dependencies' => array(
												'id'    => 'loop_position',
												'value' => 'shortcode',
											),
										),
										'loop-position-inner-column-start' => array(
											'type'    => 'columns-start',
											'columns' => 1,
										),
										'loop-position-inner-row-start' => array(
											'type'  => 'column-start',
											'class' => 'flexible-rows',
										),
										'loop_before_add_to_cart' => array(
											'label' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
											'type'  => 'html',
											'html'  => '<div class="loop-image-positions"><img class="loop-bg" src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/loop-bg.svg" alt="loop bg" /><img class="loop-item" src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/loop_before_add_to_cart.svg" alt="position" /></div>',
											'dependencies' => array(
												'id' => 'loop_position',
												'value' => 'before_add_to_cart',
											),
										),
										'loop_after_add_to_cart' => array(
											'label' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
											'type'  => 'html',
											'html'  => '<div class="loop-image-positions"><img class="loop-bg" src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/loop-bg.svg" alt="loop bg" /><img class="loop-item" src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/loop_after_add_to_cart.svg" alt="position" /></div>',
											'dependencies' => array(
												'id' => 'loop_position',
												'value' => 'after_add_to_cart',
											),
										),
										'loop_image_top_left' => array(
											'label' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
											'type'  => 'html',
											'html'  => '<div class="loop-image-positions"><img class="loop-bg" src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/loop-bg.svg" alt="loop bg" /><img class="loop-item" src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/loop_image_top_left.svg" alt="position" /></div>',
											'dependencies' => array(
												'id' => 'loop_position',
												'value' => 'image_top_left',
											),
										),
										'loop_image_top_right' => array(
											'label' => __( 'Preview:', 'wc-wlfmc-wishlist' ),
											'type'  => 'html',
											'html'  => '<div class="loop-image-positions"><img class="loop-bg" src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/loop-bg.svg" alt="loop bg" /><img class="loop-item" src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/loop_image_top_right.svg" alt="position" /></div>',
											'dependencies' => array(
												'id' => 'loop_position',
												'value' => 'image_top_right',
											),
										),
										'loop-position-inner-row-end' => array(
											'type' => 'column-end',
										),
										'loop-position-inner-column-end' => array(
											'type' => 'columns-end',
										),
										'button_label_add' => array(
											'section' => 'button-display',
											'label'   => __( '"Add To Wishlist" text', 'wc-wlfmc-wishlist' ),
											'type'    => 'text',
											'default' => __( 'Add To Wishlist', 'wc-wlfmc-wishlist' ),
										),
									),
								),
								'step-3'  => array(
									'steptitle'   => __( 'Processing', 'wc-wlfmc-wishlist' ),
									'title'       => __( 'Processing Options', 'wc-wlfmc-wishlist' ),
									'top_desc'    => __( 'Adjust the behavior of the Wishlist button after clicking on it and after clicking again.', 'wc-wlfmc-wishlist' ),
									'bottom_desc' => sprintf( __( 'you can change the pop up details and other settings from %s and %s', 'wc-wlfmc-wishlist' ), '<a href="'. esc_url( $wishlist_setting_url ) . '" target="_blank">'. __( 'MC Wishlist > wishlist', 'wc-wlfmc-wishlist' ) .'</a>', '<a href="'. esc_url( $global_url ) . '" target="_blank">'. __( 'MC Wishlist > global', 'wc-wlfmc-wishlist' ) .'</a>' ),
									'fields'      => array(
										'click_wishlist_button_behavior' => array(
											'section' => 'button-display',
											'label'   => __( 'After clicking wishlist button', 'wc-wlfmc-wishlist' ),
											'type'    => 'select',
											'class'   => 'select2-trigger',
											'options' => array(
												'open-popup' => __( 'Open the pop up', 'wc-wlfmc-wishlist' ),
												'just-add' => __( 'Just add it to Wishlist', 'wc-wlfmc-wishlist' ),
												'add-redirect' => __( 'Add and redirect to Wishlist page', 'wc-wlfmc-wishlist' ),
											),
										),
										'after_second_click'   => array(
											'section' => 'button-display',
											'label'   => __( 'Second click action', 'wc-wlfmc-wishlist' ),
											'type'    => 'select',
											'class'   => 'select2-trigger',
											'options' => array(
												'wishlist' => __( 'Go to the wishlist page', 'wc-wlfmc-wishlist' ),
												'remove'   => __( 'Remove from wishlist', 'wc-wlfmc-wishlist' ),
												'error'    => __( 'Display the error', 'wc-wlfmc-wishlist' ),
											),
											'help'    => __( 'After the product is added to the wishlist, What should happen if the user clicks the wishlist button again?', 'wc-wlfmc-wishlist' ),
											'default' => 'remove',
										),
										'remove_from_wishlist' => array(
											'section' => 'global-settings',
											'label'   => __( 'Remove from the wishlist if', 'wc-wlfmc-wishlist' ),
											'type'    => 'select',
											'class'   => 'select2-trigger',
											'options' => array(
												'none' => __( 'Do not remove', 'wc-wlfmc-wishlist' ),
												'added-to-cart' => __( 'Added to the card', 'wc-wlfmc-wishlist' ),
												'completed' => __( 'Purchase completed', 'wc-wlfmc-wishlist' ),
												'processing' => __( 'Purchase processing', 'wc-wlfmc-wishlist' ),
											),
										),
									),
								),
								'step-4'  => array(
									'steptitle'   => __( 'Share', 'wc-wlfmc-wishlist' ),
									'title'       => __( 'Share Options', 'wc-wlfmc-wishlist' ),
									'bottom_desc' => sprintf( __( 'you can change the marketing details  from %s and %s', 'wc-wlfmc-wishlist' ), '<a href="'. esc_url( $global_share_url ) . '" target="_blank">'. __( 'MC Wishlist > global > share tab', 'wc-wlfmc-wishlist' ) .'</a>', '<a href="'. esc_url( $wishlist_page_setting_url ) . '" target="_blank">'. __( 'MC Wishlist > wishlist > page tab', 'wc-wlfmc-wishlist' ) .'</a>' ),
									'fields'      => array(
										'enable_share' => array(
											'section' => 'button-display',
											'label'   => __( 'Users share their Wishlist on social media', 'wc-wlfmc-wishlist' ),
											'type'    => 'switch',
										),
										'share_items' => array(
											'section' => 'global-settings',
											'label'   => __( 'Active share buttons', 'wc-wlfmc-wishlist' ),
											'desc'    => __( 'Which social media icons show on the sharing bar?', 'wc-wlfmc-wishlist' ),
											'type'    => 'checkbox-group',
											'options' => array(
												'facebook' => __( 'Facebook', 'wc-wlfmc-wishlist' ),
												'messenger' => __( 'Facebook messenger', 'wc-wlfmc-wishlist' ),
												'twitter' => __( 'Twitter', 'wc-wlfmc-wishlist' ),
												'whatsapp' => __( 'Whatsapp', 'wc-wlfmc-wishlist' ),
												'telegram' => __( 'Telegram', 'wc-wlfmc-wishlist' ),
												'email' => __( 'Email', 'wc-wlfmc-wishlist' ),
												'copy' => __( 'Share link', 'wc-wlfmc-wishlist' ),
												'pdf'  => __( 'Download pdf', 'wc-wlfmc-wishlist' ),
											),
											'default' => array(
												'facebook',
												'messenger',
												'twitter',
												'whatsapp',
												'telegram',
												'email',
												'copy',
												'pdf',
											),
											'dependencies' => array(
												'id' => 'enable_share',
												'value' => '1',
											),
											'help'    => __( 'In what medias do you prefer your user to be able to share his / her wishlist?', 'wc-wlfmc-wishlist' ),
										),
									),
								),
								'step-5'  => array(
									'steptitle'   => __( 'Features', 'wc-wlfmc-wishlist' ),
									'title'       => __( 'Choose the plan that fits your needs', 'wc-wlfmc-wishlist' ),
									'content'     => __( 'Each plan includes a set of features designed to enhance your WordPress site. Choose the plan that best meets your needs.', 'wc-wlfmc-wishlist' ),
									'bottom_desc' => __( 'You can always upgrade anytime later from the MoreConvert dashboard if needed. Starter is great for beginners, but Premium offers more advanced features.', 'wc-wlfmc-wishlist' ),
								),
								'last'    => array(
									'steptitle'   => __( 'Support', 'wc-wlfmc-wishlist' ),
									'title'       => __( 'Support Options', 'wc-wlfmc-wishlist' ),
									'bottom_desc' => __( 'Your satisfaction is very important to us, if you have an idea, problem or question, let us know, we will try to answer you very quickly.<br>Our goal and yours is to increase sales, I hope we succeed in this challenge.', 'wc-wlfmc-wishlist' ),
									'fields'      => array(
										'live_chat'        => array(
											'section' => 'global-settings',
											'label'   => __( 'Enable support chat option to get fast and online support', 'wc-wlfmc-wishlist' ),
											'type'    => 'switch',
											'default' => '1',
										),
										'is_cache_enabled' => array(
											'section' => 'global-settings',
											'label'   => __( 'Cache protection', 'wc-wlfmc-wishlist' ),
											'type'    => 'switch',
											'default' => '1',
											'help'    => __( 'If a caching plugin or hosting caching is enabled on your website and the wishlist is not working properly, enable this option.', 'wc-wlfmc-wishlist' ),
										),
										'enable_track'     => array(
											'section' => 'global-settings',
											'label'   => __( 'I want to get more and better features of this plugin with data track permission (non-personal) to help improve it.', 'wc-wlfmc-wishlist' ),
											'type'    => 'switch',
											'default' => '1',
										),
									),
								),
								'ready'   => array(
									'steptitle' => __( 'Ready!', 'wc-wlfmc-wishlist' ),
									'before_title' => '<img width="163" height="141" alt="ready" src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/ready.gif" />',
									'title'     => __( 'Congratulations! Everything is set!', 'wc-wlfmc-wishlist' ),
									'content'   => __( 'You can have more and more detailed settings from the plugin settings.<br>This plugin is very powerful, and we are developing it tremendously to work on your sales in various ways.<br>To support us, be sure to keep the plugin active and let us know your ideas.', 'wc-wlfmc-wishlist' ),
									'buttons'   => array(
										array(
											'btn_label' => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/support.svg" width="16" height="19" alt="support" /><span>'. __( 'Support', 'wc-wlfmc-wishlist' ) . '</span>',
											'btn_class' => 'btn-primary purplelight-btn d-flex f-center gap-5',
											'btn_url'   => 'https://moreconvert.com/m6z4',
										),
                                        array(
											'btn_label' => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/book.svg" width="12" height="16" alt="doc" /><span>'. __( 'Documentation', 'wc-wlfmc-wishlist' ) . '</span>',
											'btn_class' => 'btn-primary purplelight-btn d-flex f-center gap-5',
											'btn_url'   => 'https://moreconvert.com/v4nf',
										),
										array(
											'btn_label' => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/crown.svg" width="22" height="17" alt="pro" /><span>'. __( 'About Premium', 'wc-wlfmc-wishlist' ) . '</span>',
											'btn_class' => 'btn-primary d-flex f-center gap-5',
											'btn_url'   => 'https://moreconvert.com/xwyx',
										),
										array(
											'btn_label' => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/wizard/dashboard.svg" width="18" height="18" alt="dashboard" /><span>'. __( 'Go To Dashboard', 'wc-wlfmc-wishlist' ) . '</span>',
											'btn_class' => 'btn-primary brown-btn d-flex f-center gap-5',
											'btn_url'   => admin_url(),
										),
									),
								),
							)
						),
						'options' => apply_filters(
							'wlfmc_admin_steps_options',
							array(
								'global-settings' => array(
									'live_chat',
									'is_cache_enabled',
									'remove_from_wishlist',
                                    'share_items'
								),
								'button-display'  => array(
									'enable_share',
									'click_wishlist_button_behavior',
									'after_second_click',
									'wishlist_button_position',
									'loop_position',
									'button_label_add',
									'wishlist_page_title',
									'who_can_see_wishlist_options',
								),
							)
						),
						'title'   => __( 'MoreConvert', 'wc-wlfmc-wishlist' ),
						'logo'    => '<img src="' . MC_WLFMC_URL . 'assets/backend/images/logo.svg" width="45" height="40"  alt="logo"/>',
						'type'    => 'wizard-type',
						'id'      => 'wlfmc_options',
					);
					// Remove features step if premium version is enabled.
					if ( defined( 'MC_WLFMC_PREMIUM' ) && isset( $steps_options['steps']['step-5'] ) ) {
						unset( $steps_options['steps']['step-5'] );
					}

					$fields = new MCT_Fields( $steps_options );
					$fields->output();
				}
				?>
			</div>
			<div id="snackbar"></div>
			<?php
		}

		/**
		 * Wizard print welcome video.
		 *
		 * @since 1.7.0
		 */
        public function add_welcome_video() {
	        ?>
            <video width="90" loop autoplay><source src="<?php echo esc_url( MC_WLFMC_URL . 'assets/backend/images/wizard/welcome.webm');?>" type="video/webm" /></video>
            <?php
	    }

		/**
		 * Wizard features and popup.
		 *
		 * @since 1.5.9
		 */
		public function add_wizard_features() {
			wlfmc_get_template( 'admin/mc-wizard-features.php' );
		}

		/**
		 * Run the installation.
		 *
		 * @return void
		 *
		 * @version 1.0.1
		 */
		public function install() {
			if ( wp_doing_ajax() ) {
				return;
			}

			$stored_db_version = get_option( 'wlfmc_db_version' );
			$stored_version    = get_option( 'wlfmc_version' );

			if ( ! $stored_db_version || ! $this->installed ) {
				// fresh installation.
				WLFMC_Install()->init();
			} elseif ( version_compare( $stored_db_version, WLFMC_DB_VERSION, '<' ) || version_compare( $stored_version, WLFMC_VERSION, '<' ) ) {
				// update .
				WLFMC_Install()->update( $stored_version, $stored_db_version );
				do_action( 'wlfmc_updated' );
			}

			// Plugin installed.
			do_action( 'wlfmc_installed' );
		}

		/**
		 * Check premium minimum version
		 *
		 * @return void
		 */
		public function check_minimum_version() {

			if ( defined( 'WLFMC_PREMIUM_VERSION' ) && version_compare( WLFMC_PREMIUM_VERSION, $this->minimum_pro_version, '<' ) ) {
				$premium_file = 'smart-wishlist-for-more-convert-premium/smart-wishlist-for-more-convert-premium.php';
				if ( is_plugin_active( $premium_file ) ) {
					deactivate_plugins( $premium_file );
				}
			}
		}


		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC_Admin
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
 * Unique access to instance of WLFMC_Admin class
 *
 * @return WLFMC_Admin
 */
function WLFMC_Admin(): WLFMC_Admin { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WLFMC_Admin::get_instance();
}
