<?php
/**
 * Template for displaying the Import Field
 *
 * @author MoreConvert
 * @package MoreConvert Options plugin
 * @version 2.0.0
 */

/**
 * Template variables:
 *
 * @var $name                      string Field name
 * @var $class                     string Field class
 * @var $field_id                  string Field Id
 * @var $value                     string Field value
 * @var $data                      string Data attributes
 * @var $custom_attributes         string Custom attributes
 * @var $dependencies                 string Dependencies
 * @var $desc                      string Description
 * @var $field                     array Array of all field attributes
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="upload-file <?php echo ! empty( $class ) ? esc_attr( $class ) : ''; ?>" id="<?php echo esc_attr( $field_id ); ?>"
	<?php echo wp_kses_post( $dependencies ); ?>
	<?php echo wp_kses_post( $custom_attributes ); ?>
	<?php echo isset( $data ) ? wp_kses_post( $data ) : ''; ?>>
	<div>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
		<button type="submit" class="mct_remove_file_button btn-secondary min-pad"  style="display:none"><span class="dashicons dashicons-no-alt"></span></button>
		<button type="submit" class="mct_upload_file_button min-width-btn btn-secondary" data-label="<?php esc_attr_e( 'Select File', 'mct-options' ); ?>"><?php esc_attr_e( 'Select File', 'mct-options' ); ?></button>
		<button type="submit" class="mct_import_file_button btn-primary min-pad" style="display:none"><?php esc_attr_e( 'Import', 'mct-options' ); ?></button>
	</div>
</div>
<?php if ( isset( $desc ) ) : ?>
	<p class="description" <?php echo wp_kses_post( $dependencies ); ?>><?php echo wp_kses_post( $desc ); ?></p>
<?php endif; ?>
