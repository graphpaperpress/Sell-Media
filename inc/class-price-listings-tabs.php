<?php
/**
 * Add price group tabs and its contnet.
 *
 * @package Sell Media Price Listings
 */

/**
 * Price listing tabs class.
 */
class Sell_Media_Price_Listings_Tabs {
	/**
	 * Taxonomy name.
	 *
	 * @var string
	 */
	var $taxonomy;
	/**
	 * Current term name.
	 *
	 * @var string
	 */
	var $current_term;

	/**
	 * Class Constructor.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @param array  $args     Args for tab.
	 */
	function __construct( $taxonomy, $args ) {
		$this->taxonomy = $taxonomy;
		$this->tab = $args;
		add_filter( 'sell_media_price_listing_tabs', array( $this, 'add_tab' ) );
		add_action( 'sell_media_price_listings_run', array( $this, 'run' ) );
	}

	function run( $current_tab ) {
		if ( $this->taxonomy !== $current_tab || is_network_admin() ) {
			return;
		}
		$parent_terms = $this->get_terms();

		if ( ! isset( $parent_terms->errors ) ) {

			$array_values = array_values( $parent_terms );
			$first  = ( is_array( $array_values ) ) ? array_shift( $array_values ) : '';
			$first_term_id = isset( $first->term_id ) ? $first->term_id : 0;
			$this->current_term = isset( $_GET['term_parent'] ) ? absint( $_GET['term_parent'] ) : $first_term_id;
			add_action( 'sell_media_pricelists_before_form', array( $this, 'add_pricelist_form' ), 10, 2 );
		}
		add_filter( 'sell_media_price_listings_localize_data', array( $this, 'js_data' ) );
		add_action( 'admin_head', array( $this, 'js_template' ), 25 );
		add_action( 'sell_media_price_listing_save', array( $this, 'save_data' ) );
		add_action( 'sell_meida_load_pricelists_page', array( $this, 'delete_pricelist' ) );
	}

	/**
	 * Add price group tab.
	 *
	 * @param array $tabs Previous tabs.
	 */
	function add_tab( $tabs ) {
		$tabs[ $this->taxonomy ] = $this->tab;
		$tabs[ $this->taxonomy ]['content_callback'] = array( $this, 'tab_content' );
		return $tabs;
	}

	/**
	 * Content for the current tab.
	 *
	 * @return void
	 */
	function tab_content( $current_tab ) {
		include sprintf( '%s/themes/price-listings-tabs-content.php', SELL_MEDIA_PLUGIN_DIR );
	}

	/**
	 * Form for new pricelist.
	 *
	 * @param string $current_tab Current tab.
	 * @param string $url         Form url.
	 */
	function add_pricelist_form( $current_tab, $url ) {
		?>
		<h2 class="tab-title">
            <span><?php echo esc_html( sprintf( __( '%s Pricelists', 'sell_media' ), rtrim( $this->tab['tab_title'], 's' )) ); ?></span>
		</h2>
		<form method="post" action="<?php echo esc_url( $url ); ?>" id="sell-media-new-pricelist-form">
	
		<?php wp_nonce_field( 'sell-media-price-list-page' ); ?>
			<div class="form-group">
				<label><?php esc_html_e( 'Add New Pricelist', 'sell_media' ); ?></label>
				<input type="text" name="new_term_name" required />
				<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Add New Pricelist', 'sell_media' ); ?>" />
				<input type="hidden" name="sell-media-price-list-submit" value="true" />
			</div>			
		</form>
		<?php
	}
	/**
	 * Create data for javascript.
	 *
	 * @param  array $js_data Previous js data.
	 * @return array          New js data.
	 */
	function js_data( $js_data ) {
		$js_data[ $this->taxonomy ] = array();
		if ( 'new' !== $this->current_term ) {
			$download_parents = $this->get_terms( array( 'child_of' => (int) $this->current_term, 'parent' => '' ) );
			$_terms = array();
			$index = 0;
			if ( ! empty( $download_parents ) ) {
				foreach ( $download_parents as $key => $value ) {
					if ( is_object( $value ) && (int) $this->current_term === $value->parent ) {
						$_terms[ $index ] = $value;
						$_terms[ $index ]->index = $index;
						$_terms[ $index ]->meta = get_term_meta( $value->term_id, '', true );
						$index++;
					}
				}
			}
			$js_data[ $this->taxonomy ] = $_terms;
		}
		return $js_data;
	}

