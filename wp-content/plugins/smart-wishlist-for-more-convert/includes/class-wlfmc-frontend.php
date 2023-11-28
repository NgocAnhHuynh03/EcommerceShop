<?php
/**
 * Smart Wishlist Frontend
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.6.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Frontend' ) ) {
	/**
	 * This class handles frontend for wishlist plugin
	 */
	class WLFMC_Frontend {

		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Frontend
		 */
		protected static $instance;

		/**
		 * Endpoint.
		 *
		 * @var false|mixed|void
		 */
		public $endpoint;
		/**
		 * User page.
		 *
		 * @var false|mixed|void
		 */
		public $user_page;
		/**
		 * Page id.
		 *
		 * @var int
		 */
		public $page_id;
		/**
		 * Page title.
		 *
		 * @var string
		 */
		public $page_title;
		/**
		 * Endpoint.
		 *
		 * @var false|mixed|void
		 */
		public $list_type;
		/**
		 * Constructor
		 *
		 * @return void
		 * @version 1.6.6
		 */
		public function __construct() {

			// init widget.
			add_action( 'widgets_init', array( $this, 'register_widget' ) );

			// init class.
			add_action( 'init', array( $this, 'init' ), 0 );

			// scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_and_stuffs' ) );

			// templates.
			add_action( 'template_redirect', array( $this, 'add_nocache_headers' ) );
			add_filter( 'wp_robots', array( $this, 'add_noindex_robots' ) );

			add_action( 'init', array( $this, 'add_button' ) );
			add_filter( 'body_class', array( $this, 'add_body_class' ) );

			// wishlist view.
			add_action( 'wlfmc_main_wishlist_content', array( $this, 'main_wishlist_content' ), 10, 1 );

			// dashboard page.
			$options               = new MCT_Options( 'wlfmc_options' );
			$enable_myaccount_link = $options->get_option( 'wishlist_enable_myaccount_link', false );
			$wishlist_page_id      = get_option( 'wlfmc_wishlist_page_id' );
			$wishlist_page_id      = wlfmc_object_id( $wishlist_page_id );
			$wishlist_page_id      = (int) apply_filters( 'wlfmc_wishlist_page_id', $wishlist_page_id );
			$args                  = apply_filters(
				'wlfmc_user_list_page_args',
				array(
					'user_page'  => $options->get_option( 'wishlist_user_page', 'myaccount-page' ),
					'endpoint'   => $options->get_option( 'wishlist_endpoint', 'wlfmc-wishlist' ),
					'custom_url' => $options->get_option( 'wishlist_custom_url', '' ),
					'page_id'    => $wishlist_page_id,
					'page_title' => $options->get_option( 'wishlist_page_title', esc_html__( 'Wishlist', 'wc-wlfmc-wishlist' ) ),
					'list_type'  => 'wishlist',
				),
				$options
			);

			$this->endpoint   = $args['endpoint'];
			$this->user_page  = $args['user_page'];
			$this->page_id    = $args['page_id'];
			$this->page_title = $args['page_title'];
			$this->list_type  = $args['list_type'];

			if ( 'myaccount-page' === $args['user_page'] && '' !== $args['endpoint'] ) {
				// Actions used to insert a new endpoint in the woocommerce.
				add_action( 'init', array( $this, 'add_endpoint' ) );

				if ( ! is_admin() ) {
					add_filter( 'woocommerce_get_query_vars', array( $this, 'add_query_vars' ), 10 );
					add_filter( 'woocommerce_account_menu_items', array( $this, 'dashboard_wishlist_link' ), 40 );
					if ( 'wishlist' === $this->list_type ) {
						add_action(
							'woocommerce_account_' . $this->endpoint . '_endpoint',
							array(
								$this,
								'wlfmc_wishlist_endpoint_content',
							)
						);
					}
				}
			} elseif ( ! is_admin() && 'quest-page' === $args['user_page'] && wlfmc_is_true( $enable_myaccount_link ) ) {
				$this->endpoint = 'wlfmc-wishlist';
				add_filter( 'woocommerce_get_endpoint_url', array( $this, 'account_menu_endpoint' ), 4, 10 );
				add_filter( 'woocommerce_account_menu_items', array( $this, 'dashboard_wishlist_link' ), 40 );
			}

			// Add wishlist counter to menu.
			if ( ! is_admin() ) {
				add_filter( 'wp_get_nav_menu_items', array( $this, 'prepare_menu' ), 9, 3 );
				add_filter( 'wp_nav_menu', array( $this, 'add_to_menu' ), 9, 2 );
				add_filter( 'wlfmc_counter_menu_link_class', array( $this, 'add_menu_item_class' ), 10, 3 );
			}

			add_filter( 'wlfmc_enabled_lists', array( $this, 'enable_wishlist' ), 0 );

			add_action(
				'woocommerce_single_product_summary',
				array(
					$this,
					'fix_outofstock_single_product_position',
				),
				31
			);

			// Display metaData in wishlist page for each item.
			add_action( 'wlfmc_item_meta_data', array( $this, 'item_meta_data' ), 10, 3 );
			add_filter( 'wlfmc_woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 10, 5 );

			// error when visiting private wishlists.
			add_action( 'template_redirect', array( $this, 'private_wishlist_404' ) );
		}

		/**
		 * Add wishlist name to a tab in wishlist page in pro version
		 *
		 * @param array $lists enabled lists.
		 *
		 * @return array
		 */
		public function enable_wishlist( $lists ) {
			$options                      = new MCT_Options( 'wlfmc_options' );
			$who_can_see_wishlist_options = $options->get_option( 'who_can_see_wishlist_options', 'all' );
			$disable_for_current_user     = 'users' === $who_can_see_wishlist_options && ! is_user_logged_in();
			if ( ! $disable_for_current_user && wlfmc_is_true( $options->get_option( 'wishlist_enable', '1' ) ) && wlfmc_is_true( $options->get_option( 'wishlist_tabbed_lists', '1' ) ) ) {
				$lists['wishlist'] = array(
					'title'       => $options->get_option( 'wishlist_title', __( 'Wishlist', 'wc-wlfmc-wishlist' ) ),
					'is_svg_icon' => false,
					'icon'        => apply_filters( 'wlfmc_wishlist_tab_icon_html', '<i class="wlfmc-icon-heart"></i>' ),
				);
			}
			return $lists;
		}

		/**
		 * Prepare menu to add  wishlist counter
		 *
		 * @param array  $items An array of menu item post objects.
		 * @param object $menu The menu object.
		 * @param array  $args An array of arguments used to retrieve menu item objects.
		 *
		 * @return array
		 *
		 * @since 1.3.0
		 * @version 1.6.6
		 */
		public function prepare_menu( $items, $menu, $args ) {
			$options  = new MCT_Options( 'wlfmc_options' );
			$menu_cnt = count( $items ) + 1;
			$menu_ids = $options->get_option( 'counter_add_to_menu', array() );
			if ( ! is_array( $menu_ids ) ) {
				$menu_ids = array( $menu_ids );
			}
			$menu_ids = array_filter( $menu_ids, 'absint' );
			if ( ! empty( $menu_ids ) ) {
				foreach ( $menu_ids as $menu_id ) {
					if ( '' !== $menu_id && intval( $menu_id ) === $menu->term_id && apply_filters( 'wlfmc_counter_add_to_menu', true, 'wishlist', $menu_id ) ) {
						$menu_order    = apply_filters( 'wlfmc_counter_menu_position', $options->get_option( 'counter_menu_position', '100' ), 'wishlist', $menu_id );
						$menu_title    = is_admin() ? __( 'MC Wishlist Counter', 'wc-wlfmc-wishlist' ) : 'wlfmc-wishlist';
						$wishlist_item = (object) array(
							'ID'                     => $menu_cnt + 1000000,
							'object_id'              => $menu_cnt + 1000000,
							'db_id'                  => $menu_cnt + 1000000,
							'title'                  => $menu_title,
							'post_title'             => $menu_title,
							'url'                    => '#' . $menu_title,
							'guid'                   => '#' . $menu_title,
							'post_date'              => gmdate( 'Y-m-d H:i:s' ),
							'post_date_gmt'          => gmdate( 'Y-m-d H:i:s' ),
							'post_modified'          => gmdate( 'Y-m-d H:i:s' ),
							'post_modified_gmt'      => gmdate( 'Y-m-d H:i:s' ),
							'menu_order'             => $menu_order,
							'menu_item_parent'       => 0,
							'type'                   => 'custom',
							'post_status'            => 'publish',
							'post_author'            => 1,
							'comment_status'         => 'closed',
							'ping_status'            => 'closed',
							'post_name'              => 'd',
							'post_parent'            => 0,
							'post_type'              => 'nav_menu_item',
							'comment_count'          => 0,
							'filter'                 => 'raw',
							'object'                 => 'custom',
							'type_label'             => '',
							'target'                 => '',
							'attr_title'             => '',
							'classes'                => array(),
							'post_content'           => '',
							'post_excerpt'           => '',
							'category_post'          => '',
							'nolink'                 => '',
							'description'            => '',
							'xfn'                    => '',
							'template'               => '',
							'mega_template'          => '',
							'megamenu'               => '',
							'megamenu_auto_width'    => '',
							'megamenu_col'           => '',
							'megamenu_heading'       => '',
							'megamenu_widgetarea'    => '',
							'megamenu_disable_link'  => '',
							'megamenu_disable_title' => '',
							'icon'                   => '',
							'post_password'          => '',
							'to_ping'                => '',
							'pinged'                 => '',
							'post_content_filtered'  => '',
							'post_mime_type'         => '',
							'popup_type'             => '',
							'popup_pos'              => '',
							'preview'                => '',
							'hide'                   => '',
							'tip_label'              => '',
							'block'                  => '',
							'recentpost'             => '',
							'mobile_hide'            => '',
						);

						foreach ( array_keys( $items ) as $key ) {

							if ( $items[ $key ]->menu_order > ( $menu_order - 1 ) ) {
								$items[ $key ]->menu_order = $items[ $key ]->menu_order + 1;
							}
						}
						if ( $menu_order < $menu_cnt ) {
							array_splice( $items, $menu_order - 1, 0, array( $wishlist_item ) );
						} else {
							$items[] = $wishlist_item;
						}
					}
				}
			}

			return $items;
		}

		/**
		 * Add wishlist counter to menu
		 *
		 * @param string   $nav_menu The HTML content for the navigation menu.
		 * @param stdClass $args An object containing wp_nav_menu() arguments.
		 *
		 * @return string
		 *
		 * @version 1.6.6
		 */
		public function add_to_menu( string $nav_menu, stdClass $args ): string {
			if ( strpos( $nav_menu, 'href="#wlfmc-wishlist"' ) !== false || strpos( $nav_menu, "href='#wlfmc-wishlist'" ) !== false ) {
				ob_start();
				$shortcode_atts   = apply_filters( 'wlfmc_counter_menu_shortcode_atts', ( wp_is_mobile() ? " show_products='false' dropdown_products='false' add_link_title='true'" : '' ), 'wishlist', $args );
				$link_class       = apply_filters( 'wlfmc_counter_menu_link_class', '', 'wishlist', $args );
				$container_class  = apply_filters( 'wlfmc_counter_menu_container_class', '', 'wishlist', $args );
				$container_class .= ' wlfmc-products-counter-wrapper ';
				echo do_shortcode( "[wlfmc_wishlist_counter $shortcode_atts container_class='$container_class' link_class='$link_class' ]" );
				$content = ob_get_clean();
				if ( class_exists( 'DOMDocument' ) ) {
					$use_mb = function_exists( 'mb_convert_encoding' );
					$dom    = new DOMDocument();
					libxml_use_internal_errors( true );
					if ( $use_mb ) {
						$nav_menu = mb_convert_encoding( $nav_menu, 'HTML-ENTITIES', 'UTF-8' );
					}
					$dom->loadHTML( $nav_menu );
					$links  = $dom->getElementsByTagName( 'a' );
					$length = $links->length;

					for ( $i = $length - 1; $i > - 1; $i -- ) {
						$link = $links->item( $i );

						if ( '#wlfmc-wishlist' === $link->getAttribute( 'href' ) ) {
							// phpcs:disable WordPress.NamingConventions.ValidVariableName
							$parent            = $link->parentNode;
							$parent->nodeValue = '';
							$tmpDoc            = new DOMDocument();
							if ( $use_mb && '' !== $content ) {
								$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
							}
							if ( '' !== $content ) {
								$tmpDoc->loadHTML( $content );

								foreach ( $tmpDoc->getElementsByTagName( 'body' )->item( 0 )->childNodes as $node ) {
									$node = $dom->importNode( $node, true );
									$parent->appendChild( $node );

								}
							}
							// phpcs:enable WordPress.NamingConventions.ValidVariableName
							break;

						}
					}
					// We have a fully developed HTML document, but we only want the menu itself.
					$full_html = $dom->saveHTML();
					$start     = strpos( $full_html, '<body>' ) + 6;
					$length    = strpos( $full_html, '</body>' ) - $start;
					$nav_menu  = substr(
						$full_html,
						$start,
						$length
					);

				} else {
					preg_match( '/<a(.*?)href="#wlfmc-wishlist"(.*?)>wlfmc-wishlist<\/a>/', $nav_menu, $matches );
					if ( ! empty( $matches ) && '' !== $matches[0] ) {
						$nav_menu = str_replace( $matches[0], $content, $nav_menu );
					}
				}
			}

			return $nav_menu;
		}

		/**
		 * Add menu item class for counters
		 *
		 * @param string   $class menu item class.
		 * @param string   $list_type list type.
		 * @param stdClass $menu_args An object containing wp_nav_menu() arguments.
		 * @return string
		 *
		 * @since 1.6.6
		 */
		public function add_menu_item_class( $class, $list_type, $menu_args ) {
			if ( isset( $menu_args->menu_class ) ) {
				switch ( $menu_args->menu_class ) {
					case 'hfe-nav-menu':
						return 'hfe-menu-item';
				}
			}
			return $class;
		}

		/**
		 * Initiator method.
		 *
		 * @return void
		 * @throws Exception Exception.
		 */
		public function init() {
			// update cookie from old version to new one.
			$this->update_cookies();
			$this->destroy_serialized_cookies();
			$this->convert_cookies_to_session();

			// register assets.
			$this->register_styles_and_stuffs();
		}

		/**
		 * Add wishlist link to woocommerce dashboard.
		 *
		 * @param Array $menu_links array of menu links.
		 *
		 * @return array
		 */
		public function dashboard_wishlist_link( $menu_links ) {
			$index_position = apply_filters( 'wlfmc_myaccount_position_wishlist', 1, $menu_links );

			return array_slice( $menu_links, 0, $index_position, true ) + array( $this->endpoint => $this->page_title ) + array_slice( $menu_links, $index_position, null, true );

		}

		/**
		 * Create end point for wishlist url
		 *
		 * @param string $url URL from wishlist.
		 * @param string $endpoint End point name.
		 * @param string $value Not used.
		 * @param string $permalink Not used.
		 *
		 * @return string
		 */
		public function account_menu_endpoint( $url, $endpoint, $value, $permalink ): string {
			if ( $this->endpoint === $endpoint ) {
				$page = wlfmc_object_id( $this->page_id ); // @codingStandardsIgnoreLine WordPress.Variables.GlobalVariables.OverrideProhibited
				if ( empty( $page ) ) {
					return '';
				}
				return get_permalink( $page );
			}

			return $url;
		}

		/**
		 * Add wishlist dashboard endpoint
		 *
		 * @return void
		 */
		public function add_endpoint() {

			add_rewrite_endpoint( $this->endpoint, EP_ROOT | EP_PAGES );

			// Flush the rewrite rules if any endpoints were added or removed.
			if ( get_option( 'wlfmc_' . $this->list_type . '_endpoint_previous' ) !== $this->endpoint ) {
				flush_rewrite_rules();
				update_option( 'wlfmc_' . $this->list_type . '_endpoint_previous', $this->endpoint );
			}

		}


		/**
		 * Add new query var.
		 *
		 * @param array $vars Query vars.
		 *
		 * @return array Query vars
		 * @since 2.1.1
		 */
		public function add_query_vars( $vars ) {
			$vars[ $this->endpoint ] = $this->endpoint;
			return $vars;
		}


		/**
		 * Show wishlist in woocommerce dashboard
		 *
		 * @return void
		 */
		public function wlfmc_wishlist_endpoint_content() {

			echo do_shortcode( '[wlfmc_wishlist]' );
		}

		/**
		 * Add the "Add to Wishlist" button. Needed to use in wp_head hook.
		 *
		 * @return void
		 * @version 1.3.0
		 */
		public function add_button() {
			$options = new MCT_Options( 'wlfmc_options' );

            if ( ! wlfmc_is_true( $options->get_option( 'wishlist_enable', '0' ) ) ) {
				return;
			}

			$who_can_see_wishlist_options = $options->get_option( 'who_can_see_wishlist_options', 'all' );

			if ( ( 'users' === $who_can_see_wishlist_options && ! is_user_logged_in() ) ) {
				return;
			}

			$positions = apply_filters(
				'wlfmc_button_positions',
				array(
					'before_add_to_cart'        => array(
						array(
							'hook'     => 'woocommerce_before_add_to_cart_form',
							'priority' => 29,
						),
						array(
							'hook'     => 'wlfmc_single_product_summary',
							'priority' => 1,
						),

					),
					'after_add_to_cart'         => array(
						array(
							'hook'     => 'woocommerce_after_add_to_cart_form',
							'priority' => 31,
						),
						array(
							'hook'     => 'wlfmc_single_product_summary',
							'priority' => 1,
						),

					),
					'before_add_to_cart_button' => array(
						array(
							'hook'     => 'woocommerce_before_add_to_cart_button',
							'priority' => 90,
						),
						array(
							'hook'     => 'wlfmc_single_product_summary',
							'priority' => 1,
						),

					),
					'after_add_to_cart_button'  => array(
						array(
							'hook'     => 'woocommerce_after_add_to_cart_button',
							'priority' => 20,
						),
						array(
							'hook'     => 'wlfmc_single_product_summary',
							'priority' => 1,
						),

					),

					'image_top_left'            => array(
						'hook'     => 'woocommerce_product_thumbnails',
						'priority' => 21,
					),
					'image_top_right'           => array(
						'hook'     => 'woocommerce_product_thumbnails',
						'priority' => 21,
					),
					'image_bottom_left'         => array(
						'hook'     => 'woocommerce_product_thumbnails',
						'priority' => 21,
					),
					'image_bottom_right'        => array(
						'hook'     => 'woocommerce_product_thumbnails',
						'priority' => 21,
					),
					'summary'                   => array(
						'hook'     => 'woocommerce_after_single_product_summary',
						'priority' => 11,
					),
					'flex_image_top_left'       => array(
						'hook'     => 'wlfmc_single_flex_image_top_left',
						'priority' => 10,
					),
					'flex_image_top_right'      => array(
						'hook'     => 'wlfmc_single_flex_image_top_right',
						'priority' => 10,
					),
					'flex_image_bottom_left'    => array(
						'hook'     => 'wlfmc_single_flex_image_bottom_left',
						'priority' => 10,
					),
					'flex_image_bottom_right'   => array(
						'hook'     => 'wlfmc_single_flex_image_bottom_right',
						'priority' => 10,
					),
				)
			);
			if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
				// add box position for multiple list item on the image.
				foreach ( $positions as $position => $hook ) {
					if ( in_array( $position, array( 'image_top_left', 'image_top_right', 'image_bottom_left', 'image_bottom_right' ), true ) ) {
						if ( isset( $hook['hook'] ) && isset( $hook['priority'] ) ) {
							add_action( $hook['hook'], array( $this, 'wlfmc_single_flex_' . $position ), $hook['priority'] );
						} elseif ( ! empty( $hook ) ) {
							foreach ( $hook as $v ) {
								if ( isset( $v['hook'] ) && isset( $v['priority'] ) ) {
									add_action( $v['hook'], array( $this, 'wlfmc_single_flex_' . $position ), $v['priority'] );
								}
							}
							break;
						}
					}
				}
			}
			// Add the link "Add to wishlist".
			$position = $options->get_option( 'wishlist_button_position', 'image_top_left' );

			if ( 'shortcode' !== $position && isset( $positions[ $position ] ) ) {

				if ( isset( $positions[ $position ]['hook'] ) ) {
					add_action(
						$positions[ $position ]['hook'],
						array(
							$this,
							'print_button_single',
						),
						floatval( $positions[ $position ]['priority'] )
					);
				} elseif ( ! empty( $positions[ $position ] ) ) {
					foreach ( $positions[ $position ] as $hook ) {
						add_action(
							$hook['hook'],
							array(
								$this,
								'print_button_single',
							),
							floatval( $hook['priority'] )
						);
					}
				}
			}

			// check if Add to wishlist button is enabled for loop.
			$enabled_on_loop = wlfmc_is_true( $options->get_option( 'show_on_loop', true ) );

			if ( ! $enabled_on_loop ) {
				return;
			}

			$positions = apply_filters(
				'wlfmc_loop_positions',
				array(

					'image_top_left'       => array(
						'hook'     => 'woocommerce_before_shop_loop_item',
						'priority' => 5,
					),
					'image_top_right'      => array(
						'hook'     => 'woocommerce_before_shop_loop_item',
						'priority' => 5,
					),
					'before_add_to_cart'   => array(
						'hook'     => 'woocommerce_after_shop_loop_item',
						'priority' => 7,
					),
					'after_add_to_cart'    => array(
						'hook'     => 'woocommerce_after_shop_loop_item',
						'priority' => 15,
					),
					'flex_image_top_left'  => array(
						'hook'     => 'wlfmc_loop_flex_image_top_left',
						'priority' => 10,
					),
					'flex_image_top_right' => array(
						'hook'     => 'wlfmc_loop_flex_image_top_right',
						'priority' => 10,
					),
				)
			);
			if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
				// add box position for multiple list item on the image.
				foreach ( $positions as $position => $hook ) {
					if ( in_array( $position, array( 'image_top_left', 'image_top_right' ), true ) ) {
						if ( isset( $hook['hook'] ) && isset( $hook['priority'] ) ) {
							add_action( $hook['hook'], array( $this, 'wlfmc_loop_flex_' . $position ), $hook['priority'] );
						} elseif ( ! empty( $hook ) ) {
							foreach ( $hook as $v ) {
								if ( isset( $v['hook'] ) && isset( $v['priority'] ) ) {
									add_action( $v['hook'], array( $this, 'wlfmc_loop_flex_' . $position ), $v['priority'] );
								}
							}
							break;
						}
					}
				}
			}
			// Add the link "Add to wishlist".
			$position = $options->get_option( 'loop_position', 'after_add_to_cart' );

			if ( 'shortcode' !== $position && isset( $positions[ $position ] ) ) {
				if ( isset( $positions[ $position ]['hook'] ) ) {
					add_action(
						$positions[ $position ]['hook'],
						array(
							$this,
							'print_button_loop',
						),
						floatval( $positions[ $position ]['priority'] )
					);
				} elseif ( ! empty( $positions[ $position ] ) ) {
					foreach ( $positions[ $position ] as $hook ) {
						add_action(
							$hook['hook'],
							array(
								$this,
								'print_button_loop',
							),
							floatval( $hook['priority'] )
						);
					}
				}
			}
		}

		/**
		 * Print Flex on image position
		 *
		 * @return void
		 * @since 1.7.0
		 */
		public function wlfmc_single_flex_image_top_left() {
			if ( has_action( 'wlfmc_single_flex_image_top_left' ) ) {
				?>
				<div class="wlfmc-flex-on-image wlfmc-top-of-image image_top_left wlfmc_position_image_top_left">
					<?php do_action( 'wlfmc_single_flex_image_top_left' ); ?>
				</div>
				<?php
			}
		}

		/**
		 * Print Flex on image position
		 *
		 * @return void
		 * @since 1.7.0
		 */
		public function wlfmc_single_flex_image_top_right() {
			if ( has_action( 'wlfmc_single_flex_image_top_right' ) ) {
				?>
				<div class="wlfmc-flex-on-image wlfmc-top-of-image image_top_right wlfmc_position_image_top_right">
					<?php do_action( 'wlfmc_single_flex_image_top_right' ); ?>
				</div>
				<?php
			}
		}

		/**
		 * Print Flex on image position
		 *
		 * @return void
		 * @since 1.7.0
		 */
		public function wlfmc_single_flex_image_bottom_left() {
			if ( has_action( 'wlfmc_single_flex_image_bottom_left' ) ) {
				?>
				<div class="wlfmc-flex-on-image wlfmc-top-of-image image_bottom_left wlfmc_position_image_bottom_left">
					<?php do_action( 'wlfmc_single_flex_image_bottom_left' ); ?>
				</div>
				<?php
			}
		}

		/**
		 * Print Flex on image position
		 *
		 * @return void
		 * @since 1.7.0
		 */
		public function wlfmc_single_flex_image_bottom_right() {
			if ( has_action( 'wlfmc_single_flex_image_bottom_right' ) ) {
				?>
				<div class="wlfmc-flex-on-image wlfmc-top-of-image image_bottom_right wlfmc_position_image_bottom_right">
					<?php do_action( 'wlfmc_single_flex_image_bottom_right' ); ?>
				</div>
				<?php
			}
		}

		/**
		 * Print Flex on image position
		 *
		 * @return void
		 * @since 1.7.0
		 */
		public function wlfmc_loop_flex_image_top_left() {
			if ( has_action( 'wlfmc_loop_flex_image_top_left' ) ) {
				?>
				<div class="wlfmc-flex-on-image wlfmc-top-of-image image_top_left wlfmc_position_image_top_left">
					<?php do_action( 'wlfmc_loop_flex_image_top_left' ); ?>
				</div>
				<?php
			}
		}

		/**
		 * Print Flex on image position
		 *
		 * @return void
		 * @since 1.7.0
		 */
		public function wlfmc_loop_flex_image_top_right() {
			if ( has_action( 'wlfmc_loop_flex_image_top_right' ) ) {
				?>
				<div class="wlfmc-flex-on-image wlfmc-top-of-image image_top_right wlfmc_position_image_top_right">
					<?php do_action( 'wlfmc_loop_flex_image_top_right' ); ?>
				</div>
				<?php
			}
		}

		/**
		 * Add custom hook for fix outofstock simple product
		 *
		 * @return void
		 * @since 1.3.3
		 */
		public function fix_outofstock_single_product_position() {
			do_action( 'wlfmc_single_product_summary' );
		}

		/**
		 * Alter add to cart button when on wishlist page
		 *
		 * @return void
		 */
		public function alter_add_to_cart_button() {
			add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'alter_add_to_cart_url' ), 10, 2 );
			remove_filter( 'woocommerce_loop_add_to_cart_link', 'zn_woocommerce_loop_add_to_cart_link', 10 );
			remove_filter( 'woocommerce_loop_add_to_cart_link', 'wlfmc_shopengine_print_button_in_shop_after', 10 );
			remove_filter( 'woocommerce_loop_add_to_cart_link', 'wlfmc_shopengine_print_button_in_shop_before', 10 );
		}

		/**
		 * Restore default Add to Cart button, after wishlist handling
		 *
		 * @return void
		 */
		public function restore_add_to_cart_button() {
			remove_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'alter_add_to_cart_url' ) );
			if ( function_exists( 'zn_woocommerce_loop_add_to_cart_link' ) ) {
				add_filter( 'woocommerce_loop_add_to_cart_link', 'zn_woocommerce_loop_add_to_cart_link', 10, 3 );
			}
			if ( class_exists( 'ShopEngine' ) ) {
				add_filter( 'woocommerce_loop_add_to_cart_link', 'wlfmc_shopengine_print_button_in_shop', 10, 2 );
			}
		}

		/**
		 * Display metaData in wishlist page for each item
		 *
		 * @param array|null     $meta Product meta.
		 * @param Array          $cart_item of other cart item data.
		 * @param WLFMC_Wishlist $wishlist Current wishlist.
		 *
		 * @return void
		 */
		public function item_meta_data( $meta, $cart_item, $wishlist ) {

			if ( ! empty( $meta ) ) {
				echo '<ul class="wlfmc-table-item-meta-data">';
				foreach ( $meta as $meta_row_container ) {
					if ( empty( $meta_row_container ) || ! is_array( $meta_row_container ) ) {
						continue;
					}
					foreach ( $meta_row_container as $meta_row ) {
						if ( empty( $meta_row ) || ! is_array( $meta_row ) || ! isset( $meta_row['product_id'] ) ) {
							continue;
						}
						$product = wc_get_product( isset( $meta_row['variation_id'] ) && '' !== $meta_row['variation_id'] ? $meta_row['variation_id'] : $meta_row['product_id'] );
						if ( ! $product || ( isset( $meta_row['quantity'] ) && ! floatval( $meta_row['quantity'] ) > 0 ) ) {
							continue;
						}

						$cart_item_row = array(
							'product_id'   => $meta_row['product_id'],
							'variation_id' => isset( $meta_row['variation_id'] ) && '' !== $meta_row['variation_id'] ? $meta_row['variation_id'] : '',
							'variation'    => $meta_row['attributes'] ?? '',
							'quantity'     => $meta_row['quantity'],
							'data'         => $product,
						);
						$cart_item_row = array_merge( $cart_item_row, $meta_row );
						echo '<li>';
						echo '<dl>' . esc_attr( $product->get_title() ) . ' <strong>&times; ' . esc_attr( $meta_row['quantity'] ) . '</strong></dl>';

						if ( $product->is_type( 'variation' ) ) {
							?>

							<?php
							// phpcs:ignore Generic.Commenting.DocComment
							/**
							 * @var $product WC_Product_Variation
							 */
							echo wc_get_formatted_variation( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

							?>
							<?php

						}
						do_action( 'wlfmc_item_meta_data', $meta_row, $cart_item_row, $wishlist );
						echo '</li>';
					}
				}
				remove_filter( 'woocommerce_get_item_data', array( 'WC_PB_BS_Display', 'bundle_sell_data' ), 10 );
				$cart_item['key'] = $cart_item['key'] ?? '';
				$meta_data        = wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.
				echo $meta_data ? '<li class="none-style">' . wp_kses_post( $meta_data ) . '</li>' : '';
				echo '</ul>';
			}
		}

		/**
		 * Check add to cart validation
		 *
		 * @param bool                $passed validation passed or not.
		 * @param object              $product Product.
		 * @param array|null          $meta product meta.
		 * @param WLFMC_Wishlist_Item $item Wishlist item object.
		 * @param array               $cart_item extra cart item data we want to pass into the item.
		 *
		 * @return mixed|void
		 */
		public function add_to_cart_validation( $passed, $product, $meta, $item, $cart_item ) {

			try {
				$product_id = $item->get_product_id();
				$quantity   = $item->get_quantity();
				$attributes = array();
				if ( isset( $meta['attributes'] ) && ! empty( $meta['attributes'] ) ) {
					foreach ( $meta['attributes'] as $key => $value ) {
						if ( '' !== $value ) {
							$attributes[ $key ] = $value;
						}
					}
				}
				$variation_id = 0;
				if ( $product && 'variation' === $product->get_type() ) {
					$variation_id = $product_id;
					$product_id   = $product->get_parent_id();
				}
				// phpcs:disable WordPress.Security.NonceVerification
				$post    = $_POST;
				$request = $_REQUEST;
				$files   = $_FILES;

				$post_data = $item->get_posted_data( 'view' );
				$post_data = is_array( $post_data ) ? $post_data : array();
				$_POST     = ! empty( $post_data ) ? $post_data['post'] : array();
				$_REQUEST  = ! empty( $post_data ) ? $post_data['post'] : array();
				$_FILES    = ! empty( $post_data ) ? $post_data['files'] : array();

				do_action( 'wlfmc_before_add_to_cart_validation' );

				$passed = $product->is_purchasable() && ( $product->is_in_stock() || $product->backorders_allowed() ) && 'external' !== $product->get_type() && 'variable' !== $product->get_type();
				$passed = apply_filters( 'woocommerce_add_to_cart_validation', $passed, $product_id, $quantity, $variation_id, $attributes, $cart_item );
				$passed = apply_filters( 'wlfmc_add_to_cart_validation', $passed, $product_id, $quantity, $variation_id, $attributes, $cart_item, $item );

				do_action( 'wlfmc_after_add_to_cart_validation' );

				$_POST    = $post;
				$_FILES   = $files;
				$_REQUEST = $request;

			} catch ( Exception $e ) {
				return $passed;
			}

			// phpcs:enable WordPress.Security.NonceVerification
			return $passed;
		}

		/**
		 * Add product to cart
		 *
		 * @param int                 $product_id product id.
		 * @param int                 $quantity product quantity.
		 * @param int                 $variation_id variation id.
		 * @param array               $attributes product attributes.
		 * @param array               $cart_item cart item.
		 * @param WLFMC_Wishlist_Item $item Wishlist item object.
		 *
		 * @return bool|string
		 * @throws Exception Exception.
		 */
		public function add_to_cart( $product_id, $quantity, $variation_id, $attributes, $cart_item, $item ) {

			$post    = $_POST; // phpcs:ignore WordPress.Security.NonceVerification
			$files   = $_FILES; // phpcs:ignore WordPress.Security.NonceVerification
			$request = $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification

			$post_data = $item->get_posted_data( 'view' );
			$post_data = is_array( $post_data ) ? $post_data : array();
			$_POST     = ! empty( $post_data ) ? $post_data['post'] : array();
			$_FILES    = ! empty( $post_data ) ? $post_data['files'] : array();
			$_REQUEST  = ! empty( $post_data ) ? $post_data['post'] : array();

			do_action( 'wlfmc_before_add_to_cart' );

			if ( ( empty( $_POST ) && empty( $_FILES ) && empty( $_REQUEST ) ) || ( empty( $this->array_diff_recursive( $_POST, $attributes ) ) ) && ( empty( $this->array_diff_recursive( $attributes, $_POST ) ) ) ) {  // phpcs:ignore WordPress.Security.NonceVerification
				$cart_item = array();
			}

			$result = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $attributes, $cart_item );

			do_action( 'wlfmc_after_add_to_cart' );

			$_POST    = $post;
			$_FILES   = $files;
			$_REQUEST = $request;

			return $result;
		}

		/**
		 * Recursively compares two arrays and returns the differences.
		 *
		 * @param array $array1 The first array to compare.
		 * @param array $array2 The second array to compare.
		 * @return array The differences between the arrays.
		 */
		public function array_diff_recursive( $array1, $array2 ) {
			$diff = array();
			foreach ( $array1 as $key => $value ) {
				if ( is_array( $value ) ) {
					if ( ! isset( $array2[ $key ] ) || ! is_array( $array2[ $key ] ) ) {
						$diff[ $key ] = $value;
					} else {
						$recursive_diff = $this->array_diff_recursive( $value, $array2[ $key ] );
						if ( ! empty( $recursive_diff ) ) {
							$diff[ $key ] = $recursive_diff;
						}
					}
				} elseif ( ! in_array( $value, $array2, true ) ) {
					$diff[ $key ] = $value;
				}
			}
			return $diff;
		}

		/**
		 * Filter Add to Cart button url on wishlist page
		 *
		 * @param string     $url Url to the Add to Cart.
		 * @param WC_Product $product Current product.
		 *
		 * @return string Filtered url
		 * @version 1.3.1
		 */
		public function alter_add_to_cart_url( $url, $product ) {
			global $wlfmc_wishlist_token;
			if ( $wlfmc_wishlist_token ) {
				$wishlist = wlfmc_get_wishlist( $wlfmc_wishlist_token );

				if ( ! $wishlist ) {
					return $url;
				}

				$wishlist_id = $wishlist->get_id();
				$item        = $wishlist->get_product( $product->get_id() );
				$options     = new MCT_Options( 'wlfmc_options' );

				if ( $product->is_type( array( 'simple', 'variation' ) ) && wlfmc_is_true( $options->get_option( 'redirect_after_add_to_cart', true ) ) && $product->is_purchasable() && $product->is_in_stock() ) {
					$url = add_query_arg( 'add-to-cart', $product->get_id(), wc_get_cart_url() );
				}

				if ( ! $product->is_type( 'external' ) && ! $product->is_type( 'variable' ) && 'added-to-cart' === $options->get_option( 'remove_from_wishlist', 'none' ) ) {
					$url = add_query_arg(
						array(
							'remove_from_wishlist_after_add_to_cart' => $product->get_id(),
							'wishlist_id'    => $wishlist_id,
							'wishlist_token' => $wlfmc_wishlist_token,
						),
						$url
					);
				}

				if ( $item && true === apply_filters( 'wlfmc_quantity_show', true ) ) {
					$url = add_query_arg( 'quantity', $item->get_quantity(), $url );
				}
			}

			return apply_filters( 'wlfmc_add_to_cart_redirect_url', esc_url_raw( $url ), $url, $product );
		}

		/**
		 * Print "Add to Wishlist" shortcode in single page
		 *
		 * @return void
		 * @since 1.3.2
		 */
		public function print_button_single() {
			global $product;
			/**
			 * Developers can use this filter to remove ATW button selectively from specific pages or products
			 * You can use global $product or $post to execute checks
			 */
			if ( ! apply_filters( 'wlfmc_show_add_to_wishlist', true ) ) {
				return;
			}

			if ( $product && current_filter() === 'wlfmc_single_product_summary' && ( apply_filters( 'wlfmc_hide_on_button_disabled_in_single', 'simple' !== $product->get_type() || $product->is_in_stock(), $product ) ) ) {
				return;
			}

			echo do_shortcode( '[wlfmc_add_to_wishlist is_single="true"]' );
		}

		/**
		 * Print "Add to Wishlist" shortcode in loop
		 *
		 * @return void
		 * @since 1.3.2
		 */
		public function print_button_loop() {
			/**
			 * Developers can use this filter to remove ATW button selectively from specific pages or products
			 * You can use global $product or $post to execute checks
			 */
			if ( ! apply_filters( 'wlfmc_show_add_to_wishlist', true ) ) {
				return;
			}

			echo do_shortcode( '[wlfmc_add_to_wishlist is_single=""]' );
		}

		/**
		 * Include main wishlist template
		 *
		 * @param array $var Array of variables to pass to the template.
		 *
		 * @var $var array Array of parameters for current view
		 * @return void
		 */
		public function main_wishlist_content( $var ) {
			wlfmc_get_template( 'mc-wishlist-view.php', $var );
		}

		/**
		 * Include wishlist header
		 *
		 * @param WLFMC_Wishlist $wishlist wishlist.
		 * @param array          $var Array of variables to pass to the template.
		 *
		 * @return void
		 */
		public function main_wishlist_header( $wishlist, $var ) {
			wlfmc_get_template( 'mc-wishlist-header.php', $var );
		}

		/**
		 * Add specific body class when the Wishlist page is opened
		 *
		 * @param array $classes Existing boy classes.
		 *
		 * @return array
		 */
		public function add_body_class( $classes ) {

			if ( ( wlfmc_is_wishlist_page() || wlfmc_is_multi_list_page() || wlfmc_is_waitlist_page() || wlfmc_is_tabbed_page() ) ) {
				$classes[] = 'wlfmc-wishlist';
				$classes[] = 'woocommerce';
				$classes[] = 'woocommerce-page';
			}

			return $classes;
		}

		/**
		 * Send nocache headers on wishlist page
		 *
		 * @return void
		 */
		public function add_nocache_headers() {
			if ( ! headers_sent() && ( wlfmc_is_wishlist_page() || wlfmc_is_multi_list_page() || wlfmc_is_waitlist_page() || wlfmc_is_tabbed_page() ) ) {
				wc_nocache_headers();
			}
		}

		/**
		 * Disable search engines indexing for Add to Wishlist url and shared wishlist urls.
		 * Uses "wp_robots" filter introduced in WP 5.7.
		 *
		 * @param array $robots Associative array of robots directives.
		 *
		 * @return array Filtered robots directives.
		 */
		public function add_noindex_robots( $robots ) {
			if ( ( ! isset( $_GET['add_to_wishlist'] ) && ! isset( $_GET['wishlist-action'] ) ) || apply_filters( 'wlfmc_skip_noindex_headers', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return $robots;
			}

			return wp_robots_no_robots( $robots );
		}


		/**
		 * Register scripts and styles required by the plugin
		 *
		 * @return void
		 * @version 1.6.8
		 */
		public function register_styles_and_stuffs() {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$deps   = apply_filters( 'wlfmc_main_script_deps', array( 'jquery', 'hoverIntent', 'wp-util' ) );

			wp_register_style( 'wlfmc-main', MC_WLFMC_URL . 'assets/frontend/css/style' . $suffix . '.css', null, WLFMC_VERSION );

			wp_register_script( 'toastr', MC_WLFMC_URL . 'assets/frontend/js/toastr' . $suffix . '.js', array( 'jquery' ), WLFMC_VERSION, true );

			wp_register_script( 'jquery-popupoverlay', MC_WLFMC_URL . 'assets/frontend/js/jquery.popupoverlay' . $suffix . '.js', array( 'jquery' ), WLFMC_VERSION, true );

			wp_register_script( 'wlfmc-main', MC_WLFMC_URL . 'assets/frontend/js/scripts' . $suffix . '.js', $deps, WLFMC_VERSION, true );

		}

		/**
		 * Enqueue styles, scripts and other stuffs needed in the <head>.
		 *
		 * @return void
		 */
		public function enqueue_styles_and_stuffs() {

			// main plugin style.
			wp_enqueue_style( 'wlfmc-main' );

			// custom style.
			$custom_css = $this->build_custom_css();

			if ( $custom_css ) {

				wp_add_inline_style( 'wlfmc-main', $custom_css );
			}

		}

		/**
		 * Enqueue plugin scripts.
		 *
		 * @return void
		 */
		public function enqueue_scripts() {

			wp_localize_script( 'wlfmc-main', 'wlfmc_l10n', $this->get_localize() );

			wp_enqueue_script( 'toastr' );

			wp_enqueue_script( 'jquery-popupoverlay' );

			wp_enqueue_script( 'wlfmc-main' );

		}

		/**
		 * Return localize array
		 *
		 * @return array Array with variables to be localized inside js
		 *
		 * @version 1.3.3
		 */
		public function get_localize() {
			$options    = new MCT_Options( 'wlfmc_options' );
			$ajax_mode  = $options->get_option( 'ajax_mode', 'admin-ajax.php' );
			$login_need = $options->get_option( 'login_need_text', __( 'to use your Wishlist: <br><a href="{login_url}">Login right now</a>', 'wc-wlfmc-wishlist' ) );

			$login_need       = str_replace(
				array(
					'{login_url}',
					'{signup_url}',
				),
				array(
					esc_url( $options->get_option( 'login_url', wp_login_url(), true ) ),
					esc_url( $options->get_option( 'signup_url', wp_registration_url(), true ) ),
				),
				$login_need
			);
			$lang             = null;
			$wishlist_items   = array();
			$is_cache_enabled = wlfmc_is_true( $options->get_option( 'is_cache_enabled', '1' ) );
			if ( ! $is_cache_enabled ) {
				if ( defined( 'ICL_SITEPRESS_VERSION' ) ) { // wpml current  language.
					global $sitepress;
					$lang = $sitepress->get_current_language();
				} elseif ( function_exists( 'pll_current_language' ) ) { // polylang current language.
					$lang = pll_current_language();
				}
				$wishlist_items = WLFMC_Ajax_Handler::get_current_items();
			}
			return apply_filters(
				'wlfmc_localize_script',
				array(
					'ajax_url'              => 'admin-ajax.php' === $ajax_mode ? admin_url( 'admin-ajax.php', 'relative' ) : ( 'rest_api' === $ajax_mode ? esc_url_raw( rest_url( 'wlfmc/v1/call' ) ) : add_query_arg( array() ) ),
					'wp_loaded_url'         => add_query_arg( array() ),
					'admin_url'             => admin_url( 'admin-ajax.php', 'relative' ),
					'root'                  => esc_url_raw( rest_url( 'wlfmc/v1/call' ) ),
					'nonce'                 => wp_create_nonce( 'wp_rest' ),
					'ajax_mode'             => $ajax_mode,
					'update_wishlists_data' => apply_filters( 'wlfmc_update_wishlists_data', false ),
					'wishlist_hash_key'     => apply_filters( 'wlfmc_hash_key', 'wc_wishlist_hash_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) ),
					'redirect_to_cart'      => $options->get_option( 'redirect_after_add_to_cart', true ),
					'is_cache_enabled'      => $is_cache_enabled,
					'lang'                  => $lang,
					'wishlist_items'        => $wishlist_items,
					'click_behavior'        => $options->get_option( 'click_wishlist_button_behavior', 'just-add' ),
					'multi_wishlist'        => false,
					'enable_ajax_loading'   => $options->get_option( 'ajax_loading', '1' ),
					'ajax_loader_url'       => MC_WLFMC_URL . 'assets/frontend/images/ajax-loader-alt.svg',
					'remove_from_wishlist_after_add_to_cart' => 'added-to-cart' === $options->get_option( 'remove_from_wishlist', 'none' ),
					'fragments_index_glue'  => apply_filters( 'wlfmc_fragments_index_glue', '.' ),
					'is_rtl'                => is_rtl(),
					'toast_position'        => 'default',
					'labels'                => array(
						'cookie_disabled'            => esc_html__( 'We are sorry, but this feature is available only if cookies on your browser are enabled.', 'wc-wlfmc-wishlist' ),
						'product_adding'             => esc_html__( 'Another product is being added to the list, please wait until it is finished.', 'wc-wlfmc-wishlist' ),
						'added_to_cart_message'      => apply_filters( 'wlfmc_added_to_cart_message', esc_html__( 'Product added to cart successfully', 'wc-wlfmc-wishlist' ) ),
						'failed_add_to_cart_message' => apply_filters( 'wlfmc_failed_add_to_cart_message', esc_html__( 'Product could not be added to the cart because some requirements are not met.', 'wc-wlfmc-wishlist' ) ),
						'login_need'                 => $login_need,
						'product_added_text'         => $options->get_option( 'product_added_text', '' ),
						'already_in_wishlist_text'   => $options->get_option( 'already_in_wishlist_text', '' ),
						'product_removed_text'       => $options->get_option( 'product_removed_text', '' ),
						'popup_title'                => $options->get_option( 'popup_title' ),
						'popup_content'              => $options->get_option( 'popup_content' ),
						'link_copied'                => apply_filters( 'wlfmc_the_link_was_copied_message', __( 'The link was copied to clipboard', 'wc-wlfmc-wishlist' ) ),
					),
					'actions'               => array(
						'add_to_cart_action'          => 'wlfmc_add_to_cart',
						'add_to_wishlist_action'      => 'admin-ajax.php' === $ajax_mode ? 'wlfmc_add_to_wishlist' : ( 'wp_loaded' === $ajax_mode ? 'wlfmc_wp_loaded_add_to_wishlist' : 'wlfmc_wp_rest_add_to_wishlist' ),
						'remove_from_wishlist_action' => 'admin-ajax.php' === $ajax_mode ? 'wlfmc_remove_from_wishlist' : ( 'wp_loaded' === $ajax_mode ? 'wlfmc_wp_loaded_remove_from_wishlist' : 'wlfmc_wp_rest_remove_from_wishlist' ),
						'delete_item_action'          => 'admin-ajax.php' === $ajax_mode ? 'wlfmc_delete_item' : ( 'wp_loaded' === $ajax_mode ? 'wlfmc_wp_loaded_delete_item' : 'wlfmc_wp_rest_delete_item' ),
						'load_fragments'              => 'admin-ajax.php' === $ajax_mode ? 'wlfmc_load_fragments' : ( 'wp_loaded' === $ajax_mode ? 'wlfmc_wp_loaded_load_fragments' : 'wlfmc_wp_rest_load_fragments' ),
						'load_automations'            => 'admin-ajax.php' === $ajax_mode ? 'wlfmc_load_automations' : ( 'wp_loaded' === $ajax_mode ? 'wlfmc_wp_loaded_load_automations' : 'wlfmc_wp_rest_load_automations' ),
						'update_item_quantity'        => 'admin-ajax.php' === $ajax_mode ? 'wlfmc_update_item_quantity' : ( 'wp_loaded' === $ajax_mode ? 'wlfmc_wp_loaded_update_item_quantity' : 'wlfmc_wp_rest_update_item_quantity' ),
						'change_layout'               => 'admin-ajax.php' === $ajax_mode ? 'wlfmc_change_layout' : ( 'wp_loaded' === $ajax_mode ? 'wlfmc_wp_loaded_change_layout' : 'wlfmc_wp_rest_change_layout' ),
					),
					'ajax_nonce'            => array(
						'change_layout_nonce'        => wp_create_nonce( 'change_layout' ),
						'add_to_cart_nonce'          => wp_create_nonce( 'add_to_cart_from_wishlist' ),
						'add_to_wishlist_nonce'      => wp_create_nonce( 'add_to_wishlist' ),
						'remove_from_wishlist_nonce' => wp_create_nonce( 'remove_from_wishlist' ),
						'delete_item_nonce'          => wp_create_nonce( 'delete_item' ),
						'update_item_quantity_nonce' => wp_create_nonce( 'update_item_quantity' ),
						'load_automations_nonce'     => wp_create_nonce( 'load_automations' ),
						'load_fragments_nonce'       => wp_create_nonce( 'load_fragments' ),
					),
				),
				$ajax_mode,
				$options
			);
		}

		/**
		 * Set 404 status when non-owner user tries to visit private wishlist
		 *
		 * @return void
		 * @since 1.5.3
		 */
		public function private_wishlist_404() {
			global $wp_query;

			if ( ! wlfmc_is_wishlist_page() ) {
				return;
			}

			$current_wishlist = WLFMC_Wishlist_Factory::get_current_wishlist();

			if ( ! $current_wishlist || $current_wishlist->current_user_can( 'view' ) ) {
				return;
			}

			// if we're trying to show private wishlist to non-owner user, return 404.
			$wp_query->set_404();
			status_header( 404 );
		}

		/* === WIDGETS === */

		/**
		 * Registers widget used to show wishlist counter
		 *
		 * @return void
		 * @since 1.3.0
		 */
		public function register_widget() {
			register_widget( 'WLFMC_Counter_Widget' );
		}


		/* === UTILS === */

		/**
		 * Generate CSS codes to append to each page, to apply custom style to wishlist elements
		 *
		 * @param array $rules Array of additional rules to add to default ones.
		 *
		 * @return string Generated CSS code
		 *
		 * @version 1.3.2
		 */
		protected function build_custom_css( $rules = array() ) {
			$generated_code = '';
			$rules          = apply_filters(
				'wlfmc_custom_css_rules',
				array_merge(
					array(
						'popup_buttons'                    => array(
							'selector' => '.wlfmc-wishlist-popup .wlfmc-popup-footer .wlfmc_btn_%d',
							'rules'    => array(
								'background'        => array(
									'rule'    => 'background-color: %1$s;',
									'default' => 'transparent',
								),
								'background-hover'  => array(
									'rule'    => 'background-color: %1$s; ',
									'default' => 'transparent',
									'status'  => ':hover',
								),
								'label-color'       => array(
									'rule'    => 'color: %s',
									'default' => 'transparent',
								),
								'label-hover-color' => array(
									'rule'    => 'color: %s',
									'default' => 'transparent',
									'status'  => ':hover',
								),
								'border-radius'     => array(
									'rule'    => 'border-radius: %s !important',
									'default' => '2px',
								),
							),
							'type'     => 'repeater',
						),
						'popup_background_color'           => array(
							'selector' => '.wlfmc-popup',
							'rules'    => array(
								'rule'    => 'background-color: %s',
								'default' => 'transparent',
							),
						),
						'popup_content_color'              => array(
							'selector' => '.wlfmc-popup .wlfmc-popup-content , .wlfmc-popup .wlfmc-popup-content label',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'popup_title_color'                => array(
							'selector' => '.wlfmc-popup .wlfmc-popup-title',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'popup_border_color'               => array(
							'selector' => '.wlfmc-popup',
							'rules'    => array(
								'rule'    => 'border-color: %s',
								'default' => 'transparent',
							),
						),
						'popup_icon_color'                 => array(
							'selector' => '.wlfmc-popup .wlfmc-popup-header-bordered i:not(.wlfmc-icon-close)',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'popup_icon_background_color'      => array(
							'selector' => '.wlfmc-popup .wlfmc-popup-header-bordered i:not(.wlfmc-icon-close)',
							'rules'    => array(
								'rule'    => 'background-color: %s',
								'default' => 'transparent',
							),
						),
						'popup_border_radius'              => array(
							'selector' => '.wlfmc-popup',
							'rules'    => array(
								'rule'    => 'border-radius: %s',
								'default' => '8px',
							),
						),
						'separator_color_single'           => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor)  .wlfmc-add-button > a.have-sep span',
							'rules'    => array(
								'rule'    => 'border-left-color: %s',
								'default' => 'transparent',
								'status'  => ':before',
							),
						),
						'tooltip_color'                    => array(
							'selector' => ':root',
							'rules'    => array(
								'rule'    => '--tooltip-color-custom: %s',
								'default' => 'transparent',
							),
						),
						'tooltip_background_color'         => array(
							'selector' => ':root',
							'rules'    => array(
								'rule'    => '--tooltip-bg-custom: %s',
								'default' => 'rgba(55, 64, 70, 0.9)',
							),
						),
						'tooltip_border_radius'            => array(
							'selector' => '.wlfmc-tooltip-custom',
							'rules'    => array(
								'rule'    => 'border-radius: %s !important',
								'default' => '6px',
							),
						),
						'icon_font_size_single'            => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor)  .wlfmc-add-button > a i',
							'rules'    => array(
								'rule'    => 'font-size: %s',
								'default' => '14px',
							),
						),
						'icon_color_single'                => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor)  .wlfmc-add-button > a i',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'icon_hover_color_single'          => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor)  .wlfmc-add-button > a:hover i',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'text_font_size_single'            => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor)  .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'font-size: %s',
								'default' => '14px',
							),
						),
						'text_color_single'                => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor)  .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'text_hover_color_single'          => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor)  .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
								'status'  => ':hover',
							),
						),
						'button_background_color_single'   => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'background-color: %s',
								'default' => 'transparent',
							),
						),
						'button_background_hover_color_single' => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'background-color: %s',
								'default' => 'transparent',
								'status'  => ':hover',
							),
						),
						'button_border_radius_single'      => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'border-radius: %s',
								'default' => '0',
							),
						),
						'button_border_width_single'       => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'border-width: %s',
								'default' => '0',
							),
						),
						'button_border_color_single'       => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'border-color: %s',
								'default' => 'transparent',
							),
						),
						'button_border_hover_color_single' => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'border-color: %s',
								'default' => 'transparent',
								'status'  => ':hover',
							),
						),
						'button_margin_single'             => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor)',
							'rules'    => array(
								'rule'    => 'margin: %s !important',
								'default' => '0',
							),
						),
						'button_width_single'              => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'width: %s !important',
								'default' => 'auto',
							),
						),
						'button_height_single'             => array(
							'selector' => '.wlfmc-single-btn:not(.is-elementor)  .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'height: %s',
								'default' => 'auto',
							),
						),

						'separator_color_loop'             => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor)  .wlfmc-add-button > a.have-sep span',
							'rules'    => array(
								'rule'    => 'border-left-color: %s',
								'default' => 'transparent',
								'status'  => ':before',
							),
						),
						'icon_font_size_loop'              => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor)  .wlfmc-add-button > a i',
							'rules'    => array(
								'rule'    => 'font-size: %s',
								'default' => '14px',
							),

						),
						'icon_color_loop'                  => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor)  .wlfmc-add-button > a i',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),

						),
						'icon_hover_color_loop'            => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor)  .wlfmc-add-button > a:hover i',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),

						),
						'text_font_size_loop'              => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor)  .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'font-size: %s',
								'default' => '14px',
							),
						),
						'text_color_loop'                  => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor)  .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'text_hover_color_loop'            => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor)  .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
								'status'  => ':hover',
							),
						),
						'button_height_loop'               => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'height: %s',
								'default' => 'auto',
							),
						),
						'button_border_radius_loop'        => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor)  .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'border-radius: %s',
								'default' => '0',
							),
						),
						'button_border_width_loop'         => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'border-width: %s',
								'default' => '0',
							),
						),
						'button_background_color_loop'     => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'background-color: %s',
								'default' => 'transparent',
							),
						),
						'button_background_hover_color_loop' => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'background-color: %s',
								'default' => 'transparent',
								'status'  => ':hover',
							),
						),
						'button_border_color_loop'         => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'border-color: %s',
								'default' => 'transparent',
							),
						),
						'button_border_hover_color_loop'   => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'border-color: %s',
								'default' => 'transparent',
								'status'  => ':hover',
							),
						),
						'button_width_loop'                => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor) .wlfmc-add-button > a',
							'rules'    => array(
								'rule'    => 'width: %s',
								'default' => '45px',
							),
						),
						'button_margin_loop'               => array(
							'selector' => '.wlfmc-loop-btn:not(.is-elementor)',
							'rules'    => array(
								'rule'    => 'margin: %s !important',
								'default' => '0',
							),
						),
						'login_notice_background_color'    => array(
							'selector' => '.wlfmc-guest-notice-wrapper',
							'rules'    => array(
								'rule'    => 'background-color: %s',
								'default' => 'transparent',
							),
						),
						'login_notice_buttons'             => array(
							'selector' => '.wlfmc-guest-notice-wrapper .wlfmc-notice-buttons a.wlfmc_btn_%d',
							'rules'    => array(
								'background'        => array(
									'rule'    => 'background-color: %1$s !important; ',
									'default' => 'transparent',
								),
								'background-hover'  => array(
									'rule'    => 'background-color: %1$s  !important; ',
									'default' => 'transparent',
									'status'  => ':hover',
								),
								'label-color'       => array(
									'rule'    => 'color: %s  !important',
									'default' => 'transparent',
								),
								'label-hover-color' => array(
									'rule'    => 'color: %s  !important',
									'default' => 'transparent',
									'status'  => ':hover',
								),
								'border-radius'     => array(
									'rule'    => 'border-radius: %s',
									'default' => '2px',
								),
							),
							'type'     => 'repeater',
						),
						'wishlist_button_border_radius'    => array(
							'selector' => '.wlfmc-default-table-header:not(.is-elementor) .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .apply-btn,.wlfmc-default-table:not(.is-elementor).add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),.wlfmc-default-table:not(.is-elementor)  .wlfmc-wishlist-footer .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer button[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer input[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer select, .wlfmc-default-table:not(.is-elementor).qty-same-button input.qty,.wlfmc-default-table:not(.is-elementor).qty-same-button .quantity .button',
							'rules'    => array(
								'rule'    => 'border-radius: %s !important',
								'default' => '16px',
							),
						),
						'wishlist_button_border_width'     => array(
							'selector' => '.wlfmc-default-table-header:not(.is-elementor) .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .apply-btn,.wlfmc-default-table:not(.is-elementor).add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),.wlfmc-default-table:not(.is-elementor)  .wlfmc-wishlist-footer .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer button[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer input[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer select, .wlfmc-default-table:not(.is-elementor).qty-same-button input.qty,.wlfmc-default-table:not(.is-elementor).qty-same-button .quantity .button',
							'rules'    => array(
								'rule'    => 'border-width: %s !important;border-style:solid;',
								'default' => '0',
							),
						),
						'wishlist_button_background_color' => array(
							'selector' => '.wlfmc-default-table-header:not(.is-elementor) .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .apply-btn,.wlfmc-default-table:not(.is-elementor).add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer button[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer input[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer select, .wlfmc-default-table:not(.is-elementor).qty-same-button input.qty,.wlfmc-default-table:not(.is-elementor).qty-same-button .quantity .button',
							'rules'    => array(
								'rule'    => 'background-color: %s !important',
								'default' => 'transparent',
							),
						),
						'wishlist_button_background_hover_color' => array(
							'selector' => '.wlfmc-default-table-header:not(.is-elementor) .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .apply-btn,.wlfmc-default-table:not(.is-elementor).add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer button[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer input[type="submit"]',
							'rules'    => array(
								'rule'    => 'background-color: %s !important',
								'default' => 'transparent',
								'status'  => ':hover',
							),
						),
						'wishlist_button_border_color'     => array(
							'selector' => '.wlfmc-default-table-header:not(.is-elementor) .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .apply-btn,.wlfmc-default-table:not(.is-elementor).add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer button[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer input[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer select, .wlfmc-default-table:not(.is-elementor).qty-same-button input.qty ,.wlfmc-default-table:not(.is-elementor).qty-same-button .quantity .button',
							'rules'    => array(
								'rule'    => 'border-color: %s !important',
								'default' => 'transparent',
							),
						),
						'wishlist_button_border_hover_color' => array(
							'selector' => '.wlfmc-default-table-header:not(.is-elementor) .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .apply-btn,.wlfmc-default-table:not(.is-elementor).add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer button[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer input[type="submit"]',
							'rules'    => array(
								'rule'    => 'border-color: %s !important',
								'default' => 'transparent',
								'status'  => ':hover',
							),
						),
						'wishlist_button_color'            => array(
							'selector' => '.wlfmc-default-table-header:not(.is-elementor) .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .apply-btn,.wlfmc-default-table:not(.is-elementor).add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer button[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer input[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer select, .wlfmc-default-table:not(.is-elementor).qty-same-button input.qty,.wlfmc-default-table:not(.is-elementor).qty-same-button .quantity .button',
							'rules'    => array(
								'rule'    => 'color: %s !important',
								'default' => 'transparent',
							),
						),
						'wishlist_button_hover_color'      => array(
							'selector' => '.wlfmc-default-table-header:not(.is-elementor) .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .apply-btn,.wlfmc-default-table:not(.is-elementor).add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer button[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer input[type="submit"]',
							'rules'    => array(
								'rule'    => 'color: %s !important',
								'default' => 'transparent',
								'status'  => ':hover',
							),
						),
						'wishlist_button_font_size'        => array(
							'selector' => '.wlfmc-default-table-header:not(.is-elementor) .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .apply-btn,.wlfmc-default-table:not(.is-elementor).add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer button[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer input[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer select, .wlfmc-default-table:not(.is-elementor).qty-same-button input.qty,.wlfmc-default-table:not(.is-elementor).qty-same-button .quantity .button',
							'rules'    => array(
								'rule'    => 'font-size: %s !important',
								'default' => '14px',
							),
						),
						'wishlist_button_height'           => array(
							'selector' => '.wlfmc-default-table-header:not(.is-elementor) .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .apply-btn,.wlfmc-default-table:not(.is-elementor).add-to-card-same-button tr td.last-column .button:not(.minus):not(.plus),.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer .button,.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer button[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer input[type="submit"],.wlfmc-default-table:not(.is-elementor) .wlfmc-wishlist-footer select, .wlfmc-default-table:not(.is-elementor).qty-same-button input.qty,.wlfmc-default-table:not(.is-elementor).qty-same-button .quantity .button',
							'rules'    => array(
								'rule'    => 'height: %1$s !important;max-height: %1$s  !important;min-height: %1$s !important;padding-top:0;padding-bottom:0; display: flex;justify-content: center;align-items: center;margin:0;',
								'default' => '36px',
							),
						),
						'wishlist_table_thumbnail_background' => array(
							'selector' => '.wlfmc-default-table:not(.is-elementor) .product-thumbnail img',
							'rules'    => array(
								'rule'    => 'background-color: %s',
								'default' => 'transparent',
							),
						),
						'wishlist_table_grid_border_color' => array(
							'selector' => '.wlfmc-default-table:not(.is-elementor)  .wlfmc-wishlist-footer tr.actions,.wlfmc-default-table-header:not(.is-elementor),.wlfmc-default-table:not(.is-elementor) .wishlist-items-wrapper:not(.wishlist-empty) tr',
							'rules'    => array(
								'rule'    => 'border-color: %s !important',
								'default' => 'transparent',
							),
						),
						'wishlist_table_grid_border_radius' => array(
							'selector' => '.wlfmc-default-table:not(.is-elementor)  .wlfmc-wishlist-footer tr.actions,.wlfmc-default-table-header:not(.is-elementor),.wlfmc-default-table:not(.is-elementor) .wishlist-items-wrapper:not(.wishlist-empty) tr, .wlfmc-default-table:not(.is-elementor) .wishlist-items-wrapper:not(.wishlist-empty) .wlfmc-absolute-meta-data',
							'rules'    => array(
								'rule'    => 'border-radius: %s ',
								'default' => '10px',
							),
						),
						'wishlist_table_item_background'   => array(
							'selector' => '.wlfmc-default-table:not(.is-elementor)  .wlfmc-wishlist-footer tr.actions,.wlfmc-default-table-header:not(.is-elementor),.wlfmc-default-table:not(.is-elementor) .wishlist-items-wrapper:not(.wishlist-empty) tr',
							'rules'    => array(
								'rule'    => 'background-color: %s !important',
								'default' => 'transparent',
							),
						),
						'wishlist_table_item_hover_background' => array(
							'selector' => '.wlfmc-default-table:not(.is-elementor) .wishlist-items-wrapper:not(.wishlist-empty) tr',
							'rules'    => array(
								'rule'    => 'background-color: %s !important',
								'default' => 'transparent',
								'status'  => ':hover',
							),
						),
						'wishlist_table_separator_color'   => array(
							'selector' => '.wlfmc-default-table:not(.is-elementor) td.with-border-top',
							'rules'    => array(
								'rule'    => 'border-top-color: %s !important',
								'default' => 'transparent',
							),
						),
						'facebook_color'                   => array(
							'selector' => '.wlfmc-share ul.share-items .share-item a.facebook i',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'twitter_color'                    => array(
							'selector' => '.wlfmc-share ul.share-items .share-item a.twitter i',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'messenger_color'                  => array(
							'selector' => '.wlfmc-share ul.share-items .share-item a.messenger i',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'whatsapp_color'                   => array(
							'selector' => '.wlfmc-share ul.share-items .share-item a.whatsapp i',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'telegram_color'                   => array(
							'selector' => '.wlfmc-share ul.share-items .share-item a.telegram i',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'email_color'                      => array(
							'selector' => '.wlfmc-share ul.share-items .share-item a.email i',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'pdf_color'                        => array(
							'selector' => '.wlfmc-share ul.share-items .share-item a.download-pdf i',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'copy_color'                       => array(
							'selector' => '.wlfmc-share ul.share-items .share-item a.copy-link-trigger i',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'social_border_radius'             => array(
							'selector' => '.wlfmc-share ul.share-items i',
							'rules'    => array(
								'rule'    => 'border-radius: %s !important',
								'default' => '50%',
							),
						),
						'social_border_hover_color'        => array(
							'selector' => '.wlfmc-share ul.share-items a:hover i',
							'rules'    => array(
								'rule'    => 'border-color: %s !important',
								'default' => 'rgba(59,89,152,.1)',
							),
						),
						'social_border_color'              => array(
							'selector' => '.wlfmc-share ul.share-items i',
							'rules'    => array(
								'rule'    => 'border-color: %s !important',
								'default' => 'rgba(59,89,152,.1)',
							),
						),
						'copy_button_color'                => array(
							'selector' => '.wlfmc-share .wlfmc-copy-container .copy-link-trigger',
							'rules'    => array(
								'rule'    => 'color: %s !important',
								'default' => 'rgba(59,89,152,.1)',
							),
						),
						'copy_button_hover_color'          => array(
							'selector' => '.wlfmc-share .wlfmc-copy-container .copy-link-trigger',
							'rules'    => array(
								'rule'    => 'color: %s !important',
								'default' => 'rgba(59,89,152,.1)',
								'status'  => ':hover',
							),
						),
						'copy_button_background_color'     => array(
							'selector' => '.wlfmc-share .wlfmc-copy-container .copy-link-trigger',
							'rules'    => array(
								'rule'    => 'background-color: %s !important',
								'default' => 'rgba(59,89,152,.1)',
							),
						),
						'copy_button_background_hover_color' => array(
							'selector' => '.wlfmc-share .wlfmc-copy-container .copy-link-trigger',
							'rules'    => array(
								'rule'    => 'background-color: %s !important',
								'default' => 'rgba(59,89,152,.1)',
								'status'  => ':hover',
							),
						),
						'copy_button_border_color'         => array(
							'selector' => '.wlfmc-share .wlfmc-copy-container .copy-link-trigger',
							'rules'    => array(
								'rule'    => 'border-color: %s !important',
								'default' => 'rgba(59,89,152,.1)',
							),
						),
						'copy_button_border_hover_color'   => array(
							'selector' => '.wlfmc-share .wlfmc-copy-container .copy-link-trigger',
							'rules'    => array(
								'rule'    => 'border-color: %s !important',
								'default' => 'rgba(59,89,152,.1)',
								'status'  => ':hover',
							),
						),
						'copy_field_color'                 => array(
							'selector' => '.wlfmc-share .wlfmc-copy-container',
							'rules'    => array(
								'rule'    => 'color: %s !important',
								'default' => 'rgba(59,89,152,.1)',
							),
						),
						'copy_field_border_color'          => array(
							'selector' => '.wlfmc-share .wlfmc-copy-container',
							'rules'    => array(
								'rule'    => 'border-color: %s !important',
								'default' => 'rgba(59,89,152,.1)',
							),
						),
						'copy_field_background_color'      => array(
							'selector' => '.wlfmc-share .wlfmc-copy-container',
							'rules'    => array(
								'rule'    => 'background-color: %s !important',
								'default' => 'rgba(59,89,152,.1)',
							),
						),
						'counter_button_colors'            => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-view-wishlist-link',
							'rules'    => array(
								'background'       => array(
									'rule'    => 'background-color: %1$s !important; ',
									'default' => 'transparent',
								),
								'background-hover' => array(
									'rule'    => 'background-color: %1$s !important; ',
									'default' => 'transparent',
									'status'  => ':hover',
								),
								'color'            => array(
									'rule'    => 'color: %s !important',
									'default' => 'transparent',
								),
								'color-hover'      => array(
									'rule'    => 'color: %s !important',
									'default' => 'transparent',
									'status'  => ':hover',
								),
								'border'           => array(
									'rule'    => 'border-color: %s !important',
									'default' => 'transparent',
								),
								'border-hover'     => array(
									'rule'    => 'border-color: %s !important',
									'default' => 'transparent',
									'status'  => ':hover',
								),
							),
						),
						'position_fixed_z_index'           => array(
							'selector' => '.wlfmc-elementor.wlfmc-wishlist-counter',
							'rules'    => array(
								'rule'    => 'z-index: %s !important',
								'default' => '997',
							),
						),
						'counter_color'                    => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-counter-icon i',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'counter_icon_font_size'           => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-counter-icon i',
							'rules'    => array(
								'rule'    => 'font-size: %s',
								'default' => 'inherit',
							),
						),
						'counter_number_background_color'  => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-counter-number.position-top-left,.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-counter-number.position-top-right',
							'rules'    => array(
								'rule'    => 'background-color: %s',
								'default' => 'transparent',
							),
						),
						'counter_icon_width'               => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-counter-icon i.wlfmc-svg',
							'rules'    => array(
								'rule'    => 'width: %s',
								'default' => '24px',
							),
						),
						'counter_text_color'               => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-counter-text , .wlfmc-products-counter-wrapper:not(.is-elementor) .products-number-position-left .wlfmc-counter-number , .wlfmc-products-counter-wrapper:not(.is-elementor) .products-number-position-right .wlfmc-counter-number',
							'rules'    => array(
								'rule'    => 'color: %s',
								'default' => 'transparent',
							),
						),
						'counter_font_weight'              => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-counter-text',
							'rules'    => array(
								'rule'    => 'font-weight: %s',
								'default' => 'transparent',
							),
						),
						'counter_background_color'         => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-counter-items',
							'rules'    => array(
								'rule'    => 'background-color: %s',
								'default' => 'transparent',
							),
						),
						'counter_border_color'             => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-counter-items',
							'rules'    => array(
								'rule'    => 'border-color: %s',
								'default' => 'transparent',
							),
						),
						'counter_border_radius'            => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-counter-items',
							'rules'    => array(
								'rule'    => 'border-radius: %s !important',
								'default' => '5px',
							),
						),
						'counter_button_height'            => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-view-wishlist-link',
							'rules'    => array(
								'rule'    => 'height: %s !important',
								'default' => '38px',
							),
						),
						'counter_button_font_size'         => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-view-wishlist-link',
							'rules'    => array(
								'rule'    => 'font-size: %s !important',
								'default' => '15px',
							),
						),
						'counter_button_border_width'      => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-view-wishlist-link',
							'rules'    => array(
								'rule'    => 'border-width: %s !important',
								'default' => '0',
							),
						),
						'counter_button_border_radius'     => array(
							'selector' => '.wlfmc-products-counter-wrapper:not(.is-elementor) .wlfmc-view-wishlist-link',
							'rules'    => array(
								'rule'    => 'border-radius: %s !important',
								'default' => '5px',
							),
						),

					),
					$rules
				)
			);

			if ( empty( $rules ) ) {
				return $generated_code;
			}

			$options                        = new MCT_Options( 'wlfmc_options' );
			$button_theme_single            = $options->get_option( 'button_theme_single', true );
			$button_theme_loop              = $options->get_option( 'button_theme_loop', true );
			$button_type_single             = $options->get_option( 'button_type_single', 'icon' );
			$button_type_loop               = $options->get_option( 'button_type_loop', 'icon' );
			$wishlist_custom_template       = $options->get_option( 'wishlist_custom_template', true );
			$click_wishlist_button_behavior = $options->get_option( 'click_wishlist_button_behavior', 'just-add' );
			$icon_name_single               = $options->get_option( 'icon_name_single', 'heart' );
			$icon_name_loop                 = $options->get_option( 'icon_name_loop', 'heart' );
			$icon_name_counter              = $options->get_option( 'counter_icon', 'heart' );
			$enable_counter_text            = $options->get_option( 'enable_counter_text', '0' );
			$enable_counter_products_number = $options->get_option( 'enable_counter_products_number', 'right' );
			$enable_toast_style             = $options->get_option( 'enable_toast_style', '0' );
			if ( '1' === $options->get_option( 'wishlist_disable_qty_padding', '0' ) ) {
				$rules['wishlist_disable_qty_padding'] = array(
					'selector' => '.wlfmc-default-table:not(.is-elementor) input.qty',
					'rules'    => array(
						'rule'    => 'padding-left:0 !important;padding-right: 0 !important;',
						'default' => '0',
					),
				);
			}
			if ( 'custom' === $icon_name_single ) {
				$rules['icon_font_size_single'] = array(
					'selector' => '.wlfmc-single-btn:not(.is-elementor)  .wlfmc-add-button > a i svg,.wlfmc-single-btn:not(.is-elementor)  .wlfmc-add-button > a i.wlfmc-svg',
					'rules'    => array(
						'rule'    => 'width: %1$s;min-width:%1$s',
						'default' => '20px',
					),
				);
			}
			if ( 'custom' === $icon_name_loop ) {
				$rules['icon_font_size_loop'] = array(
					'selector' => '.wlfmc-loop-btn:not(.is-elementor)  .wlfmc-add-button > a i svg,.wlfmc-loop-btn:not(.is-elementor)  .wlfmc-add-button > a i.wlfmc-svg ',
					'rules'    => array(
						'rule'    => 'width: %1$s;min-width:%1$s',
						'default' => '20px',
					),
				);
			}
			if ( wlfmc_is_true( $button_theme_single ) ) {

				if ( isset( $rules['button_margin_single'] ) ) {
					unset( $rules['button_margin_single'] );
				}

				if ( isset( $rules['button_height_single'] ) ) {
					unset( $rules['button_height_single'] );
				}
				if ( isset( $rules['button_background_color_single'] ) ) {
					unset( $rules['button_background_color_single'] );
				}

				if ( isset( $rules['button_background_hover_color_single'] ) ) {
					unset( $rules['button_background_hover_color_single'] );
				}

				if ( isset( $rules['button_border_radius_single'] ) ) {
					unset( $rules['button_border_radius_single'] );
				}
				if ( isset( $rules['button_width_single'] ) ) {
					unset( $rules['button_width_single'] );
				}
				if ( isset( $rules['button_border_width_single'] ) ) {
					unset( $rules['button_border_width_single'] );
				}

				if ( isset( $rules['button_border_color_single'] ) ) {
					unset( $rules['button_border_color_single'] );
				}

				if ( isset( $rules['button_border_hover_color_single'] ) ) {
					unset( $rules['button_border_hover_color_single'] );
				}

				if ( isset( $rules['text_font_size_single'] ) ) {
					unset( $rules['text_font_size_single'] );
				}
				if ( isset( $rules['text_color_single'] ) ) {
					unset( $rules['text_color_single'] );
				}
				if ( isset( $rules['text_hover_color_single'] ) ) {
					unset( $rules['text_hover_color_single'] );
				}
				if ( isset( $rules['icon_font_size_single'] ) ) {
					unset( $rules['icon_font_size_single'] );
				}
				if ( isset( $rules['icon_color_single'] ) ) {
					unset( $rules['icon_color_single'] );
				}
				if ( isset( $rules['icon_hover_color_single'] ) ) {
					unset( $rules['icon_hover_color_single'] );
				}
			}
			if ( wlfmc_is_true( $button_theme_loop ) ) {

				if ( isset( $rules['button_margin_loop'] ) ) {
					unset( $rules['button_margin_loop'] );
				}

				if ( isset( $rules['button_height_loop'] ) ) {
					unset( $rules['button_height_loop'] );
				}

				if ( isset( $rules['button_border_radius_loop'] ) ) {
					unset( $rules['button_border_radius_loop'] );
				}

				if ( isset( $rules['button_border_width_loop'] ) ) {
					unset( $rules['button_border_width_loop'] );
				}

				if ( isset( $rules['button_width_loop'] ) ) {
					unset( $rules['button_width_loop'] );
				}

				if ( isset( $rules['button_background_color_loop'] ) ) {
					unset( $rules['button_background_color_loop'] );
				}

				if ( isset( $rules['button_background_hover_color_loop'] ) ) {
					unset( $rules['button_background_hover_color_loop'] );
				}
				if ( isset( $rules['button_border_color_loop'] ) ) {
					unset( $rules['button_border_color_loop'] );
				}

				if ( isset( $rules['button_border_hover_color_loop'] ) ) {
					unset( $rules['button_border_hover_color_loop'] );
				}

				if ( isset( $rules['text_font_size_loop'] ) ) {
					unset( $rules['text_font_size_loop'] );
				}
				if ( isset( $rules['text_color_loop'] ) ) {
					unset( $rules['text_color_loop'] );
				}
				if ( isset( $rules['text_hover_color_loop'] ) ) {
					unset( $rules['text_hover_color_loop'] );
				}
				if ( isset( $rules['icon_font_size_loop'] ) ) {
					unset( $rules['icon_font_size_loop'] );
				}
				if ( isset( $rules['icon_color_loop'] ) ) {
					unset( $rules['icon_color_loop'] );
				}
				if ( isset( $rules['icon_hover_color_loop'] ) ) {
					unset( $rules['icon_hover_color_loop'] );
				}
			}
			if ( wlfmc_is_true( $wishlist_custom_template ) ) {

				if ( isset( $rules['wishlist_button_color'] ) ) {
					unset( $rules['wishlist_button_color'] );
				}

				if ( isset( $rules['wishlist_button_hover_color'] ) ) {
					unset( $rules['wishlist_button_hover_color'] );
				}
				if ( isset( $rules['wishlist_button_font_size'] ) ) {
					unset( $rules['wishlist_button_font_size'] );
				}
				if ( isset( $rules['wishlist_button_height'] ) ) {
					unset( $rules['wishlist_button_height'] );
				}
				if ( isset( $rules['wishlist_button_background_color'] ) ) {
					unset( $rules['wishlist_button_background_color'] );
				}
				if ( isset( $rules['wishlist_button_background_hover_color'] ) ) {
					unset( $rules['wishlist_button_background_hover_color'] );
				}
				if ( isset( $rules['wishlist_button_border_color'] ) ) {
					unset( $rules['wishlist_button_border_color'] );
				}
				if ( isset( $rules['wishlist_button_border_hover_color'] ) ) {
					unset( $rules['wishlist_button_border_hover_color'] );
				}
				if ( isset( $rules['wishlist_button_border_width'] ) ) {
					unset( $rules['wishlist_button_border_width'] );
				}
				if ( isset( $rules['wishlist_button_border_radius'] ) ) {
					unset( $rules['wishlist_button_border_radius'] );
				}

				if ( isset( $rules['wishlist_table_thumbnail_background'] ) ) {
					unset( $rules['wishlist_table_thumbnail_background'] );
				}
				if ( isset( $rules['wishlist_table_grid_border_color'] ) ) {
					unset( $rules['wishlist_table_grid_border_color'] );
				}
				if ( isset( $rules['wishlist_table_grid_border_radius'] ) ) {
					unset( $rules['wishlist_table_grid_border_radius'] );
				}
				if ( isset( $rules['wishlist_table_item_background'] ) ) {
					unset( $rules['wishlist_table_item_background'] );
				}
				if ( isset( $rules['wishlist_table_item_hover_background'] ) ) {
					unset( $rules['wishlist_table_item_hover_background'] );
				}
				if ( isset( $rules['wishlist_table_separator_color'] ) ) {
					unset( $rules['wishlist_table_separator_color'] );
				}
			}
			if ( 'icon' === $button_type_single ) {

				if ( isset( $rules['text_font_size_single'] ) ) {
					unset( $rules['text_font_size_single'] );
				}
				if ( isset( $rules['text_color_single'] ) ) {
					unset( $rules['text_color_single'] );
				}
				if ( isset( $rules['text_hover_color_single'] ) ) {
					unset( $rules['text_hover_color_single'] );
				}
			}
			if ( 'text' === $button_type_single ) {
				if ( isset( $rules['icon_font_size_single'] ) ) {
					unset( $rules['icon_font_size_single'] );
				}
				if ( isset( $rules['icon_color_single'] ) ) {
					unset( $rules['icon_color_single'] );
				}
				if ( isset( $rules['icon_hover_color_single'] ) ) {
					unset( $rules['icon_hover_color_single'] );
				}
			}
			if ( 'text' === $button_type_single || 'both' === $button_type_single ) {
				if ( isset( $rules['button_width_single'] ) ) {
					unset( $rules['button_width_single'] );
				}
			}
			if ( 'both' === $button_type_single && ! wlfmc_is_true( $options->get_option( 'separate_icon_and_text_single', false ) ) ) {
				if ( isset( $rules['separator_color_single'] ) ) {
					unset( $rules['separator_color_single'] );
				}
			}
			if ( 'icon' === $button_type_loop ) {

				if ( isset( $rules['text_font_size_loop'] ) ) {
					unset( $rules['text_font_size_loop'] );
				}
				if ( isset( $rules['text_color_loop'] ) ) {
					unset( $rules['text_color_loop'] );
				}
				if ( isset( $rules['text_hover_color_loop'] ) ) {
					unset( $rules['text_hover_color_loop'] );
				}
			}
			if ( 'text' === $button_type_loop ) {
				if ( isset( $rules['icon_font_size_loop'] ) ) {
					unset( $rules['icon_font_size_loop'] );
				}
				if ( isset( $rules['icon_color_loop'] ) ) {
					unset( $rules['icon_color_loop'] );
				}
				if ( isset( $rules['icon_hover_color_loop'] ) ) {
					unset( $rules['icon_hover_color_loop'] );
				}
			}
			if ( 'text' === $button_type_loop || 'both' === $button_type_loop ) {
				if ( isset( $rules['button_width_loop'] ) ) {
					unset( $rules['button_width_loop'] );
				}
			}
			if ( 'both' === $button_type_loop && ! wlfmc_is_true( $options->get_option( 'separate_icon_and_text_loop', false ) ) ) {
				if ( isset( $rules['separator_color_loop'] ) ) {
					unset( $rules['separator_color_loop'] );
				}
			}
			if ( ! wlfmc_is_true( $options->get_option( 'tooltip_custom_style', false ) ) ) {
				if ( isset( $rules['tooltip_color'] ) ) {
					unset( $rules['tooltip_color'] );
				}
				if ( isset( $rules['tooltip_background_color'] ) ) {
					unset( $rules['tooltip_background_color'] );
				}
				if ( isset( $rules['tooltip_border_radius'] ) ) {
					unset( $rules['tooltip_border_radius'] );
				}
			}

			if ( 'open-popup' !== $click_wishlist_button_behavior ) {
				if ( isset( $rules['popup_buttons'] ) ) {
					unset( $rules['popup_buttons'] );
				}
			}
			if ( ! wlfmc_is_true( $options->get_option( 'show_login_notice_for_guests', true ) ) ) {
				if ( isset( $rules['login_notice_background_color'] ) ) {
					unset( $rules['login_notice_background_color'] );
				}
				if ( isset( $rules['login_notice_buttons'] ) ) {
					unset( $rules['login_notice_buttons'] );
				}
			}
			if ( 'custom' === $icon_name_counter ) {
				if ( isset( $rules['counter_color'] ) ) {
					unset( $rules['counter_color'] );
				}
				if ( isset( $rules['counter_icon_font_size'] ) ) {
					unset( $rules['counter_icon_font_size'] );
				}
			}
			if ( ! wlfmc_is_true( $enable_counter_text ) ) {
				if ( isset( $rules['counter_text_color'] ) ) {
					unset( $rules['counter_text_color'] );
				}
			}
			if ( ! wlfmc_is_true( $enable_counter_text ) ) {
				if ( isset( $rules['counter_font_weight'] ) ) {
					unset( $rules['counter_font_weight'] );
				}
			}
			if ( ! wlfmc_is_true( $enable_counter_products_number ) ) {
				if ( isset( $rules['counter_number_background_color'] ) ) {
					unset( $rules['counter_number_background_color'] );
				}
			}
			if ( ! wlfmc_is_true( $enable_toast_style ) ) {
				if ( isset( $rules['toast_success_color'] ) ) {
					unset( $rules['toast_success_color'] );
				}
				if ( isset( $rules['toast_success_background_color'] ) ) {
					unset( $rules['toast_success_background_color'] );
				}
				if ( isset( $rules['toast_error_color'] ) ) {
					unset( $rules['toast_error_color'] );
				}
				if ( isset( $rules['toast_error_background_color'] ) ) {
					unset( $rules['toast_error_background_color'] );
				}
			}

			foreach ( $rules as $id => $rule ) {

				// retrieve values from db.
				$values     = $options->get_option( $id );
				$new_rules  = array();
				$rules_code = '';

				if ( isset( $rule['rules']['rule'] ) ) {
					// if we have a single-valued option, just search for the rule to apply.
					$status = $rule['rules']['status'] ?? '';

					$new_rules[ $status ]   = array();
					$new_rules[ $status ][] = $this->build_css_rule( $rule['rules']['rule'], $values, $rule['rules']['default'] );
				} elseif ( isset( $rule['type'] ) && 'repeater' === $rule['type'] ) {
					// if we have a repeater field cycle through rules, and generate CSS code.
					if ( is_array( $values ) && ! empty( $values ) ) {
						foreach ( $values as $k => $row ) {
							foreach ( $rule['rules'] as $property => $css ) {
								$status = $css['status'] ?? '';

								if ( ! isset( $new_rules[ $status ] ) ) {
									$new_rules[ $status ] = array();
								}

								$new_rules[ $k ][ $status ][] = $this->build_css_rule( $css['rule'], $row[ $property ] ?? false, $css['default'] );
							}
						}
					}
				} else {
					// otherwise, cycle through rules, and generate CSS code.
					foreach ( $rule['rules'] as $property => $css ) {
						$status = $css['status'] ?? '';

						if ( ! isset( $new_rules[ $status ] ) ) {
							$new_rules[ $status ] = array();
						}

						$new_rules[ $status ][] = $this->build_css_rule( $css['rule'], $values[ $property ] ?? false, $css['default'] );
					}
				}

				// if code was generated, prepend selector.
				if ( ! empty( $new_rules ) ) {
					if ( isset( $rule['type'] ) && 'repeater' === $rule['type'] ) {
						foreach ( $new_rules as $k => $row ) {

							$selector = sprintf( $rule['selector'], $k );
							foreach ( $row as $status => $rules ) {
								if ( ! empty( $status ) ) {
									$updated_selector = array();
									$split_selectors  = explode( ',', $selector );

									foreach ( $split_selectors as $split_selector ) {
										$updated_selector[] = $split_selector . $status;
									}

									$selector = implode( ',', $updated_selector );
								}

								$rules_code .= $selector . '{' . implode( '', $rules ) . '}';
							}
						}
					} else {
						foreach ( $new_rules as $status => $rules ) {
							$selector = $rule['selector'];

							if ( ! empty( $status ) ) {
								$updated_selector = array();
								$split_selectors  = explode( ',', $rule['selector'] );

								foreach ( $split_selectors as $split_selector ) {
									$updated_selector[] = $split_selector . $status;
								}

								$selector = implode( ',', $updated_selector );
							}

							$rules_code .= $selector . '{' . implode( '', $rules ) . '}';
						}
					}
				}

				// append new rule to generated CSS.
				$generated_code .= $rules_code;
			}

			if ( ! wlfmc_is_true( $wishlist_custom_template ) ) {
				$generated_code .= '.wlfmc-default-table-header:not(.is-elementor) .button { width: ' . $options->get_option( 'wishlist_button_height', '36px' ) . '}';
			}

			if ( ! function_exists( 'rehub_theme_after_setup' ) && ! function_exists( 'flatsome_setup' ) && ! class_exists( 'Metro_Main' ) ) {
				$generated_code .= '.single-product div.product form.cart .wlfmc-add-to-wishlist.wlfmc_position_before_add_to_cart_button {float: left;}.rtl.single-product div.product form.cart .wlfmc-add-to-wishlist.wlfmc_position_before_add_to_cart_button {float: right;}';
			}

			if ( class_exists( 'The7_Autoloader' ) ) {
				$generated_code .= ' .wlfmc-wishlist-table .quantity .qty { border-right:none;border-left:none; border-radius: 0}';
			}

			if ( function_exists( 'porto_setup' ) ) {
				$generated_code .= ' .wlfmc-wishlist-table .add-links-wrap .compare ,.wlfmc-wishlist-table .add-links-wrap .quickview { display:none}';
			}

			if ( defined( 'SHOPTIMIZER_VERSION' ) ) {
				$generated_code .= '.wlfmc-loop-btn .wlfmc-add-button > a:not(.wlfmc-custom-btn) { width: 100% !important;padding: 0 10px !important; margin-bottom:10px !important}';
			}

			if ( class_exists( 'Blocksy_Manager' ) ) {
				$generated_code .= '.wlfmc-add-to-wishlist.wlfmc_position_after_add_to_cart_button {width: auto;} .wlfmc-wishlist-table .quantity .qty {height:44px}';
			}

			if ( class_exists( 'ShopEngine' ) ) {
				$generated_code .= '.wlfmc-wishlist-table .shopengine-quickview-trigger,.wlfmc-wishlist-table .shopengine-comparison ,.wlfmc-wishlist-table .shopengine-wishlist {display:none !important}';
				$generated_code .= '.shopengine-recently-viewed-products .wlfmc-loop-btn:not(.is-elementor) .wlfmc-add-button > a {height: auto !important;min-height: 30px;}';
				$generated_code .= '.shopengine-product-list .wlfmc-loop-btn:not(.is-elementor) .wlfmc-add-button > a {height: 40px !important;text-indent: 0;}';
				$generated_code .= '.shopengine-shopengine-filterable-product-list .wlfmc-top-of-image ,.shopengine-recently-viewed-products .wlfmc-top-of-image ,.shopengine-product-list .wlfmc-top-of-image {right: 0;top: 0;position: relative !important;left:0;}';
			}

			if ( defined( 'UAEL_FILE' ) ) {
				$generated_code .= '.uael-woo-product-wrapper { position:relative;}';
			}

			if ( defined( 'ASTRA_PRO_SITES_VER' ) ) {
				$generated_code .= '.wlfmc-wishlist-table .last-column .quantity .minus, .wlfmc-wishlist-table .last-column .quantity .plus {margin: 0 !important;width:30px}';
			}

			$generated_code  = apply_filters( 'wlfmc_custom_css_output', $generated_code );
			$generated_code .= $options->get_option( 'custom_css', true );

			return $generated_code;

		}

		/**
		 * Generate each single CSS rule that will be included in custom plugin CSS
		 *
		 * @param string $rule Rule to use; placeholders may be applied to be replaced with value {@see sprintf}.
		 * @param string $value Value to inject inside rule, replacing placeholders.
		 * @param string $default Default value, to be used instead of value when it is empty.
		 *
		 * @return string Formatted CSS rule
		 */
		protected function build_css_rule( $rule, $value, $default = '' ) {
			$value = ( '0' === $value || ( ! empty( $value ) && ! is_array( $value ) ) ) ? $value : $default;

			return sprintf( rtrim( $rule, ';' ) . ';', $value );
		}

		/**
		 * Format options that will send through AJAX calls to refresh arguments
		 *
		 * @param array  $options Array of options.
		 * @param string $context Widget/Shortcode that will use the options.
		 *
		 * @return array Array of formatted options
		 * @version 1.6.0
		 */
		public function format_fragment_options( $options, $context = '' ) {
			// removes unusable values, and changes options common for all fragments.
			if ( ! empty( $options ) ) {
				foreach ( $options as $id => $value ) {
					if ( is_object( $value ) || $this->is_multidimensional( $value ) || ( is_array( $value ) && array_values( $value ) !== $value ) ) {
						// remove item if type is not supported.
						unset( $options[ $id ] );
					} elseif ( is_array( $value ) ) {
						$options[ $id ] = implode( ',', $value );
					} elseif ( 'ajax_loading' === $id ) {
						$options['ajax_loading'] = false;
					}
				}
			}

			// applies context specific changes.
			if ( ! empty( $context ) ) {
				$options['item'] = $context;
			}

			return $options;
		}

		/**
		 * Multidimensional check
		 *
		 * @param array $array array for check.
		 *
		 * @return bool
		 */
		private function is_multidimensional( $array ) {
			if ( ! is_array( $array ) ) {
				return false;
			}

			foreach ( $array as $element ) {
				if ( is_array( $element ) || is_object( $element ) ) {
					return true; // Found a nested array.
				}
			}

			return false; // No nested arrays found.
		}

		/**
		 * Decode options that come from the fragment
		 *
		 * @param array $options Options for the fragments.
		 *
		 * @return array Filtered options for the fragment
		 * @version 1.7.0
		 */
		public function decode_fragment_options( $options ) {
			if ( ! empty( $options ) ) {
                $has_svg = wlfmc_is_true( $options['is_svg_icon'] ?? false );
				foreach ( $options as $id => $value ) {
					if ( 'true' === $value ) {
						$options[ $id ] = true;
					} elseif ( 'false' === $value ) {
						$options[ $id ] = false;
					} elseif ( null !== $value ) {
                        if ( $has_svg && strpos( $value, '<svg' ) !== false && in_array( $id, array( 'empty_icon', 'has_item_icon', 'icon', 'added_icon' ), true ) ) {
	                        $options[ $id ] = wlfmc_sanitize_svg( $value );
                        } else {
	                        $options[ $id ] = wp_kses_post( wp_unslash( $value ) );
                        }

					}
				}
			}

			return $options;
		}


		/**
		 * Destroy serialize cookies, to prevent major vulnerability
		 *
		 * @return void
		 */
		protected function destroy_serialized_cookies() {
			$name = 'wlfmc_products';

			if ( isset( $_COOKIE[ $name ] ) && is_serialized( sanitize_text_field( wp_unslash( $_COOKIE[ $name ] ) ) ) ) {
				$_COOKIE[ $name ] = wp_json_encode( array() );
				wlfmc_destroycookie( $name );
			}
		}

		/**
		 * Update old wishlist cookies
		 *
		 * @return void
		 */
		protected function update_cookies() {
			$cookie     = wlfmc_getcookie( 'wlfmc_products' );
			$new_cookie = array();

			if ( ! empty( $cookie ) ) {
				foreach ( $cookie as $item ) {
					if ( ! isset( $item['add-to-wishlist'] ) ) {
						return;
					}

					$new_cookie[] = array(
						'prod_id'     => $item['add-to-wishlist'],
						'quantity'    => $item['quantity'] ?? 1,
						'wishlist_id' => false,
					);
				}

				wlfmc_setcookie( 'wlfmc_products', $new_cookie );
			}
		}

		/**
		 * Convert wishlist stored into cookies into.
		 *
		 * @return bool|void
		 * @throws Exception  When not able to load default wishlist or Data Store class.
		 */
		protected function convert_cookies_to_session() {
			$cookie = wlfmc_getcookie( 'wlfmc_products' );

			if ( ! empty( $cookie ) ) {

				$default_list = WLFMC_Wishlist_Factory::get_default_wishlist();

				if ( ! $default_list ) {
					return false;
				}

				foreach ( $cookie as $item ) {
					if ( $default_list->has_product( $item['prod_id'] ) ) {
						continue;
					}

					$new_item = new WLFMC_Wishlist_Item();

					$new_item->set_product_id( $item['prod_id'] );
					$new_item->set_quantity( $item['quantity'] );

					if ( isset( $item['dateadded'] ) ) {
						$new_item->set_date_added( $item['dateadded'] );
					}

					$default_list->add_item( $new_item );
				}

				$default_list->save();

				wlfmc_destroycookie( 'wlfmc_products' );
			}
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC_Frontend
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}

/**
 * Unique access to instance of WLFMC_Frontend class
 *
 * @return WLFMC_Frontend
 */
function WLFMC_Frontend(): WLFMC_Frontend { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WLFMC_Frontend::get_instance();
}
