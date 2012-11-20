
jQuery(document).ready(function($) {

    // prepend the Save All Uploads checkbox above the Save Changes button
    $('.savebutton, .submit').prepend('<fieldset id="sell_media_sell" style="display:none"><label for="sell_media_all_items"><input type="checkbox" name="sell_media_all_items" id="sell_media_all_items" value="1" /> Sell All Uploads</label></fieldset>');

    var items = $('#media-items').children();

    // Hide the fields when no uploads visible
    if ( items.not('.media-blank').length > 0 )
        $('#sell_media_sell').hide();
    else
        $('#sell_media_sell').show();

    $('#sell_media_all_items').click(function() {
        var checkedStatus = this.checked;
        $('#media-items table tbody tr.sell').find('td:first :checkbox').each(function() {
            $(this).prop('checked', checkedStatus);
        });
    });
});