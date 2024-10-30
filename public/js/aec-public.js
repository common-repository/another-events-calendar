(function( $ ) {
	'use strict';

	/*
	 *  This function will render a Google Map onto the selected jQuery element
	 *
	 *  @since	1.0.0
	 */
	function aec_map( $el ) {
		
		// var
		var $markers = $el.find('.marker');

		// vars
		var args = {
			zoom		: parseInt( aec.zoom ),
			center		: new google.maps.LatLng( 0, 0 ),
			mapTypeId	: google.maps.MapTypeId.ROADMAP
		};
		
		// create map	        	
		var map = new google.maps.Map( $el[0], args );
		
		// add a marker reference
		map.markers = [];
			
		if( $el.data('type') == 'normal' ) {
			
			// add markers
			$markers.each(function(){
				
				aec_add_marker( $(this), map );
				aec_center_map( map );
				
			});	
		
		} else {
		
			// Add marker
			var latitude  = $('#aec-latitude').val();
			var longitude = $('#aec-longitude').val();
			var geocoder  = new google.maps.Geocoder();
	
			if( '' != latitude && '' != longitude ) {
					
				$markers.data( 'latitude', latitude );
				$markers.data( 'longitude', longitude );
				aec_add_marker( $markers, map );		
				aec_center_map( map );
	
			} else {
				
				var default_location = $el.data('default_location');
				geocoder.geocode( { 'address': default_location }, function( results, status ) {
					if( status == google.maps.GeocoderStatus.OK ) {
						aec_update_latlng( results[0].geometry.location.lat(), results[0].geometry.location.lng() );
						$markers.data( 'latitude', results[0].geometry.location.lat() );
						$markers.data( 'longitude', results[0].geometry.location.lng() );
						aec_add_marker( $markers, map );		
						aec_center_map( map );			
					}
				});
				
			};
		} 
		
		// Update marker position on address change
		$('.aec-map-field').on('blur', function() {
			var address = [];
			
			if( $('#aec-venue-address').val() ) address.push( $('#aec-venue-address').val() );
			if( $('#aec-venue-city').val() ) address.push( $('#aec-venue-city').val() );
			if( $('#aec-venue-country').val() ) address.push( $("#aec-venue-country option:selected").text() );
			if( $('#aec-venue-state').val() ) address.push( $('#aec-venue-state').val() );
			if( $('#aec-venue-pincode').val() ) address.push( $('#aec-venue-pincode').val() );
			
			address = address.join();

			geocoder.geocode( { 'address': address}, function( results, status ) {
      			if( status == google.maps.GeocoderStatus.OK) {
					map.markers[0].setPosition( results[0].geometry.location );
					aec_update_latlng( results[0].geometry.location.lat(), results[0].geometry.location.lng() );
					aec_center_map( map );					
      			}
    		});
			
		});
		
		// An ugly fix to display the hidden map element by resizing itself
		$( 'select#aec-venues' ).on( "click", function() {
														
			if( -1 == $( this ).val() ) {
				google.maps.event.trigger( map, "resize" );
				aec_center_map( map );
			}
			
		});
		
		// When modal window is open, this script resizes the map and resets the map center
		$( '#aec-map-modal' ).on( "shown.bs.modal", function() {
															 
			google.maps.event.trigger( map, "resize" );
      		aec_center_map( map );
			
		});
		
					
	}
	
	/*
	*  This function will add a marker to the selected Google Map
	*
	*  @since	1.0.0
	*/	
	function aec_add_marker( $markers, map ) {
	
		 // var
		var latlng = new google.maps.LatLng( $markers.data( 'latitude' ), $markers.data( 'longitude' ) );
		
		// check to see if any of the existing markers match the latlng of the new marker
		if( map.markers.length ) {
			for( var i = 0; i < map.markers.length; i++ ) {
				var existing_marker = map.markers[i];
				var pos = existing_marker.getPosition();
	
				// if a marker already exists in the same position as this marker
				if( latlng.equals( pos ) ) {
					// update the position of the coincident marker by applying a small multipler to its coordinates
					var latitude  = latlng.lat() + ( Math.random() - .5 ) / 1500; // * (Math.random() * (max - min) + min);
					var longitude = latlng.lng() + ( Math.random() - .5 ) / 1500; // * (Math.random() * (max - min) + min);
					latlng = new google.maps.LatLng( latitude, longitude );
				}
			}
		}
		
		// create marker
		var marker = new google.maps.Marker({
			position  : latlng,
			map		  : map,
			draggable : ( map.type == 'form' ) ? true : false
		});
	
		// add to array
		map.markers.push( marker );
	
		// if marker contains HTML, add it to an infoWindow
		if( $markers.html() ) {
			// create info window
			var infowindow = new google.maps.InfoWindow({
				content	: $markers.html()
			});
	
			// show info window when marker is clicked
			google.maps.event.addListener(marker, 'click', function() {
	
				infowindow.open( map, marker );
	
			});
		};
		
		// update latitude and longitude values in the form when marker is moved
		if( map.type == 'form' ) {
			google.maps.event.addListener(marker, "dragend", function() {
																  
				var point = marker.getPosition();
				map.panTo(point);
				update_latlng(point);
			
			});	
		};
	
	}		  
	
	/*
	 *  This function will center the map, showing all markers attached to this map
	 *	
	 *  @since	1.0.0
	 */	
	function aec_center_map( map ) {
		
		var bounds = new google.maps.LatLngBounds();
	
		if( map.markers.length > 1 ) {
			
			// fit to bounds
			 var listener = google.maps.event.addListener(map, 'idle', function() {
				
				// loop through all markers and create bounds
				$.each( map.markers, function( i, marker ){
					var latlng = new google.maps.LatLng( marker.position.lat(), marker.position.lng() );
					bounds.extend( latlng );
				});
		
				map.setZoom( parseInt( aec.zoom ) );
    			map.fitBounds( bounds );
				
				// Marker Cluster
				var markerCluster = new MarkerClusterer( map, map.markers, { imagePath : aec.plugin_url+'public/images/m' } );
				
				google.maps.event.removeListener(listener); 
				
			});

		} else {			
		
			// loop through all markers and create bounds
				$.each( map.markers, function( i, marker ){
					var latlng = new google.maps.LatLng( marker.position.lat(), marker.position.lng() );
					bounds.extend( latlng );
				});
			
			map.setCenter( bounds.getCenter() );		
			map.setZoom( parseInt( aec.zoom ) );

		}			
	
	}
	
	/*
	*  Update latlng values in the event or venue forms.
	*
	*  @since	1.5.4
	*/	
	function aec_update_latlng( lat, lng ) {
		
		$( '#aec-latitude' ).val( lat );
		$( '#aec-longitude' ).val( lng );
		
	}
	
	/*
	 *  On Document Ready
	 *
	 *  @since	1.0.0
	 */	
	$(function() {
	
		// Add date picker in the search widget(s)
		$( ".aec-date-picker" ).datepicker({
			dateFormat: 'yy-mm-dd'
		});	
		
		// Add map in custom post type 'aec_venues'
		$( '.aec-map' ).each(function() {
			var data = {
				'type' : $(this).data('type'),	
			};
			aec_map( $(this), data['type'] );
		});
		
		// Add Mini-calendar
		$( 'body' ).on( 'click', '.aec-mini-calendar-nav', function( e ) {
			e.preventDefault();

			var data = {
				'action'	: 'aec_mini_calendar',
				'mo'        : $(this).data('month'),
				'yr'    	: $(this).data('year'),
				'widget_id' : $(this).data('id'),
			};	
			
			$( '.aec-spinner-container', '#' + data['widget_id'] ).html( '<div class="aec-spinner"></div>' );
			
			jQuery.post( aec.ajaxurl, data, function( response ) {
				$( '#' + data['widget_id'] ).replaceWith( response );
			});
		
		});
		
		// Show / Hide time selectors in the event submission form
		$( '#aec-all-day-event' ).on( 'change', function() {
											  
			if( $( "#aec-all-day-event" ).is( ":checked" ) ) { 
				$( '.aec-event-time-fields' ).hide(); 
			} else {
				$( '.aec-event-time-fields' ).show();
			}
			
		}).trigger( 'change' );	
		
		// Show / Hide recurring events in the event submission form
		$( '#aec-recurring-event' ).on( 'change', function () {
											  
			if ( $( "#aec-recurring-event" ).is( ":checked" ) ) { 
				$( '.aec-recurring-event-fields' ).show(); 
			} else {
				$( '.aec-recurring-event-fields' ).hide();
			}
			
		});
		
		// Show / Hide recurring settings based on the recurring type in the event submission form
		$( '#aec-recurring-frequency' ).on( 'change', function() {

			$( '.aec-recurring-settings' ).hide();
			
			var value = $( this ).val();
			switch( value ) {
				case 'daily' :
					$( '.aec-daily-recurrence' ).show();
					break;
				case 'weekly' :
					$( '.aec-weekly-recurrence' ).show();
					break;
				case 'monthly' :
					$( '.aec-monthly-recurrence' ).show();
					break;
			};
			
		}).trigger( 'change' );
		
		// Show / Hide Venue fields in the event submission form
		$( '#aec-venues' ).on( 'change', function() {											 
			
        	var value = $( this ).val();
			
        	 if( -1 == value ) {
            	$( '#aec-venue-fields' ).show();
			} else {
				$( '#aec-venue-fields' ).hide();
			}
			
    	}).trigger( 'change' );	
		
		// Show / Hide Organizer fields in the event submission form
		$( '#aec-add-new-organizer' ).on( 'click', function() {

			$( '#aec-organizer-fields' ).find( '.aec-organizer-fields .aec-organizer-group-id' ).html( aec.i18n_organizer + ' #' + $( '.aec-organizer-fields' ).length );
			var $clone = $( '#aec-organizer-fields' ).find( '.aec-organizer-fields' ).clone( false ); 
			$( '#aec-organizer-fields-container' ).append( $clone );
			
		});
		
		// Delete image attachment
		$( '#aec-img-delete' ).on( 'click', function( e ) {

			e.preventDefault();
			
			var $this = $( this );
			
			var data = {
				'action'        : 'aec_public_delete_attachment',
				'post_id'       : $this.data( 'post_id' ),
				'attachment_id' : $this.data( 'attachment_id' )
			};
			
			$.post( aec.ajaxurl, data, function( response ) {
				$( '#aec-img-preview' ).remove();
			});
			
		});
					
	});

})( jQuery );
