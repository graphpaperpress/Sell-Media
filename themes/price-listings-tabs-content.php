<?php
$download_parents = $this->get_terms();
$current_term = '';
?>
<h2 class="nav-tab-wrapper sell-media-sub-tab" style="padding:0">
<?php
foreach ( $download_parents as $slug => $term ) {
	$class = '';
	if ( $term->term_id === (int) $this->current_term ) {
		$current_term = $term;
		$class = ' nav-tab-active';
	}
	$url = home_url( add_query_arg( array( 'term_parent' => $term->term_id ) ) );
	echo "<a class='nav-tab$class' href='$url'>" . $term->name . '</a>';
}
$class = ( 'new' === $this->current_term ) ? ' nav-tab-active' : '';
$url = home_url(add_query_arg(array('term_parent'=>'new')));
echo "<a class='nav-tab$class' href='$url'>+</a>";
?>
</h2>
<hr/>
<div class="price-group-term-information">
	<input type="text" value="<?php echo !empty( $current_term ) ? $current_term->name: ''; ?>" name="term_name"/>
	<input type="hidden" value="<?php echo !empty( $current_term ) ? $current_term->term_id: 'new'; ?>" name="term_id" />
	<input type="hidden" value="" name="deleted_term_ids" />
	<?php
	if( isset( $_GET['term_parent'] ) && 'new' === $_GET['term_parent'] ){
	?>
	<input type="submit" name="Submit" id="sell-media-save-button"  class="button-primary" value="<?php _e( 'Create Price Group' ); ?>" />
	<input type="hidden" name="sell-media-price-list-submit" value="true" />
	<?php } ?>
</div>
<hr/>
<?php
if( !isset( $_GET['term_parent'] ) || 'new' !== $_GET['term_parent'] ){
?>
<p><?php _e( 'The sizes listed below determine the maximum dimensions in pixels. Price Groups only apply to images.', 'sell_media' ); ?></p>
<!-- Price table -->
<table class="form-table tax-<?php echo esc_attr( $this->taxonomy ); ?>" id="sell-media-price-table">
<tbody>
</tbody>
</table>
<p class="submit" style="clear: both;">
	<input type="button" name="Submit" id="sell-media-add-button"  class="button-primary" value="<?php _e( '+ Add New', 'sell_media' ); ?>" />
	<input type="submit" name="Submit" id="sell-media-save-button"  class="button-primary" value="<?php _e( 'Save All', 'sell_media' ); ?>" />
	<input type="hidden" name="sell-media-price-list-submit" value="true" />
</p>
<?php
}
else{
	?>
	<?php _e( 'Create a price group to add prices to.', 'sell_media' ); ?>
	<?php
}
?>
<style>
#sell-media-price-table{
	width: 90%;
}
#sell-media-price-table td{
	padding: 5px 10px;
}
.price-group-term-information{
	margin: 10px 0;
}
.sell-media-sub-tab{
	border: none!important;
	margin: 10px 0 !important;
}
.sell-media-sub-tab .nav-tab{
	border: none;
	border-bottom: 2px solid #f1f1f1;
	background: none;
}
.sell-media-sub-tab .nav-tab:hover{
	border-bottom: 2px solid #444444;
	background: none;
}
.sell-media-sub-tab .nav-tab-active{
	border-bottom: 2px solid #444444;
	background: none;
}
</style>
