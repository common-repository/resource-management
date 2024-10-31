<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * @package Resource Management
 * @version 1.0
 */
/*
  Plugin Name: Resource Management
  Description: A wordpress plugin for uploading and managing resources from admin. Resources are displayed in a searchable datatable and can be downloaded from frontend.
  Author: Soumen Roy
  Version: 1.0
  Author URI: mailto:soumenroy111@gmail.com
 */

include_once 'res_functions.php';

//***************************************************************** Add js/css files  ********************************
add_action('init', 'res_init');


//***************************************************************** Hook for adding admin menus ***********************
add_action('admin_menu', 'res_admin_menu');


//***************************************** Run the install scripts upon plugin activation **************************** 
register_activation_hook(__FILE__, 'res_install');
//register_deactivation_hook( __FILE__, 'res_uninstall' );
//***************************************** Create shortcode to use resource list ************************************* 
add_shortcode("resources", "res_list_func");


//***************************************** Ajax hook ************************************* 
add_action('wp_ajax_res_get_subcat', 'res_get_subcat'); // Get Subcategory items by category ID

add_action('wp_ajax_res_handle_dropped_media', 'res_handle_dropped_media'); // Drag n Drop file upload

add_action('wp_ajax_res_handle_download_media', 'res_handle_download_media'); // Download file
?>
