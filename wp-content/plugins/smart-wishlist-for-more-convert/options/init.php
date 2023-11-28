<?php
/**
 * Plugin Options.
 *
 * @author MoreConvert
 * @package MoreConvert Options plugin
 * @version 2.5.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
! defined( 'MCT_OPTION_PLUGIN_TEMPLATE_PATH' ) && define( 'MCT_OPTION_PLUGIN_TEMPLATE_PATH', dirname( __FILE__ ) );
! defined( 'MCT_OPTION_PLUGIN_URL' ) && define( 'MCT_OPTION_PLUGIN_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

// load from theme folder...
load_textdomain( 'mct-options', get_template_directory() . '/core/mct-options/mct-options-' . apply_filters( 'plugin_locale', get_locale(), 'mct-options' ) . '.mo' ) ||

// ...or from plugin folder.
load_textdomain( 'mct-options', dirname( __FILE__ ) . '/languages/mct-options-' . apply_filters( 'plugin_locale', get_locale(), 'mct-options' ) . '.mo' );


if ( ! function_exists( 'mct_option_plugin_loader' ) ) {
	/**
	 * Load the plugin Options if it's not yet loaded.
	 *
	 * @param string $plugin_path The plugin path.
	 */
	function mct_option_plugin_loader( $plugin_path ) {
		require_once $plugin_path . 'options/class-mct-ajax-handler.php';
		require_once $plugin_path . 'options/class-mct-admin.php';
		require_once $plugin_path . 'options/class-mct-fields.php';
		require_once $plugin_path . 'options/class-mct-options.php';
	}
}
