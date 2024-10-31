<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * @package All Functions
 * @version 1.0
 */

function res_admin_menu() {

    add_menu_page("Resources", "Resources", 1, "resource-management", "res_admin", 'dashicons-controls-repeat', 100); //after Separator-99
    // Add a submenu to the custom top-level menu:
    add_submenu_page('resource-management', 'Resource', 'Add New', 'manage_options', 'res-form', 'res_mng_form');
    add_submenu_page('resource-management', 'Categories', 'Categories', 'manage_options', 'res-category', 'res_mng_category');
}

function res_admin() {
    include_once 'res_list_admin.php';
}

function res_mng_form() {
    include_once 'res_form_admin.php';
}

function res_mng_category() {
    include_once 'res_category_admin.php';
}

function res_install() {
    global $wpdb;
    $res_library = $wpdb->prefix . 'res_library';
    $res_category = $wpdb->prefix . 'res_category';

    // Create the Resource Library database table
    if ($wpdb->get_var("show tables like '$res_library'") != $res_library) {
        $sql = "CREATE TABLE IF NOT EXISTS " . $res_library . " (
                        `lib_ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                          `lib_title` varchar(255) NOT NULL,
                          `lib_desc` text NOT NULL,
                          `lib_cat` int(11) NOT NULL,
                          `lib_sub_cat` int(11) NOT NULL,
                          `lib_file_type` varchar(255) NOT NULL,
                          `lib_embed_code` text NOT NULL,
                          `lib_file_size` int(11) NOT NULL,
                          `lib_file_link` varchar(255) NOT NULL,
                          `lib_file_path` varchar(255) NOT NULL,
                          `lib_crt_dt` datetime NOT NULL,
                          `lib_updt_dt` datetime NOT NULL,
                          `lib_stat` enum('Y','N') NOT NULL DEFAULT 'Y'
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Create the Resource Category database table
    if ($wpdb->get_var("show tables like '$res_category'") != $res_category) {
        $sql = "CREATE TABLE IF NOT EXISTS " . $res_category . " (
                        `cat_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                          `cat_p_id` int(11) NOT NULL DEFAULT '0',
                          `cat_title` varchar(255) NOT NULL,
                          `cat_crt_dt` datetime NOT NULL,
                          `cat_updt_dt` datetime NOT NULL,
                          `cat_stat` enum('Y','N') NOT NULL DEFAULT 'Y'
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

function res_uninstall() {
    global $wpdb;
    $res_library = $wpdb->prefix . 'res_library';
    $res_category = $wpdb->prefix . 'res_category';

    $wpdb->query("DROP TABLE IF EXISTS " . $res_library . " ");
    $wpdb->query("DROP TABLE IF EXISTS " . $res_category . " ");
}

function res_get_subcat() {
    global $wpdb; // this is how you get access to the database
    check_ajax_referer('QwE1&*_123', 'security');
    $catID = intval(sanitize_text_field($_POST['catID']));

    if (isset($catID) && $catID != 0 && current_user_can('edit_posts')) {
        $sql = "SELECT * FROM wp_res_category WHERE `cat_stat`='Y' AND `cat_p_id`= " . $catID . " ORDER BY `cat_title` ASC";
        $catrows = $wpdb->get_results($sql);

        if ($catrows) {
            $all = array();
            foreach ($catrows as $row) {
                $all[] = array(
                    'cat_id' => $row->cat_id,
                    'cat_p_id' => $row->cat_p_id,
                    'cat_title' => esc_attr(stripslashes($row->cat_title))
                );
            }
            echo json_encode($all);
        } else {
            echo 'no';
        }
    } else {
        echo 'no';
    }

    wp_die(); // this is required to terminate immediately and return a proper response
}

function res_handle_dropped_media() {

    if (!empty($_FILES) && wp_verify_nonce($_REQUEST['nonce'], 'QwE1&*_123') && current_user_can('edit_posts')) {

        if (!function_exists('wp_handle_upload')) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        $uploadedfile = $_FILES['file'];
        $arrVal = array();
        if ($uploadedfile['tmp_name'] != '') {
            $upload_overrides = array('test_form' => false);

            $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                $fileSize = filesize($movefile['file']);
                $filePath = $movefile['file'];
                $fileURL = $movefile['url'];

                $arrVal['fileSize'] = $fileSize;
                $arrVal['filePath'] = esc_attr($filePath);
                $arrVal['fileURL'] = esc_attr($fileURL);
            } else {
                /**
                 * Error generated by _wp_handle_upload()
                 * @see _wp_handle_upload() in wp-admin/includes/file.php
                 */
                //echo $movefile['error'];
            }
        }
        echo json_encode($arrVal);
        die();
    }
}

function res_handle_download_media() {
    global $wpdb;
    $media_item = intval(sanitize_text_field($_REQUEST['media_item']));
    if (isset($media_item) && $media_item != 0 && wp_verify_nonce($_REQUEST['nonce'], 'QwE1&*_123')) {
        $getrow = $wpdb->get_row("SELECT * FROM wp_res_library WHERE `lib_stat`='Y' AND `lib_ID`=" . $media_item);

        if ($getrow) {
            echo 'Downloading...' .
            $files = explode('/', $getrow->lib_file_path);
            $filename = $files[count($files) - 1];
            res_file_download($getrow->lib_file_path, NULL);
            wp_die();
        } else {
            echo 'No download file!';
            echo "<script>window.location = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
        }
    }
}

function res_file_size($bytes) {
    $label = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $bytes >= 1024 && $i < ( count($label) - 1 ); $bytes /= 1024, $i++)
        ;
    return( round($bytes, 2) . " " . $label[$i] );
}

