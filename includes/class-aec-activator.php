<?php

/**
 * Fired during plugin activation
 *
 * @link          http://yendif.com
 * @since         1.0.0
 *
 * @package       another-events-calendar
 * @subpackage    another-events-calendar/includes
 */

// Exit if accessed directly
if( ! defined( 'WPINC' ) ) {
	die;
}

/**     
 * AEC_Activator Class
 *
 * @since    1.0.0
 */
class AEC_Activator {

	/**
	 * Called during the plugin activation.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {	 	
		
		// Insert general settings
		if( ! get_option( 'aec_general_settings' ) ) {
		
			$default_values = array(
				'bootstrap'            => array( 'css', 'javascript' ),
				'has_categories'	   => 1,
				'has_tags'             => 1,
				'has_venues'           => 1,
				'has_organizers'       => 1,
				'has_recurring_events' => 1,
				'show_comments'        => 1,
				'show_past_events'     => 0,
				'default_location'     => 'US',
				'show_credit_link'	   => 1
			);
			add_option( 'aec_general_settings', $default_values );
			
		}
		
		// Insert calendar settings
		if( ! get_option( 'aec_calendar_settings' ) ) {
			
			$default_values = array(
				'view_options'     	  => array( 'month', 'week', 'day' ),
				'default_view'     	  => 'month',
				'show_all_event_days' => 1
			);
			add_option( 'aec_calendar_settings', $default_values );
			
		}
			
		// Insert events settings
		if( ! get_option( 'aec_events_settings' ) ) {
			
			$default_values = array(
				'view_options'    => array( 'table', 'grid', 'blog' ),
				'default_view'    => 'table',
				'orderby'         => 'event_start_date',
				'order'           => 'asc',
				'no_of_cols'      => 3,
				'events_per_page' => 12
			);
			add_option( 'aec_events_settings', $default_values );
			
		}
		
		// Insert categories settings
		if( ! get_option( 'aec_categories_settings' ) ) {
			
			$default_values = array(
				'orderby'               => 'name',
				'order'                 => 'asc',
				'show_events_count'     => 1,
				'hide_empty_categories' => 0
		 	);
			add_option( 'aec_categories_settings', $default_values );
			
		}
			
		// Insert pages settings
		if( ! get_option( 'aec_page_settings' ) ) {
		
			$default_values = aec_insert_pages();
			add_option( 'aec_page_settings', $default_values );
			
		}
		
		// Insert permalink settings
		if( ! get_option( 'aec_permalink_settings' ) ) {
			
			$default_values = array(
				'event_slug' => 'aec_events'
			);
			add_option( 'aec_permalink_settings', $default_values );
				
		}
			
		// Insert currency settings
		if( ! get_option( 'aec_currency_settings' ) ) {
			
			$default_values = array(
				'currency'            => 'USD',
				'position'            => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.'
			);
			add_option ('aec_currency_settings', $default_values);
				
		}
			
		// Insert map settings
		if( ! get_option( 'aec_map_settings' ) ) {
		
			$default_values = array(
				'enabled'    => 1,
				'api_key'    => '',
				'zoom_level' => 5
			);
			add_option ('aec_map_settings', $default_values);	
				
		}
		
		// Insert social share settings
		if( ! get_option( 'aec_socialshare_settings' ) ) {
		
			$default_values = array(
				'services' => array( 'facebook', 'twitter', 'gplus', 'linkedin', 'pinterest' ),
				'pages'    => array( 'categories', 'event_archives', 'event_detail' )
			);
			add_option ('aec_socialshare_settings', $default_values);	
				
		}
		
		// Add custom capabilities
		if( ! get_option( 'aec_version' ) ) {
			$roles = new AEC_Roles();
			$roles->add_caps();
		}
		
		// Add the plugin version number
		add_option( 'aec_version', AEC_PLUGIN_VERSION );

	}	
	
}
