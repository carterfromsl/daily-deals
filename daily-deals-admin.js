jQuery(document).ready(function($) {
    // Media library selection - immediately update preview
    $('.select-image').click(function(e) {
        e.preventDefault();
        let button = $(this);
        let target = button.data('target');
        let preview = button.next('.image-preview');
        
        let frame = wp.media({
            title: 'Select or Upload an Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        frame.on('select', function() {
            let attachment = frame.state().get('selection').first().toJSON();
            $(target).val(attachment.url);
            preview.html('<img src="' + attachment.url + '" style="max-width: 150px;">');
        });

        frame.open();
    });
	
	// Remove image
    $('body').on('click', '.remove-image', function() {
        let button = $(this);
        button.closest('.image-preview').empty();
        let hiddenInput = button.closest('.day-image').find('input[type="hidden"]');
        hiddenInput.val('').trigger('change');
    });

    // Disable day-form if the checkbox is not checked on page load
    $('.day-checkbox').each(function() {
        let checkbox = $(this);
        let dayForm = checkbox.closest('.day-form');
        if (!checkbox.is(':checked')) {
            dayForm.addClass('disabled');
        }
    });

    // Toggle .disabled class when checkbox is checked/unchecked
    $('.day-checkbox').change(function() {
        let checkbox = $(this);
        let dayForm = checkbox.closest('.day-form');
        if (checkbox.is(':checked')) {
            dayForm.removeClass('disabled');
        } else {
            dayForm.addClass('disabled');
        }
    });
});
