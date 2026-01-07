<?php
// allow this function to connect with wordpress ajax
add_action('wp_ajax_i8_hrm_test_connection', 'i8_hrm_test_connection');
add_action('wp_ajax_nopriv_i8_hrm_test_connection', 'i8_hrm_test_connection'); // اگر به کاربران غیرمجاز هم نیاز دارید

add_action('wp_ajax_i8_hrm_fetch_categories', 'i8_hrm_fetch_categories');
add_action('wp_ajax_nopriv_i8_hrm_fetch_categories', 'i8_hrm_fetch_categories');

add_action('wp_ajax_i8_hrm_delete_all_reports', 'i8_hrm_delete_all_reports');
add_action('wp_ajax_nopriv_i8_hrm_delete_all_reports', 'i8_hrm_delete_all_reports');

add_action('wp_ajax_i8_hrm_get_all_reports', 'i8_hrm_get_all_reports');
add_action('wp_ajax_nopriv_i8_hrm_get_all_reports', 'i8_hrm_get_all_reports');
add_action('wp_ajax_i8_hrm_fetch_post_types', 'i8_hrm_fetch_post_types');
add_action('wp_ajax_nopriv_i8_hrm_fetch_post_types', 'i8_hrm_fetch_post_types');

add_action('save_post', 'send_new_post_to_child_sites', 100);


// Send Post Post Publishe Action Function
function send_new_post_to_child_sites($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    // بررسی نوع و وضعیت پست
    $post = get_post($post_id);
    $post_type = get_post_type($post->ID);
    if ($post_type != 'post' || get_post_status($post->ID) != 'publish') {
        return;
    }

    //عدم ارسال
    if (isset($_POST['i8_hrm_manual_setting']) && $_POST['i8_hrm_manual_setting'] == 'off') {
        return;
    }

    // ارسال در حالت دستی
    if (isset($_POST['i8_hrm_manual_setting']) && $_POST['i8_hrm_manual_setting'] == 'manual') {
        $i8_hrm_body_fetch = $_POST['i8_hrm_child_site_for_send'];
        $child_sites = get_posts(
            array(
                'post_type' => 'i8_child_sites',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'include' => $i8_hrm_body_fetch,
            )
        );
        if (!is_array($child_sites)) {
            array($child_sites);
        }
    } else {
        // دریافت سایت‌های فرزند
        $child_sites = i8_hrm_fetch_child_sites();
    }

    // ارسال پست به هر سایت فرزند
    foreach ($child_sites as $child_site) {
        $is_break = false;


        $i8_hrm_forbbiden_cats = get_post_meta($child_site->ID, 'i8_hrm_forbbiden_cats', true);

        if (isset($i8_hrm_forbbiden_cats) && !empty($i8_hrm_forbbiden_cats)) {
            $post_categories = wp_get_post_terms($post_id, 'category');
            foreach ($post_categories as $category) {
                if (in_array($category->term_id, $i8_hrm_forbbiden_cats)) {
                    $is_break = true;
                }
            }
        }

        if (isset($is_break) && $is_break == true) {
            insert_into_hrm_reports(date('Y-m-d H:i:s'), $post_id, $child_site->ID, 0, 'دسته بندی این پست در لیست سایت فرزند مورد نظر محدود شده است');
            continue;
        }

        $child_site_id = $child_site->ID;
        if (i8_child_site_is_limit_post_in_day($child_site_id) == false) {
            insert_into_hrm_reports(date('Y-m-d H:i:s'), $post_id, $child_site->ID, 0, 'حداکثر تعداد پست های ارسال شده در یک روز برای این سایت فرزند سررسیده است');
            continue;
        }

        $child_site_meta = i8_hrm_fetch_child_sites_meta($child_site_id);
        $url = $child_site_meta['i8_hrm_url_path'];
        $username = $child_site_meta['i8_hrm_child_site_username'];
        $password = $child_site_meta['i8_hrm_child_site_password'];
        $jwt_token = get_jwt_token($url, $username, $password);

        // ارسال درخواست برای ایجاد پست
        $post_info = i8_hrm_fetch_post_info($post, $child_site_meta, $jwt_token, $url);
        // error_log('post_info: ' . print_r($post_info,true));
        $rest_base = isset($child_site_meta['i8_hrm_destination_post_type_base']) && $child_site_meta['i8_hrm_destination_post_type_base'] ? $child_site_meta['i8_hrm_destination_post_type_base'] : 'posts';
        $response = send_rest_post_insert_request($url, $username, $password, $post_info, $rest_base);

        $response_id = json_decode(wp_remote_retrieve_body($response));
        if ($child_site_meta['i8_hrm_postmeta_fetch'] == 'on') {
            $post_meta = get_post_meta($post->ID);
            $meta_data = array();
            foreach ($post_meta as $key => $values) {
                $meta_data[$key] = $values[0];
            }
            $post_info['meta_input'] = $meta_data;
        }

        // بررسی موفقیت‌آمیز بودن درخواست
        if ($response && isset($response_id->id)) {
            insert_into_hrm_reports(date('Y-m-d H:i:s'), $post_id, $child_site->ID, 1, '');
            // اضافه کردن تمام متادیتاهای پست
            if ($child_site_meta['i8_hrm_postmeta_fetch'] == 'on') {
                $meta_response = send_rest_post_meta_request($url, $response_id->id, $post_id, $jwt_token, $rest_base, $child_site->ID);
            }
            if (isset($child_site_meta['i8_hrm_source_meta_enabled']) && $child_site_meta['i8_hrm_source_meta_enabled'] === 'on') {
                $name_key = isset($child_site_meta['i8_hrm_source_name_meta_key']) ? $child_site_meta['i8_hrm_source_name_meta_key'] : '';
                $link_key = isset($child_site_meta['i8_hrm_source_link_meta_key']) ? $child_site_meta['i8_hrm_source_link_meta_key'] : '';
                $name_override = isset($child_site_meta['i8_hrm_source_name_override']) ? $child_site_meta['i8_hrm_source_name_override'] : '';
                if ($name_key || $link_key) {
                    send_rest_source_meta_request($url, $response_id->id, $post_id, $jwt_token, $name_key, $link_key, $name_override, $rest_base, $child_site->ID);
                }
            }
            $media_id_payload = isset($post_info['featured_media']) ? $post_info['featured_media'] : null;
            if ($media_id_payload) {
                attach_media_to_post($url, $jwt_token, $response_id->id, $media_id_payload, $rest_base, $post_id, $child_site->ID);
            }
        }
    }
}



