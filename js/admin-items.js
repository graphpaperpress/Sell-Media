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
            $('.sell-media-item-table #sell_media_selected_file_id').attr( 'value', attachment.id );
            $('.sell-media-item-table #_sell_media_attached_file').attr( 'value', attachment.url );

            var data = {
                action: "sell_media_item_icon",
                attachment_id: attachment.id
            };

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function( msg ){
                    $('.sell-media-item-table .sell-media-temp-target').html( msg );
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

    $( document ).on( 'mouseenter', '.sell-media-bulk-list-item', function(){
        $this = $(this);

        $this.find('img').css('opacity', '0.2');
        $this.find('.sell-media-bulk-list-item-edit').show();

    }).on( 'mouseleave', '.sell-media-bulk-list-item', function(){
        $this = $(this);

        $this.find('img').css('opacity','1');
        $this.find('.sell-media-bulk-list-item-edit').hide();
    });
});