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
    $i8_hrm_replace_target_1 = get_post_meta($post_id, 'i8_hrm_replace_target_1', true) ? get_post_meta($post_id, 'i8_hrm_replace_target_1', true) : '';
    $i8_hrm_replace_with_1 = get_post_meta($post_id, 'i8_hrm_replace_with_1', true) ? get_post_meta($post_id, 'i8_hrm_replace_with_1', true) : '';
    $i8_hrm_replace_target_2 = get_post_meta($post_id, 'i8_hrm_replace_target_2', true) ? get_post_meta($post_id, 'i8_hrm_replace_target_2', true) : '';
    $i8_hrm_replace_with_2 = get_post_meta($post_id, 'i8_hrm_replace_with_2', true) ? get_post_meta($post_id, 'i8_hrm_replace_with_2', true) : '';
    $i8_hrm_postmeta_fetch = get_post_meta($post_id, 'i8_hrm_postmeta_fetch', true) ? get_post_meta($post_id, 'i8_hrm_postmeta_fetch', true) : '';
    $i8_hrm_yoast_fetch = get_post_meta($post_id, 'i8_hrm_yoast_fetch', true) ? get_post_meta($post_id, 'i8_hrm_yoast_fetch', true) : '';
    $i8_hrm_rankmath_fetch = get_post_meta($post_id, 'i8_hrm_rankmath_fetch', true) ? get_post_meta($post_id, 'i8_hrm_rankmath_fetch', true) : '';

    if ($i8_hrm_url_path != '') {
        $response = i8_hrm_fetch_categories_return($i8_hrm_url_path, $i8_hrm_child_site_username, $i8_hrm_child_site_password);
        if ($response['success'] == true) {
            $child_site_categories = $response["data"];
        } else {
            // error_log($response["message"]);
        }
    }
    ?>


    <div class="flex flex-col gap-3">
        <div id="notif-span">

        </div>

        <!-- Child Site Url -->
        <div class="">
            <label for="i8_hrm_url_path" class="block text-gray-700 text-sm font-bold mb-2">آدرس :</label>
            <div class="flex flex-row">
                <input type="text" name="i8_hrm_url_path" id="i8_hrm_url_path" value="<?php echo $i8_hrm_url_path; ?>"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button type="button" id="i8_hrm_test_btn" name="h8_hrm_url_chk_btn"
                    class="btn btn-rounnded btn-sm btn-outline ">
                    <span id="i8-loading-bar" class=" loading loading-bars loading-sm hidden"></span>
                    تست اتصال</button>
            </div>
        </div>

        <div class="flex md:flex-row gap-2">
            <div class="w-full md:w-1/2  ">
                <label for="i8_hrm_child_site_username">نام کاربری</label>
                <input type="text" name="i8_hrm_child_site_username" id="i8_hrm_child_site_username"
                    value="<?php echo $i8_hrm_child_site_username; ?>"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="w-full md:w-1/2 ">
                <label for="i8_hrm_child_site_password">رمز عبور</label>
                <input type="text" name="i8_hrm_child_site_password" id="i8_hrm_child_site_password"
                    value="<?php echo $i8_hrm_child_site_password; ?>"
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
        <!-- جایگزینی -->
        <div class="flex w-full flex-col">
            <div class="divider divider-secondary label-text text-right  text-sm text-slate-800 ">جایگزینی :</div>
            <div class="div items-center justify-center w-full">

                <!-- items -->
                <div class="flex flex-row sm:flex-sm gap-4 items-center justify-center w-full">

                    <div class="flex flex-col w-1/3 sm:w-full">
                        <label>کلیدواژه هدف :</label>
                        <input type="text" class="input" name="i8_hrm_replace_target_1"
                            value="<?php echo (esc_attr(isset($i8_hrm_replace_target_1) ? $i8_hrm_replace_target_1 : '')); ?>" id="">
                    </div>

                    <img src="<?php echo HAM_PLUGIN_URL . '/assets/images/arrow-left.svg'; ?>" width="32" height="32"
                        alt="">

                    <div class="flex flex-col w-1/3 sm:w-full">
                        <label for="">جایگزینی با :</label>
                        <input type="text" class="input" name="i8_hrm_replace_with_1"
                            value="<?php echo (esc_attr(isset($i8_hrm_replace_with_1) ? $i8_hrm_replace_with_1 : '')); ?>" id="">
                    </div>

                </div>
                <!-- items 2 -->
                <div class="flex flex-row sm:flex-sm gap-4 items-center justify-center w-full">

                    <div class="flex flex-col w-1/3 sm:w-full">
                        <label>کلیدواژه هدف :</label>
                        <input type="text" class="input" name="i8_hrm_replace_target_2"
                            value="<?php echo (esc_attr(isset($i8_hrm_replace_target_2) ? $i8_hrm_replace_target_2 : '')); ?>"
                            id="">
                    </div>

                    <img src="<?php echo HAM_PLUGIN_URL . '/assets/images/arrow-left.svg'; ?>" width="32" height="32"
                        alt="">

                    <div class="flex flex-col w-1/3 sm:w-full">
                        <label for="">جایگزینی با :</label>
                        <input type="text" class="input" name="i8_hrm_replace_with_2"
                            value="<?php echo (esc_attr(isset($i8_hrm_replace_with_2) ? $i8_hrm_replace_with_2 : '')); ?>"
                            id="">
                    </div>

                </div>
            </div>
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

            <div class="divider divider-secondary label-text text-right  text-sm text-slate-800 ">دسته ها :
                <button class="btn btn-sm" name="" id="fetch_categories">
                    <span id="i8-loading-bar-2" class="loading loading-spinner loading-sm hidden"></span>
                    واکشی
                </button>

            </div>
            <div id="notif-span-2">

            </div>
            <div id="categories-frame">
                <?php
                $my_categories = get_categories(array('taxonomy' => 'category', 'hide_empty' => false));
                $categories = get_post_meta($post_id, 'category_relationships', true);
                if ($categories) {
                    foreach ($categories as $index => $category) {
                        ?>
                        <!-- fetch items -->
                        <div class="flex flex-row sm:flex-sm gap-4 items-center justify-center w-full">

                            <div class="flex flex-col w-1/3 sm:w-full">
                                <label>دسته بندی در مقصد</label>
                                <select name="category_relationships[<?php echo $index; ?>][category_child_site]"
                                    class="select select-bordered">
                                    <option value='<?php echo $category['category_child_site']; ?>' class='input input-bordered'
                                        selected><?php
                                        foreach ($child_site_categories as $child_site_category) {
                                            if ($child_site_category["id"] == $category["category_child_site"]) {
                                                echo $child_site_category["name"];
                                            }
                                        }
                                        ?>
                                    </option>
                                </select>
                            </div>

                            <img src="<?php echo HAM_PLUGIN_URL . '/assets/images/link.svg'; ?>" width="32" height="32" alt="">

                            <div class="flex flex-col w-1/3 sm:w-full">
                                <label for="">دسته بندی در مبدا</label>
                                <select name="category_relationships[<?php echo $index; ?>][category_server_site]"
                                    class="select select-bordered">
                                    <option value=""></option>
                                    <?php
                                    foreach ($my_categories as $my_category) {
                                        $selected = ($my_category->term_id == $category['category_server_site']) ? ' selected ' : '';
                                        echo '<option value="' . $my_category->term_id . '" class="input input-bordered"' . $selected . '>' . $my_category->name . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                        </div>
                        <?php
                    }
                }
                ?>
            </div>


        </div>

    </div>

    <!-- test button scripts -->

    <script>
        jQuery(document).ready(function ($) {
            $('#i8_hrm_test_btn').click(function (e) {
                e.preventDefault();
                $("#i8-loading-bar").removeClass("hidden");

                //fetch data
                var i8_hrm_url_path = $('#i8_hrm_url_path').val();
                var i8_hrm_child_site_username = $('#i8_hrm_child_site_username').val();
                var i8_hrm_child_site_password = $('#i8_hrm_child_site_password').val();
                var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

                // Send an AJAX request
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'i8_hrm_test_connection',
                        'i8_hrm_url_path': i8_hrm_url_path,
                        'i8_hrm_child_site_username': i8_hrm_child_site_username,
                        'i8_hrm_child_site_password': i8_hrm_child_site_password,
                    },
                    success: function (response) {
                        // console.log('exp: ' + response);
                        if (response == 'true') {
                            $('#notif-span').append('<div role="alert" class="alert alert-success"> <img src="<?php echo HAM_PLUGIN_URL . '/assets/images/link.svg' ?>" width="32" height="32" alt=""><span>متصل شد!</span></div>');
                        } else {
                            $('#notif-span').append('<div role="alert" class="alert alert-error"><span>اطلاعات وارد شده صحیح نیست</span></div>');
                        }
                    },
                    error: function (xhr, status, error) {
                        if (xhr.status === 404) {
                            $('#notif-span').append('<div role="alert" class="alert alert-error"><span>خطا: URL پیدا نشد.</span></div>');
                        } else if (xhr.status === 500) {
                            $('#notif-span').append('<div role="alert" class="alert alert-error"><span>خطا: مشکل داخلی سرور.</span></div>');
                        } else {
                            $('#notif-span').append('<div role="alert" class="alert alert-error"><span>خطا: ' + xhr.status + ' ' + xhr.statusText + '</span></div>');
                        }
                    }
                }).always(function () {
                    $("#i8-loading-bar").addClass("hidden");
                    setTimeout(function () { $(".alert").remove(); }, 7000);
                });
            });
        });
    </script>

    <script>
        jQuery(document).ready(function ($) {
            $('#fetch_categories').click(function (e) {
                e.preventDefault();
                $("#i8-loading-bar-2").removeClass("hidden");

                var i8_hrm_url_path = $('#i8_hrm_url_path').val();
                var i8_hrm_child_site_username = $('#i8_hrm_child_site_username').val();
                var i8_hrm_child_site_password = $('#i8_hrm_child_site_password').val();
                var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'i8_hrm_fetch_categories',
                        'i8_hrm_url_path': i8_hrm_url_path,
                        'i8_hrm_child_site_username': i8_hrm_child_site_username,
                        'i8_hrm_child_site_password': i8_hrm_child_site_password,
                    },
                    success: function (response) {
                        if (response.success) {
                            var categories = response.data;
                            var myCategories = response.myCategories;

                            // برای هر دسته‌بندی جدید
                            $.each(categories, function (index, category) {
                                // چک کردن وجود دسته‌بندی با این ID
                                var existingSelect = $(`select[name^="category_relationships"][name$="[category_child_site]"] option[value="${category.id}"]`).length;

                                // اگر این دسته‌بندی قبلاً وجود نداشت، اضافه‌اش کن
                                if (!existingSelect) {
                                    var categoryDiv = `
                                                                <div class="flex flex-row sm:flex-sm gap-4 items-center justify-center w-full">
                                                                    <div class="flex flex-col w-1/3 sm:w-full">
                                                                        <label>دسته بندی در مقصد</label>
                                                                        <select name="category_relationships[${index}][category_child_site]" class="select select-bordered">
                                                                            <option value='${category.id}' class='input input-bordered' selected>${category.name}</option>
                                                                        </select>
                                                                    </div>

                                                                    <img src="<?php echo HAM_PLUGIN_URL . '/assets/images/link.svg'; ?>" width="32" height="32" alt="">

                                                                    <div class="flex flex-col w-1/3 sm:w-full">
                                                                        <label for="">دسته بندی در مبدا</label>
                                                                        <select name="category_relationships[${index}][category_server_site]" class="select select-bordered">
                                                                            <option value=""></option>
                                                                            ${myCategories.map(myCategory => `<option value='${myCategory.term_id}' class=''>${myCategory.cat_name}</option>`).join('')}
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            `;
                                    $('#categories-frame').append(categoryDiv);
                                }
                            });
                            $('#notif-span-2').append('<div class="toast toast-end"><div class="alert alert-success"><span>با موفقیت واکشی شد!</span></div></div>');

                        } else {
                            $('#notif-span-2').append('<div class="toast toast-end"><div class="alert alert-danger"><span>مشکلی پیش آمد!</span></div></div>');
                            console.log(response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        if (xhr.status === 404) {
                            $('#notif-span-2').append('<div class="toast toast-end"><div class="alert alert-danger"><span>خطا: URL پیدا نشد.</span></div></div>');
                        } else if (xhr.status === 500) {
                            $('#notif-span-2').append('<div class="toast toast-end"><div class="alert alert-danger"><span>خطا: مشکل داخلی سرور.</span></div></div>');
                        } else {
                            $('#notif-span-2').append('<div class="toast toast-end"><div class="alert alert-danger"><span>خطا: ' + xhr.status + ' ' + xhr.statusText + '</span></div></div>');
                        }
                    }
                }).always(function () {
                    $("#i8-loading-bar-2").addClass("hidden");
                    setTimeout(function () { $(".alert").remove(); }, 7000);
                });
            });
        });
    </script>


    <script>
        // function fetchCategories(apiUrl, jwtToken) {
        //     return new Promise((resolve, reject) => {
        //         $.ajax({
        //             url: `${apiUrl}/wp-json/wp/v2/categories`,
        //             method: 'GET',
        //             headers: {
        //                 'Authorization': `Bearer ${jwtToken}`
        //             },
        //             success: function (data) {
        //                 // تبدیل داده‌ها به آرایه‌ای شامل نام و ای‌دی دسته‌بندی‌ها
        //                 const categories = data.map(category => {
        //                     return {
        //                         id: category.id,
        //                         name: category.name
        //                     };
        //                 });
        //                 resolve(categories);
        //             },
        //             error: function (jqXHR, textStatus, errorThrown) {
        //                 reject(`Error: ${textStatus}, ${errorThrown}`);
        //             }
        //         });
        //     });
        // }

        //     // مثال استفاده
        //     const apiUrl = 'https://dastyar.online'; // آدرس سایت وردپرسی
        //     const jwtToken = 'YOUR_JWT_TOKEN'; // توکن JWT

        //     fetchCategories(apiUrl, jwtToken)
        //         .then(categories => {
        //             console.log(categories);
        //         })
        //         .catch(error => {
        //             console.error(error);
        //         });
    </script>
    <link rel="stylesheet" href="<?php echo HAM_PLUGIN_URL . '/assets/css/styles.css'; ?>">


    <?php


}


