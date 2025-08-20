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

    // ✅ Insert 10 sample data if table is empty
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    if ($count == 0) {
        $sample_data = [
            [
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
            ],
            [
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
            ],
            [
                'name' => 'Robert Brown',
                'email' => 'robertb@example.com',
                'phone' => '5551112222',
                'dob' => '1985-03-10',
                'sex' => 'Male',
                'cr_number' => 'CR54321',
                'address' => '789 Lake Avenue',
                'city' => 'Chicago',
                'country' => 'USA',
                'status' => 'active',
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emilyd@example.com',
                'phone' => '4443332211',
                'dob' => '1995-09-25',
                'sex' => 'Female',
                'cr_number' => 'CR11223',
                'address' => '22 Sunset Blvd',
                'city' => 'Miami',
                'country' => 'USA',
                'status' => 'inactive',
            ],
            [
                'name' => 'Michael Wilson',
                'email' => 'michaelw@example.com',
                'phone' => '3332221110',
                'dob' => '1988-11-05',
                'sex' => 'Male',
                'cr_number' => 'CR33445',
                'address' => '88 Oak Lane',
                'city' => 'Dallas',
                'country' => 'USA',
                'status' => 'active',
            ],
            [
                'name' => 'Sophia Johnson',
                'email' => 'sophiaj@example.com',
                'phone' => '2225559999',
                'dob' => '1993-02-14',
                'sex' => 'Female',
                'cr_number' => 'CR55667',
                'address' => '12 Green Park',
                'city' => 'Seattle',
                'country' => 'USA',
                'status' => 'active',
            ],
            [
                'name' => 'David Miller',
                'email' => 'davidm@example.com',
                'phone' => '1117778888',
                'dob' => '1982-08-30',
                'sex' => 'Male',
                'cr_number' => 'CR77889',
                'address' => '450 King Street',
                'city' => 'San Francisco',
                'country' => 'USA',
                'status' => 'inactive',
            ],
            [
                'name' => 'Olivia Taylor',
                'email' => 'oliviat@example.com',
                'phone' => '6664443333',
                'dob' => '1998-12-01',
                'sex' => 'Female',
                'cr_number' => 'CR99001',
                'address' => '321 Hill Road',
                'city' => 'Boston',
                'country' => 'USA',
                'status' => 'active',
            ],
            [
                'name' => 'Daniel Thomas',
                'email' => 'danielt@example.com',
                'phone' => '9992224444',
                'dob' => '1987-06-17',
                'sex' => 'Male',
                'cr_number' => 'CR22334',
                'address' => '19 River View',
                'city' => 'Houston',
                'country' => 'USA',
                'status' => 'active',
            ],
            [
                'name' => 'Ava Martinez',
                'email' => 'avam@example.com',
                'phone' => '7771115555',
                'dob' => '1991-04-22',
                'sex' => 'Female',
                'cr_number' => 'CR44556',
                'address' => '67 Pine Street',
                'city' => 'Denver',
                'country' => 'USA',
                'status' => 'inactive',
            ],
        ];

        foreach ($sample_data as $row) {
            $wpdb->insert($table, $row);
        }
    }
});
