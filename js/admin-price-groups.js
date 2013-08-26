window.$ = jQuery;
var smPriceGroups;

/**
 * Our JS object for manipulating price groups
 */
var smPriceGroups = {
    options: {},
    init: function(){

        smPriceGroups.options.security = $('#_wpnonce').val();
        smPriceGroups.options.taxonomy = $('.sell-media-save-term').attr('data-taxonomy');

        $.ajaxSetup({
            url: ajaxurl,
            type: "post",
            success: function( msg ){
                window.location.replace( msg );
            }
        });
    },
    save: function( my_obj ){
        $.ajax({
            data: {
                action: "save_term",
                security: smPriceGroups.options.security,
                taxonomy: smPriceGroups.options.taxonomy,
                form: $('.wrap form').serialize()
            }
        });
    },
    delete: function( term_id, taxonomy, message ){
        if ( confirm( message ) == true ) {
            $.ajax({
                data: {
                    action: "delete_term",
                    term_id: term_id,
                    taxonomy: taxonomy,
                    security: smPriceGroups.options.security
                }
            });
        }
    },
    add: function( term, taxonomy ){
        $.ajax({
            data: {
                action: "add_term",
                term_name: term,
                taxonomy: taxonomy,
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

    $('.sell-media-save-term').on('click', function( event ){
        event.preventDefault();
        smPriceGroups.save( $( this ) );
    });

    $('.sell-media-delete-term').on('click', function( event ){
        event.preventDefault();

        term_id = $(this).attr('data-term_id');
        message = $(this).attr('data-message');
        taxonomy = $(this).attr('data-taxonomy');

        smPriceGroups.delete( term_id, taxonomy, message );
    });

    $('.sell-media-add-term').on('click', function( e ){
        e.preventDefault();

        $term = $(this).parent().find('input');
        taxonomy = $(this).attr('data-taxonomy');

        smPriceGroups.add( $term.val(), taxonomy );
    });

    $('.sell-media-delete-term-group').on('click', function( event ){
        event.preventDefault();

        term_id = $(this).attr('data-term_id');
        message = $(this).attr('data-message');
        taxonomy = $(this).attr('data-taxonomy');

        smPriceGroups.delete( term_id, taxonomy, message );
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