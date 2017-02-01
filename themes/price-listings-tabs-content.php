<?php
$current_term = get_term( (int) $this->current_term, $current_tab );
$download_parents = $this->get_terms();
$url = add_query_arg( array( 'term_parent' => 'new' ), admin_url( 'edit.php?' . $_SERVER['QUERY_STRING'] ) );
$current_url = false;
?>
<div class="tab-price-lists">
	<select>
		<?php
		foreach ( $download_parents as $slug => $term ) {
			$url = add_query_arg( array( 'term_parent' => $term->term_id ), admin_url( 'edit.php?' . $_SERVER['QUERY_STRING'] ) );
			if ( $this->current_term === $term->term_id ) {
				$current_url = $url;
			}
			echo "<option value='$url' " . selected( (int) $this->current_term, $term->term_id, false ) . ">" . $term->name . '</option>';
		}
		?>
	</select>
	<input type="hidden" value="<?php echo !empty( $current_term ) ? $current_term->term_id: 'new'; ?>" name="term_id" />
	<input type="hidden" value="" name="deleted_term_ids" />
	<?php
	$delete_url = add_query_arg( array( 'delete' => true ), $current_url );
	$delete_url = wp_nonce_url( $delete_url, 'delete_pricelist_nonce_action', 'delete_pricelist_nonce_name' ); ?>
	<a href="#" data-href="<?php echo esc_url( $delete_url ); ?>" class="deletion" title="<?php esc_html_e( 'Delete pricelist.', 'sell_media' ); ?>" data-message="<?php printf( __( 'Are you sure you want to delete the pricelist: %s', 'sell_media' ), $current_term->name ); ?>"><?php esc_html_e( 'Delete', 'sell_media' ); ?></a>
</div>
<hr/>
<?php
if ( ! isset( $_GET['term_parent'] ) || 'new' !== $_GET['term_parent'] ) {
?>
<!-- Price table -->
<table class="form-table tax-<?php echo esc_attr( $this->taxonomy ); ?>" id="sell-media-price-table">
	<thead>
		<tr>
			<th style="width:15%"><?php _e( 'Name', 'sell_media' ); ?></th>
			<th style="width:15%"><?php _e( 'Description', 'sell_media' ); ?></th>
			<th style="width:10%"><?php _e( 'Width', 'sell_media' ); ?></th>
			<th style="width:10%"><?php _e( 'Height', 'sell_media' ); ?></th>
			<th style="width:10%"><?php _e( 'Price', 'sell_media' ); ?></th>
			<th style="width:1%"></th>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>
<p class="submit sell-media-pricelisting-form-buttons" style="clear: both;">
	<input type="submit" name="Submit" id="sell-media-save-button"  class="button-primary" value="<?php _e( 'Save Pricelist', 'sell_media' ); ?>" />
	<input type="hidden" name="sell-media-price-list-submit" value="true" />
	<a href="javascript:void(0);" id="sell-media-add-button" class="button-secondary"><?php _e( '+ Add New', 'sell_media' ); ?></a>
</p>
<?php
}
