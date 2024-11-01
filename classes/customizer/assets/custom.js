jQuery(document).ready(function($){
	var send_time = jQuery("#email_send_time").val();
	var email_interval = jQuery("#email_interval").val();

    jQuery(".interval_desc").hide();
    jQuery(".email_select_week, .email_select_month").addClass('hide');
	if( email_interval == 'daily' ){
		jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent daily at "+send_time+" and will show results for the previous day from 00:00  to 24:00.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}
	if( email_interval == 'weekly' ){
		jQuery(".email_select_week").removeClass('hide');
		jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent Weekly on Monday at "+send_time+" and will show results for the previous 7 days.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}
	if( email_interval == 'monthly' ){
		jQuery(".email_select_month").removeClass('hide');
		jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent Monthly on the 1st (5th, 10th, etc) of the month at "+send_time+" and will show results for the previous month.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}	
});

jQuery(document).on("change", "#email_interval", function($){
	var send_time = jQuery("#email_send_time").val();
	var email_interval = jQuery("#email_interval").val();

    jQuery(".interval_desc").hide();
    jQuery(".email_select_week, .email_select_month").addClass('hide');
	if( email_interval == 'daily' ){
		//jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent daily at "+send_time+" and will show results for the previous day from 00:00  to 24:00.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}
	if( email_interval == 'weekly' ){
		jQuery(".email_select_week").removeClass('hide');
		//jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent Weekly on Monday at "+send_time+" and will show results for the previous 7 days.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}
	if( email_interval == 'monthly' ){
		jQuery(".email_select_month").removeClass('hide');
		//jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent Monthly on the 1st (5th, 10th, etc) of the month at "+send_time+" and will show results for the previous month.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}	
});

jQuery(document).on("change", "#email_send_time", function(){
	var send_time = jQuery("#email_send_time").val();
	var email_interval = jQuery("#email_interval").val();

    jQuery(".interval_desc").hide();
    jQuery(".email_select_week, .email_select_month").addClass('hide');
	if( email_interval == 'daily' ){
		//jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent daily at "+send_time+" and will show results for the previous day from 00:00  to 24:00.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}
	if( email_interval == 'weekly' ){
		jQuery(".email_select_week").removeClass('hide');
		//jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent Weekly on Monday at "+send_time+" and will show results for the previous 7 days.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}
	if( email_interval == 'monthly' ){
		jQuery(".email_select_month").removeClass('hide');
		//jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent Monthly on the 1st (5th, 10th, etc) of the month at "+send_time+" and will show results for the previous month.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}	
});

jQuery(document).on("keyup", ".heading .zoremmail-input", function(event){
	if(event.target.value){
		var str = event.target.value;
	} else {
		var str = event.target.placeholder;
	}
	
	var res = str.replace("{site_title}", sre_customizer.site_title);
	var res = res.replace("{order_number}", sre_customizer.order_number);
	var res = res.replace("{customer_first_name}", sre_customizer.customer_first_name);
	var res = res.replace("{customer_last_name}", sre_customizer.customer_last_name);
	var res = res.replace("{customer_company_name}", sre_customizer.customer_company_name);
	var res = res.replace("{customer_username}", sre_customizer.customer_username);
	var res = res.replace("{customer_email}", sre_customizer.customer_email);
	var res = res.replace("{est_delivery_date}", sre_customizer.est_delivery_date);
	
	if( str ){	
		jQuery("#content-preview-iframe").contents().find( '.report-name ' ).text(res);
	} else{
		jQuery("#content-preview-iframe").contents().find( '.report-name' ).text(event.target.placeholder);
	}
});

jQuery(document).on("keyup", ".additional_content .zoremmail-input", function(event){
	if(event.target.value){
		var str = event.target.value;
	} else {
		var str = event.target.placeholder;
	}
	
	var res = str.replace("{site_title}", sre_customizer.site_title);
	var res = res.replace("{order_number}", sre_customizer.order_number);
	var res = res.replace("{customer_first_name}", sre_customizer.customer_first_name);
	var res = res.replace("{customer_last_name}", sre_customizer.customer_last_name);
	var res = res.replace("{customer_company_name}", sre_customizer.customer_company_name);
	var res = res.replace("{customer_username}", sre_customizer.customer_username);
	var res = res.replace("{customer_email}", sre_customizer.customer_email);
	var res = res.replace("{est_delivery_date}", sre_customizer.est_delivery_date);
	
	jQuery("#content-preview-iframe").contents().find( '.main-title ~ .additonal-content' ).remove();
	if( str ){				
		jQuery("#content-preview-iframe").contents().find( '.main-title' ).after('<span class="additonal-content">'+res+'</span>');
	}
});