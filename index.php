<?php
/*
Plugin Name: همرسان
Description: افزونه همرسان - انتشار خودکار پست ها در چندین سایت به صورت همزمان 
Version: 1.0
Author: Hasht Behesht
Author URI: https://www.ihasht.ir
*/


// Declare Const vraibles
define('HAM_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
define('HAM_PLUGIN_URL', plugins_url('', __FILE__));





// Includes Files
include_once HAM_PLUGIN_DIR_PATH . 'inc/DB.php';
include_once HAM_PLUGIN_DIR_PATH . 'inc/helper_functions.php';
include_once HAM_PLUGIN_DIR_PATH . 'inc/child_sites_post_type.php';

include_once HAM_PLUGIN_DIR_PATH . 'pages/reports.php';
include_once HAM_PLUGIN_DIR_PATH . 'pages/settings.php';


// فعال‌سازی افزونه
register_activation_hook(__FILE__, 'create_reports_table');

