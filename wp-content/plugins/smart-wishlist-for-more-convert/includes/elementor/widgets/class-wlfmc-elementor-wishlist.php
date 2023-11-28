<?php
/**
 * Wishlist Widget.
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.6.8
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
if ( ! class_exists( 'WLFMC_Elementor_Wishlist' ) ) {
	/**
	 * Wishlist Elementor Widget
	 */
	class WLFMC_Elementor_Wishlist extends Widget_Base {

		/**
		 * Get widget name.
		 *
		 * Retrieve WishList widget name.
		 *
		 * @return string Widget Name.
		 * @since 1.0.1
		 * @access public
		 */
		public function get_name(): string {
			return 'wlfmc-wish-list';
		}

		/**
		 * Get widget title.
		 *
		 * Retrieve WishList widget title.
		 *
		 * @return string Widget Title.
		 * @since 1.0.1
		 * @access public
		 */
		public function get_title(): string {
			return esc_html__( 'WishList', 'wc-wlfmc-wishlist' );
		}

		/**
		 * Get widget icon.
		 *
		 * Retrieve WishList widget icon.
		 *
		 * @return string Widget Icon.
		 * @since 1.0.1
		 * @access public
		 */
		public function get_icon(): string {
			return 'eicon-table';
		}


		/**
		 * Get widget categories.
		 *
		 * Retrieve the list of categories the WishList widget belongs to.
		 *
		 * Used to determine where to display the widget in the editor.
		 *
		 * @access public
		 *
		 * @return array Widget Categories.
		 */
		public function get_categories(): array {
			return array( 'WLFMC_WishList' );
		}

		/**
		 * Register WishList Widget Controls
		 *
		 * @return void.
		 * @access protected
		 *
		 * @version 1.4.0
		 * @since   1.0.1
		 */
		protected function register_controls() {

			$options = new MCT_Options( 'wlfmc_options' );

			// Table Settings Controls.
			$this->table_settings_controls( $options );

			// Table Styling Controls.
			$this->table_styling_controls( $options );

		}

		/**
		 * Table Settings Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void.
		 * @access private
		 * @version 1.6.8
		 * @since   1.0.1
		 */
		private function table_settings_controls( MCT_Options $options ) {

			$wishlist_under_table = $options->get_option(
				'wishlist_under_table',
				array(
					'actions',
					'add-all-to-cart',
				)
			);

			$this->start_controls_section(
				'section_content',
				array(
					'label' => esc_html__( 'Table Settings', 'wc-wlfmc-wishlist' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);

			$this->add_control(
				'per_page',
				array(
					'label'   => esc_html__( 'Products Per Page', 'wc-wlfmc-wishlist' ),
					'type'    => Controls_Manager::NUMBER,
					'default' => get_option( 'posts_per_page', 9 ),
				)
			);

			$this->add_control(
				'items_show',
				array(
					'label'    => esc_html__( 'Items show', 'wc-wlfmc-wishlist' ),
					'type'     => Controls_Manager::SELECT2,
					'multiple' => true,
					'options'  => array(
						'product-checkbox'     => esc_html__( 'Checkboxes', 'wc-wlfmc-wishlist' ),
						'product-name'         => esc_html__( 'Product name', 'wc-wlfmc-wishlist' ),
						'product-review'       => esc_html__( 'Product rating', 'wc-wlfmc-wishlist' ),
						'product-thumbnail'    => esc_html__( 'Product image', 'wc-wlfmc-wishlist' ),
						'product-variation'    => esc_html__( 'Product variations selected by the user (e.g. size or color)', 'wc-wlfmc-wishlist' ),
						'product-price'        => esc_html__( 'Product price', 'wc-wlfmc-wishlist' ),
						'product-quantity'     => esc_html__( 'Quantity', 'wc-wlfmc-wishlist' ),
						'product-stock-status' => esc_html__( 'Product stock', 'wc-wlfmc-wishlist' ),
						'product-date-added'   => esc_html__( 'Date added', 'wc-wlfmc-wishlist' ),
						'product-add-to-cart'  => esc_html__( 'Add to cart button', 'wc-wlfmc-wishlist' ),
						'product-remove'       => esc_html__( 'Button to remove the product', 'wc-wlfmc-wishlist' ),
					),
					'default'  => $options->get_option(
						'wishlist_items_show',
						array(
							'product-checkbox',
							'product-remove',
							'product-thumbnail',
							'product-name',
							'product-price',
							'product-add-to-cart',
						)
					),
				)
			);
			$this->add_control(
				'enable_drag_n_drop',
				array(
					'label'        => esc_html__( 'Enable Drag & Drop products', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => wlfmc_is_true( $options->get_option( 'wishlist_drag_n_drop', false ) ) ? 'true' : '',

				)
			);
			$this->add_control(
				'view_mode',
				array(
					'label'   => esc_html__( 'Select view mode', 'wc-wlfmc-wishlist' ),
					'type'    => Controls_Manager::SELECT,
					'options' => ( defined( 'MC_WLFMC_PREMIUM' ) ? array(
						'list'      => esc_html__( 'List', 'wc-wlfmc-wishlist' ),
						'grid'      => esc_html__( 'Grid', 'wc-wlfmc-wishlist' ),
						'both-grid' => esc_html__( 'Both( Default Grid )', 'wc-wlfmc-wishlist' ),
						'both-list' => esc_html__( 'Both( Default List )', 'wc-wlfmc-wishlist' ),
					) : array(
						'list' => esc_html__( 'List', 'wc-wlfmc-wishlist' ),
						'grid' => esc_html__( 'Grid', 'wc-wlfmc-wishlist' ),
					) ),
					'default' => $options->get_option( 'wishlist_view_mode', 'list' ),
				)
			);
			$this->add_control(
				'custom_template',
				array(
					'label'        => esc_html__( 'Wishlist default template', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => wlfmc_is_true( $options->get_option( 'wishlist_custom_template', true ) ) ? 'true' : '',

				)
			);

			$this->add_control(
				'enable_actions',
				array(
					'label'        => esc_html__( 'All together Actions button', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => in_array( 'actions', (array) $wishlist_under_table, true ) ? 'true' : '',

				)
			);

			$this->add_control(
				'enable_all_add_to_cart',
				array(
					'label'        => esc_html__( '"Add All to Cart" button', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => in_array( 'add-all-to-cart', (array) $wishlist_under_table, true ) ? 'true' : '',

				)
			);

			$this->add_control(
				'enable_share',
				array(
					'label'        => esc_html__( 'Share Wishlist', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => wlfmc_is_true( $options->get_option( 'enable_share', true ) ) ? 'true' : '',
				)
			);

			$this->add_control(
				'login_notice',
				array(
					'label'        => esc_html__( 'Login notice', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => wlfmc_is_true( $options->get_option( 'show_login_notice_for_guests', true ) ) ? 'true' : '',

				)
			);

			$this->add_control(
				'share_items',
				array(
					'label'    => esc_html__( 'Active share buttons', 'wc-wlfmc-wishlist' ),
					'type'     => Controls_Manager::SELECT2,
					'multiple' => true,
					'options'  => array(
						'facebook'  => esc_html__( 'Facebook', 'wc-wlfmc-wishlist' ),
						'messenger' => esc_html__( 'Facebook messenger', 'wc-wlfmc-wishlist' ),
						'twitter'   => esc_html__( 'Twitter', 'wc-wlfmc-wishlist' ),
						'whatsapp'  => esc_html__( 'Whatsapp', 'wc-wlfmc-wishlist' ),
						'telegram'  => esc_html__( 'Telegram', 'wc-wlfmc-wishlist' ),
						'email'     => esc_html__( 'Email', 'wc-wlfmc-wishlist' ),
						'copy'      => esc_html__( 'Share link', 'wc-wlfmc-wishlist' ),
						'pdf'       => esc_html__( 'Download pdf', 'wc-wlfmc-wishlist' ),
					),
					'default'  => $options->get_option(
						'share_items',
						array(
							'facebook',
							'messenger',
							'twitter',
							'whatsapp',
							'telegram',
							'email',
							'copy',
						)
					),
				)
			);

			$this->add_control(
				'socials_title',
				array(
					'label'      => esc_html__( 'Sharing title', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::TEXT,
					'input_type' => 'text',
					'default'    => $options->get_option( 'socials_title' ),
				)
			);

			$this->end_controls_section();

		}

		/**
		 * Table Styling Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void.
		 * @access private
		 * @version 1.4.0
		 * @since   1.0.1
		 */
		private function table_styling_controls( MCT_Options $options ) {

			$this->table_footer_button_style( $options );

			$this->table_footer_select_style( $options );

			$this->table_add_to_cart_style( $options );

			$this->table_qty_style( $options );

			$this->table_pagination_style( $options );

			$this->table_item_style( $options );

		}

		/**
		 * Table footer button styling Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void
		 * @since 1.4.0
		 */
		private function table_footer_button_style( MCT_Options $options ) {

			$this->start_controls_section(
				'button_style',
				array(
					'label'     => esc_html__( 'Footer Button', 'wc-wlfmc-wishlist' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array( 'custom_template!' => 'true' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'button_typography',
					'label'    => esc_html__( 'Typography', 'wc-wlfmc-wishlist' ),
					'global'   => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'selector' => '{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer .apply-btn,{{WRAPPER}} .wlfmc-default-table.add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer .button,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer button[type="submit"],{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer input[type="submit"],{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select, {{WRAPPER}} .wlfmc-default-table.qty-same-button input.qty , {{WRAPPER}} .wlfmc-default-table.qty-same-button .quantity .button',
				)
			);

			$this->start_controls_tabs( 'tabs_button_style' );

			$this->start_controls_tab(
				'tab_button_normal',
				array(
					'label' => esc_html__( 'Normal', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'button_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_color', '#515151' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-wishlist-table-header .button,{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer .apply-btn,{{WRAPPER}} .wlfmc-default-table.add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer .button,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer button[type="submit"],{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer input[type="submit"],{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select, {{WRAPPER}} .wlfmc-default-table.qty-same-button input.qty , {{WRAPPER}} .wlfmc-default-table.qty-same-button .quantity .button' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'button_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_border_color', 'transparent' ),
					'condition' => array(
						'border_border!' => '',
					),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-wishlist-table-header .button,{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer .apply-btn,{{WRAPPER}} .wlfmc-default-table.add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer .button,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer button[type="submit"],{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer input[type="submit"],{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select, {{WRAPPER}} .wlfmc-default-table.qty-same-button input.qty , {{WRAPPER}} .wlfmc-default-table.qty-same-button .quantity .button' => 'border-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'button_background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_background_color', '#ebebeb' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-wishlist-table-header .button,{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer .apply-btn,{{WRAPPER}} .wlfmc-default-table.add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer .button,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer button[type="submit"],{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer input[type="submit"]' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_button_hover',
				array(
					'label' => esc_html__( 'Hover', 'wc-wlfmc-wishlist' ),
				)
			);
			$this->add_control(
				'button_hover_color',
				array(
					'label'     => esc_html__( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_color', '#515151' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-wishlist-table-header .button:hover,{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer .apply-btn:hover,{{WRAPPER}} .wlfmc-default-table.add-to-card-same-button tr td.last-column .button:hover:not(.minus):not(.plus),{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer .button:hover,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer button[type="submit"]:hover,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer input[type="submit"]:hover, {{WRAPPER}} .wlfmc-default-table.qty-same-button input.qty:hover, {{WRAPPER}} .wlfmc-default-table.qty-same-button .quantity .button:hover,{{WRAPPER}} .wlfmc-wishlist-table-header .button:focus,{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer .apply-btn:focus,{{WRAPPER}} .wlfmc-default-table.add-to-card-same-button tr td.last-column .button:focus:not(.minus):not(.plus),{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer .button:focus,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer button[type="submit"]:focus,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer input[type="submit"]:focus,{{WRAPPER}} .wlfmc-default-table.qty-same-button input.qty:focus, {{WRAPPER}} .wlfmc-default-table.qty-same-button .quantity .button:focus' => 'color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'button_hover_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_border_color', 'transparent' ),
					'condition' => array(
						'border_border!' => '',
					),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-wishlist-table-header .button:hover,{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer .apply-btn:hover,{{WRAPPER}} .wlfmc-default-table.add-to-card-same-button tr td.last-column .button:hover:not(.minus):not(.plus),{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer .button:hover,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer button[type="submit"]:hover,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer input[type="submit"]:hover, {{WRAPPER}} .wlfmc-default-table.qty-same-button input.qty:hover , {{WRAPPER}} .wlfmc-default-table.qty-same-button .quantity .button:hover,{{WRAPPER}} .wlfmc-wishlist-table-header .button:focus,{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer .apply-btn:focus,{{WRAPPER}} .wlfmc-default-table.add-to-card-same-button tr td.last-column .button:focus:not(.minus):not(.plus),{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer .button:focus,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer button[type="submit"]:focus,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer input[type="submit"]:focus, {{WRAPPER}} .wlfmc-default-table.qty-same-button input.qty:focus , {{WRAPPER}} .wlfmc-default-table.qty-same-button .quantity .button:focus' => 'border-color: {{VALUE}} !important;',
					),
				)
			);

			$this->add_control(
				'button_background_hover',
				array(
					'label'     => esc_html__( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_background_hover_color', '#e67e22' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-wishlist-table-header .button:hover, {{WRAPPER}} .wlfmc-wishlist-table-header .button:focus,{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer .apply-btn:hover,{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer .apply-btn:focus,
						{{WRAPPER}} .wlfmc-default-table.add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus):hover,{{WRAPPER}} .wlfmc-default-table.add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus):focus,
						{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer .button:hover,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer .button:focus,
						{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer button[type="submit"]:hover,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer button[type="submit"]:focus,
						{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer input[type="submit"]:hover,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer input[type="submit"]:focus' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'           => 'border',
					'selector'       => '{{WRAPPER}} .wlfmc-wishlist-table-header .button,{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer .apply-btn,{{WRAPPER}} .wlfmc-default-table.add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer .button,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer button[type="submit"],{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer input[type="submit"],{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select, {{WRAPPER}} .wlfmc-default-table.qty-same-button input.qty , {{WRAPPER}} .wlfmc-default-table.qty-same-button .quantity .button',
					'exclude'        => array( 'color' ),
					'fields_options' => array(
						'width'  => array(
							'label'   => esc_html__( 'Border Width', 'wc-wlfmc-wishlist' ),
							'default' => array(
								'top'      => '1',
								'right'    => '1',
								'bottom'   => '1',
								'left'     => '1',
								'unit'     => 'px',
								'isLinked' => true,
							),
						),
						'border' => array(
							'default' => 'solid',
						),
					),
					'separator'      => 'before',
				)
			);

			$this->add_control(
				'border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer .apply-btn,{{WRAPPER}} .wlfmc-default-table.add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer .button,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer button[type="submit"],{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer input[type="submit"],{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select, {{WRAPPER}} .wlfmc-default-table.qty-same-button input.qty , {{WRAPPER}} .wlfmc-default-table.qty-same-button .quantity .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'default'    => array(
						'top'      => '6',
						'right'    => '6',
						'bottom'   => '6',
						'left'     => '6',
						'unit'     => 'px',
						'isLinked' => true,
					),
				)
			);

			$this->add_responsive_control(
				'button_height',
				array(
					'label'      => esc_html__( 'Button height', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em' ),
					'default'    => array( 'size' => intval( $options->get_option( 'wishlist_button_height', '36px' ) ) ),
					'separator'  => 'before',
					'range'      => array(
						'em' => array(
							'min'  => 0,
							'step' => 1,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-wishlist-table-header .button,{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer .apply-btn,{{WRAPPER}} .wlfmc-default-table.add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer .button,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer button[type="submit"],{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer input[type="submit"],{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select, {{WRAPPER}} .wlfmc-default-table.qty-same-button input.qty , {{WRAPPER}} .wlfmc-default-table.qty-same-button .quantity .button' => 'height: {{SIZE}}{{UNIT}}',
						'{{WRAPPER}} .wlfmc-wishlist-table-header .button' => 'width: {{SIZE}}{{UNIT}}',
					),
				)
			);

			$this->end_controls_section();

		}

		/**
		 * Table select field styling Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void
		 * @since 1.4.0
		 */
		private function table_footer_select_style( MCT_Options $options ) {

			$this->start_controls_section(
				'select_style',
				array(
					'label'     => esc_html__( 'Select', 'wc-wlfmc-wishlist' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array( 'custom_template!' => 'true' ),
				)
			);

			$this->add_control(
				'select_customize',
				array(
					'label'        => esc_html__( 'Custom styles for select field', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => '',

				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'select_typography',
					'label'     => esc_html__( 'Typography', 'wc-wlfmc-wishlist' ),
					'global'    => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'exclude'   => array(
						'line_height',

					),
					'condition' => array( 'select_customize' => 'true' ),
					'selector'  => '{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select',
				)
			);

			$this->start_controls_tabs(
				'tabs_select_style',
				array(
					'condition' => array( 'select_customize' => 'true' ),
				)
			);

			$this->start_controls_tab(
				'tab_select_normal',
				array(
					'label' => esc_html__( 'Normal', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'select_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_color', '#515151' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'select_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_border_color', 'transparent' ),
					'condition' => array(
						'border_border!' => '',
					),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select' => 'border-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'select_background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_background_color', '#ebebeb' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_select_focus',
				array(
					'label' => esc_html__( 'Focus', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'select_focus_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_border_hover_color', 'transparent' ),
					'condition' => array(
						'border_border!' => '',
					),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select:focus' => 'border-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'           => 'select_border',
					'selector'       => '{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select',
					'exclude'        => array( 'color' ),
					'fields_options' => array(
						'width'  => array(
							'label'   => esc_html__( 'Border Width', 'wc-wlfmc-wishlist' ),
							'default' => array(
								'top'      => '1',
								'right'    => '1',
								'bottom'   => '1',
								'left'     => '1',
								'unit'     => 'px',
								'isLinked' => true,
							),
						),
						'border' => array(
							'default' => 'solid',
						),
					),
					'separator'      => 'before',
					'condition'      => array( 'select_customize' => 'true' ),
				)
			);

			$this->add_control(
				'select_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'default'    => array(
						'top'      => '6',
						'right'    => '6',
						'bottom'   => '6',
						'left'     => '6',
						'unit'     => 'px',
						'isLinked' => true,
					),
					'condition'  => array( 'select_customize' => 'true' ),
				)
			);

			$this->add_responsive_control(
				'select_height',
				array(
					'label'      => esc_html__( 'Height', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em' ),
					'default'    => array( 'size' => intval( $options->get_option( 'wishlist_button_height', '36px' ) ) ),
					'separator'  => 'before',
					'range'      => array(
						'em' => array(
							'min'  => 0,
							'step' => 1,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer select' => 'height: {{SIZE}}{{UNIT}}',
					),
					'condition'  => array( 'select_customize' => 'true' ),
				)
			);

			$this->end_controls_section();

		}

		/**
		 * Table add to cart button styling Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void
		 * @since 1.4.0
		 */
		private function table_add_to_cart_style( MCT_Options $options ) {

			$this->start_controls_section(
				'section_add_to_cart',
				array(
					'label'     => esc_html__( 'َAdd to cart Button', 'wc-wlfmc-wishlist' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array( 'custom_template!' => 'true' ),
				)
			);

			$this->add_control(
				'button_add_to_cart_style',
				array(
					'label'        => esc_html__( 'Apply button style for add to cart button', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => wlfmc_is_true( $options->get_option( 'wishlist_button_add_to_cart_style', true ) ) ? 'true' : '',

				)
			);

			$this->add_control(
				'add_to_cart_customize',
				array(
					'label'        => esc_html__( 'Custom styles for add to cart button', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => '',
					'condition'    => array( 'button_add_to_cart_style!' => 'true' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'add_to_cart_button_typography',
					'label'     => esc_html__( 'Typography', 'wc-wlfmc-wishlist' ),
					'global'    => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'exclude'   => array(
						// 'line_height',

					),
					'condition' => array(
						'add_to_cart_customize'     => 'true',
						'button_add_to_cart_style!' => 'true',
					),
					'selector'  => '{{WRAPPER}} .wlfmc-default-table tr td.last-column .button:not(.minus):not(.plus)',
				)
			);

			$this->start_controls_tabs(
				'tabs_add_to_cart_button_style',
				array(
					'condition' => array(
						'add_to_cart_customize'     => 'true',
						'button_add_to_cart_style!' => 'true',
					),
				)
			);

			$this->start_controls_tab(
				'tab_add_to_cart_button_normal',
				array(
					'label' => esc_html__( 'Normal', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'add_to_cart_button_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_color', '#515151' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table tr td.last-column .button:not(.minus):not(.plus)' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'add_to_cart_button_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_border_color', 'transparent' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table tr td.last-column .button:not(.minus):not(.plus)' => 'border-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'add_to_cart_button_background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_background_color', '#ebebeb' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table tr td.last-column .button:not(.minus):not(.plus)' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_add_to_cart_button_hover',
				array(
					'label' => esc_html__( 'Hover', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'add_to_cart_button_hover_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_hover_color', '#fff' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table tr td.last-column .button:not(.minus):not(.plus):focus,{{WRAPPER}} .wlfmc-default-table tr td.last-column .button:not(.minus):not(.plus):hover' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'add_to_cart_button_hover_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_border_hover_color', 'transparent' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table tr td.last-column .button:not(.minus):not(.plus):focus,{{WRAPPER}} .wlfmc-default-table tr td.last-column .button:not(.minus):not(.plus):hover' => 'border-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'add_to_cart_button_hover_background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_background_hover_color', '#e67e22' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table tr td.last-column .button:not(.minus):not(.plus):focus,{{WRAPPER}} .wlfmc-default-table tr td.last-column .button:not(.minus):not(.plus):hover' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'           => 'add_to_cart_button_border',
					'selector'       => '{{WRAPPER}} .wlfmc-default-table tr td.last-column .button:not(.minus):not(.plus)',
					'exclude'        => array( 'color' ),
					'fields_options' => array(
						'width'  => array(
							'label'   => esc_html__( 'Border Width', 'wc-wlfmc-wishlist' ),
							'default' => array(
								'top'      => '1',
								'right'    => '1',
								'bottom'   => '1',
								'left'     => '1',
								'unit'     => 'px',
								'isLinked' => true,
							),
						),
						'border' => array(
							'default' => 'solid',
						),
					),
					'separator'      => 'before',
					'condition'      => array(
						'add_to_cart_customize'     => 'true',
						'button_add_to_cart_style!' => 'true',

					),
				)
			);

			$this->add_control(
				'add_to_cart_button_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-default-table tr td.last-column .button:not(.minus):not(.plus)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'default'    => array(
						'top'      => '6',
						'right'    => '6',
						'bottom'   => '6',
						'left'     => '6',
						'unit'     => 'px',
						'isLinked' => true,
					),
					'condition'  => array(
						'add_to_cart_customize'     => 'true',
						'button_add_to_cart_style!' => 'true',
					),
				)
			);

			$this->end_controls_section();

		}

		/**
		 * Table quantity field styling Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void
		 * @since 1.4.0
		 */
		private function table_qty_style( MCT_Options $options ) {

			$this->start_controls_section(
				'section_qty',
				array(
					'label'     => esc_html__( 'Qty input', 'wc-wlfmc-wishlist' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array( 'custom_template!' => 'true' ),
				)
			);

			$this->add_control(
				'qty_style',
				array(
					'label'        => esc_html__( 'Apply styling to quantity field', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => wlfmc_is_true( $options->get_option( 'wishlist_qty_style', true ) ) ? 'true' : '',

				)
			);

			$this->add_control(
				'qty_customize',
				array(
					'label'        => esc_html__( 'Custom styles for qty field', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'condition'    => array( 'qty_style!' => 'true' ),
					'default'      => '',

				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'qty_typography',
					'label'     => esc_html__( 'Typography', 'wc-wlfmc-wishlist' ),
					'global'    => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'exclude'   => array(
						'text_transform',
						'font_style',
						'text_decoration',
						'letter_spacing',
						'word_spacing',
					),
					'condition' => array(
						'qty_customize' => 'true',
						'qty_style!'    => 'true',
					),
					'selector'  => '{{WRAPPER}} .wlfmc-default-table input.qty',
				)
			);

			$this->start_controls_tabs(
				'tabs_qty_style',
				array(
					'condition' => array(
						'qty_customize' => 'true',
						'qty_style!'    => 'true',
					),
				)
			);

			$this->start_controls_tab(
				'tab_qty_normal',
				array(
					'label' => esc_html__( 'Normal', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'qty_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_color', '#515151' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table input.qty' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'qty_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_border_color', 'transparent' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table input.qty' => 'border-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'qty_background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_background_color', '#ebebeb' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table input.qty' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_qty_hover',
				array(
					'label' => esc_html__( 'Hover', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'qty_hover_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_hover_color', '#fff' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table input.qty:focus,{{WRAPPER}} .wlfmc-default-table input.qty:hover' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'qty_hover_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_border_hover_color', 'transparent' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table input.qty:focus,{{WRAPPER}} .wlfmc-default-table input.qty:hover' => 'border-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'qty_hover_background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_background_hover_color', '#e67e22' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table input.qty:focus,{{WRAPPER}} .wlfmc-default-table input.qty:hover' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'           => 'qty_border',
					'selector'       => '{{WRAPPER}} .wlfmc-default-table input.qty',
					'exclude'        => array( 'color' ),
					'fields_options' => array(
						'width'  => array(
							'label'   => esc_html__( 'Border Width', 'wc-wlfmc-wishlist' ),
							'default' => array(
								'top'      => '1',
								'right'    => '1',
								'bottom'   => '1',
								'left'     => '1',
								'unit'     => 'px',
								'isLinked' => true,
							),
						),
						'border' => array(
							'default' => 'solid',
						),
					),
					'separator'      => 'before',
					'condition'      => array(
						'qty_customize' => 'true',
						'qty_style!'    => 'true',

					),
				)
			);

			$this->add_control(
				'qty_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-default-table input.qty' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'default'    => array(
						'top'      => '6',
						'right'    => '6',
						'bottom'   => '6',
						'left'     => '6',
						'unit'     => 'px',
						'isLinked' => true,
					),
					'condition'  => array(
						'qty_customize' => 'true',
						'qty_style!'    => 'true',
					),
				)
			);

			$this->end_controls_section();

		}

		/**
		 * Table pagination styling Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void
		 * @since 1.4.0
		 */
		private function table_pagination_style( MCT_Options $options ) {

			$this->start_controls_section(
				'section_pagination',
				array(
					'label'     => esc_html__( 'Pagination', 'wc-wlfmc-wishlist' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array( 'custom_template!' => 'true' ),
				)
			);

			$this->add_control(
				'pagination_style',
				array(
					'label'        => esc_html__( 'Apply wishlist page style for pagination bar', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => wlfmc_is_true( $options->get_option( 'wishlist_pagination_style', true ) ) ? 'true' : '',

				)
			);

			$this->add_control(
				'pagination_customize',
				array(
					'label'        => esc_html__( 'Custom styles for pagination', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'condition'    => array( 'pagination_style' => 'true' ),
					'default'      => '',

				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'pagination_typography',
					'label'     => esc_html__( 'Typography', 'wc-wlfmc-wishlist' ),
					'global'    => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'exclude'   => array(
						'text_transform',
						'font_style',
						'font_weight',
						'text_decoration',
						'letter_spacing',
						'word_spacing',
						'line_height',
					),
					'condition' => array(
						'pagination_customize' => 'true',
						'pagination_style'     => 'true',
					),
					'selector'  => '{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer nav.wishlist-pagination ul',
				)
			);

			$this->start_controls_tabs(
				'tabs_pagination_style',
				array(
					'condition' => array(
						'pagination_customize' => 'true',
						'pagination_style'     => 'true',
					),
				)
			);

			$this->start_controls_tab(
				'tab_pagination_normal',
				array(
					'label' => esc_html__( 'Normal', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'pagination_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_color', '#515151' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li a' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'pagination_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_border_color', 'transparent' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li a' => 'border-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'pagination_background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_background_color', '#ebebeb' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li a' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_pagination_hover',
				array(
					'label' => esc_html__( 'Hover', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'pagination_hover_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_hover_color', '#fff' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li a:focus,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li a:hover' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'pagination_hover_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_border_hover_color', 'transparent' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li a:focus,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li a:hover' => 'border-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'pagination_hover_background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_background_hover_color', '#e67e22' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li a:focus,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li a:hover' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_pagination_active',
				array(
					'label' => esc_html__( 'Active', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'pagination_active_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_color', '#515151' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li span' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'pagination_active_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_border_color', 'transparent' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li span' => 'border-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'pagination_active_background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'wishlist_button_background_color', '#ebebeb' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li span' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'           => 'pagination_border',
					'selector'       => '{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li span,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li a',
					'exclude'        => array( 'color' ),
					'fields_options' => array(
						'width'  => array(
							'label'   => esc_html__( 'Border Width', 'wc-wlfmc-wishlist' ),
							'default' => array(
								'top'      => '1',
								'right'    => '1',
								'bottom'   => '1',
								'left'     => '1',
								'unit'     => 'px',
								'isLinked' => true,
							),
						),
						'border' => array(
							'default' => 'solid',
						),
					),
					'separator'      => 'before',
					'condition'      => array(
						'pagination_customize' => 'true',
						'pagination_style'     => 'true',

					),
				)
			);

			$this->add_control(
				'pagination_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li span,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'default'    => array(
						'top'      => '6',
						'right'    => '6',
						'bottom'   => '6',
						'left'     => '6',
						'unit'     => 'px',
						'isLinked' => true,
					),
					'condition'  => array(
						'pagination_customize' => 'true',
						'pagination_style'     => 'true',
					),
				)
			);

			$this->add_control(
				'pagination_padding',
				array(
					'label'      => esc_html__( 'Padding', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li span,{{WRAPPER}} .wlfmc-default-table  .wlfmc-wishlist-footer nav.wishlist-pagination ul li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'default'    => array(
						'top'      => '10',
						'right'    => '10',
						'bottom'   => '10',
						'left'     => '10',
						'unit'     => 'px',
						'isLinked' => true,
					),
					'condition'  => array(
						'pagination_customize' => 'true',
						'pagination_style'     => 'true',
					),
				)
			);

			$this->end_controls_section();

		}

		/**
		 * Table items styling Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void
		 * @since 1.4.0
		 * @version 1.6.8
		 */
		private function table_item_style( MCT_Options $options ) {

			$this->start_controls_section(
				'section_items',
				array(
					'label'     => esc_html__( 'Items', 'wc-wlfmc-wishlist' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array( 'custom_template!' => 'true' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'product_title_typography',
					'label'    => esc_html__( 'Product title typography', 'wc-wlfmc-wishlist' ),
					'global'   => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'exclude'  => array( 'text_decoration' ),
					'selector' => '{{WRAPPER}} .wlfmc-default-table a.product-name',
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'date_added_typography',
					'label'    => esc_html__( 'Date added typography', 'wc-wlfmc-wishlist' ),
					'global'   => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'selector' => '{{WRAPPER}} .wlfmc-default-table .product-date-added',
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'out_of_stock_typography',
					'label'    => esc_html__( 'Out-of-stock typography', 'wc-wlfmc-wishlist' ),
					'global'   => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'selector' => '{{WRAPPER}} .wlfmc-default-table .wishlist-out-of-stock',
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'variation_title_typography',
					'label'    => esc_html__( 'Variation title typography', 'wc-wlfmc-wishlist' ),
					'global'   => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'selector' => '{{WRAPPER}} .wlfmc-default-table .product-variation .variation dt',
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'variation_value_typography',
					'label'    => esc_html__( 'Variation value typography', 'wc-wlfmc-wishlist' ),
					'global'   => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'selector' => '{{WRAPPER}} .wlfmc-default-table .product-variation .variation dd',
				)
			);

			$this->start_controls_tabs( 'tabs_table_style' );

			$this->start_controls_tab(
				'tab_table_normal',
				array(
					'label' => esc_html__( 'Normal', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'product_title_color',
				array(
					'label'     => esc_html__( 'Product title Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table a.product-name' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'date_added_color',
				array(
					'label'     => esc_html__( 'Date added Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table .product-date-added' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'out_of_stock_color',
				array(
					'label'     => esc_html__( 'Out-of-stock  Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table .wishlist-out-of-stock' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'variation_title_color',
				array(
					'label'     => esc_html__( 'Variation title Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table .product-variation .variation dt' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'variation_value_color',
				array(
					'label'     => esc_html__( 'Variation value Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table .product-variation .variation dd' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'thumbnail_background_color',
				array(
					'label'       => esc_html__( 'Thumbnail background Color', 'wc-wlfmc-wishlist' ),
					'description' => esc_html__( 'Your image must be SVG or png for this section to be effective.', 'wc-wlfmc-wishlist' ),
					'type'        => Controls_Manager::COLOR,
					'selectors'   => array(
						'{{WRAPPER}} .wlfmc-default-table .product-thumbnail img' => 'background-color: {{VALUE}};',
					),
					'default'     => $options->get_option( 'wishlist_table_thumbnail_background', '#f5f5f5' ),
				)
			);

			$this->add_control(
				'item_background_color',
				array(
					'label'     => esc_html__( 'Item background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table .wishlist-items-wrapper:not(.wishlist-empty) tr:not(.ui-sortable-placeholder),
						{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer tr.actions' => 'background-color: {{VALUE}} !important;',
					),
					'default'   => $options->get_option( 'wishlist_table_item_background', 'transparent' ),
				)
			);

			$this->add_control(
				'item_border_color',
				array(
					'label'     => esc_html__( 'Item border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table .wishlist-items-wrapper:not(.wishlist-empty) tr,
						{{WRAPPER}} .wlfmc-default-table .wlfmc-wishlist-footer tr.actions' => 'border-color: {{VALUE}};',
					),
					'default'   => $options->get_option( 'wishlist_table_grid_border_color', '#ebebeb' ),
				)
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_table_hover',
				array(
					'label' => esc_html__( 'Hover', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'product_title_hover_color',
				array(
					'label'     => esc_html__( 'Product title Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '',
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table a.product-name:hover' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'thumbnail_background_hover_color',
				array(
					'label'       => esc_html__( 'Thumbnail background Color', 'wc-wlfmc-wishlist' ),
					'description' => esc_html__( 'Your image must be SVG or png for this section to be effective.', 'wc-wlfmc-wishlist' ),
					'type'        => Controls_Manager::COLOR,
					'selectors'   => array(
						'{{WRAPPER}} .wlfmc-default-table .product-thumbnail img:hover' => 'background-color: {{VALUE}};',
					),
					'default'     => $options->get_option( 'wishlist_table_thumbnail_background', '#f5f5f5' ),
				)
			);

			$this->add_control(
				'item_background_hover_color',
				array(
					'label'     => esc_html__( 'Item background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table .wishlist-items-wrapper:not(.wishlist-empty) tr:not(.ui-sortable-placeholder):hover,
						{{WRAPPER}} .wlfmc-wishlist-footer tr.actions:hover' => 'background-color: {{VALUE}} !important;',
					),
					'default'   => $options->get_option( 'wishlist_table_item_hover_background', 'transparent' ),
				)
			);

			$this->add_control(
				'item_border_hover_color',
				array(
					'label'     => esc_html__( 'Border color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table .wishlist-items-wrapper:not(.wishlist-empty) > tr:hover,
						{{WRAPPER}} .wlfmc-wishlist-footer tr.actions:hover' => 'border-color: {{VALUE}};',
					),
					'default'   => $options->get_option( 'wishlist_table_grid_border_color', '#ebebeb' ),
				)
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'           => 'item_border',
					'selector'       => '{{WRAPPER}} .wlfmc-default-table .wishlist-items-wrapper tr,{{WRAPPER}} .wlfmc-wishlist-footer tr.actions',
					'separator'      => 'before',
					'exclude'        => array( 'color' ),
					'fields_options' => array(
						'border' => array(
							'default' => 'solid',
						),
						'width'  => array(
							'label'   => esc_html__( 'Border Width', 'wc-wlfmc-wishlist' ),
							'default' => array(
								'top'      => '1',
								'right'    => '1',
								'bottom'   => '1',
								'left'     => '1',
								'isLinked' => true,
							),
						),
					),
				)
			);

			$this->add_control(
				'item_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-default-table .wishlist-items-wrapper tr' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'default'    => array(
						'top'      => '10',
						'right'    => '10',
						'bottom'   => '10',
						'left'     => '10',
						'unit'     => 'px',
						'isLinked' => true,
					),
				)
			);

			$this->add_control(
				'table_separator_color',
				array(
					'label'     => esc_html__( 'Separator color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'separator' => 'before',
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-default-table td.with-border-top' => 'border-top-color: {{VALUE}} !important;',
					),
					'default'   => $options->get_option( 'wishlist_table_separator_color', 'transparent' ),
				)
			);

			$this->end_controls_section();

		}

		/**
		 * Render WishList widget output on the frontend.
		 *
		 * Written in PHP and used to generate the final HTML.
		 *
		 * @since 1.0.1
		 * @version 1.6.8
		 * @access protected
		 */
		protected function render() {

			$attribute_string = '';
			$settings         = $this->get_settings_for_display();

			foreach ( $settings as $key => $value ) {

				if ( in_array(
					$key,
					array(
						'socials_title',
						'share_items',
						'login_notice',
						'enable_share',
						'enable_all_add_to_cart',
						'enable_actions',
						'custom_template',
						'pagination_style',
						'qty_style',
						'button_add_to_cart_style',
						'view_mode',
						'items_show',
						'per_page',
						'enable_drag_n_drop',
					),
					true
				) ) {
					if ( is_array( $value ) ) {
						$value = implode( ',', $value );
					}

					$attribute_string .= " $key=\"$value\"";
				}
			}

			echo do_shortcode( '[wlfmc_wishlist is_elementor="true" unique_id="id-' . $this->get_id() . "\" $attribute_string]" );
		}
	}
}

