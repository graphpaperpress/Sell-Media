jQuery(function( $ ){
    // Move bulk upload tabs above post title, no hook exists there in WP
    $(function() {
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
            $('#sell_media_selected_file_id').attr( 'value', attachment.id );
            $('#_sell_media_file').attr( 'value', attachment.url );
            $('.sell-media-image').attr( 'src', attachment.url );
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

            // We set multiple to false so only get one image from the uploader
            var attachments = file_frame.state().get('selection').toJSON();

            var data = {
                action: "sell_media_uploader_multiple",
                attachments: attachments
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
});