jQuery(function($) {
    $(window).load(function(){
        var lightbox_data = localStorage.getItem( document.domain + '_sell_media_lightbox_data' );
        //alert(lightbox_data);
            var data = {
                action: 'sell_media_lightbox',
                lightbox_ids: lightbox_data,
                lightbox_domain: document.domain + '_sell_media_lightbox_data'
            };
            $.ajax({
            type: 'POST',
            url: sell_media.ajaxurl,
            data: data,
            success: function(msg){
                $("#lightbox_content").html(msg);
            }
        });
    });
});