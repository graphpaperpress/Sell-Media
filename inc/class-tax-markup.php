<?php
/**
 * Add markup (%) on tax.
 *
 * @package Sell media
 */

/**
 * Markup class.
 */
class SellMediaTaxMarkup {

	/**
	 * Constructor.
	 */
	function __construct() {
		add_action( 'admin_init', array( $this, 'apply_markup' ) );
		add_action( 'sell_media_cart_above_licenses', array( $this, 'item_detail_markup_fields' ) );

		// Add to cart fields to add markup item data.
		add_action( 'sell_media_cart_add_markup_inputs', array( $this, 'tax_markup_add_cart_input' ) );

		// Add Markup attr in add to cart items.
		add_filter( 'sell_media_cart_item_attrs', array( $this, 'tax_markup_cart_item_attrs' ) );
	}

	/**
	 * Taxonomies in which markup is to be added.
	 */
	function markup_taxonomies() {
		$taxonomies = array(
			'licenses',
		);

		return apply_filters( 'sell_media_markup_taxonomies', $taxonomies );
	}

	/**
	 * Apply markup to assigned tax.
	 */
	function apply_markup() {
		$markup_taxonomy = $this->markup_taxonomies();
		if ( ! empty( $markup_taxonomy ) ) {
			foreach ( $markup_taxonomy as $tax ) {
				$this->add( $tax );
			}
		}
	}

