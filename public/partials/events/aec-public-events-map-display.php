<?php

/**
 * Markup the events map page.
 */ 
?>

<div class="aec aec-events aec-map-layout">
	<?php if( $has_header ) : ?>
		<!-- Header -->
    	<div class="row aec-no-margin">
    		<div class="pull-left text-muted">
        		<?php printf( __( ' %d Event(s) Found', 'another-events-calendar' ), $aec_query->found_posts ); ?>
        	</div>
        
        	<?php if( count( $view_options ) > 1 ) : ?>
                    <div class="pull-right">
                        <form action="" method="GET">
                            <?php 
                                foreach( $_GET as $key => $content ) {
                                    if( 'view' != $key ) {
                                        printf( '<input type="hidden" name="%s" value="%s" />', $key, $content );
                                    }
                                }
                            ?>
                            <select name="view" onchange="this.form.submit()" class="form-control">
                                <?php
                                    foreach( $view_options as $view_option ) {
                                        printf( '<option value="%s"%s>%s</option>', $view_option, selected( $view_option, $view ), $view_option );
                                    }
                                ?>
                            </select>
                        </form>
                    </div>
                <?php endif; ?>
            
                <div class="clearfix"></div>
            </div>
        <?php endif; ?>
        
        <!-- Map -->
        <?php if( $has_map ) : ?>  
        <div class="aec-margin-top">
            <div class="embed-responsive embed-responsive-16by9">
                <div class="aec-map embed-responsive-item" data-type="normal"> 
                    <?php while( $aec_query->have_posts() ) : $aec_query->the_post();
                            $venue_id  = get_post_meta( get_the_ID(), 'venue_id', true );
                            $latitude  = get_post_meta( $venue_id, 'latitude', true );
                            $longitude = get_post_meta( $venue_id, 'longitude', true );
        					if( $venue_id > 0 ) : ?>
                            <div class="marker" data-latitude="<?php echo $latitude; ?>" data-longitude="<?php echo $longitude; ?>">
                                <div class="media" style="max-height:150px; max-width:200px;">
                                    <div class="row">
                                        <div class="col-md-4 hidden-xs hidden-sm">
                                            <div class="pull-left">
        
                                                <?php if( has_post_thumbnail() ) : ?>
                                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( array( 40,60 ) ); ?></a>
                                                <?php else : ?>
                                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                                        <img src="<?php echo AEC_PLUGIN_URL; ?>public/images/placeholder-event.jpg" class="img-responsive" />
                                                    </a>
                                                <?php endif; ?>
                                            
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="media-body">
                                                <strong><?php echo _e( 'Event :', 'another-events-calendar' ); ?></strong>
                                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php echo get_the_title(); ?></a>
                                                <br>
                                                
                                                <strong><?php echo _e( 'Date :', 'another-events-calendar' ); ?></strong>
                                                <?php 
                                                    $start_date_time = get_post_meta( get_the_ID(), 'start_date_time', true );
                                                    $start_date_time = date_i18n( get_option('date_format'), strtotime( $start_date_time ) );
                                                    echo $start_date_time; 
                                                ?>
                                                <br>
                                                
                                                <?php
                                                	if( ! empty( $general_settings['has_venues'] ) ) {
														if( $venue_id > 0 ) : ?>
                                                        	<strong><?php echo _e( 'Venue :', 'another-events-calendar' ); ?></strong>
                                                        	<a href="<?php echo aec_venue_page_link( $venue_id ); ?>"><?php echo get_the_title( $venue_id ); ?></a>
                                                    	<?php endif;
                                             		}
												?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                       	<?php endif; ?>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Pagination -->
    <?php if( $has_pagination ) : ?>
    	<!-- Footer -->
    	<div class="row aec-no-margin">
    		<?php the_aec_pagination( $aec_query->max_num_pages, "", $paged ); ?>
    	</div>
    <?php endif; ?>
</div>

<?php 
	wp_reset_postdata();
	the_footer_text();   
	the_aec_socialshare_buttons();