<?php
/**
 * Wishlist Counter template;
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.6.6
 */

/**
 * Template Variables:
 *
 * @var $atts                       array all template variables
 * @var $show_icon                  bool show icon
 * @var $show_text                  bool show text
 * @var $show_counter               bool show counter
 * @var $fragment_options           array Array of items to use for fragment generation
 * @var $dropdown_fragment_options  array Array of items to use for fragment generation
 * @var $wishlist                   WLFMC_Wishlist Current wishlist
 * @var $wishlist_token             string Current wishlist token
 * @var $wishlist_id                int Current wishlist id
 * @var $wishlist_items             array Array of items to show for current page
 * @var $per_page                   int Items per page
 * @var $show_products              bool show product list
 * @var $dropdown_products          bool show product as dropdown
 * @var $counter_text               string counter text
 * @var $button_text                string button text
 * @var $total_text                 string total text
 * @var $icon                       string counter icon
 * @var $hide_zero_products_number  bool
 * @var $hide_counter_if_no_items   bool
 * @var $products_number_position   string right/left/top-right/top-left position of product count in counter
 * @var $add_link_title             bool add line for title of counter  if not showing product list
 * @var $wishlist_link_position     string after/before product list
 * @var $wishlist_url               string wishlist url
 * @var $is_svg_icon                bool Whether  icon is svg
 * @var $is_elementor               bool Whether an elementor shortcode or not
 * @var $empty_wishlist_content     string string of no product in wishlist
 * @var $unique_id                  string counter Unique ID for fragment updates
 * @var $show_totals                bool show total products
 * @var $show_button                bool show wishlist button
 * @var $show_list_on_hover         bool show product lis on hover or after clicked
 * @var $container_class            string Container class
 * @var $link_class                 string Link class
 * @var $position_mode              string Dropdown position mode
 * @var $base_url                   string current page url
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div <?php echo $hide_counter_if_no_items && ( ! $wishlist || ! $wishlist->has_items() ) ? 'style="display:none"' : ''; ?>
	class="wlfmc-counter-wrapper <?php echo esc_attr( $unique_id ); ?> <?php echo esc_attr( $container_class ); ?> <?php echo $is_elementor ? 'is-elementor' : ''; ?> <?php echo $show_list_on_hover ? 'show-list-on-hover' : 'show-list-on-click'; ?> woocommerce wlfmc-wishlist-fragment on-first-load"
	data-id="<?php echo esc_attr( $unique_id ); ?>"
	data-fragment-options="<?php echo esc_attr( wp_json_encode( $fragment_options ) ); ?>"
	data-fragment-ref="wishlist_counter">
	<?php if ( $show_icon || $show_text ) : ?>
		<a href="<?php echo $add_link_title && ! $show_products ? esc_url( $wishlist_url ) : '#'; ?>"
			data-id="<?php echo esc_attr( $unique_id ); ?>"
			class="<?php echo esc_attr( $link_class ); ?> wlfmc-counter wlfmc-products-counter <?php echo esc_attr( 'target_' . $unique_id ); ?> <?php echo $dropdown_products && $show_products ? 'has-dropdown' : ''; ?> products-number-position-<?php echo esc_attr( $products_number_position ); ?>">
			<?php if ( $show_icon ) : ?>
				<span class="wlfmc-counter-icon">
					<?php echo $is_svg_icon && strpos( $icon, '<svg' ) !== false ? '<i class="wlfmc-svg">' . wlfmc_sanitize_svg( $icon ) . '</i>' : wp_kses_post( $icon );// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php
					if ( in_array(
						$products_number_position,
						array(
							'top-right',
							'top-left',
						),
						true
					) && $show_counter && ( ( $wishlist && $wishlist->count_items() > 0 ) || ( $wishlist && ! $hide_zero_products_number && 0 === $wishlist->count_items() ) ) ) :
						?>
						<span
							class="wlfmc-counter-number products-counter-number position-<?php echo esc_attr( $products_number_position ); ?>"><?php echo wp_kses_post( $wishlist->count_items() ); ?></span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
			<?php if ( ( ( ! is_rtl() && 'left' === $products_number_position ) || ( is_rtl() && 'right' === $products_number_position ) ) && $show_counter && ( ( $wishlist && $wishlist->count_items() > 0 ) || ( $wishlist && ! $hide_zero_products_number && 0 === $wishlist->count_items() ) ) ) : ?>
				<span class="wlfmc-counter-number products-counter-number"><?php echo wp_kses_post( $wishlist->count_items() ); ?></span>
			<?php endif; ?>
			<?php if ( $show_text ) : ?>
				<span class="wlfmc-counter-text wishlist-products-counter-text"><?php echo wp_kses_post( $counter_text ); ?></span>
			<?php endif; ?>
			<?php if ( ( ( ! is_rtl() && 'right' === $products_number_position ) || ( is_rtl() && 'left' === $products_number_position ) ) && $show_counter && ( ( $wishlist && $wishlist->count_items() > 0 ) || ( $wishlist && ! $hide_zero_products_number && 0 === $wishlist->count_items() ) ) ) : ?>
				<span class="wlfmc-counter-number products-counter-number"><?php echo wp_kses_post( $wishlist->count_items() ); ?></span>
			<?php endif; ?>
		</a>
	<?php endif; ?>
	<?php if ( $show_products && ( ! $dropdown_products || 'fixed' !== $position_mode ) ) : ?>
		<div
			data-id="<?php echo esc_attr( $unique_id ); ?>"
			class="wlfmc-counter-items wlfmc-products-counter-wishlist <?php echo esc_attr( 'dropdown_' . $unique_id ); ?> position-<?php echo esc_attr( $position_mode ); ?> <?php echo $dropdown_products ? 'wlfmc-counter-dropdown wlfmc-products-counter-dropdown' : 'wlfmc-counter-list wlfmc-products-counter-list'; ?>">
			<div class="wlfmc-counter-content wlfmc-wishlist-content d-flex flex-column gap-10">
				<?php if ( $wishlist && $wishlist->has_items() ) : ?>

					<?php if ( 'before' === $wishlist_link_position && $show_button ) : ?>

						<a href="<?php echo esc_url( $wishlist_url ); ?>" class="wlfmc-view-wishlist-link  ">
							<?php echo wp_kses_post( $button_text ); ?>
						</a>

					<?php endif; ?>

					<div class="wlfmc-mini-wishlist-list wlfmc-wishlist-items-wrapper"
						data-id="<?php echo esc_attr( $wishlist_id ); ?>"
						data-token="<?php echo esc_attr( $wishlist_token ); ?>">
						<?php foreach ( $wishlist_items as $item ) : ?>
							<?php
							// phpcs:ignore Generic.Commenting.DocComment
							/**
							 * @var $item WLFMC_Wishlist_Item
							 */
							global $product;

							$product   = $item->get_product();
							$cart_item = $item->get_cart_item( true );
							$permalink = apply_filters( 'woocommerce_cart_item_permalink', $product->get_permalink( $cart_item ), $cart_item, '' );
							?>
							<?php if ( $product && $product->exists() ) : ?>
								<div id="wlfmc-row-<?php echo esc_attr( $item->get_product_id() ); ?>"  data-item-id="<?php echo esc_attr( $item->get_id() ); ?>" data-row-id="<?php echo esc_attr( $item->get_product_id() ); ?>" class="d-flex gap-10 wlfmc-counter-item" >
									<a class="product-thumbnail" href="<?php echo esc_url( $permalink ); ?>">
										<?php echo wp_kses_post( $product->get_image() ); ?>
									</a>
									<div class="max-100">
										<a class="product-name" href="<?php echo esc_url( $permalink ); ?>">
											<?php echo wp_kses_post( apply_filters( 'woocommerce_in_cartproduct_obj_title', $product->get_title(), $product ) ); ?>
										</a>
										<div class="product-price price d-flex space-between">
											<span>
												<?php
												echo wp_kses_post( $item->get_formatted_product_price() );
												?>
											</span>
											<a href="<?php echo esc_url( add_query_arg( 'remove_from_wishlist', $item->get_product_id(), $base_url ) ); ?>" class="remove_from_wishlist wlfmc-remove-from-list" title="<?php echo esc_html( apply_filters( 'wlfmc_remove_product_wishlist_message_title', __( 'Remove this product', 'wc-wlfmc-wishlist' ) ) ); ?>" data-e-disable-page-transition>
												<i class="wlfmc-icon-close"></i>
											</a>
										</div>

									</div>

								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>

					<?php if ( $show_totals ) : ?>

						<div class="total-products d-flex space-between ">
							<span class="wlfmc-total-title"><?php echo esc_attr( $total_text ); ?></span>
							<span class="wlfmc-total-count"><?php echo esc_attr( $wishlist->count_items() ); ?></span>
						</div>

					<?php endif; ?>

					<?php if ( 'after' === $wishlist_link_position && $show_button ) : ?>

						<a href="<?php echo esc_url( $wishlist_url ); ?>" class="wlfmc-view-wishlist-link  center-align">
							<?php echo wp_kses_post( $button_text ); ?>
						</a>

					<?php endif; ?>

				<?php else : ?>

					<div class="wlfmc-wishlist-empty"><?php echo wp_kses_post( apply_filters( 'wlfmc_no_product_in_counter_wishlist_message', $empty_wishlist_content, null ) ); ?></div>

				<?php endif; ?>

			</div>
		</div>
	<?php endif; ?>
