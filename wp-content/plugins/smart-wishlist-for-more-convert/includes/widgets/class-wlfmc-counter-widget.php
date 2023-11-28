<?php
/**
 * Smart Wishlist Counter Widget
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Counter_Widget' ) ) {
	/**
	 * Wishlist Counter Widget
	 *
	 * @since 1.3.0
	 */
	class WLFMC_Counter_Widget extends WP_Widget {

		/**
		 * Sets up the widgets
		 */
		public function __construct() {
			parent::__construct(
				'wlfmc-wishlist-counter',
				__( 'MC Wishlist counters', 'wc-wlfmc-wishlist' ),
				array( 'description' => esc_html__( 'A list of products in the user\'s wishlists', 'wc-wlfmc-wishlist' ) )
			);
		}

		/**
		 * Outputs the content of the widget
		 *
		 * @param array $args     Arguments.
		 * @param array $instance Widget instance.
		 */
		public function widget( $args, $instance ) {
			$instance['counter_text'] = $instance['counter_text'] ?? '';
			$instance['show_icon']    = $instance['show_icon'] ?? 'no';
			$instance['show_text']    = $instance['show_text'] ?? 'no';
			$instance['show_list']    = $instance['show_list'] ?? 'no';
			$instance['show_button']  = $instance['show_button'] ?? 'no';
			$instance['show_totals']  = $instance['show_totals'] ?? 'no';
			$dropdown                 = 'no' === $instance['show_list'] ? 'yes' : 'no';
			$no_padding               = 'no' === $dropdown ? 'wlfmc_no_pad_list' : '';
			echo do_shortcode( '[wlfmc_wishlist_counter container_class=" ' . esc_attr( $no_padding ) . '" show_button="' . esc_attr( $instance['show_button'] ) . '" show_totals="' . esc_attr( $instance['show_totals'] ) . '" dropdown_products="' . $dropdown . '"  show_icon="' . esc_attr( $instance['show_icon'] ) . '" show_text="' . esc_attr( $instance['show_text'] ) . '" counter_text="' . esc_attr( $instance['counter_text'] ) . '"]' );
		}

		/**
		 * Outputs the options form on admin
		 *
		 * @param array $instance The widget options.
		 */
		public function form( $instance ) {
			$counter_text = $instance['counter_text'] ?? '';
			$show_icon    = ( isset( $instance['show_icon'] ) && 'yes' === $instance['show_icon'] );
			$show_text    = ( isset( $instance['show_text'] ) && 'yes' === $instance['show_text'] );
			$show_list    = ( isset( $instance['show_list'] ) && 'yes' === $instance['show_list'] );
			$show_button  = ( isset( $instance['show_button'] ) && 'yes' === $instance['show_button'] );
			$show_totals  = ( isset( $instance['show_totals'] ) && 'yes' === $instance['show_totals'] );
			?>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_icon' ) ); ?>">
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_icon' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_icon' ) ); ?>" type="checkbox" value="yes" <?php checked( $show_icon ); ?> />
					<?php esc_html_e( 'Show counter icon', 'wc-wlfmc-wishlist' ); ?>
				</label><br/>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_text' ) ); ?>">
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_text' ) ); ?>" type="checkbox" value="yes" <?php checked( $show_text ); ?> />
					<?php esc_html_e( 'Show counter text', 'wc-wlfmc-wishlist' ); ?>
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'counter_text' ) ); ?>"><?php esc_html_e( 'Counter text:', 'wc-wlfmc-wishlist' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'counter_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'counter_text' ) ); ?>" type="text" value="<?php echo esc_attr( $counter_text ); ?>"/>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_list' ) ); ?>">
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_list' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_list' ) ); ?>" type="checkbox" value="yes" <?php checked( $show_list ); ?> />
					<?php esc_html_e( 'Show products as a list', 'wc-wlfmc-wishlist' ); ?>
				</label>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_button' ) ); ?>">
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_button' ) ); ?>" type="checkbox" value="yes" <?php checked( $show_button ); ?> />
					<?php esc_html_e( 'Show wishlist button', 'wc-wlfmc-wishlist' ); ?>
				</label>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_totals' ) ); ?>">
					<input id="<?php echo esc_attr( $this->get_field_id( 'show_totals' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_totals' ) ); ?>" type="checkbox" value="yes" <?php checked( $show_totals ); ?> />
					<?php esc_html_e( 'Show products total', 'wc-wlfmc-wishlist' ); ?>
				</label>
			</p>
			<?php
		}

		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options.
		 * @param array $old_instance The previous options.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();

			$instance['show_icon']    = ( isset( $new_instance['show_icon'] ) ) ? sanitize_text_field( $new_instance['show_icon'] ) : 'no';
			$instance['show_list']    = ( isset( $new_instance['show_list'] ) ) ? sanitize_text_field( $new_instance['show_list'] ) : 'no';
			$instance['show_text']    = ( isset( $new_instance['show_text'] ) ) ? sanitize_text_field( $new_instance['show_text'] ) : 'no';
			$instance['show_totals']  = ( isset( $new_instance['show_totals'] ) ) ? sanitize_text_field( $new_instance['show_totals'] ) : 'no';
			$instance['show_button']  = ( isset( $new_instance['show_button'] ) ) ? sanitize_text_field( $new_instance['show_button'] ) : 'no';
			$instance['counter_text'] = ( isset( $new_instance['counter_text'] ) ) ? sanitize_text_field( $new_instance['counter_text'] ) : '';

			return $instance;
		}
	}
}
