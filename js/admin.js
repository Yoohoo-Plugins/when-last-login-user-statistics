jQuery(document).ready(function(){

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


	google.charts.load('current', {'packages':['bar', 'corechart']});


	/**
	 * Total logins per day per agent
	 */
	google.charts.setOnLoadCallback(wll_stats_chart_by_name_count);

	var wll_stats_name_count = new Array();
	var wll_stats_agents = new Array();
	var wll_stats_final_array = new Array();

	wll_stats_final_array.push(['Date']);

	var count = 0;

	jQuery.each( wll_stats_users_grouped_by_name_count, function( date, agent_obj ){

		var per_agent_count = new Array();
		
		per_agent_count.push(date);

		jQuery.each(agent_obj, function(key, val){

			var object_size = jQuery.map(agent_obj, function(n, i) { return i; }).length;

			if( count < object_size ){
				wll_stats_final_array[0].push(key);
			}
			count++;

			per_agent_count.push(val[key]);

		});

		wll_stats_final_array.push( per_agent_count );
		
	});

	function wll_stats_chart_by_name_count() {
		var data = google.visualization.arrayToDataTable(wll_stats_final_array);

		var options = {
			chart: {
			}
		};

		var hidden_chart = document.getElementById('wll_stats_agent_name_count_hidden');

		var hidden_my_chart = new google.visualization.ColumnChart(hidden_chart);

		google.visualization.events.addListener(hidden_my_chart, 'ready', function () {
	      	
	      	hidden_chart.innerHTML = '<img src="' + hidden_my_chart.getImageURI() + '">';

			var image_url = wll_stats_convert_base64_image( hidden_my_chart.getImageURI(), 'when-last-login-total-agent-per-day' );

	    });

	    hidden_my_chart.draw(data, google.charts.Bar.convertOptions(options));
		
		var chart = document.getElementById('wll_stats_agent_name_count');
		
		var my_chart = new google.visualization.ColumnChart(chart);

		my_chart.draw(data, google.charts.Bar.convertOptions(options));		

	}
	
	/**
	 * Per agent, hourly
	 */
	google.charts.setOnLoadCallback(wll_stats_chart_by_name_date_count);

	var wll_stats_name_date_count = new Array();
	var wll_stats_agent_array = new Array();
	var wll_stats_agent_date_array = new Array();
	var new_agent_array = new Array();
	var wll_stats_count_hourly = new Array();

	wll_stats_count_hourly.push(['Date', 'Count']);

	jQuery.each( wll_stats_users_grouped_by_name_time_hourly, function( key, val ){
		wll_stats_count_hourly.push( [ key, parseInt( val.length ) ] );
	});

	function wll_stats_chart_by_name_date_count() {
		var data = google.visualization.arrayToDataTable(wll_stats_count_hourly);

		var options = {
			chart: {
			}
		};

		var hidden_chart = document.getElementById('wll_stats_agent_date_count_hidden');

		var hidden_my_chart = new google.visualization.ColumnChart(hidden_chart);

		google.visualization.events.addListener(hidden_my_chart, 'ready', function () {
      		
      		hidden_chart.innerHTML = '<img src="' + hidden_my_chart.getImageURI() + '">';

      		var image_url = wll_stats_convert_base64_image( hidden_my_chart.getImageURI(), 'when-last-login-total-agent-hourly' );

	    });

	    hidden_my_chart.draw(data, google.charts.Bar.convertOptions(options));	

		var chart = document.getElementById('wll_stats_agent_date_count');
		
		var my_chart = new google.visualization.ColumnChart(chart);

		my_chart.draw(data, google.charts.Bar.convertOptions(options));

	}
	

	/**
	 * Total logins per day
	 */
	
	google.charts.setOnLoadCallback(wll_stats_chart_by_name_count_hourly);

	var wll_stats_name_count_hourly = new Array();

	wll_stats_name_count_hourly.push(['Date', 'Count']);
	
	jQuery.each( wll_stats_users_grouped_by_name_time, function( key, val ){
		jQuery.each( val, function( a, b ){
			wll_stats_name_count_hourly.push( [ key, parseInt( b.length ) ] );
		});
		
	});

	function wll_stats_chart_by_name_count_hourly() {
		var data = google.visualization.arrayToDataTable(wll_stats_name_count_hourly);

		var options = {
		  chart: {
		  }
		};

		var hidden_chart = document.getElementById('wll_stats_agent_date_count_hourly_hidden');

		var hidden_my_chart = new google.visualization.ColumnChart(hidden_chart);

		google.visualization.events.addListener(hidden_my_chart, 'ready', function () {
      		
      		hidden_chart.innerHTML = '<img src="' + hidden_my_chart.getImageURI() + '">';

  			var image_url = wll_stats_convert_base64_image( hidden_my_chart.getImageURI(), 'when-last-login-total-logins-per-day' );

	    });

	    hidden_my_chart.draw(data, google.charts.Bar.convertOptions(options));	

		var chart = document.getElementById('wll_stats_agent_date_count_hourly');
		
		var my_chart = new google.visualization.ColumnChart(chart);

		my_chart.draw(data, google.charts.Bar.convertOptions(options));
	}

	jQuery.each( wll_stats_users_grouped_by_name_last_logged, function( agent, date ){
		jQuery("#wll_stats_most_recent_login_time tbody").append("<tr><td>"+agent+"</td><td>"+date+"</td></tr>");
	});

	jQuery(document).ready(function(){
	    jQuery('#wll_stats_most_recent_login_time').DataTable();
	})

	function wll_stats_convert_base64_image( base64, graph ){

		var data = {
			action: 'wll_stats_convert_to_image',
			base64: base64,
			graph: graph
		}

		jQuery.post( ajaxurl, data, function( response ){
			return response;
		})

	}
});