<?php
/**
 * Smart Wishlist Elementor Compatibility
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Plugin;

if ( ! class_exists( 'WLFMC_Elementor' ) ) {
	/**
	 * WooCommerce Wishlist Elementor Main
	 */
	class WLFMC_Elementor {
		/**
		 * Current Dir
		 *
		 * @var string $dir
		 */
		private $dir;

		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Elementor
		 */
		protected static $instance;


		/**
		 * Returns single instance of the class
		 *
		 * @access public
		 * @return WLFMC_Elementor
		 * @since 1.0.1
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}


		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {
			$this->dir = __DIR__;

			add_action( 'elementor/init', array( $this, 'setup_categories' ), 0 );

			if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
				add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ), 0 );
			} else {
				add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ), 0 );
			}

			add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_style' ), 0 );
			if ( ! defined( 'WLFMC_PREMIUM_VERSION' ) ) {
				add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'after_enqueue_scripts' ) );
			}
		}


		/**
		 * Register Widgets
		 *
		 * @access public
		 *
		 * @return void
		 * @version 1.4.0
		 * @since 1.0.1
		 */
		public function register_widgets() {
			// Widgets.
			$build_widgets_filename = array(
				'add-to-wishlist',
				'wishlist',
				'wishlist-counter',
			);

			foreach ( $build_widgets_filename as $widget_filename ) {
				include $this->dir . '/widgets/class-wlfmc-elementor-' . $widget_filename . '.php';

				$class_name = ucwords( $widget_filename, '-' );

				$class_name = str_replace( '-', '_', $class_name );

				$class_name = '\WLFMC_Elementor_' . $class_name;

				if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
					Plugin::instance()->widgets_manager->register( new $class_name() );
				} else {
					Plugin::instance()->widgets_manager->register_widget_type( new $class_name() );
				}
			}
		}


		/**
		 * Enqueue Elementor Editor Style.
		 *
		 * @access public
		 * @return void
		 * @since 1.0.1
		 */
		public function enqueue_style() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_style( 'wlfmc-elementor-editor', MC_WLFMC_URL . 'assets/backend/css/elementor-editor' . $suffix . '.css', null, WLFMC_VERSION );
		}

		/**
		 * Setup Elementor Categories
		 *
		 * @access public
		 * @return void
		 * @since 1.0.1
		 */
		public function setup_categories() {
			Plugin::instance()->elements_manager->add_category(
				'WLFMC_WishList',
				array(
					'title' => esc_html__( 'MoreConvert', 'wc-wlfmc-wishlist' ),
					'icon'  => 'eicon-heart',
				)
			);
		}

		/**
		 * After enqueue Scripts
		 *
		 * Loads editor scripts for our controls.
		 *
		 * @access public
		 * @return void
		 */
		public function after_enqueue_scripts() {

			$widgets = array(
				array(
					'key'        => 'wlfmc-premium-add-to-list',
					'name'       => 'wlfmc-premium-add-to-list',
					'title'      => esc_html__( 'Add To Lists', 'wc-wlfmc-wishlist' ),
					'is_pro'     => true,
					'draw_svg'   => true,
					'icon'       => 'eicon-toggle',
					'action_url' => 'https://moreconvert.com',
				),
				array(
					'key'        => 'wlfmc-premium-all-add-to-lists',
					'name'       => 'wlfmc-premium-all-add-to-lists',
					'title'      => esc_html__( 'All Button Add To Lists', 'wc-wlfmc-wishlist' ),
					'is_pro'     => true,
					'draw_svg'   => true,
					'icon'       => 'eicon-dual-button',
					'action_url' => 'https://moreconvert.com',
				),
				array(
					'key'        => 'wlfmc-premium-list-counter',
					'name'       => 'wlfmc-premium-list-counter',
					'title'      => esc_html__( 'Mini Lists and Counter', 'wc-wlfmc-wishlist' ),
					'is_pro'     => true,
					'draw_svg'   => true,
					'icon'       => 'eicon-mega-menu',
					'action_url' => 'https://moreconvert.com',
				),
				array(
					'key'        => 'wlfmc-premium-lists',
					'name'       => 'wlfmc-premium-lists',
					'title'      => esc_html__( 'Tabbed Lists', 'wc-wlfmc-wishlist' ),
					'is_pro'     => true,
					'draw_svg'   => true,
					'icon'       => 'eicon-kit-parts',
					'action_url' => 'https://moreconvert.com',
				),
				array(
					'key'        => 'wlfmc-premium-multi-list',
					'name'       => 'wlfmc-premium-multi-list',
					'title'      => esc_html__( 'Multi List', 'wc-wlfmc-wishlist' ),
					'is_pro'     => true,
					'draw_svg'   => true,
					'icon'       => 'eicon-kit-parts',
					'action_url' => 'https://moreconvert.com',
				),
				array(
					'key'        => 'wlfmc-premium-outofstock',
					'name'       => 'wlfmc-premium-outofstock',
					'title'      => esc_html__( 'OutOfStock Box', 'wc-wlfmc-wishlist' ),
					'is_pro'     => true,
					'draw_svg'   => true,
					'icon'       => 'eicon-product-stock',
					'action_url' => 'https://moreconvert.com',
				),
				array(
					'key'        => 'wlfmc-premium-waitlist',
					'name'       => 'wlfmc-premium-waitlist',
					'title'      => esc_html__( 'Waitlist', 'wc-wlfmc-wishlist' ),
					'is_pro'     => true,
					'draw_svg'   => true,
					'icon'       => 'eicon-table',
					'action_url' => 'https://moreconvert.com',
				),
				array(
					'key'        => 'wlfmc-premium-waitlist-counter',
					'name'       => 'wlfmc-premium-waitlist-counter',
					'title'      => esc_html__( 'Mini Waitlist and Counter', 'wc-wlfmc-wishlist' ),
					'is_pro'     => true,
					'draw_svg'   => true,
					'icon'       => 'eicon-table',
					'action_url' => 'https://moreconvert.com',
				),
			);
			$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_register_script(
				'wlfmc-elementor-editor',
				MC_WLFMC_URL . 'assets/backend/js/editor' . $suffix . '.js',
				array( 'elementor-editor', 'jquery' ),
				WLFMC_VERSION,
				true
			);

			wp_localize_script(
				'wlfmc-elementor-editor',
				'WlfmcPanelSettings',
				array(
					'wlfmc_pro_widgets' => $widgets,
				)
			);
			wp_enqueue_script( 'wlfmc-elementor-editor' );

		}
	}

}

/**
 * Unique access to instance of WLFMC_Elementor class
 *
 * @return WLFMC_Elementor
 */
function WLFMC_Elementor(): WLFMC_Elementor {  // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WLFMC_Elementor::get_instance();
}
