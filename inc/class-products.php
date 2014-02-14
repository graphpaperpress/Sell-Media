<?php

Class SellMediaProducts {

	private $settings;

	public function __construct(){
		$this->settings = sell_media_get_plugin_options();
	}


	/**
	 * Retrieves and prints the price of an item
	 *
	 * @since 0.1
	 * @param $post_id The Item ID
	 * @return string
	 */
	function get_original_price( $post_id=null ){
		$price = get_post_meta( $post_id, 'sell_media_price', true );
		// if the item does not have specific price set, use default from settings
		if ( empty( $price ) )
			$price = $this->settings->default_price;
			
		 return $price;
	}

	/**
	 * Get all prices assigned to a product
	 *
	 * @return $prices (array)
	 */
	public function get_prices( $post_id=null ){
		$i = 0;
        if ( $this->settings->hide_original_price !== 'yes' ){
			$original_size = $this->get_original_image_size( $post_id, false );
			$prices[$i]['id'] = 'original';
			$prices[$i]['name'] = __( 'Original', 'sell_media' );
			$prices[$i]['description'] = __( 'The original high resolution source file', 'sell_media' );
			$prices[$i]['price'] = $this->get_original_price( $post_id );
			$prices[$i]['width'] = $original_size['original']['width'];
			$prices[$i]['height'] = $original_size['original']['height'];
		}

		// check assigned price group. We're assuming there is just one.
		$term_parent = wp_get_post_terms( $post_id, 'price-group' );
		$terms = get_terms( 'price-group', array( 'hide_empty' => false, 'parent' => $term_parent[0]->term_id ) );

		// if no assigned price group, get default from settings
		if ( empty( $term_parent ) ){
            $default = $this->settings->default_price_group;
            $terms = get_terms( 'price-group', array( 'hide_empty' => false, 'parent' => $default ) );
        }

        // loop over child terms
        foreach( $terms as $term ){
            if ( $term->parent != 0 ){
				$i++;
				$prices[$i]['id'] = $term->term_id;
				$prices[$i]['name'] = $term->name;
				$prices[$i]['description'] = $term->description;
				$prices[$i]['price'] = sell_media_get_term_meta( $term->term_id, 'price', true );
				$prices[$i]['width'] = sell_media_get_term_meta( $term->term_id, 'width', true );
				$prices[$i]['height'] = sell_media_get_term_meta( $term->term_id, 'height', true );
			}
		}

		return $prices;
	}


	/**
	 * Retrives the lowest price available of an item from the price groups
	 *
	 * @param $post_id (int) The post_id, must be a post type of "sell_media_item"
	 * @return string
	 */
	public function get_lowest_price( $post_id=null ){
	    $prices = $this->get_prices( $post_id );
	    if ( $prices ) foreach ( $prices as $price ){
	    	$lowest_price[] = $price['price'];
	    }

	    return min( $lowest_price );
	}


	/**
	 * Checks if the attachment ID is an image mime type
	 *
	 * @param $attachment_id ID of the attachment
	 * @param $mimetype an array of mimetypes
	 * @return boolean true/false
	 * @since 1.6.9
	 */
	public function mimetype_is_image( $post_id=null, $mimetypes=array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/icon' ) ){
		$attachment_mimetype = get_post_mime_type( $post_id );
		if ( in_array( $attachment_mimetype, $mimetypes ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Prints the original image resolution
	*
	* @since 1.2.4
	* @author Zane Matthew
	*/
	public function get_original_image_size( $post_id=null ){
		// check if attachment is an image
		if ( $this->mimetype_is_image( $post_id ) ) {
			$original_size = wp_get_attachment_image_src( get_post_meta( $post_id, '_sell_media_attachment_id', true ), 'full' );
			return array(
				'original'=> array(
					'height' => $original_size[2],
					'width' => $original_size[1]
				)
			);
		} else {
			return false;
		}
	}

}