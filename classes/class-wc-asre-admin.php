<?php
/**
 * Sales report email
 *
 * Class WC_ASRE_Admin
 * 
 * @version       1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WC_ASRE_Admin { 

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	
	/**
	 * Get the class instance
	 *
	 * @return WC_ASRE_Admin
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	
	/**
	 * Initialize the main plugin function
	 * 
	 * @since  1.0
	*/
	public function __construct() {
		$this->init();
	}
	
	/*
	 * init function
	 *
	 * @since  1.0
	*/
	public function init() {

		//adding hooks
		global $wpdb;
		$this->table = $wpdb->prefix . 'asre_sales_report';
		$this->screen_id = 'woocommerce-advanced-sales-report-email';
		
		//callback for admin menu register		
		add_action('admin_menu', array( $this, 'register_woocommerce_menu' ), 99 );
		
		// Handle the enable/disable/delete actions.
		add_action( 'admin_init', array( $this, 'data_toggle_callback' ) );
		
		// enable toggle in report list hook
		add_action( 'wp_ajax_enable_toggle_data_update', array( $this, 'update_enable_toggle_callback' ) );
		
		// Hook for add admin body class in settings page
		add_filter( 'admin_body_class', array( $this, 'asre_post_admin_body_class' ), 100, 1 );
		
		// Cron hook
		add_action( 'wc_asre_send', array( $this, 'cron_email_callback' ) );
		
		//ajax call to send test mail 
		//add_action( 'wp_ajax_send_test_sales_email', array( $this, 'send_test_sales_email_func' ) );
		
		// cron run date update in report setting hook
		add_action( 'wp_ajax_cron_run_date_update', array( $this, 'update_cron_run_date_callback' ) );
	}
	
	/*
	* plugin file directory function
	*/	
	public function plugin_dir_url() {
		return plugin_dir_url( __FILE__ );
	}
	
	/*
	* add unique body class
	*/
	public function asre_post_admin_body_class( $body_class ) {
		
		if (!isset($_GET['page'])) {
			return $body_class;
		}
		if ( 'woocommerce-advanced-sales-report-email' == $_GET['page'] ) {
			$body_class .= ' asre-sales-report-email-setting ';
		}

		return $body_class;
	}
	
	/*
	* Admin Menu add function
	* WC sub menu
	*/
	public function register_woocommerce_menu() {
		add_submenu_page( 'woocommerce', 'Sales Report Email', 'Sales Report Email', 'manage_options', 'woocommerce-advanced-sales-report-email', array( $this, 'woocommerce_sales_report_page_callback' ) ); 
	}
	
	
	/*
	* callback for Sales Report Email page
	*/
	public function woocommerce_sales_report_page_callback() {	
		
		global $wpdb;

		// Check the user capabilities
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html( 'You do not have sufficient permissions to access this page.', 'woocommerce-cart-notices' ) );
		}

		$tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : 'list';

		if ( 'list' === $tab ) {
			
			$data = $this->get_data();

		} elseif ( 'edit' === $tab ) {

			$id = isset( $_GET['id'] ) ? sanitize_text_field($_GET['id']) : '';
			$data = $this->get_data_byid( $id );
			
			if ( !is_object( $data ) ) {
			  $data = new stdClass();
			  $data->data = array(); 
			}         
			
			
			$this->data = $data;

			if ( ! $data && '0' != $id ) {
				wp_die( 'The requested data could not be found!', 'woocommerce-cart-notices' );
			}
			if ( '0' == $id ) {
				$this->data->id = '0';	
			}

		}
		?>
			<div class="zorem-layout__header">
				<h1 class="page_heading">
					<?php
					if ( 'edit' === $tab ) {
						$tab_heading = esc_html( 'Report Emails > Edit Report', 'woocommerce-advanced-sales-report-email');
					} else if ( 'add-ons' === $tab ) {
						$tab_heading = esc_html( 'Upgrade to Pro', 'woocommerce-advanced-sales-report-email');
					} else {
						$tab_heading = esc_html( 'Report Emails', 'woocommerce-advanced-sales-report-email');
					}
					?>
					<a href="javascript:void(0)')"><?php esc_html_e( 'Sales Report Email', 'woocommerce-advanced-sales-report-email'); ?></a> > <?php esc_html_e( $tab_heading, 'woocommerce-advanced-sales-report-email'); ?>
				</h1>
				<img class="zorem-layout__header-logo" src="<?php echo wc_sales_report_email()->plugin_dir_url(__FILE__) . 'assets/images/sre-icon.png';?>">
			</div>
			<?php do_action( 'sre_settings_admin_notice' ); ?>
			<div class="woocommerce asre_admin_layout">
			<div class="woocommerce-layout__activity-panel">
					<div class="woocommerce-layout__activity-panel-tabs">
						<button type="button" id="activity-panel-tab-help" class="components-button woocommerce-layout__activity-panel-tab">
							<span class="dashicons dashicons-menu-alt"></span> 
						</button>
					</div>
					<div class="woocommerce-layout__activity-panel-wrapper">
						<div class="woocommerce-layout__activity-panel-content" id="activity-panel-true">
							<div class="woocommerce-layout__activity-panel-header">
								<div class="woocommerce-layout__inbox-title">
									<p class="css-activity-panel-Text">Documentation</p>            
								</div>								
							</div>
							<div>
								<ul class="woocommerce-list woocommerce-quick-links__list">
									<li class="woocommerce-list__item has-action">
										<?php
										$support_link = 'https://wordpress.org/support/plugin/woo-advanced-sales-report-email/#new-topic-0' ;
										?>
										<a href="<?php echo esc_url( $support_link ); ?>" class="woocommerce-list__item-inner" target="_blank" >
											<div class="woocommerce-list__item-before">
												<img src="<?php echo wc_sales_report_email()->plugin_dir_url(__FILE__) . 'assets/images/get-support-icon.svg';?>">	
											</div>
											<div class="woocommerce-list__item-text">
												<span class="woocommerce-list__item-title">
													<div class="woocommerce-list-Text">Get Support</div>
												</span>
											</div>
											<div class="woocommerce-list__item-after">
												<span class="dashicons dashicons-arrow-right-alt2"></span>
											</div>
										</a>
									</li>            
									<li class="woocommerce-list__item has-action">
										<a href="https://www.zorem.com/docs/sales-report-email-for-woocommerce/?utm_source=wp-admin&utm_medium=SREDOCU&utm_campaign=add-ons" class="woocommerce-list__item-inner" target="_blank">
											<div class="woocommerce-list__item-before">
												<img src="<?php echo wc_sales_report_email()->plugin_dir_url(__FILE__) . 'assets/images/documentation-icon.svg';?>">
											</div>
											<div class="woocommerce-list__item-text">
												<span class="woocommerce-list__item-title">
													<div class="woocommerce-list-Text">Documentation</div>
												</span>
											</div>
											<div class="woocommerce-list__item-after">
												<span class="dashicons dashicons-arrow-right-alt2"></span>
											</div>
										</a>
									</li>
									<li class="woocommerce-list__item has-action">
										<a href="https://www.zorem.com/product/sales-report-email-for-woocommerce/?utm_source=wp-admin&utm_medium=SRE&utm_campaign=add-ons" class="woocommerce-list__item-inner" target="_blank">
											<div class="woocommerce-list__item-before">
												<img src="<?php echo wc_sales_report_email()->plugin_dir_url(__FILE__) . 'assets/images/upgrade.svg';?>">
											</div>
											<div class="woocommerce-list__item-text">
												<span class="woocommerce-list__item-title">
													<div class="woocommerce-list-Text">Upgrade To Pro</div>
												</span>
											</div>
											<div class="woocommerce-list__item-after">
												<span class="dashicons dashicons-arrow-right-alt2"></span>
											</div>
										</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="asre_admin_content">
					<input id="asre_tab1" type="radio" name="tabs" class="asre_tab_input" data-tab="list" checked>
					<a for="asre_tab1" href="admin.php?page=<?php echo esc_html($this->screen_id); ?>&amp;tab=list" <?php echo 'edit' == $tab ? 'style="display:none;"' : ''; ?> class="asre_tab_label first_label <?php echo ( 'list' === $tab ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Report Emails', 'woocommerce'); ?></a>
					<input id="asre_tab4" type="radio" name="tabs" class="asre_tab_input" data-tab="add-ons" 
					<?php 
					if ( isset($_GET['tab']) && ( 'add-ons' == $_GET['tab'] ) ) { 
						echo 'checked'; 
					} 
					?> 
					>
					<a for="asre_tab4" href="admin.php?page=<?php echo esc_html($this->screen_id); ?>&amp;tab=add-ons" <?php echo 'edit' == $tab ? 'style="display:none;"' : ''; ?> class="asre_tab_label <?php echo ( 'list' === $tab ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Upgrade to Pro', 'sales-report-email-pro-addon'); ?></a>
					<div class="menu_devider"></div>
					<?php
					if (  'list' == $tab || 'edit' == $tab ) { 
						require_once( 'views/asre_reports_tab.php' ); 
					}
					?>
					<?php
					if (  'add-ons' == $tab ) { 
						require_once( 'views/asre_addons_tab.php' );
					}
					?>
				</div>
			</div>
	<?php		
	}
	
	
	/*
	* get all data 
	*/
	public function get_data() {
		global $wpdb;

		// Avoid database table not found errors when plugin is first installed
		// by checking if the plugin option exists
		if ( empty( $this->data ) ) {
			$this->data = array();

			$wpdb->hide_errors();
			
			$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s ORDER BY id DESC', $this->table ) ); //ORDER BY name ASC
			
			if ( ! empty( $results ) ) {
				
				foreach ( $results as $key => $result ) {
					$results[ $key ]->email_enable = maybe_unserialize( $results[ $key ]->email_enable );
					$results[ $key ]->report_name = maybe_unserialize( $results[ $key ]->report_name );
					$results[ $key ]->email_interval = maybe_unserialize( $results[ $key ]->email_interval );
					$results[ $key ]->email_recipients = maybe_unserialize( $results[ $key ]->email_recipients );
				}

				$this->data = $results;
			}
		}
		return $this->data;
	}
	
	
	/*
	* get data by id
	*/
	public function get_data_byid( $id ) {
		global $wpdb;
		$results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %1s WHERE id = %d', $this->table, $id ) );
		if ( ! empty( $results ) ) {
			$results->email_enable = maybe_unserialize( $results->email_enable );
			$results->report_name = maybe_unserialize( $results->report_name );
			$results->email_interval = maybe_unserialize( $results->email_interval );
			$results->email_recipients = maybe_unserialize( $results->email_recipients );
		}
		return $results;
	}
	
	/*
	* get next cron run date in report list column
	*/	
	public function next_run_date( $data ) {

		$hrtime = $data->email_send_time;

		$week = array(
			'0' => esc_html( 'Sunday', 'woocommerce' ),
			'1' => esc_html( 'Monday', 'woocommerce' ),
			'2' => esc_html( 'Tuesday', 'woocommerce' ),
			'3' => esc_html( 'Wednesday', 'woocommerce' ),
			'4' => esc_html( 'Thursday', 'woocommerce' ),
			'5' => esc_html( 'Friday', 'woocommerce' ),
			'6' => esc_html( 'Saturday', 'woocommerce' ),
		);
		$run = $data->email_interval;
		
		if ( 'one-time' == $run ) {
			return esc_html( 'Manual', 'advanced-local-pickup-pro');
		}
		
		if ( 'monthly' == $run || 'last-30-days' == $run ) {
			$select_day_for_month = isset($data->email_select_month) ? $data->email_select_month : '1';
			if ( gmdate('j') == $select_day_for_month && current_time( 'timestamp' ) < strtotime(gmdate('Y-m-d ' . $hrtime)) ) {
				return gmdate('Y-m-d ' . $hrtime);
			} else {
				$next15th = mktime( 0, 0, 0, gmdate( 'n' ) + ( gmdate( 'j' ) >= (int) $select_day_for_month ), (int) $select_day_for_month );
				return gmdate('Y-m-d ' . $hrtime, $next15th);
			}
		}
		
		if ( 'weekly' == $run ) {
			$select_day_for_week = isset($data->email_select_week) ? $data->email_select_week : '0';
			if ( gmdate('w') == (int) $select_day_for_week && current_time( 'timestamp' ) < strtotime(gmdate('Y-m-d ' . $hrtime)) ) {
				return gmdate('Y-m-d ' . $hrtime);
			} else {
				return gmdate('Y-m-d ' . $hrtime, strtotime('next ' . $week[(int) $select_day_for_week]));
			}
		}
		
		if ( 'daily' == $run || 'daily-overnight' == $run ) {
			if ( current_time( 'timestamp' ) < strtotime(gmdate('Y-m-d ' . $hrtime)) ) {
				//today
				$datetime = new DateTime();
				return $datetime->format('Y-m-d ' . $hrtime);
			} else {
				$datetime = new DateTime('tomorrow');
				return $datetime->format('Y-m-d ' . $hrtime);
			}
		}

	}
	
	/*
	* update cron run date callback
	*/	
	public function update_cron_run_date_callback() {
		
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field($_POST['nonce']) : '';
		if ( ! wp_verify_nonce( $nonce, 'asre-ajax-nonce' ) ) {
			die();
		}
		
		$TIME = isset( $_POST['TIME'] ) ? sanitize_text_field($_POST['TIME']) : '';
		$hrtime = $TIME;

		$week = array(
			esc_html( 'Sunday', 'woocommerce' ),
			esc_html( 'Monday', 'woocommerce' ),
			esc_html( 'Tuesday', 'woocommerce' ),
			esc_html( 'Wednesday', 'woocommerce' ),
			esc_html( 'Thursday', 'woocommerce' ),
			esc_html( 'Friday', 'woocommerce' ),
			esc_html( 'Saturday', 'woocommerce' ),
		);

		$INTERVAL = isset( $_POST['INTERVAL'] ) ? sanitize_text_field($_POST['INTERVAL']) : '';
		$MONTH = isset( $_POST['MONTH'] ) ? sanitize_text_field($_POST['MONTH']) : '';
		$WEEK = isset( $_POST['WEEK'] ) ? sanitize_text_field($_POST['WEEK']) : '';
		$run = $INTERVAL;

		if ( 'monthly' == $run ) {
			$select_day_for_month = $MONTH;
			if ( gmdate('j') == $select_day_for_month && current_time('timestamp') < strtotime(gmdate('Y-m-d ' . $hrtime)) ) {
				$NextRunDate = gmdate('Y-m-d ' . $hrtime);
			} else {
				$next15th = mktime( 0, 0, 0, gmdate( 'n' ) + ( gmdate( 'j' ) >= $select_day_for_month ), $select_day_for_month ); 
				$NextRunDate = gmdate('Y-m-d ' . $hrtime, $next15th);
			}
			$newDate = gmdate('M d, Y g:iA', strtotime($NextRunDate));
		}

		if ( 'weekly' == $run || 'last-30-days' == $run ) {
			$select_day_for_week = $WEEK;
			if ( gmdate('w') == $select_day_for_week && current_time('timestamp') < strtotime(gmdate('Y-m-d ' . $hrtime)) ) {
				$NextRunDate = gmdate('Y-m-d ' . $hrtime);
			} else {
				$NextRunDate = gmdate('Y-m-d ' . $hrtime, strtotime('next ' . $week[$select_day_for_week]));
			}
			$newDate = gmdate('M d, Y g:iA', strtotime($NextRunDate));
		}

		if ( 'daily' == $run || 'daily-overnight' == $run ) {
			if ( current_time('timestamp') < strtotime(gmdate('Y-m-d ' . $hrtime)) ) {
				//today
				$datetime = new DateTime();
				$NextRunDate = $datetime->format('Y-m-d ' . $hrtime);
			} else {
				$datetime = new DateTime('tomorrow');
				$NextRunDate = $datetime->format('Y-m-d ' . $hrtime);
			}
			$newDate = gmdate('M d, Y g:iA', strtotime($NextRunDate));
		}
		
		

		$array = array(
			'NextRunDate'	=> isset($newDate) ? $newDate : '',
			'interval' => $INTERVAL,
			'sendTime' => $TIME,
			'week' => $WEEK,
			'month' => $MONTH,
		);

		echo json_encode($array);
		die();
	}
	
	/**
	 * Handle the enable/disable/delete actions.
	 *
	 * @since 1.0
	*/
	public function data_toggle_callback() {
		global $wpdb;

		// If on the WC Email reports screen & the current user can manage WooCommerce, continue.
		if ( isset( $_GET['page'] ) && $this->screen_id === $_GET['page'] && current_user_can( 'manage_woocommerce' ) ) {

			$action = isset( $_GET['action'] ) ? sanitize_text_field($_GET['action']) : false;

			// If no action or cart notice ID are set, bail.
			if ( ! $action || ! isset( $_GET['id'] ) ) {
				return;
			}

			$id = (int) $_GET['id'];

			if ( 'enable' === $action ) {

				$wpdb->query( $wpdb->prepare( 'UPDATE %1s SET email_enable=true WHERE id = %d', $this->table, $id ) );

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => $this->screen_id, 'result' => 'enabled' ), 'admin.php' ) ) );
				exit;

			} elseif ( 'disable' === $action ) {

				$wpdb->query( $wpdb->prepare( 'UPDATE %1s SET email_enable=false WHERE id = %d', $this->table, $id ) );

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => $this->screen_id, 'result' => 'disabled' ), 'admin.php' ) ) );
				exit;

			} elseif ( 'delete' === $action ) {

				$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE id = %d', $this->table, $id ) );

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => $this->screen_id, 'result' => 'deleted' ), 'admin.php' ) ) );
				
				wc_sales_report_email()->cron->remove_cron($id);
				
				exit;
			}
		}
	}
	
	/*
	* update report enable toggle of existing entry
	*/	
	public function update_enable_toggle_callback() {
		
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field($_POST['nonce']) : '';
		if ( ! wp_verify_nonce( $nonce, 'asre-ajax-nonce' ) ) {
			die();
		}
		
		global $wpdb;
		$id = isset( $_POST['ID'] ) ? sanitize_text_field($_POST['ID']) : '';
				
		if ( isset($_POST['check']) && 'true' == $_POST['check'] ) {
			$check = 1;	
		} else {
			$check = 0;		
		}

		$array = array();
		$data = array(
			'email_enable' => $check,
		);
		$where = array(
			'id' => $id,
		);

		$result = $wpdb->update( $this->table, $data, $where );

		$array = array(
			'status' => 'success',
			'id' => $id,
		);

		echo json_encode($data);
		die();
	}
	
	/*
	* get email content data for sales report
	*/
	public function email_content( $id ) {

		if (  $id == '' ) {
			return;
		}
		
		if ( $id == 0 ) {
			$data = (object) array (
				'email_enable' => '1',
				'report_name' => "Sales Report",
				'email_interval' => 'daily',
				'email_send_time' => "08:00",
				'email_recipients' => "kuldip@zorem.com",
				'display_data' => serialize(
					(object) array(
					'display_gross_sales' => "1",
					'display_total_sales' => "1",
					'display_coupon_used' => "1",
					'display_total_refunds' => "1",
					'display_total_shipping' => "1",
					'display_net_revenue' => "1",
					'display_total_orders' => "1",
					'display_total_items' => "1",
					'display_top_sellers' => "1",
					'display_top_categories' => "1",
					)
				),
			);
		} else {
			$data = $this->get_data_byid( $id );
		}
		
		
		$this->set_email_data( $data );

		$display_data = unserialize($data->display_data);
		
		$interval = isset($data->email_interval) ? $data->email_interval : 'daily';

		$date_range = $this->get_current_date_range( $interval );
		
		$data_array = array(
			'id' => $id,		
			'interval'  => $interval,
			'data' => $data,
			'display_data' => $display_data,
			'date_range' => $date_range,
		);
		
		if ( isset($display_data->display_gross_sales) && '1' == $display_data->display_gross_sales ) {
			//gross salses
			$data_array['gross_sales'] = wc_sales_report_email()->functions->get_total_reports( $date_range, $interval )->gross_sales;
		}
		
		if ( isset($display_data->display_total_sales) && '1' == $display_data->display_total_sales ) {
			//total salses
			$data_array['total_sales'] = wc_sales_report_email()->functions->get_total_reports( $date_range, $interval )->total_sales;
		}
		
		if ( isset($display_data->display_coupon_used) && '1' == $display_data->display_coupon_used ) {
			//total coupons
			$data_array['coupon_used'] = wc_sales_report_email()->functions->get_total_reports( $date_range, $interval )->coupons;
		}
		
		if ( isset($display_data->display_total_refunds) && '1' == $display_data->display_total_refunds ) {
			//total refunds
			$data_array['total_refunds'] = wc_sales_report_email()->functions->get_total_reports( $date_range, $interval )->refunds;
		}
		
		
		if ( wc_tax_enabled() ) {	
			if ( isset($display_data->display_total_tax) && '1' == $display_data->display_total_tax ) {
				//total taxes
				$data_array['total_taxes'] = wc_sales_report_email()->functions->get_total_reports( $date_range, $interval )->taxes;
			}
			
		}
		
		if ( isset($display_data->display_total_shipping) && '1' == $display_data->display_total_shipping ) {
			//total shipping
			$data_array['total_shipping'] = wc_sales_report_email()->functions->get_total_reports( $date_range, $interval )->shipping;
		}
		
		if ( isset($display_data->display_net_revenue) && '1' == $display_data->display_net_revenue ) {
			//net revenue
			$data_array['net_revenue'] = wc_sales_report_email()->functions->get_total_reports( $date_range, $interval )->net_revenue;
		}
		
		if ( isset($display_data->display_total_orders) && '1' == $display_data->display_total_orders ) {
			//total orders
			$data_array['total_orders'] = wc_sales_report_email()->functions->get_total_reports( $date_range, $interval )->orders_count;
		}
		
		if ( isset($display_data->display_total_items) && '1' == $display_data->display_total_items ) {
			//total items
			$data_array['total_items'] = wc_sales_report_email()->functions->get_total_reports( $date_range, $interval )->num_items_sold;
		}
		
		if ( isset($display_data->display_signups) && '1' == $display_data->display_signups ) {
			//total new customer
			$data_array['total_signups'] = wc_sales_report_email()->functions->get_total_new_customer( $date_range );		
		}
		
		if ( isset($display_data->display_top_sellers) && '1' == $display_data->display_top_sellers ) {
			//top seller
			$data_array['top_sellers'] = wc_sales_report_email()->functions->get_top_selling_reports( $date_range, $interval, $display_data );
		}

		if ( isset($display_data->display_top_categories) && '1' == $display_data->display_top_categories ) {
			//top categories
			$data_array['top_categories'] = wc_sales_report_email()->functions->get_top_categories_reports( $date_range, $display_data );
		}

		ob_start();
		wc_get_template(
			'email-report-content.php',
			$data_array,
			'woo-advanced-sales-report-email/', 
			wc_sales_report_email()->get_plugin_path() . '/classes/preview/'
		);
		
		$message = ob_get_clean();
		
		return $message;
		
	}
	
	/**
	 * Method triggered on Cron run.
	 * This method will create a WC_SRE_Sales_Report_Email object and call trigger method.
	 *
	 * @since  1.0.0
	*/
	public function cron_email_callback( $id ) {
		
		if ( empty( $id ) ) {
			return;
		}
		
		$data = $this->get_data_byid( $id );
		if (isset($data)) {
			// Check if extension is active
			$enabled = $data->email_enable;
			$report_status = $data->report_status;
			if ( '0' == $enabled || 'draft' == $report_status ) {
				return;
			}
			
		}

		// Check if an email should be send
		$interval = isset($data->email_interval) ? $data->email_interval : '';
		$selected_w_day = isset($data->email_select_week) ? $data->email_select_week : '';
		$selected_m_day = isset($data->email_select_month) ? $data->email_select_month : '';

		$now        = new DateTime( null, new DateTimeZone( wc_timezone_string() ) );
		
		$send_today = false;

		switch ( $interval ) {
			case 'last-30-days':
				// Send monthly reports on the selected day of the month
				if ( $selected_m_day == (int) $now->format( 'j' ) ) {
					$send_today = true;
				}
				break;
			case 'monthly':
				// Send monthly reports on the selected day of the month
				if ( $selected_m_day == (int) $now->format( 'j' ) ) {
					$send_today = true;
				}
				break;
			case 'weekly':
				// Send weekly reports on selected day of week
				if ( $selected_w_day == (int) $now->format( 'w' ) ) {
					$send_today = true;
				}
				break;
			case 'daily':
				// Send everyday if the interval is daily
				$send_today = true;
				break;
			case 'daily-overnight':
				// Send everyday if the interval is daily overnight
				$send_today = true;
				break;
		}

		// Check if we need to send an email today
		if ( true !== $send_today ) {
			return;
		}

		$wc_emails      = WC_Emails::instance();
		$emails         = $wc_emails->get_emails();	
		$mailer = WC()->mailer();
		$sent_to_admin = false;
		$plain_text = false;
		$email = '';
		
		$message = $this->email_content( $id );
		
		$email_heading = $data->email_subject;
		$subject_email = $data->email_subject;
		
		if (empty($subject_email)) {
			$subject_email = 'Sales Report for {site_title}';	
		}
		
		$subject = str_replace('{site_title}', get_bloginfo( 'name' ), $subject_email );
		
		// create a new email
		$email = new WC_Email();
		$headers = "Content-Type: text/html\r\n";
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );

		$recipients = $data->email_recipients;
		$recipients = explode(',', $recipients);
		
		$logger = wc_get_logger();
		if ($recipients) {
			foreach ($recipients as $recipient) {
				$bool = wp_mail( $recipient, $subject, $message, $email->get_headers() );
				if ('1' == $bool) { 
					$bool = 'Success';
				} else {
					$bool = 'Fail';
				}
				$logger->info( 'Report: ' . $data->report_name, array( 'source' => 'sre-log' ) );
				$logger->info( 'Email: ' . $recipient, array( 'source' => 'sre-log' ) );
				$logger->info( 'Status: ' . $bool, array( 'source' => 'sre-log' ) );
			}
		}		
	}
	
	/**
	 * Get the from name for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_name() {
		$from_name = apply_filters( 'woocommerce_email_from_name', get_option( 'woocommerce_email_from_name' ), $this );
		return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
	}

	/**
	 * Get the from address for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_address() {
		$from_address = apply_filters( 'woocommerce_email_from_address', get_option( 'woocommerce_email_from_address' ), $this );
		return sanitize_email( $from_address );
	}
	
	/*
	* set email data for sales report
	*/
	public function set_email_data( $data ) {
		$this->email_data = $data;
	}
	
	/*
	* retune email data for sales report
	*/
	public function get_email_data() {
		return $this->email_data;
	}
	
	/*
	* retune email data for sales report totals
	*/
	public function get_total_report_content( $widget_title, $current_data, $data ) {
		ob_start();
		?>
		<div class="report-widget">
			<p class="col-heading"><strong><?php esc_html_e( $widget_title, 'sales-report-email-pro' ); ?></strong></p>
			<p class="report-summary__item-data">
				<span class="widget-value">
				<?php 
					echo !empty($current_data) ? wp_kses_post($current_data) : '0';
				?>
				</span>
			</p>
		</div>
		<?php		
		$message = ob_get_clean();
		echo wp_kses_post($message);
	}
	
	/*
	* retune email data for sales report details
	*/
	public function get_details_report_content( $reports_data ) {
		ob_start();
		?>
		<h3 class="report-table-title"><?php esc_html_e( $reports_data['title'], 'sales-report-email-pro'); ?></h3>        
			<table class="report-table-widget" cellspacing="0" cellpadding="6" style="width: 100%;vertical-align:top;">
				<tbody class="report-table-list">	
					<tr>
						<?php foreach ( $reports_data['th'] as $key => $th ) { ?>
							<?php if ( is_array($reports_data['td']) && 0 < count( $reports_data['td'] ) ) { ?>
								<th <?php echo 0 != $key ? 'style="width:20%"' : ''; ?>><?php esc_html_e( $th, 'sales-report-email-pro' ); ?></th>
							<?php } ?>
						<?php } ?>
					</tr>
					<?php 
					if ( is_array($reports_data['td']) && 0 == count( $reports_data['td'] ) ) {
						echo '<tr><td colspan="' . count($reports_data['th']) . '">' . esc_html( 'data not available.', 'sales-report-email-pro' ) . '</td></tr>';
					} else { 
						foreach ( $reports_data['td'] as $td ) {
							if ( isset($td[0]) && empty($td[0]) ) {
								$td[0] = esc_html( 'Unknown', 'sales-report-email-pro' );
							}
							echo '<tr>';
							echo isset($td[0]) ? '<td>' . wp_kses_post($td[0]) . '</td>' : '';
							echo isset($td[1]) ? '<td>' . wp_kses_post($td[1]) . '</td>' : '';
							echo isset($td[2]) ? '<td>' . wp_kses_post($td[2]) . '</td>' : '';
							echo isset($td[3]) ? '<td>' . wp_kses_post($td[3]) . '</td>' : '';
							echo '</tr>';
						} 
					} 
					?>
				</tbody>
			</table>
		<?php		
		$message = ob_get_clean();
		echo wp_kses_post($message);
	}
	
	/**
	 * Get the current date range
	 *
	 * @since  1.0.0
	 * @return DateTime
	 */
	public function get_current_date_range( $interval ) {
		
		$this->interval = $interval;
		
		// Subtract a second from end date.
		$data = wc_sales_report_email()->admin->get_email_data();
		
		if ( 'one-time' == $this->interval ) {
			$daterange = isset($data->daterange) ? explode( '-', $data->daterange ) : '';
			$start_date = new DateTime( date_i18n( $daterange[0] . ' 00:00:00' ), new DateTimeZone( wc_timezone_string() ) );
			$end_date = new DateTime( date_i18n($daterange[1] . ' 23:59:59'), new DateTimeZone( wc_timezone_string() ) );
		} else {
			$start_date = new DateTime( date_i18n( 'Y-m-d 00:00:00' ), new DateTimeZone( wc_timezone_string() ) );
			$end_date = new DateTime( date_i18n('Y-m-d 23:59:59'), new DateTimeZone( wc_timezone_string() ) );
		}
		
		// Modify start date based on interval
		switch ( $this->interval ) {
			case 'one-time':
				$start_date;
				$end_date;
				break;
			case 'last-30-days':
				$start_date->modify( '-30 days' );
				$end_date->modify( '-1 day' );
				break;
			case 'monthly':
				$start_date->modify( 'first day of previous month' );
				$end_date->modify( 'last day of previous month' );		
				break;
			case 'weekly':
				$start_date->modify( '-1 week' );
				$end_date->modify( '-1 day' );
				break;	
			case 'daily':
				$start_date->modify( '-1 day' );
				$end_date->modify( '-1 day' );
				break;
			case 'daily-overnight':
				$start_date = new DateTime( date_i18n( gmdate( 'Y-m-d' ) . $data->day_hour_start ), new DateTimeZone( wc_timezone_string() ) );
				$end_date = new DateTime( date_i18n( gmdate( 'Y-m-d' ) . $data->day_hour_end ), new DateTimeZone( wc_timezone_string() ) );
				if ($data->day_hour_start >= $data->day_hour_end) {
					$start_date->modify( '-1 day' );
				}
				break;
			default:
				$start_date->modify( '-1 day' );
				$end_date->modify( '-1 day' );
				break;				
		}
		
		//date convert to gmt datetime
		$start_date_gmt = $this->convert_local_datetime_to_gmt($start_date->format('Y-m-d H:i:s'));
		$end_date_gmt = $this->convert_local_datetime_to_gmt($end_date->format('Y-m-d H:i:s'));		
		
		$date_range = (object) array(
			'start_date' => $start_date,
			'end_date' => $end_date,
			'start_date_gmt' => $start_date_gmt,
			'end_date_gmt' => $end_date_gmt,
		);
				
		return $date_range;
	}
	
	/**
	 * Get the convert datetime to gmt
	 *
	 * @param DateTime $datetime_string
	 *
	 * @since  1.0.0
	 */
	public static function convert_local_datetime_to_gmt( $datetime_string ) {
		$datetime = new \DateTime( $datetime_string, new \DateTimeZone( wc_timezone_string() ) );
		$datetime->setTimezone( new \DateTimeZone( 'GMT' ) );
		return $datetime;
	}
	
	/**
	 * Send test mail.
	 * This method will create a Sales_Report_Email object and call trigger method.
	 *
	 * @since  1.0.0
	*/
	public function send_test_sales_email_func() {
		
		$id = isset($_GET['id']) ? (int) $_GET['id'] : '';
		$data = $this->get_data_byid( $id );

		$wc_emails      = WC_Emails::instance();
		$emails         = $wc_emails->get_emails();	
		$mailer 		= WC()->mailer();
		$sent_to_admin 	= false;
		$plain_text 	= false;
		$email 			= '';
		$message 		= $this->email_content( $id );
		$email_heading 	= $data->email_subject;
		
		$subject_email 	= $data->email_subject;
		
		if (empty($subject_email)) {
			$subject_email = 'Sales Report for {site_title}';	
		}
		
		$subject = str_replace('{site_title}', get_bloginfo( 'name' ), $subject_email );

		// create a new email
		$email 		= new WC_Email();
		$headers 	= "Content-Type: text/html\r\n";
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );

		$recipients = isset($data->email_recipients) ? $data->email_recipients : get_option( 'admin_email' );
		$recipients = explode( ',', $recipients );
		
		$logger = wc_get_logger();
		if ($recipients) {
			foreach ( $recipients as $recipient) {
				$bool = wp_mail( $recipient, $subject, $message, $email->get_headers() );
				if ('0' == $bool) {
					$bool = 'Success';
				} else {
					$bool = 'Fail';
				}
				$logger->info( 'Report: ' . $data->report_name, array( 'source' => 'sre-log' ) );
				$logger->info( 'Email: ' . $recipient, array( 'source' => 'sre-log' ) );
				$logger->info( 'Status: ' . $bool, array( 'source' => 'sre-log' ) );
			}
		}

		echo json_encode( array('success' => 'true' ) );
		die();
	}
	
}
