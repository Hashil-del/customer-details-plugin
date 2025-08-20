<?php
if (!defined('ABSPATH')) exit;

class CDP_Admin {
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'cdp_customers';

        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_notices', [$this, 'notices']);

        // Form handlers
        add_action('admin_post_cdp_add_customer', [$this, 'handle_add']);
        add_action('admin_post_cdp_update_customer', [$this, 'handle_update']);
        add_action('admin_post_cdp_delete_customer', [$this, 'handle_delete']);
    }

    /* ---------------- MENU ---------------- */
    public function menu() {
        add_menu_page(
            'Customers', 'Customers', 'manage_options',
            'cdp_customers', [$this, 'page_list'],
            'dashicons-groups', 26
        );

        add_submenu_page(
            'cdp_customers', 'Add New Customer', 'Add New',
            'manage_options', 'cdp_add_customer', [$this, 'page_add']
        );

        // Hidden edit page
        add_submenu_page(
            null, 'Edit Customer', 'Edit Customer',
            'manage_options', 'cdp_edit_customer', [$this, 'page_edit']
        );
    }

    /* ---------------- NOTICES ---------------- */
    public function notices() {
        if (!isset($_GET['cdp_msg'])) return;
        $map = [
            'added'   => ['Customer added successfully.', 'success'],
            'updated' => ['Customer updated successfully.', 'success'],
            'deleted' => ['Customer deleted successfully.', 'success'],
            'exists'  => ['WP user already exists. Customer saved only.', 'warning'],
            'emaildupe'=> ['Email already exists in customer table.', 'error'],
            'invalid' => ['Invalid request.', 'error'],
            'notfound'=> ['Customer not found.', 'error'],
        ];
        [$text, $cls] = $map[$_GET['cdp_msg']] ?? [null, null];
        if (!$text) return;

        $class = $cls === 'success' ? 'notice notice-success'
                : ($cls === 'warning' ? 'notice notice-warning'
                : 'notice notice-error');

        echo '<div class="'.esc_attr($class).'"><p>'.esc_html($text).'</p></div>';
    }

    /* ---------------- HELPERS ---------------- */
    private function age_from_dob($dob) {
        try {
            $b = new DateTime($dob);
            $n = new DateTime('now');
            return $n->diff($b)->y;
        } catch (\Exception $e) { return ''; }
    }
    private function clean_phone($phone) {
        return preg_replace('/\D+/', '', $phone ?? '');
    }
    private function field($arr, $key, $default = '') {
        return isset($arr[$key]) ? sanitize_text_field($arr[$key]) : $default;
    }
    private function select($name, $value, $options) {
        echo '<select name="'.esc_attr($name).'" required>';
        foreach ($options as $opt) {
            printf('<option value="%1$s"%2$s>%1$s</option>',
                esc_attr($opt), selected($value, $opt, false));
        }
        echo '</select>';
    }

    /* ---------------- LIST PAGE ---------------- */
    public function page_list() {
        global $wpdb;

        $s = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $paged = max(1, intval($_GET['paged'] ?? 1));
        $pp = 10;
        $offset = ($paged - 1) * $pp;

        $where = 'WHERE 1=1';
        $params = [];

        if ($s !== '') {
            $like = '%' . $wpdb->esc_like($s) . '%';
            $where .= " AND (name LIKE %s OR email LIKE %s OR phone LIKE %s OR cr_number LIKE %s OR city LIKE %s OR country LIKE %s)";
            array_push($params, $like, $like, $like, $like, $like, $like);
        }

        $total = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table} $where", $params));

        $sql = "SELECT * FROM {$this->table} $where ORDER BY id DESC LIMIT %d OFFSET %d";
        $rows = $wpdb->get_results($wpdb->prepare($sql, array_merge($params, [$pp, $offset])));

        $base_url = remove_query_arg(['cdp_msg','paged'], menu_page_url('cdp_customers', false));
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Customers</h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=cdp_add_customer')); ?>" class="page-title-action">Add New</a>
            <hr class="wp-header-end">

            <form method="get">
                <input type="hidden" name="page" value="cdp_customers">
                <p class="search-box">
                    <input type="search" name="s" value="<?php echo esc_attr($s); ?>" />
                    <input type="submit" class="button" value="Search">
                </p>
            </form>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Age</th><th>Sex</th><th>City</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="9">No customers found.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td><?php echo (int)$r->id; ?></td>
                        <td><?php echo esc_html($r->name); ?></td>
                        <td><?php echo esc_html($r->email); ?></td>
                        <td><?php echo esc_html($r->phone); ?></td>
                        <td><?php echo esc_html($this->age_from_dob($r->dob)); ?></td>
                        <td><?php echo esc_html($r->sex); ?></td>
                        <td><?php echo esc_html($r->city); ?></td>
                        <td><?php echo esc_html($r->status); ?></td>
                        <td>
                            <a href="<?php echo esc_url( add_query_arg(['page'=>'cdp_edit_customer','id'=>$r->id], admin_url('admin.php')) ); ?>">Edit</a>
                            &nbsp;|&nbsp;
                            <form style="display:inline" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Delete this customer?');">
                                <?php wp_nonce_field('cdp_delete_'.$r->id); ?>
                                <input type="hidden" name="action" value="cdp_delete_customer">
                                <input type="hidden" name="id" value="<?php echo (int)$r->id; ?>">
                                <input type="submit" class="link-delete" value="Delete">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>

            <?php
            $pages = (int) ceil($total / $pp);
            if ($pages > 1) {
                echo '<div class="tablenav"><div class="tablenav-pages">';
                for ($p = 1; $p <= $pages; $p++) {
                    $url = esc_url( add_query_arg(['paged'=>$p, 's'=>$s], $base_url) );
                    $class = $p === $paged ? ' class="page-numbers current"' : ' class="page-numbers"';
                    echo "<a{$class} href=\"$url\">$p</a> ";
                }
                echo '</div></div>';
            }
            ?>
        </div>
        <?php
    }

    /* ---------------- ADD PAGE ---------------- */
    public function page_add() {
        $this->form(null, 'cdp_add_customer', 'Add Customer');
    }

    /* ---------------- EDIT PAGE ---------------- */
    public function page_edit() {
        global $wpdb;
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            wp_safe_redirect( add_query_arg('cdp_msg','notfound', menu_page_url('cdp_customers', false)) );
            exit;
        }
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE id=%d", $id));
        if (!$row) {
            wp_safe_redirect( add_query_arg('cdp_msg','notfound', menu_page_url('cdp_customers', false)) );
            exit;
        }
        $this->form($row, 'cdp_update_customer', 'Update Customer');
    }

    /* ---------------- FORM (ADD/EDIT) ---------------- */
    private function form($row, $action, $button) {
        $is_edit = (bool)$row;
        $v = function($k) use ($row){ return $row ? esc_attr($row->$k) : ''; };
        ?>
        <div class="wrap">
            <h1><?php echo $is_edit ? 'Edit Customer' : 'Add New Customer'; ?></h1>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field($action . ($is_edit ? '_'.$row->id : '')); ?>
                <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$row->id; ?>">
                <?php endif; ?>

                <table class="form-table">
                    <tr><th>Name</th><td><input type="text" name="name" value="<?php echo $v('name'); ?>" required class="regular-text"></td></tr>
                    <tr><th>Email</th><td><input type="email" name="email" value="<?php echo $v('email'); ?>" required class="regular-text"></td></tr>
                    <tr><th>Phone</th><td><input type="text" name="phone" value="<?php echo $v('phone'); ?>" required></td></tr>
                    <tr><th>Date of Birth</th><td><input type="date" name="dob" value="<?php echo $v('dob'); ?>" required></td></tr>
                    <tr><th>Sex</th><td><?php $this->select('sex', $row->sex ?? 'Male', ['Male','Female','Other']); ?></td></tr>
                    <tr><th>CR Number</th><td><input type="text" name="cr_number" value="<?php echo $v('cr_number'); ?>" required></td></tr>
                    <tr><th>Address</th><td><textarea name="address" rows="3" class="large-text"><?php echo esc_textarea($row->address ?? ''); ?></textarea></td></tr>
                    <tr><th>City</th><td><input type="text" name="city" value="<?php echo $v('city'); ?>"></td></tr>
                    <tr><th>Country</th><td><input type="text" name="country" value="<?php echo $v('country'); ?>"></td></tr>
                    <tr><th>Status</th><td><?php $this->select('status', $row->status ?? 'active', ['active','inactive']); ?></td></tr>
                </table>

                <?php submit_button($button); ?>
                <a class="button button-secondary" href="<?php echo esc_url(menu_page_url('cdp_customers', false)); ?>">Back to list</a>
            </form>
        </div>
        <?php
    }
