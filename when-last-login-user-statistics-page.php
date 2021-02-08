<div class='wrap'>

	<h2><?php _e('When Last Login - User Statistics', 'when-last-login-stats'); ?></h2>
	
	<?php
		// Check to see if All Login Records are enabled.
		$wll_settings = get_option( 'wll_settings' );

		if ( empty( $wll_settings['show_all_login_records'] ) ) {
			?>
				<div class="notice notice-warning">
					<p><?php _e( sprintf( "Please enable the %s setting to enable graph data.", "<a href='" . esc_url( admin_url( 'options-general.php?page=when-last-login-settings' ) ) . "'>All Login Records</a>"), 'when-last-login-user-statistics'); ?></p>
				</div>
			<?php
		}
	?>
	<div class='wll_stats_page_container'>

		<div class='wll_stats_page_header'>

			<div class='wll_stats_form'>
				<?php
					$query_string = "";
					$query_count = 0;
					foreach( $_GET as $key => $val ){
						if( $query_count == 0 ){
							$query_string .= "?$key=$val";
						} else {
							$query_string .= "&$key=$val";
						}
						$query_count++;
					}
					$query_string .= "&wll_stats_download=pdf";
				?>

				<a href='<?php echo admin_url( 'admin.php'.$query_string ); ?>' class='wll_stats_download_button' name='wll_stats_download_pdf'><?php _e('Download PDF Report', 'when-last-login-stats'); ?></a>

				<form method='GET'>

					<input type='hidden' name='page' value='<?php echo $_GET['page']; ?>' />
					<?php $range_selected = isset( $_GET['range'] ) ? $_GET['range'] : ""; ?>
					<select name='range' id='wll_stats_date_range'>
						<option value='all_time' <?php selected( $range_selected, 'all_time' ); ?> ><?php _e('All Time', 'when-last-login-stats'); ?></option>
						<option value='today' <?php selected( $range_selected, 'today' ); ?> ><?php _e('Today', 'when-last-login-stats'); ?></option>
						<option value='-7days' <?php selected( $range_selected, '-7days' ); ?> ><?php _e('7 Days Ago', 'when-last-login-stats'); ?></option>
						<option value='-30days' <?php selected( $range_selected, '-30days' ); ?> ><?php _e('30 Days Ago', 'when-last-login-stats'); ?></option>
						<option value='custom' <?php selected( $range_selected, 'custom' ); ?> ><?php _e('Custom', 'when-last-login-stats'); ?></option>
					</select>

					<?php
						$custom_start = isset( $_GET['custom-start'] ) ? $_GET['custom-start'] : "";
						$custom_end = isset( $_GET['custom-end'] ) ? $_GET['custom-end'] : "";
					?>
					<input type='text' name='custom-start' class='wll_stats_custom_date_fields' id='wll_stats_custom_start' value='<?php echo $custom_start; ?>' placeholder='<?php _e('Start Date', 'when-last-login-stats'); ?>'/>

					<input type='text' name='custom-end' class='wll_stats_custom_date_fields' id='wll_stats_custom_end' value='<?php echo $custom_end; ?>' placeholder='<?php _e('End Date', 'when-last-login-stats'); ?>'/>

					<input type='submit' class='button button-primary' />

				</form>

			</div>

		</div>

		<div class='wll_stats_page_body'>

			<div class='wll_column_50'><h2><?php _e('Logins per agent, per day', 'when-last-login-stats'); ?></h2><div id="wll_stats_agent_name_count" style="width: 100%; height: 400px;"></div><div id='wll_stats_agent_name_count_hidden' style="width: 600px; height: 400px; visibility: hidden; position: fixed; top: -400px;"></div></div>

			<div class='wll_column_50'><h2><?php _e('Logins per hour', 'when-last-login-stats'); ?></h2><div id="wll_stats_agent_date_count" style="width: 100%; height: 400px;"></div><div id='wll_stats_agent_date_count_hidden' style="width: 600px; height: 400px; visibility: hidden; position: fixed; top: -400px;"></div></div>

			<div class='wll_column_50'><h2><?php _e('Total logins per day', 'when-last-login-stats'); ?></h2><div id="wll_stats_agent_date_count_hourly" style="width: 100%; height: 400px;"></div><div id='wll_stats_agent_date_count_hourly_hidden' style="width: 600px; height: 400px; visibility: hidden; position: fixed; top: -400px;"></div></div>

			<div class='wll_column_50'>
				<h2><?php _e('Recent Logins', 'when-last-login-stats'); ?></h2>
				<table class='form-table' style='vertical-align: top;' id='wll_stats_most_recent_login_time'>
					<thead>
						<tr>
							<th><?php _e('Agent Name', 'when-last-login-stats'); ?></th>
							<th><?php _e('Last Login Date', 'when-last-login-stats'); ?></th>
						</tr>
					</thead>
					<tbody>
						<!-- Loaded via JS -->
					</tbody>
				</table>

			</div>		

		</div>

	</div>

</div>