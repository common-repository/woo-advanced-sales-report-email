<?php
/**
 * Sales report email
 *
 * Class WC_ASRE_Data_Functions
 * 
 * @version       1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WC_ASRE_Data_Functions { 

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	
	/**
	 * Get the class instance
	 *
	 * @return WC_ASRE_Data_Functions
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/**
	 * Get the total reports
	 *
	 * @return data
	*/
	public function get_total_reports( $date_range, $interval ) {
		
		$Interval = array(
			'daily' => 'day',
			'previous_day' => 'day',
			'daily-overnight' => 'day',
			'previous_overnight' => 'day',
			'weekly' => 'week',
			'previous_week' => 'week',
			'monthly' => 'month',
			'previous_month' => 'month',
			'last-30-days' => 'month',
			'previous-last-30-days' => 'month',
			'one-time' => 'month',
			'previous-one-time' => 'month',
		);
		
		$args = array(
			'before' 	=> $date_range->end_date->format( 'Y-m-d H:i:s' ),
			'after'  	=> $date_range->start_date->format( 'Y-m-d H:i:s' ),
			'interval' 	=> $Interval[$interval],
		);
		
		$reports = new \Automattic\WooCommerce\Admin\API\Reports\Revenue\Query( $args );
		$data = $reports->get_data();

		return $data->totals;
		
	}
	
	/**
	 * Get the new customer
	 *
	 * @return data
	*/
	public function get_total_new_customer( $date_range ) {
		
		$users_query = new WP_User_Query(
			array(
				'role' => 'customer',
				'number' => -1,
				'date_query' => array(
					array(
						'after' => $date_range->start_date->format( 'Y-m-d H:i:s' ),
						'before' => $date_range->end_date->format( 'Y-m-d H:i:s' ),
					)
				)
			)
		);
		
		return $users_query->total_users;
		
	}
	
	/**
	 * Get the top selling data
	 *
	 * @return data
	*/
	public function get_top_selling_reports( $date_range, $interval, $display_data ) {
		
		// Get setting data
		$data = wc_sales_report_email()->admin->get_email_data();
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		$limit_row = !empty($display_data->display_top_sellers_row) ? $display_data->display_top_sellers_row : 5;
		
		$products_data_store = new \Automattic\WooCommerce\Admin\API\Reports\Products\DataStore();
		$top_sellers       = $limit_row > 0 ? $products_data_store->get_data(
			apply_filters(
				'woocommerce_analytics_products_query_args',
				array(
					'orderby'       => 'items_sold',
					'order'         => 'desc',
					'before'       => $end_date,
					'after'        => $start_date,
					'per_page'      => $limit_row,
					'extended_info' => true,
				)
			)
		)->data : array();
		
		$total = array_sum( array_column( $top_sellers, 'items_sold' ) );
		
		//new array create for html
		$td_array = array();
		foreach ( $top_sellers as $top_seller ) {
			$name = $top_seller['extended_info']['name'] ? wp_kses_post($top_seller['extended_info']['name'] ) : '';
			$sku = $top_seller['extended_info']['sku'] && !empty($top_seller['extended_info']['sku'])  ? ' (' . wp_kses_post($top_seller['extended_info']['sku'] ) . ')' : '';
			$array = array( 
				$name . $sku,
				wp_kses_post($top_seller['items_sold']) . ' (' . round( ( $top_seller['items_sold']*100 )/$total ) . '%)',
				wp_kses_post(wc_price($top_seller['net_revenue'])),
			);
			array_push($td_array, $array);
		}
		
		$top_sellers = array( 
			'title' => 'Top Selling Products',
			'th' => array( 'Product Name', 'Quantity', 'Net Sales' ),
			'td' => $td_array
		);
		
		return $top_sellers;
		
	}
	
	/**
	 * Get the top categories data
	 *
	 * @return data
	*/
	public function get_top_categories_reports( $date_range, $display_data ) {
		
		// Get setting data
		$data = wc_sales_report_email()->admin->get_email_data();
		$limit_row = !empty($display_data->display_top_categories_row) ? $display_data->display_top_categories_row : 5;
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		
		$categories_data_store = new \Automattic\WooCommerce\Admin\API\Reports\Categories\DataStore();
		$top_categories       = $limit_row > 0 ? $categories_data_store->get_data(
			apply_filters(
				'woocommerce_analytics_categories_query_args',
				array(
					'orderby'       => 'items_sold',
					'order'         => 'desc',
					'before'       	=> $end_date,
					'after'        	=> $start_date,
					'per_page'      => $limit_row,
					'extended_info' => true,
				)
			)
		)->data : array();
		
		$total = array_sum( array_column( $top_categories, 'items_sold' ) );
		
		//new array create for html
		$td_array = array();
		foreach ( $top_categories as $top_category ) {
			$category_name = isset( $top_category['extended_info'] ) && isset( $top_category['extended_info']['name'] ) ? $top_category['extended_info']['name'] : '';
			$array = array( 
				$category_name,
				wp_kses_post($top_category['items_sold']) . ' (' . round( ( $top_category['items_sold']*100 )/$total ) . '%)',
				wp_kses_post(wc_price($top_category['net_revenue'])),
			);
			array_push($td_array, $array);
		}
		
		$top_categories = array( 
			'title' => 'Top Selling Categories',
			'th' => array( 'Category Name', 'Quantity', 'Net Sales' ),
			'td' => $td_array
		);
		
		return $top_categories;
		
	}
	
}
