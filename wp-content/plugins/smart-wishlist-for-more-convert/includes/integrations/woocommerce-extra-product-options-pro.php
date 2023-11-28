<?php
/**
 * WLFMC wishlist integration with WooCommerce Extra Product Options Pro plugin
 *
 * @plugin_name WooCommerce Extra Product Options Pro
 * @version 3.2.1
 * @slug woocommerce-extra-product-options-pro
 * @url https://themehigh.com/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woocommerce_extra_product_options_pro_integrate' );

/**
 * Integration with WooCommerce Extra Product Options Pro plugin
 *
 * @return void
 */
function wlfmc_woocommerce_extra_product_options_pro_integrate() {

	if ( class_exists( 'THWEPO_Price' ) ) {
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_woocommerce_extra_product_options_pro_wishlist_item_price', 9, 4 );
	}
}


/**
 *  Modify wishlist item price for WooCommerce TM Extra Product Options
 *
 * @param string              $price item price.
 * @param null|array          $product_meta product meta.
 * @param WC_Product          $product  $product Woocommerce Product.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 *
 * @throws Exception Exception.
 * @return float|int|mixed
 */
function wlfmc_woocommerce_extra_product_options_pro_wishlist_item_price( $price, $product_meta, $product, $item ) {

	if ( is_object( $product ) && isset( $product_meta['thwepo_options'] ) && ! empty( $product_meta['thwepo_options'] ) ) {
		$product_id = $product->get_id();
		if ( 'variation' === $product->get_type() ) {
			$product_id = $product->get_parent_id();
		}

		$price_class = new WLFMC_THWEPO_Price();

		return $price_class->calculate_price( $price, $product_id, $product, $item->get_quantity(), $product_meta['thwepo_options'] );
	}

	return $price;
}

if ( ! class_exists( 'WLFMC_THWEPO_Price' ) ) {
	/**
	 * WooCommerce Extra Product Options Pro Calculate price
	 */
	class WLFMC_THWEPO_Price {
		/**
		 * Calculate price
		 *
		 * @param string $price product price.
		 * @param int    $product_id product id.
		 * @param object $product product.
		 * @param int    $quantity product quantity.
		 * @param array  $options product meta.
		 *
		 * @return mixed
		 */
		public function calculate_price( $price, $product_id, $product, $quantity, $options ) {

			$price_class = new THWEPO_Price();

			$price_info_list = array();

			foreach ( $options as $name => $data ) {
				if ( isset( $data['price_field'] ) && $data['price_field'] ) {
					$price_info               = $this->prepare_extra_price( $data );
					$price_info_list[ $name ] = $price_info;
				}
			}

			$price_data = $price_class->get_calculated_extra_price_response(
				array(
					'product_id'    => $product_id,
					'price_info'    => $price_info_list,
					'product_price' => $product->get_price(),
					'product'       => $product,
					'product_qty'   => $quantity,
				)
			);
			if ( 'E000' === $price_data['code'] && isset( $price_data['price_data']['price_final'] ) ) {
				$price = $price_data['price_data']['price_final'];
			}
			return $price;
		}

		/**
		 * Prepare extra price
		 *
		 * @param array|null $data field data.
		 *
		 * @return array|false
		 */
		private function prepare_extra_price( $data ) {
			$price_info = false;

			if ( is_array( $data ) ) {
				$field_type = $data['field_type'] ?? '';

				$price_info          = array();
				$price_info['name']  = $data['name'] ?? '';
				$price_info['label'] = $data['label'] ?? '';
				$price_info['value'] = $data['value'] ?? '';

				if ( $this->is_price_field_type_option( $field_type ) ) {
					$of_price_info = $this->prepare_option_field_price_props( $data );

					$price_info['price']          = $of_price_info['price'] ?? '';
					$price_info['price_type']     = $of_price_info['price_type'] ?? '';
					$price_info['price_unit']     = '';
					$price_info['price_min_unit'] = '';
				} else {
					$price_info['price']          = $data['price'] ?? '';
					$price_info['price_type']     = $data['price_type'] ?? '';
					$price_info['price_unit']     = $data['price_unit'] ?? '';
					$price_info['price_min_unit'] = $data['price_min_unit'] ?? '';
				}
				$price_info['multiple']       = $this->is_price_field_type_multi_option( $field_type, $data );
				$price_info['quantity']       = $data['quantity'] ?? '';
				$price_info['is_flat_fee']    = isset( $data['price_flat_fee'] ) && 'yes' === $data['price_flat_fee'];
				$price_info['custom_formula'] = $data['custom_formula'] ?? '';
			}

			return $price_info;
		}

		/**
		 * Check field type has options
		 *
		 * @param string $type field type.
		 *
		 * @return bool
		 */
		private function is_price_field_type_option( $type ) {
			if ( 'select' === $type || 'multiselect' === $type || 'radio' === $type || 'checkboxgroup' === $type || 'colorpalette' === $type || 'imagegroup' === $type ) {
				return true;
			}
			return false;
		}

		/**
		 * Check price type is multiple or single
		 *
		 * @param string $type field type.
		 * @param array  $data field data.
		 *
		 * @return bool
		 */
		private function is_price_field_type_multi_option( $type, $data ) {
			if ( 'multiselect' === $type || 'checkboxgroup' === $type ) {
				return true;
			} elseif ( $data && ( 'colorpalette' === $type || 'imagegroup' === $type ) ) {
				$value = $data['value'] ?? '';
				if ( is_array( $value ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Prepare option field price
		 *
		 * @param array $args arguments array.
		 *
		 * @return array
		 */
		private function prepare_option_field_price_props( $args ) {
			$price_props = array();
			$price       = '';
			$price_type  = '';

			$type    = $args['field_type'] ?? '';
			$name    = $args['name'] ?? '';
			$value   = $args['value'] ?? false;
			$options = $args['options'] ?? false;

			if ( ! is_array( $options ) || empty( $options ) ) {
				return $price_props;
			}

			$is_multiselect = false;
			if ( ( 'colorpalette' === $type || 'imagegroup' === $type ) && is_array( $value ) ) {
				$is_multiselect = true;
			}

			if ( 'select' === $type || 'radio' === $type || ( ! $is_multiselect && ( 'colorpalette' === $type || 'imagegroup' === $type ) ) ) {
				$selected_option = $options[ $value ] ?? false;

				if ( is_array( $selected_option ) ) {
					$price      = $selected_option['price'] ?? false;
					$price_type = $selected_option['price_type'] ?? false;
					$price_type = $price_type ? $price_type : 'normal';
				}
			} elseif ( 'multiselect' === $type || 'checkboxgroup' === $type || $is_multiselect ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $ovalue ) {
						$selected_option = $options[ $ovalue ] ?? false;

						if ( is_array( $selected_option ) ) {
							$oprice      = $selected_option['price'] ?? false;
							$oprice_type = $selected_option['price_type'] ?? false;

							if ( is_numeric( $oprice ) ) {
								$oprice_type = $oprice_type ? $oprice_type : 'normal';

								if ( ! empty( $price ) ) {
									$price .= ',';
								}

								if ( ! empty( $price_type ) ) {
									$price_type .= ',';
								}

								$price      .= $oprice;
								$price_type .= $oprice_type;
							}
						}
					}
				}
			}

			if ( ! empty( $price ) && ! empty( $price_type ) ) {
				$price_props['price']      = $price;
				$price_props['price_type'] = $price_type;
			}

			return $price_props;
		}

	}
}
