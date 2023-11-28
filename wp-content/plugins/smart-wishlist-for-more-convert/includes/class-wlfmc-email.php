<?php
/**
 * Smart Wishlist email class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 *
 * @version 1.4.4
 * @since 1.3.3
 */

use Pelago\Emogrifier\CssInliner;
use Pelago\Emogrifier\HtmlProcessor\CssToAttributeConverter;
use Pelago\Emogrifier\HtmlProcessor\HtmlPruner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'WLFMC_Email' ) ) {
	/**
	 * WooCommerce Smart Wishlist Email
	 */
	class WLFMC_Email {
		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Email
		 */
		protected static $instance;

		/**
		 *  List of preg* regular expression patterns to search for,
		 *  used in conjunction with $plain_replace.
		 *  https://raw.github.com/ushahidi/wp-silcc/master/class.html2text.inc
		 *
		 * @var array $plain_search
		 * @see $plain_replace
		 */
		public $plain_search = array(
			"/\r/",                                                  // Non-legal carriage return.
			'/&(nbsp|#0*160);/i',                                    // Non-breaking space.
			'/&(quot|rdquo|ldquo|#0*8220|#0*8221|#0*147|#0*148);/i', // Double quotes.
			'/&(apos|rsquo|lsquo|#0*8216|#0*8217);/i',               // Single quotes.
			'/&gt;/i',                                               // Greater-than.
			'/&lt;/i',                                               // Less-than.
			'/&#0*38;/i',                                            // Ampersand.
			'/&amp;/i',                                              // Ampersand.
			'/&(copy|#0*169);/i',                                    // Copyright.
			'/&(trade|#0*8482|#0*153);/i',                           // Trademark.
			'/&(reg|#0*174);/i',                                     // Registered.
			'/&(mdash|#0*151|#0*8212);/i',                           // mdash.
			'/&(ndash|minus|#0*8211|#0*8722);/i',                    // ndash.
			'/&(bull|#0*149|#0*8226);/i',                            // Bullet.
			'/&(pound|#0*163);/i',                                   // Pound sign.
			'/&(euro|#0*8364);/i',                                   // Euro sign.
			'/&(dollar|#0*36);/i',                                   // Dollar sign.
			'/&[^&\s;]+;/i',                                         // Unknown/unhandled entities.
			'/[ ]{2,}/',                                             // Runs of spaces, post-handling.
		);

		/**
		 *  List of pattern replacements corresponding to pattern searched.
		 *
		 * @var array $plain_replace
		 * @see $plain_search
		 */
		public $plain_replace = array(
			'',                                             // Non-legal carriage return.
			' ',                                            // Non-breaking space.
			'"',                                            // Double quotes.
			"'",                                            // Single quotes.
			'>',                                            // Greater-than.
			'<',                                            // Less-than.
			'&',                                            // Ampersand.
			'&',                                            // Ampersand.
			'(c)',                                          // Copyright.
			'(tm)',                                         // Trademark.
			'(R)',                                          // Registered.
			'--',                                           // mdash.
			'-',                                            // ndash.
			'*',                                            // Bullet.
			'£',                                            // Pound sign.
			'EUR',                                          // Euro sign. € ?.
			'$',                                            // Dollar sign.
			'',                                             // Unknown/unhandled entities.
			' ',                                             // Runs of spaces, post-handling.
		);
		/**
		 * Email type
		 *
		 * @var string
		 */
		public $mail_type;

		/**
		 * Constructor method, used to return object of the class to WC
		 *
		 * @param string $mail_type Email type.
		 */
		public function __construct( string $mail_type = 'html' ) {
			$this->set_mail_type( $mail_type );
		}

		/**
		 * Get from name.
		 *
		 * @return void
		 */
		public function get_from_name() {
		}

		/**
		 * GEt from address.
		 *
		 * @return void
		 */
		public function get_from_address() {
		}

		/**
		 * Set email type.
		 *
		 * @param string $mail_type Email type.
		 *
		 * @return void
		 */
		public function set_mail_type( string $mail_type ) {
			$this->mail_type = $mail_type;
		}

		/**
		 * Get email type
		 *
		 * @return string
		 */
		public function get_mail_type(): string {
			return $this->mail_type;
		}

		/**
		 * Get email content type.
		 *
		 * @return string
		 */
		public function get_content_type(): string {

			switch ( $this->get_mail_type() ) {
				case 'html':
				case 'simple-template':
				case 'mc-template':
					$content_type = 'text/html';
					break;
				default:
					$content_type = 'text/plain';
					break;
			}

			return $content_type;
		}

		/**
		 * Prepare email content.
		 *
		 * @param string|null $content Email content.
		 * @param string      $email_type Email type.
		 *
		 * @return string
		 */
		public function prepare_content( $content, string $email_type = 'html' ) {
			if ( ! $content ) {
				return '';
			}
			if ( 'plain' === $email_type ) {
				$email_content = wordwrap( preg_replace( $this->plain_search, $this->plain_replace, wp_strip_all_tags( $content ) ?? '' ), 70 );
			} else {
				$email_content = $content;
			}

			return $email_content;
		}

		/**
		 * Apply inline styles to dynamic content.
		 *
		 * We only inline CSS for html emails, and to do so we use Emogrifier library (if supported).
		 *
		 * @since 1.4.4
		 * @param string|null $content Content that will receive inline styles.
		 * @return string
		 */
		public function style_inline( $content ) {
			if ( 'html' === $this->get_mail_type() ) {
				ob_start();
				wc_get_template( 'emails/email-styles.php' );
				$css = apply_filters( 'woocommerce_email_styles', ob_get_clean(), $this );

				$css_inliner_class = CssInliner::class;

				if ( $this->supports_emogrifier() && class_exists( $css_inliner_class ) ) {
					try {
						$css_inliner = CssInliner::fromHtml( $content )->inlineCss( $css );

						do_action( 'woocommerce_emogrifier', $css_inliner, $this );

						$dom_document = $css_inliner->getDomDocument();

						HtmlPruner::fromDomDocument( $dom_document )->removeElementsWithDisplayNone();
						$content = CssToAttributeConverter::fromDomDocument( $dom_document )->convertCssToVisualAttributes()->render();
					} catch ( Exception $e ) {
						$logger = wc_get_logger();
						$logger->error( $e->getMessage(), array( 'source' => 'emogrifier' ) );
					}
				} else {
					$content = '<style>' . $css . '</style>' . $content;
				}
			}

			return $content;
		}

		/**
		 * Return if emogrifier library is supported.
		 *
		 * @since 1.4.4
		 * @return bool
		 */
		protected function supports_emogrifier() {
			return class_exists( 'DOMDocument' );
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC_Email
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

	}
}