/* ---------------- HANDLERS ---------------- */
public function handle_add() {
    if (!current_user_can('manage_options')) wp_die('Unauthorized.');
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'cdp_add_customer')) {
        wp_safe_redirect( add_query_arg('cdp_msg','invalid', admin_url('admin.php?page=cdp_customers')) );
        exit;
    }
    global $wpdb;

    $name = $this->field($_POST, 'name');
    $email = sanitize_email($_POST['email'] ?? '');
    $phone = $this->clean_phone($_POST['phone'] ?? '');
    $dob = $this->field($_POST, 'dob');
    $sex = $this->field($_POST, 'sex');
    $cr = $this->field($_POST, 'cr_number');
    $addr = sanitize_textarea_field($_POST['address'] ?? '');
    $city = $this->field($_POST, 'city');
    $country = $this->field($_POST, 'country');
    $status = $this->field($_POST, 'status', 'active');

    // Check duplicate
    $exists_email = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table} WHERE email=%s", $email));
    if ($exists_email) {
        wp_safe_redirect( add_query_arg('cdp_msg','emaildupe', admin_url('admin.php?page=cdp_add_customer')) );
        exit;
    }

    // Create WP user if not exists
    $wp_user = get_user_by('email', $email);
    $notice = '';
    if (!$wp_user) {
        $user_id = wp_insert_user([
            'user_login' => $email,
            'user_email' => $email,
            'user_pass'  => $phone,
            'role'       => 'contributor',
        ]);
        if (is_wp_error($user_id)) $notice = '&cdp_msg=warning';
    } else {
        $notice = '&cdp_msg=exists';
    }

    $wpdb->insert($this->table, [
        'name'=>$name,'email'=>$email,'phone'=>$phone,'dob'=>$dob,'sex'=>$sex,'cr_number'=>$cr,
        'address'=>$addr,'city'=>$city,'country'=>$country,'status'=>$status
    ], ['%s','%s','%s','%s','%s','%s','%s','%s','%s','%s']);

    wp_safe_redirect( admin_url('admin.php?page=cdp_customers&cdp_msg=added') . $notice );
    exit;
}

