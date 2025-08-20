<?php
/**
 * Plugin Name: Customer Details Plugin
 * Description: Custom customer DB with Admin CRUD, WP user creation, frontend shortcode & AJAX.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

define('CDP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CDP_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once CDP_PLUGIN_PATH . 'admin.php'; // Admin UI

// Create table on activation (run once on activate)
register_activation_hook(__FILE__, function () {
    global $wpdb;
    $table = $wpdb->prefix . 'cdp_customers';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone BIGINT(20) NOT NULL,
        dob DATE NOT NULL,
        sex ENUM('Male','Female','Other') NOT NULL,
        cr_number VARCHAR(50) NOT NULL,
        address TEXT,
        city VARCHAR(50),
        country VARCHAR(50),
        status ENUM('active','inactive') DEFAULT 'active',
        PRIMARY KEY (id)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // ✅ Insert sample data if table is empty
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    if ($count == 0) {
        $wpdb->insert($table, [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'phone' => '1234567890',
            'dob' => '1990-05-15',
            'sex' => 'Male',
            'cr_number' => 'CR12345',
            'address' => '123 Main Street',
            'city' => 'New York',
            'country' => 'USA',
            'status' => 'active',
        ]);

        $wpdb->insert($table, [
            'name' => 'Jane Smith',
            'email' => 'janesmith@example.com',
            'phone' => '9876543210',
            'dob' => '1992-07-20',
            'sex' => 'Female',
            'cr_number' => 'CR67890',
            'address' => '456 Market Road',
            'city' => 'Los Angeles',
            'country' => 'USA',
            'status' => 'inactive',
        ]);
    }
});
