<?php
/**
 * Static class that will handle all ajax calls for the list
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'MCT_Ajax_Handler' ) ) {
	/**
	 * Woocommerce Smart Wishlist Ajax Handler
	 */
	class MCT_Ajax_Handler {
		/**
		 * Performs all required add_actions to handle forms
		 *
		 * @return void
		 * @version 1.3.3
		 */
		public static function init() {

			// add to wishlist.
			add_action( 'wp_ajax_mct_import_settings', array( 'MCT_Ajax_Handler', 'import_settings' ) );
			add_action( 'wp_ajax_mct_export_settings', array( 'MCT_Ajax_Handler', 'export_settings' ) );
			add_action( 'wp_ajax_mct_ajax_saving', array( 'MCT_Ajax_Handler', 'ajax_saving' ) );
			add_action( 'rest_api_init', array( 'MCT_Ajax_Handler', 'register_routes' ) );

			add_filter( 'upload_mimes', array( 'MCT_Ajax_Handler', 'add_json_mimetype' ) );
		}

		/**
		 * Import settings
		 *
		 * @return void
		 */
		public static function import_settings() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to access this page.', 'mct-options' ) ) );
			}

			check_ajax_referer( 'ajax-nonce', 'key' );
			$option_id     = isset( $_POST['option_id'] ) ? sanitize_text_field( wp_unslash( $_POST['option_id'] ) ) : false;
			$attachment_id = isset( $_POST['attachment_id'] ) ? absint( wp_unslash( $_POST['attachment_id'] ) ) : false;

			if ( ! $option_id || ! $attachment_id ) {
				wp_send_json_error();
			}
			$options = new MCT_Options( $option_id );

			$file_path    = get_attached_file( $attachment_id );
			$file_content = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions

			$json = json_decode( $file_content, true );
			if ( ! is_array( $json ) || empty( $json ) ) {
				// Handle error if the content is not a valid JSON object.
				wp_send_json_error( array( 'message' => __( 'Invalid JSON content', 'mct-options' ) ) );
			}

			if ( ! isset( $json['options'] ) || ! isset( $json['option_id'] ) || $option_id !== $json['option_id'] ) {
				wp_send_json_error( array( 'message' => __( 'Wrong JSON content', 'mct-options' ) ) );
			}
			$options->replace_options( $json['options'] );

			wp_send_json_success( array( 'message' => __( 'Successfully Imported.', 'mct-options' ) ) );
		}

		/**
		 * Export settings
		 *
		 * @return void
		 */
		public static function export_settings() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to access this page.', 'mct-options' ) ) );
			}

			check_ajax_referer( 'ajax-nonce', 'key' );
			$option_id = isset( $_POST['option_id'] ) ? sanitize_text_field( wp_unslash( $_POST['option_id'] ) ) : false;
			if ( ! $option_id ) {
				wp_send_json_error();
			}
			$options = new MCT_Options( $option_id );

			$name = get_bloginfo( 'name' );

			// WordPress can have a blank site title, which will cause initial client creation to fail.
			if ( empty( $name ) ) {
				$name = wp_parse_url( home_url(), PHP_URL_HOST );
				$port = wp_parse_url( home_url(), PHP_URL_PORT );
				if ( $port ) {
					$name .= ':' . $port;
				}
			}

			$name = preg_replace( '/[^A-Za-z0-9 ]/', '', $name ?? '' );
			$name = preg_replace( '/\s+/', ' ', $name ?? '' );
			$name = str_replace( ' ', '-', $name );

			wp_send_json_success(
				array(
					'message'     => __( 'Successfully Exported.', 'mct-options' ),
					'filecontent' => wp_json_encode( $options ),
					'filename'    => "$name-$option_id.json",
				)
			);
		}

		/**
		 * Ajax saving settings
		 *
		 * @return void
		 */
		public static function ajax_saving() {
			check_ajax_referer( 'ajax-nonce', '_wpnonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to access this page.', 'mct-options' ) ) );
			}

			if ( ! isset( $_POST['data'] ) ) {
				wp_send_json_error( array( 'message' => __( 'No data was received.', 'mct-options' ) ) );
			}

			parse_str( wp_unslash( urldecode( $_POST['data'] ) ), $data );  // phpcs:ignore WordPress.Security

			$option_id = isset( $data['mct-option_id'] ) ? sanitize_text_field( wp_unslash( $data['mct-option_id'] ) ) : false;
			if ( ! $option_id ) {
				wp_send_json_error( array( 'message' => __( 'Missing option ID.', 'mct-options' ) ) );
			}
			$saved_options = apply_filters( 'mct_get_option', get_option( $option_id, array() ), $option_id );
			$options       = isset( $data['mct-form-options'] ) ? json_decode( wp_unslash( $data['mct-form-options'] ) ) : array(); // phpcs:ignore WordPress.Security
			if ( ! empty( $options ) ) {
				foreach ( $options as $section => $items ) {
					if ( ! empty( $items ) ) {
						foreach ( $items as $value ) {
							$saved_options[ $section ][ $value ] = isset( $data[ $value ] ) ? wp_unslash( $data[ $value ] ) : ''; // phpcs:ignore WordPress.Security

						}
					}
				}
			}
			$validate = apply_filters( 'mct_ajax_validate', true, $option_id, $saved_options );

			if ( true === $validate ) {

				if ( apply_filters( 'mct_options_can_update', true, $option_id, $saved_options ) ) {
					update_option( $option_id, $saved_options );
					do_action( 'mct_panel_after_' . $option_id . '_ajax_update', $saved_options );
					wp_send_json_success(
						array(
							'message' => __( 'Successfully Saved.', 'mct-options' ),
						)
					);
				} else {
					wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to access this page.', 'mct-options' ) ) );
				}
			} else {
				wp_send_json_error( array( 'message' => __( 'Failed validate form.', 'mct-options' ) ) );
			}

		}

		/**
		 * Add search post routes
		 *
		 * @return void
		 */
		public static function register_routes() {
			register_rest_route(
				'mct-options/v1',
				'/search-posts',
				array(
					'methods'             => 'GET',
					'callback'            => array( 'MCT_Ajax_Handler', 'search_posts' ),
					'permission_callback' => '__return_true',
				)
			);
		}

		/**
		 * Search posts
		 *
		 * @param WP_REST_Request $request  The request object.
		 *
		 * @return array
		 */
		public static function search_posts( $request ) {
			$search_term = isset( $request['search_term'] ) ? sanitize_text_field( $request['search_term'] ) : '';

			$args = array(
				'post_type'      => 'any',
				'posts_per_page' => -1,
				's'              => $search_term,
			);

			$query   = new WP_Query( $args );
			$results = array();

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$results[] = array(
						'id'   => get_the_ID(),
						'text' => get_the_title(),
					);
				}
			}

			wp_reset_postdata();

			return $results;
		}

		/**
		 * Add json mimetype
		 *
		 * @param array $mimes mimetypes.
		 *
		 * @return array
		 */
		public static function add_json_mimetype( $mimes ) {
			// Add support for JSON files.
			$mimes['json'] = 'application/json';

			return $mimes;
		}
	}
}
MCT_Ajax_Handler::init();
