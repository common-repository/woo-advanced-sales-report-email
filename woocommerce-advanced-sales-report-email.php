<?php
/*
 * Plugin Name: Sales Report Email for WooCommerce
 * Plugin URI: http://www.zorem.com/shop/
 * Description: The Sales Report Email will help know how well your store is performing and how your products are selling by emailing you a daily, weekly, or monthly sales report email.
 * Version: 2.9
 * Text Domain: woocommerce-advanced-sales-report-email
 * Author: zorem
 * Author URI: https://www.zorem.com/
 * WC requires at least: 4.8
 * WC tested up to: 7.6.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woocommerce_Advanced_Sales_Report_Email {
	
	public $email_data;
	
	/**
	 * Sales Report Email for WooCommerce
	 *
	 * @var string
	 */
	public $version = '2.9';
	
	/**
	 * Constructor
	 *
	 * @since  1.0.0
	*/
	public function __construct() {
		
		
		// Check if Wocoomerce is activated
		if ( !$this->is_sre_pro_active() ) {
			if ( $this->is_wc_active() ) {

				// Setup the autoloader
				//$this->setup_autoloader();

				$this->includes();
				$this->init();
			}
		}
	}
	
	/**
	 * Check if WooCommerce is active
	 *
	 * @since  1.0.0
	 * @return bool
	*/
	private function is_wc_active() {
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$is_active = true;
		} else {
			$is_active = false;
		}
		

		// Do the WC active check
		if ( false === $is_active ) {
			add_action( 'admin_notices', array( $this, 'notice_activate_wc' ) );
		}		
		return $is_active;
	}
	
	/**
	 * Display WC active notice
	 *
	 * @since  1.0.0
	*/
	public function notice_activate_wc() {
		?>
		<div class="error">
			<p><?php printf( esc_html( 'Please install and activate %1$sWooCommerce%2$s for WC Sales Report Email to work!', 'woocommerce-advanced-sales-report-email' ), '<a href="' . esc_url(admin_url( 'plugin-install.php?tab=search&s=WooCommerce&plugin-search-input=Search+Plugins' )) . '">', '</a>' ); ?></p>
		</div>
		<?php
	}
	
	/**
	 * Check if SRE PRO is active
	 *
	 * @access private
	 * @since  1.0.0
	 * @return bool
	*/
	private function is_sre_pro_active() {
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'sales-report-email-pro/sales-report-email-pro.php' ) ) {
			$is_active = true;
		} else {
			$is_active = false;
		}

			
		return $is_active;
	}

	/**
	 * Initialize plugin
	 *
	 * @since  1.0.0
	*/
	private function init() {
		
		register_activation_hook( __FILE__, array( $this->install, 'asre_insert_table_columns' ) );
		add_action( 'init', array( $this->install, 'asre_update_install_callback' ) );
		
		// Load plugin textdomain
		add_action('plugins_loaded', array($this, 'load_textdomain'));
		
		//load javascript in admin
		add_action('admin_enqueue_scripts', array( $this, 'wc_esrc_enqueue' ) );
		
		//callback for add action link for plugin page	
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'my_plugin_action_links' ));
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'my_plugin_action_PRO_links' ));
		
	}

	/**
	 * Include plugin file.
	 *
	 * @since 1.0.0
	 *
	 */	
	public function includes() {

		require_once $this->get_plugin_path() . '/classes/class-wc-asre-admin-notice.php';
		$this->notice = WC_SRE_Admin_Notice::get_instance();

		require_once $this->get_plugin_path() . '/classes/class-wc-asre-installation.php';
		$this->install = WC_Install_Sales_Report_Email::get_instance();
		
		require_once $this->get_plugin_path() . '/classes/class-wc-asre-admin.php';
		$this->admin = WC_ASRE_Admin::get_instance();

		require_once $this->get_plugin_path() . '/classes/class-wc-asre-cron-manager.php';
		$this->cron = WC_ASRE_Cron_Manager::get_instance();
		
		require_once $this->get_plugin_path() . '/classes/class-wc-asre-data-functions.php';
		$this->functions = WC_ASRE_Data_Functions::get_instance();
		
		// customizer
		require_once $this->get_plugin_path() . '/classes/customizer/customizer-admin.php';	
		$this->customizer = WC_ASRE_Customizer_Admin::get_instance();

	}
	
	/**
	 * Add plugin action links.
	 *
	 * Add a link to the settings page on the plugins.php page.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $links List of existing plugin action links.
	 * @return array         List of modified plugin action links.
	 */
	public function my_plugin_action_links( $links ) {
		$links = array_merge( array(
			'<a href="' . esc_url( admin_url( '/admin.php?page=woocommerce-advanced-sales-report-email' ) ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>'
		), array(
			'<a href="' . esc_url( 'https://www.zorem.com/docs/sales-report-email-for-woocommerce/?utm_source=wp-admin&utm_medium=SRE&utm_campaign=docs' ) . '" target="_blank">' . esc_html( 'Docs', 'woocommerce' ) . '</a>'
		), array(
			'<a href="' . esc_url( 'https://wordpress.org/support/plugin/woo-advanced-sales-report-email/reviews/#new-post' ) . '" target="_blank">' . esc_html( 'Review', 'woocommerce' ) . '</a>'
		), $links );
		return $links;
	}
	
	/**
	 * Add plugin action links.
	 *
	 * Add a link to the pro product page on the plugins.php page.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $links List of existing plugin action links.
	 * @return array         List of modified plugin action links.
	 */
	public function my_plugin_action_PRO_links( $links ) {
		
		if ( class_exists( 'Sales_Report_Email_PRO_Add_on' ) ) {
			return $links;
		}
		
		$links = array_merge( $links, array(
			'<a target="_blank" style="color: #45b450; font-weight: bold;" href="' . esc_url( 'https://www.zorem.com/product/sales-report-email-pro/') . '">' . __( 'Go Pro', 'woocommerce' ) . '</a>'
		) );
		
		return $links;
	}
			
	/*
	* load text domain
	*/
	public function load_textdomain() {
		load_plugin_textdomain( 'woocommerce-advanced-sales-report-email', false, plugin_dir_path( plugin_basename(__FILE__) ) . 'languages/' );
	}
	

	/**
	 * Gets the absolute plugin path without a trailing slash, e.g.
	 * /path/to/wp-content/plugins/plugin-directory.
	 *
	 * @return string plugin path
	 */
	public function get_plugin_path() {
		if ( isset( $this->plugin_path ) ) {
			return $this->plugin_path;
		}

		$this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );

		return $this->plugin_path;
	}
	
	/*
	* @return __FILE__.
	*/
	public static function get_plugin_domain() {
		return __FILE__;
	}

	
	/*
	* plugin file directory function
	*/	
	public function plugin_dir_url() {
		return plugin_dir_url( __FILE__ );
	}
	
	/*
	* Add admin javascript
	*/	
	public function wc_esrc_enqueue() {
		
		
		// Add condition for css & js include for admin page  
		if (!isset($_GET['page'])) {
				return;
		}
		if ( 'woocommerce-advanced-sales-report-email' != $_GET['page'] ) {
			return;
		}
			
		// Add the WP Media 
		wp_enqueue_media();
		
		// Add tiptip js and css file
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woocommerce_admin_styles' );
	
		wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
		wp_enqueue_script( 'jquery-tiptip' );
		
		wp_enqueue_style( 'asrc-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css', array(), $this->version );		
		wp_enqueue_script( 'asrc-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array('jquery','wp-color-picker'), $this->version );
		
		wp_localize_script( 'asrc-admin-js', 'asrc_object', 
			array( 
				'admin_url' => admin_url(),
				'nonce' => wp_create_nonce('asre-ajax-nonce')
			) 
		);
		
	}
	
	
}
/**
 * Returns an instance of Woocommerce_Advanced_Sales_Report_Email.
 *
 * @since 1.6.5
 * @version 1.6.5
 *
 * @return Woocommerce_Advanced_Sales_Report_Email
*/
function wc_sales_report_email() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new Woocommerce_Advanced_Sales_Report_Email();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
wc_sales_report_email();

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );