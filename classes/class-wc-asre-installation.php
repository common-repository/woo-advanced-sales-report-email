<?php
/**
 * Sales report email
 *
 * Class WC_Install_Sales_Report_Email
 * 
 * @version       1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WC_Install_Sales_Report_Email { 

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	* Function callback for add not existing key in database.
	*
	*/
	public function asre_update_install_callback() {

		global $wpdb;
		$this->table = $wpdb->prefix . 'asre_sales_report';
		$columns = array( 'id', 'email_enable', 'report_name', 'report_status', 'email_recipients', 'email_subject', 'email_content', 'date_created', 'email_interval', 'email_select_week', 'email_select_month', 'day_hour_start', 'day_hour_end', 'email_send_time', 'daterange', 'branding_logo', 'show_header_image', 'display_data' );
		
		foreach ( $columns as $column ) {
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = '%2s'", $this->table, $column ), ARRAY_A );
			if ( ! $row ) {
				$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD %2s TEXT NULL DEFAULT NULL', $this->table, $column ) );
			}
		}
			
		if (version_compare(get_option( 'wc_sales_report_email' ), '1.0', '<') ) {
			
			//database functions
			global $wpdb;
			$this->table = $wpdb->prefix . 'asre_sales_report';
			if ($wpdb->get_var('show tables like "$this->table"') != $this->table) {
			$create_table_query = "
				CREATE TABLE IF NOT EXISTS `{$this->table}` (
					`id` int NOT NULL AUTO_INCREMENT,
					`email_enable` text NULL,
					`report_name` text NULL,
					`date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (id)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
			";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $create_table_query );
			}
			
			$email_enable = get_option('asre_sales_report_email_enable', '1');
			$email_content = get_option('asre_sales_report_email_content', '');
			$email_recipients = get_option('asre_sales_report_email_recipients', get_option('admin_email'));
			$email_subject = get_option('asre_sales_report_email_subject', '');
			$email_send_time = get_option('asre_sales_report_email_send_time', '08:00');
			$email_interval = get_option('asre_sales_report_email_interval', 'weekly');
			$email_select_week = get_option('asre_sales_report_email_select_week', '1');
			$email_select_month = get_option('asre_sales_report_email_select_month', '1');
			$display_total_sales = get_option('asre_sales_report_display_total_sales', '1');
			$display_coupon_used = get_option('asre_sales_report_display_coupon_used', '1');
			$display_total_refunds = get_option('asre_sales_report_display_total_refunds', '1');
			$display_total_tax = get_option('asre_sales_report_display_total_tax', '1');
			$display_total_shipping = get_option('asre_sales_report_display_total_shipping', '1');
			$display_net_revenue = get_option('asre_sales_report_display_net_revenue', '1');
			$display_total_orders = get_option('asre_sales_report_display_total_orders', '1');
			$display_total_items = get_option('asre_sales_report_display_total_items', '1');
			$display_signups = get_option('asre_sales_report_display_signups', '1');
			$display_top_sellers = get_option('asre_sales_report_display_top_sellers', '1');
			$display_top_categories = get_option('asre_sales_report_display_top_categories', '1');
			$display_previous_period = get_option('asre_sales_report_display_previous_period', '');
			$display_average_order_value = get_option('asre_sales_report_display_average_order_value', '');
			$display_average_daily_sales = get_option('asre_sales_report_display_average_daily_sales', '');
			$display_average_daily_items = get_option('asre_sales_report_display_average_daily_items', '');
			$display_sales_by_country = get_option('asre_sales_report_display_sales_by_country', '');
			$display_sales_by_coupons = get_option('asre_sales_report_display_sales_by_coupons', '');
			$display_order_status = get_option('asre_sales_report_display_order_status', '');
			$display_payment_method = get_option('asre_sales_report_display_payment_method', '');
			$display_active_subscriber = get_option('asre_sales_report_display_active_subscriber', '');
			$display_zorem_branding = get_option('asre_sales_report_display_zorem_branding', '');
			$branding_logo = get_option('asre_sales_report_branding_logo', '');
			
			// insert data in database.
			$data = array(
				'report_name' => 'Sales Report',
				'email_content' => $email_content ,
				'email_recipients' => $email_recipients,
				'email_subject' => $email_subject,
				'email_send_time' => $email_send_time ,
				'email_interval' => $email_interval,
				'email_select_week' => $email_select_week,
				'email_select_month' => $email_select_month,
				'display_zorem_branding' => $display_zorem_branding,
				'branding_logo' => $branding_logo,
				'email_enable' =>  $email_enable,
				'display_total_sales' => $display_total_sales,
				'display_coupon_used' => $display_coupon_used,
				'display_total_refunds' => $display_total_refunds,
				'display_total_tax' => $display_total_tax,
				'display_total_shipping' => $display_total_shipping,
				'display_net_revenue' => $display_net_revenue,
				'display_total_orders' => $display_total_orders,
				'display_total_items' => $display_total_items,
				'display_signups' => $display_signups,
				'display_average_order_value' => $display_average_order_value,
				'display_average_daily_sales' => $display_average_daily_sales,
				'display_average_daily_items' => $display_average_daily_items,
				'display_previous_period' => $display_previous_period,
				'display_top_sellers' => $display_top_sellers,
				'display_top_categories' => $display_top_categories,
				'display_sales_by_country' => $display_sales_by_country,
				'display_sales_by_coupons' => $display_sales_by_coupons,
				'display_order_status' => $display_order_status,
				'display_payment_method' => $display_payment_method,
				'display_total_subscriber' => $display_active_subscriber,
				'display_top_sellers_row' => '5',
				'display_top_categories_row' => '5',
			);
			
			$tabledata = $wpdb->get_row( sprintf('SELECT * FROM %s LIMIT 1', $this->table) );
			foreach ( (array) $data as $key1 => $val1  ) {
				if ( 'email_enable' == $key1 ) {
					continue;
				}
				if ( 'report_name' == $key1 ) {
					continue;
				}
				if (!isset($tabledata->$key1)) {
					$wpdb->query( sprintf( 'ALTER TABLE %s ADD $key1 text NOT NULL', $this->table) );
				}
			}
			
			$wpdb->insert( $this->table, $data );
			$id = $wpdb->insert_id;

			wp_clear_scheduled_hook( 'wc_asre_send' );
			wc_sales_report_email()->cron->reset_cron( $id );
			
			update_option( 'wc_sales_report_email', '1.0' );	
		}
		
		if (version_compare(get_option( 'wc_sales_report_email' ), '1.1', '<') ) {

			//database functions
			global $wpdb;
			$this->table = $wpdb->prefix . 'asre_sales_report';
			
			$data = array(
				'display_active_subscriptions' => '0',
				'display_signup_subscriptions' => '0',
				'display_signup_revenue' => '0',
				'display_renewal_subscriptions' => '0',
				'display_renewal_revenue' => '0',
			);
			
			$tabledata = $wpdb->get_row( sprintf('SELECT * FROM %s LIMIT 1', $this->table) );
			foreach ( (array) $data as $key1 => $val1  ) {
				if (!isset($tabledata->$key1)) {
					$wpdb->query( sprintf( 'ALTER TABLE %1s ADD %2s text NOT NULL', $this->table, $key1) );
				}
			}
			update_option('wc_sales_report_email', '1.1');
		}
		
		/*
		* since 2.4.4
		*
		* if error log when column not exist
		*/
		if (version_compare(get_option( 'wc_sales_report_email' ), '1.2', '<') ) {

			//database functions
			global $wpdb;
			$this->table = $wpdb->prefix . 'asre_sales_report';
			
			$data = array(
				'display_switch_subscriptions' => '0',
				'display_switch_revenue' => '0',
				'display_resubscribe_subscriptions' => '0',
				'display_resubscribe_revenue' => '0',
			);
			
			$tabledata = $wpdb->get_row( sprintf('SELECT * FROM %s LIMIT 1', $this->table) );
			foreach ( (array) $data as $key1 => $val1  ) {
				if (!isset($tabledata->$key1)) {
					$wpdb->query( sprintf( 'ALTER TABLE %1s ADD %2s text NOT NULL', $this->table, $key1) );
				}
			}
			update_option('wc_sales_report_email', '1.2');
		}
		
		if (version_compare(get_option( 'wc_sales_report_email' ), '1.3', '<') ) {

			//database functions
			global $wpdb;
			$this->table = $wpdb->prefix . 'asre_sales_report';
			
			$display_sales_by_country = get_option('asre_sales_report_display_sales_by_country', '');
			
			$data = array(
				'display_sales_by_billing_city' => '0',
				'display_sales_by_shipping_city' => '0',
				'display_sales_by_billing_state' => '0',
				'display_sales_by_shipping_state' => '0',
				'display_sales_by_billing_country' => '0',
				'display_sales_by_shipping_country' => '0',
				'display_sales_by_billing_city_row' => '5',
				'display_sales_by_billing_state_row' => '5',
				'display_sales_by_billing_country_row' => '5',
				'display_sales_by_shipping_city_row' => '5',
				'display_sales_by_shipping_state_row' => '5',
				'display_sales_by_shipping_country_row' => '5',
			);
			
			$tabledata = $wpdb->get_row( sprintf('SELECT * FROM %s LIMIT 1', $this->table) );
			foreach ( (array) $data as $key1 => $val1  ) {
				if (!isset($tabledata->$key1)) {
					$wpdb->query( sprintf( 'ALTER TABLE %1s ADD %2s text NOT NULL', $this->table, $key1) );
				}
			}
			update_option('wc_sales_report_email', '1.3');
		}
		
		if (version_compare(get_option( 'wc_sales_report_email' ), '1.4', '<') ) {

			//database functions
			global $wpdb;
			$this->table = $wpdb->prefix . 'asre_sales_report';
			
			$data = array(
				'display_cancellation_subscriptions' => '0',
				'display_cancellation_revenue' => '0',
			);
			
			$tabledata = $wpdb->get_row( sprintf('SELECT * FROM %s LIMIT 1', $this->table) );
			foreach ( (array) $data as $key1 => $val1  ) {
				if (!isset($tabledata->$key1)) {
					$wpdb->query( sprintf( 'ALTER TABLE %1s ADD %2s text NOT NULL', $this->table, $key1) );
				}
			}
			update_option('wc_sales_report_email', '1.4');
		}
		
		if (version_compare(get_option( 'wc_sales_report_email' ), '1.5', '<') ) {

			//database functions
			global $wpdb;
			$this->table = $wpdb->prefix . 'asre_sales_report';
			
			$data = array(
				'day_hour_start' => '00:00',
				'day_hour_end' => '00:00',
			);
			
			$tabledata = $wpdb->get_row( sprintf('SELECT * FROM %s LIMIT 1', $this->table) );
			foreach ( (array) $data as $key1 => $val1  ) {
				if (!isset($tabledata->$key1)) {
					$wpdb->query( sprintf( 'ALTER TABLE %1s ADD %2s text NOT NULL', $this->table, $key1) );
				}
			}
			update_option('wc_sales_report_email', '1.5');
		}
		
		if (version_compare(get_option( 'wc_sales_report_email' ), '1.6', '<') ) {

			//database functions
			global $wpdb;
			$this->table = $wpdb->prefix . 'asre_sales_report';
			
			$data = array(
				'display_net_subscription_gain' => '0',
			);
			
			$tabledata = $wpdb->get_row( sprintf('SELECT * FROM %s LIMIT 1', $this->table) );
			foreach ( (array) $data as $key1 => $val1  ) {
				if (!isset($tabledata->$key1)) {
					$wpdb->query( sprintf( 'ALTER TABLE %1s ADD %2s text NOT NULL', $this->table, $key1) );
				}
			}
			update_option('wc_sales_report_email', '1.6');
		}

		if (version_compare(get_option( 'wc_sales_report_email' ), '1.7', '<') ) {

			//database functions
			global $wpdb;
			$this->table = $wpdb->prefix . 'asre_sales_report';

			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s DROP COLUMN %2s', $this->table, 'display_data' ) );

			//ADD columns 
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'display_data' ", $this->table ), ARRAY_A );
			if ( ! $row ) {
				$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD display_data TEXT NULL DEFAULT NULL AFTER email_send_time', $this->table ) );

				//Migration columns
				$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s ORDER BY id DESC', $this->table ) );

				if ( empty($results) ) {
					return;
				}
				
				foreach ( $results as $report  ) {

					$data = (array) wc_sales_report_email()->admin->get_data_byid( $report->id );

					$display_data = (object) array(
						'display_gross_sales' => isset($report->display_gross_sales) ? $report->display_gross_sales : '',
						'display_total_sales' => isset($report->display_total_sales) ? $report->display_total_sales : '',
						'display_coupon_used' => isset($report->display_coupon_used) ? $report->display_coupon_used : '',
						'display_total_refunds' => isset($report->display_total_refunds) ? $report->display_total_refunds : '',
						'display_total_tax' => isset($report->display_total_tax) ? $report->display_total_tax : '',
						'display_total_shipping' => isset($report->display_total_shipping) ? $report->display_total_shipping : '',
						'display_net_revenue' => isset($report->display_net_revenue) ? $report->display_net_revenue : '',
						'display_total_orders' => isset($report->display_total_orders) ? $report->display_total_orders : '',
						'display_total_items' => isset($report->display_total_items) ? $report->display_total_items : '',
						'display_signups' => isset($report->display_signups) ? $report->display_signups : '',
						'display_top_sellers' => isset($report->display_top_sellers) ? $report->display_top_sellers : '',
						'display_top_categories' => isset($report->display_top_categories) ? $report->display_top_categories : '',
					);
					
					$data['display_data'] =  serialize( $display_data );
					$wpdb->update( $this->table, $data, array('id' => wc_clean($report->id)) );

				}

				//DROP old columns
				$tabledata = $wpdb->get_row( $wpdb->prepare('SELECT display_data FROM %1s LIMIT 1', $this->table) );

				$old_columns = unserialize($tabledata->display_data);
				if ( !empty($old_columns) ) {
					foreach ( $old_columns as $column_name => $value ) {
						$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = '%2s' ", $this->table, $column_name ), ARRAY_A );
						if ( $row ) {
							//Drop columns
							$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s DROP COLUMN %2s', $this->table, $column_name ) );
						}
					}
				}

			}

			update_option('wc_sales_report_email', '1.7');
		}
	
	}

	/**
	* Insert database table and columns
	*
	*/
	public function asre_insert_table_columns() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'asre_sales_report';

		if ( !$wpdb->query( $wpdb->prepare( 'show tables like %s', $this->table ) ) ) {			
			$this->create_advanced_sales_report_table();	
		}
	}

	/*
	* function for create salse report email table
	*/
	public function create_advanced_sales_report_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();	
		$this->table = $wpdb->prefix . 'asre_sales_report';		
		$sql = "CREATE TABLE $this->table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			email_enable varchar(500) DEFAULT '' NOT NULL,
			report_name TEXT NULL DEFAULT NULL,
			report_status TEXT NULL DEFAULT NULL,
			email_recipients TEXT NULL DEFAULT NULL,
			email_subject TEXT NULL DEFAULT NULL,
			email_content TEXT NULL DEFAULT NULL,
			date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			email_interval TEXT NULL DEFAULT NULL,
			email_select_week TEXT NULL DEFAULT NULL,
			email_select_month TEXT NULL DEFAULT NULL,
			day_hour_start TEXT NULL DEFAULT NULL,
			day_hour_end TEXT NULL DEFAULT NULL,
			email_send_time TEXT NULL DEFAULT NULL,
			daterange TEXT NULL DEFAULT NULL,
			branding_logo TEXT NULL DEFAULT NULL,
			display_data TEXT NULL DEFAULT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";			
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option('wc_sales_report_email', '1.7');
	}
	
	/**
	 * Get the class instance
	 *
	 * @return
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
