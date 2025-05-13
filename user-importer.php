<?php
/**
 * Plugin Name: User Importer
 * Description: Import users from XML or CSV with progress and history.
 * Version: 1.0
 * Author: Your Name
 */

define('UI_PATH', plugin_dir_path(__FILE__));
define('UI_URL', plugin_dir_url(__FILE__));

require_once UI_PATH . 'includes/admin-page.php';
require_once UI_PATH . 'includes/csv-import.php';

register_activation_hook(__FILE__, 'ui_create_import_table');

function ui_create_import_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_import_history';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        file_id BIGINT(20) NOT NULL,
        file_name VARCHAR(255),
        post_type VARCHAR(100),
        processed INT DEFAULT 0,
        total INT DEFAULT 0,
        skipped INT DEFAULT 0,
        status VARCHAR(50) DEFAULT 'new',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
