<?php
/**
 * Add to wishlist template
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.4.0
 */

/**
 * Template variables:
 *
 * @var $atts                       array all template variables
 * @var $base_url                   string Current page url
 * @var $wishlist_url               string Url to wishlist page
 * @var $product_id                 int Current product id
 * @var $parent_product_id          int Parent for current product
 * @var $product_type               string Current product type
 * @var $is_single                  bool Whether you're currently on single product template
 * @var $already_in_wishlist_text   string Already in wishlist text
 * @var $product_added_text         string Product added text
 * @var $available_multi_list       bool Whether multi-list is available or not
 * @var $disable_wishlist           bool Whether wishlist is disabled or not
 * @var $enabled_popup              bool Whether popup is enabled or not
 * @var $container_classes          string Container classes
 * @var $popup_vertical             string Vertically position of popup
 * @var $popup_horizontal           string Horizontally position of popup
 * @var $popup_size                 string Popup size
 * @var $popup_image                string Popup image
 * @var $popup_title                string Popup title
 * @var $popup_content              string Popup content
 * @var $signup_url                 string sign-up url
 * @var $login_url                  string login url
 * @var $is_svg_icon                bool Whether  icon is svg
 * @var $buttons                    array Array of buttons to show in popup
 * @var $tooltip_class              string Tooltip class
 * @var $tooltip_label_add          string "Add To Wishlist" tooltip text
 * @var $tooltip_label_view         string "View My Wishlist" tooltip text
 * @var $tooltip_label_remove       string "Remove From Wishlist" tooltip text
 * @var $tooltip_label_exists       string "Already In Wishlist" tooltip text
 * @var $tooltip_type               string Tooltip type default/custom
 * @var $icon                       string
 * @var $added_icon                 string
 * @var $classes_exists             string
 * @var $classes_add                string
 * @var $button_label_add           string "Add To Wishlist" text
 * @var $button_label_remove        string "Remove From Wishlist" text
 * @var $button_label_view          string "View My Wishlist" text
 * @var $button_label_exists        string "Already In Wishlist" text
 * @var $data_remove_url            string remove from wishlist url with #product_id string instead real product_id
 * @var $data_add_url               string add to wishlist url with #product_id string instead real product_id
 * @var $product_title              string product title
 * @var $product_price              string product price
 * @var $use_featured_image         bool
 * @var $popup_image_size           string
 * @var $enable_for_outofstock_product bool
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $product;

$unique_id = wp_unique_id(); ?>
<div
	class="wlfmc-add-to-wishlist wlfmc-add-to-wishlist-<?php echo esc_attr( $product_id ); ?> <?php echo esc_attr( $container_classes ); ?> "
	data-remove-url="<?php echo esc_url( $data_remove_url ); ?>"
	data-add-url="<?php echo esc_url( $data_add_url ); ?>"
	data-enable-outofstock="<?php echo esc_attr( $enable_for_outofstock_product ); ?>"
	data-popup-id="<?php echo $enabled_popup ? 'add_to_wishlist_popup_' . esc_attr( $product_id ) . '_' . esc_attr( $unique_id ) : ''; ?>"
>

	<?php if ( ! ( $disable_wishlist ) ) : ?>

		<!-- EXISTS IN WISHLIST -->
		<div class="wlfmc-add-button wlfmc-existsinwishlist <?php echo esc_attr( $tooltip_class ); ?>"
			data-tooltip-type="<?php echo esc_attr( $tooltip_type ); ?>"
			data-tooltip-text="<?php echo esc_attr( $tooltip_label_exists ); ?>"
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
			data-parent-product-id="<?php echo esc_attr( $parent_product_id ); ?>">
			<a href="#" class="wlfmc_already_in_wishlist <?php echo esc_attr( $classes_exists ); ?>" data-e-disable-page-transition>
				<?php echo $is_svg_icon && strpos( $added_icon, '<svg' ) !== false ? '<i class="wlfmc-svg">' . wlfmc_sanitize_svg( $added_icon ) . '</i>' : wp_kses_post( $added_icon ); ?><?php echo ( '' !== $button_label_exists ) ? '<span>' . wp_kses_post( $button_label_exists ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<!-- BROWSE WISHLIST -->
		<div class="wlfmc-add-button wlfmc-browsewishlist <?php echo esc_attr( $tooltip_class ); ?>"
			data-tooltip-type="<?php echo esc_attr( $tooltip_type ); ?>"
			data-tooltip-text="<?php echo esc_attr( $tooltip_label_view ); ?>"
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
			data-parent-product-id="<?php echo esc_attr( $parent_product_id ); ?>">
			<a href="<?php echo esc_url( $wishlist_url ); ?>" rel="nofollow" class="<?php echo esc_attr( $classes_exists ); ?>" data-e-disable-page-transition>
				<?php echo $is_svg_icon && strpos( $added_icon, '<svg' ) !== false ? '<i class="wlfmc-svg">' . wlfmc_sanitize_svg( $added_icon ) . '</i>' : wp_kses_post( $added_icon ); ?><?php echo ( '' !== $button_label_view ) ? '<span>' . wp_kses_post( $button_label_view ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<!-- ADD TO WISHLIST -->
		<div class="wlfmc-add-button  wlfmc-addtowishlist <?php echo esc_attr( $tooltip_class ); ?>"
			data-tooltip-text="<?php echo esc_attr( $tooltip_label_add ); ?>"
			data-tooltip-type="<?php echo esc_attr( $tooltip_type ); ?>">
			<a href="#" rel="nofollow"
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
			data-product-type="<?php echo esc_attr( $product_type ); ?>"
			data-parent-product-id="<?php echo esc_attr( $parent_product_id ); ?>"
			data-e-disable-page-transition
			class="<?php echo esc_attr( $classes_add ); ?>">
				<?php echo $is_svg_icon && strpos( $icon, '<svg' ) !== false ? '<i class="wlfmc-svg">' . wlfmc_sanitize_svg( $icon ) . '</i>' : wp_kses_post( $icon ); ?><?php echo ( '' !== $button_label_add ) ? '<span>' . wp_kses_post( $button_label_add ) . '</span>' : '';  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<!-- REMOVE FROM WISHLIST -->
		<div class="wlfmc-add-button  wlfmc-removefromwishlist <?php echo esc_attr( $tooltip_class ); ?>"
			data-tooltip-type="<?php echo esc_attr( $tooltip_type ); ?>"
			data-tooltip-text="<?php echo esc_attr( $tooltip_label_remove ); ?>">
			<a href="#" rel="nofollow"
			data-wishlist-id=""
			data-item-id=""
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
			data-product-type="<?php echo esc_attr( $product_type ); ?>"
			data-parent-product-id="<?php echo esc_attr( $parent_product_id ); ?>"
			data-e-disable-page-transition
			class="wlfmc_delete_item <?php echo esc_attr( $classes_exists ); ?>">
				<?php echo $is_svg_icon && strpos( $added_icon, '<svg' ) !== false ? '<i class="wlfmc-svg">' . wlfmc_sanitize_svg( $added_icon ) . '</i>' : wp_kses_post( $added_icon ); ?><?php echo ( '' !== $button_label_remove ) ? '<span>' . wp_kses_post( $button_label_remove ) . '</span>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<?php if ( $enabled_popup ) : ?>

			<!-- WISHLIST POPUP -->
			<div class="wlfmc-popup wlfmc-wishlist-popup auto-init size-<?php echo esc_attr( $popup_size ); ?>"
				data-horizontal="<?php echo esc_attr( $popup_horizontal ); ?>"
				data-vertical="<?php echo esc_attr( $popup_vertical ); ?>"
				data-use-featured="<?php echo esc_attr( $use_featured_image ); ?>"
				data-image-size="<?php echo esc_attr( $popup_image_size ); ?>"
				data-product-title="<?php echo esc_attr( $product_title ); ?>"
				id="add_to_wishlist_popup_<?php echo esc_attr( $product_id ); ?>_<?php echo esc_attr( $unique_id ); ?>">
				<div class="wlfmc-popup-content">
					<span class="wlfmc-popup-header-bordered f-center-item space-between">
						<span class="d-flex f-center-item gap-10">
							<?php echo $is_svg_icon && strpos( $icon, '<svg' ) !== false ? '<i class="wlfmc-svg">' . wlfmc_sanitize_svg( $icon ) . '</i>' : wp_kses_post( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span class="wlfmc-popup-title"><?php echo wp_kses_post( $popup_title ); ?></span>
						</span>
						<a class="wlfmc-popup-close" data-popup-id="add_to_wishlist_popup_<?php echo esc_attr( $product_id ); ?>_<?php echo esc_attr( $unique_id ); ?>" href="#" ><i class="wlfmc-icon-close"></i></a>
					</span>
					<?php if ( 'large' === $popup_size ) : ?>
						<div class="wlfmc-popup-header">
							<figure>
								<img loading="lazy" data-src="<?php echo $popup_image ? esc_url( $popup_image ) : esc_url( MC_WLFMC_URL . 'assets/frontend/images/popup-image.png' ); ?>" src="<?php echo $popup_image ? esc_url( $popup_image ) : esc_url( MC_WLFMC_URL . 'assets/frontend/images/popup-image.png' ); ?>" alt="<?php echo esc_html( $popup_title ); ?>"/>
							</figure>
						</div>
					<?php endif; ?>
					<div class="wlfmc-parent-product-price hide" style="display:none"><?php echo wp_kses_post( $product_price ); ?></div>
					<div class="wlfmc-popup-desc">
						<?php echo do_shortcode( $popup_content ); ?>
					</div>
				</div>
				<div class="wlfmc-popup-footer">
					<?php if ( is_array( $buttons ) && ! empty( $buttons ) ) : ?>
						<?php foreach ( $buttons as $k => $button ) : ?>
							<?php
							switch ( $button['link'] ) {
								case 'back':
									echo '<a href="#" class="wlfmc-popup-close wlfmc-btn wlfmc-popup-close wlfmc_btn_' . esc_attr( $k ) . '"  data-popup-id="add_to_wishlist_popup_' . esc_attr( $product_id ) . '_' . esc_attr( $unique_id ) . '">' . esc_attr( $button['label'] ) . '</a>';
									break;
								case 'signup':
									echo ! is_user_logged_in() ? '<a href="' . esc_url( $signup_url ) . '" class="wlfmc-btn wlfmc_btn_' . esc_attr( $k ) . '">' . esc_attr( $button['label'] ) . '</a>' : '';
									break;
								case 'login':
								case 'signup-login':
									echo ! is_user_logged_in() ? '<a href="' . esc_url( $login_url ) . '" class="wlfmc-btn wlfmc_btn_' . esc_attr( $k ) . '">' . esc_attr( $button['label'] ) . '</a>' : '';
									break;
								case 'wishlist':
									echo '<a href="' . esc_url( $wishlist_url ) . '" class="wlfmc-btn wlfmc_btn_' . esc_attr( $k ) . '">' . esc_attr( $button['label'] ) . '</a>';
									break;
								case 'custom-link':
									echo trim( $button['custom-link'] ) !== '' ? '<a href="' . esc_url( $button['custom-link'] ) . '" rel="nofollow" class="wlfmc-btn wlfmc_btn_' . esc_attr( $k ) . '">' . esc_attr( $button['label'] ) . '</a>' : '';
									break;
							}
							?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

	<?php else : ?>
		<div class="wlfmc-add-button">
			<a href="<?php echo esc_url( add_query_arg( array( 'add_to_wishlist' => $product_id ), get_permalink( wc_get_page_id( 'myaccount' ) ) ) ); // @codingStandardsIgnoreLine. ?>" rel="nofollow" data-e-disable-page-transition
			class="disabled_item <?php echo esc_attr( str_replace( array( 'add_to_wishlist', 'single_add_to_wishlist', ), '', $classes_add ) ); // @codingStandardsIgnoreLine. ?>">
				<?php echo $is_svg_icon && strpos( $icon, '<svg' ) !== false ? '<i class="wlfmc-svg">' . wlfmc_sanitize_svg( $icon ) . '</i>' : wp_kses_post( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php if ( '' !== $button_label_add ) : ?>
					<span><?php echo wp_kses_post( $button_label_add ); ?></span>
				<?php endif; ?>
			</a>
		</div>
	<?php endif; ?>

</div>
