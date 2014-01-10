jQuery( document ).ready(function( $ ){

    $(function() {
        // Move bulk upload tabs above post title, no hook exists there in WP
        $('#sell-media-bulk-tabs').insertBefore($('#post-body-content'));

    });

    // Uploading files
    var file_frame;

    $(document).on('click', '.sell-media-upload-trigger', function( event ){

        event.preventDefault();

        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select Image',
            button: {
              text: 'Use Selected Image',
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            var attachment = file_frame.state().get('selection').first().toJSON();

            // Do something with attachment.id and/or attachment.url here
            $('.sell_media_selected_file_id').attr( 'value', attachment.id );
            $('.sell_media_attached_file').attr( 'value', attachment.url );

            var data = {
                action: "sell_media_item_icon",
                attachment_id: attachment.id,
                attachment_size: "thumbnail"
            };

            // Show our loader
            $('.sell-media-temp-target').show();

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function( msg ){
                    $('.sell-media-temp-target').html( msg );
                }
            });
        });

        // Finally, open the modal
        file_frame.open();
    });

    $(document).on('click', '.sell-media-upload-trigger-multiple', function( event ){

        event.preventDefault();

        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select Images To Sell',
            description: 'This is the description',
            button: {
              text: 'Sell All Selected Images',
            },
            multiple: 'add'  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {

            var attachments = file_frame.state().get('selection').toJSON();

            /**
             * Since we only want id, title, description and url, we build a new JSON object
             * the current one (attachments) is bloated and causing the bulk updater to fail
             * after ~23 items
             */
            var slim = [];
            $.each( attachments, function( i, item ){
                slim.push({
                    id: item.id,
                    title: item.title,
                    description: item.description,
                    url: item.url
                });
            });

            var data = {
                action: "sell_media_uploader_multiple",
                attachments: slim
            };

            $('.sell-media-bulk-list').empty();
            $('.sell-media-ajax-loader').show();

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function( msg ){
                    $('.sell-media-ajax-loader').hide();
                    $('.sell-media-bulk-list').html( msg );
                }
            });
        });

        // Finally, open the modal
        file_frame.open();
    });

    $( document ).on( 'mouseenter', '.sell-media-bulk-list-item', function(){
        $this = $(this);

        $this.find('img').css('opacity', '0.4');
        $this.find('.sell-media-bulk-list-item-edit').show();

    }).on( 'mouseleave', '.sell-media-bulk-list-item', function(){
        $this = $(this);

        $this.find('img').css('opacity','1');
        $this.find('.sell-media-bulk-list-item-edit').hide();
    });


    $('#sell_media_bulk_upload_form').on('submit', function( event ){
        event.preventDefault();

        var _post_ids = [];

        $('.sell-media-bulk-list li').each(function(){
            _post_ids.push( $(this).attr('data-post_id') );
        });

        if ( _post_ids.length == 0 ) return;

        $('#sell_media_bulk_upload_save_button').attr('disabled', true).val('Saving...');

        $.ajax({
            data: 'action=sell_media_bulk_update_collection&post_ids=' + _post_ids + '&' + $('#sell_media_bulk_upload_form').serialize(),
            type: "POST",
            url: ajaxurl,
            success: function( msg ){
                $('#sell_media_bulk_upload_save_button').removeAttr('disabled').val('Saved!');
            }
        });
    });


    $(document).on('click', '.sell-media-upload-trigger-collection-icon', function( event ){

        event.preventDefault();

        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select Images To Sell',
            description: 'This is the description',
            button: {
              text: 'Use selected image as icon',
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {

            // We set multiple to false so only get one image from the uploader
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('#collection_icon_input_field').val( attachment.id );
            $('#collection_icon_url').val( attachment.url );
            $('#collection_icon_target').html( '<img src="'+attachment.sizes.thumbnail.url+'" /><br><a href="javascript:void(0);" class="upload_image_remove">Remove</a>' );

        });

        // Finally, open the modal
        file_frame.open();
    });

    $(document).on('click', '.upload_image_remove', function(){
        $('#collection_icon_target').html('');
        $('#collection_icon_url').val('');
        $('#collection_icon_input_field').val('');
    });
});