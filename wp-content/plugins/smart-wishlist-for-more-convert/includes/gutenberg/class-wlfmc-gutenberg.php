<?php
/**
 * Smart Wishlist Gutenberg Compatibility
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'WLFMC_Gutenberg' ) ) {
	/**
	 * WooCommerce Wishlist Gutenberg Main
	 */
	class WLFMC_Gutenberg {


		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Gutenberg
		 */
		protected static $instance;


		/**
		 * Returns single instance of the class
		 *
		 * @access public
		 *
		 * @return WLFMC_Gutenberg
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

			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_style' ) );
			add_filter( 'woocommerce_blocks_product_grid_item_html', array( $this, 'render_product_block' ), 10, 3 );

		}


		/**
		 * Enqueue block style.
		 *
		 * @return void.
		 */
		public function enqueue_style() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_style( 'wlfmc-blocks', MC_WLFMC_URL . 'assets/frontend/css/style' . $suffix . '.css', false, WLFMC_VERSION );

		}


		/**
		 * Add "add to wishlist" button to some gutenberg loops
		 *
		 * @param string     $html HTML of the single block item.
		 * @param array      $data      Data used to render the item.
		 * @param WC_Product $product   Current product.
		 *
		 * @return string
		 * @version 1.3.0
		 */
		public function render_product_block( $html, $data, $product ) {

			$options            = new MCT_Options( 'wlfmc_options' );
			$gutenberg_position = $options->get_option( 'gutenberg_position', 'after_add_to_cart' );
			$show_on_gutenberg  = $options->get_option( 'show_on_gutenberg', false );
			if ( wlfmc_is_true( $show_on_gutenberg ) ) {
				$add_to_wishlist = do_shortcode( '[wlfmc_add_to_wishlist  is_gutenberg="true" product_id="' . $product->get_id() . '"]' );
				$url             = "<a href=\"$data->permalink\" class=\"wc-block-grid__product-link\">$data->image $data->title</a>";

				$html  = '<li class="wc-block-grid__product">';
				$html .= in_array(
					$gutenberg_position,
					array(
						'image_top_left',
						'image_top_right',
					),
					true
				) ? $add_to_wishlist . $url : $url;
				$html .= 'after_title' === $gutenberg_position ? $add_to_wishlist . "$data->badge" : "$data->badge";
				$html .= 'before_price' === $gutenberg_position ? $add_to_wishlist . "$data->price" : "$data->price";
				$html .= 'after_price' === $gutenberg_position ? $add_to_wishlist . "$data->rating" : "$data->rating";
				$html .= 'before_add_to_cart' === $gutenberg_position ? $add_to_wishlist . "$data->button" : "$data->button";
				$html .= 'after_add_to_cart' === $gutenberg_position ? $add_to_wishlist : '';
				$html .= '</li>';

			}

			return $html;
		}


	}

}

/**
 * Unique access to instance of WLFMC_Gutenberg class
 *
 * @return WLFMC_Gutenberg
 */
function WLFMC_Gutenberg(): WLFMC_Gutenberg { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WLFMC_Gutenberg::get_instance();
}
