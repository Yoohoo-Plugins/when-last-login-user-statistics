<tr>
	<td colspan="2"><h2><?php _e('When Last Login - User Statistics Settings', 'when-last-login-stats'); ?></h2><small><?php _e('Please note that scheduled reports only contain tabulated data as graphs cannot be rendered out of the browser when being generated', 'when-last-login-stats'); ?></small></td>
</tr>
<?php 

	$wll_stats_settings = get_option( 'wll_stats_settings' ); 

	$settings_frequency = isset( $wll_stats_settings['frequency'] ) ? sanitize_text_field ( $wll_stats_settings['frequency'] ) : "";
	$frequency_days = isset( $wll_stats_settings['frequency_days'] ) ? sanitize_text_field ( $wll_stats_settings['frequency_days'] ) : "";
	$frequency_day_num = isset( $wll_stats_settings['frequency_day_num'] ) ? sanitize_text_field ( $wll_stats_settings['frequency_day_num'] ) : "";
	$frequency_h = isset( $wll_stats_settings['frequency_h'] ) ? sanitize_text_field ( $wll_stats_settings['frequency_h'] ) : "";
	$frequency_m = isset( $wll_stats_settings['frequency_m'] ) ? sanitize_text_field ( $wll_stats_settings['frequency_m'] ) : "";

	$recipient_address = isset( $wll_stats_settings['recipient_address'] ) ? sanitize_text_field ( $wll_stats_settings['recipient_address'] ) : "";

	$logo_url = isset( $wll_stats_settings['logo_url'] ) ? sanitize_text_field ( $wll_stats_settings['logo_url'] ) : "";

	$range_selected = isset( $wll_stats_settings['range_selected'] ) ? sanitize_text_field( $wll_stats_settings['range_selected'] ) : "";
	$custom_start = isset( $wll_stats_settings['custom_start'] ) ? sanitize_text_field( $wll_stats_settings['custom_start'] ) : "";
	$custom_end = isset( $wll_stats_settings['custom_end'] ) ? sanitize_text_field( $wll_stats_settings['custom_end'] ) : "";

?>
<tr>
	<th><?php _e('Automatically email a user report', 'when-last-login-stats'); ?></th>
	<td>
		<select name='wll_stats_automatically_email_frequency' id='wll_stats_automatically_email_frequency'>
			<?php

				$frequency = array(
					'never' => __('Never', 'when-last-login-stats'),
					'daily' => __('Daily', 'when-last-login-stats'),
					'weekly' => __('Weekly', 'when-last-login-stats'),
					'monthly' => __('Monthly', 'when-last-login-stats')
				);

				$frequency = apply_filters( 'wll_stats_frequency_dropdown', $frequency );

				foreach( $frequency as $key => $val ){
					echo "<option value='$key' ".selected( $key, $settings_frequency, false ).">$val</option>";
				}

				$time_array = WhenLastLoginStatistics::wll_stats_time_array();

			?>
		</select>
		<span class='wll_stats_freq_select'>
			<?php _e('on', 'when-last-login'); ?>
			<select name='wll_stats_frequency_days' id='wll_stats_frequency_days'>
				<option value='0' <?php selected( $frequency_days, '0' ); ?> ><?php _e('Sunday', 'when-last-login-stats'); ?></option>
				<option value='1' <?php selected( $frequency_days, '1' ); ?> ><?php _e('Monday', 'when-last-login-stats'); ?></option>
				<option value='2' <?php selected( $frequency_days, '2' ); ?> ><?php _e('Tuesday', 'when-last-login-stats'); ?></option>
				<option value='3' <?php selected( $frequency_days, '3' ); ?> ><?php _e('Wednesday', 'when-last-login-stats'); ?></option>
				<option value='4' <?php selected( $frequency_days, '4' ); ?> ><?php _e('Thursday', 'when-last-login-stats'); ?></option>
				<option value='5' <?php selected( $frequency_days, '5' ); ?> ><?php _e('Friday', 'when-last-login-stats'); ?></option>
				<option value='6' <?php selected( $frequency_days, '6' ); ?> ><?php _e('Saturday', 'when-last-login-stats'); ?></option>
			</select>
		</span>
		<span class='wll_stats_month_day_cont'>
			<?php _e('on the', 'when-last-login-stats'); ?>
			<input type='number' min='1' max='31' name='wll_stats_month_day' value='<?php echo $frequency_day_num; ?>' >
			<?php _e('day of the month', 'when-last-login-stats'); ?>
		</span>
		<span class='wll_stats_time_selects'>
			<?php _e('at', 'when-last-login-stats'); ?>
			<select name='wll_stats_time_to_send_h'>
				<?php
					foreach( $time_array['hours'] as $h ){
						echo "<option value='".$h."' ".selected( $frequency_h, $h, false ).">".$h."</option>";
					}
				?>
			</select>

			<select name='wll_stats_time_to_send_m'>
				<?php
					foreach( $time_array['minutes'] as $m ){
						echo "<option value='".$m."' ".selected( $frequency_m, $m, false ).">".$m."</option>";
					}
				?>
			</select>
		</span>
	</td>
