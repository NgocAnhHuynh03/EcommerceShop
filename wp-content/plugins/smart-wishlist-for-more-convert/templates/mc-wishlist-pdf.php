<?php
/**
 * Wishlist pages template; load template parts basing on the url
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.4.3
 */

/**
 * Template Variables:
 *
 * @var $atts           array all template variables
 * @var $wishlist       WLFMC_Wishlist Current wishlist
 * @var $wishlist_items array Array of items to show for current page
 * @var $items_show     array Array of items show
 * @var $page_title     string Page title
 * @var $empty_wishlist_content string string of no product in wishlist
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> >

<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
	<title><?php echo esc_html( $page_title ); ?></title>

	<style>
		* {
			font-weight:normal;
			color: #333;
		}
		.wishlist-title {
			font-size: 14px;
			text-align:center;
		}
		a.product-name{
			display:block;
			text-decoration:none;
		}
		td.product-variation {
			line-height:2px;
		}
		ul {
			display:block;
			list-style-type:none;
			line-height:1;
		}
		li , dl.variation,dl.variation dt,dl.variation dd,li.none-style,li.none-style dl,li.none-style dt ,li.none-style dd  {
			line-height:1.5;
		}
		li.none-style {
			line-height:2;
		}
		li.none-style > dl {
			list-style-type:none;
			line-height: 1;
		}
		li.none-style > dl > dt {
			line-height: 20px;
		}
		li.none-style > dl > dd{
			line-height:8px
		}*/
		.product-thumbnail-link {
			display:inline-block;
		}
		table {
			width: 100%;
		}
		.wlfmc-table-item td{
			border-bottom: 1px solid #ebebeb;
		}

		.wlfmc-wishlist-table tbody tr:nth-child(even) {
			background-color: #f9f9f9;
		}

		.thWithOuterBorder{
			border-bottom: 2px solid #ebebeb;
		}
		tr .product-price,
		tr .product-quantity,
		tr .product-stock-status,
		tr .product-add-to-cart{
			text-align: center;
		}
		td.product-quantity, th.product-quantity {
			width:65px;
		}
		td.product-thumbnail, th.product-thumbnail {
			width:65px;
		}
		td.product-add-to-cart, th.product-add-to-cart {
			width:75px;
		}
		td.product-price, th.product-price {
			width:85px;
		}
		td.product-name, th.product-name {
			width:340px;
		}
		.price-variation {
			display:none
		}

	</style>
</head>

<body>
<!-- TITLE -->
<?php
do_action( 'wlfmc_before_pdf_wishlist_title', $wishlist );