// fetch all child sites
function i8_hrm_fetch_child_sites()
{
    // get all child sites
    $child_sites = get_posts(array(
        'post_type' => 'i8_child_sites',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));

    return $child_sites;
}

//fetch hamresan meta data for a child site 
function i8_hrm_fetch_child_sites_meta($child_site_id)
{
    $i8_hrm_url_path = get_post_meta($child_site_id, 'i8_hrm_url_path', true) ? get_post_meta($child_site_id, 'i8_hrm_url_path', true) : '';
    $i8_hrm_child_site_username = get_post_meta($child_site_id, 'i8_hrm_child_site_username', true) ? get_post_meta($child_site_id, 'i8_hrm_child_site_username', true) : '';
    $i8_hrm_child_site_password = get_post_meta($child_site_id, 'i8_hrm_child_site_password', true) ? get_post_meta($child_site_id, 'i8_hrm_child_site_password', true) : '';
    $i8_hrm_publish_delay = get_post_meta($child_site_id, 'i8_hrm_publish_delay', true) ? get_post_meta($child_site_id, 'i8_hrm_publish_delay', true) : 5;
    $i8_hrm_post_status = get_post_meta($child_site_id, 'i8_hrm_post_status', true) ? get_post_meta($child_site_id, 'i8_hrm_post_status', true) : 'daft';
    $i8_hrm_title_fetch = get_post_meta($child_site_id, 'i8_hrm_title_fetch', true) ? get_post_meta($child_site_id, 'i8_hrm_title_fetch', true) : '';
    $i8_hrm_excerpt_fetch = get_post_meta($child_site_id, 'i8_hrm_excerpt_fetch', true) ? get_post_meta($child_site_id, 'i8_hrm_excerpt_fetch', true) : '';
    $i8_hrm_body_fetch = get_post_meta($child_site_id, 'i8_hrm_body_fetch', true) ? get_post_meta($child_site_id, 'i8_hrm_body_fetch', true) : '';
    $i8_hrm_thumbnail_fetch = get_post_meta($child_site_id, 'i8_hrm_thumbnail_fetch', true) ? get_post_meta($child_site_id, 'i8_hrm_thumbnail_fetch', true) : '';
    $i8_hrm_tags_fetch = get_post_meta($child_site_id, 'i8_hrm_tags_fetch', true) ? get_post_meta($child_site_id, 'i8_hrm_tags_fetch', true) : '';
    $i8_hrm_taxonomy_fetch = get_post_meta($child_site_id, 'i8_hrm_taxonomy_fetch', true) ? get_post_meta($child_site_id, 'i8_hrm_taxonomy_fetch', true) : '';
    $i8_hrm_postmeta_fetch = get_post_meta($child_site_id, 'i8_hrm_postmeta_fetch', true) ? get_post_meta($child_site_id, 'i8_hrm_postmeta_fetch', true) : '';
    $i8_hrm_yoast_fetch = get_post_meta($child_site_id, 'i8_hrm_yoast_fetch', true) ? get_post_meta($child_site_id, 'i8_hrm_yoast_fetch', true) : '';
    $i8_hrm_rankmath_fetch = get_post_meta($child_site_id, 'i8_hrm_rankmath_fetch', true) ? get_post_meta($child_site_id, 'i8_hrm_rankmath_fetch', true) : '';
    $i8_hrm_replace_target_1 = get_post_meta($child_site_id, 'i8_hrm_replace_target_1', true) ? get_post_meta($child_site_id, 'i8_hrm_replace_target_1', true) : '';
    $i8_hrm_replace_with_1 = get_post_meta($child_site_id, 'i8_hrm_replace_with_1', true) ? get_post_meta($child_site_id, 'i8_hrm_replace_with_1', true) : '';
    $i8_hrm_replace_target_2 = get_post_meta($child_site_id, 'i8_hrm_replace_target_2', true) ? get_post_meta($child_site_id, 'i8_hrm_replace_target_2', true) : '';
    $i8_hrm_replace_with_2 = get_post_meta($child_site_id, 'i8_hrm_replace_with_2', true) ? get_post_meta($child_site_id, 'i8_hrm_replace_with_2', true) : '';
    $category_relationships = get_post_meta($child_site_id, 'category_relationships', true) ? get_post_meta($child_site_id, 'category_relationships', true) : '';

    $i8_hrm_source_meta_enabled = get_post_meta($child_site_id, 'i8_hrm_source_meta_enabled', true) ? get_post_meta($child_site_id, 'i8_hrm_source_meta_enabled', true) : 'off';
    $i8_hrm_source_name_meta_key = get_post_meta($child_site_id, 'i8_hrm_source_name_meta_key', true) ? get_post_meta($child_site_id, 'i8_hrm_source_name_meta_key', true) : '';
    $i8_hrm_source_link_meta_key = get_post_meta($child_site_id, 'i8_hrm_source_link_meta_key', true) ? get_post_meta($child_site_id, 'i8_hrm_source_link_meta_key', true) : '';
    $i8_hrm_source_name_override = get_post_meta($child_site_id, 'i8_hrm_source_name_override', true) ? get_post_meta($child_site_id, 'i8_hrm_source_name_override', true) : '';
    $i8_hrm_destination_post_type_base = get_post_meta($child_site_id, 'i8_hrm_destination_post_type_base', true) ? get_post_meta($child_site_id, 'i8_hrm_destination_post_type_base', true) : 'posts';

    $i8_hrm_child_site_meta = array(
        'i8_hrm_url_path' => $i8_hrm_url_path,
        'i8_hrm_child_site_username' => $i8_hrm_child_site_username,
        'i8_hrm_child_site_password' => $i8_hrm_child_site_password,
        'i8_hrm_publish_delay' => $i8_hrm_publish_delay,
        'i8_hrm_post_status' => $i8_hrm_post_status,
        'i8_hrm_title_fetch' => $i8_hrm_title_fetch,
        'i8_hrm_excerpt_fetch' => $i8_hrm_excerpt_fetch,
        'i8_hrm_body_fetch' => $i8_hrm_body_fetch,
        'i8_hrm_thumbnail_fetch' => $i8_hrm_thumbnail_fetch,
        'i8_hrm_tags_fetch' => $i8_hrm_tags_fetch,
        'i8_hrm_taxonomy_fetch' => $i8_hrm_taxonomy_fetch,
        'i8_hrm_postmeta_fetch' => $i8_hrm_postmeta_fetch,
        'i8_hrm_yoast_fetch' => $i8_hrm_yoast_fetch,
        'i8_hrm_rankmath_fetch' => $i8_hrm_rankmath_fetch,
        'i8_hrm_replace_target_1' => $i8_hrm_replace_target_1,
        'i8_hrm_replace_with_1' => $i8_hrm_replace_with_1,
        'i8_hrm_replace_target_2' => $i8_hrm_replace_target_2,
        'i8_hrm_replace_with_2' => $i8_hrm_replace_with_2,
        'category_relationships' => $category_relationships,
        'i8_hrm_source_meta_enabled' => $i8_hrm_source_meta_enabled,
        'i8_hrm_source_name_meta_key' => $i8_hrm_source_name_meta_key,
        'i8_hrm_source_link_meta_key' => $i8_hrm_source_link_meta_key,
        'i8_hrm_source_name_override' => $i8_hrm_source_name_override,
        'i8_hrm_destination_post_type_base' => $i8_hrm_destination_post_type_base
    );
    return $i8_hrm_child_site_meta;
}


