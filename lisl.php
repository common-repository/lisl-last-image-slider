<?php
/*
Plugin Name: LISL Last-Image Slider
Plugin URI: http://blog.antonellocicchese.com/lisl-last-image-slider-plugin/
Description: Last-Image Slider, is an implementation of <a href="http://nivo.dev7studios.com/" target="_blank">Nivo Slider</a>, using <a href="http://www.darrenhoyt.com/2008/04/02/timthumb-php-script-released/" target="_blank">TimThumb</a> resizer, to display last uploaded images with a jquery cool slider effect.
WordPress plugin developed by Antonello Cicchese. Usage: simply once you activated it, put display_last_image_slider() call in your template.
Version: 1.0
Author: Antonello Cicchese
Author URI: http://www.antonellocicchese.com/
License: GPL2
*/

/*  Copyright 2010  WP Last-Image Slider - Rafael Cirolini  (email : info@antonellocicchese.com)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('admin_init', 'lisl_options_init' );
add_action('admin_menu', 'lisl_options_add_page');
add_theme_support('post-thumbnails');

register_activation_hook(__FILE__,'lisl_options_init');
register_deactivation_hook( __FILE__, 'lisl_options_remove' );



function display_last_image_slider() {
	$options = get_option('lisl_opt_array');
	
	$number = $options['lisl_image_num'] ; // NUMBER OF IMAGES TO DISPLAY
	if(!$number || $numer<1) $numer=5;
	
	$max_width=$options['lisl_image_width'];
	if(!$max_width || $max_width<=0)  $max_width=900; //MAX IMAGE WIDTH

	$max_heigth=$options['lisl_image_heigth']; 
	if(!$max_heigth || $max_heigth<=0) $max_heigth=230; // MAX IMAGE HEIGHT


/*********************************************************************************************
 ************************************* DO NOT EDIT BELOW *************************************
 ********************************************************************************************/

    	global $wpdb;
    	global $post;    	
		// get the IDs of the latest attachments

	$query = "
    		SELECT 
    			at.ID, at.post_title, at.post_parent
    		FROM $wpdb->posts AS at    
    		WHERE 
    			at.post_type = 'attachment'    	
    		AND
    			at.post_mime_type LIKE 'image/%'
    		ORDER BY at.post_date DESC
    		LIMIT $number
    	";
    	$latest_attachments = $wpdb->get_results( $query, ARRAY_A );
 
 ?>


  
  
<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL . "/lisl/"; ?>nivo-slider.css" type="text/css" media="screen" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo WP_PLUGIN_URL . "/lisl/script/"; ?>jquery.nivo.slider.pack.js" type="text/javascript"></script>

  
  <style type="text/css">
  #slider {
    position:relative;
    width:<?php echo $max_width;?>px; /* Change this to your images width */
    height:<?php echo $max_heigth;?>px; /* Change this to your images height */
    background:url(<?php echo WP_PLUGIN_URL . "/lisl/";?>images/loading.gif) no-repeat 50% 50%;	
}
#slider img {
    position:absolute;
    top:0px;
    left:0px;
    display:none;
}
#slider a {
    border:0;
    display:block;
}
.nivo-controlNav {
	position:absolute;
	left:400px;
	bottom:-42px;
}
.nivo-controlNav a {
	display:block;
	width:22px;
	height:22px;
	background:url(<?php echo WP_PLUGIN_URL . "/lisl/";?>images/bullets.png) no-repeat;
	text-indent:-9999px;
	border:0;
	margin-right:3px;
	float:left;
}
.nivo-controlNav a.active {
	background-position:0 -22px;
}

 </style>
	
	<div id="slider">

    


    

 		
<?php
	$first=true;
	$i=0;
    	foreach( $latest_attachments as $attachment ) {
			$i++;
    		$image = wp_get_attachment_image_src( $attachment['ID'] ,'large');
    		$data = wp_get_attachment_metadata( $attachment['ID'] );
    		if( $attachment['post_parent'] ) {
    			$link = get_permalink( $attachment['post_parent'] );
    		} else {
    			$link = '?s=' . preg_replace( '/\-/', '+', sanitize_title( $attachment['post_title'] ) );
    		}
?>
		<a href="<?php echo $link;?>"><img src="<?php echo WP_PLUGIN_URL . "/lisl/";?>timthumb.php?src=<?php echo $image[0]; ?>&h=<?php echo $max_heigth;?>&w=<?php echo $max_width;?>&zc=1" title="<?php echo $attachment['post_title'];?>" /></a>		    
	      
<?php		
		
		}
?>
	</div>


