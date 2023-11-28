<?php
/**
 * Template for displaying the Css Editor Field
 *
 * @author MoreConvert
 * @package MoreConvert Options plugin
 * @version 1.2.0
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
 * @var $editor_height             integer Editor Height
 * @var $field                     array Array of all field attributes
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_style( 'mct-ace-editor' );
wp_enqueue_script( 'mct-ace-editor' );
?>
<textarea  id="textarea_ace_<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>"  style="display:none"><?php echo esc_textarea( $value ); ?></textarea>
<div  id="<?php echo esc_attr( $field_id ); ?>" class="css-editor  <?php echo esc_attr( $class ); ?>" data-target-id="textarea_ace_<?php echo esc_attr( $field_id ); ?>"
	<?php echo wp_kses_post( $dependencies ); ?>
	<?php echo wp_kses_post( $custom_attributes ); ?>
	<?php echo isset( $data ) ? wp_kses_post( $data ) : ''; ?>></div>
<?php if ( isset( $desc ) ) : ?>
	<br/><p class="description"><?php echo wp_kses_post( $desc ); ?></p>
<?php endif; ?>