// prepare and send post to child site
function i8_hrm_fetch_post_info($post, $child_site_meta, $token, $url)
{
    $post_info = array();

    if ($child_site_meta['i8_hrm_title_fetch'] == 'on') {
        $post_info['title'] = $post->post_title;
    }
    if ($child_site_meta['i8_hrm_excerpt_fetch'] == 'on') {
        $post_info['excerpt'] = $post->post_excerpt;
    }
    if ($child_site_meta['i8_hrm_body_fetch'] == 'on') {
        if ($child_site_meta['i8_hrm_replace_target_1']) {
            $target_1 = $child_site_meta['i8_hrm_replace_target_1'];
            $replace_with_1 = $child_site_meta['i8_hrm_replace_with_1'];
            $new_replaced_content = i8_hrm_replace_post_content($post->post_content, $target_1, $replace_with_1);
        }
        if ($child_site_meta['i8_hrm_replace_target_2']) {
            $target_2 = $child_site_meta['i8_hrm_replace_target_2'];
            $replace_with_2 = $child_site_meta['i8_hrm_replace_with_2'];
            $new_replaced_content = i8_hrm_replace_post_content($new_replaced_content, $target_2, $replace_with_2);
        }
        if (!isset($child_site_meta['i8_hrm_replace_target_1']) && !isset($child_site_meta['i8_hrm_replace_target_2'])) {
            $post_info['content'] = $post->post_content;
        } else {
            $new_replaced_content = i8_hrm_remove_link_content($new_replaced_content);
            $post_info['content'] = $new_replaced_content;
        }
    }
    if ($child_site_meta['i8_hrm_post_status']) {
        $post_info['status'] = $child_site_meta['i8_hrm_post_status'];
    }
    if ($child_site_meta['i8_hrm_taxonomy_fetch'] == 'on') {
        //write a function to get all categories and return an array of categories
        $categories = wp_get_post_categories($post->ID);
        $category_relationships = $child_site_meta['category_relationships'];
        $category_list = i8_hrm_convert_category($categories, $category_relationships);
        $post_info['categories'] = $category_list;
        error_log('cat: '. print_r($post_info['categories']));
    }

    if ($child_site_meta['i8_hrm_thumbnail_fetch'] == 'on') {
        // get post featured image url
        if (has_post_thumbnail($post->ID)) {
            $post_media_url = get_the_post_thumbnail_url($post->ID);
            $media_id = upload_media($token, $post_media_url, $url);
            if ($media_id !== null) {
                $post_info['featured_media'] = $media_id;
            }
        }
    }

    if ($child_site_meta['i8_hrm_tags_fetch'] == 'on') {
        $tags_list = wp_get_post_tags($post->ID);
        $new_tag_list = manage_tags($token, $tags_list, $url);
        if ($new_tag_list) {
            $post_info['tags'] = $new_tag_list;
        }
    }


    if ($child_site_meta['i8_hrm_publish_delay']) {
        // فرض کنید $post و $child_site_meta به درستی تعریف شده‌اند
        $publish_delay = isset($child_site_meta['i8_hrm_publish_delay']) ? $child_site_meta['i8_hrm_publish_delay'] : 0;
        // زمان پست را به عنوان شی DateTime ایجاد کنید
        $post_date = new DateTime($post->post_date, new DateTimeZone('Asia/Tehran'));
        // زمان تأخیر را به ثانیه اضافه کنید
        $post_date->modify("+{$publish_delay} minutes");
        // تبدیل به UTC
        $post_date->setTimezone(new DateTimeZone('UTC'));
        // ذخیره تاریخ نهایی در فرمت ISO 8601
        $post_info['date'] = $post_date->format('c'); // فرمت ISO 8601
    }


    // error_log(print_r($post_info, true));
    return $post_info;

}