<script type="text/javascript">
$(window).load(function() {
    $('#slider').nivoSlider({        
        slices:15, // For slice animations
        boxCols: 8, // For box animations
        boxRows: 4, // For box animations
        animSpeed:500, // Slide transition speed
        pauseTime:3000, // How long each slide will show
        startSlide:0, // Set starting Slide (0 index)
        directionNav:false, // Next & Prev navigation
        directionNavHide:true, // Only show on hover
        controlNav:true, // 1,2,3... navigation        
        keyboardNav:true, // Use left & right arrows
        pauseOnHover:true, // Stop animation while hovering
        manualAdvance:true, // Force manual transitions
        captionOpacity:0.8, // Universal caption opacity
        prevText: 'Prev', // Prev directionNav text
        nextText: 'Next', // Next directionNav text        
    });
});
</script>

<br/><br/>
<?php
    }
	// Init plugin options to white list our options
function lisl_options_init(){	
	register_setting("LISL-group","lisl_opt_array", 'validate_lisl_imagenum');
}
function lisl_options_remove(){	
	unregister_setting( "LISL-group","lisl_opt_array", 'validate_lisl_imagenum');
}
// Add menu page
function lisl_options_add_page() {
	add_theme_page('Last Image Slider', 'LISL', 'administrator','lisl', 'lisl_admin_page');
}
// Draw the menu page itself
function lisl_admin_page() {
	?>
	<div class="wrap">
	<div id="icon-upload" class="icon32"></div><br/>
	
		<h2>Last-Image Slider Options</h2>
		<div class="updated">
			<p><strong>Thank you for using LISL plugin.</strong></p>
			<p>
				<em>Description:</em>
				<p>
					Last-Image Slider, is an implementation of <a href="http://nivo.dev7studios.com/" target="_blank">Nivo Slider</a>, using <a href="http://www.darrenhoyt.com/2008/04/02/timthumb-php-script-released/" target="_blank">TimThumb</a> resizer, to display last uploaded images with a jquery cool slider effect.
				</p>
			</p>
			<p>
				<em>Instructions:</em>
				<p>
					<ol>
					<li>Activate the plugin</li>
					<li>Go to LISL option page under Themes</li>
					<li>Configure how many images would you like to see in the slider</li>
					<li>Configure max images size</li>
					<li>Save your configuration</li>					
					<li>Now you can edit your theme and put, where you prefer, the slider effect using this call:<br/><strong>&lt;?php if ( function_exists('display_last_image_slider') )  {  display_last_image_slider(); } ?&gt;</strong></li>
					</ol>
				</p>
			</p>
			<p>
				<em>Author:</em>
				<p>
					Antonello Cicchese (<a href="http://www.antonellocicchese.com" target="_blank" >website</a>)<br/>
					Plugin page: <a href="http://blog.antonellocicchese.com/lisl-last-image-slider-plugin/" target="_blank" >LISL Last-Image Slider</a>
				</p>
			</p>
			<p>
				<em>Donate:</em>
				<p>
					If you consider this plugin useful, a donation would be apreciated: <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/it_IT/i/scr/pixel.gif" width="1" height="1">
</form>

				</p>
			</p>
		</div>
		<form method="post" action="options.php">
			<?php settings_fields('LISL-group'); ?>
			<?php $options = get_option('lisl_opt_array'); ?>
			<ul>
				<li><label >Number of images to show<span> *</span>: </label>
				<input name="lisl_opt_array[lisl_image_num]" type="text" id="lisl_opt_array[lisl_image_num]" value="<?php echo $options['lisl_image_num']; ?>" />( should be >0 , default: 5)</li>    
				<li><label >Image width<span> *</span>: </label>
				<input name="lisl_opt_array[lisl_image_width]" type="text" id="lisl_opt_array[lisl_image_width]" value="<?php echo $options['lisl_image_width']; ?>" /> ( should be > 0 , default: 900)</li>    
				<li><label >Image heigth <span> *</span>: </label>
				<input name="lisl_opt_array[lisl_image_heigth]" type="text" id="lisl_opt_array[lisl_image_heigth]" value="<?php echo $options['lisl_image_heigth']; ?>" />( should be > 0  , default: 230)</li>    
		
				<li><input type="submit" value="<?php _e('Save Changes') ?>"  class='button-primary' name='save'/></li>
			</ul>
		</form>
	</div>
<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function validate_lisl_imagenum($input) {
	
	// Say our second option must be safe text with no HTML tags
	$input['lisl_image_num'] =  wp_filter_nohtml_kses($input['lisl_image_num']);
	$input['lisl_image_width'] =  wp_filter_nohtml_kses($input['lisl_image_width']);
	$input['lisl_image_heigth'] =  wp_filter_nohtml_kses($input['lisl_image_heigth']);
	
	if($input['lisl_image_num'] <1 || !is_numeric($input['lisl_image_num'])) $input['lisl_image_num'] =5;
	if($input['lisl_image_width'] <=0 || !is_numeric($input['lisl_image_width'])) $input['lisl_image_width'] =900;
	if($input['lisl_image_heigth'] <=0 || !is_numeric($input['lisl_image_heigth'])) $input['lisl_image_heigth'] =230;
	
	return $input;
}

?>