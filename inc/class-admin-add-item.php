<?php
/**
 * Add New Items Admin Page
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sell Media add item tabs.
 */
class SellMediaAdminAddItem {

	/**
	 * Sell Media setting.
	 *
	 * @var object
	 */
	private $settings;

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->settings = sell_media_get_plugin_options();

		add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ), 999 );

		add_action( 'post-plupload-upload-ui', array( $this, 'append_media_upload_form' )  , 1 );
		add_action( 'post-html-upload-ui', array( $this, 'append_media_upload_form' )  , 1 );
		add_action( 'wp_ajax_sell_media_upload_gallery_load_image', array( $this, 'gallery_load_image' ) );
		add_action( 'wp_ajax_sell_media_load_pricelists', array( $this, 'load_pricelists' ) );
	}

	/**
	 * Register new metaboxes.
	 *
	 * @return void
	 */
	public function register_metaboxes() {
		add_meta_box( 'sell-media-main-container', __( 'Sell Media', 'sell_media' ), array( $this, 'main_container' ), 'sell_media_item', 'normal', 'high' );
		remove_meta_box( 'files_meta_box', 'sell_media_item', 'normal' );
		remove_meta_box( 'stats_meta_box', 'sell_media_item', 'normal' );
		remove_meta_box( 'options_meta_box', 'sell_media_item', 'normal' );
		remove_meta_box( 'licensesdiv', 'sell_media_item', 'side' );
		remove_meta_box( 'collectiondiv', 'sell_media_item', 'side' );
		// remove_meta_box( 'postimagediv', 'sell_media_item', 'side' );
		remove_meta_box( 'authordiv', 'sell_media_item', 'normal' );
		remove_meta_box( 'creatordiv', 'sell_media_item', 'side' );
		remove_action( 'edit_form_advanced', 'sell_media_editor' );
	}

	/**
	 * Main container call back.
	 *
	 * @return void
	 */
	public function main_container() {
		global $post;
		wp_enqueue_script( 'jquery-ui-tabs' );
		include sprintf( '%s/themes/admin-add-item-main-container.php', untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) );
	}

	/**
	 * Tabs for add item page.
	 *
	 * @return array Tabs array.
	 */
	public function get_tabs() {
		$tabs['file_upload'] = array(
			'tab_label' => __( 'File Upload', 'sell_media' ),
			'content_title' => __( 'File Upload', 'sell_media' ),
			'content_callback' => array( $this, 'file_upload_callback' ),
		);

		$tabs['description'] = array(
			'tab_label' => __( 'Description', 'sell_media' ),
			'content_title' => __( 'Description', 'sell_media' ),
			'content_callback' => array( $this, 'description_callback' ),
		);

		$tabs['price'] = array(
			'tab_label' => __( 'Price', 'sell_media' ),
			'content_title' => __( 'Price', 'sell_media' ),
			'content_callback' => array( $this, 'price_callback' ),
		);

		$tabs['stats'] = array(
			'tab_label' => __( 'Stats', 'sell_media' ),
			'content_title' => __( 'Stats', 'sell_media' ),
			'content_callback' => array( $this, 'stats_callback' ),
		);

		$tabs['advanced'] = array(
			'tab_label' => __( 'Advanced', 'sell_media' ),
			'content_title' => __( 'Advanced Options', 'sell_media' ),
			'content_callback' => array( $this, 'advanced_options_callback' ),
		);

		return apply_filters( 'sell_media_admin_new_item_tabs', $tabs );
	}

	/**
	 * File upload tab content.
	 *
	 * @param  object $post Post object.
	 * @return void
	 */
	function file_upload_callback( $post ) {
		sell_media_uploader_meta_box( $post );
	}

	function append_media_upload_form() {
		?>
		<!-- Progress Bar -->
		<div class="sell-media-upload-progress-bar">
				<div class="sell-media-upload-progress-bar-inner"></div>
				<div class="sell-media-upload-progress-bar-status">
					<span class="uploading">
						<?php esc_html_e( 'Uploading Image', 'sell_media' ); ?>
						<span class="current">1</span>
						<?php esc_html_e( 'of', 'sell_media' ); ?>
						<span class="total">3</span>
					</span>
					<span class="done"><?php esc_html_e( 'All images uploaded.', 'sell_media' ); ?></span>
				</div>
		</div>
		<?php
	}

	function gallery_load_image() {
		// Run a security check first.
		check_ajax_referer( 'sell-media-drag-drop-nonce', 'nonce' );
		// Prepare variables.
		$id  = absint( $_POST['id'] );
		echo sell_media_list_uploads( $id );
		exit;
	}

	/**
	 * Description tab content.
	 *
	 * @param  object $post Post object.
	 * @return void
	 */
	function description_callback( $post ) {
		sell_media_editor( $post );
	}

	/**
	 * Price tab content.
	 *
	 * @param  object $post Post object.
	 * @return void
	 */
	function price_callback( $post ) {
		sell_media_options_meta_box( $post );
	}

	/**
	 * Stat tab content.
	 *
	 * @param  object $post Post object.
	 * @return void
	 */
	function stats_callback( $post ) {
		sell_media_stats_meta_box( $post );
	}

	/**
	 * Advance options tab content.
	 *
	 * @param  object $post Post object.
	 * @return void
	 */
	function advanced_options_callback( $post ) {

		$obj = get_post_type_object( 'sell_media_item' );

		echo '<div id="sell-media-advanced-options-container">';

		// do_action( 'sell_media_add_item_tab_before_content_callback', 'advanced', $post );

			echo '<div id="sell-media-tax-collections" class="sell-media-tax-wrap">';
				printf( '<h3 class="tax-title">%s</h3>', esc_html__( 'Collections', 'sell_media' ) );
				printf( '<p class="tax-description description">%1$s %2$s %3$s.</p>', esc_html__( 'Assign this', 'sell_media' ), strtolower( $obj->labels->singular_name ), esc_html__( 'to a collection (optional). Archive pages are automatically created for each collection and can be accessed by adding /collection/name-of-collection/ to the end of your website url (replace "name-of-collection" with the url-friendly collection name)', 'sell_media' ) );
				post_categories_meta_box( $post, array( 'args' => array( 'taxonomy' => 'collection' ) ) );
				printf( '<div class="tax-edit"><a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=collection&post_type=sell_media_item' ) ) . '">%s</a></div>', esc_html__( 'Edit All Collections', 'sell_media' ) );
			echo '</div>';

			echo '<div id="sell-media-tax-licenses" class="sell-media-tax-wrap">';
				printf( '<h3 class="tax-title">%s</h3>', esc_html__( 'Licenses', 'sell_media' ) );
				printf( '<p class="tax-description description">%s.</p>', esc_html__( 'Select the available usage licenses that buyers can choose from when purchasing (optional). Licenses can be assigned "markup" which will increase the cost of the item being purchase. For example, you can might have a "Personal" usage license with no markup from your base pricelists and a "Commercial" usage license with 50% markup from your base pricelists', 'sell_media' ) );
				post_categories_meta_box( $post, array( 'args' => array( 'taxonomy' => 'licenses' ) ) );
				printf( '<div class="tax-edit"><a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=licenses&post_type=sell_media_item' ) ) . '">%s</a></div>', esc_html__( 'Edit All Licenses', 'sell_media' ) );
			echo '</div>';

			echo '<div id="sell-media-tax-creators" class="sell-media-tax-wrap">';
				printf( '<h3 class="tax-title">%s</h3>', esc_html__( 'Creators', 'sell_media' ) );
				printf( '<p class="tax-description description">%s.</p>', esc_html__( 'Assign a creator (optional). Creators are also automatically imported from the "Credit" IPCT metadata field in the files that you upload. Archive pages are then automatically created for each creator and can be accessed by adding /creator/name-of-creator/ to the end of your website url (replace "name-of-creator" with the url-friendly name)', 'sell_media' ) );
				post_categories_meta_box( $post, array( 'args' => array( 'taxonomy' => 'creator' ) ) );
				// post_tags_meta_box( $post, array( 'args' => array( 'taxonomy' => 'creator' ) ) );
				printf( '<div class="tax-edit"><a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=creator&post_type=sell_media_item' ) ) . '">%s</a></div>', esc_html__( 'Edit All Creators', 'sell_media' ) );
			echo '</div>';

		echo '</div>';
	}

	/**
	 * Load pricelist on add/ edit item.
	 *
	 * @return void
	 */
	function load_pricelists() {
		$id = ( isset( $_POST['parent_id'] ) && '' !== $_POST['parent_id'] ) ? absint( $_POST['parent_id'] ) :  false;
		if ( ! $id ) {
			echo 0;
			exit;
		}

		$terms = get_terms( 'price-group', array( 'hide_empty' => false, 'parent' => $id ) );
		echo '<div id="sell-media-display-pricelists">';
		if ( $terms ) {
			$parent_term = get_term( $id );
			echo '<table class="form-table">';
			echo '<tr>';
				echo '<th>' . __( 'Name', 'sell_media' ) . '</th>';
				echo '<th>' . __( 'Width', 'sell_media' ) . '</th>';
				echo '<th>' . __( 'Height', 'sell_media' ) . '</th>';
				echo '<th>' . __( 'Price', 'sell_media' ) . '</th>';
			echo '</tr>';
			foreach ( $terms as $key => $term ) {
				$term_meta = get_term_meta( $term->term_id );
				echo '<tr>';
					echo '<td>';
						echo $term->name;
					echo '</td>';
					echo '<td>';
						echo ( isset( $term_meta['width'][0] ) ? $term_meta['width'][0] : ''  );
					echo '</td>';
					echo '<td>';
						echo ( isset( $term_meta['height'][0] ) ? $term_meta['height'][0] : ''  );
					echo '</td>';
					echo '<td>';
						echo ( isset( $term_meta['price'][0] ) ? sell_media_get_currency_symbol() . $term_meta['price'][0] : ''  );
					echo '</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
		else{
			echo '<span class="desc">' . __( 'No Pricelist found.', 'sell_media' ) . '</span>';
		}
		echo '</div>';
		exit;
	}
}