// set activity log

function send_rest_post_insert_request($url, $username, $password, $post_info, $rest_base = 'posts')
{
    // get and generate jwt token in child site
    $jwt_token = get_jwt_token($url, $username, $password); // این تابع باید توکن JWT را برگرداند

    // URL REST API سایت فرزند
    $url = $url . '/wp-json/wp/v2/' . $rest_base;

    // ارسال درخواست به سایت فرزند با JWT
    $response = wp_remote_post($url, array(
        'method' => 'POST',
        'body' => json_encode($post_info),
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $jwt_token
        )
    ));

    // بررسی ارسال موفقیت آمیز بودن درخواست
    if (is_wp_error($response)) {
        // در صورت خطا بررسی کنید
        error_log('خطا در ارسال درخواست: ' . $response->get_error_message());
        
    } else {
        // error_log('success send for: ' . $url);
        // error_log('succes response: ' . print_r($response,true));
        
        return $response;
    }
}

// send post to child sites
function i8_hrm_send_post_to_child_sites($url, $post_info, $jwt_token)
{
    // ارسال درخواست به سایت فرزند با JWT
    $response = wp_remote_post($url, array(
        'method' => 'POST',
        'body' => json_encode($post_info),
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $jwt_token
        )
    ));
}

// تابعی برای گرفتن توکن JWT
function get_jwt_token($url, $username, $password)
{
    if ($url and $username and $password) {
        $token = get_option('i8_token_' . $url);
        if ($token == '' || !isset($token)) {
            $url = $url . '/wp-json/jwt-auth/v1/token';
            $response = wp_remote_post($url, array(
                'method' => 'POST',
                'body' => array(
                    'username' => $username,
                    'password' => $password
                )
            ));
            $result_response = json_decode(wp_remote_retrieve_body($response));
        }

        if (isset($result_response->token)) {
            update_option('i8_token_' . $url, $result_response->token);
            return $result_response->token;
        } else {
            return $token;
        }
    }
}

function manage_tags($jwt_token, $tags_list, $wp_api_url)
{
    $tag_ids = array();

    foreach ($tags_list as $tag) {
        // Check if the tag exists
        $response = wp_remote_get($wp_api_url . '/wp-json/wp/v2/tags?search=' . urlencode($tag->name), array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $jwt_token
            )
        ));

        if (is_wp_error($response)) {
            error_log('Error checking tag: ' . $tag->name);
            continue;
        }

        $existing_tags = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($existing_tags)) {
            // Tag exists, get its ID
            $tag_ids[] = $existing_tags[0]['id'];
        } else {
            // Tag does not exist, create it
            $create_response = wp_remote_post($wp_api_url . '/wp-json/wp/v2/tags', array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $jwt_token,
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode(array(
                    'name' => $tag->name
                ))
            ));

            if (!is_wp_error($create_response)) {
                $created_tag = json_decode(wp_remote_retrieve_body($create_response), true);
                $tag_ids[] = $created_tag['id'];
            } else {
                error_log('Error creating tag: ' . $tag->name);
            }
        }
    }

    return $tag_ids;
}

