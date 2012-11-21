
jQuery(document).ready(function($) {

    // prepend the Save All Uploads checkbox above the Save Changes button
    $('.savebutton, .submit').prepend('<fieldset id="sell_media_sell" style="display:none;margin:1em 0"><label for="sell_media_all_items"><input type="checkbox" name="sell_media_all_items" id="sell_media_all_items" value="1" /> Sell All Uploads</label></fieldset>');

    $('.media-item').livequery(function(){

        var items = $('#media-items').children();

        if ( items.length > 0 ) {
            $('#sell_media_sell').show();
        } else {
            $('#sell_media_sell').hide();
        }
    });

    $('#sell_media_all_items').click(function() {
        var checkedStatus = this.checked;
        $('#media-items table tbody tr.sell').find('td:first :checkbox').each(function() {
            $(this).prop('checked', checkedStatus);
        });
    });

});