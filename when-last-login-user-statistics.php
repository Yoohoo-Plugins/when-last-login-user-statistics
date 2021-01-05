<?php
/**
 * Plugin Name: When Last Login - User Statistics
 * Plugin URI: https://yoohooplugins.com/plugins/when-last-login-user-statistics/
 * Description: Displays user statistics based on user's login activity
 * Version: 1.0
 * Author: Yoohoo Plugins
 * Author URI: https://yoohooplugins.com
 * Text Domain: when-last-login-stats
 * Domain Path: /languages
 */

class WhenLastLoginStatistics{

	public function __construct(){

		add_action( 'rest_api_init', array( $this, 'wll_stats_rest_routes' ) );
		add_filter( 'wll_settings_page_tabs', array( $this, 'wll_stats_tabs' ) );
		add_filter( 'wll_settings_page_content', array( $this, 'wll_stats_content' ) );
		add_action( 'wll_settings_admin_menu_item', array( $this, 'wll_stats_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wll_stats_enqueue_admin_scripts' ) );

		add_action( 'admin_head', array( $this, 'wll_stats_save_settings' ) );

		add_action( 'init', array( $this, 'wll_stats_download_pdf' ) );

		register_activation_hook( __FILE__, array( $this, 'wll_stats_register_activation' ) );
		add_action( 'wll_stats_cron_job_hook', array( $this, 'wll_stats_cron_job' ) );
		register_deactivation_hook( __FILE__, array( $this, 'wll_stats_register_deactivation' ) );

		add_filter( 'cron_schedules', array( $this, 'wll_stats_modify_cron_recurrence' ) );

		add_action( 'admin_notices', array( $this, 'wll_stats_admin_notices' ) );

		add_action( 'wp_ajax_wll_stats_convert_to_image', array( $this, 'wll_convert_base64_to_image_file' ) );

		add_action( 'admin_head', array( $this, 'wll_stats_cron_job' ) );
	}

	public function wll_stats_register_activation(){

		if (! wp_next_scheduled ( 'wll_stats_cron_job_hook' )) {

			wp_schedule_event( time(), 'wll_every_ten_minutes', 'wll_stats_cron_job_hook' );

	    }

	    $wll_stats_upload_dir = wp_upload_dir();

	    $wll_stats_upload_folder = $wll_stats_upload_dir['basedir'].'/when-last-login-user-statistics';

	    if( !file_exists( $wll_stats_upload_folder ) ){

	    	if( is_writable( $wll_stats_upload_dir['basedir'] ) ){

	    		wp_mkdir_p( $wll_stats_upload_folder );

	    	} else {

	    		update_option( 'wll_stats_file_dir_not_writeable', 1 );

	    	}

	    }

	    $wll_stats_secret_token = get_option( 'wll_stats_secret_token' );

	    if( $wll_stats_secret_token == '' || !$wll_stats_secret_token ){

	    	update_option( 'wll_stats_secret_token', md5( time().'when-last-login-user-statistics' ) );

	    }


	}

	public function wll_stats_register_deactivation(){

		wp_clear_scheduled_hook('wll_stats_cron_job_hook');

	}