function upload_media($jwt_token, $image_file_path, $wp_api_url)
{
    $image_data = file_get_contents($image_file_path);
    if ($image_data === false || !is_string($image_data)) {
        error_log('Failed to get image contents from: ' . $image_file_path);
        return null;
    }
    $image_filename = basename($image_file_path);
    $ext = strtolower(pathinfo($image_filename, PATHINFO_EXTENSION));
    $mime = 'application/octet-stream';
    if ($ext === 'jpg' || $ext === 'jpeg') {
        $mime = 'image/jpeg';
    } elseif ($ext === 'png') {
        $mime = 'image/png';
    } elseif ($ext === 'gif') {
        $mime = 'image/gif';
    } elseif ($ext === 'webp') {
        $mime = 'image/webp';
    } elseif ($ext === 'svg') {
        $mime = 'image/svg+xml';
    }

    $response = wp_remote_post($wp_api_url . '/wp-json/wp/v2/media', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $jwt_token,
            'Content-Disposition' => 'attachment; filename=' . $image_filename,
            'Content-Type' => $mime
        ),
        'body' => $image_data
    ));

    if (is_wp_error($response)) {
        error_log('Error uploading media: ' . $response->get_error_message());
        return null;
    }

    $code = wp_remote_retrieve_response_code($response);
    if (!in_array($code, array(200, 201))) {
        error_log('Unexpected response on media upload: ' . $code . ' - ' . wp_remote_retrieve_body($response));
        return null;
    }

    $media = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($media['id'])) {
        return $media['id'];
    }

    return null;
}

function attach_media_to_post($url, $jwt_token, $post_id, $media_id, $rest_base = 'posts', $server_post_id = null, $child_site_id = null)
{
    if (!$media_id || !$post_id) {
        return false;
    }
    $media_url = "{$url}/wp-json/wp/v2/media/{$media_id}";
    $response1 = wp_remote_post($media_url, array(
        'method' => 'POST',
        'headers' => array(
            'Authorization' => 'Bearer ' . $jwt_token,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array('post' => $post_id)),
    ));
    if (is_wp_error($response1)) {
        if ($server_post_id && $child_site_id) {
            insert_into_hrm_reports(date('Y-m-d H:i:s'), $server_post_id, $child_site_id, 0, 'پیوست تصویر ناموفق: ' . $response1->get_error_message());
        }
        return false;
    }
    $code1 = wp_remote_retrieve_response_code($response1);
    if (!in_array($code1, array(200, 201))) {
        if ($server_post_id && $child_site_id) {
            insert_into_hrm_reports(date('Y-m-d H:i:s'), $server_post_id, $child_site_id, 0, 'پیوست تصویر ناموفق: کد ' . $code1 . ' - ' . wp_remote_retrieve_body($response1));
        }
        return false;
    }
    $post_url = "{$url}/wp-json/wp/v2/{$rest_base}/{$post_id}";
    $response2 = wp_remote_post($post_url, array(
        'method' => 'POST',
        'headers' => array(
            'Authorization' => 'Bearer ' . $jwt_token,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array('featured_media' => $media_id)),
    ));
    if (is_wp_error($response2)) {
        if ($server_post_id && $child_site_id) {
            insert_into_hrm_reports(date('Y-m-d H:i:s'), $server_post_id, $child_site_id, 0, 'ثبت تصویر شاخص ناموفق: ' . $response2->get_error_message());
        }
        return false;
    }
    $code2 = wp_remote_retrieve_response_code($response2);
    if (!in_array($code2, array(200, 201))) {
        if ($server_post_id && $child_site_id) {
            insert_into_hrm_reports(date('Y-m-d H:i:s'), $server_post_id, $child_site_id, 0, 'ثبت تصویر شاخص ناموفق: کد ' . $code2 . ' - ' . wp_remote_retrieve_body($response2));
        }
        return false;
    }
    $verify = wp_remote_get($post_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $jwt_token
        )
    ));
    if (is_wp_error($verify)) {
        if ($server_post_id && $child_site_id) {
            insert_into_hrm_reports(date('Y-m-d H:i:s'), $server_post_id, $child_site_id, 0, 'بررسی تصویر شاخص ناموفق: ' . $verify->get_error_message());
        }
        return false;
    }
    $post_obj = json_decode(wp_remote_retrieve_body($verify), true);
    if (!isset($post_obj['featured_media']) || intval($post_obj['featured_media']) !== intval($media_id)) {
        if ($server_post_id && $child_site_id) {
            insert_into_hrm_reports(date('Y-m-d H:i:s'), $server_post_id, $child_site_id, 0, 'پس از بروزرسانی، تصویر شاخص تنظیم نشد');
        }
        return false;
    }
    return true;
}

