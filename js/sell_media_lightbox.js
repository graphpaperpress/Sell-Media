jQuery(function($) {
    $(window).load(function(){
        var key = 'sellMediaLightbox';
        var lightbox_data = localStorage.getItem(key);
        var data = {
            action: 'sell_media_lightbox',
            lightbox_ids: lightbox_data,
            lightbox_domain: key
        };
        $.ajax({
            type: 'POST',
            url: sell_media.ajaxurl,
            data: data,
            success: function(msg){
                $('#sell-media-lightbox-content').html(msg);
            }
        });
    });
});
