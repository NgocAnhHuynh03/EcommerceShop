<?php
/**
 * WLFMC wishlist integration with blocksy theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.6.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_filter(
	'blocksy:header:selective_refresh',
	function ( $selective_refresh ) {
		$selective_refresh[] = array(
			'id'                  => 'header_placements_item:wishlist-counter',
			'fallback_refresh'    => false,
			'container_inclusive' => true,
			'selector'            => '#main-container > header',
			'loader_selector'     => '[data-id="wishlist-counter"]',
			'settings'            => array( 'header_placements' ),
			'render_callback'     => function () {
				echo blocksy_manager()->header_builder->render();//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			},
		);
		return $selective_refresh;
	}
);

add_filter(
	'blocksy:header:items-paths',
	function ( $paths ) {
		$paths[] = dirname( __FILE__ ) . '/blocksy';
		return $paths;
	}
);
