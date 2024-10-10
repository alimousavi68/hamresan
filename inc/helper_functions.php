<?php
// add_action('publish_post', 'send_new_post_to_child_sites');
function send_new_post_to_child_sites($post_id)
{
    // $post = get_post($post_id);

    // $post_type = get_post_type($post->ID);
    // if ($post_type != 'post') {
    //     return;
    // }

    // $post_info = array(
    //     "title" => $post->post_title,
    //     "content" => $post->post_content,
    //     ""
    //     "status" => "draft"
    // );

    // // به دست آوردن توکن JWT از سایت اصلی
    // $jwt_token = get_jwt_token(); // این تابع باید توکن JWT را برگرداند

    // // URL REST API سایت فرزند
    // $url = 'https://dastyar.online/wp-json/wp/v2/posts';

    // i8_hrm_send_post_to_child_sites($child_site_id , $post_info ,$jwt_token);
    // // ارسال درخواست به سایت فرزند با JWT
        // $response = wp_remote_post($url, array(
        //     'method' => 'POST',
        //     'body' => json_encode($post_info),
        //     'headers' => array(
        //         'Content-Type' => 'application/json',
        //         'Authorization' => 'Bearer ' . $jwt_token
        //     )
        // ));

}

// send post to child sites
function i8_hrm_send_post_to_child_sites($url , $post_info ,$jwt_token)
{
        // // ارسال درخواست به سایت فرزند با JWT
        // $response = wp_remote_post($url, array(
        //     'method' => 'POST',
        //     'body' => json_encode($post_info),
        //     'headers' => array(
        //         'Content-Type' => 'application/json',
        //         'Authorization' => 'Bearer ' . $jwt_token
        //     )
        // ));
}

// تابعی برای گرفتن توکن JWT
function get_jwt_token($url,$username,$password)
{
    // $username = 'modir';
    // $password = '0010986618bnm'; // رمز عبور واقعی

    // // URL گرفتن توکن JWT
    // $auth_url = 'https://dastyar.online/wp-json/jwt-auth/v1/token';


    if($url and $username and $password){
        $url = $url . '/wp-json/jwt-auth/v1/token';
        $response = wp_remote_post($url, array(
            'method' => 'POST',
            'body' => array(
                'username' => $username,
                'password' => $password
            )
        ));
    }
    
    if (is_wp_error($response)) {
        return false;
    }
    return true;
}



function i8_hrm_send_test_request($url, $data){
   
    // به دست آوردن توکن JWT از سایت اصلی
    $jwt_token = get_jwt_token(); // این تابع باید توکن JWT را برگرداند

    // URL REST API سایت فرزند
    $url = 'https://dastyar.online/wp-json/wp/v2/posts';

    i8_hrm_send_post_to_child_sites($child_site_id , $post_info ,$jwt_token);
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


// add tailwind css

// function i8_hrm_add_tailwind_css()
// {
//     $screen = get_current_screen();
//     if ($screen->id == 'post') {
//         wp_enqueue_style('i8_hrm_tailwind_css', HAM_PLUGIN_URL . '/assets/css/styles.css');
//     }
// }
// add_action('admin_enqueue_scripts', 'i8_hrm_add_tailwind_css', 100); -->
