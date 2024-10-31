<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * @package Resource Category/ Sub Categroy
 * @version 1.0
 */
global $wpdb;
$msg = '';
$hdLbl = 'Add';
$Cattitle = '';
$CatPiD = '';
$CatId = '';


if (isset($_REQUEST['edit_cat'])) {
    $edit_cat = (int) sanitize_text_field($_REQUEST['edit_cat']);
    $editItem = $wpdb->get_row("SELECT * FROM wp_res_category WHERE cat_id = " . $edit_cat);
    $Cattitle = stripslashes_deep($editItem->cat_title);
    $CatPiD = $editItem->cat_p_id;
    $CatId = $editItem->cat_id;
    $hdLbl = 'Edit';
}
if (isset($_REQUEST['delete_cat']) && wp_verify_nonce($_REQUEST['nonce'], 'QwE1&*_1234') && current_user_can('edit_posts')) {
    $wpdb->delete('wp_res_category', array('cat_id' => (int) sanitize_text_field($_REQUEST['delete_cat'])));

    $redirectUrl = add_query_arg('task', 'deleted', '?page=res-category');
    echo "<script>window.location = '" . $redirectUrl . "';</script>";
}



if (isset($_REQUEST['task'])) {
    $task = sanitize_text_field($_REQUEST['task']);
    if ($task == 'added') {
        $msg = 'Category added successfully.';
    }
    if ($task == 'updated') {
        $msg = 'Category updated successfully.';
    }
    if ($task == 'deleted') {
        $msg = 'Category deleted successfully.';
    }
}



if (!empty($_POST) && check_admin_referer( 'res_cat_form', 'res_cat_submit' ) && current_user_can('edit_posts')) {
    
    if (sanitize_text_field($_POST['cat_ID']) == '') {
        $insArr = array(
            'cat_title' => sanitize_text_field($_POST['cat_title']),
            'cat_p_id' => (int) sanitize_text_field($_POST['p_cat']),
            'cat_crt_dt' => date('Y-m-d h:i:s'),
            'cat_updt_dt' => date('Y-m-d h:i:s')
        );
        $wpdb->insert('wp_res_category', $insArr);

        $redirectUrl = add_query_arg('task', 'added', '?page=res-category');
    } else {
        $updtArr = array(
            'cat_title' => sanitize_text_field($_POST['cat_title']),
            'cat_p_id' => (int) sanitize_text_field($_POST['p_cat']),
            'cat_updt_dt' => date('Y-m-d h:i:s')
        );
        $wpdb->update('wp_res_category', $updtArr, array('cat_id' => (int) sanitize_text_field($_POST['cat_ID'])));

        $redirectUrl = add_query_arg('task', 'updated', '?page=res-category');
    }
    echo "<script>window.location = '" . $redirectUrl . "';</script>";
}

$myrows = $wpdb->get_results("SELECT * FROM wp_res_category WHERE `cat_p_id`=0 AND `cat_stat`='Y' ORDER BY `cat_title` ASC");
?>


