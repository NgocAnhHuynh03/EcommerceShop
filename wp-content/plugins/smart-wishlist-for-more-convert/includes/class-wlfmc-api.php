<?php
/**
 * Smart Wishlist Api
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.5.5
 * @since 1.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Api' ) ) {
	/**
	 * This class handle rest api for wishlist plugin
	 */
	class WLFMC_Api {

		/**
		 * Endpoint namespace.
		 *
		 * @var string
		 */
		protected const NAMESPACE = 'wlfmc';

		/**
		 * Route base.
		 *
		 * @var string
		 */
		protected const REST_BASE = 'v1';

		/**
		 * Load the new REST API wishlist endpoints
		 */
		public function register_routes() {

			register_rest_route(
				self::NAMESPACE . '/' . self::REST_BASE,
				'/call',
				array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( 'WLFMC_Api', 'do_action' ),
						'permission_callback' => '__return_true',
					),
				)
			);
		}

		/**
		 * Manage all rest_api ajax mode actions
		 *
		 * @param WP_REST_Request $request  The request object.
		 *
		 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
		 */
		public static function do_action( WP_REST_Request $request ) {

			$data   = $request->get_body_params();
			$action = sanitize_key( $data['action'] );

			if ( ! in_array(
				$action,
				array(
					'wlfmc_wp_rest_add_to_wishlist',
					'wlfmc_wp_rest_remove_from_wishlist',
					'wlfmc_wp_rest_update_item_quantity',
					'wlfmc_wp_rest_delete_item',
					'wlfmc_wp_rest_load_fragments',
					'wlfmc_wp_rest_load_automations',
					'wlfmc_wp_rest_change_layout',
				),
				true
			) ) {
				return rest_ensure_response( array( 'result' => false ) );
			}

			$action = str_replace( 'wlfmc_wp_rest_', '', $action );

			return self::$action( $data );

		}

		/**
		 * Add new hook for work automations after product added to lists
		 *
		 * @param array $data $_POST data.
		 *
		 * @return void
		 */
		public static function load_automations( array $data ) {
			$product_id  = isset( $data['product_id'] ) ? intval( $data['product_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$wishlist_id = isset( $data['wishlist_id'] ) ? intval( $data['wishlist_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$list_type   = isset( $data['list_type'] ) ? sanitize_text_field( wp_unslash( $data['list_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$customer_id = isset( $data['customer_id'] ) ? intval( $data['customer_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			if ( ! $wishlist_id || ! $product_id || ! $customer_id ) {
				die();
			}

			do_action( 'wlfmc_load_automations', $product_id, $wishlist_id, $customer_id, $list_type );

			// stops ajax call from further execution (no return value expected on answer body).
			die();
		}

		/**
		 *  Change wishlist and other lists view layout.
		 *
		 * @param array $data $_POST data.
		 *
		 * @return void
		 */
		public static function change_layout( array $data ) {
			$new_layout = isset( $data['new_layout'] ) ? sanitize_key( $data['new_layout'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			if ( ! in_array( $new_layout, array( 'list', 'grid' ), true ) ) {
				die();
			}
			if ( is_user_logged_in() ) {
				update_user_meta( get_current_user_id(), 'wlfmc_list_layout', $new_layout );
			} else {
				wlfmc_setcookie( 'wlfmc_list_layout', $new_layout );
			}

			// stops ajax call from further execution (no return value expected on answer body).
			die();
		}

		/**
		 * Add to wishlist from ajax call
		 *
		 * @param array $data $_POST data.
		 *
		 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
		 */
		public static function add_to_wishlist( array $data ) {

			$result = false;

			$args = array(
				'add_to_wishlist'     => 0,
				'wishlist_id'         => 0,
				'quantity'            => 1,
				'user_id'             => false,
				'dateadded'           => '',
				'wishlist_name'       => '',
				'wishlist_visibility' => 0,
			);
			$args = wp_parse_args( $data, $args );

			try {
				$result  = WLFMC()->add( $args );
				$return  = 'true';
				$message = '';

			} catch ( WLFMC_Exception $e ) {
				$return  = $e->getTextualCode();
				$message = apply_filters( 'wlfmc_error_adding_to_wishlist_message', $e->getMessage() );
			} catch ( Exception $e ) {
				$return  = 'error';
				$message = apply_filters( 'wlfmc_error_adding_to_wishlist_message', $e->getMessage() );
			}
			$product_id = isset( $data['add_to_wishlist'] ) ? intval( $data['add_to_wishlist'] ) : false; // phpcs:ignore WordPress.Security
			$fragments  = isset( $data['fragments'] ) ? json_decode( wp_unslash( $data['fragments'] ), true ) : false;// phpcs:ignore WordPress.Security

			$found_item    = false;
			$found_in_list = false;
			if ( 'exists' === $return ) {
				$found_in_list = wlfmc_get_wishlist( false );
				$found_item    = $found_in_list ? $found_in_list->get_product( $product_id ) : false;
			}
			$wishlist_url = WLFMC()->get_wc_wishlist_url( 'wishlist', 'last_operation' );

			return rest_ensure_response(
				apply_filters(
					'wlfmc_rest_add_return_params',
					array(
						'prod_id'      => $product_id,
						'result'       => $return,
						'message'      => $message,
						'wishlist_url' => $wishlist_url,
						'fragments'    => WLFMC_Ajax_Handler::refresh_fragments( $fragments ),
						'wishlist_id'  => $result ? $result['wishlist_id'] : ( $found_in_list ? $found_in_list->get_id() : false ),
						'item_id'      => $result ? $result['item_id'] : ( $found_item ? $found_item->get_id() : false ),
						'customer_id'  => $result ? $result['customer_id'] : ( $found_item ? $found_item->get_customer_id() : false ),
					)
				)
			);
		}

		/**
		 * Remove item from a wishlist
		 *
		 * @param array $data $_POST data.
		 *
		 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
		 */
		public static function remove_from_wishlist( array $data ) {
			$fragments = isset( $data['fragments'] ) ? wp_unslash( $data['fragments'] ) : false;
			$return    = false;
			$result    = false;

			$args = array(
				'remove_from_wishlist' => 0,
				'wishlist_id'          => 0,
				'user_id'              => false,
			);
			$args = wp_parse_args( $data, $args );
			try {
				$result  = WLFMC()->remove( $args );
				$return  = 'true';
				$message = '';
			} catch ( Exception $e ) {
				$message = $e->getMessage();
			}

			return rest_ensure_response(
				array(
					'result'    => $return,
					'message'   => $message,
					'count'     => $result ? $result['count'] : false,
					'fragments' => WLFMC_Ajax_Handler::refresh_fragments( $fragments ),
				)
			);
		}

		/**
		 * Remove item from a wishlist
		 * Differs from remove from wishlist, since this accepts item id instead of product id
		 *
		 * @param array $data $_POST data.
		 *
		 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
		 */
		public static function delete_item( array $data ) {

			$item_id   = isset( $data['item_id'] ) ? intval( $data['item_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$fragments = isset( $data['fragments'] ) ? wp_unslash( $data['fragments'] ) : false;
			$return    = array(
				'result' => false,
			);

			if ( $item_id ) {
				$item = WLFMC_Wishlist_Factory::get_wishlist_item( $item_id );

				if ( $item ) {
					$item->delete();
					$message = '';

					$return = array(
						'result'    => 'true',
						'message'   => $message,
						'fragments' => WLFMC_Ajax_Handler::refresh_fragments( $fragments ),
					);
				}
			}

			return rest_ensure_response( $return );
		}

		/**
		 * Update quantity of an item in wishlist
		 *
		 * @param array $data $_POST data.
		 *
		 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
		 */
		public static function update_item_quantity( array $data ) {
			$wishlist_token = isset( $data['wishlist_token'] ) ? sanitize_text_field( wp_unslash( $data['wishlist_token'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$product_id     = isset( $data['product_id'] ) ? intval( $data['product_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$new_quantity   = isset( $data['quantity'] ) ? intval( $data['quantity'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
			$cart_item_key  = isset( $data['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $data['cart_item_key'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$fragments      = isset( $data['fragments'] ) ? wp_unslash( $data['fragments'] ) : false;

			if ( ! $wishlist_token || ! $product_id ) {
				die();
			}

			$wishlist = wlfmc_get_wishlist( $wishlist_token );

			if ( ! $wishlist || ! $wishlist->current_user_can( 'update_quantity' ) ) {
				die();
			}

			$item = $wishlist->get_product( $cart_item_key ? $cart_item_key : $product_id );

			if ( ! $item ) {
				die();
			}

			do_action( 'wlfmc_before_item_quantity_update', $cart_item_key, $new_quantity, $item->get_quantity(), $item );

			$item->set_quantity( $new_quantity );
			$item->save();

			return rest_ensure_response( array( 'fragments' => WLFMC_Ajax_Handler::refresh_fragments( $fragments ) ) );
		}

		/**
		 * Generated fragments to replace in the page
		 *
		 * @param array $data $_POST data.
		 *
		 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
		 */
		public static function load_fragments( array $data ) {
			$fragment_json = isset( $_FILES['fragments_file']['tmp_name'] ) && is_uploaded_file( $_FILES['fragments_file']['tmp_name'] ) ? file_get_contents( $_FILES['fragments_file']['tmp_name'] ) : false; // phpcs:ignore
			$fragments     = $fragment_json ? json_decode( $fragment_json, true ) : false;

			if ( defined( 'ICL_SITEPRESS_VERSION' ) ) { // wpml current  language.
				global $sitepress;
				$lang = $sitepress->get_current_language();
			} elseif ( function_exists( 'pll_current_language' ) ) { // polylang current language.
				$lang = pll_current_language();
			} else { // cannot determine current language.
				$lang = null;
			}

			return rest_ensure_response(
				apply_filters(
					'wlfmc_load_fragments',
					array(
						'fragments' => WLFMC_Ajax_Handler::refresh_fragments( $fragments ),
						'products'  => WLFMC_Ajax_Handler::get_current_items(),
						'lang'      => $lang,
					)
				)
			);
		}


	}

}

add_action( 'rest_api_init', 'wlfmc_api_init' );

/**
 * Run API routes
 *
 * @return void
 */
function wlfmc_api_init() {

	$controller = new WLFMC_Api();
	$controller->register_routes();

}




