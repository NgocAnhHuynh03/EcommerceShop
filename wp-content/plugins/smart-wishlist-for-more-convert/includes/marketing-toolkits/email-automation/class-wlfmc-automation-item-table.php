<?php
/**
 * Automation list Class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
if ( ! class_exists( 'WLFMC_Automation_Item_Table' ) ) {
	/**
	 * Automation item table
	 */
	class WLFMC_Automation_Item_Table extends WP_List_Table {

		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {

			parent::__construct(
				array(
					'singular' => 'automation-item',     // Singular name of the listed records.
					'plural'   => 'automations-items',    // Plural name of the listed records.
					'ajax'     => false, // should this table support ajax?
				)
			);

		}

		/**
		 * Handles the default column output.
		 *
		 * @param object $item        The current item.
		 * @param string $column_name The current column name.
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'status':
					switch ( $item['status'] ) {
						case 'sending':
							return '<span class="status-sending">' . __( 'Sending', 'wc-wlfmc-wishlist' ) . '</span>';
						case 'not-send':
							return '<span class="status-not-send">' . __( 'Not Send', 'wc-wlfmc-wishlist' ) . '</span>';
						case 'sent':
							return '<span class="status-sent">' . __( 'Sent', 'wc-wlfmc-wishlist' ) . '</span>';
						case 'opened':
							return '<span class="status-opened">' . __( 'Opened', 'wc-wlfmc-wishlist' ) . '</span>';
						case 'clicked':
							return '<span class="status-clicked">' . __( 'Clicked', 'wc-wlfmc-wishlist' ) . '</span>';
						case 'coupon-used':
							return '<span class="status-coupon-used">' . __( 'Coupon used', 'wc-wlfmc-wishlist' ) . '</span>';
						case 'canceled':
							return '<span class="status-canceled">' . __( 'Canceled', 'wc-wlfmc-wishlist' ) . '</span><div class="mct-help-tip-wrap"><span class="mct-help-tip-dec"><p style="color:white">' . __( 'The email will not send because the user has changed the conditions on which the automation is set for his wishlist', 'wc-wlfmc-wishlist' ) . '</p></span></div>';
						case 'unsubscribed':
							return '<span class="status-unsubscribed">' . __( 'Unsubscribed', 'wc-wlfmc-wishlist' ) . '</span>';

					}

					return '';
				case 'net':
					return wc_price( $item[ $column_name ] );
				case 'dateadded':
					return '' !== $item[ $column_name ] && null !== $item[ $column_name ] ? gmdate( 'Y-m-d', strtotime( $item[ $column_name ] ) ) : '-';
				case 'email':
				case 'display_name':
				case 'coupon_code':
				case 'days':
				default:
					return $item[ $column_name ];
			}
		}

		/**
		 * Get columns
		 *
		 * @return array
		 */
		public function get_columns():array {
			return array(
				'cb'           => '<input type="checkbox" />',
				'subject'      => esc_html__( 'Subject', 'wc-wlfmc-wishlist' ),
				'display_name' => esc_html__( 'Name', 'wc-wlfmc-wishlist' ),
				'email'        => esc_html__( 'Email', 'wc-wlfmc-wishlist' ),
				'days'         => esc_html__( 'Send after days', 'wc-wlfmc-wishlist' ),
				'coupon_code'  => esc_html__( 'Coupon', 'wc-wlfmc-wishlist' ),
				'status'       => esc_html__( 'Status', 'wc-wlfmc-wishlist' ),
				'net'          => esc_html__( 'NET', 'wc-wlfmc-wishlist' ),
				'dateadded'    => esc_html__( 'Date', 'wc-wlfmc-wishlist' ),
			);
		}

		/**
		 * Handles the checkbox column output.
		 *
		 * @param object $item The current item.
		 */
		public function column_cb( $item ) {
			$src   = wp_nonce_url(
				add_query_arg(
					array(
						'wlfmc_preview_offer_id' => $item['ID'],
					),
					admin_url( 'admin.php' )
				),
				'wlfmc_preview_email'
			);
			$modal = '
            <div id="modal_preview_email_' . $item['ID'] . '" class="mct-modal  modal_preview_email  modal_preview_email_' . $item['ID'] . '" style="display:none">
                <div class="modal-overlay modal-toggle" data-modal="modal_preview_email_' . $item['ID'] . '"></div>
                <div class="modal-wrapper modal-transition modal-large">
                    <button class="modal-close modal-toggle" data-modal="modal_preview_email_' . $item['ID'] . '"><span class="dashicons dashicons-no-alt"></span></button>
                    <div class="modal-body"><div class="modal-content"><iframe class="html-preview" data-src="' . esc_url( $src ) . '"></iframe></div></div>
                </div>
            </div>';
			return sprintf(
				'<input type="checkbox" name="items[]" value="%s" />',
				$item['ID']
			) . $modal;
		}

		/**
		 * Column subject
		 *
		 * @param object $item The current item.
		 *
		 * @return string
		 */
		public function column_subject( $item ) {
			// create a nonce.
			$delete_nonce  = wp_create_nonce( 'delete-automation-item' );
			$actions       = array(
				'delete'        => sprintf( '<a href="?page=%s&tools=email-automation&tools-action=view&action=delete&automation_id=%s&item=%s&_wpnonce=%s" onclick="return confirm(\'%s\')">' . esc_html__( 'Delete', 'wc-wlfmc-wishlist' ) . '</a>', isset( $_REQUEST['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) : '', isset( $_REQUEST['automation_id'] ) ? absint( $_REQUEST['automation_id'] ) : '', absint( $item['ID'] ), $delete_nonce, esc_html__( 'Are you sure you want to delete?', 'wc-wlfmc-wishlist' ) ), // phpcs:ignore WordPress.Security.NonceVerification
				'preview-email' => sprintf( '<a href="#"  class="modal-toggle modal_preview_email-toggle" data-modal="modal_preview_email_%d" >' . esc_html__( 'Preview', 'wc-wlfmc-wishlist' ) . '</a>', absint( $item['ID'] ) ), // phpcs:ignore WordPress.Security.NonceVerification
			);
			$options       = maybe_unserialize( $item['email_options'] );
			$email_subject = ( $options['mail_subject'] ?? '-' );
			return sprintf( '<a href="#"  class="modal-toggle modal_preview_email-toggle" data-modal="modal_preview_email_%d" >%s</a>', absint( $item['ID'] ), $email_subject ) . $this->row_actions( $actions );
		}

		/**
		 * Get a list of sortable columns.
		 *
		 * @return array
		 */
		public function get_sortable_columns(): array {
			return array(
				'dateadded' => array( 'dateadded', true ),
				'status'    => array( 'status', false ),
				'net'       => array( 'net', false ),
			);
		}

		/**
		 * Prepares the list of items for displaying.
		 */
		public function prepare_items() {

			$this->_column_headers = $this->get_column_info();

			/** Process bulk action */
			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'automation_item_per_page' );
			$current_page = $this->get_pagenum();
			$total_items  = self::record_count();

			$this->items = self::get_items( $per_page, $current_page );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total_items / $per_page ),
				)
			);

		}

		/**
		 * Processes the bulk actions.
		 *
		 * @return void
		 */
		public function process_bulk_action() {

			// Detect when a bulk action is being triggered...
			if ( 'delete' === $this->current_action() && isset( $_REQUEST['automation_id'] ) && isset( $_REQUEST['item'] ) ) {

				// In our file that handles the request, verify the nonce.
				$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

				if ( ! wp_verify_nonce( $nonce, 'delete-automation-item' ) ) {

					die( 'Go get a life script kiddies' );

				} else {

					if ( ! apply_filters( 'wlfmc_can_delete_automation_item', true ) ) {
						wp_safe_redirect(
							add_query_arg(
								array(
									'cant-access'   => 1,
									'page'          => 'mc-email-automations',
									'tools-action'  => 'view',
									'automation_id' => intval( $_REQUEST['automation_id'] ),
								),
								admin_url( 'admin.php' )
							)
						);
						exit;
					}
					self::delete_order( absint( $_REQUEST['automation_id'] ), absint( $_REQUEST['item'] ) );

				}
			}

			// If the delete bulk action is triggered.
			if ( ( ( isset( $_REQUEST['action'] ) && 'bulk-delete' === $_REQUEST['action'] ) || ( isset( $_REQUEST['action2'] ) && 'bulk-delete' === $_REQUEST['action2'] ) ) && isset( $_REQUEST['items'] ) && isset( $_REQUEST['automation_id'] ) ) {

				$delete_ids = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['items'] ) );
				if ( empty( $delete_ids ) ) {
					return;
				}

				if ( ! apply_filters( 'wlfmc_can_delete_automation_item', true ) ) {
					wp_safe_redirect(
						add_query_arg(
							array(
								'cant-access'   => 1,
								'page'          => 'mc-email-automations',
								'tools-action'  => 'view',
								'automation_id' => intval( $_REQUEST['automation_id'] ),
							),
							admin_url( 'admin.php' )
						)
					);
					exit;
				}
				// loop over the array of record IDs and delete them.
				foreach ( $delete_ids as $id ) {
					self::delete_order( absint( $_REQUEST['automation_id'] ), $id );

				}
			}

		}

		/**
		 * Delete a order record.
		 *
		 * @param int $automation_id automation id.
		 * @param int $id item ID.
		 */
		public static function delete_order( int $automation_id, int $id ) {
			global $wpdb;
			$wpdb->delete( // phpcs:ignore WordPress.DB
				$wpdb->wlfmc_wishlist_offers,
				array(
					'ID'            => $id,
					'automation_id' => $automation_id,
				),
				array( '%d', '%d' )
			);
		}

		/**
		 * Record count.
		 *
		 * @return string|null
		 */
		public static function record_count() {
			global $wpdb;
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$automation_id = isset( $_GET['automation_id'] ) ? absint( $_GET['automation_id'] ) : 0;
			$sql           = "SELECT COUNT(items.ID) as count
                    FROM $wpdb->wlfmc_wishlist_offers AS items
                        INNER JOIN $wpdb->wlfmc_wishlist_customers AS customers ON customers.customer_id = items.customer_id
                        LEFT JOIN $wpdb->users AS users ON customers.user_id = users.ID
                        LEFT JOIN $wpdb->usermeta AS m1 ON users.ID = m1.user_id AND m1.meta_key = 'first_name'
                        LEFT JOIN $wpdb->usermeta AS m2 ON users.ID = m2.user_id AND m2.meta_key = 'last_name'
                        LEFT JOIN $wpdb->usermeta AS m3 ON users.ID = m3.user_id AND m3.meta_key = 'billing_phone'
                        LEFT JOIN $wpdb->posts AS posts ON items.coupon_id = posts.ID
                        WHERE ( ( users.user_email IS NOT NULL AND users.user_email  != '' ) OR  customers.email != '' ) AND items.automation_id=$automation_id";

			if ( ! empty( $_REQUEST['status'] ) && in_array(
				sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ),
				array(
					'sending',
					'not-send',
					'sent',
					'opened',
					'clicked',
					'coupon-used',
				),
				true
			) ) {
				$sql .= ' AND  items.status="' . esc_sql( sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) ) . '"';
			}

			if ( ! empty( $_REQUEST['email_key'] ) ) {
				$email_key = absint( wp_unslash( $_REQUEST['email_key'] ) );
				$sql      .= " AND items.email_key = $email_key";
			}

			if ( ! empty( $_REQUEST['s'] ) ) {
				$query_var = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
				$sql      .= ' AND (
                         users.user_login LIKE "%' . esc_sql( $query_var ) . '%"
                        OR users.user_email LIKE "%' . esc_sql( $query_var ) . '%"
                        OR customers.email LIKE "%' . esc_sql( $query_var ) . '%"
                        OR customers.first_name LIKE "%' . esc_sql( $query_var ) . '%"
                        OR customers.last_name LIKE "%' . esc_sql( $query_var ) . '%"
                        OR customers.phone LIKE "%' . esc_sql( $query_var ) . '%"
                        OR m1.meta_value LIKE "%' . esc_sql( $query_var ) . '%"
                        OR m2.meta_value LIKE "%' . esc_sql( $query_var ) . '%"
                        OR m3.meta_value LIKE "%' . esc_sql( $query_var ) . '%"
                        OR posts.post_name LIKE "%' . esc_sql( $query_var ) . '%"
                    )';
			}

			// phpcs:enable WordPress.Security.NonceVerification.Recommended
			return $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB
		}

		/**
		 * Get all items.
		 *
		 * @param int $per_page item per page.
		 * @param int $page_number page number.
		 *
		 * @return array|object|null
		 */
		public static function get_items( int $per_page, int $page_number = 1 ) {

			global $wpdb;
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$automation_id = isset( $_GET['automation_id'] ) ? absint( $_GET['automation_id'] ) : 0;

			$sql = "SELECT items.* , 
                        IFNULL( users.user_email, customers.email) AS email ,
                        CONCAT_WS( ' ', IFNULL( m1.meta_value, customers.first_name),  IFNULL( m2.meta_value, customers.last_name) ) as display_name,
                        IFNULL( m1.meta_value, customers.first_name) AS first_name ,
                        IFNULL( m2.meta_value, customers.last_name) AS last_name, 
                        IFNULL( m3.meta_value, customers.phone) AS phone,
                        posts.post_name  AS coupon_code
                    FROM $wpdb->wlfmc_wishlist_offers AS items
                        INNER JOIN $wpdb->wlfmc_wishlist_customers AS customers ON customers.customer_id = items.customer_id
                        LEFT JOIN $wpdb->users AS users ON customers.user_id = users.ID
                        LEFT JOIN $wpdb->usermeta AS m1 ON users.ID = m1.user_id AND m1.meta_key = 'first_name'
                        LEFT JOIN $wpdb->usermeta AS m2 ON users.ID = m2.user_id AND m2.meta_key = 'last_name'
                        LEFT JOIN $wpdb->usermeta AS m3 ON users.ID = m3.user_id AND m3.meta_key = 'billing_phone'
                        LEFT JOIN $wpdb->posts AS posts ON items.coupon_id = posts.ID
                        WHERE ( ( users.user_email IS NOT NULL AND users.user_email  != '' ) OR  customers.email != '' ) AND items.automation_id=$automation_id";

			if ( ! empty( $_REQUEST['status'] ) && in_array(
				sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ),
				array(
					'sending',
					'not-send',
					'sent',
					'opened',
					'clicked',
					'coupon-used',
				),
				true
			) ) {
				$sql .= ' AND  items.status="' . esc_sql( sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) ) . '"';
			}

			if ( ! empty( $_REQUEST['email_key'] ) ) {
				$email_key = absint( wp_unslash( $_REQUEST['email_key'] ) );
				$sql      .= " AND items.email_key = $email_key";
			}

			if ( ! empty( $_REQUEST['s'] ) ) {
				$query_var = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
				$sql      .= ' AND (
                         users.user_login LIKE "%' . esc_sql( $query_var ) . '%"
                        OR users.user_email LIKE "%' . esc_sql( $query_var ) . '%"
                        OR customers.email LIKE "%' . esc_sql( $query_var ) . '%"
                        OR customers.first_name LIKE "%' . esc_sql( $query_var ) . '%"
                        OR customers.last_name LIKE "%' . esc_sql( $query_var ) . '%"
                        OR customers.phone LIKE "%' . esc_sql( $query_var ) . '%"
                        OR m1.meta_value LIKE "%' . esc_sql( $query_var ) . '%"
                        OR m2.meta_value LIKE "%' . esc_sql( $query_var ) . '%"
                        OR m3.meta_value LIKE "%' . esc_sql( $query_var ) . '%"
                        OR posts.post_name LIKE "%' . esc_sql( $query_var ) . '%"
                    )';
			}

			if ( ! empty( $_REQUEST['orderby'] ) ) {
				$sql .= ' ORDER BY ' . esc_sql( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) );
				$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) : ' ASC';
			}

			// phpcs:enable WordPress.Security.NonceVerification.Recommended
			$sql .= " LIMIT $per_page";

			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

			return $wpdb->get_results( $sql, 'ARRAY_A' ); // phpcs:ignore WordPress.DB
		}

		/**
		 * Displays the search box.
		 *
		 * @param string $text The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 */
		public function search_box( $text, $input_id ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
				return;
			}

			$input_id = $input_id . '-search-input';

			if ( ! empty( $_REQUEST['orderby'] ) ) {
				echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) . '" />';
			}
			if ( ! empty( $_REQUEST['order'] ) ) {
				echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) . '" />';
			}
			?>
			<p class="search-box">
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $text ); ?>:</label>
				<input type="search" placeholder="<?php esc_attr_e( 'Search for coupon, name and email', 'wc-wlfmc-wishlist' ); ?>" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
				<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
			</p>
			<?php
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Display the views.
		 *
		 * @return array
		 */
		public function get_views(): array {
			global $wpdb;
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$automation_id = isset( $_GET['automation_id'] ) ? absint( $_GET['automation_id'] ) : 0;
			$params        = array( $automation_id );
			$sql           = "SELECT  count(*) as all_status,
 				sum(IF(items.status = 'sending', 1, 0)) as sending,
				sum(IF(items.status = 'not-send', 1, 0)) as not_send,
				sum(IF(items.status = 'opened', 1, 0)) as opened,
				sum(IF(items.status = 'clicked', 1, 0)) as clicked,
				sum(IF(items.status = 'sent', 1, 0)) as sent,
				sum(IF(items.status = 'coupon-used', 1, 0)) as coupon_used,
				sum(IF(items.status = 'canceled', 1, 0)) as canceled,
				sum(IF(items.status = 'unsubscribed', 1, 0)) as unsubscribed
				from $wpdb->wlfmc_wishlist_offers  AS items
                INNER JOIN $wpdb->wlfmc_wishlist_customers AS customers ON customers.customer_id = items.customer_id
				LEFT JOIN $wpdb->users AS users ON customers.user_id = users.ID
				WHERE ( ( users.user_email IS NOT NULL AND users.user_email  != '' ) OR  customers.email != '' ) AND items.automation_id=%d ";
			if ( ! empty( $_REQUEST['email_key'] ) ) {
				$sql     .= ' AND items.email_key = %d';
				$params[] = absint( wp_unslash( $_REQUEST['email_key'] ) );
			}

			$count = $wpdb->get_row( $wpdb->prepare( $sql, $params ), ARRAY_A ); // phpcs:ignore WordPress.DB

			$views   = array();
			$current = ( ! empty( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : 'all' );

			// All link.
			if ( $count['all_status'] > 0 ) {
				$class        = ( 'all' === $current ? ' class="current"' : '' );
				$url          = remove_query_arg( 'status' );
				$views['all'] = "<a href='$url' $class >" . esc_html__( 'All', 'wc-wlfmc-wishlist' ) . ' (' . $count['all_status'] . ')</a>';

			}
			if ( $count['sending'] > 0 ) {
				$url              = add_query_arg( 'status', 'sending' );
				$class            = ( 'sending' === $current ? ' class="current"' : '' );
				$views['sending'] = "<a href='$url' $class >" . esc_html__( 'Sending', 'wc-wlfmc-wishlist' ) . ' (' . $count['sending'] . ')</a>';

			}
			if ( $count['not_send'] > 0 ) {
				$url               = add_query_arg( 'status', 'not-send' );
				$class             = ( 'not-send' === $current ? ' class="current"' : '' );
				$views['not_send'] = "<a href='$url' $class >" . esc_html__( 'Not Send', 'wc-wlfmc-wishlist' ) . ' (' . $count['not_send'] . ')</a>';

			}
			if ( $count['clicked'] > 0 ) {
				$url              = add_query_arg( 'status', 'clicked' );
				$class            = ( 'clicked' === $current ? ' class="current"' : '' );
				$views['clicked'] = "<a href='$url' $class >" . esc_html__( 'Clicked', 'wc-wlfmc-wishlist' ) . ' (' . $count['clicked'] . ')</a>';

			}
			if ( $count['sent'] > 0 ) {
				$url           = add_query_arg( 'status', 'sent' );
				$class         = ( 'sent' === $current ? ' class="current"' : '' );
				$views['sent'] = "<a href='$url' $class >" . esc_html__( 'Sent', 'wc-wlfmc-wishlist' ) . ' (' . $count['sent'] . ')</a>';

			}
			if ( $count['coupon_used'] > 0 ) {
				$url                  = add_query_arg( 'status', 'coupon-used' );
				$class                = ( 'coupon-used' === $current ? ' class="current"' : '' );
				$views['coupon_used'] = "<a href='$url' $class >" . esc_html__( 'Coupon used', 'wc-wlfmc-wishlist' ) . ' (' . $count['coupon_used'] . ')</a>';

			}
			if ( $count['opened'] > 0 ) {
				$url             = add_query_arg( 'status', 'opened' );
				$class           = ( 'opened' === $current ? ' class="current"' : '' );
				$views['opened'] = "<a href='$url' $class >" . esc_html__( 'Opened', 'wc-wlfmc-wishlist' ) . ' (' . $count['opened'] . ')</a>';

			}
			if ( $count['canceled'] > 0 ) {
				$url               = add_query_arg( 'status', 'canceled' );
				$class             = ( 'canceled' === $current ? ' class="current"' : '' );
				$views['canceled'] = "<a href='$url' $class >" . esc_html__( 'Canceled', 'wc-wlfmc-wishlist' ) . ' (' . $count['canceled'] . ')</a>';

			}
			if ( $count['unsubscribed'] > 0 ) {
				$url                   = add_query_arg( 'status', 'unsubscribed' );
				$class                 = ( 'unsubscribed' === $current ? ' class="current"' : '' );
				$views['unsubscribed'] = "<a href='$url' $class >" . esc_html__( 'Unsubscribed', 'wc-wlfmc-wishlist' ) . ' (' . $count['unsubscribed'] . ')</a>';

			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
			return $views;
		}

		/**
		 * Returns an associative array containing the bulk action
		 *
		 * @return array
		 */
		public function get_bulk_actions(): array {
			return array(
				'bulk-delete' => __( 'Delete', 'wc-wlfmc-wishlist' ),
			);
		}

		/**
		 * Message
		 * define an array of message and show the content od message if
		 * is find in the query string
		 */
		public function message() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$message = apply_filters(
				'wlfmc_email_automation_item_table_messages',
				array(
					'cant-access' => $this->get_message( '<strong>' . __( 'You Can\'t access to delete automation item.', 'wc-wlfmc-wishlist' ) . '</strong>', 'error', false ),
				)
			);

			foreach ( $message as $key => $value ) {
				if ( isset( $_GET[ $key ] ) ) {
					echo wp_kses_post( $value );
				}
			}
			// phpcs:enable
		}

		/**
		 * Get Message
		 * return html code of message
		 *
		 * @param string $message The message.
		 * @param string $type The type of message (can be 'error' or 'updated').
		 * @param bool   $echo Set to true if you want to print the message.
		 *
		 * @return string
		 */
		public function get_message( $message, $type = 'error', $echo = true ) {
			$message = '<div id="message" class="' . esc_attr( $type ) . ' fade"><p>' . wp_kses_post( $message ) . '</p></div>';
			if ( $echo ) {
				echo wp_kses_post( $message );
			}

			return $message;
		}
	}
}
