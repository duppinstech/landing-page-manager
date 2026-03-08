jQuery(document).ready(function($) {
    let debounceTimer;

    $('#tag-client_domain').on('blur', function() {
        const domain = $(this).val();

        // Skip if empty
        if (!domain) {
            return;
        }

        // Check if colors already filled
        const primaryColor   = $('#tag-client_primary_color').val();
        const secondaryColor = $('#tag-client_secondary_color').val();

        if (primaryColor && secondaryColor) {
            return; // Already populated, no need to fetch
        }

        // Fetch colors via REST API
        $.ajax({
            url: lpmanager_ajax.rest_url,
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', lpmanager_ajax.nonce);
            },
            data: {
                domain: domain
            },
            success: function(response) {
                if (response.primary) {
                    $('#tag-client_primary_color').val(response.primary);
                }
                if (response.secondary) {
                    $('#tag-client_secondary_color').val(response.secondary);
                }
            },
            error: function(xhr) {
                console.warn('Failed to fetch colors:', xhr.responseJSON);
            }
        });
    });
});