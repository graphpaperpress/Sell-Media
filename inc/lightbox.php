<?php

/**
 * Lightbox functions
 * @package package Sell Media
 * @since Sell Media 1.9.3
 */

/*// Add AJAX actions
add_action('wp_ajax_sell_media_lightbox_ajax', 'sell_media_add_to_lightbox');
add_action('wp_ajax_nopriv_sell_media_lightbox_ajax', 'sell_media_add_to_lightbox');

function sell_media_add_to_lightbox() {
	$blogname = get_site_url();
	$blogname = str_replace( array(':','.','/','-'), '', $blogname);

	// get post ID
	$id = $_POST['id'];
	$cookie_name = '_stockphotography_lightbox_' . $blogname;
	if( isset($_COOKIE[$cookie_name]) ) {

		$cookie=  $_COOKIE[$cookie_name];
		$lightbox=explode(',',$cookie);

		if(!in_array($id,$lightbox)) {
			$cookie=$cookie.','.$id;
			$status = true;
		} else {
		// not in array
		// text will not update when clicked
		}

	} else {
		$cookie=$id;
		$status = true;
    }
	// set lightbox cookie
	setcookie($cookie_name, $cookie ,time()+3600*24*365,'/');

	// generate the response
	$response = json_encode(
		array(
			'success' => $status,
			'postID' => $id
		)
	);
	// JSON header
	header('Content-type: application/json');
	echo $response;
	die();
}

// Add AJAX actions
add_action('wp_ajax_sell_media_lightbox_remove_ajax', 'sell_media_remove_from_lightbox');
add_action('wp_ajax_nopriv_sell_media_lightbox_remove_ajax', 'sell_media_remove_from_lightbox');

function sell_media_remove_from_lightbox() {
	$blogname = get_site_url();
	$blogname = str_replace( array(':','.','/','-'), '', $blogname);

	// get post ID
	$id = $_POST['id'];
	$cookie_name = '_stockphotography_lightbox_' . $blogname;

	if( isset($_COOKIE[$cookie_name]) ) {

		$cookie=  $_COOKIE[$cookie_name];
		$lightbox=explode(',', $cookie);

		// remove post ID from lightbox cookie
		unset($lightbox[array_search($id, $lightbox)]);
		$lightbox = implode(',', $lightbox);
		$cookie = $lightbox;
		$status = true;

    }
	// set lightbox cookie
	setcookie($cookie_name, $cookie ,time()+3600*24*365,'/');

	// generate the response
	$response = json_encode(
		array(
			'success' => $status,
			'postID' => $id
		)
	);
	// JSON header
	header('Content-type: application/json');
	echo $response;
	die();
}*/

function sell_media_in_lightbox() {
	$blogname = get_site_url();
	$blogname = str_replace( array(':','.','/','-'), '', $blogname);

	global $post;
	$cookie_name = '_stockphotography_lightbox_' . $blogname;
	$lightbox=array();
	if(isset($_COOKIE[$cookie_name])) {
		$cookie= $_COOKIE[$cookie_name];
		$lightbox=explode(',', $cookie);
	}
	if(in_array($post->ID,$lightbox))
		return true;
}

/**
 * Adds the 'sell_media_lightbox' short code to the editor. [sell_media_lightbox]
 *
 * @since 1.9.3
 */
function sell_media_lightbox_shortcode() { ?>

	<script type="text/javascript">
		jQuery(document).ready(function() {
			var lightbox_data = localStorage.getItem( 'sell_media_lightbox_data' );
			//alert(lightbox_data);
		});
	</script>
<?php
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'sell_media_item',
            'meta_key' => 'sell_media_free_downloads',
            'meta_value' => 'on'
        );

        $posts = New WP_Query( $args );
        ob_start(); ?>

        <?php return ob_get_clean();
}
add_shortcode( 'sell_media_lightbox', 'sell_media_lightbox_shortcode' );