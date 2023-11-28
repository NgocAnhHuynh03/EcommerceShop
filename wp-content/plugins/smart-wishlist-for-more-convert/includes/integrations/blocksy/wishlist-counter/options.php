<?php
/**
 * WLFMC wishlist integration with blocksy theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.6.6
 */

$link_options = array(
	'counter-only' => __( 'Disabled', 'wc-wlfmc-wishlist' ),
	'on-hover'     => __( 'Show on hover', 'wc-wlfmc-wishlist' ),
	'on-click'     => __( 'Show on click', 'wc-wlfmc-wishlist' ),
);
$counter_link = add_query_arg(
	array(
		'page' => 'mc-wishlist-settings',
		'tab'  => 'counter',
	),
	admin_url( 'admin.php' )
);

$options = array(
	'display_mini_wishlist_for_counter' => array(
		'label'   => __( 'Display mini wishlist for counter', 'wc-wlfmc-wishlist' ),
		'type'    => 'ct-select',
		'value'   => 'counter-only',
		'view'    => 'text',
		'choices' => blocksy_ordered_keys( $link_options ),
	),
	blocksy_rand_md5()                  => array(
		'type'      => 'ct-condition',
		'condition' => array( 'display_mini_wishlist_for_counter' => 'counter-only' ),
		'options'   => array(
			'enable_counter_add_link_title' => array(
				'label' => __( 'Add Link For "Wishlist" Counter Title', 'wc-wlfmc-wishlist' ),
				'type'  => 'ct-switch',
				'value' => 'no',
			),
		),
	),

	'wishlist_counter_text'             => array(
		'label' => __( 'Custom Wishlist Text', 'wc-wlfmc-wishlist' ),
		'type'  => 'text',
		'value' => '',
	),

	'wishlist_counter_description'      => array(
		'type'  => 'ct-title',
		'label' => __( 'Other Settings', 'wc-wlfmc-wishlist' ),
		/* translators: %s: multi-list counter setting page */
		'desc'  => sprintf( __( 'To change the settings of this section, please refer to the %s.', 'wc-wlfmc-wishlist' ), '<a href="' . esc_url( $counter_link ) . '" target="_blank">' . __( 'wishlist counter page', 'wc-wlfmc-wishlist' ) . '</a>' ),
	),
);
