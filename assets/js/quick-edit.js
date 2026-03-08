jQuery(function($) {
    $(document).on('click', '.editinline', function() {
        var $postRow = $(this).closest('tr');
        var postId = $postRow.attr('id').replace("post-", "");

        // Delay to ensure Quick Edit row is rendered
        setTimeout(function() {
            var clientId = $('#client-' + postId).data('term-id');
            var keywordId = $('#keywords-' + postId).data('term-id');
			console.log('Client ID:', clientId);
			console.log('Keyword ID:', keywordId);

            
            // Target selects within the visible inline edit row
            var $quickEditRow = $('#edit-' + postId);
            //$quickEditRow.find('#landing_page_client_field').val(clientId);
            $quickEditRow.find('select[name="landing_page_client"]').val(clientId);
            //$quickEditRow.find('#landing_page_keyword_field').val(keywordId);
            $quickEditRow.find('select[name="landing_page_keyword"]').val(keywordId);
        }, 0);
    });
});