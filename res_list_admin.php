<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * @package Resource List
 * @version 1.0
 */
global $wpdb;
$msg = '';



if (isset($_REQUEST['delete_item']) && wp_verify_nonce($_REQUEST['nonce'], 'QwE1&*_1234') && current_user_can('edit_posts')) {
    $delete_item = (int) sanitize_text_field($_REQUEST['delete_item']);
    $resrow = $wpdb->get_row("SELECT * FROM wp_res_library WHERE lib_ID = " . $delete_item);
    if (file_exists(stripslashes_deep($resrow->lib_file_path))) {
        unlink(stripslashes_deep($resrow->lib_file_path));
    }
    $wpdb->get_row("DELETE FROM wp_res_library WHERE lib_ID = " . $delete_item);
    $redirectUrl = add_query_arg('task', 'deleted', '?page=resource-management');
    echo "<script>window.location = '" . $redirectUrl . "';</script>";
}


if (isset($_REQUEST['task'])) {
    $task = sanitize_text_field($_REQUEST['task']);
    if ($task == 'added') {
        $msg = 'Resource added successfully.';
    }
    if ($task == 'updated') {
        $msg = 'Resource updated successfully.';
    }
    if ($task == 'deleted') {
        $msg = 'Resource deleted successfully.';
    }
}
?>

<div class="wrap">
    <h1>Resource List <a class="page-title-action" href="<?php echo admin_url('admin.php?page=res-form'); ?>">Add New</a></h1>
<?php if ($msg != '') { ?>
        <div class="updated notice notice-success is-dismissible" id="message">
            <p><?php echo $msg; ?></p>
            <button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
<?php } ?>


<?php $resrows = $wpdb->get_results("SELECT * FROM wp_res_library WHERE `lib_stat`='Y' ORDER BY `lib_updt_dt` DESC"); ?>
    <div class="lib_grid_container">
        <table id="library_table" class="display widefat " cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Sub Category</th>
                    <th>Last Updated Date</th>
                    <th>File Type</th>
                    <th>File Size</th>
                    <th style="text-align: center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($resrows) {
                    foreach ($resrows as $eachItem) {
                        $catRow = $wpdb->get_row("SELECT * FROM wp_res_category WHERE cat_id = " . $eachItem->lib_cat);
                        $subcatRow = $wpdb->get_row("SELECT * FROM wp_res_category WHERE cat_id = " . $eachItem->lib_sub_cat);

                        $edit_post = add_query_arg('edit_item', $eachItem->lib_ID, '?page=res-form');
                        $del_post = add_query_arg('delete_item', $eachItem->lib_ID, '?page=resource-management&nonce='.wp_create_nonce("QwE1&*_1234"));

                        if ($eachItem->lib_file_type == 'doc')
                            $fileType = 'Document';
                        if ($eachItem->lib_file_type == 'pdf')
                            $fileType = 'PDF';
                        if ($eachItem->lib_file_type == 'image')
                            $fileType = 'Image';
                        if ($eachItem->lib_file_type == 'text')
                            $fileType = 'Text';
                        if ($eachItem->lib_file_type == 'excel')
                            $fileType = 'Excel';
                        if ($eachItem->lib_file_type == 'video')
                            $fileType = 'Video';
                        ?>
                        <tr>
                            <td><?php echo stripslashes_deep($eachItem->lib_title); ?></td>
                            <td><?php if ($catRow) echo stripslashes_deep($catRow->cat_title); ?></td>
                            <td><?php if ($subcatRow) echo stripslashes_deep($subcatRow->cat_title); ?></td>
                            <td><?php echo date('M d, Y h:i:s a', strtotime($eachItem->lib_updt_dt)); ?></td>
                            <td><?php echo $fileType; ?></td>
                            <td><?php echo res_file_size($eachItem->lib_file_size); ?></td>
                            <td style="text-align: center"> <a href="<?php echo $edit_post; ?>">Edit</a>
                        <?php if ($eachItem->lib_file_type != 'video') { ?>
                                    | <a onclick="window.open('<?php echo admin_url('admin-ajax.php?action=res_handle_download_media&nonce='.wp_create_nonce("QwE1&*_123")) . '&media_item=' . $eachItem->lib_ID; ?>')" href="javascript:void(0);" >Download</a>
        <?php } ?>
                                | <a href="javascript:void(0);" onclick="DelRes('<?php echo $del_post; ?>');">Delete</a> 
                            </td>
                        </tr>
    <?php }
} ?>
            </tbody>
        </table>
    </div>
    <p> Use shortcode: <strong>[resources]</strong> for all resources to any post or in pages . 
        <br> Use <strong>[resources res_cat=Category ID]</strong> for any particular resource category even multiple category ID can be included by separating a comma(,).</p>
</div>

<script type="text/javascript">
    (function ($) {
        var library_table = $("#library_table");
        library_table.dataTable({
            responsive: true,
            pageLength: 50,
            order: [[3, "desc"]],
            lengthMenu: [[50, 100, 200, -1], [50, 100, 200, "All"]],
            columnDefs: [{orderable: false, "targets": [-1]}] /* -1 = 1st colomn, starting from the right. Add more numbers by comma separating */
        });

    })(jQuery);

    function DelRes(delLink) {
        if (window.confirm('Are you sure to delete this resource?')) {
            window.location = delLink;

        }
    }
</script>