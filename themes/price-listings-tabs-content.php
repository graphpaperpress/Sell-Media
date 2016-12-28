<?php
$tab = $this->tab;
$current_term = get_term( (int) $this->current_term, $current_tab );
$download_parents = $this->get_terms();
$url = home_url( add_query_arg( array( 'term_parent' => 'new' ) ) );
?>
<h2 class="tab-title">
	<span><?php echo __( 'Pricelists for ', 'sell_media' ) . ' ' . $tab['tab_title']; ?></span>
	<a class="page-title-action tab-create-new-list" href="javascript:void(0);"><?php _e( 'Add New Pricelist', 'sell_media' ); ?> </a>
</h2>
<div class="tab-price-lists">
	<select>
		<?php
		foreach ( $download_parents as $slug => $term ) {
			$url = home_url( add_query_arg( array( 'term_parent' => $term->term_id ) ) );
			echo "<option value='$url' " . selected( (int) $this->current_term, $term->term_id, false ) . ">" . $term->name . '</option>';
		}
		?>
	</select>
	<input type="hidden" value="<?php echo !empty( $current_term ) ? $current_term->term_id: 'new'; ?>" name="term_id" />
	<input type="hidden" value="" name="deleted_term_ids" />
</div>
<h2 class="tab-price-list-edit-title">
	<?php echo sprintf( __( 'Edit Pricelist - %s', 'sell_media' ), $current_term->name ); ?>
</h2>
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
	<a href="javascript:void(0);" id="sell-media-add-button" ><?php _e( '+ Add New', 'sell_media' ); ?> </a>
	<input type="submit" name="Submit" id="sell-media-save-button"  class="button-primary" value="<?php _e( 'Save', 'sell_media' ); ?>" />
	<input type="hidden" name="sell-media-price-list-submit" value="true" />
</p>
<?php
}
