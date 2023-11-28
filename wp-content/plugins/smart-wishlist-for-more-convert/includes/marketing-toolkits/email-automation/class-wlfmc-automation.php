<?php
/**
 * Smart Wishlist Automation
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WLFMC_Automation' ) ) {
	/**
	 * Woocommerce Smart Wishlist Automation
	 */
	class WLFMC_Automation {
		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Automation
		 */
		protected static $instance;


		/**
		 * Automation data
		 *
		 * @var array
		 */
		protected $data = array(
			'id'               => 0,
			'automation_name'  => '',
			'is_active'        => 0,
			'send_queue'       => 0,
			'opened'           => 0,
			'clicked'          => 0,
			'click_rate'       => '',
			'open_rate'        => '',
			'coupon_used'      => '',
			'recipients_total' => 0,
			'net_total'        => 0,
			'created_at'       => '',
			'options'          => array(),
		);

		/**
		 * Constructor
		 *
		 * @param int|WLFMC_Automation $automation Automation.
		 *
		 * @return void
		 */
		public function __construct( $automation = 0 ) {

			if ( is_numeric( $automation ) && $automation > 0 ) {
				$this->set_id( $automation );
			} elseif ( $automation instanceof self ) {
				$this->set_id( $automation->get_id() );
			}

			if ( $this->get_id() > 0 ) {
				$this->load();
			}
		}

		/**
		 * Load automation data from Db.
		 *
		 * @return false|void
		 */
		protected function load() {
			global $wpdb;
			$automation = $wpdb->get_row( //phpcs:ignore WordPress.DB
				$wpdb->prepare(
					"SELECT automation.* ,
       			sum(IF(items.status IN ('sent', 'opened','clicked' ,'coupon-used'), 1, 0)) sent,
	        	sum(IF(items.status IN ('sending' , 'not-send'), 1, 0)) send_queue,
	            sum(IF(items.status IN ('opened','clicked' ,'coupon-used'), 1, 0)) opened,
	            sum(IF(items.status IN ('clicked' ,'coupon-used'), 1, 0)) clicked,
       			sum(IF(items.status = 'coupon-used', 1, 0)) coupon_used,
           		(sum(IF(items.status IN ('opened','clicked' ,'coupon-used'), 1, 0)) / sum(IF(items.status IN ('sent', 'opened','clicked' ,'coupon-used'), 1, 0)) * 100 ) AS open_rate,
            	(sum(IF(items.status IN ('clicked' ,'coupon-used'), 1, 0)) / sum(IF(items.status IN ('sent', 'opened','clicked' ,'coupon-used'), 1, 0)) * 100 ) AS click_rate,
	            sum(IF(items.net > 0, items.net, 0)) net_total,
       			COUNT(DISTINCT(items.customer_id)) as recipients_total
	            FROM $wpdb->wlfmc_wishlist_automations as automation
	            LEFT JOIN $wpdb->wlfmc_wishlist_offers as items ON automation.ID = items.automation_id WHERE automation.is_pro = 0 AND  automation.ID=%d  GROUP BY automation.ID",
					$this->get_id()
				),
				ARRAY_A
			);// phpcs:ignore WordPress.DB

			if ( is_null( $automation ) ) {
				$this->set_id( 0 );

				return false;
			}

			$options                    = maybe_unserialize( $automation['options'] );
			$options                    = is_array( $options ) ? $options : array();
			$options['is_active']       = $automation['is_active'];
			$options['automation_name'] = $automation['automation_name'];

			$options    = wp_parse_args(
				$options,
				array(
					'include-product'      => array(),
					'free-shipping'        => '',
					'individual-use'       => '',
					'exclude-sale-items'   => '',
					'user-restriction'     => '',
					'delete-after-expired' => '',
				)
			);
			$this->data = array(
				'id'               => $automation['ID'],
				'is_active'        => $automation['is_active'],
				'automation_name'  => $automation['automation_name'],
				'sent'             => $automation['sent'],
				'send_queue'       => $automation['send_queue'],
				'opened'           => $automation['opened'],
				'clicked'          => $automation['clicked'],
				'coupon_used'      => $automation['coupon_used'],
				'click_rate'       => floatval( $automation['click_rate'] ),
				'open_rate'        => floatval( $automation['open_rate'] ),
				'recipients_total' => $automation['recipients_total'],
				'net_total'        => $automation['net_total'],
				'options'          => $options,
				'created_at'       => $automation['created_at'],
			);
		}

		/**
		 * Set key
		 *
		 * @param string $key Data key.
		 * @param mixed  $value Value of key.
		 *
		 * @return void
		 */
		public function __set( $key, $value ) {
			if ( array_key_exists( $key, $this->data ) ) {
				$this->data[ $key ] = $value;
			}

		}

		/**
		 * Get key
		 *
		 * @param string $key Data key.
		 *
		 * @return mixed|null
		 */
		public function __get( $key ) {
			if ( array_key_exists( $key, $this->data ) ) {
				return $this->data[ $key ];
			}

			return null;
		}

		/**
		 * Update Automation.
		 */
		public function update() {
			global $wpdb;
			$update = $wpdb->update( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->wlfmc_wishlist_automations,
				array(
					'automation_name' => $this->get_name(),
					'is_active'       => $this->is_active(),
					'options'         => serialize( $this->get_options() ), //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
				),
				array(
					'ID'     => $this->get_id(),
					'is_pro' => 0,
				),
				array( '%s', '%d', '%s' ),
				array( '%d', '%d' )
			);
			if ( ! is_wp_error( $update ) && $this->is_active() ) {
				// deactivate other automations.
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_automations SET is_active=0 WHERE  ID != %d", $this->get_id() ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery

			}

			return $update;
		}


		/**
		 * Insert Automation.
		 *
		 * @return int|false The number of rows inserted, or false on error.
		 */
		public function insert() {
			global $wpdb;
			$insert = $wpdb->insert( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->wlfmc_wishlist_automations,
				array(
					'automation_name' => $this->get_name(),
					'is_active'       => $this->is_active(),
					'options'         => serialize( $this->get_options() ), //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
				),
				array( '%s', '%s', '%d', '%s' )
			);

			if ( ! is_wp_error( $insert ) ) {
				$this->set_id( $wpdb->insert_id );

				if ( $this->is_active() ) {
					// deactivate other automations.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_automations SET is_active=0 WHERE  ID != %d", $this->get_id() ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
				}
			}

			return $insert;
		}


		/**
		 * Delete a automation.
		 *
		 * @return bool
		 */
		public function delete() {
			global $wpdb;

			$items_deleted = $wpdb->delete( $wpdb->wlfmc_wishlist_offers, array( 'automation_id' => $this->get_id() ), array( '%d' ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
			if ( $items_deleted || 0 === absint( $this->get_recipients_total() ) ) {
				$automation_deleted = $wpdb->delete( $wpdb->wlfmc_wishlist_automations, array( 'ID' => $this->get_id() ), array( '%d' ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
				if ( $automation_deleted ) {
					$this->set_id( 0 );

					return true;
				}
			}

			return false;
		}


		/**
		 * Save a automation.
		 *
		 * @return mixed
		 */
		public function save() {

			if ( $this->get_id() ) {
				$this->update();
			} else {
				$this->insert();
			}

			return $this->get_id();
		}

		/**
		 * Get automation id.
		 *
		 * @return mixed
		 */
		public function get_id() {
			return $this->data['id'];
		}

		/**
		 * Get automation name.
		 *
		 * @return mixed
		 */
		public function get_name() {
			return $this->data['automation_name'];
		}

		/**
		 * Get automation options.
		 *
		 * @return mixed
		 */
		public function get_options() {
			return $this->data['options'];
		}


		/**
		 *  Get from name for email.
		 *
		 * @return string
		 */
		public function get_from_name(): string {

			return $this->data['options']['email-from-name'] ?? '';

		}

		/**
		 *  Get from address for email.
		 *
		 * @return string
		 */
		public function get_from_address(): string {

			return $this->data['options']['email-from-address'] ?? '';

		}

		/**
		 *  Get from mail type.
		 *
		 * @return string
		 */
		public function get_mail_type(): string {

			return $this->data['options']['mail-type'] ?? '';

		}

		/**
		 * Get email content type.
		 *
		 * @return string
		 */
		public function get_content_type() {

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
		 * Get automation is active or not.
		 *
		 * @return bool
		 */
		public function is_active(): bool {
			return wlfmc_is_true( $this->data['is_active'] );
		}


		/**
		 * Get recipients total count.
		 *
		 * @param string $context Context.
		 *
		 * @return mixed|string
		 */
		public function get_recipients_total( string $context = 'view' ) {
			$value = $this->data['recipients_total'];
			if ( 'view' === $context ) {
				$value = number_format( (float) $value );
			}

			return $value;

		}


		/**
		 * Get open and click rate and recipients by email key.
		 *
		 * @param int $email_key Email key.
		 *
		 * @return array|object|stdClass|null
		 */
		public function get_email_key_states( int $email_key ) {
			global $wpdb;

			return $wpdb->get_row( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT
           		(sum(IF(items.status IN ('opened','clicked' ,'coupon-used'), 1, 0)) / sum(IF(items.status IN ('sent', 'opened','clicked' ,'coupon-used'), 1, 0)) * 100 ) AS open_rate,
            	(sum(IF(items.status IN ('clicked' ,'coupon-used'), 1, 0)) / sum(IF(items.status IN ('sent', 'opened','clicked' ,'coupon-used'), 1, 0)) * 100 ) AS click_rate,
	            sum(IF(items.net > 0, items.net, 0)) as net,
       			COUNT(DISTINCT(items.customer_id)) as recipients
	            FROM $wpdb->wlfmc_wishlist_automations as automation
	            LEFT JOIN $wpdb->wlfmc_wishlist_offers as items ON automation.ID = items.automation_id WHERE  automation.ID=%d AND items.email_key=%d GROUP BY automation.ID",
					$this->get_id(),
					$email_key
				),
				ARRAY_A
			);

		}


		/**
		 * Get net total amount.
		 *
		 * @param string $context Context.
		 *
		 * @return mixed|string
		 */
		public function get_net_total( string $context = 'view' ) {

			$value = $this->data['net_total'];
			if ( 'view' === $context ) {
				$value = wc_price( $value );
			}

			return $value;
		}

		/**
		 * Get sent total count.
		 *
		 * @param string $context Context.
		 *
		 * @return mixed|string
		 */
		public function get_sent( string $context = 'view' ) {

			$value = $this->data['sent'];
			if ( 'view' === $context ) {
				$value = number_format( (float) $value );
			}

			return $value;
		}

		/**
		 * Get send queue count.
		 *
		 * @param string $context Context.
		 *
		 * @return mixed|string
		 */
		public function get_send_queue( string $context = 'view' ) {

			$value = $this->data['send_queue'];
			if ( 'view' === $context ) {
				$value = number_format( (float) $value );
			}

			return $value;
		}

		/**
		 * Get open rate.
		 *
		 * @param string $context Context.
		 *
		 * @return float|int|string
		 */
		public function get_open_rate( string $context = 'view' ) {

			$value = $this->data['open_rate'];
			if ( 'view' === $context ) {
				$value = number_format( (float) $value ) . '%';
			}

			return $value;
		}

		/**
		 * Get click rate.
		 *
		 * @param string $context Context.
		 *
		 * @return float|int|string
		 */
		public function get_click_rate( $context = 'view' ) {

			$value = $this->data['click_rate'];
			if ( 'view' === $context ) {
				$value = number_format( (float) $value ) . '%';
			}

			return $value;
		}


		/**
		 * Get count email offer in queue by email_key
		 *
		 * @param int $email_key Email key.
		 *
		 * @return int
		 */
		public function get_count_send_queue_by_email_key( int $email_key = 0 ): int {
			global $wpdb;

			return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) as count FROM $wpdb->wlfmc_wishlist_offers WHERE automation_id=%d AND  email_key=%d AND status in ('sending' , 'not-send')", $this->get_id(), $email_key ) );//phpcs:ignore WordPress.DB
		}


		/**
		 * Get email queues
		 *
		 * @param int $limit limit.
		 *
		 * @return array|object|stdClass[]|null
		 */
		public function get_email_queue( int $limit ) {
			global $wpdb;

			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->wlfmc_wishlist_offers WHERE automation_id=%d AND status in ('sending' , 'not-send') AND  `datesend` < NOW() LIMIT %d", $this->get_id(), $limit ) );//phpcs:ignore WordPress.DB
		}

		/**
		 * Delete email queue that not sent
		 */
		public function delete_email_queue() {
			global $wpdb;
			$count = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->wlfmc_wishlist_offers WHERE automation_id=%d AND status in ('sending' , 'not-send') ", $this->get_id() ) );//phpcs:ignore WordPress.DB

			return $count > 0;
		}

		/**
		 * Returns text with placeholders that can be used in this email
		 *
		 * @param string $email_type Email type.
		 * @param int    $number number of default content.
		 *
		 * @return string Placeholders
		 */
		public static function get_default_content( $email_type, $number = 0 ) {
			switch ( $number ) {
				case 1:
					if ( 'plain' === $email_type ) {
						return _x(
							'Hi {user_first_name},

 I\'ll keep this short to make the 19 seconds it takes to read this worth your time (yes, I timed it.)

If you remember, you were interested in some of our products, which are still on your Wishlist.
Would it be helpful if we send you the wishlist link?
{wishlist_url}

Thinking about The specific result achieved after the purchase?
So finalize your purchase?

Thanks so much for your attention
Regards,
{site_name}',
							'Unnecessary translation',
							'wc-wlfmc-wishlist'
						);
					} else {
						return _x(
							'<p>Hi {user_first_name},</p>
<br>
<p>I\'ll keep this short to make the 19 seconds it takes to read this worth your time (yes, I timed it.)</p>
<br>
<p>If you remember, you were interested in some of our products, which are still on your Wishlist.
Would it be helpful if we send you the wishlist link? </p>
<p><a href="{wishlist_url}">Wishlist</a></p>
<br>
<p>Thinking about The specific result achieved after the purchase?</p>
<p>So finalize your purchase?</p>
<br>
<p>Thanks so much for your attention</p>
<p>Regards,</p>
<p>{site_name}</p>',
							'Unnecessary translation',
							'wc-wlfmc-wishlist'
						);
					}
				case 2:
					if ( 'plain' === $email_type ) {
						return _x(
							'Hey {user_first_name},
It\'s {employee name} on this side.

I’ve got good news for you: Now you can get your favorite product for {coupon_amount} off!  Simply use the code below to get it:
{coupon_code}
{wishlist_url}

Remember to use this discount code at the checkout and it\'s valid until {expiry_date}.

Please let us know if you need any assistance. Hope you like the deal!

Best,
{site_name}',
							'Unnecessary translation',
							'wc-wlfmc-wishlist'
						);
					} else {
						return _x(
							'<p>Hey {user_first_name},</p>
<p>It\'s {employee name} on this side.</p>
<br>
<p>I’ve got good news for you: Now you can get your favorite product for {coupon_amount} off!  Simply use the code below to get it:</p>
<p>{coupon_code}</p>
<p><a href="{wishlist_url}">Wishlist</a></p>
<br>
<p>Remember to use this discount code at the checkout and it\'s valid until {expiry_date}.</p>
<br>
<p>Please let us know if you need any assistance. Hope you like the deal!</p>
<br>
<p>Best,</p>
<p>{site_name}</p>',
							'Unnecessary translation',
							'wc-wlfmc-wishlist'
						);
					}
				case 3:
					if ( 'plain' === $email_type ) {
						return _x(
							'Don’t hesitate !

Howdy,
{employee name} again!
The best deals are selling out fast!

You have an opportunity to buy. Your time isn’t unlimited, so make sure you make your decision fast and get benefited.
{coupon_code}
{checkout_url}

I\'n not going anywhere, so I’ll be here if you need my help.
{site_name}',
							'Unnecessary translation',
							'wc-wlfmc-wishlist'
						);
					} else {
						return _x(
							'<p>Don’t hesitate !</p>
<br>
<p>Howdy,</p>
<p>{employee name} again!</p>
<p>The best deals are selling out fast!</p>
<br>
<p>You have an opportunity to buy. Your time isn’t unlimited, so make sure you make your decision fast and get benefited.</p>
<p>{coupon_code}</p>
<p><a href="{checkout_url}">Checkout Now!</a></p>
<br>
<p>I\'n not going anywhere, so I’ll be here if you need my help.</p>
<p>{site_name}</p>',
							'Unnecessary translation',
							'wc-wlfmc-wishlist'
						);
					}
				case 4:
					if ( 'plain' === $email_type ) {
						return _x(
							'Time is running out!
You have limited time to buy your favorite product for the lowest price.

In order not to waste time:
I want to use  {coupon_amount} off: {checkout_url}
I want to miss it out: {shop_url}

1. Use the code {coupon_code} at checkout. it\'s only valid until {expiry_date}.
2. My number: --- (If you have any questions)
3. Website support number: --- (Customer Service)

Have a quick and good purchase,
{site_name}',
							'Unnecessary translation',
							'wc-wlfmc-wishlist'
						);
					} else {
						return _x(
							'<p>Time is running out!</p>
<p>You have limited time to buy your favorite product for the lowest price</p>
<br>
<p>In order not to waste time:</p>
<p>I want to use  {coupon_amount} off: <a href="{checkout_url}">Checkout Now!</a></p>
<p>I want to miss it out:<a href="{shop_url}">Website</a></p>
<br>
<p>1. Use the code {coupon_code} at checkout. it\'s only valid until {expiry_date}.</p>
<p>2. My number: --- (If you have any questions)</p>
<p>3. Website support number: --- (Customer Service)</p>
<br>
<p>Have a quick and good purchase,</p>
<p>{site_name}</p>',
							'Unnecessary translation',
							'wc-wlfmc-wishlist'
						);
					}
				case 5:
					if ( 'plain' === $email_type ) {
						return _x(
							'opportunities don\'t wait.
We give you time to get your lovely products and tomorrow you can\'t.
In order not to waste your last opportunity:

Use {coupon_amount} off Right Now!


1. Go to your wishlist and click on add to cart
2. Use the code {coupon_code} at checkout. it\'s only valid until {expiry_date}.
3. My number: --- (If you have any questions)
4. Website support number: --- (Customer Service)

Have a quick and good purchase,

{site_name}',
							'Unnecessary translation',
							'wc-wlfmc-wishlist'
						);
					} else {
						return _x(
							'<p>opportunities don\'t wait.</p>
<p>We give you time to get your lovely products and tomorrow you can\'t.</p>
<p>In order not to waste your last opportunity:</p>
<br>
<p>Use {coupon_amount}  off Right Now!</p>
<br>

<p>1. Go to <a href="{wishlist_url}">your wishlist</a> and click on add to cart</p>
<p>2. Use the code {coupon_code} at checkout. it\'s only valid until {expiry_date}.</p>
<p>3. My number: --- (If you have any questions)</p>
<p>4. Website support number: --- (Customer Service)</p>
<br>
<p>Have a quick and good purchase,</p>
<p>{site_name}</p>',
							'Unnecessary translation',
							'wc-wlfmc-wishlist'
						);
					}
				default:
					if ( 'plain' === $email_type ) {
						return _x(
							'Hi {user_first_name}
A offer for you!

use this coupon code
{coupon_code}
to get an amazing discount!',
							'Unnecessary translation',
							'wc-wlfmc-wishlist'
						);
					} else {
						return _x(
							'<p>Hi {user_first_name}</p>
<p>A offer for you!</p>
<p>Use this coupon code <b>{coupon_code}</b> to get an amazing discount!</p>',
							'Unnecessary translation',
							'wc-wlfmc-wishlist'
						);
					}
			}

		}


		/**
		 * Set automation id.
		 *
		 * @param int $id automation id.
		 *
		 * @return void
		 */
		public function set_id( $id ) {
			$this->data['id'] = absint( $id );
		}

		/**
		 * Set automation name.
		 *
		 * @param string $name automation name.
		 *
		 * @return void
		 */
		public function set_name( $name ) {
			$this->data['automation_name']            = $name;
			$this->data['options']['automation_name'] = $name;
		}

		/**
		 * Set automation options.
		 *
		 * @param array $options automation options.
		 *
		 * @return void
		 */
		public function set_options( $options ) {
			$this->data['options']                    = $options;
			$this->data['options']['automation_name'] = $this->get_name();
			$this->data['options']['is_active']       = $this->is_active();

		}

		/**
		 * Set automation status.
		 *
		 * @param int $status options.
		 * @return void
		 */
		public function set_status( $status ) {
			$this->data['is_active']            = $status;
			$this->data['options']['is_active'] = $status;
		}

		/**
		 * Set automation paused.
		 *
		 * @return void
		 */
		public function set_paused() {
			$this->data['is_active']            = 0;
			$this->data['options']['is_active'] = 0;
		}

		/**
		 * Set automation active.
		 *
		 * @return void
		 */
		public function set_active() {
			$this->data['is_active']            = 1;
			$this->data['options']['is_active'] = 1;
		}

		/**
		 * Returns single instance of the class
		 *
		 * @access public
		 *
		 * @return WLFMC_Automation
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}


	}
}

