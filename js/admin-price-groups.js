var smPriceGroups;

jQuery( document ).ready(function( $ ){

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
                    // location.reload();
                    console.log( msg );
                }
            });
        },
        update: function(){
            $.ajax({
                data: {
                    action: "update_term",
                    term_name: $('#sell_media_term_name').val(),
                    term_id: $('#sell_media_term_name').attr('data-term_id'),
                    security: smPriceGroups.options.security
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
        delete: function(){
            if ( confirm("Are you sure you want to delete this?") == true ) {
                $.ajax({
                    data: {
                        action: "sell_media_delete_term",
                        term_id: $( this ).attr('data-term_id'),
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

    smPriceGroups.init();
    $('.sell-media-update-term').on('click', smPriceGroups.update);
    $('.sell-media-save-term').on('click', smPriceGroups.save );
    $('.sell-media-delete-term').on('click', smPriceGroups.delete );
    $('.sell-media-add-term').on('click', smPriceGroups.add );
    $('.sell-media-delete-term-group').on('click', smPriceGroups.delete );
});