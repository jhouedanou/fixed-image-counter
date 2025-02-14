jQuery(document).ready(function($) {
    $('#upload_image_button').click(function() {
        var image = wp.media({
            title: 'Choisir une image',
            multiple: false
        }).open().on('select', function() {
            var uploaded_image = image.state().get('selection').first();
            var image_id = uploaded_image.id;
            
            $('#fixed_image_id').val(image_id);
            $('#image_preview').html('<img src="' + uploaded_image.attributes.url + '" style="max-width:200px;"/>');
            
            // Sauvegarde l'ID de l'image via AJAX
            $.post(ajaxurl, {
                action: 'save_fixed_image',
                image_id: image_id
            });
        });
    });
});