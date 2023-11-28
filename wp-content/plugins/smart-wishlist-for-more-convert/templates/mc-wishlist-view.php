<?php
/**
 * Wishlist Content template; load template parts basing on the url
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.6.8
 */

/**
 * Template Variables:
 *
 * @var $atts                          array all template variables
 * @var $form_action                   string from action
 * @var $template_part                 string Template part currently being loaded (manage)
 * @var $users_wishlists               WLFMC_Wishlist[] Array of user wishlists
 * @var $fragment_options              array Array of items to use for fragment generation
 * @var $wishlist                      WLFMC_Wishlist Current wishlist
 * @var $wishlist_items                array Array of items to show for current page
 * @var $wishlist_token                string Current wishlist token
 * @var $wishlist_id                   int Current wishlist id
 * @var $redirect_to_cart              bool Redirect after add to cart
 * @var $users_wishlists               array Array of current user wishlists
 * @var $pagination                    bool Whether pagination is enabled
 * @var $pagination_mode               string Pagination mode pagination/load-more
 * @var $load_more_text                string Load more text
 * @var $per_page                      int Items per page
 * @var $current_page                  int Current page
 * @var $page_links                    string page links
 * @var $is_user_owner                 bool Whether current user is wishlist owner
 * @var $additional_info               bool Whether to show Additional info textarea in Ask an estimate form
 * @var $items_show                    array Array of items show
 * @var $available_multi_list          bool Whether multi-list is enabled and available
 * @var $no_interactions               bool
 * @var $share_enabled                 bool Whether share buttons should appear
 * @var $share_atts                    array Array of options; shows which share links should be shown
 * @var $share_position                string Share position
 * @var $share_items                   array Array of active share buttons
 * @var $login_notice_class            string Notice class
 * @var $login_notice                  bool Whether to show quest notice
 * @var $login_notice_content          string Quest notice content
 * @var $login_notice_buttons          array Array of quest notice buttons
 * @var $signup_url                    string sign-up url
 * @var $login_url                     string login url
 * @var $var                           array Array of attributes that needs to be sent to sub-template
 * @var $empty_wishlist_title          string title of no product in wishlist
 * @var $empty_wishlist_content        string string of no product in wishlist
 * @var $enable_actions                bool Whether All together Actions is enabled
 * @var $enable_all_add_to_cart        bool Whether All add to cart is enabled
 * @var $view_mode                     string wishlist view mode grid/list
 * @var $wishlist_class                string wishlist class
 * @var $wishlist_pagination_class     string wishlist pagination class
 * @var $unique_id                     string wishlist Unique ID
 * @var $action_label                  string "Actions" text
 * @var $action_add_to_cart_label      string "Add to cart" tex
 * @var $action_remove_label           string "Remove" text
 * @var $all_add_to_cart_label         string "Add all to cart" tex
 * @var $all_add_to_cart_tooltip_label string "Add all to cart" tooltip text
 * @var $apply_label                   string "Apply" text
 * @var $share_on_label                string "Share on:" text
 * @var $tooltip_type                  string Tooltip type default/custom
 * @var $tooltip_class                 string Tooltip class
 * @var $share_on_facebook_tooltip_label  string "Share on facebook" text
 * @var $share_on_messenger_tooltip_label string "Share with messenger" text
 * @var $share_on_twitter_tooltip_label   string "Share on Twitter" text
 * @var $share_on_whatsapp_tooltip_label  string "Share on whatsApp" text
 * @var $share_on_telegram_tooltip_label  string "Share on Telegram" text
 * @var $share_on_email_tooltip_label     string "Share with email" text
 * @var $share_on_copy_link_tooltip_label string "Click to copy the link" text
 * @var $share_on_download_pdf_tooltip_label string "Download pdf" text
 * @var $base_url                      string current page url
 * @var $is_cache_enabled              string|bool Cache enabled state
 * @var $enable_drag_n_drop            bool Whether enabled drag & drop
 * @var $product_move                  bool Whether enabled product move to another list
 * @var $product_copy                  bool Whether enabled product copy to another list
 * @var $move_all_to_list_label        string move all to list label
 * @var $copy_all_to_list_label        string copy all to list label
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form id="wlfmc-wishlist-form" action="<?php echo esc_attr( $form_action ); ?>" method="post" class="wlfmc-wishlist-form wlfmc-wishlist-<?php echo esc_attr( $unique_id ); ?> wlfmc-wishlist-fragment woocommerce on-first-load" data-fragment-options="<?php echo esc_attr( wp_json_encode( $fragment_options ) ); ?>" data-fragment-ref="wishlist">
	<?php
	$notices = false;
	if ( function_exists( 'wc_print_notices' ) && isset( WC()->session ) && ( ! wlfmc_is_true( $is_cache_enabled ) || wp_doing_ajax() ) ) {
		wc_print_notices();
	}
	if ( function_exists( 'wc_notice_count' ) && isset( WC()->session ) ) {
		$notices = wc_notice_count();
	}


	?>
	<?php if ( true === $login_notice ) : ?>
		<div class="<?php echo esc_attr( $login_notice_class ); ?>">
			<?php if ( isset( $login_notice_content ) && '' !== $login_notice_content ) : ?>
				<div class="wlfmc-notice-content">
					<?php echo do_shortcode( $login_notice_content ); ?>
				</div>
			<?php endif; ?>
			<?php if ( isset( $login_notice_buttons ) ) : ?>
				<div class="wlfmc-notice-buttons">
					<?php if ( is_array( $login_notice_buttons ) && ! empty( $login_notice_buttons ) ) : ?>
						<?php foreach ( $login_notice_buttons as $k => $button ) : ?>
							<?php
							switch ( $button['link'] ) {
								case 'signup':
									echo ! is_user_logged_in() ? '<a href="' . esc_url( $signup_url ) . '" class="button wlfmc-btn wlfmc_btn_' . esc_attr( $k ) . '">' . esc_attr( $button['label'] ) . '</a>' : '';
									break;
								case 'login':
								case 'signup-login':
									echo ! is_user_logged_in() ? '<a href="' . esc_url( $login_url ) . '" class="button  wlfmc-btn wlfmc_btn_' . esc_attr( $k ) . '">' . esc_attr( $button['label'] ) . '</a>' : '';
									break;
								case 'custom-link':
									echo trim( $button['custom-link'] ) !== '' ? '<a href="' . esc_url( $button['custom-link'] ) . '" rel="nofollow" class="wlfmc-btn button wlfmc_btn_' . esc_attr( $k ) . '">' . esc_attr( $button['label'] ) . '</a>' : '';
									break;
							}
							?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="wlfmc-wishlist-table-wrapper">

		<?php do_action( 'wlfmc_before_wishlist_table', $wishlist, $atts ); ?>

		<table
			class="wlfmc-wishlist-table wlfmc-wishlist-items-wrapper wlfmc-list <?php echo $enable_drag_n_drop ? 'sortable' : ''; ?> <?php echo wp_is_mobile() ? 'is-mobile' : ''; ?> view-mode-<?php echo esc_attr( $view_mode ); ?>  <?php echo esc_attr( $wishlist_class ); ?>"
			data-pagination="<?php echo esc_attr( $pagination ); ?>"
			data-per-page="<?php echo esc_attr( $per_page ); ?>"
			data-page="<?php echo esc_attr( $current_page ); ?>"
			data-id="<?php echo esc_attr( $wishlist_id ); ?>"
			data-wishlist-type="<?php echo $wishlist ? esc_attr( $wishlist->get_type() ) : ''; ?>"
			data-customer-id="<?php echo $wishlist ? esc_attr( $wishlist->get_customer_id() ) : ''; ?>"
			data-is-owner="<?php echo esc_attr( $is_user_owner ); ?>"
			data-token="<?php echo esc_attr( $wishlist_token ); ?>">

			<tbody class="wishlist-items-wrapper <?php echo ( ! $wishlist || ! $wishlist->has_items() ) ? 'wishlist-empty' : ''; ?>">

			<?php if ( $wishlist && $wishlist->has_items() ) : ?>
				<?php foreach ( $wishlist_items as $item ) : ?>
					<?php
					// phpcs:ignore Generic.Commenting.DocComment
					/**
					 * @var $item WLFMC_Wishlist_Item
					 */
					global $product;
					$product      = $item->get_product();
					$availability = $product->get_availability();
					$stock_status = $availability['class'] ?? false;
					$cart_item    = $item->get_cart_item();
					$meta         = $item->get_product_meta();
					$permalink    = apply_filters( 'woocommerce_cart_item_permalink', $product->get_permalink( $item->get_cart_item( true ) ), $cart_item, '' );
					ob_start();
					do_action( 'wlfmc_item_meta_data', $meta, $cart_item, $wishlist );
					$item_meta_date = ob_get_clean();
					$item_meta_date = '' !== wlfmc_remove_empty_html_tags( $item_meta_date ) ? $item_meta_date : '';
					?>
					<?php if ( $product->exists() ) : ?>
						<tr id="wlfmc-row-<?php echo esc_attr( $item->get_product_id() ); ?>"
							data-row-id="<?php echo esc_attr( $item->get_product_id() ); ?>"
							data-item-id="<?php echo esc_attr( $item->get_id() ); ?>"
							class="wlfmc-table-item">
							<?php if ( $enable_drag_n_drop ) : ?>
								<td class="sortable-handle">
									<i class="wlfmc-icon-drag-drop"></i>
									<input type="hidden" name="items[<?php echo esc_attr( $item->get_product_id() ); ?>][position]" value="<?php echo esc_attr( $item->get_position() ); ?>"/>
								</td>
							<?php endif; ?>
							<td class="first-column">
								<div class="d-flex f-center-items wlfmc-thumbnail-wrapper">
									<div class="d-flex f-center-item wlfmc-action-icons">
										<?php if ( in_array( 'product-checkbox', $items_show, true ) ) : ?>
											<?php do_action( 'wlfmc_table_before_product_checkbox', $item, $wishlist ); ?>

											<label class="product-checkbox checkbox-label">
												<input type="checkbox" value="yes" name="items[<?php echo esc_attr( $item->get_product_id() ); ?>][cb]">
												<span></span>
											</label>
											<?php do_action( 'wlfmc_table_after_product_checkbox', $item, $wishlist ); ?>

										<?php endif; ?>
										<?php if ( $product_move ) : ?>
											<a href="#" class="product-move wlfmc-popup-trigger" data-item-id="<?php echo esc_attr( $item->get_id() ); ?>" data-popup-id="move_popup" title="<?php echo esc_html( apply_filters( 'wlfmc_product_move_title', esc_html__( 'Product Move', 'wc-wlfmc-wishlist' ) ) ); ?>">
												<i class="wlfmc-icon-move-to-list"></i>
											</a>
										<?php endif; ?>
										<?php if ( $product_copy ) : ?>
                                            <a href="#" class="product-copy <?php echo $available_multi_list ? 'wlfmc-popup-trigger' : 'wlfmc_copy_to_default_list';?>" data-wishlist-id="<?php echo esc_attr( $wishlist_id ); ?>"  data-item-id="<?php echo esc_attr( $item->get_id() ); ?>" data-popup-id="copy_popup" title="<?php echo esc_html( apply_filters( 'wlfmc_product_copy_title', esc_html__( 'Product Copy', 'wc-wlfmc-wishlist' ) ) ); ?>">
                                                <i class="wlfmc-icon-move-to-list"></i>
                                            </a>
										<?php endif; ?>
										<?php if ( '' !== $item_meta_date ) : ?>
											<a href="#" class="product-components" title="<?php echo esc_html( apply_filters( 'wlfmc_product_components_title', esc_html__( 'Product components', 'wc-wlfmc-wishlist' ) ) ); ?>">
												<i class="wlfmc-icon-components"></i>
											</a>
										<?php endif; ?>
										<?php if ( in_array( 'product-remove', $items_show, true ) && $wishlist->current_user_can( 'remove_from_wishlist' ) ) : ?>
											<?php do_action( 'wlfmc_table_before_product_remove', $item, $wishlist ); ?>

											<a href="<?php echo esc_url( add_query_arg( 'remove_from_wishlist', $item->get_product_id(), $base_url ) ); ?>" class="product-remove remove_from_wishlist wlfmc-remove-from-list" title="<?php echo esc_html( apply_filters( 'wlfmc_remove_product_wishlist_message_title', esc_html__( 'Remove this product', 'wc-wlfmc-wishlist' ) ) ); ?>" data-e-disable-page-transition>
												<i class="wlfmc-icon-close"></i>
											</a>

											<?php do_action( 'wlfmc_table_after_product_remove', $item, $wishlist ); ?>
										<?php endif; ?>
									</div>
									<?php if ( in_array( 'product-thumbnail', $items_show, true ) ) : ?>
										<?php do_action( 'wlfmc_table_before_product_thumbnail', $item, $wishlist ); ?>

										<a class="product-thumbnail" href="<?php echo esc_url( $permalink ); ?>">
											<?php echo wp_kses_post( $product->get_image() ); ?>
										</a>

										<?php do_action( 'wlfmc_table_after_product_thumbnail', $item, $wishlist ); ?>
									<?php endif; ?>
								</div>
							</td>
							<td class="center-column">
								<div class="d-flex flex-column gap-5">
									<?php do_action( 'wlfmc_table_product_details_start', $item, $wishlist, $atts ); ?>

									<?php if ( in_array( 'product-name', $items_show, true ) ) : ?>
										<?php do_action( 'wlfmc_table_before_product_name', $item, $wishlist ); ?>

										<a class="product-name" href="<?php echo esc_url( $permalink ); ?>">
											<strong>
											<?php
											echo wp_kses_post(
												apply_filters(
													'wlfmc_table_item_product_name',
													is_callable(
														array(
															$product,
															'get_name',
														)
													) ? $product->get_name() : $product->get_title(),
													$product
												)
											);
											?>
											</strong>
											<?php if ( in_array( 'product-stock-status', $items_show, true ) && ! $product->is_in_stock() ) : ?>
												<?php do_action( 'wlfmc_table_before_product_stock', $item, $wishlist ); ?>
												<span class="wishlist-out-of-stock">
													<?php esc_html_e( apply_filters( 'wlfmc_out_of_stock_label', esc_html__( 'Out of stock', 'wc-wlfmc-wishlist' ) ) ); // @codingStandardsIgnoreLine. $product->is_in_stock()?>
												</span>
												<?php do_action( 'wlfmc_table_after_product_stock', $item, $wishlist ); ?>
											<?php endif; ?>
										</a>

										<?php do_action( 'wlfmc_table_after_product_name', $item, $wishlist ); ?>

									<?php endif; ?>

									<?php if ( in_array( 'product-review', $items_show, true ) && $item->get_rating_count() && wc_review_ratings_enabled() ) : ?>
										<?php do_action( 'wlfmc_table_before_product_review', $item, $wishlist ); ?>
										<div class="product-review">
											<?php echo wc_get_rating_html( $item->get_average_rating() ); // phpcs:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</div>
										<?php do_action( 'wlfmc_table_after_product_review', $item, $wishlist ); ?>
									<?php endif; ?>

									<?php if ( in_array( 'product-date-added', $items_show, true ) && $item->get_date_added() ) : ?>
										<!-- Date added -->
										<?php
										// translators: date added label: 1 date added.
										echo '<div class="product-date-added dateadded hide-on-list-mode">' . esc_html( sprintf( __( 'Added on: %s', 'wc-wlfmc-wishlist' ), $item->get_date_added_formatted() ) ) . '</div>';
										?>

									<?php endif; ?>

									<?php if ( in_array( 'product-variation', $items_show, true ) && ( $product->is_type( 'variation' ) || ( ! empty( $meta['attributes'] ) ) ) ) : ?>
										<?php do_action( 'wlfmc_table_before_product_variation', $item, $wishlist ); ?>
										<div class="product-variation">
											<?php
											// phpcs:ignore Generic.Commenting.DocComment
											/**
											 * @var $product WC_Product_Variation
											 */
											echo wc_get_formatted_variation( ! empty( $meta['attributes'] ) ? array_combine( array_map( 'rawurldecode', array_keys( $meta['attributes'] ) ), $meta['attributes'] ) : $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

											?>
										</div>

										<?php do_action( 'wlfmc_table_after_product_variation', $item, $wishlist ); ?>
									<?php endif; ?>

									<?php if ( '' !== $item_meta_date ) : ?>
										<div class="wlfmc-meta-data wlfmc-absolute-meta-data" style="display:none">
											<p class="d-flex  f-center-item space-between">
												<span><i class="wlfmc-icon-components"></i>&nbsp;<?php echo esc_html( apply_filters( 'wlfmc_product_components_title', esc_html__( 'Product components', 'wc-wlfmc-wishlist' ) ) ); ?></span>
												<a href="#" class="close-components"><i class="wlfmc-icon-close"></i></a>
											</p>
											<div class="wlfmc-row-meta-scrollable">
												<?php echo wp_kses_post( $item_meta_date ); ?>
											</div>
										</div>
									<?php endif; ?>

									<?php if ( in_array( 'product-price', $items_show, true ) ) : ?>
										<?php do_action( 'wlfmc_table_before_product_price', $item, $wishlist ); ?>
										<div class="product-price price">
											<?php
											echo wp_kses_post( $item->get_formatted_product_price() );
											echo wp_kses_post( $item->get_price_variation() );
											?>
										</div>
										<?php do_action( 'wlfmc_table_after_product_price', $item, $wishlist ); ?>
									<?php endif; ?>

									<?php do_action( 'wlfmc_table_product_details_end', $item, $wishlist, $atts ); ?>
								</div>
							</td>
							<td class="last-column">
								<div class="d-flex flex-column gap-5">
									<div
										class="d-flex gap-5 f-center-item justify-center f-wrap-on-mobile f-wrap-on-grid">
										<?php if ( in_array( 'product-add-to-cart', $items_show, true ) ) : ?>
											<?php do_action( 'wlfmc_table_before_product_cart', $item, $wishlist ); ?>


											<?php do_action( 'wlfmc_table_product_before_add_to_cart', $item, $wishlist ); ?>

											<!-- Add to cart button -->

											<?php if ( apply_filters( 'wlfmc_product_add_to_cart_button', true, $item, $product, $cart_item ) ) : // ! empty( $meta ) ?>
												<?php

												$add_to_cart_url   = wp_nonce_url(
													add_query_arg(
														array(
															'lid' => $item->get_id(),
															'wid' => $wishlist_id,
															'action' => 'wlfmc_add_to_cart',
														),
														$redirect_to_cart ? wc_get_cart_url() : $form_action
													),
													'wlfmc_add_to_cart'
												);
												$passed_validation = true;
												if ( ! in_array( $product->get_type(), array( 'external', 'variable' ), true ) ) {
													$passed_validation = apply_filters( 'wlfmc_woocommerce_add_to_cart_validation', true, $product, $meta, $item, $cart_item );
													if ( function_exists( 'wc_clear_notices' ) && isset( WC()->session ) && ! $notices ) {
														wc_clear_notices();
													}
												}
												$add_to_cart_text    = apply_filters( 'wlfmc_read_more_text', __( 'Read more', 'woocommerce' ), $product, $passed_validation );
												$select_options_text = apply_filters( 'wlfmc_select_options_text', __( 'Select options', 'woocommerce' ), $product, $passed_validation );
												switch ( $product->get_type() ) {
													case 'external':
														$can_add_with_ajax = false;
														$add_to_cart_url   = $product->add_to_cart_url();
														$add_to_cart_text  = $product->add_to_cart_text();
														break;
													case 'variable':
														$can_add_with_ajax = false;
														$add_to_cart_url   = $permalink;
														$add_to_cart_text  = $product->add_to_cart_text();
														break;
													case 'simple':
														$can_add_with_ajax = $product->is_purchasable() && $passed_validation && ( $product->is_in_stock() || $product->backorders_allowed() );
														$add_to_cart_url   = ! $product->is_purchasable() || ! $passed_validation || ( ! $product->is_in_stock() && ! $product->backorders_allowed() ) ? $permalink : $add_to_cart_url;
														if ( $product->is_purchasable() && ( $product->is_in_stock() || $product->backorders_allowed() ) ) {
															$add_to_cart_text = $passed_validation ? $product->single_add_to_cart_text() : $select_options_text;
														}
														break;
													case 'variation':
														$can_add_with_ajax = $passed_validation && ( $product->is_in_stock() || $product->backorders_allowed() );
														$add_to_cart_url   = ! $passed_validation || ( ! $product->is_in_stock() && ! $product->backorders_allowed() ) ? $permalink : $add_to_cart_url;
														if ( $product->is_purchasable() && ( $product->is_in_stock() || $product->backorders_allowed() ) ) {
															$add_to_cart_text = $passed_validation ? $product->single_add_to_cart_text() : $select_options_text;
														}
														break;
													default:
														$can_add_with_ajax = $product->is_purchasable() && $passed_validation && ( $product->is_in_stock() || $product->backorders_allowed() );
														$add_to_cart_text  = ! $product->is_purchasable() || ! $passed_validation || ( ! $product->is_in_stock() && ! $product->backorders_allowed() ) ? $product->add_to_cart_text() : $product->single_add_to_cart_text();
														$add_to_cart_url   = ! $product->is_purchasable() || ! $passed_validation || ( ! $product->is_in_stock() && ! $product->backorders_allowed() ) ? $permalink : $add_to_cart_url;

												}
												$can_add_with_ajax = apply_filters( 'wlfmc_product_with_meta_add_to_cart_enable_ajax', $can_add_with_ajax, $product, $passed_validation );
												$add_to_cart_text  = apply_filters( 'wlfmc_product_with_meta_add_to_cart_text', $add_to_cart_text, $product, $passed_validation, $item, $meta );
												$add_to_cart_url   = apply_filters( 'wlfmc_product_with_meta_add_to_cart_url', $add_to_cart_url, $permalink, $product, $passed_validation, $redirect_to_cart, $form_action, $item, $meta, $wishlist_id );
												$button_attributes = apply_filters(
													'wlfmc_product_with_meta_add_to_cart_custom_attributes',
													array(
														'data-product_sku' => $product->get_sku(),
														'aria-label' => $product->add_to_cart_description(),
														'aria-describedby' => $product->add_to_cart_aria_describedby(),
														'rel' => 'nofollow',
														'data-e-disable-page-transition' => '',
													),
													$product,
													$passed_validation,
													$item,
													$meta
												);
												$button_class      = ' add_to_cart_button button ' . ( $can_add_with_ajax && 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ? 'wlfmc_ajax_add_to_cart' : '' );
												echo apply_filters(
													'wlfmc_product_with_meta_add_to_cart_link',
													sprintf(
														'<a href="%s" data-item_id="%d" data-wishlist_id="%d" data-product_id="%d"  data-quantity="%s" class="%s" %s >%s</a>',
														esc_url( $add_to_cart_url ),
														esc_attr( $item->get_id() ),
														esc_attr( $wishlist_id ),
														esc_attr( $product->get_id() ),
														esc_attr( $item->get_quantity() ),
														esc_attr( $button_class ),
														! empty( $button_attributes ) ? wc_implode_html_attributes( $button_attributes ) : '',
														esc_html( $add_to_cart_text )
													),
													$product,
													$passed_validation,
													$item,
													$meta
												);
												?>
											<?php elseif ( has_action( 'wlfmc_table_product_' . $product->get_type() . '_add_to_cart_button' ) ) : ?>
												<?php do_action( 'wlfmc_table_product_' . $product->get_type() . '_add_to_cart_button', $item, $wishlist, $product, $cart_item, $permalink ); ?>
											<?php else : ?>
												<?php woocommerce_template_loop_add_to_cart( array( 'quantity' => $item->get_quantity() ) ); ?>
											<?php endif; ?>
											<?php do_action( 'wlfmc_table_product_after_add_to_cart', $item, $wishlist ); ?>
										<?php endif; ?>
										<?php if ( in_array( 'product-quantity', $items_show, true ) && ! $no_interactions && $wishlist->current_user_can( 'update_quantity' ) && 'out-of-stock' !== $item->get_stock_status() ) : ?>

											<?php do_action( 'wlfmc_table_before_product_quantity', $item, $wishlist ); ?>

											<?php
											woocommerce_quantity_input(
												array(
													'input_name'  => 'items[' . $item->get_product_id() . '][quantity]',
													'input_value' => $item->get_quantity(),
												)
											);
											?>
											<?php do_action( 'wlfmc_table_after_product_quantity', $item, $wishlist ); ?>

										<?php endif; ?>
									</div>
									<?php if ( in_array( 'product-date-added', $items_show, true ) && $item->get_date_added() ) : ?>
										<!-- Date added -->
										<div class="product-date-added dateadded hide-on-grid-mode">
										<?php
										// translators: %s is date added label.
										echo sprintf( esc_html__( 'Added on: %s', 'wc-wlfmc-wishlist' ), $item->get_date_added_formatted() ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										?>
										</div>
									<?php endif; ?>

								</div>
							</td>
						</tr>
						<?php if ( '' !== $item_meta_date ) : ?>
							<tr class="wlfmc-row-meta-data hide parent-row-id-<?php echo esc_attr( $item->get_product_id() ); ?>" >
								<td colspan="3">
									<div class="wlfmc-meta-data" >
										<?php echo wp_kses_post( $item_meta_date ); ?>
									</div>
								</td>
							</tr>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="wishlist-empty-row">
					<td colspan="3" class="last-column">
						<?php
							echo wp_kses_post( apply_filters( 'wlfmc_no_product_in_wishlist_image', '<img class="empty-image" src="' . esc_url( MC_WLFMC_URL ) . 'assets/frontend/images/empty-wishlist.svg" width="400" height="216">', $wishlist ) );
							echo wp_kses_post( apply_filters( 'wlfmc_no_product_in_wishlist_title', '' !== $empty_wishlist_title ? '<h3 class="empty-title">' . $empty_wishlist_title . '</h3>' : '', $wishlist ) );
							echo do_shortcode( apply_filters( 'wlfmc_no_product_in_wishlist_message', '' !== $empty_wishlist_content ? '<div class="empty-content">' . $empty_wishlist_content . '</div>' : '', $wishlist ) );
							echo wp_kses_post( apply_filters( 'wlfmc_no_product_in_wishlist_button', '<a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '" class="wc-forward button empty-button">' . esc_html__( 'Go to shop', 'woocommerce' ) . '</a>', $wishlist ) );
						?>
					</td>
				</tr>
			<?php endif; ?>
			</tbody>
			<tfoot class="wlfmc-wishlist-footer">
			<?php do_action( 'wlfmc_table_before_pagination', $wishlist, $atts ); ?>
			<?php if ( ! empty( $page_links ) ) : ?>
				<tr class="pagination-row ">
					<td colspan="3">
						<nav class="<?php echo esc_attr( $wishlist_pagination_class ); ?>"><?php echo wp_kses_post( $page_links ); ?></nav>
					</td>
				</tr>
			<?php endif; ?>

			<?php do_action( 'wlfmc_table_after_pagination', $wishlist, $atts ); ?>

			<?php if ( $wishlist && $wishlist->has_items() && ( $enable_actions || $enable_all_add_to_cart || ( ( $product_move || $product_copy ) && in_array( 'product-checkbox', $items_show, true ) ) ) ) : ?>
				<tr class="actions">
					<td colspan="3">
						<div class="action-wrapper space-between f-center-item d-flex f-wrap gap-10">
							<?php if ( $enable_actions ) : ?>
								<!-- Bulk actions form -->
								<div class="wlfmc_wishlist_bulk_action f-wrap justify-center d-flex f-center-item gap-5">
									<?php if ( in_array( 'product-checkbox', $items_show, true ) ) : ?>
										<label class="checkbox-label">
											<input type="checkbox" value="" name="" id="bulk_add_to_cart"/>
											<span></span>
										</label>
									<?php endif; ?>
									<label class="screen-reader-text" for="bulk_actions"><?php echo esc_attr( $action_label ); ?></label>
									<select name="bulk_actions" id="bulk_actions">
										<option value=""><?php echo esc_attr( $action_label ); ?></option>
										<option
											value="add_to_cart"><?php echo esc_attr( $action_add_to_cart_label ); ?></option>

										<?php if ( $wishlist->current_user_can( 'remove_from_wishlist' ) ) : ?>
											<option
												value="delete"><?php echo esc_attr( $action_remove_label ); ?></option>
										<?php endif; ?>
									</select>
									<button type="submit" class="apply-btn button" name="apply_bulk_actions" ><?php echo esc_attr( $apply_label ); ?></button>
								</div>
							<?php endif; ?>
							<div class="d-flex f-center f-wrap gap-5">
								<?php if ( $product_move ) : ?>
									<a href="#" class="button multiple-product-move wlfmc-popup-trigger" style="display:none" data-popup-id="move_popup">
										<?php echo esc_attr( $move_all_to_list_label ); ?>
									</a>
								<?php endif; ?>
								<?php if ( $product_copy ) : ?>
                                    <a href="#" class="button multiple-product-copy <?php echo $available_multi_list ? 'wlfmc-popup-trigger' : 'wlfmc_copy_to_default_list';?>" style="display:none" data-wishlist-id="<?php echo esc_attr( $wishlist_id ); ?>" data-popup-id="copy_popup">
                                        <i class="wlfmc-icon-move-to-list"></i>&nbsp;<span><?php echo esc_attr( $copy_all_to_list_label ); ?></span>
                                    </a>
								<?php endif; ?>
								<?php if ( $enable_all_add_to_cart ) : ?>
									<button type="submit" class="add-all-to-cart-btn button <?php echo '' !== $all_add_to_cart_tooltip_label ? esc_attr( $tooltip_class ) : ''; ?>" data-tooltip-type="<?php echo esc_attr( $tooltip_type ); ?>" name="add_all_to_cart"  data-tooltip-text="<?php echo esc_textarea( $all_add_to_cart_tooltip_label ); ?>" ><?php echo esc_attr( $all_add_to_cart_label ); ?></button>
								<?php endif; ?>
							</div>

							<?php wp_nonce_field( 'wlfmc_edit_wishlist_action', 'wlfmc_edit_wishlist' ); ?>
							<input type="hidden" value="<?php echo esc_attr( $wishlist_token ); ?>" name="wishlist_id" id="wishlist_id">
						</div>
					</td>
				</tr>
			<?php endif; ?>

			<?php do_action( 'wlfmc_table_after_actions', $wishlist, $atts ); ?>

			<?php if ( 'after_table' === $share_position && $wishlist && $wishlist->has_items() && $share_enabled && is_array( $share_items ) && ! empty( $share_items ) ) : ?>
				<tr>
					<td colspan="3" class="with-border-top">
						<div class="share-wrapper">
							<!-- Sharing section -->
							<div class="wlfmc-share d-flex justify-center f-center-item">
								<strong
									class="wlfmc-share-title"><?php echo esc_attr( $share_on_label ); ?></strong>
								<ul class="share-items">
									<?php foreach ( $share_items as $k => $share_item ) : ?>
										<?php if ( 'facebook' === $share_item ) : ?>
											<li class="share-item">
												<a target="_blank" rel="noopener" class="facebook  <?php echo '' !== $share_on_facebook_tooltip_label ? esc_attr( $tooltip_class ) : ''; ?>" data-tooltip-type="<?php echo esc_attr( $tooltip_type ); ?>" href="https://www.facebook.com/sharer.php?u=<?php echo rawurlencode( $share_atts['share_link_url'] ); ?>&p[title]=<?php echo esc_attr( $share_atts['share_socials_title'] ); ?>"  data-tooltip-text="<?php echo esc_attr( $share_on_facebook_tooltip_label ); ?>">
													<?php echo $share_atts['share_facebook_icon'] ? wp_kses_post( $share_atts['share_facebook_icon'] ) : esc_html__( 'Facebook', 'wc-wlfmc-wishlist' ); ?>
												</a>
											</li>
										<?php endif; ?>

										<?php if ( 'twitter' === $share_item ) : ?>
											<li class="share-item">
												<a target="_blank" rel="noopener" class="twitter <?php echo '' !== $share_on_twitter_tooltip_label ? esc_attr( $tooltip_class ) : ''; ?>" data-tooltip-type="<?php echo esc_attr( $tooltip_type ); ?>" href="https://twitter.com/share?url=<?php echo rawurlencode( $share_atts['share_link_url'] ); ?>&amp;text=<?php echo esc_attr( $share_atts['share_socials_title'] ); ?>"  data-tooltip-text="<?php echo esc_attr( $share_on_twitter_tooltip_label ); ?>">
													<?php echo $share_atts['share_twitter_icon'] ? wp_kses_post( $share_atts['share_twitter_icon'] ) : esc_html__( 'Twitter', 'wc-wlfmc-wishlist' ); ?>
												</a>
											</li>
										<?php endif; ?>

										<?php if ( 'messenger' === $share_item ) : ?>
											<li class="share-item">
												<a target="_blank" rel="noopener" class="messenger  <?php echo '' !== $share_on_messenger_tooltip_label ? esc_attr( $tooltip_class ) : ''; ?>" data-tooltip-type="<?php echo esc_attr( $tooltip_type ); ?>" href="fb-messenger://share/?link=<?php echo rawurlencode( $share_atts['share_link_url'] ); ?>"  data-tooltip-text="<?php echo esc_attr( $share_on_messenger_tooltip_label ); ?>">
													<?php echo $share_atts['share_messenger_icon'] ? wp_kses_post( $share_atts['share_messenger_icon'] ) : esc_html__( 'Messenger', 'wc-wlfmc-wishlist' ); ?>
												</a>
											</li>
										<?php endif; ?>

										<?php if ( 'email' === $share_item ) : ?>
											<li class="share-item">
												<a class="email <?php echo '' !== $share_on_email_tooltip_label ? esc_attr( $tooltip_class ) : ''; ?>" data-tooltip-type="<?php echo esc_attr( $tooltip_type ); ?>" href="mailto:?subject=<?php echo esc_attr( apply_filters( 'wlfmc_email_share_subject', $share_atts['share_socials_title'] ) ); ?>&amp;body=<?php echo esc_attr( apply_filters( 'wlfmc_email_share_body', rawurlencode( $share_atts['share_link_url'] ) ) ); ?>&amp;title=<?php echo esc_attr( $share_atts['share_socials_title'] ); ?>"  data-tooltip-text="<?php echo esc_attr( $share_on_email_tooltip_label ); ?>">
													<?php echo $share_atts['share_email_icon'] ? wp_kses_post( $share_atts['share_email_icon'] ) : esc_html__( 'Email', 'wc-wlfmc-wishlist' ); ?>
												</a>
											</li>
										<?php endif; ?>

										<?php if ( 'whatsapp' === $share_item ) : ?>
											<li class="share-item">
												<a class="whatsapp  <?php echo '' !== $share_on_whatsapp_tooltip_label ? esc_attr( $tooltip_class ) : ''; ?>" data-tooltip-type="<?php echo esc_attr( $tooltip_type ); ?>" href="<?php echo esc_attr( $share_atts['share_whatsapp_url'] ); ?>" data-action="share/whatsapp/share" target="_blank" rel="noopener"  data-tooltip-text="<?php echo esc_attr( $share_on_whatsapp_tooltip_label ); ?>">
													<?php echo $share_atts['share_whatsapp_icon'] ? wp_kses_post( $share_atts['share_whatsapp_icon'] ) : esc_html__( 'Whatsapp', 'wc-wlfmc-wishlist' ); ?>
												</a>
											</li>
										<?php endif; ?>

										<?php if ( 'telegram' === $share_item ) : ?>
											<li class="share-item">
												<a class="telegram  <?php echo '' !== $share_on_telegram_tooltip_label ? esc_attr( $tooltip_class ) : ''; ?>" data-tooltip-type="<?php echo esc_attr( $tooltip_type ); ?>" href="<?php echo esc_attr( $share_atts['share_telegram_url'] ); ?>" target="_blank" rel="noopener"  data-tooltip-text="<?php echo esc_attr( $share_on_telegram_tooltip_label ); ?>">
													<?php echo $share_atts['share_telegram_icon'] ? wp_kses_post( $share_atts['share_telegram_icon'] ) : esc_html__( 'Telegram', 'wc-wlfmc-wishlist' ); ?>
												</a>
											</li>
										<?php endif; ?>
										<?php if ( 'copy' === $share_item ) : ?>
											<li class="share-item">
												<a class="copy-link-trigger <?php echo '' !== $share_on_copy_link_tooltip_label ? esc_attr( $tooltip_class ) : ''; ?>" data-tooltip-type="<?php echo esc_attr( $tooltip_type ); ?>"  data-tooltip-text="<?php echo esc_attr( $share_on_copy_link_tooltip_label ); ?>" data-href="<?php echo esc_url( $share_atts['share_link_url'] ); ?>"><?php echo wp_kses_post( $share_atts['share_copy_icon'] ); ?></a>
											</li>
										<?php endif; ?>
										<?php if ( 'pdf' === $share_item ) : ?>
											<li class="share-item">
												<a class="download-pdf <?php echo '' !== $share_on_download_pdf_tooltip_label ? esc_attr( $tooltip_class ) : ''; ?>" data-tooltip-type="<?php echo esc_attr( $tooltip_type ); ?>"  data-tooltip-text="<?php echo esc_attr( $share_on_download_pdf_tooltip_label ); ?>" href="<?php echo esc_url( $share_atts['share_pdf_url'] ); ?>" target="_blank"><?php echo wp_kses_post( $share_atts['share_pdf_icon'] ); ?></a>
											</li>
										<?php endif; ?>
									<?php endforeach; ?>
								</ul>


							</div>
						</div>
					</td>
				</tr>
			<?php endif; ?>

			<?php do_action( 'wlfmc_table_after_share', $wishlist, $atts ); ?>

			</tfoot>
		</table>

		<?php if ( $product_move && $available_multi_list && $wishlist && $wishlist->has_items() ) : ?>
			<?php do_action( 'wlfmc_product_move', $wishlist, $atts ); ?>
		<?php endif; ?>

		<?php if ( $product_copy && $wishlist && $wishlist->has_items()  ) : ?>
			<?php do_action( 'wlfmc_product_copy', $wishlist, $atts ); ?>
		<?php endif; ?>

		<?php do_action( 'wlfmc_after_wishlist_table', $wishlist, $atts ); ?>

	</div>
</form>