</div>
<?php if ( $show_products && $dropdown_products && 'fixed' === $position_mode ) : ?>
	<div  <?php echo $hide_counter_if_no_items && ( ! $wishlist || ! $wishlist->has_items() ) ? 'style="display:none"' : ''; ?>
		class="wlfmc-counter-wrapper  <?php echo 'wrapper_' . esc_attr( $unique_id ); ?> <?php echo esc_attr( $container_class ); ?> <?php echo $is_elementor ? 'is-elementor' : ''; ?> <?php echo $show_list_on_hover ? 'show-list-on-hover' : 'show-list-on-click'; ?> woocommerce wlfmc-wishlist-fragment on-first-load"
		data-fragment-options="<?php echo esc_attr( wp_json_encode( $dropdown_fragment_options ) ); ?>"
		data-fragment-ref="wishlist_counter">
		<div
			data-id="<?php echo esc_attr( $unique_id ); ?>"
			class="wlfmc-counter-items wlfmc-products-counter-wishlist <?php echo esc_attr( 'dropdown_' . $unique_id ); ?> position-<?php echo esc_attr( $position_mode ); ?> wlfmc-counter-dropdown wlfmc-products-counter-dropdown">
			<div class="wlfmc-counter-content wlfmc-wishlist-content d-flex flex-column gap-10">
				<?php if ( $wishlist && $wishlist->has_items() ) : ?>

					<?php if ( 'before' === $wishlist_link_position && $show_button ) : ?>

						<a href="<?php echo esc_url( $wishlist_url ); ?>" class="wlfmc-view-wishlist-link  ">
							<?php echo wp_kses_post( $button_text ); ?>
						</a>

					<?php endif; ?>

					<div class="wlfmc-mini-wishlist-list wlfmc-wishlist-items-wrapper"
						data-id="<?php echo esc_attr( $wishlist_id ); ?>"
						data-token="<?php echo esc_attr( $wishlist_token ); ?>">
						<?php foreach ( $wishlist_items as $item ) : ?>
							<?php
							// phpcs:ignore Generic.Commenting.DocComment
							/**
							 * @var $item WLFMC_Wishlist_Item
							 */
							global $product;

							$product   = $item->get_product();
							$cart_item = $item->get_cart_item( true );
							$permalink = apply_filters( 'woocommerce_cart_item_permalink', $product->get_permalink( $cart_item ), $cart_item, '' );
							?>
							<?php if ( $product && $product->exists() ) : ?>
								<div id="wlfmc-row-<?php echo esc_attr( $item->get_product_id() ); ?>"  data-item-id="<?php echo esc_attr( $item->get_id() ); ?>" data-row-id="<?php echo esc_attr( $item->get_product_id() ); ?>" class="d-flex gap-10 wlfmc-counter-item" >
									<a class="product-thumbnail" href="<?php echo esc_url( $permalink ); ?>">
										<?php echo wp_kses_post( $product->get_image() ); ?>
									</a>
									<div class="max-100">
										<a class="product-name" href="<?php echo esc_url( $permalink ); ?>">
											<?php echo wp_kses_post( apply_filters( 'woocommerce_in_cartproduct_obj_title', $product->get_title(), $product ) ); ?>
										</a>
										<div class="product-price price d-flex space-between">
											<span>
												<?php
												echo wp_kses_post( $item->get_formatted_product_price() );
												?>
											</span>
											<a href="<?php echo esc_url( add_query_arg( 'remove_from_wishlist', $item->get_product_id(), $base_url ) ); ?>" class="remove_from_wishlist wlfmc-remove-from-list" title="<?php echo esc_html( apply_filters( 'wlfmc_remove_product_wishlist_message_title', __( 'Remove this product', 'wc-wlfmc-wishlist' ) ) ); ?>" data-e-disable-page-transition>
												<i class="wlfmc-icon-close"></i>
											</a>
										</div>

									</div>

								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>

					<?php if ( $show_totals ) : ?>

						<div class="total-products d-flex space-between ">
							<span class="wlfmc-total-title"><?php echo esc_attr( $total_text ); ?></span>
							<span class="wlfmc-total-count"><?php echo esc_attr( $wishlist->count_items() ); ?></span>
						</div>

					<?php endif; ?>

					<?php if ( 'after' === $wishlist_link_position && $show_button ) : ?>

						<a href="<?php echo esc_url( $wishlist_url ); ?>" class="wlfmc-view-wishlist-link  center-align">
							<?php echo wp_kses_post( $button_text ); ?>
						</a>

					<?php endif; ?>

				<?php else : ?>

					<div class="wlfmc-wishlist-empty"><?php echo wp_kses_post( apply_filters( 'wlfmc_no_product_in_counter_wishlist_message', $empty_wishlist_content, null ) ); ?></div>

				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif; ?>
