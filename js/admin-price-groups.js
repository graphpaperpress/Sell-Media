window.$ = jQuery;
var smPriceGroups;

/**
 * Our JS object for manipulating price groups
 */
var smPriceGroups = {
    options: {
        security: $('#_wpnonce').val()
    },
    init: function(){
        $.ajaxSetup({
            url: ajaxurl,
            type: "post",
            success: function( msg ){
                window.location.replace( msg );
            }
        });
    },
    save: function(){
        $.ajax({
            data: {
                action: "save_term",
                form: $('.wrap form').serialize(),
                security: smPriceGroups.options.security
            }
        });
    },
    delete: function( my_obj ){
        if ( confirm( my_obj.attr('data-message') ) == true ) {
            $.ajax({
                data: {
                    action: "delete_term",
                    term_id: my_obj.attr('data-term_id'),
                    security: smPriceGroups.options.security
                }
            });
        }
    },
    add: function(){
         $.ajax({
            data: {
                action: "add_term",
                term_name: $( '#sell_media_term_name' ).val(),
                security: smPriceGroups.options.security
            },
            global: false,
            success: function( msg ){
                window.location.replace( msg );
            }
        });
    }
};


jQuery( document ).ready(function( $ ){

    smPriceGroups.init();
    smPriceGroups.options.security = $('#_wpnonce').val();

    $('.sell-media-save-term').on('click', function( event ){
        smPriceGroups.save();
        event.preventDefault();
    });

    $('.sell-media-delete-term').on('click', function( event ){
        event.preventDefault();
        smPriceGroups.delete( $( this ) );
    });

    $('.sell-media-add-term').on('click', smPriceGroups.add );

    $('.sell-media-delete-term-group').on('click', function( event ){
        event.preventDefault();
        smPriceGroups.delete( $( this ) );
    });


    $( document ).on('click', '.sell-media-price-groups-repeater-add', function( event ){
        var counter = +($('.sell-media-price-groups-row:last').attr('data-index')) + 1 ;
        event.preventDefault();

        html = '<tr class="sell-media-price-groups-row" data-index="'+counter+'">';
        html += '<td>';
            html += '<input type="text" class="" name="new_child['+counter+'][name]" size="24" value="">';
            html += '<p class="description">Name</p>';
        html += '</td>';
        html += '<td>';
            html += '<input type="hidden" name="new_child['+counter+'][parent]" value="'+ $('.sell-media-price-group-parent-id:last').val() + '" />';
            html += '<input type="number" step="1" min="0" class="small-text" name="new_child['+counter+'][width]" value="">';
            html += '<p class="description">Width</p>';
        html += '</td>';
        html += '<td>';
            html += '<input type="number" step="1" min="0" class="small-text" name="new_child['+counter+'][height]" value="">';
            html += '<p class="description">Height</p>';
        html += '</td>';
        html += '<td>';
            html += '<span class="description">$</span>&nbsp;';
            html += '<input type="number" step="1" min="0" class="small-text" name="new_child['+counter+'][price]" value="">';
            html += '<p class="description">Price</p>';
        html += '</td>';
        html += '<td>';
            html += '<a href="#" class="sell-media-xit sell-media-price-group-repeater-remove" data-type="price">×</a>';
        html += '</td>';
        html += '</tr>';

        $('.sell-media-price-groups-table').append( html );
        counter++;
    });

    $( document ).on('click','.sell-media-price-group-repeater-remove', function( event ){
        event.preventDefault();
        if ( $('.sell-media-price-groups-table tbody tr').length == 1 ){
            alert('You must have at least one size option');
            return;
        }
        $(this).closest('tr').remove();
    });
});