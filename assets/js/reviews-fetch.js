jQuery(document).ready(function($) {
    $('#fetch-reviews-btn').on('click', function(e) {
        e.preventDefault();

        var postID = $('#post_ID').val();
        var statusEl = $('#fetch-reviews-status');

        statusEl.text('Fetching reviews...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fetch_google_reviews',
                security: lpmanager_vars.nonce,
                post_id: postID
            },
            success: function(response) {
                if (response.success) {
                    statusEl.text('Reviews fetched successfully. Injecting into field…');
                    const reviewsField = document.querySelector('[data-custom-id="reviews_html_code"]');
                    if (reviewsField) {
                        reviewsField.value = response.data;
                        statusEl.text('Done. Reviews code inserted into field.');
                    } else {
                        statusEl.text('Error: Could not find reviews field.');
                    }
                } else {
                    statusEl.text('Error: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                statusEl.text('AJAX error: ' + error);
            }
        });
    });
});