public function handle_update() {
    if (!current_user_can('manage_options')) wp_die('Unauthorized.');
    $id = intval($_POST['id'] ?? 0);
    if (!$id || !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'cdp_update_customer_'.$id)) {
        wp_safe_redirect( add_query_arg('cdp_msg','invalid', admin_url('admin.php?page=cdp_customers')) );
        exit;
    }
    global $wpdb;

    $name = $this->field($_POST, 'name');
    $email = sanitize_email($_POST['email'] ?? '');
    $phone = $this->clean_phone($_POST['phone'] ?? '');
    $dob = $this->field($_POST, 'dob');
    $sex = $this->field($_POST, 'sex');
    $cr = $this->field($_POST, 'cr_number');
    $addr = sanitize_textarea_field($_POST['address'] ?? '');
    $city = $this->field($_POST, 'city');
    $country = $this->field($_POST, 'country');
    $status = $this->field($_POST, 'status', 'active');

    // Check dupe
    $dupe = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table} WHERE email=%s AND id<>%d", $email, $id));
    if ($dupe) {
        wp_safe_redirect( add_query_arg(['cdp_msg'=>'emaildupe','page'=>'cdp_edit_customer','id'=>$id], admin_url('admin.php')) );
        exit;
    }

    $wpdb->update($this->table, [
        'name'=>$name,'email'=>$email,'phone'=>$phone,'dob'=>$dob,'sex'=>$sex,'cr_number'=>$cr,
        'address'=>$addr,'city'=>$city,'country'=>$country,'status'=>$status
    ], ['id'=>$id]);

    wp_safe_redirect( admin_url('admin.php?page=cdp_customers&cdp_msg=updated') );
    exit;
}

public function handle_delete() {
    if (!current_user_can('manage_options')) wp_die('Unauthorized.');
    $id = intval($_POST['id'] ?? 0);
    if (!$id || !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'cdp_delete_'.$id)) {
        wp_safe_redirect( add_query_arg('cdp_msg','invalid', admin_url('admin.php?page=cdp_customers')) );
        exit;
    }
    global $wpdb;
    $wpdb->delete($this->table, ['id'=>$id], ['%d']);
    wp_safe_redirect( admin_url('admin.php?page=cdp_customers&cdp_msg=deleted') );
    exit;
}

}

new CDP_Admin();
