<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_ASRE_Customizer_Admin {

	private static $screen_id = 'sre_customizer';
	private static $text_domain = 'woocommerce-advanced-sales-report-email';
	private static $screen_title = 'SRE Customizer'; 

	// WooCommerce email classes.
	public static $email_types_class_names  = array(
		'new_order'                         => 'WC_Email_New_Order',
		'cancelled_order'                   => 'WC_Email_Cancelled_Order',
		'customer_processing_order'         => 'WC_Email_Customer_Processing_Order',
		'customer_completed_order'          => 'WC_Email_Customer_Completed_Order',
		'customer_refunded_order'           => 'WC_Email_Customer_Refunded_Order',
		'customer_on_hold_order'            => 'WC_Email_Customer_On_Hold_Order',
		'customer_invoice'                  => 'WC_Email_Customer_Invoice',
		'failed_order'                      => 'WC_Email_Failed_Order',
		'customer_new_account'              => 'WC_Email_Customer_New_Account',
		'customer_note'                     => 'WC_Email_Customer_Note',
		'customer_reset_password'           => 'WC_Email_Customer_Reset_Password',
	);
	
	public static $email_types_order_status = array(
		'new_order'                         => 'processing',
		'cancelled_order'                   => 'cancelled',
		'customer_processing_order'         => 'processing',
		'customer_completed_order'          => 'completed',
		'customer_refunded_order'           => 'refunded',
		'customer_on_hold_order'            => 'on-hold',
		'customer_invoice'                  => 'processing',
		'failed_order'                      => 'failed',
		'customer_new_account'              => null,
		'customer_note'                     => 'processing',
		'customer_reset_password'           => null,
	);
	
	/**
	 * Get the class instance
	 *
	 * @since  1.0
	 * @return WC_ASRE_Customizer_Admin
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	*/
	private static $instance;
	
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
		add_action( 'admin_menu', array( $this, 'register_woocommerce_menu' ), 99 );

		add_action('rest_api_init', array( $this, 'route_api_functions' ) );
				
		add_action('admin_enqueue_scripts', array( $this, 'customizer_enqueue_scripts' ) );

		add_action('admin_footer', array( $this, 'admin_footer_enqueue_scripts' ) );

		add_action( 'wp_ajax_' . self::$screen_id . '_email_preview', array( $this, 'get_preview_func' ) );
		add_action( 'wp_ajax_send_' . self::$screen_id . '_test_email', array( $this, 'send_test_email_func' ) );


		// Custom Hooks for everyone
		add_filter( 'sre_customizer_email_options', array( $this, 'sre_customizer_email_options' ), 10, 1);
		add_filter( 'sre_customizer_preview_content', array( $this, 'sre_customizer_preview_content' ), 10, 1);
		
	}
	
	/*
	 * Admin Menu add function
	 *
	 * @since  2.4
	 * WC sub menu 
	*/
	public function register_woocommerce_menu() {
		add_menu_page( __( self::$screen_title, self::$text_domain ), __( self::$screen_title, self::$text_domain ), 'manage_options', self::$screen_id, array( $this, 'react_settingsPage' ) );
	}

	/*
	 * Call Admin Menu data function
	 *
	 * @since  2.4
	 * WC sub menu 
	*/
	public function react_settingsPage()
    {
        echo '<div id="root"></div>';
    }

	/*
	 * Add admin javascript
	 *
	 * @since  2.4
	 * WC sub menu 
	*/
	public function admin_footer_enqueue_scripts() {
		echo '<style type="text/css">#toplevel_page_'. self::$screen_id .' { display: none !important; }</style>';
	}
	
	/*
	* Add admin javascript
	*
	* @since 1.0
	*/	
	public function customizer_enqueue_scripts() {
		
		
		$page = isset( $_GET['page'] ) ? sanitize_text_field($_GET['page']) : '' ;
		
		// Add condition for css & js include for admin page  
		if ( self::$screen_id == $page ) {
			// Add the WP Media 
			wp_enqueue_media();
			wp_enqueue_script( self::$screen_id, plugin_dir_url(__FILE__) . 'dist/main.js', ['jquery', 'wp-util', 'wp-color-picker'], time(), true);
			wp_localize_script( self::$screen_id, self::$screen_id, array(
				'admin_email' => get_option('admin_email'),
				'back_to_wordpress_link' => admin_url("admin.php?page=woocommerce-advanced-sales-report-email"),
				'pro_link' => 'https://www.zorem.com/product/sales-report-email-pro/',
				'nonce'					=> wp_create_nonce('ajax-nonce'),
			));
			wp_enqueue_style( self::$screen_id.'-custom', plugin_dir_url(__FILE__) . 'assets/custom.css', array(), time() );
			wp_enqueue_script( self::$screen_id.'-custom', plugin_dir_url(__FILE__) . 'assets/custom.js', ['jquery', 'wp-util', 'wp-color-picker'], time(), true );
		}
		
	}


	/*
	 * Customizer Routes API 
	*/
	public function route_api_functions() {

		register_rest_route( self::$screen_id, 'settings',array(
			'methods'  => 'GET',
			'callback' => [$this, 'return_json_sucess_settings_route_api'],
			'permission_callback' => '__return_true',
		));

		/*register_rest_route( self::$screen_id, 'preview', array(
			'methods'  => 'GET',
			'callback' => [$this, 'return_json_sucess_preview_route_api'],
			'permission_callback' => '__return_true',
		));*/

		register_rest_route( self::$screen_id, 'store/update',array(
			'methods'				=> 'POST',
			'callback'				=> [$this, 'update_store_settings'],
			'permission_callback'	=> '__return_true',
		));

		register_rest_route( self::$screen_id, 'send-test-email',array(
			'methods'				=> 'POST',
			'callback'				=> [$this, 'send_test_email_func'],
			'permission_callback'	=> '__return_true',
		));

	}

	/*
	 * Settings API 
	*/
	public function return_json_sucess_settings_route_api( $request ) {
		$preview = !empty($request->get_param('preview')) ? $request->get_param('preview') : '';
		return wp_send_json_success($this->customize_setting_options_func( $preview ));

	}

	public function customize_setting_options_func( $preview) {

		$settings = apply_filters(  self::$screen_id . '_email_options' , $preview );
		
		return $settings; 

	}

	/*
	 * Preview API 
	*/
	/*public function return_json_sucess_preview_route_api($request) {
		$preview = !empty($request->get_param('preview')) ? $request->get_param('preview') : '';
		return wp_send_json_success($this->get_preview_email($preview));
	}*/

	public function get_preview_func() {
		$preview = isset($_GET['preview']) ? $_GET['preview'] : '';
		echo $this->get_preview_email($preview);die();
	}

	/**
	 * Get the email content
	 *
	 */
	public function get_preview_email( $preview ) { 

		$content = apply_filters( self::$screen_id . '_preview_content' , $preview );
		
		$content .= '<style type="text/css">body{margin: 0;}</style>';
		
		add_filter( 'wp_kses_allowed_html', array( $this, 'allowed_css_tags' ) );
		add_filter( 'safe_style_css', array( $this, 'safe_style_css' ), 10, 1 );

		return wp_kses_post($content);
	}

	/*
	* update a customizer settings
	*/
	public function update_store_settings( $request ) {
		
		$preview = !empty($request) ? $request->get_param('preview') : '';

		$data = $request->get_params() ? $request->get_params() : array();
		
		if ( ! empty( $data ) ) {
			
			//data to be saved
			
			$settings = $this->customize_setting_options_func( $preview );
			
			foreach ( $settings as $key => $val ) {
				if ( !isset($data[$key] ) || (isset($val['show']) && $val['show'] != true) ) {
					continue;
				}

				//check column exist
				if ( isset( $val['type'] ) && 'textarea' == $val['type'] && !isset( $val['option_key'] ) && isset($val['option_name']) ) {
					$option_data = get_option( $val['option_name'], array() );
					$option_data[$key] =isset($data[$key]) ? htmlentities( wp_unslash( $data[$key] ) ) : '';
					update_option( $val['option_name'], $option_data );
				} elseif ( isset( $val['option_type'] ) && 'key' == $val['option_type'] ) {
					$data[$key] = isset($data[$key]) ? wc_clean( wp_unslash( $data[$key] ) ) : '';
					update_option( $key, $data[$key] );
				} elseif ( isset( $val['option_type'] ) && 'array' == $val['option_type'] ) {
					if ( isset( $val['option_key'] ) && isset( $val['option_name'] ) ) {
						$option_data = get_option( $val['option_name'], array() );
						if ( $val['option_key'] == 'enabled' ) {
							$option_data[$val['option_key']] = isset($data[$key]) && $data[$key] == 1 ? wc_clean( wp_unslash( "yes" ) ) : wc_clean( wp_unslash( "no" ) );
						} else {
							$option_data[$val['option_key']] = isset($data[$key]) ? wc_clean( wp_unslash( $data[$key] ) ) : '';
						}
						update_option( $val['option_name'], $option_data );
					} else if ( isset($val['option_name']) ) {
						$option_data = get_option( $val['option_name'], array() );
						$option_data[$key] = isset($data[$key]) ? wc_clean( wp_unslash( $data[$key] ) ) : '';
						update_option( $val['option_name'], $option_data );
					}
				}
			}

			//// SRE Free Settings Save
			global $wpdb;
			$this->table = $wpdb->prefix . 'asre_sales_report';
			$report_data = (array) wc_sales_report_email()->admin->get_data_byid( $preview );
			$display_data = isset($report_data->display_data) && !empty($report_data->display_data) ? unserialize($report_data->display_data) : (object) array();

			foreach ( $settings as $key2 => $val2 ) {
				if ( !isset($data[$key2] ) && $val2['show'] != true ) {
					continue;
				}

				if ( isset( $val2['database_column'] ) ) {
					if ( isset( $val2['column_name'] ) && 'display_data' == $val2['column_name'] ) {
						$display_data->$key2 = isset($data[$key2]) ? sanitize_text_field($data[$key2]) : '';
						if ( isset( $val2['breackdown'] ) && true == $val2['breackdown'] ) {
							$row_key = $key2 . '_row';
							$display_data->$row_key = isset($data[$row_key]) ? sanitize_text_field($data[$row_key]) : '';
						}
					} else {
						$report_data[$key2] = isset($data[$key2]) ? sanitize_text_field($data[$key2]) : '';
					}
				}
			}

			if (!empty($display_data)) {
				$reports = wc_sales_report_email()->admin->get_data();
				if ( 1 > $preview && 1 > count($reports) ) { 
					$report_data['report_status'] = 'publish';
					$report_data['display_data'] = serialize($display_data);
					$wpdb->insert( $this->table, $report_data );
					$preview = $wpdb->insert_id;
				} else {
					$report_data['report_status'] = 'publish';
					$report_data['display_data'] = serialize($display_data);
					$wpdb->update( $this->table, $report_data, array('id' => wc_clean($preview)) );
				}

				//cron reset/update
				wc_sales_report_email()->cron->reset_cron($preview);
			}
			/// end SRE Free ////

			echo json_encode( array('success' => true, 'preview' => $preview) );
			die();
	
		}

		echo json_encode( array('success' => false) );
		die();
	}

	/*
	* send a test email
	*/
	public function send_test_email_func($request) {

		$data = $request->get_params() ? $request->get_params() : array();

		$preview = !empty( $data['preview'] ) ? sanitize_text_field($data['preview']) : '';
		$recipients = !empty( $data['recipients'] ) ? sanitize_text_field($data['recipients']) : '';

		if ( ! empty( $preview ) && ! empty( $recipients ) ) {
			
			$message 		= apply_filters( self::$screen_id . '_preview_content' , $preview );
			$subject_email 	= 'email';
			$subject = str_replace('{site_title}', get_bloginfo( 'name' ), 'Test ' . $subject_email );
			
			// create a new email
			$email 		= new WC_Email();
			add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
			add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );

			$recipients = explode( ',', $recipients );
			if ($recipients) {
				foreach ( $recipients as $recipient) {
					wp_mail( $recipient, $subject, $message, $email->get_headers() );
				}
			}
			
			echo json_encode( array('success' => true) );
			die();
			
		}

		echo json_encode( array('success' => false) );
		die();
	}

	public function sre_customizer_email_options($preview) {
		
		$report_data = wc_sales_report_email()->admin->get_data_byid( $preview );

		$display_data = isset($report_data->display_data) && !empty($report_data->display_data) ? unserialize($report_data->display_data) : array();

		$send_time_array = array();
				
		for ( $hour = 0; $hour < 24; $hour++ ) {
			for ( $min = 0; $min < 60; $min = $min + 30 ) {	
				$this_time = gmdate( 'g:ia', strtotime( "2014-01-01 $hour:$min" ) );
				$This_time = gmdate( 'H:i', strtotime( "2014-01-01 $hour:$min" ) );
				$send_time_array[ $This_time ] = $this_time;
			}
			unset($send_time_array[ '00:00' ]);
		}

		$month = array();
		for ( $day = 1; $day <= 30; $day++ ) {
			$month[ $day ] = $day;
		}
		
		$week = array(
			esc_html( 'Sunday', 'woocommerce' ),
			esc_html( 'Monday', 'woocommerce' ),
			esc_html( 'Tuesday', 'woocommerce' ),
			esc_html( 'Wednesday', 'woocommerce' ),
			esc_html( 'Thursday', 'woocommerce' ),
			esc_html( 'Friday', 'woocommerce' ),
			esc_html( 'Saturday', 'woocommerce' ),
		);
		$interval = array(
			'daily'   => esc_html( 'Daily', 'woocommerce' ),
			'weekly'  => esc_html( 'Weekly', 'woocommerce' ),
			'monthly' => esc_html( 'Monthly', 'woocommerce' ),
		);
		
		$settings = array(

			//panels
			'report_settings'	=> array(
				'title'	=> esc_html__( 'Report Settings', self::$text_domain ),
				'type'	=> 'panel',
			),
			'report_design'	=> array(
				'title'	=> esc_html__( 'Report Design', self::$text_domain ),
				'type'	=> 'panel',
			),
			'report_totals'	=> array(
				'title'	=> esc_html__( 'Report Totals', self::$text_domain ),
				'type'	=> 'panel',
			),
			'report_details'	=> array(
				'title'	=> esc_html__( 'Report Details', self::$text_domain ),
				'type'	=> 'panel',
			),
			
			//settings
			'email_enable' => array(
				'parent'=> 'report_settings',
				'title'    => esc_html__( 'Enable email', self::$text_domain ),
				'type'     => 'tgl-btn',
				'show'		=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'default'	=> isset($report_data->email_enable) ? $report_data->email_enable : 1
			),
			'report_name' => array(
				'parent'=> 'report_settings',
				'type'		=> 'text',
				'title'		=> esc_html( 'Report Name', self::$text_domain ),
				'show'		=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'placeholder' => esc_html( 'Sales Report', self::$text_domain ),
				'default'	=> isset($report_data->report_name) ? $report_data->report_name : esc_html( 'Sales Report', self::$text_domain ),
				'class'	=> 'heading'
			),
			'email_interval' => array(
				'parent'=> 'report_settings',
				'type'		=> 'select',
				'title'		=> esc_html( 'Report Type', self::$text_domain ),
				'show'		=> true,
				'refresh'	=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'class'     => 'email_interval',
				'options'	=> $interval,
				'default'	=> isset($report_data->email_interval) ? $report_data->email_interval : 'daily'
			),
			'email_select_week' => array(
				'parent'=> 'report_settings',
				'type'		=> 'select',
				'title'		=> esc_html( 'Day of Week', self::$text_domain ),
				'show'		=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'options'   => $week,
				'class'     => 'half email_select_week ' . ($report_data->email_interval != 'weekly' ? 'hide' : ''),
				'tooltip'     => esc_html( 'Day of the week to send the report email.', self::$text_domain ),
				'default'	=> isset($report_data->email_select_week) ? $report_data->email_select_week : ''
			),
			'email_select_month' => array(
				'parent'=> 'report_settings',
				'type'		=> 'select',
				'title'		=> esc_html( 'Day of Month', self::$text_domain ),				
				'show'		=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'options'   => $month,
				'class'     => 'half email_select_month ' . ($report_data->email_interval != 'monthly' ? 'hide' : ''),
				'tooltip'     => esc_html( 'the day on the month to send the report email.', self::$text_domain ),
				'default'	=> isset($report_data->email_select_month) ? $report_data->email_select_month : ''
			),
			'email_send_time' => array(
				'parent'=> 'report_settings',
				'type'		=> 'select',
				'title'		=> esc_html( 'Send Report At', self::$text_domain ),				
				'show'		=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'class'     => 'half email_send_time',
				'options'   => $send_time_array,
				'tooltip'     => esc_html( 'the time of day to send out the report email.', self::$text_domain ),
				'default'	=> isset($report_data->email_send_time) ? $report_data->email_send_time : '08:00'
			),
			'email_recipients' => array(
				'parent'=> 'report_settings',
				'title'    => esc_html__( 'Email Recipients', self::$text_domain ),
				'desc'  => esc_html__( 'add comma-separated email addresses', self::$text_domain ),
				'type'     => 'text',
				'show'     => true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'default'	=> isset($report_data->email_recipients) ? $report_data->email_recipients : get_option('admin_email')
			),
			'email_subject' => array(
				'parent'=> 'report_settings',
				'title'    => esc_html__( 'Email Subject', self::$text_domain ),
				'desc'  => esc_html__( 'Available placeholder: {site_title} ', self::$text_domain ),
				'type'     => 'text',
				'show'     => true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'placeholder' => esc_html( 'Sales Report for {site_title}', self::$text_domain ),
				'default'	=> isset($report_data->email_subject) ? $report_data->email_subject : '',
			),
			'email_content' => array(
				'parent'=> 'report_settings',
				'title'    => esc_html__( 'Additional content', self::$text_domain ),
				'type'     => 'textarea',
				'show'     => true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'class'	=> 'additional_content',
				'placeholder'	=> esc_html( '', self::$text_domain ),
				'default'	=> isset($report_data->email_content) ? $report_data->email_content : '',
			),

			//email design
			'show_header_image' => array(
				'parent'=> 'report_design',
				'title'    => esc_html__( 'Display Header Image', self::$text_domain ),
				'type'     => 'tgl-btn',
				'show'     => true,
				'disabled' => true,
				'pro'		=> true,
				'database_column' => 'design_settings',
				'option_type' => 'array',
				'default'	=> isset($report_data->show_header_image) ? $report_data->show_header_image : 1
			),
			'branding_logo' => array(
				'parent'=> 'report_design',
				'title'    => esc_html__( 'Change header image', self::$text_domain ),
				'type'     => 'media',
				'show'     => true,
				'disabled' => true,
				'pro'		=> true,
				'database_column' => 'design_settings',
				'option_type' => 'array',
				'desc'     => esc_html( 'image size requirements: 200px/40px.', self::$text_domain ),
				'default'	=> isset($report_data->branding_logo) ? $report_data->branding_logo : ''
			),
			'display_edit_report_link' => array(
				'parent'=> 'report_design',
				'type'		=> 'checkbox',
				'title'    => esc_html__( 'Hide Edit Report Link', self::$text_domain ),	
				'show'		=> true,
				'disabled' => true,
				'pro'		=> true,
				'database_column' => 'design_settings',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Enable this option to remove zorem branding in this email.', self::$text_domain ),
				'default'	=> isset($display_data->display_edit_report_link) ? $display_data->display_edit_report_link : 0
			),
			'display_zorem_branding' => array(
				'parent'=> 'report_design',
				'type'		=> 'checkbox',
				'title'    => esc_html__( 'Hide Powered by zorem Link', self::$text_domain ),	
				'show'		=> true,
				'disabled' => true,
				'pro'		=> true,
				'database_column' => 'design_settings',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Enable this option to remove zorem branding in this email.', self::$text_domain ),
				'default'	=> isset($display_data->display_zorem_branding) ? $display_data->display_zorem_branding : 0
			),

			//report totals
			'display_previous_period' => array(
				'parent'=> 'report_totals',
				'type'		=> 'checkbox',
				'title'    => esc_html__( 'Compare to the previous period', self::$text_domain ),	
				'show'		=> true,
				'disabled' => true,
				'pro'		=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Compare the report totals to previous period.', self::$text_domain ),
				'default'	=> isset($display_data->display_previous_period) ? $display_data->display_previous_period : 1
			),
			'display_gross_sales' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Gross Sales', self::$text_domain ),				
				'show'		=> true,
				'refresh'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'sum of all orders not including shipping & taxes with refunds taken off.', self::$text_domain),
				'default'	=> isset($display_data->display_gross_sales) ? $display_data->display_gross_sales : 1
			),
			'display_total_sales' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Total Sales', self::$text_domain ),				
				'show'		=> true,
				'refresh'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'sum of all orders including shipping & taxes with refunds taken off.', self::$text_domain),
				'default'	=> isset($display_data->display_total_sales) ? $display_data->display_total_sales : 1
			),
			'display_coupon_used' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Net Discount Amount', 'woocommerce' ),				
				'show'		=> true,
				'refresh'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Total discounts with coupons.', self::$text_domain),
				'default'	=> isset($display_data->display_coupon_used) ? $display_data->display_coupon_used : 1
			),
			'display_coupon_count' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Discounted Orders', 'woocommerce' ),				
				'show'		=> true,
				'disabled' => true,
				'pro'		=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Total discounts orders with coupons.', self::$text_domain),
				'default'	=> isset($display_data->display_coupon_count) ? $display_data->display_coupon_count : ''
			),
			'display_total_refunds' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Refunds', 'woocommerce' ),				
				'show'		=> true,
				'refresh'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Total Refunds during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_total_refunds) ? $display_data->display_total_refunds : 1
			),
			'display_total_tax' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Taxes', 'woocommerce' ),				
				'show'		=> true,
				'refresh'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Total tax(Order Tax + Shipping Tax) charges during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_total_tax) ? $display_data->display_total_tax : ''
			),
			'display_total_shipping' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Shipping', 'woocommerce' ),				
				'show'		=> true,
				'refresh'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Total shipping charges during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_total_shipping) ? $display_data->display_total_shipping : 1
			),
			'display_total_shipping_tax' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Shipping Tax', 'woocommerce' ),				
				'show'		=> true,
				'disabled' => true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Total shipping tax charges during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_total_shipping_tax) ? $display_data->display_total_shipping_tax : ''
			),
			'display_net_revenue' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Net Sales', self::$text_domain ),				
				'show'		=> true,
				'refresh'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'sum of all orders, with refunds, shipping & taxes taken off.', self::$text_domain),
				'default'	=> isset($display_data->display_net_revenue) ? $display_data->display_net_revenue : 1
			),
			'display_total_orders' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Orders', 'woocommerce' ),				
				'show'		=> true,
				'refresh'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Total count of orders in status Processing/Complete.', self::$text_domain),
				'default'	=> isset($display_data->display_total_orders) ? $display_data->display_total_orders : 1
			),
			'display_total_items' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Items Sold', self::$text_domain ),				
				'show'		=> true,
				'refresh'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Total items sold during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_total_items) ? $display_data->display_total_items : 1
			),
			'display_signups' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'New Customers', self::$text_domain ),				
				'show'		=> true,
				'refresh'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Total number of new signups during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_signups) ? $display_data->display_signups : ''
			),
			'display_downloads' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Downloads', self::$text_domain ),				
				'show'		=> true,
				'disabled' => true,
				'pro'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Total count of downloaded files during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_downloads) ? $display_data->display_downloads : ''
			),
			'display_average_order_value' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'AVG. Order Value', self::$text_domain ),				
				'show'		=> true,
				'disabled' => true,
				'pro'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Average Order Value during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_average_order_value) ? $display_data->display_average_order_value : ''
			),
			'display_average_daily_sales' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'AVG. Daily Sales', self::$text_domain ),				
				'show'		=> true,
				'disabled' => true,
				'pro'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Average Daily Sales during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_average_daily_sales) ? $display_data->display_average_daily_sales : ''
			),
			'display_average_daily_items' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'AVG. Order Items', self::$text_domain ),				
				'show'		=> true,
				'disabled'	=> true,
				'pro'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'Average Items per order during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_average_daily_items) ? $display_data->display_average_daily_items : ''
			),

			//report details
			'display_top_sellers' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Top Selling Products', self::$text_domain ),				
				'show'		=> true,
				'refresh'	=> true,
				'breackdown' => true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'product name, quantity, amount during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_top_sellers) ? $display_data->display_top_sellers : 1
			),
			'display_top_variations' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Top Selling Variations', self::$text_domain ),				
				'show'		=> true,
				'disabled'	=> true,
				'pro'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'product name, quantity, amount during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_top_variations) ? $display_data->display_top_variations : ''
			),
		   'display_top_categories' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Top Selling Categories', self::$text_domain ),				
				'show'		=> true,
				'refresh'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'category name, quantity, amount during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_top_categories) ? $display_data->display_top_categories : 1
			),
			'display_sales_by_coupons' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Sales By Coupons', self::$text_domain ),				
				'show'		=> true,
				'disabled'	=> true,
				'pro'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'coupon, quantity used and total discount amount during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_sales_by_coupons) ? $display_data->display_sales_by_coupons : ''
			),
			'display_sales_by_billing_city' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Sales By Billing City', self::$text_domain ),				
				'show'		=> true,
				'disabled'	=> true,
				'pro'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'City, orders count, total amount during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_sales_by_billing_city) ? $display_data->display_sales_by_billing_city : ''
			),
			'display_sales_by_shipping_city' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Sales By Shipping City', self::$text_domain ),				
				'show'		=> true,
				'disabled'	=> true,
				'pro'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'City, orders count, total amount during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_sales_by_shipping_city) ? $display_data->display_sales_by_shipping_city : ''
			),
			'display_sales_by_billing_state' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Sales By Billing State', self::$text_domain ),				
				'show'		=> true,
				'disabled'	=> true,
				'pro'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'State, orders count, total amount during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_sales_by_billing_state) ? $display_data->display_sales_by_billing_state : ''
			),
			'display_sales_by_shipping_state' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Sales By Shipping State', self::$text_domain ),				
				'show'		=> true,
				'disabled'	=> true,
				'pro'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'State, orders count, total amount during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_sales_by_shipping_state) ? $display_data->display_sales_by_shipping_state : ''
			),
			'display_sales_by_billing_country' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Sales By Billing Country', self::$text_domain ),				
				'show'		=> true,
				'disabled'	=> true,
				'pro'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'country, orders count, total amount during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_sales_by_billing_country) ? $display_data->display_sales_by_billing_country : ''
			),
			'display_sales_by_shipping_country' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Sales By Shipping Country', self::$text_domain ),				
				'show'		=> true,
				'disabled'	=> true,
				'pro'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'country, orders count, total amount during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_sales_by_shipping_country) ? $display_data->display_sales_by_shipping_country : ''
			),
			'display_order_status' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Orders By Status', self::$text_domain ),				
				'show'		=> true,
				'disabled'	=> true,
				'pro'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'orders Status, order count, total amount during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_order_status) ? $display_data->display_order_status : ''
			),
			'display_order_details' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Report By Order details', self::$text_domain ),				
				'show'		=> true,
				'disabled'	=> true,
				'pro'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'orders id, customer name, total amount during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_order_details) ? $display_data->display_order_details : ''
			),
			'display_payment_method' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html( 'Orders By Payment Method', self::$text_domain ),				
				'show'		=> true,
				'disabled'	=> true,
				'pro'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'tooltip'     => esc_html( 'payment method, order count, total amount during the report period.', self::$text_domain),
				'default'	=> isset($display_data->display_payment_method) ? $display_data->display_payment_method : ''
			),
		);

		return $settings;
	}

	public function sre_customizer_preview_content( $preview ) {

		$content = wc_sales_report_email()->admin->email_content( $preview );

		return $content;
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
	
	/**
	 * Get the email order status
	 *
	 * @param string $email_template the template string name.
	 */
	public function get_email_order_status( $email_template ) {
		
		$order_status = apply_filters( 'customizer_email_type_order_status_array', self::$email_types_order_status );
		
		$order_status = self::$email_types_order_status;
		
		if ( isset( $order_status[ $email_template ] ) ) {
			return $order_status[ $email_template ];
		} else {
			return 'processing';
		}
	}

	/**
	 * Get the email class name
	 *
	 * @param string $email_template the email template slug.
	 */
	public function get_email_class_name( $email_template ) {
		
		$class_names = apply_filters( 'customizer_email_type_class_name_array', self::$email_types_class_names );

		$class_names = self::$email_types_class_names;
		if ( isset( $class_names[ $email_template ] ) ) {
			return $class_names[ $email_template ];
		} else {
			return false;
		}
	}


	public function allowed_css_tags( $tags ) {
		$tags['style'] = array( 'type' => true, );
		return $tags;
	}
	
	public function safe_style_css( $styles ) {
		 $styles[] = 'display';
		return $styles;
	}

}
