<?php

/**
 * Markup the categories page.
 */
?>

<div class="aec aec-categories"> 
	<?php echo aec_list_categories(); ?>   
</div>

<?php 
	the_footer_text();
	the_aec_socialshare_buttons();