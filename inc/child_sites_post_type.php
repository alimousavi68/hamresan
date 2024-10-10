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
    $i8_hrm_url_path = get_post_meta($post_id, 'i8_hrm_url_path', true) ? get_post_meta($post_id, 'i8_hrm_url_path', true) : '';
    $i8_hrm_child_site_username = get_post_meta($post_id, 'i8_hrm_child_site_username', true) ? get_post_meta($post_id, 'i8_hrm_child_site_username', true) : '';
    $i8_hrm_child_site_password = get_post_meta($post_id, 'i8_hrm_child_site_password', true) ? get_post_meta($post_id, 'i8_hrm_child_site_password', true) : '';
    $i8_hrm_publish_delay = get_post_meta($post_id, 'i8_hrm_publish_delay', true) ? get_post_meta($post_id, 'i8_hrm_publish_delay', true) : 5;
    $i8_hrm_post_status = get_post_meta($post_id, 'i8_hrm_post_status', true) ? get_post_meta($post_id, 'i8_hrm_post_status', true) : 'daft';
    $i8_hrm_title_fetch = get_post_meta($post_id, 'i8_hrm_title_fetch', true) ? get_post_meta($post_id, 'i8_hrm_title_fetch', true) : '';
    $i8_hrm_excerpt_fetch = get_post_meta($post_id, 'i8_hrm_excerpt_fetch', true) ? get_post_meta($post_id, 'i8_hrm_excerpt_fetch', true) : '';
    $i8_hrm_body_fetch = get_post_meta($post_id, 'i8_hrm_body_fetch', true) ? get_post_meta($post_id, 'i8_hrm_body_fetch', true) : '';
    $i8_hrm_thumbnail_fetch = get_post_meta($post_id, 'i8_hrm_thumbnail_fetch', true) ? get_post_meta($post_id, 'i8_hrm_thumbnail_fetch', true) : '';
    $i8_hrm_tags_fetch = get_post_meta($post_id, 'i8_hrm_tags_fetch', true) ? get_post_meta($post_id, 'i8_hrm_tags_fetch', true) : '';
    $i8_hrm_taxonomy_fetch = get_post_meta($post_id, 'i8_hrm_taxonomy_fetch', true) ? get_post_meta($post_id, 'i8_hrm_taxonomy_fetch', true) : '';
    $i8_hrm_postmeta_fetch = get_post_meta($post_id, 'i8_hrm_postmeta_fetch', true) ? get_post_meta($post_id, 'i8_hrm_postmeta_fetch', true) : '';
    $i8_hrm_yoast_fetch = get_post_meta($post_id, 'i8_hrm_yoast_fetch', true) ? get_post_meta($post_id, 'i8_hrm_yoast_fetch', true) : '';
    $i8_hrm_rankmath_fetch = get_post_meta($post_id, 'i8_hrm_rankmath_fetch', true) ? get_post_meta($post_id, 'i8_hrm_rankmath_fetch', true) : '';
    ?>


    <div class="flex flex-col gap-3">

        <!-- Child Site Url -->
        <div class="">
            <label for="i8_hrm_url_path" class="block text-gray-700 text-sm font-bold mb-2">آدرس :</label>
            <div class="flex flex-row">
                <input type="text" name="i8_hrm_url_path" value="<?php echo $i8_hrm_url_path; ?>"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button type="button" name="h8_hrm_url_chk_btn" class="btn btn-rounnded btn-sm btn-outline ">تست
                    اتصال</button>
            </div>
        </div>

        <div class="flex md:flex-row gap-2">
            <div class="w-full md:w-1/2  ">
                <label for="i8_hrm_child_site_username">نام کاربری</label>
                <input type="text" name="i8_hrm_child_site_username" value="<?php echo $i8_hrm_child_site_username; ?>"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="w-full md:w-1/2 ">
                <label for="i8_hrm_child_site_password">رمز عبور</label>
                <input type="text" name="i8_hrm_child_site_password" value="<?php echo $i8_hrm_child_site_password; ?>"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
        </div>

        <!-- publish delay -->
        <div class="w-full">
            <label class="block text-gray-700 text-sm font-bold mb-2">تاخیر در انتشار :</label>
            <input type="number" value="<?php echo $i8_hrm_publish_delay; ?>" name="i8_hrm_publish_delay"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
            <span class="label-text-alt tw-text-xs text-gray-400">دقیقه</span>
        </div>

        <!-- post status -->
        <div class="w-full">
            <label class="block text-gray-700 text-sm font-bold mb-2">وضعیت پست :</label>
            <select name="i8_hrm_post_status" id=""
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="draft" <?php echo ($i8_hrm_post_status == 'draft') ? 'selected' : '' ?>>پیش نویس</option>
                <option value="publish" <?php echo ($i8_hrm_post_status == 'publish') ? 'selected' : '' ?>>منتشر شده</option>
                <option value="Pending" <?php echo ($i8_hrm_post_status == 'Pending') ? 'selected' : '' ?>>در انتظار بررسی
                </option>
            </select>

        </div>
        <!-- واکشی -->
        <div class="flex w-full flex-col">

            <div class="divider divider-secondary label-text text-right  text-sm text-slate-800 ">واکشی بشود:</div>
            <!-- fetch items -->
            <div class="div grid grid-cols-3 md:grid-cols-3 gap-4 items-center justify-center w-full">
                <!-- col-1 -->
                <div class="flex flex-col space-y-4">
                    <!-- title is fetch? -->
                    <label class="" for="i8_hrm_title_fetch">
                        <input type="checkbox" name="i8_hrm_title_fetch" <?php echo ($i8_hrm_title_fetch == 'on') ? ' checked="checked" ' : '' ?> />
                        عنوان
                    </label>
                    <!-- excerpt is fetch? -->
                    <label class="" for="i8_hrm_excerpt_fetch">
                        <input type="checkbox" name="i8_hrm_excerpt_fetch" <?php echo ($i8_hrm_excerpt_fetch == 'on') ? ' checked="checked" ' : '' ?> />
                        چکیده مطلب
                    </label>
                    <!-- body is fetch? -->
                    <label class="" for="i8_hrm_body_fetch">
                        <input type="checkbox" name="i8_hrm_body_fetch" <?php echo ($i8_hrm_body_fetch == 'on') ? ' checked="checked" ' : '' ?> />
                        بدنه مطلب
                    </label>
                </div>
                <!-- col-2 -->
                <div class="flex flex-col space-y-4">
                    <!-- thumbnail is fetch? -->
                    <label class="" for="i8_hrm_thumbnail_fetch">
                        <input type="checkbox" name="i8_hrm_thumbnail_fetch" <?php echo ($i8_hrm_thumbnail_fetch == 'on') ? ' checked="checked" ' : '' ?> />
                        تصویر شاخص
                    </label>
                    <!-- tags is fetch? -->
                    <label class="" for="i8_hrm_tags_fetch">
                        <input type="checkbox" name="i8_hrm_tags_fetch" <?php echo ($i8_hrm_tags_fetch == 'on') ? ' checked="checked" ' : '' ?> />
                        برچسب ها
                    </label>
                    <!-- taxonomy is fetch? -->
                    <label class="" for="i8_hrm_taxonomy_fetch">
                        <input type="checkbox" name="i8_hrm_taxonomy_fetch" <?php echo ($i8_hrm_taxonomy_fetch == 'on') ? ' checked="checked" ' : '' ?> />
                        دسته بندی ها
                    </label>
                </div>
                <!-- col-3 -->
                <div class="flex flex-col space-y-4">
                    <!-- post meta is fetch? -->
                    <label class="" for="i8_hrm_postmeta_fetch">
                        <input type="checkbox" name="i8_hrm_postmeta_fetch" <?php echo ($i8_hrm_postmeta_fetch == 'on') ? ' checked="checked" ' : '' ?> />
                        پست متا
                    </label>
                    <!-- yoast is fetch? -->
                    <label class="" for="i8_hrm_yoast_fetch">
                        <input type="checkbox" name="i8_hrm_yoast_fetch" <?php echo ($i8_hrm_yoast_fetch == 'on') ? ' checked="checked" ' : '' ?> />
                        یواست
                    </label>
                    <!-- rankmath is fetch? -->
                    <label class="" for="i8_hrm_rankmath_fetch">
                        <input type="checkbox" name="i8_hrm_rankmath_fetch" <?php echo ($i8_hrm_rankmath_fetch == 'on') ? ' checked="checked" ' : '' ?> />
                        رنک مث
                    </label>
                </div>
            </div>

        </div>

        <!-- دسته ها -->
        <div class="flex w-full flex-col">

            <div class="divider divider-secondary label-text text-right  text-sm text-slate-800 ">دسته ها : </div>

            <!-- fetch items -->
            <div class="flex flex-row sm:flex-sm gap-4 items-center justify-center w-full">

                <div class="flex flex-col w-1/3 sm:w-full">
                    <label>دسته بندی در مقصد</label>
                    <option name="i8_hrm_taxonomy_" value="1" class="input input-bordered " disabled >سیاسی</option>
                </div>

                <img src="<?php echo HAM_PLUGIN_URL . '/assets/images/link.svg' ?>" width="32" height="32" alt="">

                <div class="flex flex-col w-1/3 sm:w-full">
                    <label for="">دسته بندی در مبدا</label>
                    <select name="i8_hrm_primary_taxonomy_source[]" class="select select-bordered">
                        <option value="" class="">سیاست</option>
                    </select>
                </div>

            </div>





        </div>

    </div>

    <link rel="stylesheet" href="<?php echo HAM_PLUGIN_URL . '/assets/css/styles.css'; ?>">


    <?php


}