function res_list_func($atts) {
    $args = shortcode_atts(array('res_cat' => 'all'), $atts);


    // begin output buffering
    ob_start();

    // output some content
    include_once 'res_list_front.php';

    // end output buffering, grab the buffer contents, and empty the buffer
    return ob_get_clean();
}

function res_init() {

    wp_enqueue_style('res-datatables-min', plugins_url('css/datatables.min.css', __FILE__), array(), '1.10.11');
    wp_enqueue_script('res-datatables-min', plugins_url('js/datatables.min.js', __FILE__), array('jquery'), '1.10.11');

    if (is_admin()) {

        if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'res-form') {
            wp_enqueue_style('res-dropzone.css', plugins_url('css/dropzone.css', __FILE__));
            wp_enqueue_script('res-dropzone.js', plugins_url('js/dropzone.js', __FILE__));
        }
    }
    wp_enqueue_style('res-style', plugins_url('css/res-style.css', __FILE__));
}

function res_file_download($filename = '', $data = '', $set_mime = FALSE) {

    if ($filename === '' OR $data === '') {
        return;
    } elseif ($data === NULL) {
        if (@is_file($filename) && ($filesize = @filesize($filename)) !== FALSE) {
            $filepath = $filename;
            $filename = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', $filename));
            $filename = end($filename);
        } else {
            return;
        }
    } else {
        $filesize = strlen($data);
    }

    // Set the default MIME type to send
    $mime = 'application/octet-stream';

    $x = explode('.', $filename);
    $extension = end($x);

    if ($set_mime === TRUE) {
        if (count($x) === 1 OR $extension === '') {
            /* If we're going to detect the MIME type,
             * we'll need a file extension.
             */
            return;
        }

        // Load the mime types
        $mimes = & get_mimes();

        // Only change the default MIME if we can find one
        if (isset($mimes[$extension])) {
            $mime = is_array($mimes[$extension]) ? $mimes[$extension][0] : $mimes[$extension];
        }
    }


    if (count($x) !== 1 && isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/Android\s(1|2\.[01])/', $_SERVER['HTTP_USER_AGENT'])) {
        $x[count($x) - 1] = strtoupper($extension);
        $filename = implode('.', $x);
    }

    if ($data === NULL && ($fp = @fopen($filepath, 'rb')) === FALSE) {
        return;
    }

    // Clean output buffer
    if (ob_get_level() !== 0 && @ob_end_clean() === FALSE) {
        @ob_clean();
    }

    // Generate the server headers
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Expires: 0');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . $filesize);

    // Internet Explorer-specific headers
    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) {
        header('Cache-Control: no-cache, no-store, must-revalidate');
    }

    header('Pragma: no-cache');

    // If we have raw data - just dump it
    if ($data !== NULL) {
        exit($data);
    }

    // Flush 1MB chunks of data
    while (!feof($fp) && ($data = fread($fp, 1048576)) !== FALSE) {
        echo $data;
    }

    fclose($fp);
    exit;
}