function save_i8_hrm_child_site_settings_meta_box($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    // Save meta values
    // check post type is post
    $post = get_post($post_id);
    $post_type = get_post_type($post->ID);
    if ($post_type != 'i8_child_sites') {
        return;
    }

    // url_path
    if (isset($_POST['i8_hrm_url_path'])) {
        update_post_meta($post_id, 'i8_hrm_url_path', sanitize_text_field($_POST['i8_hrm_url_path']));
    }

    // replace target 1
    if (isset($_POST['i8_hrm_replace_target_1'])) {
        update_post_meta($post_id, 'i8_hrm_replace_target_1', $_POST['i8_hrm_replace_target_1']);
    }
    // replace with 1
    if (isset($_POST['i8_hrm_replace_with_1'])) {
        update_post_meta($post_id, 'i8_hrm_replace_with_1', $_POST['i8_hrm_replace_with_1']);
    }

    // replace target 2
    if (isset($_POST['i8_hrm_replace_target_2'])) {
        update_post_meta($post_id, 'i8_hrm_replace_target_2', ($_POST['i8_hrm_replace_target_2']));
    }

    // replace with 2
    if (isset($_POST['i8_hrm_replace_with_2'])) {
        update_post_meta($post_id, 'i8_hrm_replace_with_2', ($_POST['i8_hrm_replace_with_2']));
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

    // realationship category
    $relations = isset($_POST['category_relationships']) ? $_POST['category_relationships'] : [];

    /**
     * OutPut Sample :
     * $category_child_site = [
     * ['category_child_site' => 1, 'category_server_site' => 2],
     * ['category_child_site' => 3, 'category_server_site' => null], // ارتباطی وجود ندارد
     * ];
     */

    // فیلتر کردن و ذخیره‌سازی داده‌ها
    // حذف داده‌های خالی
    $cleaned_relations = array_filter($relations, function ($relation) {
        return !empty($relation['category_child_site']) && !empty($relation['category_server_site']);
    });

    update_post_meta($post_id, 'category_relationships', $cleaned_relations);

}
add_action('save_post', 'save_i8_hrm_child_site_settings_meta_box');


