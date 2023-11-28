<?php
/**
 * Email List Items
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.5.0
 */

/**
 * Template variables:
 *
 * @var $items array list items
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$text_align  = is_rtl() ? 'right' : 'left';
$margin_side = is_rtl() ? 'left' : 'right';
if ( ! empty( $items ) ) :?>
	<div style="margin-bottom: 40px;">
		<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
			<thead>
			<tr>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'wc-wlfmc-wishlist' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'wc-wlfmc-wishlist' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Added Price', 'wc-wlfmc-wishlist' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ( $items as $item_id => $item ) :
				$product = $item->get_product();
				$image   = '';

				if ( ! apply_filters( 'wlfmc_email_item_visible', true, $item ) ) {
					continue;
				}

				if ( is_object( $product ) ) {
					$image = $product->get_image( 'woocommerce_thumbnail', array( 'style' => 'max-width:64px !important;height:auto;' ) );
				}

				?>
				<tr class="<?php echo esc_attr( apply_filters( 'wlfmc_email_item_class', 'list_item', $item ) ); ?>">
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
						<?php

						// Show title/image etc.
						echo wp_kses_post( apply_filters( 'wlfmc_email_item_thumbnail', $image, $item ) );

						// Product name.
						echo wp_kses_post( apply_filters( 'wlfmc_email_item_name', $product->get_title(), $item, false ) );
						?>
					</td>
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
						<?php
						echo wp_kses_post( apply_filters( 'wlfmc_email_item_quantity', esc_html( $item->get_quantity() ), $item ) );
						?>
					</td>
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
						<?php echo wp_kses_post( $item->get_formatted_original_price() ); ?>
					</td>
				</tr>

				<?php
			endforeach;
			?>
			</tbody>
		</table>
	</div>
	<?php
endif;
