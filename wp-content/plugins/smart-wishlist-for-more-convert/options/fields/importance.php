<?php
/**
 * Template for displaying the Add importance Field
 *
 * @author MoreConvert
 * @package MoreConvert Options plugin
 * @version 2.3.0
 */

/**
 * Template variables:
 *
 * @var $name                      string Field name
 * @var $class                     string Field class
 * @var $field_id                  string Field Id
 * @var $value                     array Field value
 * @var $data                      string Data attributes
 * @var $custom_attributes         string Custom attributes
 * @var $dependencies                 string Dependencies
 * @var $desc                      string Description
 * @var $field                     array Array of all field attributes
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>
<div id="<?php echo esc_attr( $field_id ); ?>" class="mct-repeater simple-repeater <?php echo esc_attr( $class ); ?>"
	<?php echo wp_kses_post( $dependencies ); ?>>
	<table class="mct-border-table mct-responsive-table" data-repeater-list="<?php echo esc_attr( $name ); ?>">
		<thead>
		<tr>
			<th><?php esc_attr_e( 'Label', 'mct-options' ); ?></th>
			<th><?php esc_attr_e( 'Slug', 'mct-options' ); ?></th>
			<th><?php esc_attr_e( 'Action', 'mct-options' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php if ( is_array( $value ) && ! empty( $value ) ) : ?>
			<?php foreach ( $value as $k => $row ) : ?>
				<tr data-repeater-item>
					<td data-title="<?php esc_attr_e( 'Label', 'mct-options' ); ?>">
						<input class="" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $k ); ?>][label]" value="<?php echo esc_attr( $row['label'] ); ?>" type="text" autocomplete="off"/>
					</td>
					<td data-title="<?php esc_attr_e( 'Slug', 'mct-options' ); ?>">
						<input class="" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $k ); ?>][slug]" value="<?php echo esc_attr( $row['slug'] ); ?>" type="text" autocomplete="off"/>
					</td>
					<td data-title="<?php esc_attr_e( 'Action', 'mct-options' ); ?>">
						<a data-repeater-delete href="#"><span class="dashicons dashicons-dismiss"></span></a>
					</td>

				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr data-repeater-item>
				<td data-title="<?php esc_attr_e( 'Label', 'mct-options' ); ?>">
					<input class="" autocomplete="off" type="text" name="<?php echo esc_attr( $name ); ?>[][label]"/>
				</td>
				<td data-title="<?php esc_attr_e( 'Slug', 'mct-options' ); ?>">
					<input class="" name="<?php echo esc_attr( $name ); ?>[][slug]" type="text" autocomplete="off"/>
				</td>
				<td data-title="<?php esc_attr_e( 'Action', 'mct-options' ); ?>">
					<a data-repeater-delete href="#"><span class="dashicons dashicons-dismiss"></span></a>
				</td>
			</tr>
		<?php endif; ?>

		</tbody>
	</table>
	<button data-repeater-create type="button" class="btn-secondary">
		<?php esc_attr_e( 'Add new', 'mct-options' ); ?>
	</button>
</div>
<?php if ( isset( $desc ) ) : ?>
	<p class="description"><?php echo wp_kses_post( $desc ); ?></p>
<?php endif; ?>
