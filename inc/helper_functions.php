<?php


// create post type i8_child_sites
function create_post_type_i8_child_sites()
{
    // Register Custom Post Type
    register_post_type(
        'i8_child_sites',
        array(
            'labels' => array(
                'name' => 'مقاصد همرسان',
                'singular_name' => 'مقصد همرسان',
                'add_new' => __('افزودن'), // عنوان دکمه افزودن
                'add_new_item' => __('افزودن مقصد جدید'), // عنوان صفحه افزودن  جدید
                'edit_item' => __('ویرایش مقصد'), // عنوان صفحه ویرایش کتاب
                'new_item' => __('مقصد جدید'), // عنوان کتاب جدید
                'view_item' => __('مشاهده مقصد'), // عنوان مشاهده کتاب
                'search_items' => __('جستجوی مقصد'), // عنوان جستجوی کتاب‌ها
                'not_found' => __('مقصدی یافت نشد'), // عنوان زمانی که کتابی یافت نشود
                'not_found_in_trash' => __('مقصدی در سطل زباله یافت نشد'), // عنوان زمانی که کتابی در سطل زباله یافت نشود
            ),
            'public' => true,
            'has_archive' => false,
            'supports' => array('title'), // ویژگی‌هایی که پست تایپ پشتیبانی می‌کند
            'menu_position' => 7, // موقعیت در منو
            'menu_icon' => 'dashicons-sticky', // آیکون منو
            'exclude_from_search' => true, // عدم نمایش در جستجو
            'publicly_queryable' => false, // عدم نمایش در جستجو
            'show_in_rest' => false,
            'capability_type' => 'hamresan_child_sites', // نوع قابلیت برای پست تایپ
            'capabilities' => array(
                'edit_post' => 'edit_child_site',
                'read_post' => 'read_child_site',
                'delete_post' => 'delete_child_sites',
                'edit_posts' => 'edit_child_sites',
                'edit_others_posts' => 'edit_others_child_sites',
                'publish_posts' => 'publish_child_site',
                'read_private_posts' => 'read_private_books',
            ),
            'map_meta_cap' => true, // استفاده از قابلیت‌های سفارشی
        )
    );
}
add_action('init', 'create_post_type_i8_child_sites');


// تابعی برای اضافه کردن قابلیت‌ها به نقش‌های کاربری
function add_custom_capabilities()
{
    // دریافت نقش مدیر کل
    $role = get_role('administrator');

    // اضافه کردن قابلیت‌ها به نقش مدیر کل
    $role->add_cap('edit_child_site');
    $role->add_cap('read_child_site');
    $role->add_cap('delete_child_sites');
    $role->add_cap('edit_child_sites');
    $role->add_cap('edit_others_child_sites');
    $role->add_cap('publish_child_site');
    $role->add_cap('read_private_books');
}
// افزودن اکشن برای اضافه کردن قابلیت‌ها
add_action('admin_init', 'add_custom_capabilities');



// ایجاد متاباکس برای تنظیمات مقصد
add_action('add_meta_boxes', 'i8_hrm_setting_metabox');
function i8_hrm_setting_metabox()
{
    add_meta_box('hrm_setting_metabox', 'تنظیمات مقصد', 'display_hrm_setting_metabox_callback', 'i8_child_sites', 'normal', 'high');
}

function display_hrm_setting_metabox_callback($post)
{
    $post_id = $post->ID;

    ?>
    <link rel="stylesheet" href="<?php echo HAM_PLUGIN_URL . '/assets/css/styles.css'; ?>">

    <div class="form-comtainer">
        <div class="row form-control">
            <label for="i8_hrm_url_path" class="">
                <div class="label">
                    <span class="label-text">آدرس : </span>
                </div>
                <input type="text" name="i8_hrm_url_path" class="input  input-bordered w-full max-w-xs">

            </label>
            <button type="button" name="h8_hrm_url_chk_btn" class="btn btn-primary btn-rounnded btn-sm	btn-outline ">تست
                اتصال</button>

        </div>
        <div class="row form-control">
            <label class="form-control w-full max-w-xs">
                <div class="label">
                    <span class="label-text">تاخیر</span>
                </div>
                <input type="number" placeholder="زمان تاخیر انتشار" class="input  input-bordered w-full max-w-xs" />
                <div class="label">
                    <span class="label-text-alt text-xs text-gray-200">دقیقه</span>
                </div>
            </label>
        </div>
        <div class="row">

            <select name="i8_hrm_post_status" id="" class="select select-bordered w-full max-w-s ">
                <option value="draft" selected>پیش نویس</option>
                <option value="published">منتشر شده</option>
                <option value="formore">در انتظار بررسی</option>
            </select>
        </div>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <!-- لینک به DaisyUI -->
        <link href="https://cdn.jsdelivr.net/npm/daisyui@1.14.0/dist/full.css" rel="stylesheet">
        
        <div class="row flex w-full flex-col">
            <div class=" divider  divider-secondary label-text text-right  text-base text-slate-600 ">واکشی بشود:</div>

            <!-- title is fetch? -->
            <div class="form-control">
                <label class="label cursor-pointer">
                    <span class="label-text">عنوان</span>
                    <input type="checkbox" class="toggle" checked="checked" name="i8_hrm_title_fetch" />
                </label>
            </div>

            <div class="form-control">
                <label class="cursor-pointer label">
                    <span class="label-text">Toggle حالت</span>
                    <input type="checkbox" class="toggle toggle-primary" />
                </label>
            </div>

        </div>
    </div>




    <?php


}


// function save_plans_custom_meta_box($post_id)
// {
//     if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
//         return;

//     // Save meta values
//     if (isset($_POST['plan_duration'])) {
//         update_post_meta($post_id, 'plan_duration', sanitize_text_field($_POST['plan_duration']));
//     }
//     if (isset($_POST['plan_cron_interval'])) {
//         update_post_meta($post_id, 'plan_cron_interval', sanitize_text_field($_POST['plan_cron_interval']));
//     }
//     if (isset($_POST['plan_max_post_fetch'])) {
//         update_post_meta($post_id, 'plan_max_post_fetch', sanitize_text_field($_POST['plan_max_post_fetch']));
//     }

// }
// add_action('save_post', 'save_plans_custom_meta_box');