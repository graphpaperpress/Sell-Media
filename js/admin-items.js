// Move bulk upload tabs above post title, no hook exists there in WP
jQuery(function() {
    jQuery('#sell-media-bulk-tabs').insertBefore(jQuery('#post-body-content'));
});

// Uploading files
var file_frame;

jQuery(document).on('click', '.sell-media-upload-trigger', function( event ){

    event.preventDefault();

    // If the media frame already exists, reopen it.
    if ( file_frame ) {
        file_frame.open();
        return;
    }

    // Create the media frame.
    file_frame = wp.media.frames.file_frame = wp.media({
        title: jQuery( this ).data( 'uploader_title' ),
        button: {
          text: jQuery( this ).data( 'uploader_button_text' ),
        },
        multiple: false  // Set to true to allow multiple files to be selected
    });

    // When an image is selected, run a callback.
    file_frame.on( 'select', function() {
        // We set multiple to false so only get one image from the uploader
        var attachment = file_frame.state().get('selection').first().toJSON();

        // Do something with attachment.id and/or attachment.url here
        jQuery('#sell_media_selected_file_id').attr( 'value', attachment.id );
        jQuery('.sell_media_image').attr( 'src', attachment.url );
    });

    // Finally, open the modal
    file_frame.open();
});

jQuery(document).on('click', '.sell-media-upload-trigger-multiple', function( event ){

    event.preventDefault();

    // Create the media frame.
    file_frame = wp.media.frames.file_frame = wp.media({
        title: jQuery( this ).data( 'uploader_title' ),
        button: {
          text: jQuery( this ).data( 'uploader_button_text' ),
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

        jQuery.ajax({
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