	public function wll_stats_cron_job(){

		$must_send = false;

		$wll_stats_settings = get_option( 'wll_stats_settings' );

 		$settings_frequency = isset( $wll_stats_settings['frequency'] ) ? sanitize_text_field ( $wll_stats_settings['frequency'] ) : "";
		$frequency_days = isset( $wll_stats_settings['frequency_days'] ) ? sanitize_text_field ( $wll_stats_settings['frequency_days'] ) : "";
		$frequency_day_num = isset( $wll_stats_settings['frequency_day_num'] ) ? sanitize_text_field ( $wll_stats_settings['frequency_day_num'] ) : "";
		$frequency_h = isset( $wll_stats_settings['frequency_h'] ) ? sanitize_text_field ( $wll_stats_settings['frequency_h'] ) : "";
		$frequency_m = isset( $wll_stats_settings['frequency_m'] ) ? sanitize_text_field ( $wll_stats_settings['frequency_m'] ) : "";

	 	$current_time = current_time( 'timestamp' );

	 	$ret = "";

	 	if( $settings_frequency == 'never' ){
	 		return;
	 	}
	 	if( $settings_frequency == 'daily' ){

	 		$new_string_time = strtotime( current_time( 'Y-m-d ' ).$frequency_h.":".$frequency_m.":00" );

	 		if( $current_time >= $new_string_time ){
	 			$must_send = true;
	 		}

	 	} else if( $settings_frequency == 'weekly' ){

	 		$current_day = date( 'w' );

	 		if( $frequency_days == $current_day ){

 				$new_string_time = strtotime( current_time( 'Y-m-d ' ).$frequency_h.":".$frequency_m.":00" );

		 		if( $current_time >= $new_string_time ){
		 			$must_send = true;
		 		}

	 		}

	 	} else if( $settings_frequency == 'monthly' ){

	 		$current_day = date( 'j' );

	 		if( $frequency_day_num == $current_day ){

 				$new_string_time = strtotime( current_time( 'Y-m-d ' ).$frequency_h.":".$frequency_m.":00" );

		 		if( $current_time >= $new_string_time ){
		 			$must_send = true;
		 		}

	 		}

	 	}

	 	$mod_current_time = strtotime( current_time( 'Y-m-d ' ).$frequency_h.":".$frequency_m.":00" );

	 	$last_sent_date = get_option( 'wll_stats_last_sent_date' );

	 	if( is_array( $last_sent_date ) ){

	 		if( isset( $last_sent_date['date'] ) ){

	 			if( $last_sent_date['date'] === current_time( 'Y-m-d' ) ){
	 				/**
	 				 * Its the current day, lets check if it's been sent?
	 				 */

	 				if( isset( $last_sent_date['sent_today'] ) ){

	 					if( $last_sent_date['sent_today'] === 0 ){

			 				if( $must_send ){

			 					update_option( 'wll_stats_last_sent_date', array( 'sent_today' => 1, 'date' => current_time( 'Y-m-d' ) ) );

			 				}

			 			} else {

			 				/**
			 				 * Already been sent
			 				 */
			 				$must_send = false;

			 			}

			 		}

	 			} else {

 					update_option( 'wll_stats_last_sent_date', array( 'sent_today' => 0, 'date' => current_time( 'Y-m-d' ) ) );

 					$must_send = false;

	 			}
	 		}

	 	} else {

	 		update_option( 'wll_stats_last_sent_date', array( 'sent_today' => 0, 'date' => current_time( 'Y-m-d' ) ) );

	 	}

	 	if( $must_send ){

	 		$range_selected = isset( $wll_stats_settings['range_selected'] ) ? sanitize_text_field( $wll_stats_settings['range_selected'] ) : "";
			$custom_start = isset( $wll_stats_settings['custom_start'] ) ? sanitize_text_field( $wll_stats_settings['custom_start'] ) : "";
			$custom_end = isset( $wll_stats_settings['custom_end'] ) ? sanitize_text_field( $wll_stats_settings['custom_end'] ) : "";

		 	$stats = $this::wll_stats_return_user_stats_array( array( 'range' => $range_selected, 'custom-start' => $custom_start, 'custom-end' => $custom_end ) );

		 	$ret .= "<h3 style='width: 60%; margin: 20px auto;'>".__('Logins per agent, per day', 'when-last-login-stats')."</h3>";
		 	$ret .= "<table width='60%' style='margin: 0 auto; border: 1px solid #CCC; text-align: center;'>";
		 	$ret .= "<thead>";
		 	$ret .= "<tr><th>".__('Date', 'when-last-login-stats')."</th><th>".__('Agent Name', 'when-last-login-stats')."</th><th>".__('Login Count', 'when-last-login-stats')."</th></tr>";
		 	$ret .= "</thead>";
		 	$ret .= "<tbody>";
		 	if( isset( $stats['wll_stats_users_grouped_by_name_time'] ) ){
	 			foreach( $stats['wll_stats_users_grouped_by_name_time'] as $key => $val ){
	 				foreach( $val as $a_name => $array ){
	 					$ret .= "<tr><td>$key</td><td>$a_name</td><td>".count( $array )."</td></tr>";
	 				}
	 			}
		 	}
		 	$ret .= "</tbody>";
		 	$ret .= "</table>";

		 	$ret .= "<h3 style='width: 60%; margin: 20px auto;'>".__('Logins per hour', 'when-last-login-stats')."</h3>";
		 	$ret .= "<table width='60%' style='margin: 0 auto; border: 1px solid #CCC; text-align: center;'>";
		 	$ret .= "<thead>";
		 	$ret .= "<tr><th>".__('Date & Time', 'when-last-login-stats')."</th><th>".__('Login Count', 'when-last-login-stats')."</th></tr>";
		 	$ret .= "</thead>";
		 	$ret .= "<tbody>";
		 	if( isset( $stats['wll_stats_users_grouped_by_name_time_hourly'] ) ){
	 			foreach( $stats['wll_stats_users_grouped_by_name_time_hourly'] as $key => $val ){
					$ret .= "<tr><td>$key</td><td>".count($val)."</td></tr>";
	 			}
		 	}
		 	$ret .= "</tbody>";
		 	$ret .= "</table>";

		 	$ret .= "<h3 style='width: 60%; margin: 20px auto;'>".__('Total logins per day', 'when-last-login-stats')."</h3>";
		 	$ret .= "<table width='60%' style='margin: 0 auto; border: 1px solid #CCC; text-align: center;'>";
		 	$ret .= "<thead>";
		 	$ret .= "<tr><th>".__('Date', 'when-last-login-stats')."</th><th>".__('Agent Name', 'when-last-login-stats')."</th><th>".__('Login Count', 'when-last-login-stats')."</th></tr>";
		 	$ret .= "</thead>";
		 	$ret .= "<tbody>";
		 	if( isset( $stats['wll_stats_users_grouped_by_name_count'] ) ){
	 			foreach( $stats['wll_stats_users_grouped_by_name_count'] as $key => $val ){
					foreach( $val as $a_name => $array ){
	 					$ret .= "<tr><td>$key</td><td>$a_name</td><td>$array[$a_name]</td></tr>";
	 				}
	 			}
		 	}
		 	$ret .= "</tbody>";
		 	$ret .= "</table>";

		 	$recipients = isset( $wll_stats_settings['recipient_address'] ) ? $wll_stats_settings['recipient_address'] : "";

	 		$recipients_array = explode( ",", $recipients);

		 	$subject = __('When Last Login - Usage Statistics Scheduled Report', 'when-last-login');

		 	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		 	if( is_array( $recipients_array ) ){

		 		foreach( $recipients_array as $recip ){
		 			$sent = wp_mail( trim( $recip ), $subject, $ret, $headers );
		 		}

		 	}

		}

	}

