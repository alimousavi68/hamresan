<?php 
// تابع برای ایجاد جدول
function create_reports_table() {
    global $wpdb;

    // نام جدول
    $table_name = $wpdb->prefix . 'hrm_reports';

    // بررسی وجود جدول
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // SQL برای ایجاد جدول
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            send_date datetime NOT NULL,
            send_post_id int NOT NULL,
            send_target_child_site_id int NOT NULL,
            send_status int NOT NULL,
            send_error_msg text,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // اجرای SQL
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}


// تابع برای دریافت تمام رکوردهای جدول i8_hrm_reports
function get_all_reports() {
    global $wpdb;

    // نام جدول
    $table_name = $wpdb->prefix . 'hrm_reports';

    // اجرای کوئری برای دریافت تمام رکوردها
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY `send_date` DESC", ARRAY_A);

    // بررسی اینکه آیا رکوردی وجود دارد یا خیر
    if (!empty($results)) {
        return $results; // بازگشت آرایه‌ای از رکوردها
    } else {
        return array(); // بازگشت آرایه خالی اگر رکوردی وجود نداشته باشد
    }
}


function insert_into_hrm_reports($send_date, $send_post_id, $send_target_child_site_id, $send_status, $send_error_msg = null) {
    global $wpdb; // دسترسی به شیء $wpdb برای کار با پایگاه داده

    // // بررسی وجود داده‌های ورودی
    // if (empty($send_date) || empty($send_post_id) || empty($send_target_child_site_id) || empty($send_status)) {
    //     return new WP_Error('missing_data', 'تمامی فیلدها الزامی هستند.');
    // }

    // آماده‌سازی داده‌ها برای درج
    $data = array(
        'send_date' => $send_date,
        'send_post_id' => intval($send_post_id),
        'send_target_child_site_id' => intval($send_target_child_site_id),
        'send_status' => intval($send_status),
        'send_error_msg' => sanitize_textarea_field($send_error_msg),
    );

    // درج داده‌ها در جدول
    $inserted = $wpdb->insert($wpdb->prefix . 'hrm_reports', $data);

    // بررسی موفقیت درج
    // if ($inserted) {
    //     return $wpdb->insert_id; // شناسه رکورد جدید
    // } else {
    //     return new WP_Error('insert_failed', 'درج رکورد با خطا مواجه شد.');
    // }
}

//delete all reports
function i8_hrm_delete_all_reports() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hrm_reports';
    $wpdb->query("DELETE FROM $table_name");
    return true;
}


