<?php
/**
 * WLFMC wishlist integration with Kadence theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.6.5
 */

namespace Kadence_Wlfmc;

/**
 * Main Header_Wishlist class
 */
class Header_Wishlist {
	/**
	 * Instance Control
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Holds theme settings array sections.
	 *
	 * @var array $settings_sections settings sections.
	 */
	public static $settings_sections = array(
		'header-wishlist',
		'header-mobile-wishlist',
	);

	/**
	 * Instance Control.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning instances of the class is Forbidden', 'kadence' ), '1.0' );
	}

	/**
	 * Disable un-serializing of the class.
	 *
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of the class is forbidden', 'kadence' ), '1.0' );
	}

	/**
	 * Constructor function.
	 */
	public function __construct() {
		add_filter( 'kadence_theme_customizer_sections', array( $this, 'add_customizer_sections' ), 10 );
		add_filter( 'kadence_theme_customizer_control_choices', array( $this, 'add_customizer_header_choices' ), 10 );
		add_action( 'customize_register', array( $this, 'create_wlfmc_settings_array' ), 1 );
		add_action( 'get_template_part_template-parts/header/wishlist', array( $this, 'header_wishlist_output' ), 10 );
		add_action( 'get_template_part_template-parts/header/mobile-wishlist', array( $this, 'header_mobile_wishlist_output' ), 10 );
	}

	/**
	 * Get header wishlist template.
	 */
	public function header_wishlist_output() {
		$this->locate_header_template( 'wishlist.php' );
	}
	/**
	 * Get header mobile wishlist template.
	 */
	public function header_mobile_wishlist_output() {
		$this->locate_header_template( 'mobile-wishlist.php' );
	}

	/**
	 * Output header template.
	 *
	 * @param string $template_name the name of the template.
	 */
	public function locate_header_template( $template_name ) {
		$template_path = 'kadence_wlfmc/';
		$default_path  = MC_WLFMC_INC . 'integrations/kadence/templates/';

		// Look within passed path within the theme - this is priority.
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);
		// Get default template/.
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		// Allow 3rd party plugin filter template file from their plugin.
		$filter_template = apply_filters( 'kadence_wlfmc_get_template', $template, $template_name, $template_path, $default_path );

		if ( $filter_template !== $template ) {
			if ( ! file_exists( $filter_template ) ) {
				return;
			}
			$template = $filter_template;
		}

		include $template;
	}

	/**
	 * Add Choices
	 *
	 * @access public
	 * @param array $choices registered choices with kadence theme.
	 * @return array
	 */
	public function add_customizer_header_choices( $choices ) {
		$choices['header_desktop_items']['wishlist']       = array(
			'name'    => esc_html__( 'Wishlist Counter', 'wc-wlfmc-wishlist' ),
			'section' => 'kadence_customizer_header_wishlist',
		);
		$choices['header_mobile_items']['mobile-wishlist'] = array(
			'name'    => esc_html__( 'Mobile Wishlist Counter', 'wc-wlfmc-wishlist' ),
			'section' => 'kadence_customizer_mobile_wishlist',
		);
		return $choices;
	}
	/**
	 * Add Sections
	 *
	 * @access public
	 * @param array $sections registered sections with kadence theme.
	 * @return array
	 */
	public function add_customizer_sections( $sections ) {
		$sections['header_wishlist'] = array(
			'title'    => __( 'Wishlist Counter', 'wc-wlfmc-wishlist' ),
			'panel'    => 'header',
			'priority' => 20,
		);
		$sections['mobile_wishlist'] = array(
			'title'    => __( 'Mobile Wishlist Counter', 'wc-wlfmc-wishlist' ),
			'panel'    => 'header',
			'priority' => 20,
		);
		return $sections;
	}
	/**
	 * Registers the sidebars.
	 */


	/**
	 * Add settings
	 *
	 * @access public
	 * @param object $wp_customize the customizer object.
	 * @return void
	 */
	public function create_wlfmc_settings_array( $wp_customize ) {
		// Load Settings files.
		foreach ( self::$settings_sections as $key ) {
			require_once MC_WLFMC_INC . 'integrations/kadence/' . $key . '-options.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound
		}
	}
}

Header_Wishlist::get_instance();