if ( ! empty( $page_title ) ) :
	?>
	<table border="0" cellpadding="0">
		<tbody>
		<tr>
			<td>
				<div class="wishlist-title">
					<?php echo apply_filters( 'wlfmc_wishlist_title', '<strong>' . esc_html( $page_title ) . '</strong><br>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
<?php endif; ?>

<?php do_action( 'wlfmc_before_pdf_wishlist_table', $wishlist, $atts ); ?>

<table class="wlfmc-wishlist-table" border="0" cellpadding="6" cellspacing="0">
	<thead>
		<tr>
			<th class="product-thumbnail thWithOuterBorder"></th>
			<th class="product-name thWithOuterBorder">
				<span class="nobr">
					<?php echo esc_html( apply_filters( 'wlfmc_wishlist_pdf_view_name_heading', __( 'Product Name', 'wc-wlfmc-wishlist' ) ) ); ?>
				</span>
			</th>
			<th class="product-price thWithOuterBorder">
				<span class="nobr">
					<?php echo esc_html( apply_filters( 'wlfmc_wishlist_pdf_view_price_heading', __( 'Unit Price', 'wc-wlfmc-wishlist' ) ) ); ?>
				</span>
			</th>
			<th class="product-quantity thWithOuterBorder">
				<span class="nobr">
					<?php echo esc_html( apply_filters( 'wlfmc_wishlist_pdf_view_quantity_heading', __( 'Quantity', 'wc-wlfmc-wishlist' ) ) ); ?>
				</span>
			</th>
			<th class="product-stock-status thWithOuterBorder">
				<span class="nobr">
					<?php echo esc_html( apply_filters( 'wlfmc_wishlist_pdf_view_stock_heading', __( 'Stock status', 'wc-wlfmc-wishlist' ) ) ); ?>
				</span>
			</th>

			<th class="product-add-to-cart thWithOuterBorder">
				<?php esc_html_e( 'Added date', 'wc-wlfmc-wishlist' ); ?>
			</th>
		</tr>
	</thead>
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
			$meta         = $item->get_product_meta( 'view' );
			$image_size   = apply_filters( 'wlfmc_pdf_thumbnail_item_size', array( 32, 32 ) );
			$permalink    = apply_filters( 'woocommerce_cart_item_permalink', $product->get_permalink( $item->get_cart_item( true ) ), $cart_item, '' );
			if ( $product->exists() ) :
				?>
				<tr class="wlfmc-table-item" nobr="true">
					<td class="product-thumbnail" align="center">
						<a href="<?php echo esc_url( $permalink ); ?>" class="product-thumbnail-link">
							<img src="<?php echo esc_url( $product->get_image_id() ? current( wp_get_attachment_image_src( $product->get_image_id(), 'full' ) ) : wc_placeholder_img_src() ); ?>" alt="<?php esc_attr_e( 'Product image', 'wc-wlfmc-wishlist' ); ?>" width="<?php echo esc_attr( $image_size[1] ); ?>" height="<?php echo esc_attr( $image_size[0] ); ?>" style="vertical-align:middle; " />
						</a>
					</td>
					<td class="product-name">
						<table border="0" cellpadding="0">
							<tbody>
								<tr><td>&nbsp;</td></tr>
								<tr>
									<td>
										<a class="product-name" href="<?php echo esc_url( $permalink ); ?>">
											<strong><?php echo wp_kses_post( apply_filters( 'woocommerce_in_cartproduct_obj_title', $product->get_title(), $product ) ); ?></strong>
										</a>
									</td>
								</tr>
								<tr><td>&nbsp;</td></tr>
							</tbody>
						</table>
						<?php if ( in_array( 'product-variation', $items_show, true ) && ( $product->is_type( 'variation' ) || ( isset( $meta['attributes'] ) && ! empty( $meta['attributes'] ) ) ) ) : ?>
							<?php do_action( 'wlfmc_table_before_product_variation', $item, $wishlist ); ?>
							<table border="0" cellpadding="0">
								<tbody>
								<tr>
									<td class="product-variation">
										<?php
										// phpcs:ignore Generic.Commenting.DocComment
										/**
										 * @var $product WC_Product_Variation
										 */
										echo wc_get_formatted_variation( ! empty( $meta['attributes'] ) ? array_combine( array_map( 'rawurldecode', array_keys( $meta['attributes'] ) ), $meta['attributes'] ) : $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

										?>
									</td>
								</tr>
								</tbody>
							</table>
							<table border="0" cellpadding="0">
								<tbody>
								<tr>
									<td>
										<?php do_action( 'wlfmc_table_after_product_variation', $item, $wishlist ); ?>
									</td>
								</tr>
								</tbody>
							</table>
						<?php endif; ?>

						<?php do_action( 'wlfmc_pdf_table_meta_data', $meta, $cart_item, $wishlist ); ?>
					</td>
					<td class="product-price" align="center">
						<?php
						echo wp_kses_post( $item->get_formatted_product_price() );
						echo wp_kses_post( $item->get_price_variation() );
						?>
					</td>
					<td class="product-quantity" align="center">
						<?php echo esc_html( $item->get_quantity() ); ?>
					</td>
					<td class="product-stock-status" align="center">
						<?php echo 'out-of-stock' === $stock_status ? '<span class="wishlist-out-of-stock">' . esc_html__( 'Out of stock', 'wc-wlfmc-wishlist' ) . '</span>' : '<span class="wishlist-in-stock">' . esc_html__( 'In Stock', 'wc-wlfmc-wishlist' ) . '</span>'; ?>
					</td>
					<td class="product-add-to-cart" align="center">
						<!-- Date added -->
						<?php if ( $item->get_date_added() ) : ?>
							<?php echo esc_html( $item->get_date_added_formatted() ); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php else : ?>
		<tr nobr="true">
			<td colspan="6">
				<?php echo wp_kses_post( apply_filters( 'wlfmc_no_product_in_wishlist_message', $empty_wishlist_content, $wishlist ) ); ?>
			</td>
		</tr>
	<?php endif; ?>
	</tbody>
</table>

<?php do_action( 'wlfmc_after_pdf_wishlist_table', $wishlist, $atts ); ?>

</body>
</html>
