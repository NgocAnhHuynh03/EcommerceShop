<?php
/**
 * Wishlist Counter Widget.
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
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use Elementor\Icons_Manager;
use ElementorPro\Plugin;

if ( ! class_exists( 'WLFMC_Elementor_Wishlist_Counter' ) ) {
	/**
	 * Wishlist Counter Elementor Widget
	 */
	class WLFMC_Elementor_Wishlist_Counter extends Widget_Base {

		/**
		 * Get widget name.
		 *
		 * Retrieve WishList widget name.
		 *
		 * @return string Widget Name.
		 * @access public
		 */
		public function get_name(): string {
			return 'wlfmc-wishlist-counter';
		}

		/**
		 * Get widget title.
		 *
		 * Retrieve WishList widget title.
		 *
		 * @return string Widget Title.
		 * @access public
		 */
		public function get_title(): string {
			return esc_html__( 'Mini Wishlist & Counter', 'wc-wlfmc-wishlist' );
		}

		/**
		 * Get widget icon.
		 *
		 * Retrieve WishList widget icon.
		 *
		 * @return string Widget Icon.
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
		 */
		protected function register_controls() {

			$options = new MCT_Options( 'wlfmc_options' );
			$this->general_setting_control( $options );
			$this->counter_settings_controls( $options );
			$this->mini_wishlist_settings_controls( $options );
			$this->style_controls( $options );

		}

		/**
		 * General Settings Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void
		 * @since   1.5.0
		 */
		private function general_setting_control( MCT_Options $options ) {
			$this->start_controls_section(
				'section_general_content',
				array(
					'label' => __( 'General Settings', 'wc-wlfmc-wishlist' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);
			$this->add_control(
				'display_mode',
				array(
					'label'   => esc_html__( 'Display Mode', 'wc-wlfmc-wishlist' ),
					'type'    => Controls_Manager::SELECT,
					'options' => array(
						'mini-wishlist' => __( 'Mini Wishlist', 'wc-wlfmc-wishlist' ),
						'counter-only'  => __( 'Counter Only', 'wc-wlfmc-wishlist' ),
						'on-hover'      => __( 'Counter & Mini Wishlist (show on-hover)', 'wc-wlfmc-wishlist' ),
						'on-click'      => __( 'Counter & Mini Wishlist (show on-click)', 'wc-wlfmc-wishlist' ),
					),
					'default' => $options->get_option( 'display_mini_wishlist_for_counter', 'counter-only' ),
				)
			);
			$this->end_controls_section();
		}

		/**
		 * Counter Settings Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void
		 *
		 * @version 1.6.8
		 * @since   1.4.0
		 */
		private function counter_settings_controls( MCT_Options $options ) {

			$this->start_controls_section(
				'section_counter_content',
				array(
					'label'     => __( 'Counter Settings', 'wc-wlfmc-wishlist' ),
					'tab'       => Controls_Manager::TAB_CONTENT,
					'condition' => array(
						'display_mode!' => 'mini-wishlist',
					),
				)
			);

			$this->add_control(
				'show_icon',
				array(
					'label'        => esc_html__( 'Show icon', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',

				)
			);

			$this->add_control(
				'icon_name',
				array(
					'label'     => esc_html__( 'Counter icon', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => wlfmc_get_icon_names( 'wishlist' ),
					'condition' => array(
						'show_icon' => 'true',
					),
					'default'   => $options->get_option( 'counter_icon', 'heart' ),
				)
			);

			$this->add_control(
				'empty_icon',
				array(
					'label'       => esc_html__( 'Icon', 'wc-wlfmc-wishlist' ),
					'type'        => Controls_Manager::ICONS,
					'skin'        => 'inline',
					'label_block' => false,
					'condition'   => array(
						'icon_name' => 'custom',
						'show_icon' => 'true',
					),
				)
			);

			$this->add_control(
				'has_item_icon',
				array(
					'label'       => esc_html__( 'Icon Added', 'wc-wlfmc-wishlist' ),
					'type'        => Controls_Manager::ICONS,
					'skin'        => 'inline',
					'label_block' => false,
					'condition'   => array(
						'icon_name' => 'custom',
						'show_icon' => 'true',
					),
				)
			);

			$this->add_control(
				'show_text',
				array(
					'label'        => esc_html__( 'Show counter text', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => wlfmc_is_true( $options->get_option( 'enable_counter_text', true ) ) ? 'true' : '',
				)
			);
			$this->add_control(
				'add_link_title',
				array(
					'label'        => esc_html__( 'Add Link For "Wishlist" Counter Title', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => wlfmc_is_true( $options->get_option( 'enable_counter_add_link_title', true ) ) ? 'true' : '',
					'conditions'   => array(
						'relation' => 'and',
						'terms'    => array(
							array(
								'name'     => 'display_mode',
								'operator' => '===',
								'value'    => 'counter-only',
							),
							array(
								'relation' => 'or',
								'terms'    => array(
									array(
										'name'     => 'show_text',
										'operator' => '===',
										'value'    => 'true',
									),
									array(
										'name'     => 'show_icon',
										'operator' => '===',
										'value'    => 'true',
									),
								),
							),
						),
					),
				)
			);

			$this->add_control(
				'counter_text',
				array(
					'label'      => __( 'Counter text', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::TEXT,
					'input_type' => 'text',
					'default'    => $options->get_option( 'counter_text' ),
					'condition'  => array(
						'show_text' => 'true',
					),
				)
			);

			$this->add_control(
				'show_counter',
				array(
					'label'        => esc_html__( 'Show counter', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => wlfmc_is_true( $options->get_option( 'enable_counter_products_number', true ) ) ? 'true' : '',

				)
			);

			$this->add_control(
				'hide_zero_products_number',
				array(
					'label'        => esc_html__( 'Hide zero number', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => wlfmc_is_true( $options->get_option( 'hide_counter_zero_products_number', true ) ) ? 'true' : '',
					'condition'    => array(
						'show_counter' => 'true',
					),
				)
			);
			if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
				$this->add_control(
					'hide_counter_if_no_items',
					array(
						'label'        => esc_html__( 'Hide Counter if No Products Found', 'wc-wlfmc-wishlist' ),
						'type'         => Controls_Manager::SWITCHER,
						'return_value' => 'true',
						'default'      => wlfmc_is_true( $options->get_option( 'hide_counter_if_no_items', false ) ) ? 'true' : '',

					)
				);
			}

			$this->add_control(
				'products_number_position',
				array(
					'label'     => esc_html__( 'Products number position', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'right'     => __( 'Right side of the text', 'wc-wlfmc-wishlist' ),
						'left'      => __( 'Left side of the text', 'wc-wlfmc-wishlist' ),
						'top-right' => __( 'On icon - top right', 'wc-wlfmc-wishlist' ),
						'top-left'  => __( 'On icon - top left', 'wc-wlfmc-wishlist' ),
					),
					'default'   => $options->get_option( 'counter_products_number_position', 'right' ),
					'condition' => array(
						'show_counter' => 'true',
					),
				)
			);

			$this->end_controls_section();

		}

		/**
		 * Mini wishlist Settings Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void
		 * @since   1.4.0
		 */
		private function mini_wishlist_settings_controls( MCT_Options $options ) {

			$this->start_controls_section(
				'section_mini_wishlist_content',
				array(
					'label'     => __( 'Mini wishlist Settings', 'wc-wlfmc-wishlist' ),
					'tab'       => Controls_Manager::TAB_CONTENT,
					'condition' => array(
						'display_mode!' => 'counter-only',
					),
				)
			);

			$this->add_control(
				'position_mode',
				array(
					'label'     => esc_html__( 'Mini wishlist position mode', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'absolute' => __( 'Absolute position', 'wc-wlfmc-wishlist' ),
						'fixed'    => __( 'Fixed position', 'wc-wlfmc-wishlist' ),
					),
					'default'   => $options->get_option( 'mini_wishlist_position_mode', 'fixed' ),
					'condition' => array(
						'display_mode!' => 'mini-wishlist',
					),
				)
			);

			$this->add_control(
				'show_button',
				array(
					'label'        => esc_html__( 'Show Button', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
				)
			);

			$this->add_control(
				'wishlist_link_position',
				array(
					'label'     => esc_html__( 'Wishlist button position', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'after'  => __( 'After products', 'wc-wlfmc-wishlist' ),
						'before' => __( 'Before products', 'wc-wlfmc-wishlist' ),
					),
					'condition' => array(
						'show_button' => 'true',
					),
					'default'   => $options->get_option( 'counter_mini_wishlist_link_position', 'after' ),
				)
			);

			$this->add_control(
				'button_text',
				array(
					'label'      => __( 'Button text', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::TEXT,
					'input_type' => 'text',
					'default'    => $options->get_option( 'counter_button_text', __( 'View My Wishlist', 'wc-wlfmc-wishlist' ) ),
					'condition'  => array(
						'show_button' => 'true',
					),
				)
			);

			$this->add_control(
				'show_totals',
				array(
					'label'        => esc_html__( 'Show totals', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
				)
			);
			$this->add_control(
				'total_text',
				array(
					'label'      => __( '"Total product" text', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::TEXT,
					'input_type' => 'text',
					'default'    => $options->get_option( 'counter_total_text', __( 'Total products', 'wc-wlfmc-wishlist' ) ),
					'condition'  => array(
						'show_totals' => 'true',
					),
				)
			);

			$this->add_control(
				'per_page',
				array(
					'label'   => esc_html__( 'Products Per Page', 'wc-wlfmc-wishlist' ),
					'type'    => Controls_Manager::NUMBER,
					'default' => $options->get_option( 'counter_per_page_products_count', 4 ),
				)
			);

			$this->end_controls_section();

		}
		/**
		 * Counter Styling Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void.
		 * @access private
		 *
		 * @since   1.4.0
		 */
		private function style_controls( MCT_Options $options ) {
			$this->text_styles( $options );
			$this->dropdown_styles( $options );
			$this->button_styles( $options );

		}

		/**
		 * Counter Button Styling Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void.
		 * @access private
		 *
		 * @since   1.4.0
		 */
		private function button_styles( MCT_Options $options ) {

			$counter_button_colors = $options->get_option(
				'counter_button_colors',
				array(
					'color'            => '#515151',
					'color-hover'      => '#fff',
					'background'       => '#ebebeb',
					'background-hover' => '#e67e22',
					'border'           => 'rgb(0,0,0,0)',
					'border-hover'     => 'rgb(0,0,0,0)',
				)
			);

			$this->start_controls_section(
				'button_style',
				array(
					'label'     => __( 'Mini Wishlist Button', 'wc-wlfmc-wishlist' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array(
						'show_button' => 'true',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'button_typography',
					'label'    => esc_html__( 'Button Typography', 'wc-wlfmc-wishlist' ),
					'global'   => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'exclude'  => array(
						'line_height',
						'text_decoration',
					),
					'selector' => '{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-view-wishlist-link',
				)
			);

			$this->start_controls_tabs( 'tabs_button_style' );

			$this->start_controls_tab(
				'tab_button_normal',
				array(
					'label' => __( 'Normal', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'button_text_color',
				array(
					'label'     => __( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $counter_button_colors['color'],
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-view-wishlist-link' => 'color: {{VALUE}}!important;',
					),
				)
			);

			$this->add_control(
				'button_border_color',
				array(
					'label'     => __( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $counter_button_colors['border'],
					'condition' => array(
						'border_border!' => '',
					),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-view-wishlist-link' => 'border-color: {{VALUE}}!important;',
					),
				)
			);

			$this->add_control(
				'button_background_color',
				array(
					'label'     => __( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $counter_button_colors['background'],
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-view-wishlist-link' => 'background-color: {{VALUE}}!important;',
					),
				)
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_button_hover',
				array(
					'label' => __( 'Hover', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'hover_color',
				array(
					'label'     => __( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-view-wishlist-link:hover , {{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-view-wishlist-link:focus' => 'color: {{VALUE}}!important;',
					),
					'default'   => $counter_button_colors['color-hover'],

				)
			);

			$this->add_control(
				'button_hover_border_color',
				array(
					'label'     => __( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $counter_button_colors['border-hover'],
					'condition' => array(
						'border_border!' => '',
					),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-view-wishlist-link:hover, {{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-view-wishlist-link:focus' => 'border-color: {{VALUE}}!important;',
					),
				)
			);

			$this->add_control(
				'button_background_hover',
				array(
					'label'     => __( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $counter_button_colors['background-hover'],
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-view-wishlist-link:hover,{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-view-wishlist-link:focus' => 'background-color: {{VALUE}}!important;',
					),
				)
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'           => 'border',
					'selector'       => '{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-view-wishlist-link',
					'exclude'        => array( 'color' ),
					'fields_options' => array(
						'width'  => array(
							'label'     => __( 'Border Width', 'wc-wlfmc-wishlist' ),
							'default'   => array(
								'top'      => '1',
								'right'    => '1',
								'bottom'   => '1',
								'left'     => '1',
								'unit'     => 'px',
								'isLinked' => true,
							),
							'selectors' => array(
								'{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
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
					'label'      => __( 'Border Radius', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-view-wishlist-link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}!important;',
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
					'default'    => array( 'size' => intval( $options->get_option( 'counter_button_height', '38px' ) ) ),
					'separator'  => 'before',
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-view-wishlist-link' => 'height: {{SIZE}}{{UNIT}}!important;',
					),
				)
			);

			$this->end_controls_section();

		}

		/**
		 * Counter Dropdown styling Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void.
		 * @access private
		 *
		 * @since   1.4.0
		 */
		private function dropdown_styles( MCT_Options $options ) {

			$this->start_controls_section(
				'dropdown_style',
				array(
					'label'     => __( 'Mini Wishlist Product list', 'wc-wlfmc-wishlist' ),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => array(
						'display_mode!' => 'counter-only',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'product_title_typography',
					'label'    => esc_html__( 'Product title Typography', 'wc-wlfmc-wishlist' ),
					'global'   => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'exclude'  => array(
						'line_height',
						'text_decoration',
					),
					'selector' => '{{WRAPPER}} .wlfmc-products-counter-wrapper .product-name',
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'total_products_typography',
					'label'     => esc_html__( 'Total products Typography', 'wc-wlfmc-wishlist' ),
					'global'    => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'condition' => array(
						'show_totals' => 'true',
					),
					'selector'  => '{{WRAPPER}} .wlfmc-products-counter-wrapper .total-products span',
				)
			);

			$this->add_control(
				'dropdown_product_title_color',
				array(
					'label'     => __( 'Product title Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#333',
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper  .product-name' => 'color: {{VALUE}}!important;',
					),
				)
			);

			$this->add_control(
				'dropdown_background_color',
				array(
					'label'     => __( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'counter_background_color', '#fff' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-products-counter-wishlist' => 'background-color: {{VALUE}}!important;',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => 'dropdown_box_shadow',
					'selector' => '{{WRAPPER}} .wlfmc-products-counter-wishlist',
				)
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'           => 'dropdown_border',
					'selector'       => '{{WRAPPER}} .wlfmc-products-counter-wishlist',
					'fields_options' => array(
						'width'  => array(
							'label'   => __( 'Border Width', 'wc-wlfmc-wishlist' ),
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
						'color'  => array(
							'label'   => __( 'Border Color', 'wc-wlfmc-wishlist' ),
							'default' => $options->get_option( 'counter_border_color', '#f5f5f5' ),
						),
					),
					'separator'      => 'before',
				)
			);

			$this->add_control(
				'dropdown_border_radius',
				array(
					'label'      => __( 'Border Radius', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-products-counter-wishlist' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}!important;',
					),
					'default'    => array(
						'top'      => '5',
						'right'    => '5',
						'bottom'   => '5',
						'left'     => '5',
						'unit'     => 'px',
						'isLinked' => true,
					),
				)
			);

			$this->end_controls_section();

		}

		/**
		 * Counter Text Styling Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void.
		 * @access private
		 *
		 * @since   1.4.0
		 */
		private function text_styles( MCT_Options $options ) {

			$this->start_controls_section(
				'text_style',
				array(
					'label'      => __( 'Counter Text & Icon', 'wc-wlfmc-wishlist' ),
					'tab'        => Controls_Manager::TAB_STYLE,
					'conditions' => array(
						'relation' => 'or',
						'terms'    => array(
							array(
								'name'     => 'show_text',
								'operator' => '===',
								'value'    => 'true',
							),
							array(
								'name'     => 'show_icon',
								'operator' => '===',
								'value'    => 'true',
							),
							array(
								'name'     => 'show_counter',
								'operator' => '===',
								'value'    => 'true',
							),
						),
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'text_typography',
					'label'     => esc_html__( 'Text Typography', 'wc-wlfmc-wishlist' ),
					'global'    => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'condition' => array(
						'show_text' => 'true',
					),
					'selector'  => '{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-counter-text',
				)
			);

			$is_inline_font_icon_active = class_exists( 'ElementorPro\Plugin' ) && Plugin::elementor()->experiments->is_feature_active( 'e_font_icon_svg' );

			$this->add_control(
				'is_inline_font_icon',
				array(
					'label'   => __( 'Inline Font Icon', 'wc-wlfmc-wishlist' ),
					'type'    => Controls_Manager::HIDDEN,
					'default' => $is_inline_font_icon_active,
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'           => 'icon_typography',
					'label'          => esc_html__( 'Icon font size', 'wc-wlfmc-wishlist' ),
					'exclude'        => array(
						'font_family',
						'font_weight',
						'text_transform',
						'font_style',
						'text_decoration',
						'letter_spacing',
						'word_spacing',
					),
					'fields_options' => array(
						'typography' => array(
							'default' => 'custom',
						),
						'font_size'  => array(
							'size_units' => array( 'px', 'em', 'rem', 'vw' ),
							'default'    => array(
								'size' => '16',
								'unit' => 'px',
							),
							'responsive' => true,
						),
					),
					'selector'       => '{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-counter-icon i',
					'conditions'     => array(
						'relation' => 'and',
						'terms'    => array(
							array(
								'name'     => 'show_icon',
								'operator' => '=',
								'value'    => 'true',
							),
							array(
								'relation' => 'or',
								'terms'    => array(
									array(
										'name'     => 'icon_name',
										'operator' => '!=',
										'value'    => 'custom',
									),
									array(
										'relation' => 'and',
										'terms'    => array(
											array(
												'name'     => 'is_inline_font_icon',
												'operator' => '!=',
												'value'    => true,
											),
											array(
												'name'     => 'icon_name',
												'operator' => '=',
												'value'    => 'custom',
											),
											array(
												'relation' => 'or',
												'terms'    => array(
													array(
														'name'     => 'empty_icon[library]',
														'operator' => '!=',
														'value'    => 'svg',
													),
													array(
														'name'     => 'has_item_icon[library]',
														'operator' => '!=',
														'value'    => 'svg',
													),
												),
											),
										),
									),
								),
							),
						),
					),
				)
			);

			$this->add_control(
				'icon_size',
				array(
					'label'      => __( 'Icon Size', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 100,
						),
					),
					'default'    => array(
						'size' => 30,
						'unit' => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-counter-icon i.wlfmc-svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};font-size: {{SIZE}}{{UNIT}};',
					),
					'conditions' => array(
						'relation' => 'and',
						'terms'    => array(
							array(
								'relation' => 'and',
								'terms'    => array(
									array(
										'name'     => 'show_icon',
										'operator' => '=',
										'value'    => 'true',
									),
									array(
										'name'     => 'icon_name',
										'operator' => '=',
										'value'    => 'custom',
									),
								),
							),
							array(
								'relation' => 'or',
								'terms'    => array(
									array(
										'name'     => 'is_inline_font_icon',
										'operator' => '=',
										'value'    => true,
									),
									array(
										'name'     => 'empty_icon[library]',
										'operator' => '=',
										'value'    => 'svg',
									),
									array(
										'name'     => 'has_item_icon[library]',
										'operator' => '=',
										'value'    => 'svg',
									),
								),
							),
						),
					),
				)
			);

			$this->add_control(
				'text_color',
				array(
					'label'     => __( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'counter_text_color', '#333' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-counter-text' => 'color: {{VALUE}}!important;',
					),
					'condition' => array(
						'show_text' => 'true',
					),
				)
			);

			$this->add_control(
				'icon_color',
				array(
					'label'     => __( 'Icon Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'counter_color', '#333' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-counter-icon i' => 'color: {{VALUE}}!important;',
					),
					'condition' => array(
						'show_icon' => 'true',
					),
				)
			);

			$this->add_control(
				'count_background_color',
				array(
					'label'     => __( 'Count background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => 'red',
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-counter-number.position-top-right,{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-counter-number.position-top-left' => 'background-color: {{VALUE}}!important;',
					),
					'condition' => array(
						'show_counter'              => 'true',
						'products_number_position!' => array( 'left', 'right' ),

					),
				)
			);

			$this->add_control(
				'count_color',
				array(
					'label'     => __( 'Count Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#fff',
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-counter-number.position-top-right,{{WRAPPER}} .wlfmc-products-counter-wrapper .wlfmc-counter-number.position-top-left' => 'color: {{VALUE}}!important;',
					),
					'condition' => array(
						'show_counter'              => 'true',
						'products_number_position!' => array( 'left', 'right' ),
					),
				)
			);

			$this->end_controls_section();

		}

		/**
		 * Render WishList widget output on the frontend.
		 *
		 * Written in PHP and used to generate the final HTML.
		 *
		 * @access protected
		 */
		protected function render() {

			$attribute_string = '';
			$settings         = $this->get_settings_for_display();

			foreach ( $settings as $key => $value ) {

				if ( in_array(
					$key,
					array(
						'show_icon',
						'show_button',
						'show_totals',
						'show_text',
						'show_counter',
						// 'show_empty_content',
						'display_mode',
						'position_mode',
						'hide_zero_products_number',
						'hide_counter_if_no_items',
						'add_link_title',
						'counter_text',
						'button_text',
						'total_text',
						'per_page',
						'icon_name',
						'empty_icon',
						'has_item_icon',
						'products_number_position',
						'wishlist_link_position',
					),
					true
				) ) {
					if ( isset( $settings['icon_name'] ) && 'custom' === $settings['icon_name'] && ! empty( $value ) && in_array(
						$key,
						array(
							'empty_icon',
							'has_item_icon',
						),
						true
					) ) {
						$icon              = htmlspecialchars( Icons_Manager::try_get_icon_html( $value, array( 'aria-hidden' => 'true' ) ) );
						$attribute_string .= ' icon_prefix_class="" is_svg_icon="true" ';
						$attribute_string .= " $key='{$icon}'";

					} elseif ( 'icon_name' === $key && 'custom' !== $value ) {
						$icon_added        = $value . '-o';
						$attribute_string .= " empty_icon=\"$value\"";
						$attribute_string .= " has_item_icon=\"$icon_added\"";
					} elseif ( ! in_array( $key, array( 'empty_icon', 'has_item_icon' ), true ) ) {
						if ( 'display_mode' === $key ) {
							$show_products      = 'true';
							$dropdown_products  = 'true';
							$show_list_on_hover = 'true';
							switch ( $value ) {
								case 'mini-wishlist':
									$dropdown_products = 'false';
									break;
								case 'counter-only':
									$show_products = 'false';
									break;
								case 'on-click':
									$show_list_on_hover = 'false';
									break;
							}
							$attribute_string .= " show_products=\"$show_products\" dropdown_products=\"$dropdown_products\"  show_list_on_hover=\"$show_list_on_hover\" ";
						} else {
							$attribute_string .= " $key=\"$value\"";
						}
					}
				}
			}

			echo do_shortcode( '[wlfmc_wishlist_counter is_elementor="true"  is_svg_icon="" unique_id="id-' . $this->get_id() . "\" $attribute_string]" );
		}
	}
}

