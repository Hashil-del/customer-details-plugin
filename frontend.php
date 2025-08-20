<?php
if (!defined('ABSPATH')) exit;

// Shortcode for frontend
add_shortcode('cdp_customer_list', function() {
    ob_start();
    include CDP_PLUGIN_PATH . 'template/customer-list-template.php';
    return ob_get_clean();
});

// AJAX for frontend
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('cdp-frontend-js', CDP_PLUGIN_URL . 'assets/frontend.js', ['jquery'], null, true);
    wp_localize_script('cdp-frontend-js', 'cdp_front_ajax', ['ajax_url' => admin_url('admin-ajax.php')]);
});

add_action('wp_ajax_nopriv_cdp_fetch_front_customers', function() {
    global $wpdb;
    $table = $wpdb->prefix . 'cdp_customers';
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE status='active' AND (name LIKE %s OR email LIKE %s) LIMIT 50",
        "%$search%", "%$search%"
    ));
    echo json_encode($results);
    wp_die();
});