function i8_hrm_convert_category($categories, $category_relationships)
{
    $cat_list = array();
    foreach ($categories as $cat_item) {
        foreach ($category_relationships as $rel_item) {
            if ($rel_item["category_server_site"] == $cat_item) {
                $cat_list[] = $rel_item["category_child_site"];
            }
        }

    }
    return $cat_list;
}

function i8_hrm_test_connection()
{
    $url = $_POST['i8_hrm_url_path'];
    $username = $_POST['i8_hrm_child_site_username'];
    $password = $_POST['i8_hrm_child_site_password'];
    if ($url and $username and $password) {
        $url = $url . '/wp-json/jwt-auth/v1/token';
        $response = wp_remote_post($url, array(
            'method' => 'POST',
            'body' => array(
                'username' => $username,
                'password' => $password
            )
        ));
    }

    $result_response = json_decode(wp_remote_retrieve_body($response));
    if (isset($result_response->token)) {
        $token = $result_response->token;
    }

    if ($response['response']['code'] == 200) {
        echo json_encode(true);
    } else {
        echo json_encode(false); // استفاده از false به عنوان یک مقدار بولی
    }
    wp_die(); // پایان پردازش

}

//fetch child site categories
function i8_hrm_fetch_categories($url = '', $username = '', $password = '')
{
    $url = (isset($_POST['i8_hrm_url_path'])) ? $_POST['i8_hrm_url_path'] : $url;
    $username = (isset($_POST['i8_hrm_child_site_username']) ? $_POST['i8_hrm_child_site_username'] : $username);
    $password = (isset($_POST['i8_hrm_child_site_password']) ? $_POST['i8_hrm_child_site_password'] : $password);

    $token = get_jwt_token($url, $username, $password);
    $url = $url . '/wp-json/wp/v2/categories?per_page=100&hide_empty=false';

    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'content-type' => 'application/json'
        ),
        'timeout' => 30 // Increase timeout to 30 seconds
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        $response = array(
            'success' => false,
            'message' => 'خطا در ارسال درخواست' . $response->get_error_message()
        );
        wp_send_json($response); // equal return
    }


    $status_code = wp_remote_retrieve_response_message($response);
    if ($status_code !== "OK") {
        $response = array(
            'success' => false,
            'message' => 'خطا در دریافت اطلاعات: ' . wp_remote_retrieve_body($response)
        );
        wp_send_json($response); // equal return
    }


    $my_categories = get_categories(array('taxonomy' => 'category', 'hide_empty' => false));
    $categories = json_decode(wp_remote_retrieve_body($response), true);
    $response = array(
        'success' => true,
        'data' => $categories,
        'myCategories' => $my_categories
    );

    wp_send_json($response); // equal return
}

