<?php
/** 
 *
 * @class   WC_SRE_Admin_Notice
 * @package WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_SRE_Admin_Notice class
 *
 * @since 1.0.0
 */
class WC_SRE_Admin_Notice {
	
	/**
	 * Get the class instance
	 *
	 * @since  1.0.0
	 * @return WC_SRE_Admin_Notice
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 * @var object Class Instance
	*/
	private static $instance;
	
	/*
	* construct function
	*
	* @since 1.0.0
	*/
	function __construct() {
		$this->init();
    }

	/*
	* init function
	*
	* @since 1.0.0
	*/
    function init() {
        
		//callback for notices hook in admin
        add_action( 'admin_notices', array( $this, 'sre_pro_admin_notice' ) );
		add_action('admin_init', array( $this, 'sre_pro_plugin_notice_ignore' ) );

		//add_action('sre_settings_admin_notice', array( $this, 'sre_settings_admin_notice' ) );
		
    }

    /**
	 * SRE pro admin notice
	 *
	 * @since 1.0.0
	 */
	function sre_pro_admin_notice() {
		
		if ( class_exists( 'Sales_Report_Email_PRO' ) ) {
			return;
		}

		$date_now = date( 'm-d-Y' );
        if ( $date_now >= '04-18-2023') {
            $option_variable = 'sre_pro_v_2_9_1_plugin_notice_ignore';
            $query_variable = 'sre-pro-v-2-9-1-ignore-notice';
        } else {
            $option_variable = 'sre_pro_v_2_9_plugin_notice_ignore';
            $query_variable = 'sre-pro-v-2-9-ignore-notice';
        }

        if ( get_option( $option_variable ) ) {
            return;
        }
		
		$dismissable_url = esc_url(  add_query_arg( $query_variable, 'true' ) );
        ?>
        <style>
        .wp-core-ui .notice.sre-dismissable-notice{
            position: relative;
            padding-right: 38px;
            border-left-color: #005B9A;
        }
        .wp-core-ui .notice.sre-dismissable-notice h3{
            margin-bottom: 5px;
        }
        .wp-core-ui .notice.sre-dismissable-notice a.notice-dismiss{
            padding: 9px;
            text-decoration: none;
        }
        .wp-core-ui .button-primary.sre_notice_btn {
            background: #005B9A;
            color: #fff;
            border-color: #005B9A;
            text-transform: uppercase;
            padding: 0 11px;
            font-size: 12px;
            height: 30px;
            line-height: 28px;
            margin: 5px 0 15px;
        }
        </style>
        <div class="notice updated notice-success sre-dismissable-notice">
            <a href="<?php esc_html_e( $dismissable_url ); ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
            <h2 style="margin-bottom: 10px;">Important Update!</h2>
            <p>We will retire the <strong>Sales Report Email for WooCommerce</strong> plugin at the end of this month. We want to thank you for your support of the plugin over the years. As a way to show our appreciation, we are offering a <strong>50% discount</strong> on the <a target="blank" href="https://www.zorem.com/product/email-reports-for-woocommerce/">PRO</a> version of the plugin.</p>
			<p>Use code <strong>SRENEW50</strong> at checkout to receive the discount.</p>
			<p>This is a limited-time offer, so take advantage of it before Apr 30th, 2023.</p>
            <p>Best regards, </br>The zorem team</p>
            <a class="button-primary sre_notice_btn" target="blank" href="https://www.zorem.com/product/email-reports-for-woocommerce/">Upgrade Now</a>
            <a class="button-primary sre_notice_btn" href="<?php esc_html_e( $dismissable_url ); ?>">Dismiss</a>
        </div>
        <?php
		
	}
	
	/**
	 * SRE pro admin notice ignore
	 *
	 * @since 1.0.0
	 */
	function sre_pro_plugin_notice_ignore(){
		
		$date_now = date( 'm-d-Y' );
        if ( $date_now >= '04-18-2023') {
            $option_variable = 'sre_pro_v_2_9_1_plugin_notice_ignore';
            $query_variable = 'sre-pro-v-2-9-1-ignore-notice';
        } else {
            $option_variable = 'sre_pro_v_2_9_plugin_notice_ignore';
            $query_variable = 'sre-pro-v-2-9-ignore-notice';
        }
        if ( isset( $_GET[$query_variable] ) ) {
            update_option( $option_variable, 'true' );
        }

	}


	public function sre_settings_admin_notice() {
		$date_now = gmdate( 'Y-m-d' );
		if ( $date_now > '2022-06-30' ) {
			return;
		}
		include 'views/admin_message_panel.php';
	}
    
	
}

