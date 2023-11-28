<?php
/**
 * Shortcodes Class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.6.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WLFMC_Shortcode' ) ) {
	/**
	 * Woocommerce Smart Wishlist Shortcodes
	 */
	class WLFMC_Shortcode {

		/**
		 * Init shortcodes available for the plugin
		 *
		 * @return void
		 *
		 * @version 1.3.0
		 */
		public static function init() {
			// register shortcodes.
			add_shortcode( 'wlfmc_wishlist', array( 'WLFMC_Shortcode', 'wishlist' ) );
			add_shortcode( 'wlfmc_add_to_wishlist', array( 'WLFMC_Shortcode', 'add_to_wishlist' ) );
			add_shortcode( 'wlfmc_wishlist_counter', array( 'WLFMC_Shortcode', 'wishlist_counter' ) );
		}

		/**
		 * Print the wishlist HTML.
		 *
		 * @param array  $atts Array of attributes for the shortcode.
		 * @param string $content Shortcode content (none expected).
		 *
		 * @return string Rendered shortcode
		 *
		 * @version 1.6.8
		 */
		public static function wishlist( $atts, $content = null ) {
			global $wlfmc_is_wishlist, $wlfmc_wishlist_token;
			$options          = new MCT_Options( 'wlfmc_options' );
			$wishlist_enabled = wlfmc_is_true( $options->get_option( 'wishlist_enable', '1' ) );
			if ( ! $wishlist_enabled ) {
				return '';
			}

			$who_can_see_wishlist_options = $options->get_option( 'who_can_see_wishlist_options', 'all' );
			if ( ( 'users' === $who_can_see_wishlist_options && ! is_user_logged_in() ) ) {
				return '';
			}

			$wishlist_under_table = $options->get_option(
				'wishlist_under_table',
				array(
					'actions',
					'add-all-to-cart',
				)
			);

			$atts = shortcode_atts(
				array(
					'unique_id'                => wp_unique_id(),
					'is_elementor'             => false,
					'wishlist_id'              => get_query_var( 'wishlist_id', false ),
					'action_params'            => get_query_var( WLFMC()->wishlist_param, false ),
					'per_page'                 => get_option( 'posts_per_page', 9 ),
					'current_page'             => 1,
					'pagination'               => true,
					'wishlist_url'             => WLFMC()->get_wc_wishlist_url(),
					'no_interactions'          => false,
					'items_show'               => $options->get_option(
						'wishlist_items_show',
						array(
							'product-checkbox',
							'product-remove',
							'product-thumbnail',
							'product-name',
							'product-price',
							'product-add-to-cart',
						)
					),
					'enable_layout_toggle'     => false,
					'product_move'             => $options->get_option( 'product_move', '0' ),
					'product_copy'             => $options->get_option( 'product_copy', '0' ),
					'move_all_to_list_label'   => $options->get_option( 'multi_list_move_all_to_list_label', __( 'Move all to list', 'wc-wlfmc-wishlist' ) ),
					'copy_all_to_list_label'   => $options->get_option( 'copy_all_to_list_label', __( 'Copy all to list', 'wc-wlfmc-wishlist' ) ),
					'enable_drag_n_drop'       => $options->get_option( 'wishlist_drag_n_drop', '0' ),
					'is_cache_enabled'         => $options->get_option( 'is_cache_enabled', '1' ),
					'wishlist_page_title'      => $options->get_option( 'wishlist_page_title', __( 'My Wishlist', 'wc-wlfmc-wishlist' ) ),
					'view_mode'                => $options->get_option( 'wishlist_view_mode', 'list' ),
					'button_add_to_cart_style' => $options->get_option( 'wishlist_button_add_to_cart_style', true ),
					'qty_style'                => $options->get_option( 'wishlist_qty_style', true ),
					'pagination_style'         => $options->get_option( 'wishlist_pagination_style', true ),
					'custom_template'          => $options->get_option( 'wishlist_custom_template', true ),
					'enable_actions'           => in_array( 'actions', (array) $wishlist_under_table, true ),
					'enable_all_add_to_cart'   => in_array( 'add-all-to-cart', (array) $wishlist_under_table, true ),
					'enable_share'             => $options->get_option( 'enable_share' ),
					'share_position'           => $options->get_option( 'share_position', 'after_table' ),
					'share_items'              => $options->get_option( 'share_items' ),
					'socials_title'            => $options->get_option( 'socials_title' ),
					'login_notice'             => $options->get_option( 'show_login_notice_for_guests', '0' ),
				),
				$atts
			);

			/**
			 * Variables
			 *
			 * @var $per_page int
			 * @var $current_page int
			 * @var $pagination string
			 * @var $wishlist_id int
			 * @var $action_params array
			 * @var $no_interactions string
			 * @var $items_show array|string
			 * @var $view_mode string
			 * @var $button_add_to_cart_style string
			 * @var $qty_style string
			 * @var $pagination_style string
			 * @var $custom_template string
			 * @var $enable_actions string
			 * @var $enable_all_add_to_cart string
			 * @var $enable_share string
			 * @var $share_items array|string
			 * @var $socials_title string
			 * @var $login_notice string
			 * @var $unique_id string
			 * @var $is_elementor string
			 * @var $enable_drag_n_drop string
			 * @var $product_move string
			 * @var $product_copy string
			 */

			extract( $atts ); // phpcs:ignore WordPress.PHP.DontExtract

			$items_show = ! is_array( $items_show ) ? array_map( 'trim', explode( ',', $items_show ) ) : $items_show;

			// init params needed to load correct template.
			$template_part            = 'view';
			$is_user_owner            = false;
			$product_move             = wlfmc_is_true( $product_move );
			$product_copy             = wlfmc_is_true( $product_copy );
			$enable_drag_n_drop       = wlfmc_is_true( $enable_drag_n_drop );
			$is_elementor             = wlfmc_is_true( $is_elementor );
			$no_interactions          = wlfmc_is_true( $no_interactions );
			$button_add_to_cart_style = wlfmc_is_true( $button_add_to_cart_style );
			$qty_style                = wlfmc_is_true( $qty_style );
			$pagination_style         = wlfmc_is_true( $pagination_style );
			$custom_template          = wlfmc_is_true( $custom_template );
			$enable_actions           = wlfmc_is_true( $enable_actions );
			$enable_all_add_to_cart   = wlfmc_is_true( $enable_all_add_to_cart );
			$enable_share             = wlfmc_is_true( $enable_share );
			$login_notice             = wlfmc_is_true( $login_notice );
			$enable_layout_toggle     = wlfmc_is_true( $enable_layout_toggle );
			$enable_layout_toggle     = $enable_layout_toggle || in_array( $view_mode, array( 'both-grid', 'both-list' ), true );
			$view_mode                = str_replace( 'both-', '', $view_mode );
			$view_mode                = '' === $view_mode ? 'list' : $view_mode;
			$user_view_mode           = is_user_logged_in() ? get_user_meta( get_current_user_id(), 'wlfmc_list_layout', true ) : wlfmc_getcookie( 'wlfmc_list_layout' );
			$user_view_mode           = $enable_layout_toggle && in_array( $user_view_mode, array( 'list', 'grid' ), true ) ? $user_view_mode : $view_mode;
			$popup_position           = $options->get_option( 'popup_position', 'center-center' );
			$popup_position           = explode( '-', $popup_position );
			$popup_vertical           = $popup_position[0];
			$popup_horizontal         = $popup_position[1];
			$tooltip_direction        = isset( $atts['tooltip_direction'] ) ? $atts['tooltip_direction'] : $options->get_option( 'tooltip_direction', 'top' );
			$tooltip_class            = ' wlfmc-tooltip wlfmc-tooltip-' . $tooltip_direction;
			$tooltip_type             = wlfmc_is_true( $options->get_option( 'tooltip_custom_style', '0' ) ) ? 'custom' : 'default';
			$action_params            = explode( '/', apply_filters( 'wlfmc_current_wishlist_view_params', $atts['action_params'] ) );
			$action                   = ( isset( $action_params[0] ) ) ? $action_params[0] : 'view';
			$additional_params        = array(
				'base_url'                  => wlfmc_get_current_url(),
				// wishlist data.
				'wishlist'                  => false,
				'wishlist_token'            => '',
				'wishlist_id'               => false,
				'is_private'                => false,

				// wishlist items.
				'count'                     => 0,
				'wishlist_items'            => array(),

				// page data.
				'current_page'              => $current_page,
				'page_links'                => false,

				// user data.
				'is_user_logged_in'         => is_user_logged_in(),
				'is_user_owner'             => $is_user_owner,

				// view data.
				'no_interactions'           => $no_interactions,
				'product_move'              => $product_move,
				'product_copy'              => $product_copy,
				'enable_layout_toggle'      => $enable_layout_toggle,
				'popup_vertical'            => $popup_vertical,
				'popup_horizontal'          => $popup_horizontal,
				'view_mode'                 => $user_view_mode,
				'items_show'                => $items_show,
				'empty_wishlist_title'      => $options->get_option( 'empty_wishlist_title', esc_html__( 'YOUR WISHLIST IS EMPTY!', 'wc-wlfmc-wishlist' ) ),
				'empty_wishlist_content'    => $options->get_option( 'empty_wishlist_content', esc_html__( 'You have not added any products to your wishlist.', 'wc-wlfmc-wishlist' ) ),
				'enable_actions'            => $enable_actions,
				'enable_all_add_to_cart'    => $enable_all_add_to_cart,
				'tooltip_class'             => $tooltip_class,
				'tooltip_type'              => $tooltip_type,
				'wishlist_class'            => 'wlfmc-default-table',
				'wishlist_header_class'     => 'wlfmc-default-table-header',
				'wishlist_pagination_class' => apply_filters( 'wlfmc_default_pagination_class', 'woocommerce-pagination' ),
				// share data.
				'share_enabled'             => false,

				// template data.
				'template_part'             => $template_part,
				'additional_info'           => false,
				'available_multi_list'      => false,
				'users_wishlists'           => array(),
				'redirect_to_cart'          => $options->get_option( 'redirect_after_add_to_cart', true ),
				'form_action'               => esc_url( WLFMC()->get_wishlist_url( 'wishlist', 'view' ) ),
			);

			$additional_params['wishlist_class'] .= $is_elementor ? ' is-elementor' : '';

			if ( $custom_template ) {
				$additional_params['wishlist_class']        .= ' wishlist-default-style';
				$additional_params['wishlist_header_class'] .= ' wishlist-default-style';
			}

			if ( $button_add_to_cart_style ) {
				$additional_params['wishlist_class'] .= ' add-to-card-same-button';
			}
			if ( $qty_style ) {
				$additional_params['wishlist_class'] .= ' qty-same-button';
			}
			if ( $pagination_style ) {
				$additional_params['wishlist_pagination_class'] = 'wishlist-pagination';
			}

			$wishlist = WLFMC_Wishlist_Factory::get_current_wishlist( $atts );

			if ( $wishlist && $wishlist->current_user_can( 'view' ) ) {

				// set global wishlist token.
				$wlfmc_wishlist_token = $wishlist->get_token();

				// retrieve wishlist params.
				$is_user_owner = $wishlist->is_current_user_owner();
				$count         = $wishlist->count_items();
				$offset        = 0;

				if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
					$enable_drag_n_drop = $enable_drag_n_drop && 1 < $wishlist->count_items() && $wishlist->current_user_can( 'drag_n_drop' ) && ! $no_interactions;
					$pagination         = ! $enable_drag_n_drop && $pagination;
				}

				// sets current page, number of pages and element offset.
				$queried_page = isset( $_REQUEST['pagenum'] ) ? absint( $_REQUEST['pagenum'] ) : $current_page; // phpcs:ignore WordPress.Security.NonceVerification
				$queried_page = apply_filters( 'wlfmc_wishlist_queried_page', $queried_page );
				$current_page = max( 1, $queried_page ? $queried_page : $current_page );

				// sets variables for pagination, if shortcode atts is set to yes.
				if ( $pagination && ! $no_interactions && $count > 1 ) {
					$pages = ceil( $count / $per_page );
					if ( $current_page > $pages ) {
						$current_page = $pages;
					}

					$offset = ( $current_page - 1 ) * $per_page;

					if ( $pages > 1 ) {
						$base       = $is_user_owner ? esc_url_raw( add_query_arg( array( 'pagenum' => '%#%' ), WLFMC()->get_wc_wishlist_url() ) ) : esc_url_raw( add_query_arg( array( 'pagenum' => '%#%' ), $wishlist->get_share_url() ) );
						$page_links = paginate_links(
							array(
								'base'      => $base,
								'format'    => '',
								'current'   => $current_page,
								'total'     => $pages,
								'show_all'  => true,
								'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
								'next_text' => is_rtl() ? '&larr;' : '&rarr;',
								'type'      => 'list',
								'end_size'  => 3,
								'mid_size'  => 3,
							)
						);
					}
				} else {
					$per_page = 0;
				}

				// retrieve items to print.
				$wishlist_items = $wishlist->get_items( $per_page, $offset );

				$additional_params = wp_parse_args(
					array(
						// wishlist items.
						'count'                            => $count,
						'wishlist_items'                   => $wishlist_items,

						// wishlist data.
						'wishlist'                         => $wishlist,
						'is_default'                       => $wishlist->get_is_default(),
						'wishlist_token'                   => $wishlist->get_token(),
						'wishlist_id'                      => $wishlist->get_id(),
						'is_private'                       => $wishlist->has_privacy( 'private' ),

						// page data Wishlist.
						'current_page'                     => $current_page,
						'page_links'                       => isset( $page_links ) && ! $no_interactions ? $page_links : false,

						// user data.
						'is_user_owner'                    => $is_user_owner,

						// template data.
						'enable_drag_n_drop'               => $enable_drag_n_drop,
						'form_action'                      => WLFMC()->get_wc_wishlist_url(),

						// labels.
						'action_label'                     => $options->get_option( 'action_label', __( 'Actions', 'wc-wlfmc-wishlist' ) ),
						'action_add_to_cart_label'         => $options->get_option( 'action_add_to_cart_label', __( 'Add to cart', 'wc-wlfmc-wishlist' ) ),
						'action_remove_label'              => $options->get_option( 'action_remove_label', __( 'Remove', 'wc-wlfmc-wishlist' ) ),
						'all_add_to_cart_label'            => $options->get_option( 'all_add_to_cart_label', __( 'Add all to cart', 'wc-wlfmc-wishlist' ) ),
						'all_add_to_cart_tooltip_label'    => $options->get_option( 'all_add_to_cart_tooltip_label', __( 'All products on the Wishlist will be added to the cart (except out-of-stock products and variable products without specifying the variable).', 'wc-wlfmc-wishlist' ) ),
						'apply_label'                      => $options->get_option( 'apply_label', __( 'Apply', 'wc-wlfmc-wishlist' ) ),
						'share_on_label'                   => $options->get_option( 'share_on_label', __( 'Share on:', 'wc-wlfmc-wishlist' ) ),
						'copy_field_label'                 => $options->get_option( 'copy_field_label', __( 'Or copy the link', 'wc-wlfmc-wishlist' ) ),
						'copy_button_text'                 => $options->get_option( 'copy_button_text', __( 'Copy', 'wc-wlfmc-wishlist' ) ),
						'share_tooltip'                    => $options->get_option( 'share_tooltip', __( 'Share', 'wc-wlfmc-wishlist' ) ),
						'share_popup_title'                => $options->get_option( 'share_popup_title', __( 'Share your list', 'wc-wlfmc-wishlist' ) ),
						'share_on_facebook_tooltip_label'  => $options->get_option( 'share_on_facebook_tooltip_label', __( 'Share on facebook', 'wc-wlfmc-wishlist' ) ),
						'share_on_messenger_tooltip_label' => $options->get_option( 'share_on_messenger_tooltip_label', __( 'Share with messenger', 'wc-wlfmc-wishlist' ) ),
						'share_on_twitter_tooltip_label'   => $options->get_option( 'share_on_twitter_tooltip_label', __( 'Share on twitter', 'wc-wlfmc-wishlist' ) ),
						'share_on_whatsapp_tooltip_label'  => $options->get_option( 'share_on_whatsapp_tooltip_label', __( 'Share on whatsApp', 'wc-wlfmc-wishlist' ) ),
						'share_on_telegram_tooltip_label'  => $options->get_option( 'share_on_telegram_tooltip_label', __( 'Share on Telegram', 'wc-wlfmc-wishlist' ) ),
						'share_on_email_tooltip_label'     => $options->get_option( 'share_on_email_tooltip_label', __( 'Share with email', 'wc-wlfmc-wishlist' ) ),
						'share_on_copy_link_tooltip_label' => $options->get_option( 'share_on_copy_link_tooltip_label', __( 'Click to copy the link', 'wc-wlfmc-wishlist' ) ),
						'share_on_download_pdf_tooltip_label' => $options->get_option( 'share_on_download_pdf_tooltip_label', __( 'Download pdf', 'wc-wlfmc-wishlist' ) ),
					),
					$additional_params
				);
				// share options.
				$enable_share = $enable_share && ! $wishlist->has_privacy( 'private' );
				$share_items  = ! is_array( $share_items ) ? array_map( 'trim', explode( ',', $share_items ) ) : $share_items;
				// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.urlencode_urlencode
				if ( ! $no_interactions && $enable_share && is_array( $share_items ) && ! empty( $share_items ) ) {
					$share_link_url = apply_filters( 'wlfmc_shortcode_share_link_url', $wishlist->get_share_url(), $wishlist );
					$socials_title  = apply_filters( 'wlfmc_socials_title', urlencode( $socials_title ) );
					$share_atts     = array(
						'share_socials_title' => $socials_title,
						'share_link_url'      => $share_link_url,
					);

					foreach ( $share_items as $item ) {
						$share_atts[ 'share_' . $item . '_icon' ] = apply_filters( 'wlfmc_socials_share_' . $item . '_icon', '<i class="wlfmc-icon-' . $item . '"></i>' );
					}
					if ( wp_is_mobile() ) {
						$share_whatsapp_url = 'whatsapp://send?text=' . $socials_title . ' – ' . urlencode( $share_link_url );
						$share_telegram_url = 'tg://msg_url?url=' . urlencode( $share_link_url ) . '&text=' . $socials_title;
					} else {
						$share_whatsapp_url = 'https://web.whatsapp.com/send?text=' . $socials_title . ' – ' . urlencode( $share_link_url );
						$share_telegram_url = 'https://t.me/share/url?url=' . urlencode( $share_link_url ) . '&text=' . $socials_title;
					}

					$share_atts['share_whatsapp_url'] = $share_whatsapp_url;
					$share_atts['share_telegram_url'] = $share_telegram_url;
					$share_atts['share_pdf_url']      = $wishlist->get_download_pdf_url();

					$additional_params['share_enabled'] = true;
					$additional_params['share_items']   = $share_items;
					$additional_params['share_atts']    = $share_atts;
				}
				// phpcs:enable WordPress.PHP.DiscouragedPHPFunctions.urlencode_urlencode
				$additional_params['login_notice'] = false;
				if ( 'all' === $who_can_see_wishlist_options && $login_notice && $is_user_owner && ! is_user_logged_in() ) {

					$additional_params['login_notice']         = true;
					$additional_params['login_notice_class']   = 'wlfmc-guest-notice-wrapper';
					$additional_params['login_notice_content'] = $options->get_option( 'login_notice_content', '' );
					$additional_params['login_notice_buttons'] = $options->get_option( 'login_notice_buttons', '' );
					$additional_params['login_url']            = $options->get_option( 'login_url', wp_login_url(), true );
					$additional_params['signup_url']           = $options->get_option( 'signup_url', wp_registration_url(), true );

				}
			} else {
				$additional_params['enable_drag_n_drop'] = false;
				$additional_params['login_notice']       = false;
			}

			// filter params.
			$additional_params = apply_filters( 'wlfmc_wishlist_params', $additional_params, $action, $action_params, $pagination, $per_page, $atts, $wishlist, $options );

			$additional_params['enable_drag_n_drop'] = defined( 'MC_WLFMC_PREMIUM' ) && $is_user_owner && wlfmc_is_true( $additional_params['enable_drag_n_drop'] );
			$additional_params['product_move']       = defined( 'MC_WLFMC_PREMIUM' ) && $is_user_owner && wlfmc_is_true( $additional_params['product_move'] );
			$additional_params['product_copy']       = defined( 'MC_WLFMC_PREMIUM' ) && ! $is_user_owner && wlfmc_is_true( $additional_params['product_copy'] );

			$atts = array_merge(
				$atts,
				$additional_params
			);

			$atts['fragment_options'] = WLFMC_Frontend()->format_fragment_options( $atts, 'wishlist' );

			// apply filters for add to cart buttons.
			WLFMC_Frontend()->alter_add_to_cart_button();

			// sets that we're in the wishlist template.
			$wlfmc_is_wishlist = true;

			$template = wlfmc_get_template( 'mc-wishlist.php', $atts, true );

			// we're not in wishlist template anymore.
			$wlfmc_is_wishlist = false;

			// remove filters for add to cart buttons.
			WLFMC_Frontend()->restore_add_to_cart_button();

			// enqueue scripts.
			WLFMC_Frontend()->enqueue_scripts();

			$wlfmc_wishlist_token = null;

			return apply_filters( 'wlfmc_wishlist_html', $template, $atts );
		}

		/**
		 * Return "Add to Wishlist" button.
		 *
		 * @param array  $atts Array of parameters for the shortcode.
		 * @param string $content Shortcode content (usually empty).
		 *
		 * @return string  Rendered shortcode
		 *
		 * @version 1.4.0
		 */
		public static function add_to_wishlist( $atts, $content = null ) {
			global $post, $product;

			// product object.
			$current_product = ( isset( $atts['product_id'] ) && '' !== trim( $atts['product_id'] ) ) ? wc_get_product( $atts['product_id'] ) : false;
			$current_product = $current_product ? $current_product : ( $product instanceof WC_Product ? $product : false );
			$current_product = $current_product ? $current_product : ( $post instanceof WP_Post ? wc_get_product( $post->ID ) : false );

			if ( ! $current_product instanceof WC_Product ) {
				return '';
			}

			$current_product_id = $current_product->get_id();

			// product parent.
			$current_product_parent = $current_product->is_type( 'variation' ) ? $current_product->get_parent_id() : 0;

			if ( is_array( $atts ) ) {
				$atts['product_id'] = $current_product_id;
			}

			$options          = new MCT_Options( 'wlfmc_options' );
			$wishlist_enabled = wlfmc_is_true( $options->get_option( 'wishlist_enable', '1' ) );

			if ( ! $wishlist_enabled || ( ! $options->get_option( 'enable_for_outofstock_product' ) && ! $current_product->is_in_stock() ) ) {
				return '';
			}

			$who_can_see_wishlist_options = $options->get_option( 'who_can_see_wishlist_options', 'all' );
			if ( ( 'users' === $who_can_see_wishlist_options && ! is_user_logged_in() ) ) {
				return '';
			}

			$disable_wishlist               = false;
			$force_user_to_login            = wlfmc_is_true( $options->get_option( 'force_user_to_login', false ) );
			$click_wishlist_button_behavior = $options->get_option( 'click_wishlist_button_behavior', 'just-add' );
			$after_second_click             = $options->get_option( 'after_second_click', 'remove' );
			$already_in_wishlist            = $options->get_option( 'already_in_wishlist_text' );
			$product_added                  = $options->get_option( 'product_added_text' );
			$loop_position                  = isset( $atts['position'] ) ? esc_attr( $atts['position'] ) : $options->get_option( 'loop_position', 'after_add_to_cart' );
			$single_position                = isset( $atts['position'] ) ? esc_attr( $atts['position'] ) : $options->get_option( 'wishlist_button_position', 'after_add_to_cart' );
			$is_single                      = isset( $atts['is_single'] ) ? wlfmc_is_true( $atts['is_single'] ) : wlfmc_is_single();
			$is_gutenberg                   = isset( $atts['is_gutenberg'] ) && wlfmc_is_true( $atts['is_gutenberg'] );
			$is_elementor                   = isset( $atts['is_elementor'] ) && wlfmc_is_true( $atts['is_elementor'] );
			$gutenberg_position             = $options->get_option( 'gutenberg_position', 'after_add_to_cart' );
			$button_label_add               = $options->get_option( 'button_label_add', esc_html__( 'Add to wishlist', 'wc-wlfmc-wishlist' ) );
			$button_label_view              = $options->get_option( 'button_label_view', esc_html__( 'View My Wishlist', 'wc-wlfmc-wishlist' ) );
			$button_label_remove            = $options->get_option( 'button_label_remove', esc_html__( 'Remove from wishlist', 'wc-wlfmc-wishlist' ) );
			$button_label_exists            = $options->get_option( 'button_label_exists', esc_html__( 'Already in wishlist', 'wc-wlfmc-wishlist' ) );

			if ( $is_single ) {
				// button icon.
				$icon        = $options->get_option( 'icon_name_single', 'heart' );
				$added_icon  = $icon . '-o';
				$is_svg_icon = false;
				if ( 'custom' === $icon ) {
					$is_svg_icon = true;
					$icon        = $options->get_option( 'icon_svg_single' );
					$added_icon  = $options->get_option( 'icon_svg_added_single' );
				}
				$separate_icon_and_text = $options->get_option( 'separate_icon_and_text_single', false );
				$button_type            = $options->get_option( 'button_type_single', 'icon' );
				$button_theme           = $options->get_option( 'button_theme_single', true );
				$position_class         = ' wlfmc_position_' . $single_position;
				$tooltip                = $options->get_option( 'enable_tooltip_single', true );
				$tooltip_direction      = $options->get_option( 'tooltip_direction_single', 'top' );

			} else {
				// button icon.
				$icon        = $options->get_option( 'icon_name_loop', 'heart' );
				$added_icon  = $icon . '-o';
				$is_svg_icon = false;
				if ( 'custom' === $icon ) {
					$is_svg_icon = true;
					$icon        = $options->get_option( 'icon_svg_loop' );
					$added_icon  = $options->get_option( 'icon_svg_added_loop' );
				}
				$separate_icon_and_text = $options->get_option( 'separate_icon_and_text_loop', false );
				$button_type            = $options->get_option( 'button_type_loop', 'icon' );
				$button_theme           = $options->get_option( 'button_theme_loop', true );
				$position_class         = ' wlfmc_position_' . $loop_position;
				$tooltip                = $options->get_option( 'enable_tooltip_loop', true );
				$tooltip_direction      = $options->get_option( 'tooltip_direction_loop', 'top' );
				$enabled_on_loop        = wlfmc_is_true( $options->get_option( 'show_on_loop', true ) );
				if ( ! $enabled_on_loop ) {
					return '';
				}
			}
			$tooltip_type           = wlfmc_is_true( $options->get_option( 'tooltip_custom_style', '0' ) ) ? 'custom' : 'default';
			$separate_icon_and_text = isset( $atts['separate_icon_and_text'] ) ? wlfmc_is_true( $atts['separate_icon_and_text'] ) : wlfmc_is_true( $separate_icon_and_text );
			$button_theme           = isset( $atts['button_theme'] ) ? wlfmc_is_true( $atts['button_theme'] ) : wlfmc_is_true( $button_theme );
			$tooltip                = isset( $atts['enable_tooltip'] ) ? wlfmc_is_true( $atts['enable_tooltip'] ) : wlfmc_is_true( $tooltip );
			$button_type            = isset( $atts['button_type'] ) ? $atts['button_type'] : $button_type;
			$tooltip_direction      = isset( $atts['tooltip_direction'] ) ? $atts['tooltip_direction'] : $tooltip_direction;
			$tooltip_class          = ( true === $tooltip && 'icon' === $button_type ) ? ' wlfmc-tooltip wlfmc-tooltip-' . $tooltip_direction : '';

			// button class.

			$classes = apply_filters( 'wlfmc_add_to_wishlist_button_classes', 'wlfmc_add_to_wishlist button alt ' );

			if ( ( 'all' === $who_can_see_wishlist_options && $force_user_to_login && ! is_user_logged_in() ) ) {
				$disable_wishlist = true;
				$classes         .= ' wlfmc-btn-login-need';
			}

			if ( $separate_icon_and_text && 'both' === $button_type ) {
				$classes .= ' have-sep';
			}

			$container_classes  = $is_single ? ' wlfmc-single-btn' : ' wlfmc-loop-btn';
			$container_classes .= $is_elementor ? ' is-elementor' : '';
			$container_classes .= ! $is_elementor ? $position_class : '';
			$container_classes .= ' wlfmc-btn-type-' . $button_type;

			if ( ! $is_elementor && ( ( in_array( $gutenberg_position, array( 'image_top_left', 'image_top_right' ), true ) && true === $is_gutenberg ) || ( in_array( $loop_position, array( 'image_top_left', 'image_top_right' ), true ) && ! $is_single ) || ( in_array( $single_position, array( 'image_top_left', 'image_top_right', 'image_bottom_left', 'image_bottom_right' ), true ) && $is_single ) ) ) {
				$container_classes .= ' wlfmc-top-of-image ';
				if ( $is_single ) {
					$container_classes .= $single_position;
				} elseif ( $is_gutenberg ) {
					$container_classes .= $gutenberg_position;
				} else {
					$container_classes .= $loop_position;
				}
			}

			switch ( $after_second_click ) {
				case 'remove':
					$container_classes .= ' show-remove-after-add';
					break;
				case 'wishlist':
					$container_classes .= ' show-browse-after-add';
					break;
				case 'error':
					$container_classes .= ' show-exists-after-add';
					break;
			}

			if ( ! $button_theme ) {
				$classes = str_replace( 'button', 'wlfmc-custom-btn', $classes );
			}

			$classes_add    = apply_filters( 'wlfmc_button_classes_add', $classes );
			$classes_exists = apply_filters( 'wlfmc_button_classes_exists', str_replace( 'wlfmc_add_to_wishlist', '', $classes ) );

			$data_remove_url = add_query_arg( 'remove_from_wishlist', '#product_id', wlfmc_get_current_url() );
			$data_add_url    = add_query_arg( 'add_to_wishlist', '#product_id', wlfmc_get_current_url() );
			$exists          = wp_doing_ajax() && WLFMC()->is_product_in_wishlist( $current_product_id );
			if ( $exists ) {
				$container_classes .= ' exists';
			}
			// get default wishlist url.
			$wishlist_url = WLFMC()->get_wc_wishlist_url();

			// get product type.
			$product_type = $current_product->get_type();

			$additional_params = array(
				'base_url'                      => wlfmc_get_current_url(),
				'wishlist_url'                  => $wishlist_url,
				'container_classes'             => $container_classes,
				'product_id'                    => $current_product_id,
				'parent_product_id'             => $current_product_parent ? $current_product_parent : $current_product_id,
				'product_type'                  => $product_type,
				'already_in_wishlist_text'      => apply_filters( 'wlfmc_product_already_in_wishlist_text_button', $already_in_wishlist ),
				'product_added_text'            => apply_filters( 'wlfmc_product_added_to_wishlist_message_button', $product_added ),
				'available_multi_list'          => false,
				'is_svg_icon'                   => $is_svg_icon,
				'icon_prefix_class'             => 'wlfmc-icon-',
				'disable_wishlist'              => $disable_wishlist,
				'tooltip_class'                 => $tooltip_class,
				'tooltip_type'                  => $tooltip_type,
				'enabled_popup'                 => false,
				'enable_for_outofstock_product' => $options->get_option( 'enable_for_outofstock_product' ),
				'icon'                          => $icon,
				'added_icon'                    => $added_icon,
				'classes_exists'                => $classes_exists,
				'classes_add'                   => $classes_add,
				'button_label_add'              => apply_filters( 'wlfmc_button_label_add', $button_label_add, $atts ),
				'button_label_remove'           => apply_filters( 'wlfmc_button_label_remove', $button_label_remove, $atts ),
				'button_label_view'             => apply_filters( 'wlfmc_button_label_view', $button_label_view, $atts ),
				'button_label_exists'           => apply_filters( 'wlfmc_button_label_exists', $button_label_exists, $atts ),
				'tooltip_label_add'             => apply_filters( 'wlfmc_tooltip_label_add', $button_label_add, $atts ),
				'tooltip_label_remove'          => apply_filters( 'wlfmc_tooltip_label_remove', $button_label_remove, $atts ),
				'tooltip_label_view'            => apply_filters( 'wlfmc_tooltip_label_view', $button_label_view, $atts ),
				'tooltip_label_exists'          => apply_filters( 'wlfmc_tooltip_label_exists', $button_label_exists, $atts ),
				'data_remove_url'               => $data_remove_url,
				'data_add_url'                  => $data_add_url,
			);

			// popup.
			if ( 'open-popup' === $click_wishlist_button_behavior ) {

				$popup_title        = $options->get_option( 'popup_title' );
				$popup_content      = $options->get_option( 'popup_content' );
				$popup_position     = $options->get_option( 'popup_position', 'center-center' );
				$popup_size         = $options->get_option( 'popup_size', 'small' );
				$popup_image        = $options->get_option( 'popup_image' );
				$popup_image_size   = $options->get_option( 'popup_image_size', 'medium' );
				$popup_image_width  = $options->get_option( 'popup_image_width' );
				$popup_image_height = $options->get_option( 'popup_image_height' );
				$buttons            = $options->get_option( 'popup_buttons' );
				$use_featured_image = wlfmc_is_true( $options->get_option( 'use_featured_image' ) );
				$popup_image_size   = ( 'manual' === $popup_image_size && '' !== $popup_image_width && '' === $popup_image_height ) ? array(
					$popup_image_width,
					$popup_image_height,
				) : $popup_image_size;
				$image_attributes   = wp_get_attachment_image_src( $popup_image, $popup_image_size );
				$image_attributes   = ( $use_featured_image ) ? wp_get_attachment_image_src( $current_product->get_image_id(), $popup_image_size ) : $image_attributes;
				$popup_image_src    = $image_attributes ? $image_attributes[0] : '';
				$popup_position     = explode( '-', $popup_position );
				$popup_vertical     = $popup_position[0];
				$popup_horizontal   = $popup_position[1];

				$placeholders  = array(
					'{product_name}'  => $current_product->get_title(),
					'{product_price}' => $current_product->get_price_html(),
				);
				$popup_title   = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $popup_title );
				$popup_content = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $popup_content );

				$additional_params = array_merge(
					$additional_params,
					array(
						'enabled_popup'      => true,
						'popup_vertical'     => $popup_vertical,
						'popup_horizontal'   => $popup_horizontal,
						'popup_size'         => $popup_size,
						'popup_image'        => $popup_image_src,
						'popup_title'        => $popup_title,
						'popup_content'      => $popup_content,
						'product_title'      => $current_product->get_title(),
						'product_price'      => $current_product->get_price_html(),
						'use_featured_image' => $use_featured_image,
						'popup_image_size'   => $popup_image_size,
						'login_url'          => $options->get_option( 'login_url', wp_login_url(), true ),
						'signup_url'         => $options->get_option( 'signup_url', wp_registration_url(), true ),
						'buttons'            => $buttons,

					)
				);
			}

			$additional_params = apply_filters( 'wlfmc_add_to_wishlist_params', $additional_params, $atts );

			$atts = shortcode_atts(
				$additional_params,
				$atts
			);

			$atts['enabled_popup']    = wlfmc_is_true( $atts['enabled_popup'] );
			$atts['is_svg_icon']      = wlfmc_is_true( $atts['is_svg_icon'] );
			$atts['disable_wishlist'] = wlfmc_is_true( $atts['disable_wishlist'] );

			if ( in_array( $button_type, array( 'icon', 'both' ), true ) ) {
				$icon_html       = ( ! $atts['is_svg_icon'] ) ? '<i class="' . $atts['icon_prefix_class'] . $atts['icon'] . '"></i>' : $atts['icon'];
				$added_icon_html = ( ! $atts['is_svg_icon'] ) ? '<i class="' . $atts['icon_prefix_class'] . $atts['added_icon'] . '"></i>' : $atts['added_icon'];
			} else {
				$atts['is_svg_icon'] = false;
				$icon_html           = '';
				$added_icon_html     = '';
			}
			if ( 'icon' === $button_type ) {
				$atts['button_label_add']    = '';
				$atts['button_label_remove'] = '';
				$atts['button_label_view']   = '';
				$atts['button_label_exists'] = '';
			}

			$atts['icon']       = apply_filters( 'wlfmc_icon_html', $icon_html, $atts );
			$atts['added_icon'] = apply_filters( 'wlfmc_added_icon_html', $added_icon_html, $atts );

			$template = wlfmc_get_template( 'mc-add-to-wishlist.php', $atts, true );

			// enqueue scripts.
			WLFMC_Frontend()->enqueue_scripts();

			return apply_filters( 'wlfmc_add_to_wishlist_button_html', $template, $wishlist_url, $product_type, $atts );
		}


		/**
		 * Return "wishlist" product counter.
		 *
		 * @param array  $atts Array of parameters for the shortcode.
		 * @param string $content Shortcode content (usually empty).
		 *
		 * @return string  Rendered shortcode
		 *
		 * @since 1.4.0
		 * @version 1.6.6
		 */
		public static function wishlist_counter( $atts, $content = null ) {
			$options                      = new MCT_Options( 'wlfmc_options' );
			$wishlist_enabled             = wlfmc_is_true( $options->get_option( 'wishlist_enable', '1' ) );
			$who_can_see_wishlist_options = $options->get_option( 'who_can_see_wishlist_options', 'all' );

			if ( ! $wishlist_enabled || ( 'users' === $who_can_see_wishlist_options && ! is_user_logged_in() ) ) {
				return '';
			}

			$empty_icon    = $options->get_option( 'counter_icon', 'heart' );
			$has_item_icon = $empty_icon . '-o';
			$is_svg_icon   = false;
			if ( 'custom' === $empty_icon ) {
				$is_svg_icon   = true;
				$empty_icon    = $options->get_option( 'counter_icon_svg_zero' );
				$has_item_icon = $options->get_option( 'counter_icon_svg_added' );
			}
			$enable_mini_wishlist   = $options->get_option( 'display_mini_wishlist_for_counter', 'on-hover' );
			$show_products          = 'counter-only' !== $enable_mini_wishlist;
			$show_list_on_hover     = 'on-click' !== $enable_mini_wishlist;
			$dropdown_position_mode = $options->get_option( 'mini_wishlist_position_mode', 'fixed' );

			$wishlist          = WLFMC_Wishlist_Factory::get_default_wishlist();
			$additional_params = array(
				'base_url'                  => wlfmc_get_current_url(),
				// wishlist data.
				'wishlist'                  => false,
				'wishlist_token'            => '',
				'wishlist_id'               => false,
				'wishlist_items'            => array(),
				'is_private'                => false,
				'is_elementor'              => false,
				'show_icon'                 => true,
				'show_button'               => true,
				'show_totals'               => true,
				'dropdown_products'         => true,
				'show_products'             => $show_products,
				'show_list_on_hover'        => $show_list_on_hover,
				'position_mode'             => $dropdown_position_mode,
				'unique_id'                 => wp_unique_id(),
				'link_class'                => '',
				'container_class'           => ' wlfmc-products-counter-wrapper ',
				'show_text'                 => $options->get_option( 'enable_counter_text', '1' ),
				'show_counter'              => $options->get_option( 'enable_counter_products_number', '1' ),
				'counter_text'              => $options->get_option( 'counter_text', '' ),
				'button_text'               => $options->get_option( 'counter_button_text', esc_html__( 'View my Wishlist', 'wc-wlfmc-wishlist' ) ),
				'total_text'                => $options->get_option( 'counter_total_text', esc_html__( 'Total products', 'wc-wlfmc-wishlist' ) ),
				'per_page'                  => $options->get_option( 'counter_per_page_products_count', '4' ),
				'empty_icon'                => $empty_icon,
				'has_item_icon'             => $has_item_icon,
				'is_svg_icon'               => $is_svg_icon,
				'icon_prefix_class'         => 'wlfmc-icon-',
				'hide_zero_products_number' => $options->get_option( 'hide_counter_zero_products_number', '1' ),
				'hide_counter_if_no_items'  => $options->get_option( 'hide_counter_if_no_items', '0' ),
				'products_number_position'  => $options->get_option( 'counter_products_number_position', 'right' ),
				'add_link_title'            => $options->get_option( 'enable_counter_add_link_title', '1' ),
				'wishlist_link_position'    => $options->get_option( 'counter_mini_wishlist_link_position', 'after' ),
				'wishlist_url'              => WLFMC()->get_wc_wishlist_url(),
				'empty_wishlist_content'    => $options->get_option( 'counter_empty_wishlist_content', esc_html__( 'You have not added any products to your wishlist.', 'wc-wlfmc-wishlist' ) ),
			);

			$additional_params = apply_filters( 'wlfmc_wishlist_counter_params', $additional_params, $atts );

			$atts = shortcode_atts(
				$additional_params,
				$atts
			);
			if ( $wishlist && $wishlist->current_user_can( 'view' ) ) {
				$atts['wishlist']       = $wishlist;
				$atts['is_default']     = $wishlist->get_is_default();
				$atts['wishlist_token'] = $wishlist->get_token();
				$atts['wishlist_id']    = $wishlist->get_id();
				$atts['is_private']     = $wishlist->has_privacy( 'private' );
				$atts['wishlist_items'] = $wishlist->get_items( $atts['per_page'] ?? $options->get_option( 'counter_per_page_products_count', '4' ), 0 );
			}
			$atts['show_icon']                 = wlfmc_is_true( $atts['show_icon'] );
			$atts['show_button']               = wlfmc_is_true( $atts['show_button'] );
			$atts['show_totals']               = wlfmc_is_true( $atts['show_totals'] );
			$atts['show_list_on_hover']        = wlfmc_is_true( $atts['show_list_on_hover'] ) && ! wp_is_mobile();
			$atts['show_text']                 = wlfmc_is_true( $atts['show_text'] );
			$atts['show_counter']              = wlfmc_is_true( $atts['show_counter'] );
			$atts['show_products']             = wlfmc_is_true( $atts['show_products'] );
			$atts['dropdown_products']         = wlfmc_is_true( $atts['dropdown_products'] );
			$atts['is_svg_icon']               = wlfmc_is_true( $atts['is_svg_icon'] );
			$atts['is_elementor']              = wlfmc_is_true( $atts['is_elementor'] );
			$atts['hide_zero_products_number'] = wlfmc_is_true( $atts['hide_zero_products_number'] );
			$atts['hide_counter_if_no_items']  = defined( 'MC_WLFMC_PREMIUM' ) && wlfmc_is_true( $atts['hide_counter_if_no_items'] );
			$atts['add_link_title']            = wlfmc_is_true( $atts['add_link_title'] );
			$atts['has_item_icon']             = str_replace( '"', "'", htmlspecialchars_decode( $atts['has_item_icon'] ) );
			$atts['empty_icon']                = str_replace( '"', "'", htmlspecialchars_decode( $atts['empty_icon'] ) );
			// change icon when item exists in wishlist.
			if ( $wishlist && 0 < $wishlist->count_items() ) {
				$atts['icon'] = $atts['has_item_icon'];
			} else {
				$atts['icon'] = $atts['empty_icon'];
			}

			$icon_html = ( ! $atts['is_svg_icon'] ) ? '<i class="' . $atts['icon_prefix_class'] . htmlspecialchars_decode( $atts['icon'] ) . '"></i>' : htmlspecialchars_decode( $atts['icon'] );

			// set fragment options.
			$atts['fragment_options'] = WLFMC_Frontend()->format_fragment_options( $atts, 'wishlist_counter' );
			$atts['icon']             = apply_filters( 'wlfmc_wishlist_counter_icon_html', str_replace( '"', "'", $icon_html ), $atts );
			if ( 'fixed' === $atts['position_mode'] ) {
				$dropdown_atts                      = $atts;
				$dropdown_atts['show_icon']         = false;
				$dropdown_atts['show_text']         = false;
				$dropdown_atts['dropdown_products'] = true;

				$atts['dropdown_fragment_options'] = WLFMC_Frontend()->format_fragment_options( $dropdown_atts, 'wishlist_counter' );
			}
			$template = wlfmc_get_template( 'mc-wishlist-counter.php', $atts, true );

			// enqueue scripts.
			WLFMC_Frontend()->enqueue_scripts();

			return apply_filters( 'wlfmc_wishlist_counter_html', $template, $atts );

		}

	}
}

WLFMC_Shortcode::init();