	/**
	 * Add Markups
	 *
	 * @param String $taxonmy Tax Name.
	 *
	 * @return void
	 */
	function add( $taxonmy ) {
		if ( ! $taxonmy ) {
			return;
		}
		if ( ! taxonomy_exists( $taxonmy ) ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-slider' );
		// Add markup files in taxonomy.
		add_action( "{$taxonmy}_add_form_fields", array( $this, 'add_custom_term_form_fields' ) );
		add_action( "{$taxonmy}_edit_form_fields", array( $this, 'edit_custom_term_form_fields' ), 10, 2 );

		// Manage Columns.
		add_filter( "manage_edit-{$taxonmy}_columns", array( $this, 'custom_tax_columns_headers' ) );
		add_filter( "manage_{$taxonmy}_custom_column", array( $this, 'custom_license_columns_content' ), 10, 3 );

		// save tax meta.
		add_action( "create_{$taxonmy}", 'sell_media_save_extra_taxonomy_fields' );
		add_action( "edited_{$taxonmy}", 'sell_media_save_extra_taxonomy_fields' );
	}

	/**
	 * Add form fields to add terms page for our custom taxonomies.
	 *
	 * @param string $tag Taxonomy name.
	 */
	function add_custom_term_form_fields( $tag ) {
		$tax_name = $tag;

		if ( is_object( $tag ) ) {
			$term_id  = $tag->term_id;
			$tax_name = $tag->name;
		} else {
			$term_id = null;
		}
		$taxonomy_details = get_taxonomy( $tax_name );
		?>
        <div class="form-field">
            <label for="markup"><?php esc_html_e( 'Markup', 'sell_media' ); ?></label>
			<?php $this->the_markup_slider( $taxonomy_details ); ?>
        </div>
        <div class="form-field">
			<?php $this->the_default_checkbox( $taxonomy_details, $term_id ); ?>
        </div>
		<?php wp_nonce_field( 'sell_media_taxonomy_admin_nonce', 'taxonomy_wpnonce' );
	}

	/**
	 * Function for building the slider on Add/Edit License admin page.
	 *
	 * @param object $taxonomy_details Taxonomy detials.
	 */
	function the_markup_slider( $taxonomy_details ) {
		if ( isset( $_GET['tag_ID'] ) ) {
			$term_id = intval( $_GET['tag_ID'] );
		} else {
			$term_id = null;
		}

		if ( get_term_meta( $term_id, 'markup', true ) !== false ) {
			$initial_markup = str_replace( '%', '', get_term_meta( $term_id, 'markup', true ) );
		} else {
			$initial_markup = 0;
		}

		$singular_name = isset( $taxonomy_details->labels->singular_name ) ? $taxonomy_details->labels->singular_name : $taxonomy_details->name;
		$settings      = sell_media_get_plugin_options(); ?>
        <script>
            function calc_price(markUp) {

                var price = <?php echo (float) esc_js( $settings->default_price ); ?>;
                if (markUp == undefined)
                    var markUp = <?php echo (float) esc_js( $initial_markup ); ?>;

                finalPrice = (price + (markUp * 0.01) * price);
                finalPrice = finalPrice.toFixed(2);

                return finalPrice;
            }

            jQuery(document).ready(function ($) {
                document.querySelector('.menu-cart-total').innerHTML = calc_price();
            });

            function updateSlider(slideAmount) {
                document.querySelector('input.markup-target').value = slideAmount + '%';
                document.querySelector('span.markup-target').innerHTML = slideAmount + '%';
                document.querySelector('.menu-cart-total').innerHTML = calc_price(slideAmount);
            }

            jQuery(document).ajaxComplete(function( event, xhr, settings ){
				if( ~settings.data.indexOf('action=add-tag') ) {
					jQuery('body.post-type-sell_media_item [name="meta_value[default]"]').prop('checked', false);
					jQuery('#slide').val( 0 );
					updateSlider( 0 );
				}
            });
        </script>
        <div class="sell_media-slider-container">
            <input id="slide" type="range" min="-100" max="1000" step=".1" value="<?php echo esc_attr( $initial_markup ); ?>" oninput="updateSlider(this.value)">
            <div class="sell_media-price-container">
                <input name="meta_value[markup]" class="markup-target" type="text" value="<?php echo (float) $initial_markup; ?>" size="40"/>
            </div>
            <p class="description">
				<?php echo wp_kses( sprintf( __( 'Increase the price of a item if a buyer selects this %s by dragging the slider above.', 'sell_media' ), $singular_name ), [ 'a' => [ 'href' => true, 'target' => true ] ] ); ?><?php
				if ( get_term_meta( $term_id, 'markup', true ) ) {
					$default_markup = get_term_meta( $term_id, 'markup', true );
				} else {
					$default_markup = '0%';
				}

				if ( $settings->default_price ) {
					$price = sell_media_get_currency_symbol() . $settings->default_price;
				} else {
					$price = __( 'you have not set a default price', 'sell_media' );
				}

				printf( __( ' The %1$s of %2$s with %3$s markup is %4$s', 'sell_media' ), '<a href="' . admin_url() . 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_general_settings
					">default item price</a>', '<strong>' . $price . '</strong>', '<strong><span class="markup-target">' . $default_markup . '</span></strong>', '<strong>' . sell_media_get_currency_symbol() . '<span class="menu-cart-total"></span></strong>' );
				?>
            </p>
        </div>
		<?php wp_nonce_field( 'sell_media_taxonomy_admin_nonce', 'taxonomy_wpnonce' );
	}

	/**
	 * Prints the checkbox for the default license type.
	 *
	 * @param int $term_id Term ID.
	 * @param object $taxonomy_details Taxonomy Details.
	 */
	function the_default_checkbox( $taxonomy_details, $term_id = null ) {
		$singular_name = isset( $taxonomy_details->labels->singular_name ) ? $taxonomy_details->labels->singular_name : $taxonomy_details->name;

		$title = sprintf( __( 'Add as default %s?', 'sell_media' ), $singular_name );
		$desc  = sprintf( __( 'Check to add this as a default %s option for all newly created items.', 'sell_media' ), $singular_name );

		?>
        <tr class="form-field sell_media-markup-container">
            <th scope="row" valign="top">
                <label for="markup"><?php esc_html_e( $title, 'sell_media' ); ?></label>
            </th>
            <td>
                <input name="meta_value[default]" style="width: auto;" id="meta_value[default]" type="checkbox" <?php checked( get_term_meta( $term_id, 'default', true ), 'on' ); ?> size="40"/> <span class="description"><label for="meta_value[default]"><?php esc_html_e( $desc, 'sell_media' ); ?></label></span>
            </td>
        </tr>
		<?php
	}

	/**
	 * Edit form fields to edit terms page for our custom taxonomies.
	 *
	 * @param object $tag Tax.
	 * @param string $taxonomy Taxonomy.
	 */
	function edit_custom_term_form_fields( $tag, $taxonomy ) {
		if ( is_object( $tag ) ) {
			$term_id = intval( $tag->term_id );
		} else {
			$term_id = null;
		}
		$taxonomy_details = get_taxonomy( $taxonomy );
		?>
        <tr class="form-field sell_media-markup-container">
            <th scope="row" valign="top">
                <label for="markup"><?php esc_html_e( 'Markup', 'sell_media' ); ?></label>
            </th>
            <td>
				<?php $this->the_markup_slider( $taxonomy_details ); ?>
            </td>
        </tr>
        <tr class="form-field sell_media-markup-container">
            <td><?php $this->the_default_checkbox( $taxonomy_details, $term_id ); ?></td>
        </tr>
		<?php
	}

	/**
	 * Display Custom License Column Headers in wp-admin.
	 *
	 * @param array $columns Columns.
	 */
	function custom_tax_columns_headers( $columns ) {
		$columns_local = array();

		if ( isset( $columns['cb'] ) ) {
			$columns_local['cb'] = $columns['cb'];
			unset( $columns['cb'] );
		}

		if ( isset( $columns['name'] ) ) {
			$columns_local['name'] = $columns['name'];
			unset( $columns['name'] );
		}

		if ( ! isset( $columns_local['license_term_price'] ) ) {
			$columns_local['tax_term_price'] = __( '% Markup', 'sell_media' );
		}

		// Rename the post column to Images.
		if ( isset( $columns['posts'] ) ) {
			$columns['posts'] = __( 'Media', 'sell_media' );
		}

		$columns_local = array_merge( $columns_local, $columns );

		return array_merge( $columns_local, $columns );
	}

	/**
	 * Display Custom License Column Content below Headers in wp-admin.
	 *
	 * @param string $row_content Row Content.
	 * @param string $column_name Column Name.
	 * @param int $term_id Term
	 *
	 * ID.
	 */
	function custom_license_columns_content( $row_content, $column_name, $term_id ) {
		switch ( $column_name ) {
            case 'tax_term_price':
			    // TODO: make default markup using constant value
				$tmp_markup = get_term_meta( $term_id, 'markup', true );
				if ( $tmp_markup == '' || $tmp_markup == '%' ) {
					$tmp_markup = '0%';
				}
				return $tmp_markup;
				break;
			default:
				break;
		}
	}

	/**
	 * Add markup fields in front end.
	 */
	function item_detail_markup_fields() {
		global $post;
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : $post->ID;
		$post_id    = $product_id;
		if ( wp_get_post_parent_id( $product_id ) > 0 ) {
			$post_id = wp_get_post_parent_id( $product_id );
		}
		$markup_taxonomies = $this->markup_taxonomies();

		foreach ( $markup_taxonomies as $tax ) {
			if ( ! taxonomy_exists( $tax ) ) {
				continue;
			}
			$m        = wp_get_post_terms( $post_id, $tax );
			$taxonomy = get_taxonomy( $tax );
			if ( count( $m ) > 0 ) {
				?>
                <fieldset id="sell_media_download_<?php echo esc_attr( $tax ) ?>_fieldset" class="sell-media-add-to-cart-fieldset sell-media-add-to-cart-<?php echo esc_attr( $tax ) ?>-fieldset">
					<?php
					$sell_media_tooltip_text = 'Select a ' . esc_attr( $taxonomy->labels->singular_name ) . ' that most closely describes the intended use of this item. Additional ' . esc_attr( $taxonomy->labels->singular_name ) . ' details will be displayed here after selecting a ' . esc_attr( $taxonomy->labels->singular_name ) . '.';
					$tooltip_text            = apply_filters( "sell_media_{$tax}_tooltip_text", esc_attr__( $sell_media_tooltip_text, 'sell_media' ) );
					?>
                    <span class="sell-media-select-box sell-media-select-small">
						<select data-markup-taxonomy="<?php echo esc_attr( $tax ) ?>" id="sell_media_item_<?php echo esc_attr( $tax ) ?>" class="sum sell-media-select" required>
							<option selected="selected" value="" data-id="" data-price="0" title="<?php echo esc_attr( $tooltip_text ); ?>"><?php esc_html_e( sprintf( __( 'Select a %s', 'sell_media' ), strtolower( $taxonomy->labels->singular_name ) ) ); ?></option>
						<?php sell_media_build_options( array( 'post_id' => $post_id, 'taxonomy' => $tax, 'type' => 'select' ) ); ?>
						</select>
					</span>
                </fieldset>
				<?php
			}
		}
	}

	/**
	 * Add additional input fields to add markup cart data.
	 *
	 * @return void
	 */
	function tax_markup_add_cart_input() {
		$markup_fields = $this->markup_taxonomies();
		if ( is_array( $markup_fields ) && count( $markup_fields ) > 0 ) {
			foreach ( $markup_fields as $markup_field ) {
				if ( 'licenses' === $markup_field ) {
					continue;
				} ?>
                <input class="item_markup_<?php echo esc_attr( $markup_field ) ?>" name="item_markup_<?php echo esc_attr( $markup_field ) ?>" type="text" value="<?php esc_attr_e( 'No ' . $markup_field, 'sell_media' ); ?>"/>                <input class="item_markup_<?php echo esc_attr( $markup_field ) ?>_id" name="item_markup_<?php echo esc_attr( $markup_field ) ?>_id" type="text" value="0"/>
				<?php
			}
		}
	}

	/**
	 * Add Markup attr to cart items.
	 *
	 * @param array $attrs Cart Attributes.
	 *
	 * @return array
	 */
	function tax_markup_cart_item_attrs( $attrs ) {
		if ( ! $attrs ) {
			return;
		}
		$markup_fields = $this->markup_taxonomies();
		if ( is_array( $markup_fields ) && count( $markup_fields ) > 0 ) {
			foreach ( $markup_fields as $markup_field ) {
				if ( 'licenses' === $markup_field ) {
					continue;
				}
				$markup_name = "item_markup_{$markup_field}";
				$markup_id   = "item_markup_{$markup_field}_id";
				if ( isset( $_POST[ $markup_name ] ) && '' != sanitize_text_field( $_POST[ $markup_name ] ) ) {
					$attrs[ $markup_name ] = sanitize_text_field( $_POST[ $markup_name ] );
				}

				if ( isset( $_POST[ $markup_id ] ) && '' != sanitize_text_field( $_POST[ $markup_id ] ) ) {
					$attrs[ $markup_id ] = sanitize_text_field( $_POST[ $markup_id ] );
				}
			}
		}

		return $attrs;
	}
}