<div class="wrap">
    <h1>Resource Categories</h1>
    <?php if ($msg != '') { ?>
        <div class="updated notice notice-success is-dismissible" id="message">
            <p><?php echo $msg; ?></p>
            <button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
    <?php } ?>

    <h2><?php echo $hdLbl; ?></h2>
    <form action="<?php echo admin_url('admin.php?page=res-category'); ?>" method="post" onsubmit="return Categoryvalid();">
        <div class="lib_drop_resource_container">
            <div class="lib_drop_image_table">
                <div class="lib_drop_image_table_cell">
                    <div class="lib_form_group">                
                        <div class="lib_label"><label>Category</label></div>
                        <div class="lib_title_section">
                            <input type="text" name="cat_title" id="cat_title" value="<?php echo esc_attr($Cattitle); ?>" placeholder="Enter Category Here"/>
                        </div>
                    </div> 
                </div>

                <div class="lib_drop_image_table_cell">        
                    <div class="lib_form_group">
                        <div class="lib_label"><label>Parent Category</label></div>
                        <div class="lib_select_custom">
                            <select name="p_cat">
                                <option value="0">As main category</option>
                                <?php
                                if ($myrows) {
                                    foreach ($myrows as $p_cat) {

                                        if ($CatPiD == $p_cat->cat_id)
                                            $sel = 'selected="selected"';
                                        else
                                            $sel = '';
                                        if ($CatId != $p_cat->cat_id) {
                                            ?>
                                            <option value="<?php echo $p_cat->cat_id; ?>" <?php echo $sel; ?>><?php echo esc_attr(stripslashes_deep($p_cat->cat_title)); ?></option>
        <?php }
    }
} ?>
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <div class="lib_buton_section">
                <input type="submit" value="Submit" class="button button-primary button-large" name="Submit">
                <input type="button" value="Cancel" class="button button-large" id="cancel_button" onclick="window.location = '<?php echo admin_url('admin.php?page=res-category'); ?>'" name="Cancel">
                <input type="hidden" name="cat_ID" value="<?php echo $CatId; ?>"/>
                 <?php wp_nonce_field('res_cat_form', 'res_cat_submit'); ?>
            </div>  
        </div>
    </form>


    <?php
    $catrows = $wpdb->get_results("SELECT * FROM wp_res_category WHERE `cat_stat`='Y' ORDER BY `cat_updt_dt` DESC");
    $allList = array();
    foreach ($catrows as $catrow) {
        $allList[$catrow->cat_id] = array(
            'catTitle' => stripslashes_deep($catrow->cat_title),
            'catParentTitle' => '',
            'updatOn' => $catrow->cat_updt_dt,
            'catId' => $catrow->cat_id,
            'catParentId' => $catrow->cat_p_id
        );
        if ($catrow->cat_p_id != 0) {
            $singleRow = $wpdb->get_row("SELECT * FROM wp_res_category WHERE cat_id = " . $catrow->cat_p_id);
            $allList[$catrow->cat_id]['catParentTitle'] = stripslashes_deep($singleRow->cat_title);
        }
    }

    if ($allList) {
        ?>
        <div class="lib_grid_container">
            <h2>List</h2>
            <table id="category_table" class="display widefat " cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Parent Category</th>
                        <th>Updated On</th>
                        <th style="text-align: right"></th>
                    </tr>
                </thead>
                <tbody>
    <?php
    foreach ($allList as $eachItem) {
        $edit_post = add_query_arg('edit_cat', $eachItem['catId'], '?page=res-category');
        $del_post = add_query_arg('delete_cat', $eachItem['catId'], '?page=res-category&nonce='.wp_create_nonce("QwE1&*_1234"));
        ?>
                        <tr>
                            <td><?php echo $eachItem['catId'] ?></td>
                            <td><?php echo $eachItem['catTitle'] ?></td>
                            <td><?php echo $eachItem['catParentTitle'] ?></td>
                            <td><?php echo date('M d, Y', strtotime($eachItem['updatOn'])); ?></td>
                            <td><a href="<?php echo $edit_post; ?>">Edit</a> 
                                | <a href="javascript:void(0);" onclick="Delcat('<?php echo $del_post; ?>');">Delete</a> 
                            </td>
                        </tr>
        <?php } ?>
                </tbody>
            </table>
        </div>

<?php } ?>
</div>
<script type="text/javascript">
    function Categoryvalid() {
        var cat_title = document.getElementById("cat_title");
        if (cat_title.value.trim() == '') {
            alert('Please enter a Title.');
            cat_title.focus();
            return false;
        }

    }
    function Delcat(delURL) {
        if (window.confirm('Are you sure want to delete?')) {
            window.location = delURL;
        } else {
            return false;
        }
    }
</script>
<script type="text/javascript">
    (function ($) {
        var category_table = $("#category_table");
        category_table.dataTable({
            responsive: true,
            pageLength: 10,
            order: [[3, "desc"]],
//                                        lengthMenu: [[50, 100, 200, -1], [50, 100, 200, "All"]],
            columnDefs: [{orderable: false, "targets": [-1]}] /* -1 = 1st colomn, starting from the right. Add more numbers by comma separating */
        });

    })(jQuery);
</script>