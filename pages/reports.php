<?php

// تابع برای اضافه کردن منو و زیر منو
function add_reports_menu()
{
    // اضافه کردن زیر منو به پست تایپ i8_child_sites
    add_submenu_page(
        'edit.php?post_type=i8_child_sites', // parent slug
        'گزارشات', // page title
        'گزارشات', // menu title
        'manage_options', // capability
        'i8_reports', // menu slug
        'render_reports_page' // function to display the page
    );
}

// تابع برای نمایش محتوای صفحه گزارشات
function render_reports_page()
{
    echo '<div class="wrap">';
    echo '<div class=""><h1>گزارشات</h1>'; ?>
    <button type="button" id="delete_all_reports" name="delete_all_reports" 
            class="btn btn-rounnded btn-sm btn-outline">
        <span id="i8-loading-bar" class=" loading loading-bars loading-sm hidden"></span>
        حذف همه</button>
        </div>
    <table class="widefat" id="reports-table">
        <thead>
            <tr>
                <th>#</th>
                <th>عنوان پست</th>
                <th>مقصد</th>
                <th>وضعیت</th>
                <th>تاریخ ارسال</th>
                <th>خطا</th>
                
            </tr>
        </thead>
        <tbody>
            <?php
            $all_reports = get_all_reports();
            if ($all_reports) {
                foreach ($all_reports as $report) {
                    $send_post = get_post($report['send_post_id']);
                    $child_site = get_post($report['send_target_child_site_id']);
                    $success_msg = '<div  style="color:green;">موفق</div>';
                    $error_msg = '<div  style="color:red;">ناموفق</div>';
                    $send_status = ($report['send_status'] == 1) ? $success_msg : $error_msg;
                    echo '<tr>';
                    echo '<td>' . $report['id'] . '</td>';
                    echo '<td><a href="' . get_permalink($send_post->ID) . '" target="_blank" >' . $send_post->post_title . '</a></td>';
                    echo '<td><a hef="' . get_permalink($child_site->ID) . '" >' . $child_site->post_title . '</a></td>';
                    echo '<td>' . $send_status . '</td>';
                    echo '<td>' . $report['send_date'] . '</td>';
                    echo '<td>' . $report['send_error_msg'] . '</td>';
                    
                    echo '</tr>';
                }
            }
            ?>
        </tbody>

        <script>
            jQuery(document).ready(function ($) {
                $('#delete_all_reports').click(function (e) {
                    e.preventDefault();
                    $("#i8-loading-bar").removeClass("hidden");
                    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

                    // Send an AJAX request
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            'action': 'i8_hrm_delete_all_reports',
                        },
                        success: function (response) {
                            console.log('delete all recored done!');
                            // delete #reports-table all rows
                            $('#reports-table tbody').empty();
                        },
                        error: function (xhr, status, error) {
                            console.log('delete all recored is failed!');
                        }
                    }).always(function () {
                        $("#i8-loading-bar").addClass("hidden");
                        setTimeout(function () { $(".alert").remove(); }, 7000);
                    });
                });
            });
        </script>
        
        <link rel="stylesheet" href="<?php echo HAM_PLUGIN_URL . '/assets/css/styles.css'; ?>">
        <?php
        echo '</div>';
}

// اضافه کردن منو در admin
add_action('admin_menu', 'add_reports_menu');