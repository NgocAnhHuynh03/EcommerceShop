<?php
/**
 * Header Mobile Wishlist Options
 *
 * @package Kadence_Wlfmc
 */

namespace Kadence_Wlfmc;

use Kadence\Theme_Customizer;
use function Kadence\kadence;

Theme_Customizer::add_settings(
	array(
		'mobile_display_mini_wishlist_for_counter' => array(
			'control_type' => 'kadence_select_control',
			'section'      => 'mobile_wishlist',
			'priority'     => 11,
			'default'      => 'counter-only',
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
		'mobile_enable_counter_add_link_title'     => array(
			'control_type' => 'kadence_switch_control',
			'sanitize'     => 'kadence_sanitize_toggle',
			'section'      => 'mobile_wishlist',
			'priority'     => 6,
			'default'      => true,
			'label'        => __( 'Add Link For "Wishlist" Counter Title', 'wc-wlfmc-wishlist' ),
			'context'      => array(
				array(
					'setting'  => 'mobile_display_mini_wishlist_for_counter',
					'operator' => '=',
					'value'    => 'counter-only',
				),
			),
			'transport'    => 'refresh',
		),
	)
);
