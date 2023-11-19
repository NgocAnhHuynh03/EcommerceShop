<?php
/**
 * Block Styles
 *
 * @link https://developer.wordpress.org/reference/functions/register_block_style/
 *
 * @package Kiddiemart Lite
 * @since 1.0
 */

if ( function_exists( 'register_block_style' ) ) {
	/**
	 * Register block styles.
	 *
	 * @since 0.1
	 *
	 * @return void
	 */
	function kiddiemart_lite_register_block_styles() {

		//Wp Block Group Padding Zero
		register_block_style(
			'core/group',
			array(
				'name'  => 'padding-0',
				'label' => esc_html__( 'No Padding', 'kiddiemart-lite' ),
			)
		);

		//Wp Block Columns Gap Zero
		register_block_style(
			'core/columns',
			array(
				'name'  => 'have-gap-0',
				'label' => esc_html__( 'Gap Zero', 'kiddiemart-lite' ),
			)
		);

		//Wp Block Post Author Style
		register_block_style(
			'core/post-author',
			array(
				'name'  => 'post-author-card',
				'label' => esc_html__( 'Theme Style', 'kiddiemart-lite' ),
			)
		);

		//Wp Block Button Style
		register_block_style(
			'core/button',
			array(
				'name'         => 'button',
				'label'        => esc_html__( 'Plain', 'kiddiemart-lite' ),
			)
		);

		//Post Comments Style
		register_block_style(
			'core/post-comments',
			array(
				'name'         => 'pro-post-comments',
				'label'        => esc_html__( 'Theme Style', 'kiddiemart-lite' ),
			)
		);

		//Latest Comments Style
		register_block_style(
			'core/latest-comments',
			array(
				'name'         => 'latest-comments',
				'label'        => esc_html__( 'Theme Style', 'kiddiemart-lite' ),
			)
		);

		//Wp Block Quote Style
		register_block_style(
			'core/quote',
			array(
				'name'         => 'block-quote',
				'label'        => esc_html__( 'Theme Style', 'kiddiemart-lite' ),
			)
		);

		//Wp Block Pullquote style
		register_block_style(
			'core/pullquote',
			array(
				'name'         => 'pullquote',
				'label'        => esc_html__( 'Theme Style', 'kiddiemart-lite' ),
			)
		);


		//Wp Block Table Style
		register_block_style(
			'core/table',
			array(
				'name'         => 'wp-table',
				'label'        => esc_html__( 'Theme Style', 'kiddiemart-lite' ),
			)
		);


		//Wp Block Pre Style
		register_block_style(
			'core/preformatted',
			array(
				'name'         => 'wp-preformatted',
				'label'        => esc_html__( 'Theme Style', 'kiddiemart-lite' ),
			)
		);

		//Wp Block Verse Style
		register_block_style(
			'core/verse',
			array(
				'name'         => 'wp-verse',
				'label'        => esc_html__( 'Theme Style', 'kiddiemart-lite' ),
			)
		);
	}
	add_action( 'init', 'kiddiemart_lite_register_block_styles' );
}
