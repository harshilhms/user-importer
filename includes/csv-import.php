<?php

add_action('wp_ajax_ui_import_users', 'ui_import_users_callback');

function ui_import_users_callback() {
    check_ajax_referer('ui_nonce', 'nonce');

    if (!isset($_FILES['import_file'])) {
        wp_send_json_error(['message' => 'No file']);
    }

    $file = $_FILES['import_file']['tmp_name'];
    $file_name = $_FILES['import_file']['name'];
    $type = $_POST['file_type'];

    $data = []; // Read CSV or XML file
    if ($type === 'csv') {
        if (($handle = fopen($file, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $data[] = $row;
            }
            fclose($handle);
        }
    } else {
        $xml = simplexml_load_file($file);
        $data = json_decode(json_encode($xml), true);
    }

    $total = count($data);
    $processed = 0;
    $skipped = 0;

    foreach ($data as $user_data) {
        $email = is_array($user_data) ? $user_data[2] : $user_data['email']; // adapt as needed
        if (email_exists($email)) {
            $skipped++;
            continue;
        }

        wp_insert_user([
            'user_login' => sanitize_user($user_data[0]),
            'user_pass'  => wp_generate_password(),
            'user_email' => sanitize_email($email),
            'role'       => 'subscriber',
        ]);

        $processed++;
    }

    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'user_import_history', [
        'file_id'   => time(),
        'file_name'=> sanitize_file_name($file_name),
        'post_type'=> 'user',
        'processed'=> $processed,
        'total'    => $total,
        'skipped'  => $skipped,
        'status'   => 'completed'
    ]);

    wp_send_json_success(['processed' => $processed, 'total' => $total]);
}
