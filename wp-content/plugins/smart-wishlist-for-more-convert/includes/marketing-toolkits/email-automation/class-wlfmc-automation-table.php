<?php
/**
 * Automation list Class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
if ( ! class_exists( 'WLFMC_Automation_Table' ) ) {
	/**
	 * Automation table
	 */
	class WLFMC_Automation_Table extends WP_List_Table {

		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {

			parent::__construct(
				array(
					'singular' => 'automation',     // Singular name of the listed records.
					'plural'   => 'automations',    // Plural name of the listed records.
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
					$status = $item['is_active'] ? __( 'Active', 'wc-wlfmc-wishlist' ) : __( 'Paused', 'wc-wlfmc-wishlist' );
					$class  = $item['is_active'] ? 'active' : 'pause';

					return '<span class="status-' . $class . '">' . $status . '</span>';
				case 'net_total':
					return wc_price( $item[ $column_name ] );
				case 'click_rate':
				case 'open_rate':
					return floatval( $item[ $column_name ] ) . '%';
				case 'created_at':
					return '' !== $item[ $column_name ] && null !== $item[ $column_name ] ? gmdate( 'Y-m-d', strtotime( $item[ $column_name ] ) ) : '-';
				case 'sent':
				case 'recipients':
				case 'automation_name':
				default:
					return $item[ $column_name ];
			}
		}

		/**
		 * Get columns
		 *
		 * @return array
		 */
		public function get_columns(): array {
			return array(
				'created_at'      => __( 'Date', 'wc-wlfmc-wishlist' ),
				'automation_name' => __( 'Name', 'wc-wlfmc-wishlist' ),
				'recipients'      => __( 'Recipients', 'wc-wlfmc-wishlist' ),
				'open_rate'       => __( 'Open Rate', 'wc-wlfmc-wishlist' ),
				'click_rate'      => __( 'Click Rate', 'wc-wlfmc-wishlist' ),
				'status'          => __( 'Status', 'wc-wlfmc-wishlist' ),
				'net_total'       => __( 'NET', 'wc-wlfmc-wishlist' ),
				'action'          => __( 'Action', 'wc-wlfmc-wishlist' ),

			);
		}

		/**
		 * Handles the checkbox column output.
		 *
		 * @param object $item The current item.
		 */
		public function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="items[]" value="%s" />',
				$item['ID']
			);
		}

		/**
		 * Generate row actions div.
		 *
		 * @param object $item The current item.
		 */
		public function column_action( $item ): string {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( empty( $_REQUEST['page'] ) ) {
				return '';
			}

			return '<div class="margin-bet">' .
				sprintf( '<a href="?page=%s&tools=email-automation&tools-action=edit&automation_id=%s" class="center-align btn-secondary ico-btn gear-btn min-width-btn small-btn" >' . __( 'Manage', 'wc-wlfmc-wishlist' ) . '</a>', esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ), absint( $item['ID'] ) ) .
				sprintf( '<a href="?page=%s&tools=email-automation&tools-action=view&automation_id=%s" class="center-align btn-secondary ico-btn report-btn min-width-btn small-btn" >' . __( 'Reports', 'wc-wlfmc-wishlist' ) . '</a>', esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ), absint( $item['ID'] ) ) .
				'</div>';
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Column recipients
		 *
		 * @param object $item The current item.
		 *
		 * @return string
		 */
		public function column_recipients( $item ): string {
			// create a nonce.
			$delete_nonce = wp_create_nonce( 'delete-recipients' );

			$actions = array(
				'delete-recipients' => sprintf( '<a href="?page=%s&tools=email-automation&action=delete-recipients&item=%s&_wpnonce=%s" onclick="return confirm(\'%s\')">' . __( 'Delete scheduled emails', 'wc-wlfmc-wishlist' ) . '</a>', isset( $_REQUEST['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) : '', absint( $item['ID'] ), $delete_nonce, __( 'Are you sure you want to delete scheduled emails?', 'wc-wlfmc-wishlist' ) ), // phpcs:ignore WordPress.Security.NonceVerification
			);

			return $item['recipients'] . $this->row_actions( $actions );
		}

		/**
		 * Column created_at
		 *
		 * @param object $item The current item.
		 *
		 * @return string
		 */
		public function column_created_at( $item ): string {
			// create a nonce.
			$delete_nonce = wp_create_nonce( 'delete-automation' );

			$actions = array(
				'delete' => sprintf( '<a href="?page=%s&tools=email-automation&action=delete&item=%s&_wpnonce=%s" onclick="return confirm(\'%s\')">' . __( 'Delete', 'wc-wlfmc-wishlist' ) . '</a>', isset( $_REQUEST['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) : '', absint( $item['ID'] ), $delete_nonce, __( 'Are you sure you want to delete?', 'wc-wlfmc-wishlist' ) ), // phpcs:ignore WordPress.Security.NonceVerification
			);

			return gmdate( 'Y-m-d', strtotime( $item['created_at'] ) ) . $this->row_actions( $actions );
		}

		/**
		 * Get a list of sortable columns.
		 *
		 * @return array
		 */
		public function get_sortable_columns(): array {
			return array(
				'created_at'      => array( 'created_at', true ),
				'recipients'      => array( 'recipients', false ),
				'automation_name' => array( 'automation_name', false ),
				'open_rate'       => array( 'open_rate', false ),
				'click_rate'      => array( 'click_rate', false ),
				'net_total'       => array( 'net_total', false ),
			);
		}

		/**
		 * Prepares the list of items for displaying.
		 */
		public function prepare_items() {

			$this->_column_headers = $this->get_column_info();

			/** Process bulk action */
			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'automation_per_page' );
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
			if ( 'delete' === $this->current_action() ) {

				// In our file that handles the request, verify the nonce.
				$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

				if ( ! wp_verify_nonce( $nonce, 'delete-automation' ) ) {

					die( 'Go get a life script kiddies' );

				} elseif ( isset( $_REQUEST['item'] ) ) {
					self::delete_automation( absint( $_REQUEST['item'] ) );

				}
			}

			if ( 'delete-recipients' === $this->current_action() ) {

				// In our file that handles the request, verify the nonce.
				$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

				if ( ! wp_verify_nonce( $nonce, 'delete-recipients' ) ) {

					die( 'Go get a life script kiddies' );

				} elseif ( isset( $_REQUEST['item'] ) ) {
					self::delete_recipients( absint( $_REQUEST['item'] ) );

				}
			}

		}

		/**
		 * Delete a automation record.
		 *
		 * @param int $id automation ID.
		 */
		public static function delete_automation( $id ) {

			$automation = new WLFMC_Automation( $id );

			if ( ! apply_filters( 'wlfmc_can_delete_automation', true ) ) {
				wp_safe_redirect(
					add_query_arg(
						array(
							'cant-access' => 1,
							'page'        => 'mc-email-automations',
						),
						admin_url( 'admin.php' )
					)
				);
				exit;
			}

			$deleted = $automation->delete();
			if ( $deleted ) {
				wp_safe_redirect(
					add_query_arg(
						array(
							'deleted' => 1,
							'page'    => 'mc-email-automations',
						),
						admin_url( 'admin.php' )
					)
				);
			} else {
				wp_safe_redirect(
					add_query_arg(
						array(
							'cant-delete' => 1,
							'page'        => 'mc-email-automations',
						),
						admin_url( 'admin.php' )
					)
				);
			}

			exit;
		}

		/**
		 * Delete a recipient
		 *
		 * @param int $id recipient id.
		 *
		 * @return void
		 */
		public function delete_recipients( $id ) {
			$automation = new WLFMC_Automation( $id );

			if ( ! apply_filters( 'wlfmc_can_delete_recipients', true ) ) {
				wp_safe_redirect(
					add_query_arg(
						array(
							'cant-access' => 1,
							'page'        => 'mc-email-automations',
						),
						admin_url( 'admin.php' )
					)
				);
				exit;
			}

			$deleted = $automation->delete_email_queue();

			if ( $deleted ) {
				wp_safe_redirect(
					add_query_arg(
						array(
							'recipients-deleted' => 1,
							'page'               => 'mc-email-automations',
						),
						admin_url( 'admin.php' )
					)
				);
			} else {
				wp_safe_redirect(
					add_query_arg(
						array(
							'cant-delete-recipients' => 1,
							'page'                   => 'mc-email-automations',
						),
						admin_url( 'admin.php' )
					)
				);
			}

			exit;
		}


		/**
		 * Record count.
		 *
		 * @return string|null
		 */
		public static function record_count() {
			global $wpdb;

			$sql = "SELECT COUNT(ID) as count FROM $wpdb->wlfmc_wishlist_automations ";
			if ( isset( $_REQUEST['status'] ) && in_array( // phpcs:ignore WordPress.Security.NonceVerification
				$_REQUEST['status'], // phpcs:ignore WordPress.Security.NonceVerification
				array(
					'1',
					'0',
				),
				true
			) ) {
				$sql .= ' WHERE is_pro = 0 AND is_active="' . esc_sql( sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) ) . '"'; // phpcs:ignore WordPress.Security.NonceVerification
			} else {
				$sql .= ' WHERE is_pro = 0';
			}

			return $wpdb->get_var( $sql );// phpcs:ignore WordPress.DB
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
			$sql = "SELECT automation.* ,
			sum(IF(items.status IN ('sent', 'opened','clicked' ,'coupon-used'), 1, 0)) sent,
            sum(IF(items.status IN ('sending' , 'not-send'), 1, 0)) send_queue,
            (sum(IF(items.status IN ('opened','clicked' ,'coupon-used'), 1, 0)) / sum(IF(items.status IN ('sent', 'opened','clicked' ,'coupon-used'), 1, 0)) * 100 ) AS open_rate,
            (sum(IF(items.status IN ('clicked' ,'coupon-used'), 1, 0)) / sum(IF(items.status IN ('sent', 'opened','clicked' ,'coupon-used'), 1, 0)) * 100 ) AS click_rate,
            sum(IF(items.net > 0, items.net, 0)) net_total,
            COUNT(DISTINCT(items.customer_id)) as recipients
 			FROM $wpdb->wlfmc_wishlist_automations as automation
 			LEFT JOIN $wpdb->wlfmc_wishlist_offers as items ON automation.ID = items.automation_id WHERE automation.is_pro = 0 ";

			// phpcs:disable WordPress.Security.NonceVerification
			if ( isset( $_REQUEST['status'] ) && in_array(
				sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ),
				array(
					'1',
					'0',
				),
				true
			) ) {
				$sql .= ' AND  automation.is_active="' . esc_sql( sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) ) . '"';
			}
			if ( ! empty( $_REQUEST['s'] ) ) {
				$sql .= ' AND  automation.automation_name LIKE "%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%"';
			}
			$sql .= ' GROUP BY automation.ID';

			if ( ! empty( $_REQUEST['orderby'] ) ) {
				$sql .= ' ORDER BY ' . esc_sql( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) );
				$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) : ' ASC';
			}
			// phpcs:enable WordPress.Security.NonceVerification
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
			if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {// phpcs:ignore WordPress.Security.NonceVerification
				return;
			}

			$input_id = $input_id . '-search-input';

			if ( ! empty( $_REQUEST['orderby'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification
				echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) . '" />';// phpcs:ignore WordPress.Security.NonceVerification
			}
			if ( ! empty( $_REQUEST['order'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification
				echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) . '" />';// phpcs:ignore WordPress.Security.NonceVerification
			}
			?>
			<p class="search-box">
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $text ); ?>:</label>
				<input type="search" placeholder="<?php esc_attr_e( 'Search by name', 'wc-wlfmc-wishlist' ); ?>" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
				<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
			</p>
			<?php
		}

		/**
		 * Display the views.
		 *
		 * @return array
		 */
		public function get_views(): array {
			global $wpdb;
			$count   = $wpdb->get_row( //phpcs:ignore WordPress.DB
				"SELECT  count(*) all_status,
 				sum(IF(is_active = '1', 1, 0)) as is_active,
				sum(IF(is_active = '0', 1, 0)) as paused

			    FROM $wpdb->wlfmc_wishlist_automations WHERE is_pro = 0",
				ARRAY_A
			);
			$views   = array();
			$current = ( isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : 'all' );// phpcs:ignore WordPress.Security.NonceVerification

			$removable_query_args = wp_removable_query_args();
			$current_url          = remove_query_arg( $removable_query_args );

			if ( $count['all_status'] > 0 ) {
				$class        = ( 'all' === $current ? ' class="current"' : '' );
				$url          = esc_url( remove_query_arg( 'status', $current_url ) );
				$views['all'] = "<a href='$url' $class >" . __( 'All', 'wc-wlfmc-wishlist' ) . ' (' . $count['all_status'] . ')</a>';

			}
			if ( $count['is_active'] > 0 ) {
				$url                = esc_url( add_query_arg( 'status', '1', $current_url ) );
				$label              = __( 'Active', 'wc-wlfmc-wishlist' );
				$class              = ( '1' === $current ? ' class="current"' : '' );
				$views['is_active'] = "<a href='$url' $class >" . $label . ' (' . $count['is_active'] . ')</a>';

			}
			if ( $count['paused'] > 0 ) {
				$label           = __( 'Paused', 'wc-wlfmc-wishlist' );
				$url             = esc_url( add_query_arg( 'status', '0', $current_url ) );
				$class           = ( '0' === $current ? ' class="current"' : '' );
				$views['paused'] = "<a href='$url' $class >" . $label . ' (' . $count['paused'] . ')</a>';

			}

			return $views;
		}

		/**
		 * Message
		 * define an array of message and show the content od message if
		 * is find in the query string
		 */
		public function message() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$message = apply_filters(
				'wlfmc_automation_table_messages',
				array(
					'deleted'                => $this->get_message( '<strong>' . __( 'Automation Removed.', 'wc-wlfmc-wishlist' ) . '</strong>', 'updated', false ),
					'cant-delete'            => $this->get_message( '<strong>' . __( 'You can\'t remove a sending automation.', 'wc-wlfmc-wishlist' ) . '</strong>', 'error', false ),
					'recipients-deleted'     => $this->get_message( '<strong>' . __( 'All scheduled emails Removed.', 'wc-wlfmc-wishlist' ) . '</strong>', 'updated', false ),
					'cant-delete-recipients' => $this->get_message( '<strong>' . __( 'Can\'t find any scheduled emails.', 'wc-wlfmc-wishlist' ) . '</strong>', 'error', false ),
					'cant-access'            => $this->get_message( '<strong>' . __( 'You Can\'t access to Delete automation and recipients.', 'wc-wlfmc-wishlist' ) . '</strong>', 'error', false ),
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

