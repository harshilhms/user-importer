jQuery(document).ready(function($) {
    $('#ui-import-form').on('submit', function(e) {
        e.preventDefault();

        let file = $('#import_file')[0].files[0];
        if (!file) return;

        $('#import_btn').hide();
        $('#filename').text(file.name);
        $('#file-info').html(`
            <strong>Title:</strong> ${file.name}<br>
            <strong>Size:</strong> ${(file.size / (1024 * 1024)).toFixed(2)} MB
        `);

        $('#progress-box').show();
        startImport(file);
    });

    function startImport(file) {
        let formData = new FormData();
        formData.append('action', 'ui_import_users');
        formData.append('import_file', file);
        formData.append('file_type', $('#file_type').val());
        formData.append('nonce', ui_ajax.nonce);

        $.ajax({
            url: ui_ajax.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                if (res.success) {
                    $('#percent').text('100%');
                    $('#processed').text(res.data.total);
                } else {
                    alert('Import failed.');
                }
                $('#import_btn').show();
            }
        });
    }
});
