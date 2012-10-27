jQuery(function($) {

    $('.custom_upload_image_button').click(function() {
        formfield = $(this).siblings('.custom_upload_image');
        preview = $(this).siblings('.custom_preview_image');
        tb_show('', 'media-upload.php?type=image&TB_iframe=true');
        window.send_to_editor = function(html) {
            imgurl = $('img',html).attr('src');
            classes = $('img', html).attr('class');
            id = classes.replace(/(.*?)wp-image-/, '');
            formfield.val(id);
            preview.attr('src', imgurl);
            tb_remove();
        }
        return false;
    });

    $('.custom_clear_image_button').click(function() {
        var defaultImage = $(this).parent().siblings('.custom_default_image').text();
        $(this).parent().siblings('.custom_upload_image').val('');
        $(this).parent().siblings('.custom_preview_image').attr('src', defaultImage);
        return false;
    });

});

jQuery('.repeatable-add').click(function() {
    field = jQuery(this).closest('td').find('.custom_repeatable li:last').clone(true);
    fieldLocation = jQuery(this).closest('td').find('.custom_repeatable li:last');
    jQuery('input', field).val('').attr('name', function(index, name) {
        return name.replace(/(\d+)/, function(fullMatch, n) {
            return Number(n) + 1;
        });
    })
    field.insertAfter(fieldLocation, jQuery(this).closest('td'))
    return false;
});

jQuery('.repeatable-remove').click(function(){
    jQuery(this).parent().remove();
    return false;
});

jQuery('.custom_repeatable').sortable({
    opacity: 0.6,
    revert: true,
    cursor: 'move',
    handle: '.sort',
    update: function(event,ui){
        var i = 0;
        jQuery('#aa_repeatable-repeatable li input').each(function() {
            jQuery(this).attr('name', 'aa_repeatable['+ i++ +']');
        });
    }
});