// Number format.
Number.prototype.formatMoney = function(c, d, t) {
    var n = this,
        c = isNaN(c = Math.abs(c)) ? 2 : c,
        d = d == undefined ? "." : d,
        t = t == undefined ? "," : t,
        s = n < 0 ? "-" : "",
        i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
        j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

jQuery(document).ready(function($) {

    var view_port_height = '';

    /**
     * Update cart totals on load
     */
    sm_update_cart_totals(1);

    /**
     * Check required fields on load
     */
    required_fields();

    /**
     * Resize item overlays to fit image
     */
    resize_item_overlay();

    /**
     * Popup resize
     */
    function popup_resize() {
        $('.sell-media-dialog-box').width(0).height(0);
        // assign values to the overlay and dialog box and show overlay and dialog
        var width = $(window).width();
        var height = $(document).height();
        $('.sell-media-dialog-box').width(width).height(height);
    }

    /**
     * Popup
     */
    function popup(message) {
        popup_resize();
        $('.sell-media-dialog-box').addClass('is-visible');
        var dialogTop = $(document).scrollTop() + 25;
        $('.sell-media-dialog-box #sell-media-dialog-box-target').css({ top: dialogTop });
    }

    /**
     * Ajax request.
     */
    function sell_media_popup_ajax_request(new_data) {
        $('#sell-media-dialog-box-target').addClass('sell-media-spinner-large');

        $('body').addClass('quick_view_modal_popup_open');

        var old_data = {
            "action": "sell_media_load_template",
            "location": "quick_view",
            "template": "cart.php",
        };
        var final_data = $.extend(old_data, new_data);

        // Check current item.
        var current_item = $('.sell-media-grid-single-item.sell-media-active-popup-item');
        var last_index = parseInt($('.sell-media-grid-single-item').length) - 1;

        $('.sell-media-dialog-box-prev').show();
        $('.sell-media-dialog-box-next').show();

        if ($('.sell-media-grid-single-item').index(current_item) == last_index) {
            $('.sell-media-dialog-box-next').hide();
            $('.sell-media-dialog-box-prev').show();
        }
        if ('0' == $('.sell-media-grid-single-item').index(current_item)) {
            $('.sell-media-dialog-box-prev').hide();
            $('.sell-media-dialog-box-next').show();
        }

        if (('0' == $('.sell-media-grid-single-item').index(current_item)) && ($('.sell-media-grid-single-item').index(current_item) == last_index)) {
            $('.sell-media-dialog-box-prev').hide();
            $('.sell-media-dialog-box-next').hide();
        }

        // send ajax request for product in shopping cart
        $.ajax({
            type: "POST",
            url: sell_media.ajaxurl,
            data: final_data,
            success: function(msg) {
                view_port_height = parseFloat($(window).height())-parseFloat(50);
                var css = '<style>.sell-media-dialog-box-content, .sell-media-quick-view-container, .sell-media-quick-view-image{height:'+view_port_height+'px !important;align-items: center;display: flex;width:100%;}</style>';
                msg = msg+css;
                var target = $('#sell-media-dialog-box-target .sell-media-dialog-box-content');
                // if there's an image already, fade out, then fade in
                if ($('#sell-media-dialog-box-target .sell-media-dialog-box-content img').length) {
                    $(target).fadeOut('fast', function() {

                        $(target).html(msg).fadeIn('fast', function() {
                            setTimeout(function(){
                                $('#sell-media-dialog-box-target').removeClass('sell-media-spinner-large');

                                $('.sell-media-dialog-box-content .sell-media-quick-view-image .sell_media_image').css({'object-fit': 'contain' });

                                setTimeout(function(){                                    
                                    $('#sell-media-dialog-box-target').removeClass('sell-media-spinner-large');
                                    if( $('.sell-media-quick-view-content-inner #print_on_demand').length ) {                                        
                                        $('input[name="type"][value="print_on_demand"]').prop('checked', true);
                                        setTimeout(function() {
                                            $('input[name="type"]').trigger('change');
                                            $('.sell-media-quick-view-content-inner #sell_media_print_wrapper').addClass('is-hidden');     
                                            $('.sell-media-quick-view-content-inner #sell_media_download_wrapper').addClass('is-hidden'); /* hidding other options */                            
                                        },10);
                                    } else {
                                        if($('.sell-media-quick-view-content-inner #sell_media_print_wrapper').is(':visible')){
                                            
                                            if($('#sell_media_print_wrapper #sell_media_print_size_fieldset #sell_media_print_size').length){
                                                $($('#sell_media_print_size').children()[1]).attr('selected',true).trigger('change');
                                            }
                                        }

                                        if($('.sell-media-quick-view-content-inner .sell-media-add-to-cart-download-fields').is(':visible')){
                                            if($('#sell_media_download_wrapper .sell-media-add-to-cart-fieldset select').length){
                                                $($('#sell_media_download_wrapper .sell-media-add-to-cart-fieldset select').children()[1]).attr('selected',true).trigger('change');
                                            }
                                        }
                                    }

                                    var sellmedia_quick_view_image_height = $('.sell-media-quick-view-container .sell-media-quick-view-image').height();
                                    var sellmedia_quick_view_content_height = $('.sell-media-quick-view-container .sell-media-quick-view-content').height();
                                    var overflow_y = 'unset';
                                    if(sellmedia_quick_view_content_height > sellmedia_quick_view_image_height)
                                        overflow_y = 'scroll';
                                    $('.sell-media-quick-view-container .sell-media-quick-view-content').css({'max-height': sellmedia_quick_view_image_height+'px','overflow-y':overflow_y });
                                }, 100);

                            }, 100);

                        });
                    });
                    // otherwise, this is the first load
                } else {                    
                    $(target).html(msg);
                    setTimeout(function(){

                        $('.sell-media-dialog-box-content .sell-media-quick-view-image .sell_media_image').css({'object-fit': 'contain' });

                        var fit_width_view_content = $('.sell-media-quick-view-container .sell-media-quick-view-content').width();

                        setTimeout(function(){                            
                            $('#sell-media-dialog-box-target').removeClass('sell-media-spinner-large');

                            if( $('.sell-media-quick-view-content-inner #print_on_demand').length ) {                               
                                $('input[name="type"][value="print_on_demand"]').prop('checked', true);
                                setTimeout(function() {
                                    $('input[name="type"]').trigger('change');
                                    $('.sell-media-quick-view-content-inner #sell_media_print_wrapper').addClass('is-hidden');     
                                    $('.sell-media-quick-view-content-inner #sell_media_download_wrapper').addClass('is-hidden'); /* hidding other options */                            
                                },10);
                            } else {
                                if($('.sell-media-quick-view-content-inner #sell_media_print_wrapper').is(':visible')){
                                            
                                    if($('#sell_media_print_wrapper #sell_media_print_size_fieldset #sell_media_print_size').length){
                                        $($('#sell_media_print_size').children()[1]).attr('selected',true).trigger('change');
                                    }
                                }
                                        
                                if($('.sell-media-quick-view-content-inner .sell-media-add-to-cart-download-fields').is(':visible')){
                                    if($('#sell_media_download_wrapper .sell-media-add-to-cart-fieldset select').length){
                                        $($('#sell_media_download_wrapper .sell-media-add-to-cart-fieldset select').children()[1]).attr('selected',true).trigger('change');
                                    }
                                }
                            }
                            var sellmedia_quick_view_image_height = $('.sell-media-quick-view-container .sell-media-quick-view-image').height();
                            var sellmedia_quick_view_content_height = $('.sell-media-quick-view-container .sell-media-quick-view-content').height();
                            var overflow_y = 'unset';
                            if(sellmedia_quick_view_content_height > sellmedia_quick_view_image_height)
                                overflow_y = 'scroll';
                            $('.sell-media-quick-view-container .sell-media-quick-view-content').css({'max-height': sellmedia_quick_view_image_height+'px','overflow-y':overflow_y });

                        }, 100);

                    }, 100);

                }                
                required_fields();

            }
        });
    }

    /**
     * Popup next previous.
     */
    function sell_media_popup_next_prev(event) {
        if (!$('.sell-media-dialog-box').hasClass('is-visible'))
            return false;

        var current_item = $('.sell-media-grid-single-item.sell-media-active-popup-item');

        if ('next' == event) {
            var next_item = current_item.nextAll('.sell-media-grid-single-item').first();
        }
        if ('prev' == event) {
            var next_item = current_item.prevAll('.sell-media-grid-single-item').first();
        }

        var next_item_id = next_item.find('.sell-media-quick-view').attr('data-product-id');
        var next_item_attachment_id = next_item.find('.sell-media-quick-view').attr('data-attachment-id');

        // If next item exists
        if (next_item_id && next_item_attachment_id) {

            $('.sell-media-dialog-box-next, .sell-media-dialog-box-prev').show();

            // remove active class from current element
            current_item.removeClass('sell-media-active-popup-item');
            next_item.addClass('sell-media-active-popup-item');

            // send ajax request for product in shopping cart
            sell_media_popup_ajax_request({
                "product_id": next_item_id,
                "attachment_id": next_item_attachment_id
            });
        }
    }

    /**
     * When the user clicks on our trigger we set-up the overlay,
     * launch our dialog, and send an Ajax request to load our cart form.
     */
    $(document).on('click', '.sell-media-quick-view', function(event) {

        event.preventDefault();

        popup();

        var parent = $(this).parents('.sell-media-grid-item');
        var item_id = $(this).data('product-id');
        var item_attachment_id = $(this).data('attachment-id');

        parent.addClass('sell-media-active-popup-item');

        // send ajax request for product in shopping cart
        sell_media_popup_ajax_request({
            "product_id": item_id,
            "attachment_id": item_attachment_id
        });

    });

    /**
     * Close dialog
     */
    $(document).on('click', '.sell-media-dialog-box', function(event) {
        if ($(event.target).is('.close') || $(event.target).is('.sell-media-dialog-box')) {
            event.preventDefault();
            $(this).removeClass('is-visible');
            $('body').removeClass('quick_view_modal_popup_open');
            $('.sell-media-grid-item').removeClass('sell-media-active-popup-item');
            $('#sell-media-dialog-box-target').removeClass('loaded');
            if ('sell-media-empty-dialog-box' !== $(this).attr('id')) {
                $('#sell-media-dialog-box-target .sell-media-dialog-box-content').html('');
            }
        }
    });

    /**
     * Close popup when clicking the esc keyboard button.
     * Next popup on next keybord button.
     * Previous popup on previous keybord button.
     */
    $(document).keyup(function(event) {
        // Esc
        if (event.which == '27') {
            $('.sell-media-dialog-box').removeClass('is-visible');
            $('body').removeClass('quick_view_modal_popup_open');
            $('.sell-media-grid-item').removeClass('sell-media-active-popup-item');
            $('#sell-media-dialog-box-target').removeClass('loaded');
            if ('sell-media-empty-dialog-box' !== $('.sell-media-dialog-box').attr('id')) {
                $('#sell-media-dialog-box-target .sell-media-dialog-box-content').html('');
            }
        }

        // Next
        if (event.which == '39') {
            sell_media_popup_next_prev('next');
        }

        // Prev
        if (event.which == '37') {
            sell_media_popup_next_prev('prev');
        }
    });

    // Prev slide on prev button.
    $(document).on('click', '.sell-media-dialog-box-prev', function(event) {
        event.preventDefault();
        sell_media_popup_next_prev('prev');
        return false;
    });

    // Next slide on next button.
    $(document).on('click', '.sell-media-dialog-box-next', function(event) {
        event.preventDefault();
        sell_media_popup_next_prev('next');
        return false;
    });

    /**
     * Check the required fields and change state of add to cart button
     */
    function required_fields() {
        // if size, license, or type (print/download) fields exists, disable add button
        if ($('#sell_media_download_size_fieldset').length || $('#sell_media_download_license_fieldset').length || $('#sell_media_product_type').length) {
            $('.item_add').prop('disabled', true);
        } else {
            $('.item_add').prop('disabled', false);
        }

        var required = '.sell-media-add-to-cart-fields [required]';
        // bind change for all your just click and keyup for all text fields
        $(document).on('change keyup', required, function() {
            var flag = 0;
            // check every el in collection
            $(required).each(function() {
                if ($(this).val() != '') flag++;
            });
            // number of nonempty (nonchecked) fields == nubmer of required fields
            if (flag == $(required).length)
                $('.item_add').prop('disabled', false);
            else
                $('.item_add').prop('disabled', true);
        });
    }

    /**
     * When the user clicks on our trigger we set-up the overlay,
     * launch our dialog to load the terms of service.
     */
    $(document).on('click', '.sell-media-empty-dialog-trigger', function() {
        $('#sell-media-dialog-box-target').addClass('loaded');
        popup();
    });

    /**
     * Resize dialog
     *
     * if user resizes the window, call the same function again
     * to make sure the overlay fills the screen and dialog box is aligned to center
     */
    $(window).resize(function() {
        popup_resize();
        resize_item_overlay();
    });

    /**
     * Resize item overlays to match image
     */
    function resize_item_overlay() {
        $('#sell-media-archive .sell-media-item').each(function(i, elem) {
            var width = $(this).children('img').width();
            // $(this).find('.sell-media-quick-view').css({'width':width});
        });
    }

    /**
     * Checkout click
     */
    $(document).on('click', '.sell-media-cart-checkout', function() {
        $(this).prop('disabled', true).css({ "cursor": "progress" }).text(sell_media.checkout_wait_text);
    });

    /**
     * Show search options when user clicks inside the search field
     */
    $('.sell-media-search-query').on('click', function() {
        var parent = $(this).parents('div.sell-media-search');

        parent.find('.sell-media-search-hidden, .sell-media-search-close').show();
        parent.find('.sell-media-search-form').addClass('active');
    });

    /**
     * Hide search options when user clicks outside
     */
    $(document).on('click', function(event) {
        if (!$(event.target).closest('#sell-media-search-form').length) {
            $('.sell-media-search-hidden').hide();
            $('.sell-media-search-form').removeClass('active');
        }
    });

    /**
     * Terms of service checkbox
     */
    $('#sell_media_terms_cb').on('click', function() {
        $this = $(this);
        $this.val() == 'checked' ? $this.val('') : $this.val('checked');
    });

    /**
     * Size/License selections
     */
    $(document).on('change', '#sell_media_download_wrapper .sell-media-add-to-cart-fieldset select', function() {

        // get the price from the selected option
        var price = $('#sell_media_item_size :selected').data('price');
        // if the price doesn't exist, set the price to the total shown
        // either the custom price of the item or the default price from settings
        if (price == undefined || price == 0) {
            price = $('#sell_media_item_base_price').length ? $('#sell_media_item_base_price').val() : 0;
        }

        // Hide or Show checkout and add to cart button
        if ($(this).attr('id') == 'sell_media_item_size') {
            $('.item_add').show();
            $('.sell-media-checkout-btn').remove();
        }

        sum = parseFloat(price);
        $('#sell_media_download_wrapper fieldset.sell-media-add-to-cart-fieldset').each(function() {
            // check for selected markup or single markup
            var option = $('option:selected', $(this).children('select')).data('price');
            
            if( $(this).find('select option:selected').data('name') ) {
                var markup = $(this).find('select option:selected').data('price');
                var markup_name = $(this).find('select option:selected').data('name');
                var markup_id = $(this).find('select option:selected').val();
            } else {
                var markup = '';
                var markup_name = '';
                var markup_id = '';
            }

            // selected tax doesn't have markup
            if (markup !== undefined && markup > 0) {
                sum += parseFloat((markup / 100) * price);
            }

            total_sum = sum.toFixed(2);

            // set price_group id so it is passed to cart
            var price_group = $('#sell_media_item_size :selected').data('id');
            if (price_group != null)
                $('.item_pgroup').attr('value', price_group);

            // set item_size so it is passed to cart
            var size = $('#sell_media_item_size :selected').data('size');
            if (size != null)
                $('.item_size').attr('value', size);

            if ($(this).attr('id') == 'sell_media_download_licenses_fieldset') {
                // set license name for display on cart
                if (markup_name != null)
                    $('.item_usage').attr('value', markup_name);

                // set license id
                if (markup_id != null)
                    $('.item_license').attr('value', markup_id);
            } else {
                if ($(this).attr('id') != 'sell_media_download_size_fieldset') {
                    var taxonomy = $(this).children('select').data('markup-taxonomy');
                    item_markup = $('option:selected', $(this).children('select')).data('name');
                    item_markup_id = $(this).children('select').val();
                    if (!item_markup)
                        item_markup = 'No ' + taxonomy;
                    if (!item_markup_id)
                        item_markup_id = 0;

                    $('.item_markup_' + taxonomy).attr({ 'value': item_markup });
                    $('.item_markup_' + taxonomy + '_id').attr({ 'value': item_markup_id });
                }
            }
            // set the license description
            var license_desc = $('#sell_media_item_licenses :selected').attr('title');
            // must use .attr since .data types are cached by jQuery
            if (license_desc) {
                $('#licenses_desc').attr('data-tooltip', license_desc).show();
            } else {
                $('#licenses_desc').hide();
            }
        });

        $('#total').text(total_sum);
        $('#total').attr('data-price', total_sum);

    });

    /**
     * Lightbox
     */
    $(document).on('click', '.sell-media-add-to-lightbox', function(event) {

        event.preventDefault();

        var post_id = $(this).data('id'),
            attachment_id = $(this).data('attachment-id'),
            selector = $('#sell-media-lightbox-content #sell-media-' + attachment_id);

        var data = {
            action: 'sell_media_update_lightbox',
            post_id: post_id,
            attachment_id: attachment_id,
            _nonce: sell_media._nonce
        };

        $.ajax({
            type: 'POST',
            url: sell_media.ajaxurl,
            data: data,
            success: function(msg) {
                $('.lightbox-counter').text(msg.count);
                $('#lightbox-' + post_id).text(msg.text);
                $('#lightbox-' + post_id).attr("title", msg.text);
                $(selector).hide();
                if (msg.text == 'Remove') {
                    $('.lightbox-notice').fadeIn('fast');
                } else {
                    $('.lightbox-notice').fadeOut('fast');
                }
            }
        });
    });

    // Empty the lightbox
    $('.empty-lightbox').on('click', function(event) {
        event.preventDefault();

        var emptied = $.removeCookie('sell_media_lightbox', { path: '/' });

        if (emptied) {
            $('#sell-media-grid-item-container').remove();
            $('#sell-media-lightbox-content').html($(this).data('empty-text'));
            $('.lightbox-counter').text(0);
        }
    });

    // Count lightbox
    function count_lightbox() {
        var cookie = $.cookie('sell_media_lightbox');
        if (cookie === undefined) {
            return 0;
        } else {
            var data = $.parseJSON(cookie),
                keys = [];
            $.each(data, function(key, value) {
                keys.push(key);
            });
            return keys.length;
        }
    }

    // Lightbox menu
    $('<span class="lightbox-counter">' + count_lightbox() + '</span>').appendTo('.lightbox-menu a');

    var currency_symbol = sell_media.currencies[sell_media.currency_symbol].symbol;
    // Checkout total menu
    $('<span class="sell-media-cart-total checkout-counter-wrap">' + currency_symbol + '<span class="checkout-price">0</span></span>').appendTo('.checkout-total a');

    // Checkout qty menu
    $('<span class="sell-media-cart-quantity checkout-counter">0</span>').appendTo('.checkout-qty a:first');

    /**
     * Update menu cart qty and subtotal on load
     */
    sm_update_cart_menu();

    // Reload current location
    $('.reload').click(function() {
        location.reload();
    });


    /**
     * Add to cart
     */
    $(document).on('click', 'button.item_add', function() {
        var $button = $(this);
        var data = $("form#sell-media-cart-items").serializeArray();
        // TODO: add .first() here - I see and issue on the page where couple of blocks present: "price: 1.001.001.00"
        var price = $("span#total").text();
        var qty = $(".checkout-qty").text();
        var ajaxurl = sell_media.ajaxurl + '?action=sm_add_to_cart&price=' + price;
        $button.addClass('sell-media-spinner-light');
        // Add cart item in session.
        $.post(ajaxurl, data, function(response) {
            var message = sell_media.added_to_cart;

            $('.sell-media-added').remove();
            var res = jQuery.parseJSON(response);

            window.location = sell_media.checkout_url;
            return;

            if (typeof res == 'object' && '0' == res.code) {
                var message = res.message;
            }

            $('#sell-media-add-to-cart').after('<p class="sell-media-added">' + message + '</p>');
            $button.hide();
            $('#sell-media-add-to-cart').append('<a class="sell-media-button sell-media-checkout-btn" href="'+sell_media.checkout_url+'">'+sell_media.checkout_text+'</a>');
            sm_update_cart_menu();
            $button.removeClass('sell-media-spinner-light');
        });

        // Disable add to cart button.
        $button.attr("disabled", "disabled");

    });

    // Decrease item qty.
    $(document).on('click', '.sell-media-cart-decrement', function() {
        sm_update_cart_item($(this), 'minus');
    });

    // Increase item qty.
    $(document).on('click', '.sell-media-cart-increment', function() {
        sm_update_cart_item($(this), 'plus');
    });

    // Submit to payment gateway
    $(document).on('click', '#pay_via_paypal_purchase', function() {        
        var btn = $(this);
        btn.prop('disabled', true).css({ "cursor": "progress" }).text(sell_media.checkout_wait_text);            
            $.ajax({
                type: "POST",
                url: sell_media.ajaxurl,
                data: {
                    action: 'paypal_process',
                    gateway: 'paypal',
                    discount: $('#discount-id').val(),
                    _nonce: sell_media_paypal_obj.paypal_nonce
                },
                success: function(response) {

                    if (response.status) {
                        window.location = response.redirect_uri;
                    } else {
                        btn.prop('disabled', false).text(sell_media.checkout_text);
                    }
                }, error: function (error) {
                    btn.prop('disabled', false).text(sell_media.checkout_text);
                }
        });        
    });

    /**
     * Filters Shortcode
     */
    function sell_media_ajax_filter_show_items() {

        $('#sell-media-ajax-filter-content .sell-media-grid-item.hide').each(function() {
            $(this).fadeIn('slow').delay();
            $(this).removeClass('hide');
        });

    }

    function sell_media_ajax_filter_request(new_data, append) {
        var old_data = {
            "action": "sell_media_ajax_filter"
        };
        var final_data = $.extend(old_data, new_data);

        if (!append)
            $('#sell-media-ajax-filter-content').html('').addClass('sell-media-ajax-loader ');
        else {
            $('#sell-media-ajax-filter-content .load-more-button').html('').addClass('sell-media-ajax-loader ');

        }
        $.post(sell_media.ajaxurl, final_data, function(response) {
            if ('' == response)
                return false;

            var content = $.parseHTML(response.content)
            if (!append) {
                $(content).find('.sell-media-grid-item').addClass('hide');
                $('#sell-media-ajax-filter-content').html(content);
                $('#sell-media-ajax-filter-content').append(response.load_more);
            } else {
                $(content).addClass('hide');
                $('#sell-media-ajax-filter-content div.load-more-button').remove();
                $('#sell-media-ajax-filter-content .sell_media_ajax_filter_items_container').append(content);
                $('#sell-media-ajax-filter-content').append(response.load_more);
            }
            sell_media_ajax_filter_show_items();
            $('#sell-media-ajax-filter-container .sell-media-ajax-filter-tabs .sell-media-ajax-filter-tab-item').removeClass('stop-click');
            $('#sell-media-ajax-filter-container .sell-media-ajax-filter-terms a').removeClass('stop-click');
            $('#sell-media-ajax-filter-content').removeClass('sell-media-ajax-loader ');

            if (sell_media.thumbnail_layout && sell_media.thumbnail_layout === 'sell-media-masonry') {
                macy_init();
            }

        });
    }

    // Event for the ajax filters.
    $(document).on('click', '#sell-media-ajax-filter-container .sell-media-ajax-filter-tabs .sell-media-ajax-filter-tab-item', function() {

        if ($(this).hasClass('stop-click') || $(this).hasClass('selected-tab'))
            return false;


        var tab_selected = $(this).attr('id');

        $('#sell-media-ajax-filter-container .sell-media-ajax-filter-tabs .sell-media-ajax-filter-tab-item').removeClass('selected-tab').addClass('stop-click');
        $(this).addClass('selected-tab');


        if ('keywords' == tab_selected) {
            $('#sell-media-ajax-filter-container .sell-media-ajax-filter-keyword-terms').show();
        } else {
            $('#sell-media-ajax-filter-container .sell-media-ajax-filter-keyword-terms').hide();
        }

        if ('collections' == tab_selected) {
            $('#sell-media-ajax-filter-container .sell-media-ajax-filter-collection-terms').show();
        } else {
            $('#sell-media-ajax-filter-container .sell-media-ajax-filter-collection-terms').hide();
        }


        if ('keywords' == tab_selected || 'collections' == tab_selected) {
            $('#sell-media-ajax-filter-container .sell-media-ajax-filter-tabs .sell-media-ajax-filter-tab-item').removeClass('stop-click');
            return false;
        }

        // Do ajax.
        var post_data = {
            'tab': tab_selected
        };

        sell_media_ajax_filter_request(post_data, false);

    });

    // Ajax keyword filter
    $(document).on('click', '#sell-media-ajax-filter-container .sell-media-ajax-filter-terms a', function(event) {

        event.preventDefault();

        if ($(this).hasClass('stop-click'))
            return false;

        var tab_selected = $('#sell-media-ajax-filter-container .sell-media-ajax-filter-tabs .sell-media-ajax-filter-tab-item.selected-tab').attr('id');
        var term_selected = $(this).attr('data-termid');

        $('#sell-media-ajax-filter-container .sell-media-ajax-filter-terms a').removeClass('selected-term');
        $(this).addClass('selected-term');

        $('#sell-media-ajax-filter-container .sell-media-ajax-filter-terms a').addClass('stop-click');

        // Do ajax.
        var post_data = {
            'tab': tab_selected,
            'term': term_selected
        };

        sell_media_ajax_filter_request(post_data, false);

    });

    // Filter load more.
    $(document).on('click', '.load-more-button a', function() {

        if ($(this).hasClass('stop-click'))
            return false;

        $(this).addClass('stop-click');

        var tab_selected = $('#sell-media-ajax-filter-container .sell-media-ajax-filter-tabs .sell-media-ajax-filter-tab-item.selected-tab').attr('id');
        var term_selected = $('#sell-media-ajax-filter-container .sell-media-ajax-filter-terms a.selected-term').attr('data-termid');
        var currentpage = $(this).attr('data-currentpage');
        // Do ajax.
        var post_data = {
            'tab': tab_selected,
            'term': term_selected,
            'paged': currentpage
        };

        sell_media_ajax_filter_request(post_data, true);

    });

    $(document).on('click', '.drop-down-close-button', function() {

        $(this).parent().hide();

        $('.sell-media-ajax-filter-tabs a').removeClass('selected-tab');

    });

    $(document).on('click', '.term-pagination span.next', function() {
        var pagination_wrap = $(this).parent();
        var parent = $(this).parent().parent();
        var current_group = parent.find('ul.current-term-group');
        var next_group = current_group.next();
        parent.find('ul').removeClass('current-term-group').addClass('hide');
        next_group.removeClass('hide').addClass('current-term-group').show();

        pagination_wrap.find('span.prev').show();
        if (parent.find('ul').index(next_group) == (parseInt(parent.find('ul').length) - 1)) {
            pagination_wrap.find('span.next').hide();
        } else {
            pagination_wrap.find('span.next').show();
        }
    });

    $(document).on('click', '.term-pagination span.prev', function() {
        var pagination_wrap = $(this).parent();
        var parent = $(this).parent().parent();
        var current_group = parent.find('ul.current-term-group');
        var prev_group = current_group.prev();
        parent.find('ul').removeClass('current-term-group').addClass('hide');
        prev_group.removeClass('hide').addClass('current-term-group');
        pagination_wrap.find('span.next').show();
        if (0 == parent.find('ul').index(prev_group)) {
            pagination_wrap.find('span.prev').hide();
        } else {
            pagination_wrap.find('span.next').show();
        }
    });

    $(document).on('click', '#sell_media_product_type input[name="type"]', function() {
        var data = {
            'id': $('#sell-media-cart-items .item_number').val(),
            'attachment_id': $('#sell-media-cart-items .item_attachment').val(),
            'type': $(this).val(),
            'action': 'sell_media_ajax_add_to_cart_button',
        };

        $('.button-container #sell-media-add-to-cart').html('');
        $.post(sell_media.ajaxurl, data, function(res) {
            $('.button-container #sell-media-add-to-cart').html(res);
        });
            
        $('#sell_media_product_type li').removeClass('selected-tab');
        $('#sell_media_product_type input[name="type"]:checked').parent().addClass('selected-tab');
    });

    if($('#sell_media_download_wrapper .sell-media-add-to-cart-fieldset select').length){
        $($('#sell_media_download_wrapper .sell-media-add-to-cart-fieldset select').children()[1]).attr('selected',true).trigger('change');
    }
}); // End jQuery document ready.

/**
 * Update the menu cart with Qty and Subtotal
 */
function sm_update_cart_menu() {
    var sell_media_cart_info = jQuery.parseJSON(jQuery.cookie('sell_media_cart_info') || null);
    if (sell_media_cart_info != null) {
        jQuery('.checkout-price').text(sell_media_cart_info.subtotal);
        jQuery('.checkout-counter').text(sell_media_cart_info.qty);
    }
}

/**
 * Calculate shipping costs
 */
function sm_calculate_shipping() {
    // Define vars.
    var total_shipping = 0,
        subtotal = 0,
        items = jQuery("#sell-media-checkout-cart .item"),
        total_print_qty = 0;

    // Check if sell media reprints is active.
    if ('undefined' === typeof sell_media_reprints) {
        return total_shipping;
    }

    // Get price of all items.
    items.each(function() {
        var price = jQuery(this).attr('data-price');
        var type = jQuery(this).attr('data-type');
        var current_qty = jQuery(this).find('.item-quantity').text();

        // Check if product is printable.
        if ('print' === type) {
            total_print_qty += parseInt(current_qty);
            subtotal += parseFloat(price) * parseFloat(current_qty);
        }
    });

    // Show print items are on cart.
    if (total_print_qty > 0) {
        jQuery('.sell-media-totals div.shipping').show();

        // Check if shipping is on total rate.
        if ('shippingTotalRate' == sell_media_reprints.reprints_shipping && '' !== sell_media_reprints.reprints_shipping_flat_rate) {
            var total_shipping = parseFloat(subtotal) * parseFloat(sell_media_reprints.reprints_shipping_flat_rate);
        }

        // Check if shipping is on flate rate.
        if ('shippingFlatRate' == sell_media_reprints.reprints_shipping && '' !== sell_media_reprints.reprints_shipping_flat_rate) {
            var total_shipping = parseFloat(sell_media_reprints.reprints_shipping_flat_rate);
        }

        // Check if shipping is on quantity rate.
        if ('shippingQuantityRate' == sell_media_reprints.reprints_shipping && '' !== sell_media_reprints.reprints_shipping_flat_rate) {
            var total_shipping = parseInt(total_print_qty) * parseFloat(sell_media_reprints.reprints_shipping_flat_rate);
        }
    }

    // Return total shipping cost.
    return total_shipping;
}

/**
 * Update cart total.
 */
function sm_update_cart_totals(isShippingUpdate) {
    // Define vars.
    var items = jQuery("#sell-media-checkout-cart .item"),
        currency_symbol = sell_media.currencies[sell_media.currency_symbol].symbol,
        subtotal = 0,
        tax = 0,
        total_shipping = 0,
        total_qty = 0;

    // Hide Shipping cost.
    jQuery('.sell-media-totals div.shipping').hide();

    // Get price of all items.
    items.each(function() {
        var price = jQuery(this).attr('data-price');
        var current_qty = jQuery(this).find('.item-quantity').text();

        total_qty += parseInt(current_qty);
        subtotal += parseFloat(price) * parseFloat(current_qty);
    });

    // Update menu qty counter
    jQuery('.checkout-counter').text(total_qty);

    // Update menu subtotal
    jQuery('.checkout-price').text(subtotal.formatMoney(2, '.', ','));

    // Set grand total.
    var grand_total = subtotal;

    // Add tax if tax is set.
    if (sell_media.tax > 0 && sell_media.tax_display == 'exclusive') {
        tax = parseFloat(subtotal) * parseFloat(sell_media.tax);
        grand_total = subtotal + tax;
    } else {
        jQuery('.sell-media-totals-table div.tax').hide();
    }

    // Add shipping cost.
    if ('2' === sell_media.shipping) {
        var total_shipping = sm_calculate_shipping();
        var grand_total = parseFloat(grand_total) + parseFloat(total_shipping);
    }

    // Show subtotal.
    jQuery('.sell-media-totals .sell-media-cart-total').html(currency_symbol + subtotal.formatMoney(2, '.', ','));

    // Show tax.
    jQuery('.sell-media-totals .sell-media-cart-tax').html(currency_symbol + tax.formatMoney(2, '.', ','));

    // Show shipping.
    jQuery('.sell-media-totals .sell-media-cart-shipping').html(currency_symbol + total_shipping.formatMoney(2, '.', ','));

    // Show Grand total.
    jQuery('.sell-media-totals .sell-media-cart-grand-total').html(currency_symbol + grand_total.formatMoney(2, '.', ','));
    if(typeof sell_media_calculate_discount !== "undefined") {
        sell_media_calculate_discount();
    }
    
    if(typeof sell_media_calculate_print_on_demand_shipping !== "undefined") {
        sell_media_calculate_print_on_demand_shipping(total_shipping, subtotal, isShippingUpdate );
    }
}
/**
 * Update cart item
 * @param  {object} el   Element of item
 * @param  {string} type Update type
 */
function sm_update_cart_item(el, type) {

    jQuery(".sell-media-cart-decrement, .sell-media-cart-increment").prop('disabled', true);
    jQuery(".sell-media-cart-decrement, .sell-media-cart-increment").removeClass( 'sell-media-update-cart-spinner' );
    el.addClass( 'sell-media-update-cart-spinner' );
    var parent = el.parents('li'),
        id = parent.attr('id'),
        price = parent.attr('data-price'),
        current_qty = parent.find('.item-quantity').text(),
        currency_symbol = sell_media.currencies[sell_media.currency_symbol].symbol,
        updated_qty = parseInt(current_qty) - 1;    
        
    // Add qty if type is 'plus'.
    if ('plus' === type)
        updated_qty = parseInt(current_qty) + 1;
    

    // Update price.
    var updated_price = parseInt(updated_qty) * parseFloat(price);
   
     // Update qty.
     parent.find('.item-quantity .count').text(updated_qty);

     // Update item total.
     parent.find('.item-total').html(currency_symbol + updated_price.formatMoney(2, '.', ','));     
    
    // Hide if qty is less than 1.
    if (updated_qty < 1) {
        parent.fadeOut('slow').remove();
        if (jQuery("#sell-media-checkout-cart .sell-media-cart-items li").length < 1) {
            jQuery("#sell-media-checkout-cart").fadeOut('slow');
            jQuery("#sell-media-empty-cart-message").fadeIn('slow');
        }
    }
    if (typeof sell_media_apply_discount_code !== "undefined" && jQuery(document).find('#discount-code').first().val()) {
        jQuery('#sell-media-discount-amount').addClass('ajax-loader');
        setTimeout(function(){
            sell_media_apply_discount_code();
        },1000);
    }

    // Update cart item in session.
    var isCartUpdate = jQuery.post(sell_media.ajaxurl, { action: 'sm_update_cart', cart_item_id: id, qty: updated_qty });    

    isCartUpdate.done( function() {        
        // Update cart total.
        var isShippingUpdate = parent.attr('data-type') == 'print_on_demand' ? 1 : 0;        
        sm_update_cart_totals(isShippingUpdate);
        setTimeout(function(){
            //Enabling button again
            jQuery(".sell-media-cart-decrement, .sell-media-cart-increment").prop('disabled', false );
            jQuery(".sell-media-cart-decrement, .sell-media-cart-increment").removeClass( 'sell-media-update-cart-spinner' );        
        }, 1000);

    });    
}

/**
 * After ajax call, init Macy.js again so masonry layouts work on Filters shortcodes
 */
function macy_init() {
    Macy.init({
        container: ".sell-media-grid-item-masonry-container",
        trueOrder: false,
        waitForImages: false,
        margin: 10,
        columns: 4,
        breakAt: {
            940: 3,
            768: 2,
            420: 1
        }
    });
}