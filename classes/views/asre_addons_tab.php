<?php
/**
 * html code for tools tab
 */

$pro_plugins = array(	
	5 => array(
		'title' => 'Advanced Shipment Tracking',
		'description' => 'AST PRO provides powerful features to easily add tracking info to WooCommerce orders, automate the fulfillment workflows and keep your customers happy and informed. AST allows you to easily add tracking and fulfill your orders straight from the Orders page, while editing orders, and allows customers to view the tracking i from the View Order page.',
		'url' => 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=CEV&utm_campaign=add-ons',
		'image' => 'ast-icon.png',
		'height' => '45px',
		'file' => 'ast-pro/ast-pro.php'
	),	
	0 => array(
		'title' => 'TrackShip for WooCommerce',
		'description' => 'Take control of your post-shipping workflows, reduce time spent on customer service and provide a superior post-purchase experience to your customers.Beyond automatic shipment tracking, TrackShip brings a branded tracking experience into your store, integrates into your workflow, and takes care of all the touch points with your customers after shipping.',
		'url' => 'https://wordpress.org/plugins/trackship-for-woocommerce/?utm_source=wp-admin&utm_medium=ts4wc&utm_campaign=add-ons',
		'image' => 'trackship-logo.png',
		'height' => '45px',
		'file' => 'trackship-for-woocommerce/trackship-for-woocommerce.php'
	),
	1 => array(
		'title' => 'SMS for WooCommerce',
		'description' => 'Keep your customers informed by sending them automated SMS text messages with order & delivery updates. You can send SMS notifications to customers when the order status is updated or when the shipment is out for delivery and more…',
		'url' => 'https://www.zorem.com/product/sms-for-woocommerce/?utm_source=wp-admin&utm_medium=SMSWOO&utm_campaign=add-ons',
		'image' => 'smswoo-icon.png',
		'height' => '45px',
		'file' => 'sms-for-woocommerce/sms-for-woocommerce.php'
	),
	2 => array(
		'title' => 'Country Based Restrictions',
		'description' => 'The country-based restrictions plugin by zorem works by the WooCommerce Geolocation or the shipping country added by the customer and allows you to restrict products on your store to sell or not to sell to specific countries.',
		'url' => 'https://www.zorem.com/products/country-based-restriction-pro/?utm_source=wp-admin&utm_medium=CBR&utm_campaign=add-ons',
		'image' => 'cbr-icon.png',
		'height' => '45px',
		'file' => 'country-based-restriction-pro-addon/country-based-restriction-pro-addon.php'
	),		
	3 => array(
		'title' => 'Advanced Local Pickup',
		'description' => 'The Advanced Local Pickup (ALP) plugin helps you handle store pickup more conveniently by extending the WooCommerce Local Pickup shipping method and creating a local pickup fulfillment workflow, allows you to set up multiple pickup locations, pickup per item, products availability per location, force local pickup, pickup appointments, and more!',
		'url' => 'https://www.zorem.com/product/advanced-local-pickup-pro/?utm_source=wp-admin&utm_medium=ALPPRO&utm_campaign=add-ons',
		'image' => 'alp-icon.png',
		'height' => '45px',
		'file' => 'advanced-local-pickup-pro/advanced-local-pickup-pro.php'
	),
	4 => array(
		'title' => 'Order Status Manager',
		'description' => 'The Advanced Order Status Manager allows store owners to manage the WooCommerce orders statuses, create, edit, and delete custom Custom Order Statuses and integrate them into the WooCommerce orders flow.',
		'url' => 'https://www.zorem.com/products/advanced-order-status-manager/?utm_source=wp-admin&utm_medium=OSM&utm_campaign=add-ons',
		'image' => 'osm-icon.png',
		'height' => '45px',
		'file' => 'advanced-order-status-manager/advanced-order-status-manager.php'
	),	
);
?>
<section id="asre_content4" class="aosm_tab_section">
	<div class="d_table addons_page_dtable">
		<div class="addon_inner_section">
			<div class="row">
				<div class="col asre-features-list asre-btn">
					<h1 class="plugin_title"><?php echo wp_kses_post('Sales Report Email PRO'); ?></h1>
					<ul>
						<li>One Year of Updates & Support</li>
						<li>Schedule multiple email reports</li>
						<li>Schedule partial daily report by hours and overnight</li>
						<li>Additional Report Totals – Average order value, Average Daily Sales, Average Items per order</li>
						<li>Additional Report Details – Sales By Coupons, Orders By Status, Orders By Payment Method</li>
						<li>Subscriptions Totals – Active Subscriptions, Subscriptions signups revenue, Subscription Switch, Resubscribe, Cancellation...</li>
						<li>Subscriptions details – Subscriptions by status (total), Subscriptions by status</li>
					</ul>
					<a href="https://www.zorem.com/product/sales-report-email-pro/?utm_source=wp-admin&utm_medium=SRE&utm_campaign=add-ons" class="install-now button-primary pro-btn" target="blank">Upgrade To PRO ></a>	
				</div>
				<div class="col asre-pro-image">
					<img src="<?php echo esc_url(wc_sales_report_email()->plugin_dir_url() . 'assets/images/addon-banner.jpg'); ?>" width="100%">
				</div>
			</div>
		</div>
		<div class="plugins_section free_plugin_section">
			<?php foreach($pro_plugins as $Plugin){ ?>
				<div class="single_plugin">
					<div class="free_plugin_inner">
						<div class="plugin_image">
							<img src="<?php echo esc_url(wc_sales_report_email()->plugin_dir_url() . 'assets/images/' . $Plugin['image']); ?>" height="<?php echo esc_html($Plugin['height']); ?>">
							<h3 class="plugin_title"><?php echo esc_html($Plugin['title']); ?></h3>
						</div>
						<div class="plugin_description asre-btn">
							<p><?php echo esc_html($Plugin['description']); ?></p>
							<?php if ( is_plugin_active( $Plugin['file'] ) ) { ?>
								<button type="button" class="button button-disabled" disabled="disabled">Installed</button>
							<?php } else { ?>
								<a href="<?php echo esc_url($Plugin['url']); ?>" class="button install-now button-primary" target="blank">more info</a>
							<?php } ?>								
						</div>
					</div>		
				</div>	
			<?php } ?>
		</div>				
	</div>
</section>
