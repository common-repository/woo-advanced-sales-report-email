<?php
/**
 * Sales report email preview
 *
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
$email_content = str_replace('{site_title}', get_bloginfo( 'name' ), $data->email_content );
$general_format = get_option('date_format');
$time_format = get_option('time_format');
if ( 'F j, Y' == $general_format) {
	$date_format = 'M j, Y';
} else { 
	$date_format = $general_format;
}

?>    
<html 
<?php 
if (is_rtl()) {
	echo 'dir="rtl"';
}
?>
>
	<head>
		<meta media="all" name="viewport" content="width=device-width, initial-scale=1.0">
		<title>
			<?php 
			if ( empty($data->email_subject)) {
				echo esc_html(str_replace('{site_title}', get_bloginfo( 'name' ), 'Sales Report for {site_title}' ));
			} else {
				echo esc_html($data->email_subject);
			} 
			?>
		</title>
		<?php if (is_rtl()) { ?>
		<style type="text/css">
		.report-table-widget th {
			text-align: right !important;
		}
		.growth-span {
			float:left !important;
		}
		.sales-report-email-template {
			text-align: right !important;
		}
		.report-table-widget tr td {
			text-align: right !important;
		}
		@media screen and (max-width: 500px) {
			.report-dates td {
				float: left;
				width: 100%;
				font-size: 13px !important;
				text-align: right !important;
				padding-left: 0;
			}
		}
		</style>
		<?php } else { ?>
		<style type="text/css">
		.report-table-widget th {
			text-align: left;
		}

		@media screen and (max-width: 500px) {
			.report-dates td {
				float: left;
				width: 100%;
				font-size: 13px !important;
				text-align: left !important;
				padding-left: 0;
			}
		}
		</style>
		<?php } ?>
		<style type="text/css">
		.sales-report-email-template {
			max-width: 900px;
			margin: 20px auto;
			padding: 70px 0;
			font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
			background-color: #ffffff;
			border: 1px solid #e0e0e0;
			border-radius: 3px;
			padding: 20px;
		}

		.report-heading {
			border-bottom: 1px solid #e0e0e0;
			margin-bottom: 20px;
			display: inline-block;
			width: 100%;
		}

		.report-summary {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			display: table;
			width: 100%;
		}

		.report-widget {
			width: 33.33%;
			display: inline-block;
			padding: 14px 16px;
			margin-left: 0;
			background-color: #fafafa;
			border: 1px solid #e0e0e0;
			text-decoration: none;
			box-sizing: border-box;
		}

		.column4 .report-widget {
			width: calc(25% - 0px);
		}

		.report-widget:hover {
			background-color: #f3f4f5;
		}

		.report-summary__item-data {
			margin: 0;
			margin-bottom: 5px;
			float: left;
			width: 100%;
		}

		.report-summary__item-prev-label {
			font-size: 11px;
			color: #555d66;
			display: inline-block;
			width: 100%;
		}

		.report-summary__item-prev-value {
			font-size: 11px;
			color: #555d66;
			display: inline-block;
		}

		.col-6 {
			width: 49%;
			box-sizing: border-box;
			display: inline-block;
			margin-right: 5px;
			vertical-align: top;
		}

		.col-heading {
			margin: 0 0 10px !important;
			display: block;
			margin-bottom: 16px;
			font-size: 11px;
			text-transform: uppercase;
			color: #6c7781;
			line-height: 1.4em;
			overflow: hidden;
			white-space: nowrap;
			text-overflow: ellipsis;
		}

		.widget-value {
			margin-bottom: 4px;
			font-size: 18px;
			font-size: 1.125rem;
			font-weight: 600;
			color: #191e23;
			flex: 1 0 auto;
		}

		.report-table-widget tr td img {
			width: 50px;
			height: auto;
			vertical-align: middle;
			margin-right: 10px;
		}

		.report-table-widget {
			border-radius: 0;
			border: 1px solid #e0e0e0;
			box-shadow: none;
			font-size: 14px;
		}

		.report-table-widget tr td {
			border-top: 1px solid #e0e0e0;
			padding: 7px 10px;
			display: table-cell;
			line-height: 1.5;
			color: #333;
		}

		.report-table-widget thead tr th {
			text-align: left;
			padding: 15px 10px;
			border-radius: 0;
		}

		.report-table-widget tbody tr th, .report-table-widget tbody tr td {
			background-color: #fff;
			padding: 7px 10px;
			border-top: 1px solid #e0e0e0;
		}
		
		.report-table-widget tbody tr > td, .report-table-widget tbody tr > th {
			border-left: 0;
		}

		.report-table-widget tbody tr > td ~ td, .report-table-widget tbody tr > th ~ th {
			border-left: 1px solid #e0e0e0;
		}
		
		.report-table-widget tbody tr th {
			background-color: #fff;
			border-top: none;
			font-weight: 600;
			font-size: 14px;
			color: #333;
		}

		.growth-span {
			font-size: 80%;
			margin-left: 10px;
			line-height: 1.7;
		}
		
		.report-table-title {
			margin: 0;
			padding: 10px;
			font-size: 16px;
			font-weight: 600;
			color: #333;
			border: 1px solid #e0e0e0;
			background: #f5f5f5;
			border-bottom: 0;
			margin-top: 20px;
		}

		.report-summary__item-data .woocommerce-summary__item-delta-icon {
			float: right;
			vertical-align: middle;
			margin-right: 3px;
			fill: currentColor;
		}
		
		.growth-span.arrow-down {
			background: #e05b49;
		}

		.growth-span.arrow-up {
			background: #4caf50;
		}
		.growth-span {
			color: #fff;
			padding: 5px;
			float: right;
			border-radius: 3px;
			line-height: 1;
			background: #4caf50;
		}

		.asre-plugin-logo {
			margin-bottom: 10px;
			max-width: 260px;
		}

		.report-heading {
			display: inline-block;
			width: 100%;
		}
		
		.main-title .report-name {
			font-size: 20px;
			margin: 0;
			float: left;
			margin-top: 10px;
			margin-bottom: 10px;
		}
		.current-date {
			font-size: 14px;
			display: block;
			float: right;
			padding: 13px 0;
		}
		.additonal-content {
			padding-bottom: 20px;
			font-size: 14px;
			display: block;
		}
		
		a {
			color: #2196f3;
		}
		
		@media only screen and (max-width: 1149px) {
			.report-dates td {
				font-size: 15px;
			}
			.report-widget {
				width: 33.33% !important;
			}
		}

		@media only screen and (max-width: 768px) {
			.report-dates td {
				font-size: 15px;
			}
			.col-heading {
				margin: 5px 0 20px !important;
				font-size: 1em !important;
			}
			.report-widget {
				width: 50% !important;
			}
			.report-table-widget {
				width: 100% !important;
			}
			.widget-value {
				font-size: 1.8em !important;
			}
			.report-summary__item-data {
				margin-bottom: 20px !important;
			}
			.growth-span {
				font-size: 100% !important;
			}
			.growth-span img {
				width: 18px !important
			}
			.report-summary__item-prev-label,
			.report-summary__item-prev-value {
				font-size: 1em !important;
				margin-bottom: 5px !important;
			}
		}

		@media only screen and (max-width: 650px) {
			.report-widget {
				width: 50% !important;
			}
			td.current-date {
				float: left;
			}
			.current-date {
				float: left;
				line-height: 1.5;
				padding-top: 0;
				width: 100%;
			}
			td.previous-date {
				float: left !important;
			}
			p.col-heading {
				font-size: 0.8em !important;
			}
		}

		@media only screen and (max-width: 550px) {
			.sales-report-email-template {
				padding: 10px 10px 20px !important;
			}
			
			.report-widget {
				width: 100% !important;
			}
			.report-table-widget {
				width: 100% !important;
			}
			.col-heading {
				margin: 10px 0 15px !important;
				font-size: 0.7em !important;
			}
			.widget-value {
				font-size: 1.5em !important;
			}
			.report-summary__item-data {
				margin-bottom: 10px !important;
			}
			.growth-span {
				font-size: 80% !important;
			}
			.growth-span img {
				width: 15px !important
			}
			.report-summary__item-prev-label,
			.report-summary__item-prev-value {
				font-size: 1em !important;
			}
			.asre-plugin-logo {
				max-width:200px;
			}
			
			.edit-report-link, .zorem-branding-link {
				display: block;
				text-align: center;
				padding: 5px;
				float: none !important;
			}
		}
		</style>
	</head>
	<body>
		<div class="sales-report-email-template">
			<div class="main-title">
				<img src="<?php echo esc_url(apply_filters( 'asre_branding_logo_url', wc_sales_report_email()->plugin_dir_url(__FILE__) . 'assets/images/sre-logo.png', $data )); ?>" class="asre-plugin-logo" style="display: block;" alt="" >
				
				<div class="report-heading">
					<span class="report-name" style=""><?php echo esc_html($data->report_name); ?></span>
					<span class="current-date"> 
						<?php esc_html_e('Report dates', 'woocommerce-advanced-sales-report-email'); ?>: 
						<?php 
						$startday = $date_range->start_date->format('j');
						$endday = $date_range->end_date->format('j');
						$startmonth = $date_range->start_date->format('m');
						$endmonth = $date_range->end_date->format('m');
						$startyear = $date_range->start_date->format('y');
						$endyear = $date_range->end_date->format('y');
						$starttime = $date_range->start_date->format('H:i:s');
						$endtime = $date_range->end_date->format('H:i:s');
						
						if ( $startday != $endday && $startmonth == $endmonth && $startyear == $endyear && '00:00:00' == $starttime &&  '23:59:59' == $endtime  ) {
							echo '<strong>' . esc_html($date_range->start_date->format('F')) . ' ' . esc_html( $date_range->start_date->format('j')) . '-' . esc_html($date_range->end_date->format('j')) . ', ' . esc_html($date_range->end_date->format('Y')) . '</strong>';
						} else if ( $startday == $endday && $startmonth == $endmonth && $startyear == $endyear && '00:00:00' == $starttime &&  '23:59:59' == $endtime ) { 
							echo '<strong>' . esc_html($date_range->start_date->format('F')) . ' ' . esc_html( $date_range->start_date->format('j')) . ', ' . esc_html($date_range->end_date->format('Y')) . '</strong>';
						} else {
							?>
							<strong>
							<?php if ( 'daily-overnight' == $data->email_interval ) { ?>
								<?php echo esc_html($date_range->start_date->format('M j')); ?>
							<?php } else { ?>
								<?php echo esc_html($date_range->start_date->format('M j, Y')); ?>
							<?php } ?>
							<?php 
							if ( 'daily-overnight' == $data->email_interval ) {
								echo esc_html($date_range->start_date->format($time_format));
							} 
							?>
							- 
							<?php if ( 'daily-overnight' == $data->email_interval ) { ?>
								<?php echo esc_html($date_range->end_date->format('M j')); ?>
							<?php } else { ?>
								<?php echo esc_html($date_range->end_date->format('M j, Y')); ?>
							<?php } ?>
							<?php 
							if ( 'daily-overnight' == $data->email_interval ) {
								echo esc_html($date_range->end_date->format($time_format));
							} 
							?>
							</strong>
							<?php
						}
						?>
					</span>
				</div>
			</div>
			<?php if ($email_content) { ?>
				<span class="additonal-content"><?php esc_html_e($email_content); ?></span>
			<?php } ?>
			<?php 
			$i = 0;
			if ( isset($display_data->display_gross_sales) && '1' == $display_data->display_gross_sales ) {
				$i++;
			}
			if ( isset($display_data->display_total_sales) && '1' == $display_data->display_total_sales ) {
				$i++;
			}
			if ( isset($display_data->display_coupon_used) && '1' == $display_data->display_coupon_used ) {
				$i++;
			}
			if ( isset($display_data->display_total_refunds) && '1' == $display_data->display_total_refunds ) {
				$i++;
			}
			if (wc_tax_enabled()) {
				if ( isset($display_data->display_total_tax) && '1' == $display_data->display_total_tax ) {
					$i++;
				}
			}
			if ( isset($display_data->display_total_shipping) && '1' == $display_data->display_total_shipping ) {
				$i++;
			}
			if ( isset($display_data->display_net_revenue) && '1' == $display_data->display_net_revenue ) {
				$i++;
			}
			if ( isset($display_data->display_total_orders) && '1' == $display_data->display_total_orders ) {
				$i++;
			}
			if ( isset($display_data->display_total_items) && '1' == $display_data->display_total_items ) {
				$i++;
			}
			if ( isset($display_data->display_signups) && '1' == $display_data->display_signups ) {
				$i++;
			}
			
			$column4 = array( 3, 6, 9 );	
			?>
			<u class="report-summary has-6-items 
				<?php 
				if ( !in_array( $i, $column4) ) { 
					echo 'column4'; 
				} 
				?>
				" style="list-style-type:none;text-decoration: none;" >
				<?php 
				
				$report_totals_data = array(
					'display_gross_sales' => isset ($display_data->display_gross_sales) && '1' == $display_data->display_gross_sales ? array ( 'Gross Sales', wc_price($gross_sales), $data ) : array(),
					'display_total_sales' => isset ($display_data->display_total_sales) && '1' == $display_data->display_total_sales ? array ( 'Total Sales', wc_price($total_sales), $data ) : array(),
					'display_coupon_used' => isset ($display_data->display_coupon_used) && '1' == $display_data->display_coupon_used ? array ( 'Net Discount Amount', wc_price($coupon_used),  $data ) : array(),
					'display_total_refunds' => isset ($display_data->display_total_refunds) && '1' == $display_data->display_total_refunds ? array ( 'Refunds', wc_price($total_refunds),  $data ) : array(),
					'display_total_tax' => isset ($display_data->display_total_tax) && '1' == $display_data->display_total_tax && wc_tax_enabled() ? array ( 'Taxes', wc_price($total_taxes), $data ) : array(),
					'display_total_shipping' => isset ($display_data->display_total_shipping) && '1' == $display_data->display_total_shipping ? array ( 'Shipping', wc_price($total_shipping), $data ) : array(),
					'display_net_revenue' => isset ($display_data->display_net_revenue) && '1' == $display_data->display_net_revenue ? array ( 'Net Sales', wc_price($net_revenue), $data ) : array(),
					'display_total_orders' => isset ($display_data->display_total_orders) && '1' == $display_data->display_total_orders ? array ( 'Orders', $total_orders, $data ) : array(),
					'display_total_items' => isset ($display_data->display_total_items) && '1' == $display_data->display_total_items ? array ( 'Items Sold', $total_items, $data ) : array(),
					'display_signups' => isset ($display_data->display_signups) && '1' == $display_data->display_signups ? array ( 'New Customers', $total_signups, $data ) : array(),
				);
				
				$sort_report_totals = !empty($data->report_totals_sort) ? unserialize($data->report_totals_sort) : array_keys((array) $report_totals_data);
				if ( !empty($sort_report_totals)  ) {
					foreach ( $sort_report_totals as $key ) {
						if ( isset($display_data->$key) && '1' == $display_data->$key ) { 
							if (!empty($report_totals_data[$key])) {
								wc_sales_report_email()->admin->get_total_report_content( $report_totals_data[$key][0], $report_totals_data[$key][1], $display_data );
							}
						}
					}
				}
				
				?>
			</u>
			<?php 
			$report_details_data = array(
				'display_top_sellers' => isset ($display_data->display_top_sellers) && '1' == $display_data->display_top_sellers ? $top_sellers : array(),
				'display_top_categories' => isset ($display_data->display_top_categories) && '1' == $display_data->display_top_categories ? $top_categories : array(),
			);
			 
			$sort_report_details = !empty($data->report_details_sort) ? unserialize($data->report_details_sort) : array_keys((array) $report_details_data);
			if ( !empty($sort_report_details)  ) { 
				foreach ( $sort_report_details as $key ) {
					if ( isset($display_data->$key) && '1' == $display_data->$key ) { 
						if (!empty($report_details_data[$key])) { 
							wc_sales_report_email()->admin->get_details_report_content( $report_details_data[$key] );
						}
					}
				}
			}

			echo '<div style="display: flow-root;">';
			echo '<span class="zorem-branding-link" style="float: right;padding: 20px 0 0;"><a href="https://zorem.com">Powered by zorem</a></span>';	
			echo '</div>';
			?>
		</div>
	</body>
</html>
