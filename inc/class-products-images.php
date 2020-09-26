<?php

/**
 * Product Images Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SellMediaImages extends SellMediaProducts {

	/**
	 * Constructor
	 */
	function __construct() {

		// the IPTC arrays is quite long, so it deserves to be in a separate file
		require_once( dirname( __FILE__ ) . '/libraries/iptc.php' );

		// fires when an attachment post is created
		add_action( 'add_attachment', array( $this, 'add_attachment' ) );
	}


	/**
	 * The add_attachment hook gets triggered when the attachment post is created.
	 * In WordPress media uploads are handled as posts.
	 * We use the hook to parse the source file's IPTC data.
	 * The IPTC data is then saved as custom taxonomy terms.
	 */
	public function add_attachment( $post_id ) {

		// uploaded image is handled as post by WordPress
		$post = get_post( $post_id );

		// original file path
		$original_file = get_attached_file( $post_id );

		if ( file_exists( $original_file ) && wp_attachment_is_image( $post_id ) ) {
			$this->parse_iptc_info( $original_file, $post_id );
		}

		wp_update_post( $post );
	}

	/**
	 * Extracts image metadata from the image specified by its path.
	 *
	 * @param  $original_file the original file path
	 * @param  $post_id the attachment id
	 * @return structured array with all available metadata
	 */
	public function parse_iptc_info( $original_file = null, $post_id = null ) {

		// Check if attachment is image.
		if ( ! wp_attachment_is_image( $post_id ) ) {
			return false;
		}

		$this->metadata = array();

		// extract metadata from file
		//  the $meta variable will be populated with it
		getimagesize( $original_file, $meta );

		// parse iptc
		//  IPTC is stored in the APP13 key of the extracted metadata
		$iptc = null;
		if ( isset( $meta['APP13'] ) ) {
			$iptc = iptcparse( $meta['APP13'] );
		}

		if ( $iptc ) {
			if ( ! isset( $this->IPTC_MAPPING ) || empty( $this->IPTC_MAPPING ) ) {
				include( dirname( __FILE__ ) . '/libraries/iptc.php' );
			}
			// add named copies to all found IPTC items
			foreach ( $iptc as $key => $value ) {
				if ( isset( $this->IPTC_MAPPING[ $key ] ) ) {

					// save IPTC in meta 
					$name = $this->IPTC_MAPPING[ $key ];
					$iptc[ $name ] = $value;

					// save keywords as terms in a custom taxonomy
					if ( '2#025' === $key ) {
						$this->set_terms( $post_id, $value, 'keywords' );
					}

					// save creator as terms in a custom taxonomy
					if ( '2#080' === $key ) {
						$this->set_terms( $post_id, $value, 'creator' );
					}

					// save city as terms in a custom taxonomy
					if ( '2#090' === $key ) {
						$this->set_terms( $post_id, $value, 'city' );
					}

					// save region as terms in a custom taxonomy
					if ( '2#095' === $key ) {
						$this->set_terms( $post_id, $value, 'region' );
					}

					// save country as terms in a custom taxonomy
					if ( '2#101' === $key ) {
						$this->set_terms( $post_id, $value, 'country' );
					}
				}
			}
		}

		if ( $iptc ) {
			$this->metadata['IPTC'] = $iptc;
			add_post_meta( $post_id, '_sell_media_iptc', $this->metadata['IPTC'], true ) or update_post_meta( $post_id, '_sell_media_iptc', $this->metadata['IPTC'] );
		}

		// no need for return but good for testing
		return $this->metadata;
	}

	/**
	 * Update/Saves iptc info as term. Does not check for valid iptc keys!
	 *
	 * @param $post_id, the post_id (the attachment_id)
	 * @param  $terms the keywords or terms
	 * @param  $taxonomy the custom taxonomy
	 * @since 0.1
	 */
	public function set_terms( $post_id = null, $terms = null, $taxonomy = null ) {
		if ( is_null( $taxonomy ) ) {
			return false;
		}

		foreach ( $terms as $term ) {
			$result = wp_set_post_terms( $post_id, $terms, $taxonomy, true );
		}
		return;
	}


	/**
	 * Move the uploaded file into the protected area
	 *
	 * In order to "protect" our uploaded file, we resize the original
	 * file down to the largest WordPress size set in Media Settings.
	 * Then we take the uploaded file and move it to the "protected area".
	 * Last, we copy (rename) our resized uploaded file to be the original
	 * file.
	 *
	 * @param $attachment_id As WordPress sees it in *postmeta table
	 * "_wp_attached_file", i.e., YYYY/MM/file-name.ext
	 * @since 1.0.1
	 */
	public function move_image_from_attachment( $attachment_id = null ) {

		$original_file = get_attached_file( $attachment_id );

		if ( file_exists( $original_file ) ) {

			// Assign the FULL PATH to our destination file.
			$wp_upload_dir = wp_upload_dir();
			//$destination_file = sell_media_get_upload_dir() . $wp_upload_dir['subdir'] . '/' . basename( $original_file );
			$protected_dir = sell_media_get_upload_dir();
			$destination_file = str_replace( $wp_upload_dir['basedir'], $protected_dir, $original_file );

			// Check if the destination directory exists, i.e.
			// wp-content/uploads/sell_media/YYYY/MM if not we create it.
			if ( ! file_exists( dirname( $destination_file ) ) ) {
				wp_mkdir_p( dirname( $destination_file ) );
			}

			/**
			 * Resize original file down to the largest size set in the Media Settings
			 *
			 * Determine which version of WP we are using.
			 * Would rather check if the correct function exists
			 * but the function 'image_make_intermediate_size' uses other
			 * functions that are in trunk and not in 3.4
			 */
			global $wp_version;
			if ( version_compare( $wp_version, '3.5', '>=' ) ) {

				/**
				 * Resize the "original" to our largest size set in the Media Settings.
				 *
				 * This creates a file named filename-[width]x[height].jpg
				 * From here the "original" file is still in our uploads dir, its needed to create
				 * the additional image sizes. Once we're done making the additional sizes, we rename
				 * the filename-[width]x[height].jpg to filename.jpg, thus having a resized "original"
				 * file.
				 */
				$image_new_size = image_make_intermediate_size( $original_file, get_option( 'large_size_w' ), get_option( 'large_size_h' ), false );

				/**
				 * If for some reason the image resize fails we just fall back to the original image.
				 * Example, the image the user is trying to sell is smaller than our "max width".
				 */
				if ( empty( $image_new_size ) ) {
					$resized_image = $original_file;
					$keep_original = true;
				} else {
					$keep_original = false;
					$resized_image = $wp_upload_dir['path'] . '/' . $image_new_size['file'];
				}

				if ( ! file_exists( $destination_file ) ) {

					/**
					 * Move our originally upload file into the protected area
					 */
					copy( $original_file, $destination_file );
					if ( ! $keep_original ) {
						unlink( $original_file );
					}

					/**
					 * We rename our resize original file i.e., "filename-[width]x[height].jpg" located in our uploads directory
					 * to "filename.jpg"
					 */
					$new_path_source = dirname( $original_file ) . '/' . basename( $resized_image );
					$new_path_destination = $original_file;
					copy( $new_path_source, $new_path_destination );
				}

			} else {

				$resized_image = image_resize( $original_file, get_option( 'large_size_w' ), get_option( 'large_size_h' ), false, null, $wp_upload_dir['path'], 90 );
				if ( ! file_exists( $destination_file ) ) {
					// Copy original to our protected area
					@copy( $original_file, $destination_file );

					// Copy (rename) our resized image to the original
					@copy( $resized_image, dirname( $resized_image ) . '/' . basename( $original_file ) );
				}
			}
		}
	}


	/**
	* Prints the original image resolution
	*
	* @param (int)$post_id The post_id to the sell media item
	* @since 1.2.4
	*/
	public function get_original_image_size( $post_id = null, $attachment_id = null ) {

		$original_protected_file = Sell_Media()->products->get_protected_file( $post_id, $attachment_id );

		// check if attachment is an image
		if ( wp_attachment_is_image( $attachment_id ) && '' != $original_protected_file ) {
			list( $width, $height, $type, $attr ) = getimagesize( $original_protected_file );
			return array(
				'original' => array(
					'height' => $height,
					'width' => $width,
				),
			);
		}
	}


	/**
	 * @param $post_id (int) The post to a sell media item post type
	 * @param $term_id (int) The term id for a term from the price-group taxonomy
	 * @param $size_not_available (bool) If true returns and array of unavailable sizes
	 *
	 * @return Array of downloadable sizes or single size if $term_id is present
	 */
	public function get_downloadable_size( $post_id = null, $attachment_id = null, $term_id = null, $size_not_available = false ) {

		$null = null;
		$download_sizes = array();

		/**
		 * Loop over price groups checking for children,
		 * compare the width and height assigned to a price group
		 * with the width and height of the current image. Remove
		 * sizes that are not downloadable.
		 */
		$size_groups = sell_media_get_price_groups( $post_id, 'price-group' );
		if ( ! empty( $size_groups ) ) {

			$image = $this->get_original_image_size( $post_id, $attachment_id );

			foreach ( $size_groups as $size ) {

				/**
				 * Check for children only
				 */
				if ( $size->parent > 0 ) {

					/**
					 * Retrieve the height and width for our price group
					 */
					$pg_width = get_term_meta( $size->term_id, 'width', true );
					$pg_height = get_term_meta( $size->term_id, 'height', true );

					/**
					 * Build our array to be returned, the downloadable width and height
					 * are calculated later and added to this array
					 */
					$download_sizes[ $size->term_id ] = array(
						'name' => $size->name,
					);

					/**
					 * Calculate dimensions and coordinates for a resized image that fits
					 * within a specified width and height. If $crop is true, the largest
					 * matching central portion of the image will be cropped out and resized
					 * to the required size.
					 *
					 * Note we need to pass in $null due to what image_resize_dimensions() returns
					 */
					list(
						$null,
						$null,
						$null,
						$null,
						$download_sizes[ $size->term_id ]['width'],
						$download_sizes[ $size->term_id ]['height']
						) = image_resize_dimensions(
							$image['original']['width'],
							$image['original']['height'],
							$pg_width,
							$pg_height,
							$crop = false
						);

					/**
					 * If no width/height can be determined we remove it from our array of
					 * available download sizes.
					 */
					if ( empty( $download_sizes[ $size->term_id ]['width'] ) ) {
						$unavailable_size[ $size->term_id ] = array(
							'name' => $download_sizes[ $size->term_id ]['name'],
							'height' => $pg_height,
							'width' => $pg_width,
							);
						unset( $download_sizes[ $size->term_id ] );
					}

					/**
					 * Check for portraits and if the available download size is larger than
					 * the original we remove it.
					 */
					$terms = wp_get_post_terms( $post_id, 'price-group' );
					$heights[] = '';
					if ( ! empty( $terms ) ) {
						foreach ( $terms as $term ) {
							if ( 0 !== $term->parent ) {
								$height = get_term_meta( $term->term_id, 'height', true );
								$heights[] = $height;
							}
						}
					}
					$smallest_height = min( $heights );

					/**
					 * Compare the original image size with our array of images sizes from
					 * Price Groups array, removing items that are not available.
					 */
					if ( $image['original']['height'] >= $image['original']['width']
						&& isset( $download_sizes[ $size->term_id ] )
						&& $download_sizes[ $size->term_id ]['height'] <= $smallest_height ) {
							$unavailable_size[ $size->term_id ] = array(
								'name' => $download_sizes[ $size->term_id ]['name'],
								'price' => $download_sizes[ $size->term_id ]['price'],
								'height' => $pg_height,
								'width' => $pg_width,
								);
							unset( $download_sizes[ $price->term_id ] );
					}
				}
			}
		}

		// Returns an array of available and unavailable sizes
		if ( $size_not_available ) {
			$sizes = array(
				'available' => $download_sizes,
				'unavailable' => empty( $unavailable_size ) ? null : $unavailable_size,
				);
		} elseif ( empty( $term_id ) ) {
			// return all available sizes
			$sizes = $download_sizes;
		} else {
			// return available size for a given product
			// Since we no longer check if the image sold is available in the download sizes
			// we allow the buyer to download the original image if the size they purchased
			// is larger than the original image i.e., they can purchase a size they can never
			// download.
			//
			// Hence if they paid for the original, OR they paid for a larger image than
			// available they get the original image.
			$sizes = empty( $download_sizes[ $term_id ] ) ? 'original' : $download_sizes[ $term_id ];
		}

		return $sizes;
	}


	/**
	 * Determines orientation of an image
	 *
	 * @param $post_id (int)
	 * @param $orientation (string) any|landscape|portrait
	 *
	 * @return (bool) true/false
	 */
	public function get_orientation( $post_id = null, $orientation = null ) {

		$attachment_id = get_post_meta( $post_id, '_sell_media_attachment_id', true );
		$meta = wp_get_attachment_metadata( $attachment_id, true );

		if ( ! empty( $meta ) ) {

			if ( empty( $orientation ) || 'any' == $orientation ) {
				return true;
			}

			if ( 'landscape' == $orientation && $meta['height'] < $meta['width'] ) {
				return true;
			}

			if ( 'portrait' == $orientation && $meta['height'] > $meta['width'] ) {
				return true;
			}
		}
	}

}
