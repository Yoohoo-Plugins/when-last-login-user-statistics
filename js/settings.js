jQuery(document).ready(function(){

	console.log('Loading');

	jQuery("#wll_stats_custom_start").datepicker();
	jQuery("#wll_stats_custom_end").datepicker();

	jQuery("#wll_stats_date_range").on("change", function(){

		var range = jQuery(this).val();

		if( range == 'custom' ){
			jQuery('.wll_stats_custom_date_fields').show();
		} else {
			jQuery('.wll_stats_custom_date_fields').hide();
		}

	});

	var wll_date_range = jQuery("#wll_stats_date_range").val();

	if( wll_date_range == 'custom' ){
		jQuery('.wll_stats_custom_date_fields').show();
	} else {
		jQuery('.wll_stats_custom_date_fields').hide();
	}
	

	var current_freq = jQuery("#wll_stats_automatically_email_frequency").val();

	if( current_freq == 'never' ){

		jQuery('.wll_stats_freq_select').hide();
		jQuery('.wll_stats_month_day_cont').hide();
		jQuery('.wll_stats_time_selects').hide();

	} else if( current_freq == 'daily' ){

		jQuery('.wll_stats_freq_select').hide();
		jQuery('.wll_stats_month_day_cont').hide();
		jQuery('.wll_stats_time_selects').show();

	} else if( current_freq == 'weekly' ){

		jQuery('.wll_stats_freq_select').show();
		jQuery('.wll_stats_month_day_cont').hide();
		jQuery('.wll_stats_time_selects').show();

	} else if( current_freq == 'monthly' ){

		jQuery('.wll_stats_freq_select').hide();
		jQuery('.wll_stats_month_day_cont').show();
		jQuery('.wll_stats_time_selects').show();

	}

	jQuery("#wll_stats_automatically_email_frequency").on("change", function(){

		var selected_freq = jQuery(this).val();

		console.log(selected_freq);

		if( selected_freq == 'never' ){

			jQuery('.wll_stats_freq_select').hide();
			jQuery('.wll_stats_month_day_cont').hide();
			jQuery('.wll_stats_time_selects').hide();

		} else if( selected_freq == 'daily' ){

			jQuery('.wll_stats_freq_select').hide();
			jQuery('.wll_stats_month_day_cont').hide();
			jQuery('.wll_stats_time_selects').show();

		} else if( selected_freq == 'weekly' ){

			jQuery('.wll_stats_freq_select').show();
			jQuery('.wll_stats_month_day_cont').hide();
			jQuery('.wll_stats_time_selects').show();

		} else if( selected_freq == 'monthly' ){

			jQuery('.wll_stats_freq_select').hide();
			jQuery('.wll_stats_month_day_cont').show();
			jQuery('.wll_stats_time_selects').show();

		}
		
	});

});