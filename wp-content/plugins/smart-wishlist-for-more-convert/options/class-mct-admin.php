<?php
/**
 * Admin Class
 *
 * @author MoreConvert
 * @package MoreConvert Options plugin
 * @version 2.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MCT_Admin' ) ) {
	/**
	 * This class handles admin for options plugin
	 */
	class MCT_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var MCT_Admin
		 */
		protected static $instance;


		/**
		 * Plugin Version
		 *
		 * @var string
		 */
		public $version = '2.5.4';

		/**
		 * Options
		 *
		 * @var array
		 */
		private $options;

		/**
		 * MCT_Admin constructor.
		 *
		 * @param array $args all options.
		 *
		 * @return void
		 *
		 * @version 2.0.0
		 */
		public function __construct( array $args = array() ) {
			if ( is_array( $args ) && ! empty( $args ) ) {
				$this->options = $args;
			}

			add_action( 'admin_init', array( $this, 'save_option' ) );
			add_action( 'admin_init', array( $this, 'reset_option' ) );

		}

		/**
		 * Init
		 *
		 * @return void
		 */
		public function init() {
			add_filter( 'admin_body_class', array( $this, 'add_admin_body_classes' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_js' ) );
		}

		/**
		 * Wizard init
		 *
		 * @return void
		 */
		public function wizard_init() {
			add_filter( 'admin_body_class', array( $this, 'add_wizard_body_classes' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_js' ) );
			add_action( 'admin_head', array( $this, 'add_inline_style_wizard_page' ) );
		}


		/**
		 * Returns single instance of the class
		 *
		 * @return MCT_Admin
		 */
		public static function get_instance(): MCT_Admin {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Enqueue admin style and js
		 *
		 * @version 1.1.0
		 */
		public function enqueue_admin_js() {
			if ( is_admin() ) {
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				// Add the color picker css file.
				wp_enqueue_style( 'wp-color-picker' );

				// Include WordPress Color Picker script.
				wp_enqueue_script( 'wp-color-picker' );

				wp_dequeue_script( 'wp-color-picker-alpha' );
				wp_register_script( 'wp-color-picker-alpha-v3', MCT_OPTION_PLUGIN_URL . '/assets/js/wp-color-picker-alpha.js', array( 'wp-color-picker' ), $this->version, true );
				wp_enqueue_script( 'wp-color-picker-alpha-v3' );

				// Include WordPress Media Uploader.
				wp_enqueue_media();

				if ( function_exists( 'WC' ) ) {
					/* Register select2 stylesheet */
					if ( ! wp_style_is( 'select2', 'registered' ) ) {

						wp_register_style( 'select2', WC()->plugin_url() . '/assets/css/select2.css', array(), WC()->version );
					}
					/* Register select2 script */
					if ( ! wp_script_is( 'wc-enhanced-select', 'registered' ) ) {
						if ( ! wp_script_is( 'selectWoo', 'registered' ) ) {
							wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), WC()->version, true );
						}
						wp_register_script(
							'wc-enhanced-select',
							WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js',
							array(
								'jquery',
								'selectWoo',
							),
							WC()->version,
							true
						);
						wp_localize_script(
							'wc-enhanced-select',
							'wc_enhanced_select_params',
							array(
								'i18n_no_matches'         => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
								'i18n_ajax_error'         => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
								'i18n_input_too_short_1'  => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
								'i18n_input_too_short_n'  => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
								'i18n_input_too_long_1'   => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
								'i18n_input_too_long_n'   => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
								'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
								'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
								'i18n_load_more'          => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
								'i18n_searching'          => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
								'ajax_url'                => admin_url( 'admin-ajax.php' ),
								'search_products_nonce'   => wp_create_nonce( 'search-products' ),
								'search_customers_nonce'  => wp_create_nonce( 'search-customers' ),
								'search_categories_nonce' => wp_create_nonce( 'search-categories' ),
							)
						);
					}

					wp_enqueue_script( 'selectWoo' );

					wp_enqueue_script( 'wc-enhanced-select' );

				} else {
					if ( ! wp_style_is( 'select2', 'registered' ) ) {

						wp_register_style( 'select2', MCT_OPTION_PLUGIN_URL . '/assets/lib/css/select2.min.css', array(), $this->version );
					}
					if ( ! wp_script_is( 'select2', 'registered' ) ) {

						wp_enqueue_script( 'select2', MCT_OPTION_PLUGIN_URL . '/assets/lib/js/select2.min.js', array( 'jquery' ), $this->version, true );

					}

					wp_enqueue_script( 'select2' );
				}

				wp_enqueue_style( 'select2' );

				wp_register_style( 'mct-ace-editor', MCT_OPTION_PLUGIN_URL . '/assets/lib/css/ace.css', array(), $this->version );

				wp_register_script( 'mct-ace-editor', MCT_OPTION_PLUGIN_URL . '/assets/lib/js/ace.js', array(), $this->version, true );

				wp_enqueue_script( 'mct-repeater', MCT_OPTION_PLUGIN_URL . '/assets/js/repeater' . $suffix . '.js', array( 'jquery' ), $this->version, true );

				wp_register_script( 'mct-admin', MCT_OPTION_PLUGIN_URL . '/assets/js/option-scripts' . $suffix . '.js', array( 'jquery' ), $this->version, true );

				wp_register_script( 'mct-moment', MCT_OPTION_PLUGIN_URL . '/assets/lib/js/moment.min.js', array( 'jquery' ), $this->version, true );
				wp_register_script(
					'mct-daterangepicker',
					MCT_OPTION_PLUGIN_URL . '/assets/lib/js/daterangepicker.min.js',
					array(
						'jquery',
						'mct-moment',
					),
					$this->version,
					true
				);

				wp_register_style( 'mct-daterangepicker', MCT_OPTION_PLUGIN_URL . '/assets/lib/css/daterangepicker.min.css', array(), $this->version );

				wp_localize_script(
					'mct-admin',
					'mct_admin_params',
					array(
						'ajax_url'                     => admin_url( 'admin-ajax.php' ),
						'search_post_url'              => esc_url_raw( rest_url( 'mct-options/v1/search-posts' ) ),
						'ajax_nonce'                   => wp_create_nonce( 'ajax-nonce' ),
						'i18n_delete_image_confirm'    => esc_html__( 'Are you sure?', 'mct-options' ),
						'i18n_delete_file_confirm'     => esc_html__( 'Are you sure?', 'mct-options' ),
						'i18n_limit_repeater_alert'    => esc_html__( 'You can not add more items.', 'mct-options' ),
						'i18n_delete_repeater_confirm' => esc_html__( 'Are you sure you want to delete this element?', 'mct-options' ),
						'i18n_reset_confirm'           => esc_html__( 'Are you sure you want to reset all the settings of this section?', 'mct-options' ),
						'range_datepicker'             => apply_filters(
							'mct_range_datepicker_labels',
							array(
								'applyLabel'       => __( 'Apply', 'mct-options' ),
								'cancelLabel'      => __( 'Clear', 'mct-options' ),
								'customRangeLabel' => __( 'Custom', 'mct-options' ),
								'last_7_days'      => __( 'Last 7 days', 'mct-options' ),
								'last_30_days'     => __( 'Last 30 days', 'mct-options' ),
								'last_90_days'     => __( 'Last 90 days', 'mct-options' ),
								'last_365_days'    => __( 'Last 365 days', 'mct-options' ),
							)
						),
					)
				);

				wp_enqueue_script( 'mct-admin' );

				wp_enqueue_style( 'mct-admin', MCT_OPTION_PLUGIN_URL . '/assets/css/option-styles' . $suffix . '.css', array(), $this->version );
			}
		}


		/**
		 *
		 * Adds body classes to the main wp-admin wrapper, allowing us to better target elements in specific scenarios.
		 *
		 * @param string $admin_body_class admin body class.
		 *
		 * @return string
		 *
		 * @since 2.0.0
		 */
		public static function add_admin_body_classes( string $admin_body_class = '' ): string {

			$classes   = explode( ' ', trim( $admin_body_class ) );
			$classes[] = 'mct-option-page';

			$admin_body_class = implode( ' ', array_unique( $classes ) );

			return " $admin_body_class ";
		}

		/**
		 *
		 * Adds body classes to the main wp-admin wrapper, allowing us to better target elements in specific scenarios.
		 *
		 * @param string $admin_body_class admin body class.
		 *
		 * @return string
		 *
		 * @since 2.0.0
		 */
		public static function add_wizard_body_classes( string $admin_body_class = '' ): string {

			$classes   = explode( ' ', trim( $admin_body_class ) );
			$classes[] = 'mct-option-page';
			$classes[] = 'mct-wizard-page';

			$admin_body_class = implode( ' ', array_unique( $classes ) );

			return " $admin_body_class ";
		}

		/**
		 * Add inline css in wizard page
		 *
		 * @since 2.0.0
		 */
		public function add_inline_style_wizard_page() {
			echo '<style>
				    html.wp-toolbar {
				      padding-top: 0 !important;
				    }
				  </style>';
		}

		/**
		 * Save options
		 *
		 * @return void
		 * @version 2.0.0
		 */
		public function save_option() {
			if ( isset( $_POST['mct-action'] ) ) {
				if ( isset( $this->options['options'] ) && is_array( $this->options['options'] ) ) {

					$options       = $this->get_main_key_options();
					$saved_options = $this->get_options();

					foreach ( $this->options['options'] as $section => $items ) {

						if ( $_POST['mct-action'] === $section && isset( $_POST[ 'mct-' . $section . '-nonce' ] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ 'mct-' . $section . '-nonce' ] ) ), 'mct-' . $section ) ) {

							$new_options = array();

							do_action( 'mct_panel_before_' . $this->options['id'] . '_update' );
							foreach ( $options[ $section ] as $value ) {

								$new_options[ $value ] = isset( $_POST[ $value ] ) ? wp_unslash( $_POST[ $value ] ) : ''; // phpcs:ignore WordPress.Security

							}
							$validate = apply_filters( 'mct_options_validate', true, $this->options['id'], $new_options );

							if ( true === $validate ) {
								$saved_options[ $section ] = $new_options;
								if ( apply_filters( 'mct_options_can_update', true, $this->options['id'], $new_options ) ) {
									update_option( $this->options['id'], $saved_options );
									do_action( 'mct_panel_after_' . $this->options['id'] . '_update' );
								} else {
									$validate = 'not-access';
								}
							}

							$state = true === $validate ? 'saved' : $validate;

							$url = remove_query_arg( 'reset' );

							$url = apply_filters(
								'mct_panel_redirect_' . $this->options['id'],
								add_query_arg(
									array(
										'page' => isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '',
										$state => 1,
									),
									$url
								)
							);

							header( 'Location: ' . $url );
							exit;
						}
					}
				}
			}
			if ( isset( $_POST['mct-action-wizard'] ) ) {
				if ( $_POST['mct-action-wizard'] === $this->options['id'] && isset( $_POST[ 'mct-' . $this->options['id'] . '-wizard-nonce' ] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ 'mct-' . $this->options['id'] . '-wizard-nonce' ] ) ), 'mct-' . $this->options['id'] . '-wizard' ) ) {

					$saved_options  = $this->get_options();
					$wizard_options = isset( $_POST['mct-form-options'] ) ? json_decode( wp_unslash( $_POST['mct-form-options'] ) ) : array(); // phpcs:ignore WordPress.Security
					if ( ! empty( $wizard_options ) ) {
						foreach ( $wizard_options as $section => $items ) {
							if ( ! empty( $items ) ) {
								foreach ( $items as $value ) {
									$saved_options[ $section ][ $value ] = isset( $_POST[ $value ] ) ? wp_unslash( $_POST[ $value ] ) : ''; // phpcs:ignore WordPress.Security

								}
							}
						}
					}
					$validate = apply_filters( 'mct_wizard_validate', true, $this->options['id'], $saved_options );

					if ( true === $validate ) {

						if ( apply_filters( 'mct_options_can_update', true, $this->options['id'], $saved_options ) ) {
							$saved_options = apply_filters( 'mct_wizard_' . $this->options['id'] . '_value', $saved_options );
							update_option( $this->options['id'], $saved_options );
							do_action( 'mct_panel_after_' . $this->options['id'] . '_wizard_update', $saved_options );
						}
					}

					$url = apply_filters(
						'mct_wizard_redirect_' . $this->options['id'],
						add_query_arg(
							array(
								'step' => isset( $_GET['step'] ) ? sanitize_key( wp_unslash( $_GET['step'] ) ) : '',
							)
						)
					);

					header( 'Location: ' . $url );
					exit;
				}
			}

		}

		/**
		 * Reset options
		 *
		 * @return void
		 * @version 2.4.6
		 * @since 2.0.0
		 */
		public function reset_option() {

			if ( isset( $_POST['mct-reset'] ) ) {
				if ( isset( $this->options['options'] ) && is_array( $this->options['options'] ) ) {

					$can_reset     = false;
					$default_value = $this->get_default_values();
					$saved_options = $this->get_options();
					foreach ( $this->options['options'] as $section => $items ) {
						if ( $_POST['mct-reset'] === $section && isset( $_POST[ 'mct-' . $section . '-nonce' ] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ 'mct-' . $section . '-nonce' ] ) ), 'mct-' . $section ) ) {
							$can_reset = true;
							if ( isset( $saved_options[ $section ] ) ) {
								$saved_options[ $section ] = $default_value[ $section ] ?? $default_value;
							} else {
								$saved_options = $default_value;
							}
						}
					}

					if ( $can_reset ) {

						$can_reset = apply_filters( 'mct_options_can_reset', true, $this->options['id'] );
						$url       = remove_query_arg( array( 'saved', 'tab' ) );

						if ( $can_reset ) {
							update_option( $this->options['id'], $saved_options );
							$url = apply_filters(
								'mct_panel_redirect_' . $this->options['id'],
								add_query_arg(
									array(
										'page'  => isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '',
										'reset' => 1,
									),
									$url
								)
							);
						} else {
							$url = apply_filters(
								'mct_panel_redirect_' . $this->options['id'],
								add_query_arg(
									array(
										'page'       => isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '',
										'not-access' => 1,
									),
									$url
								)
							);
						}

						header( 'Location: ' . $url );
						exit;
					}
				}
			}
		}

		/**
		 * Set default value to options
		 *
		 * @param string $key option key.
		 * @version 2.4.6
		 * @since 2.0.0
		 */
		public function set_default_options( $key = false ) {
			$default_value = $this->get_default_values();
			if ( apply_filters( 'mct_options_can_set_default_values', true, $this->options['id'] ) ) {
				if ( $key ) {
					$options = get_option( $this->options['id'], array() );
					if ( empty( $options ) ) {
						$options = $default_value;
					} else {
						$options[ $key ] = $default_value[ $key ] ?? $default_value;
					}
					update_option( $this->options['id'], $options );
				} else {
					update_option( $this->options['id'], $default_value );
				}
			}
		}

		/**
		 * Move array of options between parent option keys
		 *
		 * @param string $from from key.
		 * @param string $to to key.
		 * @param array  $options array of options.
		 *
		 * @since 2.4.5
		 * @return void
		 */
		public function move_options( $from, $to, $options ) {
			$current_options = get_option( $this->options['id'], array() );
			$changed         = false;
			foreach ( $options as $option ) {
				if ( isset( $current_options[ $from ][ $option ] ) ) {
					if ( ! isset( $current_options[ $to ][ $option ] ) ) {
						$current_options[ $to ][ $option ] = $current_options[ $from ][ $option ];
					}
					unset( $current_options[ $from ][ $option ] );
					$changed = true;
				}
			}
			if ( $changed ) {
				update_option( $this->options['id'], $current_options );
			}
		}

		/**
		 * Get main array options
		 * return an array with all key options
		 *
		 * @return array
		 * @version 1.1.0
		 */
		public function get_main_key_options(): array {
			$all_fields = array();
			if ( is_array( $this->options['options'] ) ) {
				foreach ( $this->options['options'] as $section => $value ) {
					$section_fields = array();
					if ( isset( $value['tabs'] ) ) {
						foreach ( $value['tabs'] as $tab => $fields ) {
							foreach ( $this->options['options'][ $section ]['fields'][ $tab ] as $k => $v ) {
								if ( isset( $v['type'] ) && ! in_array( $v['type'], array( 'end', 'separator', 'start', 'iframe', 'column-start', 'column-end', 'columns-start', 'columns-end' ), true ) ) {
									if ( 'group-fields' === $v['type'] ) {
										foreach ( $v['fields'] as $fk => $fv ) {
											$section_fields[] = $fk;
										}
									} else {
										$section_fields[] = $k;
									}
								}
							}
						}
					} else {
						foreach ( $value['fields'] as $k => $v ) {
							if ( isset( $v['type'] ) && ! in_array( $v['type'], array( 'end', 'separator', 'start', 'iframe', 'column-start', 'column-end', 'columns-start', 'columns-end' ), true ) ) {
								if ( 'group-fields' === $v['type'] ) {
									foreach ( $v['fields'] as $fk => $fv ) {
										$section_fields[] = $fk;
									}
								} else {
									$section_fields[] = $k;
								}
							}
						}
					}
					$all_fields[ $section ] = $section_fields;
				}
			}

			return $all_fields;
		}

		/**
		 * Get option from DB
		 *
		 * @return mixed
		 * @version 2.1.1
		 */
		public function get_options() {
			return apply_filters( 'mct_get_option', get_option( $this->options['id'], array() ), $this->options['id'] );
		}


		/**
		 * Get default option value
		 *
		 * @return array
		 * @since 2.0.0
		 */
		public function get_default_values(): array {
			$all_fields = array();
			if ( isset( $this->options['options'] ) && is_array( $this->options['options'] ) ) {
				foreach ( $this->options['options'] as $section => $value ) {
					$section_fields = array();
					if ( isset( $value['tabs'] ) ) {
						foreach ( $value['tabs'] as $tab => $fields ) {
							foreach ( $this->options['options'][ $section ]['fields'][ $tab ] as $k => $v ) {

								if ( isset( $v['type'] ) && ! in_array( $v['type'], array( 'end', 'separator', 'start', 'iframe', 'column-start', 'column-end', 'columns-start', 'columns-end' ), true ) ) {
									if ( 'group-fields' === $v['type'] ) {
										foreach ( $v['fields'] as $fk => $fv ) {
											$section_fields[ $fk ] = $fv['default'] ?? '';
										}
									} else {
										$section_fields[ $k ] = $v['default'] ?? '';
									}
								}
							}
						}
					} else {
						foreach ( $value['fields'] as $k => $v ) {
							if ( isset( $v['type'] ) && ! in_array( $v['type'], array( 'end', 'separator', 'start', 'iframe', 'column-start', 'column-end', 'columns-start', 'columns-end' ), true ) ) {
								if ( 'group-fields' === $v['type'] ) {
									foreach ( $v['fields'] as $fk => $fv ) {
										$section_fields[ $fk ] = $fv['default'] ?? '';
									}
								} else {
									$section_fields[ $k ] = $v['default'] ?? '';
								}
							}
						}
					}
					$all_fields[ $section ] = $section_fields;
				}
			}

			return $all_fields;
		}


	}
}

