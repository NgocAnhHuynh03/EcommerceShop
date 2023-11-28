<?php
/**
 * WLFMC wishlist integration with Kadence theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.6.5
 */

namespace Kadence_Wlfmc;

use Kadence\Theme_Customizer;
use function Kadence\kadence;

Theme_Customizer::add_settings(
	array(
		'display_mini_wishlist_for_counter' => array(
			'control_type' => 'kadence_select_control',
			'section'      => 'header_wishlist',
			'priority'     => 11,
			'default'      => 'on-hover',
			'label'        => __( 'Display mini wishlist for counter', 'wc-wlfmc-wishlist' ),
			'input_attrs'  => array(
				'options' => array(
					'counter-only' => array(
						'name' => __( 'Disabled', 'wc-wlfmc-wishlist' ),
					),
					'on-hover'     => array(
						'name' => __( 'Show on hover', 'wc-wlfmc-wishlist' ),
					),
					'on-click'     => array(
						'name' => __( 'Show on click', 'wc-wlfmc-wishlist' ),
					),
				),
			),
			'transport'    => 'refresh',
		),
		'enable_counter_add_link_title'     => array(
			'control_type' => 'kadence_switch_control',
			'sanitize'     => 'kadence_sanitize_toggle',
			'section'      => 'header_wishlist',
			'priority'     => 6,
			'default'      => false,
			'label'        => __( 'Add Link For "Wishlist" Counter Title', 'wc-wlfmc-wishlist' ),
			'context'      => array(
				array(
					'setting'  => 'display_mini_wishlist_for_counter',
					'operator' => '=',
					'value'    => 'counter-only',
				),
			),
			'transport'    => 'refresh',
		),
	)
);
