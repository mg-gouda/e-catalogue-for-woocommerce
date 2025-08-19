jQuery(document).ready(function($) {
    // Initialize WordPress Color Picker
    if (typeof wp.colorPicker !== 'undefined') {
        $('.ecfw-color-field').wpColorPicker();
    }

    // Media Uploader for Cover Image
    $(document).on('click', '.ecfw-upload-button', function(e) {
        e.preventDefault();
        var button = $(this);
        var customUploader = wp.media({
            title: 'Choose Image',
            library: {
                type: 'image'
            },
            multiple: false
        }).on('select', function() {
            var attachment = customUploader.state().get('selection').first().toJSON();
            button.prev('input').val(attachment.url);
            button.siblings('.ecfw-image-preview').attr('src', attachment.url).show();
        }).open();
    });

    // Clear Cover Image
    $(document).on('click', '.ecfw-clear-button', function(e) {
        e.preventDefault();
        var button = $(this);
        button.prevAll('input[type="text"]').val('');
        button.siblings('.ecfw-image-preview').attr('src', '').hide();
    });
});