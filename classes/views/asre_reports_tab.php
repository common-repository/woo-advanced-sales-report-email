<section id="asre_content1" class="asre_tab_section <?php if ( 'list' !== $tab ) { echo 'asre_report_form_section'; } ?>">
	<div class="asre_tab_inner_container">
		<?php if ( 'list' === $tab ) : ?>
		<?php
		//wc_sales_report_email()->admin->update_email_enable_options_callback();
		$data = wc_sales_report_email()->admin->get_data();
			?>
			<div id="the_list" class="widefat">
				<?php if ( empty( $data ) ) : ?>
					<div id="report-row">
							<span colspan="4"><?php esc_html_e( 'No Reports', 'sales-report-email-pro' ); ?></span>
						</div>
				<?php else : ?>	
				<?php foreach ( array_slice($data, 0, 1) as $w_data ) : ?>
				<div id="report-row" class="<?php echo esc_html($w_data->email_enable) ? 'active' : 'inactive'; ?>" value="<?php echo esc_html($w_data->id); ?>">
					<?php 
					if ( $w_data->email_enable ) {
						$checked = 'checked';
					} else {
						$checked = '';
					}
					?>
					<span class="report tgl-btn-parent" style="padding-right: 5px;">
						<input type="hidden" name="email_enable" value="0">
						<input type="checkbox" id="email_enable_<?php echo esc_html($w_data->id); ?>" name="email_enable" data-id="<?php echo esc_html($w_data->id); ?>" class="tgl tgl-flat-sre enable_status_list" <?php echo esc_html($checked); ?> value="<?php echo esc_html($w_data->email_enable); ?>"/>
						<label class="tgl-btn" for="email_enable_<?php echo esc_html($w_data->id); ?>"></label>
					</span>
					<span class="report-title" style="padding-right: 5px;">
						<strong style="margin:0">
							<a class="row-title" href="admin.php?page=sre_customizer&id=<?php echo esc_html($w_data->id); ?>">
								<?php echo esc_html(stripslashes( $w_data->report_name )); ?>
							</a>
						</strong>
						<span class="report-next-run-date">
						<?php
						if ( '1' == $w_data->email_enable ) { 
							echo '(';
							esc_html_e( 'Next Run Date', 'sales-report-email-pro' );
							echo ' - ';
							esc_html_e( $this->next_run_date($w_data), 'sales-report-email-pro' );
							echo ')';
						}
						?>
						</span>
					</span>
					<span class="report-action">
							<a href="admin.php?page=sre_customizer&id=<?php echo esc_html($w_data->id); ?>" class="edit">
								<span class="dashicons dashicons-admin-generic"></span><?php //esc_html_e( 'Edit', 'sales-report-email-pro' ); ?></a>
							<a onclick="return confirm( 'Are you sure you want to delete this entry?' );" href="admin.php?page=<?php echo esc_html($this->screen_id); ?>&amp;action=delete&amp;id=<?php echo esc_html($w_data->id); ?>" class="trash">
								<span class="dashicons dashicons-trash"></span><?php //esc_html_e( 'Delete', 'sales-report-email-pro' ); ?>
							</a>
					</span>
				</div>
				<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="report-tab asre-edit asre-btn">
				<a <?php if ( ( count($data) >= 1 )) { ?>
				onclick="return confirm( 'You need to purchase of Sales Report Email PRO Add-on plugin' );" 
				<?php } ?>
				<?php if ( ( count($data) == '0' ) ) { ?>
				href="admin.php?page=sre_customizer&id=0"
				<?php } ?> class="button-primary create_new_report <?php echo ( 'edit' === $tab ) ? 'nav-tab-active' : ''; ?>">
					  <?php esc_html_e( 'Add Report', 'woocommerce-advanced-sales-report-email' ); ?> +
				</a>
			</div>
		<br/>
		<?php endif; ?>
	</div>
</section>
<?php if ('edit' == $tab) { ?>
<dialog class="mdl-dialog" style="width: 999px;height: 600px;">
	<div class="mdl-dialog__content" style="padding: 0 !important;">
		<iframe id="iframe" style="width: 100%;height: 585px;" src="<?php echo esc_url(admin_url('admin-ajax.php')); ?>?action=preview_asre_page&amp;id=<?php echo esc_html($data->id); ?>"></iframe>
	</div>
	<div class="mdl-dialog__actions mdl-dialog__actions--full-width">
		<span style="font-size:35px;position: absolute;top: 0;right: 0px;"class="dashicons dashicons-dismiss close"></span>
	</div>
</dialog>
<div id="" class="popupwrapper sre-features-popup" style="display:none;">
	<div class="popuprow" style="width: 500px;border: 1px solid #e0e0e0;padding: 20px;border-radius: 3px;">
		<div class="col asre-features-list asre-btn">
			<h1 class="plugin_title"><?php echo wp_kses_post('Upgrade to PRO!'); ?></h1>
			<ul>
				<li>One Year of Updates & Support</li>
				<li>Schedule multiple email reports</li>
				<li>Schedule partial daily report by hours and overnight</li>
				<li>Additional Report Totals – Average order value, Average Daily Sales, Average Items per order</li>
				<li>Additional Report Details – Sales By Coupons, Orders By Status, Orders By Payment Method</li>
				<li>Subscriptions Totals – Active Subscriptions, Subscriptions signups revenue, Subscription Switch, Resubscribe, Cancellation...</li>
				<li>Subscriptions details – Subscriptions by status (total), Subscriptions by status</li>
			</ul>
			<a href="https://www.zorem.com/product/sales-report-email-for-woocommerce/?utm_source=wp-admin&utm_medium=SRE&utm_campaign=add-ons" class="install-now button-primary pro-btn" target="blank" style="width: 100%;text-align: center;">UPGRADE TO PRO ></a>	
		</div>
	</div>
	<div class="popupclose"></div>
</div>
<script>
	var dialog = document.querySelector('dialog');
	var showModalButton = document.querySelector('.cbr-show-modal');
	if (! dialog.showModal) {
	  dialogPolyfill.registerDialog(dialog);
	}
	showModalButton.addEventListener('click', function() {
	  document.querySelector(".asre-sales-report-email-setting").style.overflowY  = "hidden";
	  dialog.showModal();
	  setInterval(document.getElementById('iframe').contentWindow.location.reload(), 1500);
	});
	dialog.querySelector('.close').addEventListener('click', function() {
	  document.querySelector(".asre-sales-report-email-setting").style.overflowY  = "unset";
	  dialog.close();
	});
</script>
<?php } ?>