function save_i8_hrm_child_site_settings_meta_box($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    // Save meta values
    // url_path
    if (isset($_POST['i8_hrm_url_path'])) {
        update_post_meta($post_id, 'i8_hrm_url_path', sanitize_text_field($_POST['i8_hrm_url_path']));
    }

    // username
    if (isset($_POST['i8_hrm_child_site_username'])) {
        update_post_meta($post_id, 'i8_hrm_child_site_username', sanitize_text_field($_POST['i8_hrm_child_site_username']));
    }

    // password 
    if (isset($_POST['i8_hrm_child_site_password'])) {
        update_post_meta($post_id, 'i8_hrm_child_site_password', sanitize_text_field($_POST['i8_hrm_child_site_password']));
    }

    // publish_delay
    if (isset($_POST['i8_hrm_publish_delay'])) {
        update_post_meta($post_id, 'i8_hrm_publish_delay', sanitize_text_field($_POST['i8_hrm_publish_delay']));
    }

    // post_status
    if (isset($_POST['i8_hrm_post_status'])) {
        update_post_meta($post_id, 'i8_hrm_post_status', sanitize_text_field($_POST['i8_hrm_post_status']));
    }

    // title_fetch
    if (isset($_POST['i8_hrm_title_fetch'])) {
        update_post_meta($post_id, 'i8_hrm_title_fetch', sanitize_text_field($_POST['i8_hrm_title_fetch']));
    } else {
        update_post_meta($post_id, 'i8_hrm_title_fetch', 'off');
    }

    // excerpt_fetch
    if (isset($_POST['i8_hrm_excerpt_fetch'])) {
        update_post_meta($post_id, 'i8_hrm_excerpt_fetch', sanitize_text_field($_POST['i8_hrm_excerpt_fetch']));
    } else {
        update_post_meta($post_id, 'i8_hrm_excerpt_fetch', 'off');
    }

    // body_fetch
    if (isset($_POST['i8_hrm_body_fetch'])) {
        update_post_meta($post_id, 'i8_hrm_body_fetch', sanitize_text_field($_POST['i8_hrm_body_fetch']));
    } else {
        update_post_meta($post_id, 'i8_hrm_body_fetch', 'off');
    }

    // thumbnail_fetch
    if (isset($_POST['i8_hrm_thumbnail_fetch'])) {
        update_post_meta($post_id, 'i8_hrm_thumbnail_fetch', sanitize_text_field($_POST['i8_hrm_thumbnail_fetch']));
    } else {
        update_post_meta($post_id, 'i8_hrm_thumbnail_fetch', 'off');
    }

    // tags_fetch
    if (isset($_POST['i8_hrm_tags_fetch'])) {
        update_post_meta($post_id, 'i8_hrm_tags_fetch', sanitize_text_field($_POST['i8_hrm_tags_fetch']));
    } else {
        update_post_meta($post_id, 'i8_hrm_tags_fetch', 'off');
    }

    // taxonomy_fetch
    if (isset($_POST['i8_hrm_taxonomy_fetch'])) {
        update_post_meta($post_id, 'i8_hrm_taxonomy_fetch', sanitize_text_field($_POST['i8_hrm_taxonomy_fetch']));
    } else {
        update_post_meta($post_id, 'i8_hrm_taxonomy_fetch', 'off');
    }

    // postmeta_fetch
    if (isset($_POST['i8_hrm_postmeta_fetch'])) {
        update_post_meta($post_id, 'i8_hrm_postmeta_fetch', sanitize_text_field($_POST['i8_hrm_postmeta_fetch']));
    } else {
        update_post_meta($post_id, 'i8_hrm_postmeta_fetch', 'off');
    }

    // rankmath_fetch
    if (isset($_POST['i8_hrm_rankmath_fetch'])) {
        update_post_meta($post_id, 'i8_hrm_rankmath_fetch', sanitize_text_field($_POST['i8_hrm_rankmath_fetch']));
    } else {
        update_post_meta($post_id, 'i8_hrm_rankmath_fetch', 'off');
    }
}
add_action('save_post', 'save_i8_hrm_child_site_settings_meta_box');

