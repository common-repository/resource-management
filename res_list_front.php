<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * @package Frontend Resource List
 * @version 1.0
 */
global $wpdb;

$qry = "SELECT * FROM wp_res_library WHERE `lib_stat`='Y' ";
if ($args) {
    if ($args['res_cat'] != 'all') {
        $qry .= " AND `lib_cat` IN (" . $args['res_cat'] . ")";
        $qry .= " OR `lib_sub_cat` IN (" . $args['res_cat'] . ")";
    }
}
$qry .= " ORDER BY `lib_title` ASC";
?>

<div class="wrap_2" style="margin-bottom: 40px;">
    <h2>Resource List </h2>

    <?php $resrows = $wpdb->get_results($qry); ?>
    <div class="lib_grid_container">
        <table id="library_table" class="display widefat " cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Resource</th>
                    <th>Category</th>
                    <th>Sub Category</th>
                    <th style="text-align: center">Download</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($resrows) {
                    foreach ($resrows as $eachItem) {
                        $catRow = $wpdb->get_row("SELECT * FROM wp_res_category WHERE cat_id = " . $eachItem->lib_cat);
                        $subcatRow = $wpdb->get_row("SELECT * FROM wp_res_category WHERE cat_id = " . $eachItem->lib_sub_cat);

                        $edit_post = add_query_arg('edit_item', $eachItem->lib_ID, '?page=res-form');
                        $del_post = add_query_arg('delete_item', $eachItem->lib_ID, '?page=resource-management');
                        $download_link = add_query_arg('download', $eachItem->lib_ID);

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
                            <td>
        <?php if ($eachItem->lib_desc) { ?>
                                    <div class="tooltip_res"><?php echo stripslashes_deep($eachItem->lib_title); ?>
                                        <span class="tooltip_restext">
            <?php echo stripslashes_deep($eachItem->lib_desc); ?>
                                        </span>
                                    </div>
                                <?php } else echo stripslashes_deep($eachItem->lib_title); ?>
                            </td>
                            <td><?php if ($catRow) echo stripslashes_deep($catRow->cat_title); ?></td>
                            <td><?php if ($subcatRow) echo stripslashes_deep($subcatRow->cat_title); ?></td>
                            <td style="text-align: center"> 
        <?php if ($eachItem->lib_file_type != 'video') { ?>
                                    <a class="download_link" onclick="window.open('<?php echo admin_url('admin-ajax.php?action=res_handle_download_media&nonce='.wp_create_nonce("QwE1&*_123")) . '&media_item=' . $eachItem->lib_ID; ?>')" href="javascript:void(0);" title="Download" ></a>
                                    <p style="font-size: 12px; color: #666;"><?php echo $fileType . ': ' . res_file_size($eachItem->lib_file_size); ?></p>
                        <?php } ?>

                            </td>
                        </tr>
    <?php }
} ?>
            </tbody>
        </table>
    </div>

</div>

<script type="text/javascript">
    (function ($) {
        var library_table = $("#library_table");
        library_table.dataTable({
            responsive: true,
            pageLength: 50,
            order: [[0, "asc"]],
            lengthMenu: [[50, 100, 200, -1], [50, 100, 200, "All"]],
            columnDefs: [{orderable: false, "targets": [-1]}] /* -1 = 1st colomn, starting from the right. Add more numbers by comma separating */
        });

    })(jQuery);


</script>