<?php  

/**
 * Events.
 *
 * @link          http://yendif.com
 * @since         1.7.0
 *
 * @package       another-events-calendar
 * @subpackage    another-events-calendar/public
 */

// Exit if accessed directly
if( ! defined( 'WPINC' ) ) {  
	die;
}

/**
 * AEC_Public_Map Class
 *
 * @since    1.7.0
 */
class  AEC_Public_Map { 

	/**
	 * Get things started.
	 *
	 * @since    1.7.0
	 */
	public function __construct( ) { 
	
		global $post;
		
		add_shortcode( 'aec_map', array( $this, 'shortcode_aec_map' ) );

	}

	/**
	 * Process the shortcode [aec_map].
	 *
	 * @since    1.7.0
	 *
	 * @params   array    $atts    An associative array of attributes.
	 */
	public function shortcode_aec_map( $atts ) {   

		// Load dependencies
		wp_enqueue_style( AEC_PLUGIN_SLUG );
		
		wp_enqueue_script( AEC_PLUGIN_SLUG.'-google-map' );
		wp_enqueue_script( AEC_PLUGIN_SLUG.'-markercluster' );
		wp_enqueue_script( AEC_PLUGIN_SLUG.'-bootstrap' );		
		wp_enqueue_script( AEC_PLUGIN_SLUG );
		
		// Vars
		$general_settings = get_option( 'aec_general_settings' );
		$events_settings  = get_option( 'aec_events_settings' );
		$map_settings     = get_option( 'aec_map_settings' );
		
		if( empty( $map_settings['enabled'] ) ) return '';
		
		$atts = shortcode_atts(   
   			array(
        		'category' 	  => '', 
				'tag'		  => '',
        		'venue'   	  => '',
				'organizer'	  => '',
				'filterby'	  => '',
				'past_events' => empty( $general_settings['show_past_events'] ) ? 0 : 1,
				'orderby'	  => $events_settings['orderby'],
				'order'		  => $events_settings['order'],
				'limit'       => $events_settings['events_per_page'],
    		), 
    		$atts
		);
		
		$has_recurring_link = ! empty( $general_settings['has_recurring_events'] ) ? 1 : 0;
		$no_of_cols         = empty( $events_settings['no_of_cols'] ) ? 1 : $events_settings['no_of_cols'] ;
		$span               = round( 12 / $no_of_cols );
		$count              = 0;
		
		// Get category
		$category_slug = get_query_var('aec_category') ? sanitize_title( get_query_var('aec_category') ) : '';
		
		if( empty( $category_slug ) ) {
			if( $atts['category'] ) {
				$category = get_term( (int) $atts['category'], 'aec_categories' );
				if( $category ) $category_slug = $category->slug;
			}
		}
		
		// Get tag
		$tag_slug = get_query_var('aec_tag') ? get_query_var('aec_tag') : '';
		
		if( empty( $tag_slug ) ) {
			if( $atts['tag'] ) {
				$tag = get_term_by( 'id', $atts['tag'], 'aec_tags' );
				if( $tag ) $tag_slug = $tag->slug;
			}
		}
		
		// Get organizer
		$organizer_slug = get_query_var('aec_organizer') ? get_query_var('aec_organizer') : '';	
		$organizer = '';
			
		if( $organizer_slug ) {
			$organizer = get_page_by_path( $organizer_slug, OBJECT, 'aec_organizers' );
		} else {
			if( $atts['organizer'] ) $organizer = get_post( (int) $atts['organizer'] );
		}
		
		// Build query 
		$paged = aec_get_page_number();
		
		$args = array(
			'post_type'      => 'aec_events', 
			'posts_per_page' => empty( $atts['limit'] ) ? -1 : (int) $atts['limit'],
			'order'  		 => sanitize_text_field( $atts['order'] ),
			'paged'          => $paged,
			'post_status'	 => 'publish',
		);
		
		$tax_queries = array();
		
		// categories
		if( ! empty( $category_slug ) ) { 
			$tax_queries[] = array(
				'taxonomy' => 'aec_categories',
				'field'    => 'slug',
				'terms'    => $category_slug,
				
			);
		}
		
		// tags
		if( ! empty( $tag_slug ) ) { 
			$tax_queries[] = array(
				'taxonomy' => 'aec_tags',
				'field'    => 'slug',
				'terms'    => $tag_slug,
				
			);
		}
		
		$meta_queries = array();
		
		// organizer
		if( !empty( $organizer->ID ) ) { 
			$meta_queries[] = array( 
				array( 
					'key'     => 'organizers',
					'value'   => '"'.$organizer->ID.'"', 
					'compare' => 'LIKE'
				) 
			);
		}
		
		if( ! empty( $atts['venue'] ) ) {
			$meta_queries[]	= array( 
				array( 
					'key'     => 'venue_id', 
					'value'   => (int) $atts['venue'], 
					'compare' => '=' 
				) 
			);
		}
		
		if( empty( $atts['past_events'] ) ) { 
			$meta_queries[] = array(
				'relation' => 'OR',
				array(
					'key'     => 'start_date_time',
					'value'	  => current_time('mysql'),
					'compare' => '>=',
					'type'    => 'DATETIME'
				),
				array(
					'key'     => 'end_date_time',
					'value'	  => current_time('mysql'),
					'compare' => '>=',
					'type'    => 'DATETIME'
				)
			);
		} 
		
		// Filter by past events
		if( ! empty( $atts['filterby'] ) && 'past_events' == $atts['filterby'] ) { 
			$meta_queries[] = array( 
				'relation' => 'AND',
				array(
					'key'     => 'start_date_time',
					'value'	  => current_time('mysql'),
					'compare' => '<',
					'type'    => 'DATETIME'
				),
				array(
					'key'     => 'end_date_time',
					'value'	  => current_time('mysql'),
					'compare' => '<',
					'type'    => 'DATETIME'
				)
			);
		}
		
		if( $slug = get_query_var( 'aec_event' ) ) {
		
			$queried_event = get_page_by_path( $slug, OBJECT, 'aec_recurring_events' );
			
			$meta_queries[] = array( 
				'key'     => 'parent',
				'value'   => (int) $queried_event->ID, 
				'compare' => '=',
			);
				
		}
		
		$count_meta_queries = count( $meta_queries );
		if( $count_meta_queries ) {
			$args['meta_query'] = ( $count_meta_queries > 1 ) ? array_merge( array( 'relation' => 'AND' ), $meta_queries ) : array( $meta_queries );
		}
		
		$count_tax_queries = count( $tax_queries );
		if( $count_tax_queries ) {
			$args['tax_query'] = ( $count_tax_queries > 1 ) ? array_merge( array( 'relation' => 'AND' ), $tax_queries ) : array( $tax_queries );
		}
		
		switch( trim( $atts['orderby'] ) ) {
			case 'date':
				$args['orderby'] = 'date';
				break;
			case 'title':
				$args['orderby'] = 'title';
				break;
			case 'event_start_date':
				$args['meta_key'] = 'start_date_time';
				$args['orderby']  = 'meta_value_datetime start_date_time';
				break;
		}
		
		$aec_query = new WP_Query( $args );
			
		ob_start();
		include AEC_PLUGIN_DIR."public/partials/map/aec-public-map-display.php";
		return ob_get_clean();
			
				
	}	

}