jQuery(function( $ ){
    // Move bulk upload tabs above post title, no hook exists there in WP
    $(function() {
        $('#sell-media-bulk-tabs').insertBefore($('#post-body-content'));
    });

    $(document).on('click', '.sell-media-upload-trigger', function( event ){

        // Uploading files
        var file_frame;

        event.preventDefault();

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
            $('#sell_media_selected_file_id').attr( 'value', attachment.id );
            $('#_sell_media_file').attr( 'value', attachment.url );
            $('.sell_media_image').attr( 'src', attachment.url );
        });

        // Finally, open the modal
        file_frame.open();
    });

    $(document).on('click', '.sell-media-upload-trigger-multiple', function( event ){

        // Uploading files
        var file_frame;

        event.preventDefault();

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

            // We set multiple to false so only get one image from the uploader
            var attachments = file_frame.state().get('selection').toJSON();

            var data = {
                action: "sell_media_uploader_multiple",
                attachments: attachments
            };

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function( msg ){
                    $('.sell_media_bulk_list').html( msg );
                }
            });
        });

        // Finally, open the modal
        file_frame.open();
    });
});