	public function wll_stats_modify_cron_recurrence( $schedules ){

		$schedules['wll_every_ten_minutes'] = array(
	            'interval'  => 600,
	            'display'   => __( 'Every 10 Minutes', 'when-last-login-stats' )
	    );

	    return $schedules;

	}

	public function wll_stats_admin_notices(){

		$ret = "";

		$wll_stats_upload_dir = wp_upload_dir();

	    $wll_stats_upload_folder = $wll_stats_upload_dir['basedir'];

		if( get_option( 'wll_stats_file_dir_not_writeable' ) == 1 || !file_exists( $wll_stats_upload_folder.'/when-last-login-user-statistics' ) ){

			$wll_stats_upload_dir = wp_upload_dir();

		    $wll_stats_upload_folder = $wll_stats_upload_dir['basedir'];

			$ret .= "<div class='error'>";
			$ret .= "<h2>".__('When Last Login - User Statistics')."</h2>";
			$ret .= "<p>".sprintf(__("The folder 'when-last-login-user-statistics' was unable to be created in your uploads directory. Please create this folder in %s and ensure it is writeable to allow for When Last Login - User Statistics PDF functionality to function correctly.", "when-last-login-stats"), $wll_stats_upload_folder )."</p>";
			$ret .= "</div>";

		}

		echo $ret;

	}

	public function wll_stats_rest_routes() {

		register_rest_route('when_last_login_user_stats/v1','/return_stats', array(
			'methods' => 'GET, POST',
			'callback' => array( $this, 'wll_return_stats_callback' ),
			'permission_callback' => array( $this, 'wll_stats_permission_check' ),
		));

	}

