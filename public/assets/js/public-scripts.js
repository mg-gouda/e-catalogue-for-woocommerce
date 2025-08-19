jQuery(document).ready(function($) {
    // Handle PDF Download Button Click
    $(document).on('click', '.ecfw-download-pdf-button', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        var button = $(this);
        button.text(button.text() + '...'); // Simple loading indicator.
        button.prop('disabled', true);

        $.ajax({
            url: ecfw_public_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'ecfw_download_pdf',
                product_id: productId,
                nonce: ecfw_public_ajax.download_nonce
            },
            xhrFields: {
                responseType: 'blob' // Important for handling file downloads
            },
            success: function(blob) {
                var filename = 'woocommerce-catalog-' + productId + '.pdf';
                if (window.navigator && window.navigator.msSaveOrOpenBlob) {
                    // For IE
                    window.navigator.msSaveOrOpenBlob(blob, filename);
                } else {
                    // For other browsers
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(link.href);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert('Failed to generate PDF. Please try again.');
            },
            complete: function() {
                // Reset button state (remove simple loading indicator)
                button.text(button.text().replace('...', ''));
                button.prop('disabled', false);
            }
        });
    });

    // Handle PDF Share Button Click
    $(document).on('click', '.ecfw-share-pdf-button', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        var modal = $('#ecfw-share-email-modal');
        modal.data('product-id', productId);
        modal.find('#ecfw-share-product-id').val(productId); // Set hidden product ID

        // Reset form and messages
        modal.find('#ecfw-recipient-emails').val('');
        modal.find('#ecfw-email-subject').val('');
        modal.find('#ecfw-email-message').val('');
        modal.find('.ecfw-modal-message').hide().text('');
        modal.find('.ecfw-send-email-button').prop('disabled', false).text(ecfw_public_ajax.send_button_text);

        modal.addClass('active');
    });

    // Close Modal
    $(document).on('click', '.ecfw-close-modal-button, .ecfw-modal-overlay', function(e) {
        if ($(e.target).hasClass('ecfw-modal-overlay') || $(e.target).hasClass('ecfw-close-modal-button')) {
            $('#ecfw-share-email-modal').removeClass('active');
        }
    });

    // Prevent closing modal when clicking inside the content
    $(document).on('click', '.ecfw-modal-content', function(e) {
        e.stopPropagation();
    });

    // Handle Send Email Button Click
    $(document).on('click', '.ecfw-send-email-button', function(e) {
        e.preventDefault();
        var button = $(this);
        var modal = $('#ecfw-share-email-modal');
        var productId = modal.data('product-id');
        var recipients = modal.find('#ecfw-recipient-emails').val();
        var subject = modal.find('#ecfw-email-subject').val();
        var message = modal.find('#ecfw-email-message').val();
        var messageBox = modal.find('.ecfw-modal-message');

        // Basic validation
        if (!recipients) {
            messageBox.removeClass('success').addClass('error').text(ecfw_public_ajax.invalid_email_error).show();
            return;
        }

        button.prop('disabled', true).text(ecfw_public_ajax.sending_text);
        messageBox.hide();

        $.ajax({
            url: ecfw_public_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'ecfw_share_pdf_via_email',
                product_id: productId,
                recipients: recipients,
                subject: subject,
                message: message,
                nonce: ecfw_public_ajax.share_nonce
            },
            success: function(response) {
                if (response.success) {
                    messageBox.removeClass('error').addClass('success').text(ecfw_public_ajax.success_message).show();
                    // Optionally clear form fields after success
                    modal.find('#ecfw-recipient-emails').val('');
                    modal.find('#ecfw-email-subject').val('');
                    modal.find('#ecfw-email-message').val('');
                } else {
                    messageBox.removeClass('success').addClass('error').text(response.data.message || ecfw_public_ajax.error_message).show();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                messageBox.removeClass('success').addClass('error').text(ecfw_public_ajax.error_message).show();
            },
            complete: function() {
                button.prop('disabled', false).text(ecfw_public_ajax.send_button_text);
            }
        });
    });
});