function i8_hrm_fetch_post_types($url = '', $username = '', $password = '')
{
    $url = (isset($_POST['i8_hrm_url_path'])) ? $_POST['i8_hrm_url_path'] : $url;
    $username = (isset($_POST['i8_hrm_child_site_username']) ? $_POST['i8_hrm_child_site_username'] : $username);
    $password = (isset($_POST['i8_hrm_child_site_password']) ? $_POST['i8_hrm_child_site_password'] : $password);
    $token = get_jwt_token($url, $username, $password);
    $endpoint = $url . '/wp-json/wp/v2/types';
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'content-type' => 'application/json'
        ),
        'timeout' => 30
    );
    $response = wp_remote_get($endpoint, $args);
    if (is_wp_error($response)) {
        wp_send_json(array('success' => false, 'message' => $response->get_error_message()));
    }
    $status_code = wp_remote_retrieve_response_message($response);
    if ($status_code !== "OK") {
        wp_send_json(array('success' => false, 'message' => wp_remote_retrieve_body($response)));
    }
    $types = json_decode(wp_remote_retrieve_body($response), true);
    wp_send_json(array('success' => true, 'data' => $types));
}
function i8_hrm_fetch_categories_return($url = '', $username = '', $password = '')
{
    $url = (isset($_POST['i8_hrm_url_path'])) ? $_POST['i8_hrm_url_path'] : $url;
    $username = (isset($_POST['i8_hrm_child_site_username']) ? $_POST['i8_hrm_child_site_username'] : $username);
    $password = (isset($_POST['i8_hrm_child_site_password']) ? $_POST['i8_hrm_child_site_password'] : $password);

    $token = get_jwt_token($url, $username, $password);
    $url = $url . '/wp-json/wp/v2/categories?per_page=100&hide_empty=false';

    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'content-type' => 'application/json'
        )
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        $response = array(
            'success' => false,
            'message' => 'خطا در ارسال درخواست' . $response->get_error_message()
        );
        return $response;
    }


    $status_code = wp_remote_retrieve_response_message($response);
    if ($status_code !== "OK") {
        $response = array(
            'success' => false,
            'message' => 'خطا در دریافت اطلاعات: ' . wp_remote_retrieve_body($response)
        );
        return $response;
    }


    $my_categories = get_categories(array('taxonomy' => 'category', 'hide_empty' => false));
    $categories = json_decode(wp_remote_retrieve_body($response), true);
    $response = array(
        'success' => true,
        'data' => $categories,
        'myCategories' => $my_categories
    );

    return $response;
}
function send_rest_post_meta_request($url, $post_id, $server_post_id, $jwt_token, $rest_base = 'posts', $child_site_id = null)
{
    $meta_data = get_post_meta($server_post_id);
    $filtered_meta_data = array();

    foreach ($meta_data as $key => $value) {
        if (!empty($value[0])) {
            $filtered_meta_data[] = [
                'key' => $key,
                'value' => $value[0] // فقط مقدار اول را ذخیره کنید
            ];
        }
    }

    if (!empty($filtered_meta_data)) {
        $post_url = "{$url}/wp-json/wp/v2/{$rest_base}/{$post_id}";

        foreach ($filtered_meta_data as $post_meta) {
            $response = wp_remote_post($post_url, [
                'method' => 'POST',
                'headers' => [
                    'Authorization' => 'Bearer ' . $jwt_token,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode(array('meta' => array($post_meta['key'] => $post_meta['value']))),
            ]);

            if (is_wp_error($response)) {
                if ($child_site_id) {
                    insert_into_hrm_reports(date('Y-m-d H:i:s'), $server_post_id, $child_site_id, 0, 'ارسال متادیتا ناموفق: ' . $response->get_error_message());
                }
                return 'Error: ' . $response->get_error_message();
            }

            $response_code = wp_remote_retrieve_response_code($response);
            if (!in_array($response_code, array(200, 201))) {
                if ($child_site_id) {
                    insert_into_hrm_reports(date('Y-m-d H:i:s'), $server_post_id, $child_site_id, 0, 'ارسال متادیتا ناموفق: کد ' . $response_code . ' - ' . wp_remote_retrieve_body($response));
                }
                return 'Error: Received response code ' . $response_code;
            }
        }

        return 'All meta data added successfully.';
    } else {
        return 'No meta data to send.';
    }
}

function send_rest_source_meta_request($url, $post_id, $server_post_id, $jwt_token, $name_key, $link_key, $name_override = '', $rest_base = 'posts', $child_site_id = null)
{
    $post_url = "{$url}/wp-json/wp/v2/{$rest_base}/{$post_id}";
    $source_url = get_permalink($server_post_id);
    $source_site_name = $name_override ? $name_override : get_bloginfo('name');
    $payload = array('meta' => array());
    if (!empty($name_key) && !empty($source_site_name)) {
        $payload['meta'][$name_key] = $source_site_name;
    }
    if (!empty($link_key) && !empty($source_url)) {
        $payload['meta'][$link_key] = $source_url;
    }
    if (!empty($payload['meta'])) {
        $response = wp_remote_post($post_url, array(
            'method' => 'POST',
            'headers' => array(
                'Authorization' => 'Bearer ' . $jwt_token,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($payload),
        ));
        if (is_wp_error($response)) {
            if ($child_site_id) {
                insert_into_hrm_reports(date('Y-m-d H:i:s'), $server_post_id, $child_site_id, 0, 'ارسال متای منبع ناموفق: ' . $response->get_error_message());
            }
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            if (!in_array($response_code, array(200, 201))) {
                if ($child_site_id) {
                    insert_into_hrm_reports(date('Y-m-d H:i:s'), $server_post_id, $child_site_id, 0, 'ارسال متای منبع ناموفق: کد ' . $response_code . ' - ' . wp_remote_retrieve_body($response));
                }
            }
        }
    }
}

// replace a word with other word
function i8_hrm_replace_post_content($content, $target, $replace)
{
    $content = str_replace($target, $replace, $content);
    return $content;

}

// remove a link contentt
function i8_hrm_remove_link_content($content)
{
    // حذف تگ‌های <a> و حفظ محتوای متنی آن‌ها
    $content = preg_replace('/<a\s+.*?href=[\'"](.*?)[\'"].*?>(.*?)<\/a>/', '$2', $content);
    return $content;
}



// add tailwind css

// function i8_hrm_add_tailwind_css()
// {
//     $screen = get_current_screen();
//     if ($screen->id == 'post') {
//         wp_enqueue_style('i8_hrm_tailwind_css', HAM_PLUGIN_URL . '/assets/css/styles.css');
//     }
// }
// add_action('admin_enqueue_scripts', 'i8_hrm_add_tailwind_css', 100); -->



function i8_child_site_is_limit_post_in_day($post_id)
{
    $limt_sent_post_in_day = get_post_meta($post_id, 'i8_hrm_limit_sent_post_in_day', true);
    if ($limt_sent_post_in_day) {
        $current_day = get_post_meta($post_id, 'i8_hrm_limit_sent_post_today', true);
        $sent_post_today = get_post_meta($post_id, 'i8_hrm_sent_post_today', true);

        if ($current_day == '') {
            update_post_meta($post_id, 'i8_hrm_limit_sent_post_today', date('Y-m-d'));
            update_post_meta($post_id, 'i8_hrm_sent_post_today', 0);
            $current_day = get_post_meta($post_id, 'i8_hrm_limit_sent_post_today', true);
            $sent_post_today = get_post_meta($post_id, 'i8_hrm_sent_post_today', true);
        }

        if ($current_day != date('Y-m-d')) {
            update_post_meta($post_id, 'i8_hrm_limit_sent_post_today', date('Y-m-d'));
            update_post_meta($post_id, 'i8_hrm_sent_post_today', 0);
        }

        if ($current_day == date('Y-m-d') && $sent_post_today <= $limt_sent_post_in_day) {
            $sent_post_today++;
            update_post_meta($post_id, 'i8_hrm_sent_post_today', $sent_post_today);
            return true;
        } else {
            return false;
        }

    } else {
        return true;
    }
}



function display_categories_select($post_id)
{
    // واکشی تمام دسته‌بندی‌ها
    $categories = get_categories();
    $i8_hrm_forbbiden_cats = get_post_meta($post_id, 'i8_hrm_forbbiden_cats', true) ? get_post_meta($post_id, 'i8_hrm_forbbiden_cats', true) : '';
    // نمایش انتخابگر دسته‌بندی‌ها
    echo '<select id="category-select" name="i8_hrm_forbbiden_cats[]" multiple="multiple" style="width: 100%;">';

    foreach ($categories as $category) {
        $selected = '';
        if (is_array($i8_hrm_forbbiden_cats)) {
            $selected = in_array($category->term_id, $i8_hrm_forbbiden_cats) ? 'selected' : '';
        }

        echo '<option value="' . esc_attr($category->term_id) . '" ' . $selected . '>' . esc_html($category->name) . '</option>';
    }

    echo '</select>';
}


function add_hamresan_post_metabbox()
{
    add_meta_box('i8_hrm_metabox', 'همرسان', 'i8_hrm_render_hamresan_metabox', 'post', 'side', 'high');
}
function i8_hrm_render_hamresan_metabox($post)
{
    $post_status = get_post_status($post->ID);

    // بررسی وضعیت و نمایش پیام مناسب
    if ($post_status === 'publish') {
       $is_publish = true;
    } else {
        $is_publish = false;
    }

    ?>
    <div class="">
        <div>ارسال به : </div>
        <div class="widefat">
            <label>
                <input type="radio" name="i8_hrm_manual_setting" id="i8_hrm_manual_setting" value="auto" <?php echo ($is_publish == false) ? ' checked ' : ''; ?>
                    onchange="toggleSelect()">
                پیش‌فرض
            </label>
            <label>
                <input type="radio" name="i8_hrm_manual_setting" id="i8_hrm_manual_setting" value="manual"
                    onchange="toggleSelect()">
                دستی
            </label>
            <label>
                <input type="radio" name="i8_hrm_manual_setting" id="i8_hrm_manual_setting" value="off" <?php echo ($is_publish == true) ? ' checked ' : ''; ?>
                    onchange="toggleSelect()">
                عدم ارسال
            </label>
            <br>
        </div>
        <div class="widefat">
            <select name="i8_hrm_child_site_for_send" id="i8_hrm_child_site_for_send" class="widefat" multiple>
                <?php
                $child_site = get_posts(array(
                    'post_type' => 'i8_child_sites',
                    'posts_per_page' => -1,
                    'post_status' => 'publish'
                ));
                foreach ($child_site as $child): ?>
                    <option value="<?php echo $child->ID; ?>" selected><?php echo $child->post_title; ?></option>
                    <?php
                endforeach;
                ?>
            </select>
        </div>
    </div>

    <script>
        function toggleSelect() {
            const radios = document.getElementsByName('i8_hrm_manual_setting');
            const selectElement = document.getElementById('i8_hrm_child_site_for_send');
            let selectedValue = null;

            // پیدا کردن رادیو باتن انتخاب شده
            for (let radio of radios) {
                if (radio.checked) {
                    selectedValue = radio.value;
                    break; // اگر رادیو باتن انتخاب شده پیدا شد، از حلقه خارج می‌شویم
                }
            }

            // بررسی وضعیت رادیو باتن‌ها و تنظیم وضعیت select
            if (selectedValue === 'auto') {
                // اگر حالت پیش‌فرض انتخاب شده باشد
                selectElement.disabled = true; // غیرفعال کردن select
                selectElement.classList.add('disabled'); // اضافه کردن کلاس غیرفعال (اختیاری)
                // انتخاب همه گزینه‌ها
                for (let option of selectElement.options) {
                    option.selected = true;
                }
            } else if (selectedValue === 'manual') {
                // اگر حالت دستی انتخاب شده باشد
                selectElement.disabled = false; // فعال کردن select
                selectElement.classList.remove('disabled'); // حذف کلاس غیرفعال (اختیاری)
                // عدم انتخاب هیچ گزینه‌ای
                for (let option of selectElement.options) {
                    option.selected = false;
                }
            } else if (selectedValue === 'off') {
                // اگر حالت عدم ارسال انتخاب شده باشد
                selectElement.disabled = true; // غیرفعال کردن select
                selectElement.classList.add('disabled'); // اضافه کردن کلاس غیرفعال (اختیاری)
                // عدم انتخاب هیچ گزینه‌ای
                for (let option of selectElement.options) {
                    option.selected = false;
                }
            }
        }

        // در ابتدا وضعیت را تنظیم می‌کنیم
        toggleSelect();
    </script>
    <?php
}
add_action('add_meta_boxes', 'add_hamresan_post_metabbox');