	/**
	 * Get terms.
	 *
	 * @param  array $args Arguments for terms.
	 * @return array       Terms.
	 */
	function get_terms( $args = array() ) {
		$default_args = array(
			'hide_empty' => false,
			'parent' => 0,
			'taxonomy' => $this->taxonomy,
			'orderby' => 'id',
		);
		$args = wp_parse_args( $args, $default_args );
		return get_terms( $args );
	}

	/**
	 * Javascript template for listing price.
	 *
	 * @return void
	 */
	function js_template() {
	?>

	    <script type="text/html" id="tmpl-sm-download-group-post">
				<#
				_.each ( data, function( value ){
					var term_id = typeof(value.term_id)!== 'undefined' ?  value.term_id : 'new';
					var title = typeof(value.name)!== 'undefined' ?  value.name : '';
					var description = typeof(value.description)!== 'undefined' ?  value.description : '';
					var field_name = '' !== title ?  'terms_children['+term_id+']' : 'new_children['+value.index+']';
					var width = '';
					var height = '';
					var price = '';
					if( 'undefined' !== typeof(value.meta) ){
						var width = typeof(value.meta.width)!== 'undefined' ?  value.meta.width : '';
						var height = typeof(value.meta.height)!== 'undefined' ?  value.meta.height : '';
						var price = typeof(value.meta.price)!== 'undefined' ?  value.meta.price : '';
						var is_default = ( typeof(value.meta.default)!== 'undefined' && 1 == value.meta.default )  ?  true : false;
					}
					var alert_message = '' !== title ?  "<?php echo esc_js( 'Are you sure you want to delete the price: ', 'sell_media' ); ?>" + value.name + '?' : '<?php echo esc_js( 'Are you sure you want to delete this price? ', 'sell_media' ); ?>';
				#>
				<tr id="_row-data-{{value.index}}" data-index="{{value.index}}">
					<td>
						<#
						var input_type = 'text';
						if( true === is_default ){
							var input_type = 'hidden';
						#>
						{{title}}
						<# } #>
						<input type="{{input_type}}" class="" name="{{field_name}}[name]" size="24" value="{{title}}" required>
					</td>
					<td>
						<#
						if( true === is_default ){
						#>
						{{description}}
						<# } #>
						<input type="{{input_type}}" class="" name="{{field_name}}[description]" size="24" value="{{description}}">
					</td>
					<td>
						<#
						if( true === is_default ){
						#>
						{{width}}
						<# } #>
						<input type="{{input_type}}" class="small-text" name="{{field_name}}[width]" value="{{width}}" data-parsley-type="number" required>
					</td>
					<td>
						<#
						if( true === is_default ){
						#>
						{{height}}
						<# } #>
						<input type="{{input_type}}" class="small-text" name="{{field_name}}[height]" value="{{height}}" data-parsley-type="number" required>
					</td>
					<td>
						<input type="number" class="small-text" name="{{field_name}}[price]" value="{{price}}" required step="0.01" min="0" placeholder="0.00">
					</td>
					<td>
						<a href="#" class="sell-media-xit sell-media-price-group-delete-term" data-taxonomy="price-group" data-termid="{{term_id}}" data-type="price" data-message="{{alert_message}}"  data-index="{{value.index}}">×</a>
					</td>
				</tr>
				<# } ) #>

	    </script>
	<?php }

	function save_data( $redirect_url ) {

		if( !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'],'sell-media-price-list-page') ) {
			wp_die( esc_html("Unauthorized Access"));
		}
		// Save new pricelist.
		if ( isset( $_POST['new_term_name'] ) && '' !== sanitize_text_field( $_POST['new_term_name'] ) ) {
			if ( ! term_exists( sanitize_text_field($_POST['new_term_name']), $this->taxonomy ) ) {
				$term = wp_insert_term( sanitize_text_field($_POST['new_term_name']), $this->taxonomy );
				$parent_term_id = $term['term_id'];
			} else {
			    $gpp_tmp = sanitize_text_field( $_POST['new_term_name'] );
				wp_die( wp_kses( "Pricelist <strong>{$gpp_tmp}</strong> already exists!", array( 'strong' => array() ) ) );
			}
		} 
		else {
			// Update pricelists.
			$parent_term_id = ( isset( $_POST['term_id'] ) && '' != sanitize_text_field( $_POST['term_id'] ) ) ? (int) $_POST['term_id']: 0;
			if ( isset( $_POST['term_name'] ) && '' !== sanitize_text_field( $_POST['term_name'] ) ) {
				if ( 0 !== $parent_term_id ) {
					wp_update_term( $parent_term_id, $this->taxonomy, array(
						'name' => sanitize_text_field($_POST['term_name']),
					));
				}
			}

			if ( isset( $_POST['terms_children'] ) && is_array( $_POST['terms_children'] ) && count( $_POST['terms_children'] ) ) {
				foreach ( (array) $_POST['terms_children'] as $term_id => $data ) {
					$term_id = (int) $term_id;
					if ( isset($data['name']) && '' !== sanitize_text_field($data['name']) ) {
						wp_update_term( $term_id, $this->taxonomy, array(
							'name' => __(sanitize_text_field($data['name']), 'sell_media' ),
							'description' => isset($data['description']) ? __(sanitize_text_field($data['description']), 'sell_media' ) : '',
						));
						if(isset($data['width'])) {
							update_term_meta( $term_id, 'width', sanitize_text_field( $data['width'] ) );
						}
						if(isset($data['height'])) {
							update_term_meta( $term_id, 'height', sanitize_text_field( $data['height'] ) );
						}
						if(isset($data['price'])) {
							update_term_meta( $term_id, 'price', sanitize_text_field( $data['price'] ) );
						}
					}
				}
			}

			if ( isset( $_POST['deleted_term_ids'] ) && '' !== sanitize_text_field( $_POST['deleted_term_ids'] ) ) {
				$deleted_term_ids = explode( ',', sanitize_text_field($_POST['deleted_term_ids']) );
				if ( ! empty( $deleted_term_ids ) ) {
					foreach ( $deleted_term_ids as $key => $term_id ) {
						if ( 'new' !== $term_id ) {
							wp_delete_term( (int) $term_id, $this->taxonomy );
						}
					}
				}
			}

			if ( isset( $_POST['new_children'] ) && is_array( $_POST['new_children'] ) && count( $_POST['new_children'] ) ) {
				foreach ( (array) $_POST['new_children'] as $term_id => $data ) {
					if ( '' !== sanitize_text_field($data['name']) ) {

						if ( term_exists( sanitize_text_field($data['name']), $this->taxonomy, $parent_term_id ) ) {
							$term    = get_term_by( 'name', __(sanitize_text_field($data['name']), 'sell_media' ), $this->taxonomy );
							$term_id = ( isset( $term->term_id ) ) ? intval($term->term_id) : false;

						} else {

							$term    = wp_insert_term( sanitize_text_field($data['name']), $this->taxonomy, array(
								'parent' => (int) $parent_term_id,
								'description' => __( sanitize_text_field($data['description']), 'sell_media' ),
							) );
							$term_id = ! is_wp_error( $term ) ? $term['term_id'] : false;
						}

						if ( $term_id ) {
							if(isset($data['width'])) {
								update_term_meta( $term_id, 'width', sanitize_text_field( $data['width'] ) );
							}
							if(isset($data['height'])) {
								update_term_meta( $term_id, 'height', sanitize_text_field( $data['height'] ) );
							}
							if(isset($data['price'])) {
								update_term_meta( $term_id, 'price', sanitize_text_field( $data['price'] ) );
							}
						}
					}
				}
			}			
		}

		// Saving settings.
		if ( isset( $_POST['settings'] ) && is_array($_POST['settings']) && count($_POST['settings']) ){

			$settings = array_map( 'sanitize_text_field', ( array ) sell_media_get_plugin_options() );

			$sanitized_settings_from_post = [];
			// TODO: move to helpers file
			$gpp_allowed_keys = [
				'admin_columns',
				'watermark_attachment_url',
				'watermark_attachment_id',
				'watermark_all',
				'free_downloads_api_key',
				'free_downloads_list',
				'mailchimp_api_key',
				'mailchimp_list',
				'reprints_hide_download_tabs',
				'reprints_base_region',
				'reprints_unit_measurement',
				'default_price',
				'hide_original_price',
				'default_price_group',
				'test_mode',
				'checkout_page',
				'thanks_page',
				'dashboard_page',
				'login_page',
				'search_page',
				'lightbox_page',
				'customer_notification',
				'style',
				'layout',
				'thumbnail_crop',
				'thumbnail_layout',
				'titles',
				'breadcrumbs',
				'quick_view',
				'file_info',
				'search_relation',
				'plugin_credit',
				'post_type_slug',
				'order_by',
				'terms_and_conditions',
				'admin_columns',
				'default_price',
				'hide_original_price',
				'default_price_group',
				'currency',
				'paypal_additional_test_email',
				'paypal_test_client_id',
				'paypal_test_client_secret_key',
				'paypal_live_client_id',
				'paypal_live_client_secret_key',
				'tax',
				'tax_display',
				'tax_rate',
				'from_name',
				'from_email',
				'success_email_subject',
				'success_email_body',
				'misc'
			];
			// Process only safe keys from $_POST
			foreach ( $_POST['settings'] as $tmp_key => $tmp_val ) {
                if( in_array($tmp_key, $gpp_allowed_keys)){
	                $sanitized_settings_from_post[$tmp_key] = sanitize_text_field($tmp_val);
                }
			}

			$settings = array_merge( $settings, $sanitized_settings_from_post );
			$options_name = sell_media_get_current_plugin_id() . '_options';
			update_option( $options_name, $settings );
		}

		$url_parameters['term_parent'] = $parent_term_id;
		$redirect_url = add_query_arg( $url_parameters, $redirect_url );
		wp_redirect( $redirect_url );
		//exit(); // TODO: look, this will break the form layout
	}

	function delete_pricelist( $redirect_url ) {
		// Check if request is for delete and parent term is set.
		if ( ! isset( $_GET['delete'] ) || '1' !== $_GET['delete'] || ! isset( $_GET['term_parent'] ) || '' === $_GET['term_parent'] || !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'],'sell-media-price-list-page') ) {
			return;
		}

		// Check valid nonce.
		check_admin_referer( 'delete_pricelist_nonce_action', 'delete_pricelist_nonce_name' );

		$term_parent = absint( $_GET['term_parent'] );
		$taxonomy = ( isset( $_GET['tab'] ) && '' !== $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : $this->taxonomy;
		$child_terms = get_term_children( $term_parent, $taxonomy );
		wp_delete_term( (int) $term_parent, $taxonomy );

		// Delete its child terms.
		if ( ! empty( $child_terms ) ) {
			foreach ( $child_terms as $key => $term_id ) {
				wp_delete_term( (int) $term_id, $taxonomy );
			}
		}

		$redirect_url = add_query_arg( array( 'page' =>$_GET['page'], 'tab' => $taxonomy ), $redirect_url );
		wp_redirect( $redirect_url );
		exit;
	}
}

/**
 * Init price lisiting tabs.
 *
 * @return void
 */
function sell_media_init_price_listings_tabs() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	if ( is_plugin_active( 'sell-media-reprints/sell-media-reprints-self-fulfillment.php' ) ) {
		$print_price_group = new Sell_Media_Price_Listings_Tabs( 'reprints-price-group', array(
			'tab_title' => __( 'Prints', 'sell_media' ),
		) );
	}

	// TODO: remove this unused variable
	$download_price_group = new Sell_Media_Price_Listings_Tabs( 'price-group', array(
		'tab_title' => __( 'Downloads', 'sell_media' ),
	) );

}

add_action( 'init', 'sell_media_init_price_listings_tabs', 9 );

/**
 * Default pricelists.
 *
 * @return array Default Price lists.
 */
function sell_media_get_default_pricelists() {
	$pricelists = array(
		'sm-download-default' => array(
			'title' => __( 'Default', 'sell_media' ),
			'taxonomy' => 'price-group',
			'childrens' => array(
				'sm-download-default-small' => array(
					'title' => __( 'Small', 'sell_media' ),
					'description' => __( '1000 pixels max size', 'sell_media' ),
					'meta' => array(
						'width' => 1000,
						'height' => 1000,
						'price' => '10.00',
					),
				),
				'sm-download-default-medium' => array(
					'title' => __( 'Medium', 'sell_media' ),
					'description' => __( '2000 pixels max size', 'sell_media' ),
					'meta' => array(
						'width' => 2000,
						'height' => 2000,
						'price' => '20.00',
					),
				),
				'sm-download-default-large' => array(
					'title' => __( 'Large', 'sell_media' ),
					'description' => __( '4000 pixels max size', 'sell_media' ),
					'meta' => array(
						'width' => 4000,
						'height' => 4000,
						'price' => '40.00',
					),
				),
			),
		),

		// Reprint.
		'sm-reprints-default' => array(
			'title' => __( 'Default', 'sell_media' ),
			'taxonomy' => 'reprints-price-group',
			'childrens' => array(
				'sm-reprint-default-4-6' => array(
					'title' => __( '4x6', 'sell_media' ),
					'description' => __( 'Color 4x6 print', 'sell_media' ),
					'meta' => array(
						'width' => 1200, // 4 inch * 300 dpi
						'height' => 1800, // 6 inch * 300 dpi
						'price' => '10.00',
					),
				),
				'sm-reprint-default-8-12' => array(
					'title' => __( '8x12', 'sell_media' ),
					'description' => __( 'Color 8x12 print', 'sell_media' ),
					'meta' => array(
						'width' => 2400, // 8 inch * 300 dpi
						'height' => 3600, // 12 inch * 300 dpi
						'price' => '20.00',
					),
				),
				'sm-reprint-default-16-24' => array(
					'title' => __( '16x24', 'sell_media' ),
					'description' => __( 'Color 16x24 print', 'sell_media' ),
					'meta' => array(
						'width' => 4800, // 16 inch * 300 dpi
						'height' => 7200, // 24 inch * 300 dpi
						'price' => '40.00',
					),
				),
			),
		),
	);
	return $pricelists;
}

/**
 * Default pricelist set function.
 *
 * @param array $pricelists Default pricelists.
 */
function sell_media_set_default_pricelist( $pricelists ) {
	if ( ! empty( $pricelists ) ) {
		foreach ( $pricelists as $key => $value ) {
			$check_if_default_exists = get_option( 'sell_media_default_pricelists_saved_' . $value['taxonomy'] );
			if ( taxonomy_exists( $value['taxonomy'] ) && false === $check_if_default_exists  ) {
				$term = term_exists( $key, $value['taxonomy'] );
				if ( ! $term ) {
					$term_insert = wp_insert_term( $value['title'],  $value['taxonomy'], array(
						'slug' => $key,
					));
					if ( ! empty( $value['childrens'] ) ) {
						foreach ( $value['childrens'] as $children_key => $children ) {
							$children_term_insert = wp_insert_term( sanitize_text_field( $children['title'] ),  sanitize_text_field( $value['taxonomy'] ), array(
								'slug' => $children_key,
								'description' => $children['description'],
								'parent' => $term_insert['term_id'],
							));
							$data = $children['meta'];
							update_term_meta( sanitize_text_field( $children_term_insert['term_id'] ), 'width', sanitize_text_field( $data['width'] ) );
							update_term_meta( sanitize_text_field( $children_term_insert['term_id'] ), 'height', sanitize_text_field( $data['height'] ) );
							update_term_meta( sanitize_text_field( $children_term_insert['term_id'] ), 'price', sanitize_text_field( $data['price'] ) );
							update_term_meta( $children_term_insert['term_id'], 'default', true );
						}
					}
				}
				update_option( 'sell_media_default_pricelists_saved_' . sanitize_text_field( $value['taxonomy'] ), true );
			}
		}
	}
}

/**
 * Set default pricelists.
 */
function sell_media_set_default_pricelists() {
	$pricelists = sell_media_get_default_pricelists();
	sell_media_set_default_pricelist( $pricelists );
}

add_action( 'init', 'sell_media_set_default_pricelists', 11 );