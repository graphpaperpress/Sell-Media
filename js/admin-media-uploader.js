(function( $ ) {
    $(function() {

      /**
       * Update the file list hidden field.
       */
      function update_files(){
          var array = [];
          $('.sell-media-upload-list li').each(function(){
              array.push($(this).data('post_id'));
          });
          var new_array = array.join(',');

          // Set the new value
          $('#sell-media-attachment-id').val(new_array);

          // On files update check fields.
          sell_media_is_attachment_audio_video( new_array );
      }

      // Make gallery sortable.
      if ( $.isFunction( $.fn.sortable ) ) {
        $( '.sell-media-upload-list' ).sortable({
          update: function(){
            update_files();
          }
        });
      }

        if ( typeof uploader !== 'undefined' ) {

            // Set a custom progress bar
            var sell_media_upload_bar      = $( '.sell-media-upload-progress-bar' ),
                sell_media_upload_progress = $( '.sell-media-upload-progress-bar div.sell-media-upload-progress-bar-inner' ),
                sell_media_upload_status   = $( '.sell-media-upload-progress-bar div.sell-media-upload-progress-bar-status' ),
                sell_media_upload_error    = $( '#sell-media-upload-error' ),
                sell_media_upload_file_count = 0;

						// Files Added for Uploading
            uploader.bind( 'FilesAdded', function ( up, files ) {

                // Hide any existing errors
                $( sell_media_upload_error ).html( '' );

                // Get the number of files to be uploaded
                sell_media_upload_file_count = files.length;

                // Set the status text, to tell the user what's happening
                $( '.uploading .current', $( sell_media_upload_status ) ).text( '1' );
                $( '.uploading .total', $( sell_media_upload_status ) ).text( sell_media_upload_file_count );
                $( '.uploading', $( sell_media_upload_status ) ).show();
                $( '.done', $( sell_media_upload_status ) ).hide();

                // Fade in the upload progress bar
                $( sell_media_upload_bar ).fadeIn();

            } );

						// File Uploading - show progress bar
            uploader.bind( 'UploadProgress', function( up, file ) {

                // Update the status text
                $( '.uploading .current', $( sell_media_upload_status ) ).text( ( sell_media_upload_file_count - up.total.queued ) + 1 );

                // Update the progress bar
                $( sell_media_upload_progress ).css({
                    'width': up.total.percent + '%'
                });

            });

            // File Uploaded - AJAX call to process image and add to screen.
            uploader.bind( 'FileUploaded', function( up, file, info ) {

							var data = info['response'].replace(/^<pre>(\d+)<\/pre>$/, '$1');

							if ( data.match(/media-upload-error|error-div/) ){

	               $( sell_media_upload_error ).html( info['response'] );

							}else{
									// AJAX call to sell_media_upload to store the newly uploaded image in the meta against this Gallery
									$.post(
											sell_media_drag_drop_uploader.ajax,
											{
													action:  'sell_media_upload_gallery_load_image',
													nonce:   sell_media_drag_drop_uploader.drag_drop_nonce,
													id:      info.response
											},
											function(msg){
												$('.sell-media-ajax-loader').hide();
		                    $('.sell-media-upload-list').append( msg );
		                    update_files();
											}
									);
								}
            });

						/**
				     * Check if attachment is audio or video.
				     */
				    function sell_media_is_attachment_audio_video( attachment_ids ){
				        if (attachment_ids === undefined)
				            return false;

				        var attachment_ids = attachment_ids.split( ',' );
				        var data = {
				            'action' : 'check_attachment_is_audio_video',
				            'attachment_id' : attachment_ids[0]
				        }
				        $.post( ajaxurl, data, function( res ){
				            if( 'true' == res ){
				                $('#sell-media-embed-link-field').show();
				            }
				        }  );
				    }

						// Files Uploaded
            uploader.bind( 'UploadComplete', function() {

                // Update status
                $( '.uploading', $( sell_media_upload_status ) ).hide();
                $( '.done', $( sell_media_upload_status ) ).show();

                // Hide Progress Bar
                setTimeout( function() {
                    $( sell_media_upload_bar ).fadeOut();
                }, 1000 );

            });

            // File Upload Error
            uploader.bind('Error', function(up, err) {

                // Show message
                $(sell_media_upload_error).html( '<div class="error fade"><p>' + err.file.name + ': ' + err.message + '</p></div>' );
                up.refresh();

            });

        }

    });
})( jQuery );
