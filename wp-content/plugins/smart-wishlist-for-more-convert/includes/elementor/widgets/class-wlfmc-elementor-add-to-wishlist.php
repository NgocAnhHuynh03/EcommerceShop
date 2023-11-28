<?php
/**
 * Add to WishList Widget.
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.6.7
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
use Elementor\Icons_Manager;
use ElementorPro\Plugin;

if ( ! class_exists( 'WLFMC_Elementor_Add_To_Wishlist' ) ) {
	/**
	 * Add to wishlist  Elementor Widget
	 */
	class WLFMC_Elementor_Add_To_Wishlist extends Widget_Base {

		/**
		 * Get widget name.
		 *
		 * Retrieve Add to WishList widget name.
		 *
		 * @return string Widget Name.
		 * @since 1.0.1
		 * @access public
		 */
		public function get_name(): string {
			return 'wlfmc-add-to-wish-list';
		}

		/**
		 * Get widget title.
		 *
		 * Retrieve Add to WishList widget title.
		 *
		 * @return string Widget Title.
		 * @since 1.0.1
		 * @access public
		 */
		public function get_title(): string {
			return esc_html__( 'Add To Wishlist', 'wc-wlfmc-wishlist' );
		}

		/**
		 * Get widget icon.
		 *
		 * Retrieve Add to WishList widget icon.
		 *
		 * @return string Widget Icon.
		 * @since 1.0.1
		 * @access public
		 */
		public function get_icon(): string {
			return 'eicon-plus-square-o';
		}

		/**
		 * Get widget categories.
		 *
		 * Retrieve Add to WishList widget categories.
		 *
		 * @return array Widget Categories.
		 * @since 1.0.1
		 * @access public
		 */
		public function get_categories(): array {
			return array( 'WLFMC_WishList' );
		}

		/**
		 * Register Add to WishList Widget Controls
		 *
		 * @return void.
		 * @access protected
		 *
		 * @version 1.4.0
		 * @since   1.0.1
		 */
		protected function register_controls() {

			$options = new MCT_Options( 'wlfmc_options' );

			// Settings Controls.
			$this->settings_controls( $options );

			// Styling Controls.
			$this->styling_controls( $options );

		}

		/**
		 * Table Settings Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void
		 */
		protected function settings_controls( MCT_Options $options ) {

			$this->start_controls_section(
				'section_button',
				array(
					'label' => esc_html__( 'Button', 'wc-wlfmc-wishlist' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);

			$this->add_control(
				'product_id',
				array(
					'label'       => esc_html__( 'Product ID', 'wc-wlfmc-wishlist' ),
					'type'        => Controls_Manager::NUMBER,
					'default'     => '',
					'placeholder' => '123',
					'description' => esc_html__( 'You can add custom product id or leave empty.', 'wc-wlfmc-wishlist' ),

				)
			);

			$this->add_control(
				'is_single',
				array(
					'label'        => esc_html__( 'Is product single page', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',

				)
			);

			$this->add_control(
				'is_single_description',
				array(
					'raw'             => esc_html__( 'You should install custom skin plugin and create a loop for editing product listing on the Elementor editor or you can design listings from wishlist setting > wishlist button', 'wc-wlfmc-wishlist' ),
					'type'            => Controls_Manager::RAW_HTML,
					'content_classes' => 'elementor-descriptor',
				)
			);

			$this->add_control(
				'button_type',
				array(
					'label'   => esc_html__( 'Button Type', 'wc-wlfmc-wishlist' ),
					'type'    => Controls_Manager::SELECT,
					'options' => array(
						'icon' => esc_html__( 'Icon only', 'wc-wlfmc-wishlist' ),
						'text' => esc_html__( 'Text only', 'wc-wlfmc-wishlist' ),
						'both' => esc_html__( 'Icon and text', 'wc-wlfmc-wishlist' ),
					),
					'default' => $options->get_option( 'button_type_single', 'icon' ),
				)
			);

			$this->add_control(
				'icon_name',
				array(
					'label'     => esc_html__( 'Button Icon', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => wlfmc_get_icon_names( 'wishlist' ),
					'default'   => $options->get_option( 'icon_name_single', 'heart' ),
					'condition' => array(
						'button_type!' => 'text',
					),
				)
			);

			$this->add_control(
				'icon',
				array(
					'label'       => esc_html__( 'Icon', 'wc-wlfmc-wishlist' ),
					'type'        => Controls_Manager::ICONS,
					'skin'        => 'inline',
					'label_block' => false,
					'condition'   => array(
						'button_type!' => 'text',
						'icon_name'    => 'custom',
					),
				)
			);

			$this->add_control(
				'added_icon',
				array(
					'label'       => esc_html__( 'Icon Added', 'wc-wlfmc-wishlist' ),
					'type'        => Controls_Manager::ICONS,
					'skin'        => 'inline',
					'label_block' => false,
					'condition'   => array(
						'button_type!' => 'text',
						'icon_name'    => 'custom',
					),
				)
			);

			$this->add_control(
				'separate_icon_and_text',
				array(
					'label'        => esc_html__( 'Separate icon and text', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => '1',
					'default'      => wlfmc_is_true( $options->get_option( 'separate_icon_and_text_single', false ) ) ? 'true' : '',
					'condition'    => array(
						'button_type' => 'both',
					),
				)
			);

			$this->add_control(
				'separator_color',
				array(
					'label'     => esc_html__( 'Separator color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'separator_color_single', 'transparent' ),
					'condition' => array(
						'separate_icon_and_text' => '1',
						'button_type'            => 'both',
					),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-add-button > a.have-sep span:before' => 'border-left-color: {{VALUE}}!important;',
					),
				)
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_tooltip',
				array(
					'label'     => esc_html__( 'Tooltip', 'wc-wlfmc-wishlist' ),
					'tab'       => Controls_Manager::TAB_CONTENT,
					'condition' => array(
						'button_type' => 'icon',
					),
				)
			);

			$this->add_control(
				'enable_tooltip',
				array(
					'label'        => esc_html__( 'Button tooltip', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => '1',
					'default'      => wlfmc_is_true( $options->get_option( 'enable_tooltip_single', false ) ) ? 'true' : '',

				)
			);

			$this->add_control(
				'tooltip_direction',
				array(
					'label'     => esc_html__( 'Tooltip direction', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'top'    => esc_html__( 'Top', 'wc-wlfmc-wishlist' ),
						'bottom' => esc_html__( 'Bottom', 'wc-wlfmc-wishlist' ),
						'right'  => esc_html__( 'Right', 'wc-wlfmc-wishlist' ),
						'left'   => esc_html__( 'Left', 'wc-wlfmc-wishlist' ),
					),
					'default'   => $options->get_option( 'tooltip_direction_single', 'top' ),
					'condition' => array(
						'enable_tooltip' => '1',
					),
				)
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_labels',
				array(
					'label' => esc_html__( 'Button and tooltip custom text', 'wc-wlfmc-wishlist' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);

			$this->add_control(
				'button_label_add',
				array(
					'label'       => esc_html__( '"Add To Wishlist" text', 'wc-wlfmc-wishlist' ),
					'type'        => Controls_Manager::TEXT,
					'input_type'  => 'text',
					'default'     => $options->get_option( 'button_label_add', esc_html__( 'Add To Wishlist', 'wc-wlfmc-wishlist' ) ),
					'placeholder' => esc_html__( 'Add To Wishlist', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'button_label_view',
				array(
					'label'       => esc_html__( '"View My Wishlist" text', 'wc-wlfmc-wishlist' ),
					'type'        => Controls_Manager::TEXT,
					'input_type'  => 'text',
					'default'     => $options->get_option( 'button_label_view', esc_html__( 'View My Wishlist', 'wc-wlfmc-wishlist' ) ),
					'placeholder' => esc_html__( 'View My Wishlist', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'button_label_remove',
				array(
					'label'       => esc_html__( '"Remove From Wishlist" text', 'wc-wlfmc-wishlist' ),
					'type'        => Controls_Manager::TEXT,
					'input_type'  => 'text',
					'default'     => $options->get_option( 'button_label_remove', esc_html__( 'Remove From Wishlist', 'wc-wlfmc-wishlist' ) ),
					'placeholder' => esc_html__( 'Remove From Wishlist', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->add_control(
				'button_label_exists',
				array(
					'label'       => esc_html__( '"Already In Wishlist" text', 'wc-wlfmc-wishlist' ),
					'type'        => Controls_Manager::TEXT,
					'input_type'  => 'text',
					'default'     => $options->get_option( 'button_label_exists', esc_html__( 'Already In Wishlist', 'wc-wlfmc-wishlist' ) ),
					'placeholder' => esc_html__( 'Already in Wishlist', 'wc-wlfmc-wishlist' ),
				)
			);

			$this->end_controls_section();

		}

		/**
		 * Table Styling Controls
		 *
		 * @param MCT_Options $options saved options for default values.
		 *
		 * @return void
		 */
		protected function styling_controls( MCT_Options $options ) {
			$this->start_controls_section(
				'section_style',
				array(
					'label' => esc_html__( 'Custom Style', 'wc-wlfmc-wishlist' ),
					'tab'   => Controls_Manager::TAB_STYLE,
				)
			);
			$this->add_control(
				'button_theme',
				array(
					'label'        => esc_html__( 'Default button style', 'wc-wlfmc-wishlist' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => wlfmc_is_true( $options->get_option( 'button_theme_single', true ) ) ? 'true' : '',

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
					'name'      => 'text_typography',
					'label'     => esc_html__( 'Text Typography', 'wc-wlfmc-wishlist' ),
					'global'    => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'exclude'   => array(
						'line_height',

					),
					'selector'  => '{{WRAPPER}} .wlfmc-add-button a',
					'condition' => array(
						'button_type!'  => 'icon',
						'button_theme!' => 'true',
					),
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
						'line_height',
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
					'selector'       => '{{WRAPPER}} .wlfmc-add-button a i',
					'conditions'     => array(
						'relation' => 'and',
						'terms'    => array(
							array(
								'relation' => 'and',
								'terms'    => array(
									array(
										'name'     => 'button_type',
										'operator' => '!=',
										'value'    => 'text',
									),
									array(
										'name'     => 'button_theme',
										'operator' => '!=',
										'value'    => 'true',
									),
								),
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
														'name'     => 'icon[library]',
														'operator' => '!=',
														'value'    => 'svg',
													),
													array(
														'name'     => 'added_icon[library]',
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
						'{{WRAPPER}} .wlfmc-add-button a i.wlfmc-svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};font-size: {{SIZE}}{{UNIT}};',
					),
					'conditions' => array(
						'relation' => 'and',
						'terms'    => array(
							array(
								'relation' => 'and',
								'terms'    => array(
									array(
										'name'     => 'button_type',
										'operator' => '!=',
										'value'    => 'text',
									),
									array(
										'name'     => 'button_theme',
										'operator' => '!=',
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
										'name'     => 'icon[library]',
										'operator' => '=',
										'value'    => 'svg',
									),
									array(
										'name'     => 'added_icon[library]',
										'operator' => '=',
										'value'    => 'svg',
									),
								),
							),
						),
					),
				)
			);

			$this->start_controls_tabs(
				'tabs_button_style',
				array(
					'condition' => array(
						'button_theme!' => 'true',
					),
				)
			);

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
					'default'   => $options->get_option( 'text_color_single', 'rgb(230,126,34)' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-add-button a' => 'color: {{VALUE}}!important;',
					),
				)
			);

			$this->add_control(
				'button_icon_color',
				array(
					'label'     => esc_html__( 'Icon Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'icon_color_single', 'rgb(230,126,34)' ),
					'selectors' => array(
						'{{WRAPPER}} i, {{WRAPPER}} svg' => 'fill: {{VALUE}}!important; color: {{VALUE}}!important;',
					),
				)
			);

			$this->add_control(
				'button_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'button_border_color_single', 'transparent' ),
					'condition' => array(
						'border_border!' => '',
					),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-add-button a' => 'border-color: {{VALUE}}!important;',
					),
				)
			);

			$this->add_control(
				'button_background_color',
				array(
					'label'     => esc_html__( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'button_background_color_single', 'transparent' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-add-button a' => 'background-color: {{VALUE}}!important;',
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
				'hover_color',
				array(
					'label'     => esc_html__( 'Text Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-add-button a:hover, {{WRAPPER}} .wlfmc-add-button a:focus' => 'color: {{VALUE}}!important;',
					),
					'default'   => $options->get_option( 'text_hover_color_single', 'rgb(81,81,81)' ),

				)
			);

			$this->add_control(
				'hover_icon_color',
				array(
					'label'     => esc_html__( 'Icon Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'icon_hover_color_single', 'rgb(81,81,81)' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-add-button a:hover i, {{WRAPPER}} .wlfmc-add-button a:hover svg' => 'fill: {{VALUE}}!important; color: {{VALUE}}!important;',
					),
				)
			);

			$this->add_control(
				'button_hover_border_color',
				array(
					'label'     => esc_html__( 'Border Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'button_border_hover_color_single', 'transparent' ),
					'condition' => array(
						'border_border!' => '',
					),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-add-button a:hover, {{WRAPPER}} .wlfmc-add-button a:focus' => 'border-color: {{VALUE}}!important;',
					),
				)
			);

			$this->add_control(
				'button_background_hover',
				array(
					'label'     => esc_html__( 'Background Color', 'wc-wlfmc-wishlist' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => $options->get_option( 'button_background_hover_color_single', 'transparent' ),
					'selectors' => array(
						'{{WRAPPER}} .wlfmc-add-button a:hover, {{WRAPPER}} .wlfmc-add-button a:focus' => 'background-color: {{VALUE}}!important;',
					),
				)
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'           => 'border',
					'selector'       => '{{WRAPPER}} .wlfmc-add-button a',
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
						'button_theme!' => 'true',
					),
				)
			);

			$this->add_control(
				'border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-add-button a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}!important;',
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
						'button_theme!' => 'true',
					),
				)
			);

			$this->add_responsive_control(
				'button_height',
				array(
					'label'      => esc_html__( 'Button height', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em' ),
					'default'    => array( 'size' => intval( $options->get_option( 'button_height_single', '45px' ) ) ),
					'separator'  => 'before',
					'range'      => array(
						'em' => array(
							'min'  => 0,
							'step' => 1,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-add-button a' => 'height: {{SIZE}}{{UNIT}}!important',
					),
					'condition'  => array(
						'button_theme!' => 'true',
					),
				)
			);

			$this->add_responsive_control(
				'button_width',
				array(
					'label'      => esc_html__( 'Button width', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em' ),
					'default'    => array( 'size' => intval( $options->get_option( 'button_width_single', '45px' ) ) ),
					'range'      => array(
						'em' => array(
							'min'  => 0,
							'step' => 1,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-add-button a' => 'width: {{SIZE}}{{UNIT}}!important',
					),
					'condition'  => array(
						'button_type'   => 'icon',
						'button_theme!' => 'true',
					),
				)
			);

			$this->add_responsive_control(
				'button_width_type',
				array(
					'label'                => esc_html__( 'Button Width Type', 'wc-wlfmc-wishlist' ),
					'type'                 => Controls_Manager::SELECT,
					'default'              => '',
					'options'              => array(
						''        => esc_html__( 'Default', 'wc-wlfmc-wishlist' ),
						'inherit' => esc_html__( 'Full Width', 'wc-wlfmc-wishlist' ) . ' (100%)',
						'auto'    => esc_html__( 'Inline', 'wc-wlfmc-wishlist' ) . ' (auto)',
					),
					'selectors_dictionary' => array(
						'inherit' => '100%',
					),
					'prefix_class'         => 'elementor-widget%s__width-',
					'selectors'            => array(
						'{{WRAPPER}}, {{WRAPPER}} .wlfmc-add-button a,{{WRAPPER}} .wlfmc-add-to-wishlist,{{WRAPPER}} .wlfmc-add-button' => 'width: {{VALUE}} !important',
					),
					'condition'            => array(
						'button_theme!' => 'true',
					),
				)
			);

			$this->add_responsive_control(
				'button_margin',
				array(
					'label'      => esc_html__( 'Button margin', 'wc-wlfmc-wishlist' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						'{{WRAPPER}} .wlfmc-add-to-wishlist' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					),
					'default'    => array(
						'top'      => '0',
						'right'    => '0',
						'bottom'   => '0',
						'left'     => '0',
						'unit'     => 'px',
						'isLinked' => true,
					),
					'condition'  => array(
						'button_theme!' => 'true',
					),
				)
			);

			$this->end_controls_section();

		}

		/**
		 * Render Add to WishList widget output on the frontend.
		 *
		 * Written in PHP and used to generate the final HTML.
		 *
		 * @version 1.4.0
		 * @since   1.0.1
		 * @access protected
		 */
		protected function render() {
			$attribute_string = '';
			$settings         = $this->get_settings_for_display();

			foreach ( $settings as $key => $value ) {

				if ( in_array(
					$key,
					array(
						'product_id',
						'is_single',
						'button_type',
						'icon_name',
						'icon',
						'added_icon',
						'button_theme',
						'separate_icon_and_text',
						'enable_tooltip',
						'tooltip_direction',
						'button_label_add',
						'button_label_view',
						'button_label_remove',
						'button_label_exists',

					),
					true
				) ) {

					if ( 'is_single' === $key ) {
						$attribute_string .= 'true' === $value ? ' is_single="true"' : ' is_single=""';
					} elseif ( isset( $settings['icon_name'] ) && 'custom' === $settings['icon_name'] && ! empty( $value ) && in_array(
						$key,
						array(
							'icon',
							'added_icon',
						),
						true
					) ) {

						$icon              = Icons_Manager::try_get_icon_html( $value, array( 'aria-hidden' => 'true' ) );
						$attribute_string .= ' icon_prefix_class="" is_svg_icon="true" ';
						$attribute_string .= " $key='{$icon}'";
					} elseif ( 'icon_name' === $key && 'custom' !== $value ) {
						$icon_added        = $value . '-o';
						$attribute_string .= " icon=\"$value\"";
						$attribute_string .= " added_icon=\"$icon_added\"";
					} elseif ( ! in_array( $key, array( 'icon', 'added_icon' ), true ) ) {

						$attribute_string .= " $key=\"$value\"";
					}

					if ( in_array( $key, array( 'button_label_add', 'button_label_view', 'button_label_remove', 'button_label_exists' ), true ) ) {
						$tooltip_key       = str_replace( 'button', 'tooltip', $key );
						$attribute_string .= " $tooltip_key=\"$value\"";
					}
				}
			}

			echo do_shortcode( "[wlfmc_add_to_wishlist is_svg_icon=\"\"  is_elementor=\"true\" $attribute_string]" );
		}
	}
}

