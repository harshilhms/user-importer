<?php

add_action('admin_menu', 'ui_register_menu');
function ui_register_menu() {
    add_menu_page('User Import', 'User Import', 'manage_options', 'user-import', 'ui_import_page');
}

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_user-import') return;
    wp_enqueue_script('ui-script', UI_URL . 'assets/js/custom.js', ['jquery'], null, true);
    wp_localize_script('ui-script', 'ui_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ui_nonce')
    ]);
});

function ui_import_page() {
    ?>
    <div class="wrap">
        <h1>Import Users</h1>

        <form id="ui-import-form" enctype="multipart/form-data">
            <input type="file" id="import_file" name="import_file" required>
            <select id="file_type" name="file_type">
                <option value="csv">CSV</option>
                <option value="xml">XML</option>
            </select>
            <button type="submit" id="import_btn" class="button button-primary">Import</button>
        </form>

        <div id="file-info" style="margin-top:15px;"></div>
        <div id="progress-box" style="display:none; border:1px solid #ccc; padding:10px; margin-top:15px;">
            <strong>Percentage Complete:</strong> <span id="percent">0%</span><br>
            <strong>Processed:</strong> <span id="processed">0</span><br>
            <strong>File:</strong> <span id="filename"></span><br>
            <strong>Post Type:</strong> <span id="posttype">user</span><br>
            <div><img src="<?php echo admin_url('images/spinner.gif'); ?>" /></div>
        </div>

        <h2>Import History</h2>
        <?php ui_render_history_table(); ?>
    </div>
    <?php
}

function ui_render_history_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'user_import_history';
    $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");

    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>File</th><th>Post type</th><th>Processed</th><th>Skipped</th><th>Status</th><th>Date</th></tr></thead><tbody>';
    foreach ($results as $row) {
        echo "<tr>
                <td>$row->id</td>
                <td>$row->file_id</td>
                <td>$row->post_type</td>
                <td>{$row->processed} of {$row->total}</td>
                <td>{$row->skipped}</td>
                <td>{$row->status}</td>
                <td>{$row->created_at}</td>
              </tr>";
    }
    echo '</tbody></table>';
}
