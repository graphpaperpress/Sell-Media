jQuery(document).ready(function ($) {

    /**
     * Uploading files
     */
    var file_frame;

    /**
     * Upload button clicked
     * Used on add/edit item page
     * Ajax callback to show uploaded/selected items in meta box
     * Updates hidden input field with new attachment ids
     */
    $(document).on('click', '.sell-media-upload-button', function (event) {

        event.preventDefault();

        var post_id = $(this).data('id');

        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select Files To Sell',
            description: 'This is the description',
            button: {
                text: 'Sell All Selected Files',
            },
            multiple: 'add'  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function () {

            var attachments = file_frame.state().get('selection').toJSON();
            /**
             * Since we only want id, title, description and url, we build a new JSON object
             * the current one (attachments) is bloated and causing the bulk updater to fail
             * after ~23 items
             */
            var attachments_array = [];

            $.each(attachments, function (i, item) {
                attachments_array.push({
                    id: item.id,
                    title: item.title,
                    description: item.description,
                    url: item.url
                });
            });

            var data = {
                action: "sell_media_upload_callback",
                attachments: attachments_array,
                id: post_id,
                security: $('#sell_media_meta_box_nonce').val()
            };

            $('.sell-media-ajax-loader').show();

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function (msg) {
                    $('.sell-media-ajax-loader').hide();
                    $('.sell-media-upload-list').append(msg);
                    update_files();
                }
            });
        });

        // Finally, open the modal
        file_frame.open();
    });

    /**
     * Remove from file list
     */
    $(document).on('click', '.sell-media-delete', function (event) {
        // Remove the file
        var id = $(this).data('id');
        $('.sell-media-attachment[data-post_id="' + id + '"]').remove();

        // Update the file list hidden field
        update_files();
        return false;
    });

    /**
     * Check if attachment is audio or video.
     */
    function sell_media_is_attachment_audio_video(attachment_ids) {
        if (attachment_ids === undefined)
            return false;

        var attachment_ids = attachment_ids.split(',');
        var data = {
            'action': 'check_attachment_is_audio_video',
            'attachment_id': attachment_ids[0]
        }
        $.post(ajaxurl, data, function (res) {
            if ('true' == res) {
                $('#sell-media-embed-link-field').show();
            }
        });
    }

    // On load check item type.
    var attachment_ids = $('input#sell-media-attachment-id').val();
    sell_media_is_attachment_audio_video(attachment_ids);

    /**
     * Update the file list hidden field
     */
    function update_files() {
        var array = [];
        $('.sell-media-upload-list li').each(function () {
            array.push($(this).data('post_id'));
        });
        var new_array = array.join(',');

        // Set the new value
        $('#sell-media-attachment-id').val(new_array);

        // On files update check fields.
        sell_media_is_attachment_audio_video(new_array);
    }

    /**
     * Toggle the upload options
     * Used on add/edit item page
     */
    $('.sell-media-upload-options').on('click', function (event) {
        event.preventDefault();
        $(this).find('span').toggleClass('dashicons-arrow-up dashicons-arrow-down');
        $('#sell-media-upload-show-options').toggle();
    });

    /**
     * Generic toggler
     */
    $('.sell-media-toggler').on('click', function () {
        $(this).toggleClass('active');
        $(this).find('span').toggleClass('dashicons-arrow-up dashicons-arrow-down');
        $(this).next('.toggle').toggle();
    });

    /**
     * Remove disabled property when bulk selector changes
     * Used on add/edit item page
     */
    $('#sell-media-upload-bulk-selector').change(function () {
        var button = $('#sell-media-upload-bulk-processor');
        if ($(this).val()) {
            $(button).prop('disabled', false);
        } else {
            $(button).prop('disabled', true);
        }
    });

    /**
     * Ajax callback to insert attachments in bulk upload directory into WP
     * Used on add/edit item page
     */
    $('#sell-media-upload-bulk-processor').on('click', function (event) {
        event.preventDefault();

        var selector = $(this);

        $(selector).text($(selector).data('uploading-text'));

        var directory = $('#sell-media-upload-bulk-selector').val(),
            post_id = $('#post_ID').attr('value');

        var data = {
            action: "sell_media_upload_bulk_callback",
            dir: directory,
            security: $('#sell_media_meta_box_nonce').val(),
        };

        $.ajax({
            'url': ajaxurl,
            'type': 'POST',
            'data': data,
            'success': function (data) {
                callback(data);
            }
        });

        var imagelist;
        var arraylength = 0;

        function callback(response) {
            imagelist = JSON.parse(response);
            var len = $('.sell-media-upload-list .sell-media-attachment').length;
            //arraylength = Object.keys(imagelist).length;

            var allAJAX = imagelist.map((m, i) => {
                var idx = 0;
                if(len>0) {
                    idx = len+i;
                } else {
                    idx = 0+i;
                }

                $('.sell-media-upload-list').append('<li><div class="sell-media-loading-icon"><div class="spinner-icon"></div></div></li>');
                $('.sell-media-upload-list li').css({ 'background-color': '#eeeeee' });

                var data1 = {
                    action: "sell_media_upload_bulk_move",
                    dir: directory,
                    id: post_id,
                    image: m,
                    security: $('#sell_media_meta_box_nonce').val(),
                };

                return $.ajax({
                    url: ajaxurl,
                    data: data1,
                    type: 'POST'
                }).done(function (data) {
                    $('.sell-media-upload-list').children('li').eq(idx).replaceWith(data);
                    update_files();
                    $('.sell-media-upload-list').children('li').eq(idx).css({ 'background-color': 'transparent' });
                }).fail(function (error) {
                    if (error.statusText == 'error') {
                        $('.sell-media-upload-list').children('li').eq(idx).find('img').remove();
                        $('.sell-media-upload-list').children('li').eq(idx).append("<div><span style='color:red;font-weight:bold;display:block;width:100%;text-align:center;'>Failed.</span> <span class='tryagain' style='cursor:pointer;'>Try again</span></div>");
                    }
                });
            });

            Promise.all(allAJAX).then(function () {
                $(selector).text($(selector).data('default-text'));
            });
        }


    });


    $('.sell-media-upload-list').on('click', '.tryagain', function (event) {
        event.preventDefault();
        var i = $(this).closest('li').index();
        console.log(i);
        $('.sell-media-upload-list li').css({ 'background-color': '#dddddd' });
        var directory = $('#sell-media-upload-bulk-selector').val(),
            post_id = $('#post_ID').attr('value');
        var imagename = $(this).closest('li').find('.image-name').html();

        $(this).closest('li').replaceWith('<li><div class="image-name">' + imagename + '</div><img src="' + sell_media.pluginurl + 'images/loading.gif" style="display: block; width: 20px;height:20px;margin:auto;"/></li>');

        var data = {
            action: "sell_media_upload_bulk_move",
            image: imagename,
            dir: directory,
            id: post_id,
            security: $('#sell_media_meta_box_nonce').val()
        };
        $.ajax({
            'url': ajaxurl,
            'type': 'POST',
            'data': data,
            'success': function (data) {
                console.log(data);
                $('.sell-media-upload-list').children('li').eq(i).replaceWith(data);
                $('.sell-media-upload-list').children('li').eq(i).css({ 'background-color': 'transparent' });
                update_files();
            }
        });
    });

    /**
     * Upload thumbnail icon for collections
     */
    $(document).on('click', '.sell-media-upload-trigger-collection-icon', function (event) {

        event.preventDefault();

        if (file_frame) {
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
        file_frame.on('select', function () {

            // We set multiple to false so only get one image from the uploader
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('#collection_icon_input_field').val(attachment.id);
            $('#collection_icon_url').val(attachment.url);
            if (undefined !== attachment.sizes.thumbnail) {
                $('#collection_icon_target').html('<img src="' + attachment.sizes.thumbnail.url + '" /><br><a href="javascript:void(0);" class="upload_image_remove">Remove</a>');
            } else {
                $('#collection_icon_target').html('<img src="' + attachment.sizes.full.url + '" /><br><a href="javascript:void(0);" class="upload_image_remove">Remove</a>');
            }
        });

        // Finally, open the modal
        file_frame.open();
    });

    /**
     * Remove thumbnail icon for collections
     */
    $(document).on('click', '.upload_image_remove', function () {
        $('#collection_icon_input_field').val('');
        $('#collection_icon_url').val('');
        $('#collection_icon_target img').remove();
    });

    /**
     * Remove thumbnail icon for collections
     */
    $(document).ajaxComplete(function (event, xhr, settings) {
        /**
         * We should somehow intercept the correct event among lots of them fired by WordPress
         */
        if (~settings.data >= 0 && ~settings.data.indexOf('action=add-tag') >= 0) {
            $('body.post-type-sell_media_item #collection_icon_target img').remove();
        }
    });

    // We create a copy of the WP inline edit post function.
    var $wp_inline_edit = inlineEditPost.edit;
    // And then we overwrite the function with our own code.
    inlineEditPost.edit = function (id) {
        // "call" the original WP edit function.
        // Prevent WordPress hanging.
        $wp_inline_edit.apply(this, arguments);

        // Now we take care of our business.

        // Get the post ID.
        var $post_id = 0;
        if (typeof (id) == 'object')
            $post_id = parseInt(this.getId(id));

        if ($post_id > 0) {
            // Define the edit row.
            var $edit_row = $('#edit-' + $post_id);
            var $post_row = $('#post-' + $post_id);
            // Get the data.
            var $sell_media_price = $post_row.find('td.column-sell_media_price').html();
            var $sell_media_price_group = $post_row.find('td.column-taxonomy-price-group a').text();
            // Populate the data.
            $(':input[name="sell_media_price"]', $edit_row).val($sell_media_price.replace(/^\D+/g, ""));

            $('select[name="sell_media_price_group"] option', $edit_row).filter(function () {
                return $(this).text() == $sell_media_price_group;
            }).attr('selected', true);
        }
    };

    /**
     * Send ajax data for bulk edit.
     */
    $(document).on('click', '#bulk_edit', function () {
        // Define the bulk edit row.
        var $bulk_row = $('#bulk-edit');

        // Get the selected post ids that are being edited.
        var $post_ids = new Array();
        $bulk_row.find('#bulk-titles').children().each(function () {
            $post_ids.push($(this).attr('id').replace(/^(ttle)/i, ''));
        });

        // Get the data.
        var sell_media_price_group = $bulk_row.find('select[name="sell_media_price_group"]').val();
        var sell_media_price = $bulk_row.find('input[name="sell_media_price"]').val();
        var nonce = $bulk_row.find('input[name="sell_media_quick_edit_nonce"]').val();

        // Save the data.
        $.ajax({
            url: ajaxurl, // This is a variable that WordPress has already defined for us.
            type: 'POST',
            async: false,
            cache: false,
            data: {
                action: 'sell_media_save_bulk_edit', // This is the name of our WP AJAX function that we'll set up next.
                post_ids: $post_ids, // And these are the 2 parameters we're passing to our function.
                sell_media_price_group: sell_media_price_group,
                sell_media_price: sell_media_price,
                sell_media_quick_edit_nonce: nonce
            }
        });
    });

    /*
     * Tab js.
     */
    if ($.fn.tabs) {
        $('.sell-media-add-item-main-container-wrap').tabs({
            activate: function (event, ui) {
                $(ui.newPanel).css({ display: 'table' })
            },
            create: function (event, ui) {
                $(ui.panel).css({ display: 'table' })
            }
        });
    }

    /**
     * Display Price lists in item add/ edit.
     */
    function sellMediaDisplayPricelistTable(priceListId) {
        $.post(ajaxurl, { action: 'sell_media_load_pricelists', parent_id: priceListId }, function (res) {
            $("#sell-media-display-pricelists").remove();
            if ('0' != res) {
                var url = $("#sell-media-edit-pricelist-link-wrap a").data('href');
                $("#sell-media-edit-pricelist-link-wrap a").attr('href', url + priceListId).show();
                $("#sell-media-edit-pricelist-link-wrap").show();
                $("#sell-media-price-group-field").append(res);
            }
            else {
                $("#sell-media-edit-pricelist-link-wrap").hide();
            }
        });
    }

    var selectedPriceList = $('select#sell-media-price-group').val();
    sellMediaDisplayPricelistTable(selectedPriceList);
    $('select#sell-media-price-group').on('change', function () {
        var groupParentId = $(this).val();
        sellMediaDisplayPricelistTable(groupParentId);
    });

    /*
    * Ajax call for refund PayPal amount.
    * */
    $(document).on('click', '#paypal_payment_refund_btn', function (e) {
        e.preventDefault();
        if (!confirm("Are you sure you want to refund the order.")) {
            return;
        }

        var btn = $(this);
        btn.attr('disabled', true).addClass('loader');
        $.ajax({
            type: "POST",
            url: sell_media_paypal.ajax_url,
            data: {
                action: 'sell_media_paypal_order_refund',
                transaction_id: btn.attr('data-transaction_id'),
                refund_amount: $('#paypal-order-amount').val(),
                currency_code: $('#paypal_payment_currency_code').val(),
                payment_id: $('#paypal_payment_id').val(),
                _nonce: sell_media_paypal.paypal_refund_nonce
            },
            success: function (response) {
                var refund_wrapper = $('.paypal-image-refund-wrapper');
                console.log(response);
                if (response.status) {
                    refund_wrapper.empty().append('<li><strong>' + sell_media_paypal.paypal_refund_label + ' </strong>' + response.refund_id + '</li>');
                } else {
                    var msg_box = '<div class="sell-media-msg-box error"><p>' + response.message + '</p></div>';
                    refund_wrapper.before(msg_box);
                }
                btn.attr('disabled', false).removeClass('loader');
            }
        });
    });
});
