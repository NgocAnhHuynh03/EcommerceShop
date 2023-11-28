<?php
/**
 * WLFMC wishlist integration with neve theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.6.6
 */

namespace WLFMC_Neve;

use HFG\Core\Components\Abstract_Component;
use HFG\Core\Settings\Manager as SettingsManager;
use HFG\Main;
use function HFG\component_setting;
/**
 * Class Wishlist_Counter
 *
 * @package HFG\Core\Components
 */
class Wishlist_Counter extends Abstract_Component {

	const COMPONENT_ID          = 'wishlist_counter';
	const WISHLIST_TITLE        = '';
	const DISPLAY_MINI_WISHLIST = 'counter-only';
	const ADD_LINK_TITLE        = true;

	/**
	 * Button constructor.
	 */
	public function init() {
		$this->set_property( 'label', __( 'Wishlist Counter', 'wc-wlfmc-wishlist' ) );
		$this->set_property( 'id', self::COMPONENT_ID );
		$this->set_property( 'width', 1 );
		$this->set_property( 'is_auto_width', true );
		$this->set_property( 'default_selector', '.builder-item--' . $this->get_id() );
		$this->set_property(
			'default_padding_value',
			array(
				'mobile'       => array(
					'top'    => 0,
					'right'  => 10,
					'bottom' => 0,
					'left'   => 10,
				),
				'tablet'       => array(
					'top'    => 0,
					'right'  => 10,
					'bottom' => 0,
					'left'   => 10,
				),
				'desktop'      => array(
					'top'    => 0,
					'right'  => 10,
					'bottom' => 0,
					'left'   => 10,
				),
				'mobile-unit'  => 'px',
				'tablet-unit'  => 'px',
				'desktop-unit' => 'px',
			)
		);
	}

	/**
	 * Called to register component controls.
	 */
	public function add_settings() {

		SettingsManager::get_instance()->add(
			array(
				'id'                 => self::WISHLIST_TITLE,
				'group'              => self::COMPONENT_ID,
				'tab'                => SettingsManager::TAB_GENERAL,
				'transport'          => 'post' . $this->get_builder_id(),
				'sanitize_callback'  => 'wp_filter_nohtml_kses',
				'default'            => __( 'Wishlist', 'wc-wlfmc-wishlist' ),
				'label'              => __( 'Custom title', 'wc-wlfmc-wishlist' ),
				'type'               => 'text',
				'section'            => $this->section,
				'conditional_header' => true,
			)
		);

		SettingsManager::get_instance()->add(
			array(
				'id'                 => self::DISPLAY_MINI_WISHLIST,
				'group'              => self::COMPONENT_ID,
				'tab'                => SettingsManager::TAB_GENERAL,
				'transport'          => 'post' . $this->get_builder_id(),
				'sanitize_callback'  => 'wp_filter_nohtml_kses',
				'default'            => 'counter-only',
				'label'              => __( 'Display mini wishlist for counter', 'wc-wlfmc-wishlist' ),
				'type'               => 'select',
				'options'            => array(
					'choices' => array(
						'counter-only' => __( 'Disabled', 'wc-wlfmc-wishlist' ),
						'on-hover'     => __( 'Show on hover', 'wc-wlfmc-wishlist' ),
						'on-click'     => __( 'Show on click', 'wc-wlfmc-wishlist' ),
					),
				),
				'section'            => $this->section,
				'conditional_header' => true,
			)
		);
		SettingsManager::get_instance()->add(
			array(
				'id'                 => self::ADD_LINK_TITLE,
				'group'              => self::COMPONENT_ID,
				'tab'                => SettingsManager::TAB_GENERAL,
				'transport'          => 'post' . $this->get_builder_id(),
				'sanitize_callback'  => 'absint',
				'default'            => 0,
				'label'              => __( 'Add Link For "Wishlist" Counter Title', 'wc-wlfmc-wishlist' ),
				'type'               => 'neve_toggle_control',
				'section'            => $this->section,
				'conditional_header' => true,
			)
		);
	}

	/**
	 * The render method for the component.
	 */
	public function render_component() {
		Main::get_instance()->load( 'component-wishlist-counter' );
	}
}
