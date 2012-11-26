jQuery(document).ready(function($) {
    /**
     * prepend the Save All Uploads checkbox above the Save Changes button
     */
    $('.savebutton, .submit').prepend('<fieldset id="sell_media_sell" style="display:none;margin:1em 0"><label for="sell_media_all_items"><input type="checkbox" name="sell_media_all_items" id="sell_media_all_items" value="1" /> Sell All Uploads</label></fieldset>');
});


/**
 * Once our ajax request is completed, i.e. an upload
 * we show our check box to toggle the "sell items"
 */
jQuery( document ).ajaxComplete(function() {
    if ( jQuery('#media-items').length ) {
        jQuery('#sell_media_sell').show();
    }
});


/**
 * Since the checkboxes are attached to the dom after the page is
 * loaded we use .on() to attach the click event.
 */
jQuery( document ).on('click','#sell_media_all_items', function(){

    var checkedStatus = this.checked;

    jQuery('#media-items table tbody tr.sell').find('td:first :checkbox').each(function() {
        jQuery(this).prop('checked', checkedStatus);
    });
});