</tr>

<tr>

	<th><?php _e('User Statistics time period', 'when-last-login-stats'); ?></th>
	<td>
		<select id='wll_stats_date_range' name='wll_stats_timeslot_range'>
			<option value='all_time' <?php selected( $range_selected, 'all_time' ); ?> ><?php _e('All Time', 'when-last-login-stats'); ?></option>
			<option value='today' <?php selected( $range_selected, 'today' ); ?> ><?php _e('Today', 'when-last-login-stats'); ?></option>
			<option value='-7days' <?php selected( $range_selected, '-7days' ); ?> ><?php _e('7 Days Ago', 'when-last-login-stats'); ?></option>
			<option value='-30days' <?php selected( $range_selected, '-30days' ); ?> ><?php _e('30 Days Ago', 'when-last-login-stats'); ?></option>
			<option value='custom' <?php selected( $range_selected, 'custom' ); ?> ><?php _e('Custom', 'when-last-login-stats'); ?></option>
		</select>

		<input type='text' class='wll_stats_custom_date_fields' name='wll_stats_custom_start' id='wll_stats_custom_start' value='<?php echo $custom_start; ?>' placeholder='<?php _e('Start Date', 'when-last-login-stats'); ?>'/>

		<input type='text' class='wll_stats_custom_date_fields' name='wll_stats_custom_end' id='wll_stats_custom_end' value='<?php echo $custom_end; ?>' placeholder='<?php _e('End Date', 'when-last-login-stats'); ?>'/>
	</td>

</tr>

<tr>
	<th><?php _e('Recipient Email Address', 'when-last-login-stats'); ?></th>
	<td><input style='width: 250px' type='text' name='wll_stats_recipient_email_addresses' value='<?php echo $recipient_address; ?>' /><span class='description'><p><?php _e('Comma separate email addresses for multiple recipients', 'when-last-login-stats'); ?></p></span></td>
</tr>

<!-- <tr>
	<th><?php _e('Report Logo URL', 'when-last-login-stats'); ?></th>
	<td><input style='width: 250px' type='text' name='wll_stats_report_logo_url' value='<?php echo $logo_url; ?>' /><button class='button' id='wll_stats_report_logo_media_button'><?php _e('Upload', 'when-last-login-stats'); ?></button></td>
</tr> -->

<tr>
	<td colspan="2"><hr/></td>
</tr>

<tr>
	<td colspan="2"><h2><?php _e('REST API', 'when-last-login-stats'); ?></h2></td>
</tr>

<tr>
	<th><?php _e('Secret Token', 'when-last-login-stats'); ?></th>
	<td><input style='width: 300px' type='text' readonly='true' value='<?php echo get_option( 'wll_stats_secret_token' ); ?>' /></td>
</tr>

<tr>
	<th><?php _e('Return Statistics', 'when-last-login-stats'); ?></th>
	<td><input style='width: 300px' type='text' readonly='true' value='when_last_login_user_stats/v1/return_stats'/></td>
</tr>
<tr>
	<th></th>
	<td>
		<table>
			<tr>
				<td><?php _e('Accepts:', 'when-last-login-stats'); ?></td>
				<td>GET | POST</td>
			</tr>
			<tr>
				<td><?php _e('Optional Arguments', 'when-last-login-stats'); ?></td>
				<td><code>range</code></td>
				<td>all_time | today | -7days | -30days | custom</td>
			</tr>
			<tr>
				<td></td>
				<td><code>start_date</code></td>
				<td><?php _e('A PHP formatted start date', 'when-last-login-stats'); ?><small class='description'><p><?php echo sprintf( __('Requires the %s to be set to %s', 'when-last-login-stats'), '<i>range</i>', '<i>custom</i>' ); ?></p></small></td>
			</tr>
			<tr>
				<td></td>
				<td><code>end_date</code></td>
				<td><?php _e('A PHP formatted end date', 'when-last-login-stats'); ?><small class='description'><p><?php echo sprintf( __('Requires the %s to be set to %s', 'when-last-login-stats'), '<i>range</i>', '<i>custom</i>' ); ?></p></small></td>
			</tr>
		</table>
	</td>
</tr>

<tr>
    <th><input type="submit" name="wll_save_stats_settings"  class="button-primary" value="<?php _e('Save Settings', 'when-last-login'); ?>" /></th>
    <td></td>
</tr>