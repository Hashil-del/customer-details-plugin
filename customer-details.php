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

// Create table + insert sample data on activation
register_activation_hook(__FILE__, function () {
    global $wpdb;
    $table = $wpdb->prefix . 'cdp_customers';
    $charset = $wpdb->get_charset_collate();

    // Create table if not exists
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

    // Insert sample 10 records if table is empty
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    if ($count == 0) {
        $sample_data = [
            ['John Doe', 'john@example.com', '9876543210', '1990-05-15', 'Male', 'CR001', '123 Street, NY', 'New York', 'USA', 'active'],
            ['Jane Smith', 'jane@example.com', '9876543211', '1988-03-22', 'Female', 'CR002', '456 Avenue, LA', 'Los Angeles', 'USA', 'active'],
            ['Ali Khan', 'ali@example.com', '9876543212', '1992-07-10', 'Male', 'CR003', '78 Road, Karachi', 'Karachi', 'Pakistan', 'inactive'],
            ['Priya Sharma', 'priya@example.com', '9876543213', '1995-01-05', 'Female', 'CR004', 'Delhi Street', 'Delhi', 'India', 'active'],
            ['David Lee', 'david@example.com', '9876543214', '1985-11-30', 'Male', 'CR005', 'Seoul Town', 'Seoul', 'South Korea', 'active'],
            ['Maria Garcia', 'maria@example.com', '9876543215', '1991-06-17', 'Female', 'CR006', 'Madrid Central', 'Madrid', 'Spain', 'inactive'],
            ['Ahmed Hassan', 'ahmed@example.com', '9876543216', '1989-04-09', 'Male', 'CR007', 'Cairo Lane', 'Cairo', 'Egypt', 'active'],
            ['Sophia Brown', 'sophia@example.com', '9876543217', '1993-09-12', 'Female', 'CR008', 'London Road', 'London', 'UK', 'active'],
            ['Carlos Silva', 'carlos@example.com', '9876543218', '1987-02-25', 'Male', 'CR009', 'Rio Street', 'Rio de Janeiro', 'Brazil', 'inactive'],
            ['Mei Lin', 'mei@example.com', '9876543219', '1994-08-03', 'Female', 'CR010', 'Shanghai Blvd', 'Shanghai', 'China', 'active'],
        ];

        foreach ($sample_data as $row) {
            $wpdb->insert($table, [
                'name'     => $row[0],
                'email'    => $row[1],
                'phone'    => $row[2],
                'dob'      => $row[3],
                'sex'      => $row[4],
                'cr_number'=> $row[5],
                'address'  => $row[6],
                'city'     => $row[7],
                'country'  => $row[8],
                'status'   => $row[9],
            ]);
        }
    }
});