	// Capability check for REST API for admins only.
	public function wll_stats_permission_check( $request ) {
		if ( current_user_can( apply_filters( 'wll_rest_api_caps', 'manage_options' ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function wll_return_stats_callback( WP_REST_Request $request ){

		$return_array = array();

		if(isset($request)){

			if(isset($request['token'])){

				$check_token = get_option('wll_stats_secret_token');

				if($check_token !== false && $request['token'] === $check_token){

					$range = isset( $request['range'] ) ? $request['range'] : "";
					$start_date = isset( $request['start_date'] ) ? $request['start_date'] : "";
					$end_date = isset( $request['end_date'] ) ? $request['end_date'] : "";

					$args = array(
						'range' => $range,
						'start_date' => $start_date,
						'end_date' => $end_date
					);

					$return_array['response'] = "Login stats returned successfully";
					$return_array['code'] = "200";
					$return_array['body'] = $this::when_last_login_return_stats( $args );

			 	} else {
					$return_array['response'] = "Secret token is invalid";
					$return_array['code'] = "401";
				}

			}else{
				$return_array['response'] = "No secret 'token' found";
				$return_array['code'] = "401";
				$return_array['requirements'] = array("token" => "YOUR_SECRET_TOKEN");
			}

		}else{
			$return_array['response'] = "No request data found";
			$return_array['code'] = "400";
			$return_array['requirements'] = array("token" => "YOUR_SECRET_TOKEN");
		}

		return $return_array;

	}

	private function when_last_login_return_stats( $additional_args ){

		$additional_args = apply_filters( 'wll_stats_additional_args_filter', $additional_args );

		if( $additional_args['range'] == 'today' ){

			$args = array(
				'posts_per_page' => -1,
				'post_type' => 'wll_records',
				'date_query' => array(
					'after' => 'today',
					'inclusive' => true
				)
			);

		} else if( $additional_args['range'] == '-7days' ){

			$args = array(
				'posts_per_page' => -1,
				'post_type' => 'wll_records',
				'date_query' => array(
					'before' => '7 Days'
				)
			);

		} else if( $additional_args['range'] == '-30days' ){

			$args = array(
				'posts_per_page' => -1,
				'post_type' => 'wll_records',
				'date_query' => array(
					'before' => '30 Days'
				)
			);

		} else if( $additional_args['range'] == 'custom' ){

			$new_start_date = strtotime( $additional_args['start_date'] .' - 1 day' );
			$new_end_date =  strtotime( $additional_args['end_date'] .' + 1 day' );

			$args = array(
				'posts_per_page' => -1,
				'post_type' => 'wll_records',
				'date_query' => array(
					'after' => date('Y-m-d', $new_start_date ),
					'before' => date('Y-m-d', $new_end_date ),
					'inclusive' => true
				)
			);

		} else {

			$args = array(
				'posts_per_page' => -1,
				'post_type' => 'wll_records'
			);

		}

		$args = apply_filters( 'wll_return_stats_wpquery_filter', $args );

		$login_record_array = array();

		$the_query = new WP_Query( $args );

		if ( $the_query->have_posts() ) {

			while ( $the_query->have_posts() ) {

				$the_query->the_post();

				$activity_author = get_the_author();
				$activity_date = get_the_date( 'Y-m-d H:i:s' );

				$activity_last_login = get_the_author_meta( 'when_last_login' );
				$activity_author_id = get_the_author_meta( 'ID' );

				$login_record_array[] = array(
					'author_name' => $activity_author,
					'author_id' => $activity_author_id,
					'date' => strtotime( $activity_date ),
					'last_login_timestamp' => (int) $activity_last_login
				);

			}

			wp_reset_postdata();

		}

		return $login_record_array;

	}

	public function wll_stats_tabs( $tabs ){

		$tabs['user-statistics'] = array(
			'title' => __('User Statistics', 'when-last-login-stats'),
			'icon' => ''
		);

		return $tabs;

	}

	public function wll_stats_content( $content ){

		$content['user-statistics'] = plugin_dir_path( __FILE__ ).'/when-last-login-user-statistics-settings.php';

		return $content;

	}

	public function wll_stats_page(){

		add_submenu_page( 'when-last-login-settings', __('When Last Login - User Statistics', 'when-last-login-stats'), __('User Statistics', 'when-last-login-stats'), 'manage_options', 'when-last-login-stats', array( $this, 'wll_stats_page_callback' ) );
	}

	public function wll_stats_page_callback(){

		include plugin_dir_path( __FILE__ ).'/when-last-login-user-statistics-page.php';

	}

	public function wll_stats_enqueue_admin_scripts(){

		if( isset( $_GET['page'] ) && $_GET['page'] == 'when-last-login-settings' ){

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'wll-stats-settings-js', plugins_url( '/js/settings.js', __FILE__ ) );
			wp_enqueue_style( 'jquery-ui', 'http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );

		}

		if( isset( $_GET['page'] ) && $_GET['page'] == 'when-last-login-stats' ){

			wp_enqueue_script( 'wll-stats-google-charts' , 'https://www.gstatic.com/charts/loader.js' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-datepicker' );

		    wp_enqueue_style( 'jquery-ui', 'http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );

			wp_enqueue_style( 'wll-stats-datatables-css', '//cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css' );
			wp_enqueue_script( 'wll-stats-datatables-js', '//cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js' );

			wp_enqueue_style( 'wll-stats-admin-css', plugins_url( '/css/admin.css', __FILE__ ) );

			$wll_stats_returned = $this::wll_stats_return_user_stats_array( $_GET );

			wp_enqueue_script( 'wll-stats-admin-script', plugins_url( '/js/admin.js', __FILE__ ) );

			wp_localize_script( 'wll-stats-admin-script', 'wll_stats_users_grouped_by_name_time', $wll_stats_returned['wll_stats_users_grouped_by_name_time'] ); // This is being used
			wp_localize_script( 'wll-stats-admin-script', 'wll_stats_users_grouped_by_name_time_hourly', $wll_stats_returned['wll_stats_users_grouped_by_name_time_hourly'] );
			wp_localize_script( 'wll-stats-admin-script', 'wll_stats_users_grouped_by_name_count', $wll_stats_returned['wll_stats_users_grouped_by_name_count'] ); // This is being used
			wp_localize_script( 'wll-stats-admin-script', 'wll_stats_users_grouped_by_name_last_logged', $wll_stats_returned['wll_stats_users_grouped_by_name_last_logged'] );

		}

	}

	private static function wll_stats_return_user_stats_array( $array ){

		if( isset( $array['range'] ) ){

			if( $array['range'] == 'today' ){

				$args = array(
					'timeout' => 15,
					'body' => array(
						'range' => 'today',
						'token' => get_option( 'wll_stats_secret_token' )
					)
				);

			} else if( $array['range'] == '-7days' ){

				$args = array(
					'timeout' => 15,
					'body' => array(
						'range' => '-7days',
						'token' => get_option( 'wll_stats_secret_token' )
					)
				);

			} else if( $array['range'] == '-30days' ){

				$args = array(
					'timeout' => 15,
					'body' => array(
						'range' => '-30days',
						'token' => get_option( 'wll_stats_secret_token' )
					)
				);

			} else if( $array['range'] == 'custom' ){

				$start_date = isset( $array['custom-start'] ) ? $array['custom-start'] : "";
				$end_date = isset( $array['custom-end'] ) ? $array['custom-end'] : "";

				$args = array(
					'timeout' => 15,
					'body' => array(
						'range' => 'custom',
						'start_date' => $start_date,
						'end_date' => $end_date,
						'token' => get_option( 'wll_stats_secret_token' )
					)
				);

			} else {

				$args = array( 'timeout' => 15, 'body' => array( 'token' => get_option( 'wll_stats_secret_token' ) ) );

			}

		} else {

			$args = array( 'timeout' => 15, 'body' => array( 'token' => get_option( 'wll_stats_secret_token' ) ) );

		}

		$response = wp_remote_post( get_rest_url(null, 'when_last_login_user_stats/v1/return_stats'), $args );


		$response_body = wp_remote_retrieve_body( $response );

		$users_grouped_by_name_time = array();
		$users_grouped_by_name_last_logged = array();
		$users_grouped_by_name_count = array();
		$users_grouped_by_name_time_hourly = array();
		$users_grouped_by_name_time_orig = array();
		// $the_stuff = array();
		$agents = array();
		if( $response_body != "" ){

			$response_body = json_decode( $response_body );

			if( is_object( $response_body ) ){

				if( $response_body->code == '200' ){

					$statistics = $response_body->body;

					if( is_array( $statistics ) ){

						foreach( $statistics as $stat ){
							$agents[] = $stat->author_name;
							$users_grouped_by_name_time_orig[date( 'Y-m-d', $stat->date )][$stat->author_name][] = date( 'Y-m-d', $stat->date );

							$users_grouped_by_name_time_hourly[date( 'Y-m-d H:00', $stat->date )][] = date( 'Y-m-d H:00', $stat->date );

							$users_grouped_by_name_last_logged[$stat->author_name] = date( 'Y-m-d H:i:s', $stat->last_login_timestamp );
						}

						$agents = array_unique( $agents );

						foreach( $agents as $na ){
							foreach( $users_grouped_by_name_time_orig as $key => $val ){
								if( !isset( $users_grouped_by_name_time_orig[$key][$na] ) ){
									$users_grouped_by_name_time[$key][$na] = array();
								} else {
									$users_grouped_by_name_time[$key] = $val;
								}
							}
						}

						foreach( $users_grouped_by_name_time as $date => $val ){
							foreach( $val as $agent_name => $agent ){
								$users_grouped_by_name_count[$date][$agent_name] = array( $agent_name => count( $agent ) );
							}
						}
						// This ^^^ works.. don't ask how or why.. it just does.

						/**
						 * Check each array
						 * If count == 0
						 * - Then: Set a default to avoid Google Charts from outputting errors
						 * - We use false to zero out the influence on the chart
						 * - In some sub array combinations we use zero to zero out the chart
						 *
						 * Ignore name/last-login array as this is a dataTable, does not apply
						*/
						if(count($users_grouped_by_name_time_hourly) == 0){
							$users_grouped_by_name_time_hourly[' '] = false;
						}

						if(count($users_grouped_by_name_count) == 0){
							$users_grouped_by_name_count[' '][' '] = array(' ' => 0);
						}

						if(count($users_grouped_by_name_time_orig) == 0){
							$users_grouped_by_name_time_orig[' '][' '] = array();
						}


						return array(
							'wll_stats_users_grouped_by_name_time' => $users_grouped_by_name_time_orig,
							'wll_stats_users_grouped_by_name_time_hourly' => $users_grouped_by_name_time_hourly,
							'wll_stats_users_grouped_by_name_count' => $users_grouped_by_name_count,
							'wll_stats_users_grouped_by_name_last_logged' => $users_grouped_by_name_last_logged
						);
					}

				}

			}

		} else {
			if(count($users_grouped_by_name_time_hourly) == 0){
				$users_grouped_by_name_time_hourly[' '] = false;
			}

			if(count($users_grouped_by_name_count) == 0){
				$users_grouped_by_name_count[' '][' '] = array(' ' => 0);
			}

			if(count($users_grouped_by_name_time_orig) == 0){
				$users_grouped_by_name_time_orig[' '][' '] = array();
			}

			return array(
				'wll_stats_users_grouped_by_name_time' => $users_grouped_by_name_time_orig,
				'wll_stats_users_grouped_by_name_time_hourly' => $users_grouped_by_name_time_hourly,
				'wll_stats_users_grouped_by_name_count' => $users_grouped_by_name_count,
				'wll_stats_users_grouped_by_name_last_logged' => $users_grouped_by_name_last_logged
			);
		}

	}

	public function wll_convert_base64_to_image_file(){

		if( isset( $_POST['action'] ) && $_POST['action'] == 'wll_stats_convert_to_image' ){

			$wll_stats_upload_dir = wp_upload_dir();

		    $wll_stats_upload_folder = $wll_stats_upload_dir['basedir'].'/when-last-login-user-statistics';
		    $wll_stats_upload_url = $wll_stats_upload_dir['baseurl'].'/when-last-login-user-statistics';

			$img = str_replace( 'data:image/png;base64,', '', $_POST['base64'] );

			$img = str_replace( ' ', '+', $img );

			$data = base64_decode( $img );

			$new_image_file = get_current_user_id().'-'.sanitize_text_field( $_POST['graph'] ).'.png';

			$new_image_url = $wll_stats_upload_url.'/'.$new_image_file;

			if( file_exists( $wll_stats_upload_folder.'/'.$new_image_file ) ){

				unlink( $wll_stats_upload_folder.'/'.$new_image_file );

			}

			$added = file_put_contents( $wll_stats_upload_folder.'/'.$new_image_file, $data );

			if( $added == 0 ){

				$added = false;

			} else {

				$added = $wll_stats_upload_url.'/'.$new_image_file;

			}

			echo $added;

			wp_die();

		}

	}

	public function wll_stats_download_pdf(){

		if( isset( $_GET['wll_stats_download'] ) && $_GET['wll_stats_download'] == 'pdf' ){

			require_once( plugin_dir_path( __FILE__ ).'/tcpdf/tcpdf.php' );

			$wll_stats_upload_dir = wp_upload_dir();

		    $wll_stats_upload_url = $wll_stats_upload_dir['baseurl'].'/when-last-login-user-statistics';

		    $user_id = get_current_user_id();

			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor( __('When Last Login - User Statistics', 'when-last-login-stats' ) );
			$pdf->SetTitle( __('When Last Login - User Statistics Report - ', 'when-last-login-stats' ).date( 'Y-m-d' ) );
			// $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
			// set margins
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
			// $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			// $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

			// set auto page breaks
			$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

			// set image scale factor
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			$pdf->AddPage();

			$html = "<h4>".__('Logins per agent, per day', 'when-last-login-stats')."</h4>";
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			$pdf->Image( $wll_stats_upload_url . '/'.$user_id.'-when-last-login-total-agent-per-day.png',  '', '', 0, 0, '', '', 'center', true);

			$pdf->AddPage();

			$html = "<h4>".__('Logins per hour', 'when-last-login-stats')."</h4>";
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			$pdf->Image( $wll_stats_upload_url . '/'.$user_id.'-when-last-login-total-agent-hourly.png',  '', '', 0, 0, '', '', 'center', true);

			$pdf->AddPage();

			$html = "<h4>".__('Total logins per day', 'when-last-login-stats')."</h4>";
			$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
			$pdf->Image( $wll_stats_upload_url . '/'.$user_id.'-when-last-login-total-logins-per-day.png',  '', '', 0, 0, '', '', 'center', true);

			$pdf->Output('when-last-login-user-statistics-report-'.date( 'Y-m-d' ).'.pdf', 'I');

		}

	}

	public static function wll_stats_time_array(){

		$h = array();

		for( $i = 0; $i <= 23; $i++){
			$h[] = sprintf('%02d', $i );
		}

		$m = array();

		for( $i = 0; $i <= 59; $i++){
			$m[] = sprintf('%02d', $i );
		}

		$t = array(
			'hours' 	=> $h,
			'minutes' 	=> $m
		);

		return $t;

	}

	public function wll_stats_save_settings(){

		if( isset( $_POST['wll_save_stats_settings'] ) ){

			$frequency = isset( $_POST['wll_stats_automatically_email_frequency'] ) ? sanitize_text_field ( $_POST['wll_stats_automatically_email_frequency'] ) : "";
			$frequency_days = isset( $_POST['wll_stats_frequency_days'] ) ? sanitize_text_field ( $_POST['wll_stats_frequency_days'] ) : "";
			$frequency_day_num = isset( $_POST['wll_stats_month_day'] ) ? sanitize_text_field ( $_POST['wll_stats_month_day'] ) : "";
			$frequency_h = isset( $_POST['wll_stats_time_to_send_h'] ) ? sanitize_text_field ( $_POST['wll_stats_time_to_send_h'] ) : "";
			$frequency_m = isset( $_POST['wll_stats_time_to_send_m'] ) ? sanitize_text_field ( $_POST['wll_stats_time_to_send_m'] ) : "";

			$recipient_address = isset( $_POST['wll_stats_recipient_email_addresses'] ) ? sanitize_text_field ( $_POST['wll_stats_recipient_email_addresses'] ) : "";

			$logo_url = isset( $_POST['wll_stats_report_logo_url'] ) ? sanitize_text_field ( $_POST['wll_stats_report_logo_url'] ) : "";

			$range_selected = isset( $_POST['wll_stats_timeslot_range'] ) ? sanitize_text_field( $_POST['wll_stats_timeslot_range'] ) : "";
			$custom_start = isset( $_POST['wll_stats_custom_start'] ) ? sanitize_text_field( $_POST['wll_stats_custom_start'] ) : "";
			$custom_end = isset( $_POST['wll_stats_custom_end'] ) ? sanitize_text_field( $_POST['wll_stats_custom_end'] ) : "";

			$settings = array(
				'frequency' => $frequency,
				'frequency_days' => $frequency_days,
				'frequency_day_num' => $frequency_day_num,
				'frequency_h' => $frequency_h,
				'frequency_m' => $frequency_m,
				'recipient_address' => $recipient_address,
				'logo_url' => $logo_url,
				'range_selected' => $range_selected,
				'custom_start' => $custom_start,
				'custom_end' => $custom_end
			);

			update_option( 'wll_stats_settings', $settings );

		}

	}

}

new WhenLastLoginStatistics();
