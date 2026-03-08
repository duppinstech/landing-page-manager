jQuery(document).ready(function($) {
  // Pre-select the active template on page load
  var activeTemplate = $('[name="carbon_fields_compact_input[_lpmanager_active_template]"]').val();
  if (activeTemplate) {
    $('.lpmanager-template-card').each(function() {
      if ($(this).data('template') === activeTemplate) {
        $(this).addClass('selected');
      }
    });
  }

  // Template selection click handler
  $('.lpmanager-template-card').on('click', function() {
    $('.lpmanager-template-card').removeClass('selected');
    $(this).addClass('selected');

    var selectedTemplate = $(this).data('template');

    // Set the value of the hidden input field to the selected template
    $('[name="carbon_fields_compact_input[_lpmanager_active_template]"]').val(selectedTemplate);
  });

  // Upload template handler with spinner
  $('#lpmanager_template_upload').on('change', function(e) {
    var file_data = $(this).prop('files')[0];
    if (!file_data) return;

    var form_data = new FormData();
    form_data.append('action', 'lpmanager_upload_template');
    form_data.append('nonce', lpmanager_vars.nonce);
    form_data.append('template_zip', file_data);

    $('#lpmanager-upload-spinner').show();

    $.ajax({
      url: lpmanager_vars.ajaxurl,
      type: 'POST',
      data: form_data,
      contentType: false,
      processData: false,
      success: function(response) {
        $('#lpmanager-upload-spinner').hide();
        if (response.success) {
          location.reload();
        } else {
          alert(response.data || 'Upload failed.');
        }
      },
      error: function() {
        $('#lpmanager-upload-spinner').hide();
        alert('Upload error.');
      }
    });
  });
	// mask API Keys
    var selectors = [
        'input[name="carbon_fields_compact_input[_lpmanager_google_maps_api_key]"]',
        'input[name="carbon_fields_compact_input[_lpmanager_brandfetch_api_key]"]'
    ];
    selectors.forEach(function(selector) {
        var $input = $(selector);
        if ($input.length && $input.val()) {
            var masked = '*'.repeat(Math.max(0, $input.val().length - 4)) + $input.val().slice(-4);
            $input.val(masked);
        }
    });
});