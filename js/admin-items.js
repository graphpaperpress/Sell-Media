jQuery(function( $ ){
    // Move bulk upload tabs above post title, no hook exists there in WP
    $(function() {
        $('#sell-media-bulk-tabs').insertBefore($('#post-body-content'));
    });

    // Uploading files
    var file_frame;

    $(document).on('click', '.sell-media-upload-trigger', function( event ){

        event.preventDefault();

        // If the media frame already exists, reopen it.
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
            $('.sell_media_image').attr( 'src', attachment.url );
        });

        // Finally, open the modal
        file_frame.open();
    });

    $(document).on('click', '.sell-media-upload-trigger-multiple', function( event ){

        event.preventDefault();

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: $( this ).data( 'uploader_title' ),
            button: {
              text: $( this ).data( 'uploader_button_text' ),
            },
            multiple: true
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
                }
            });
        });

        // Finally, open the modal
        file_frame.open();
    });
});