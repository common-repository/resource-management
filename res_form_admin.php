<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * @package Resource Form
 * @version 1.0
 */
global $wpdb;
$res_ID = '';
$lib_title = '';
$lib_desc = '';
$lib_embed_code = '';
$lib_file_type = '';
$lib_cat = '';
$lib_sub_cat = '';
$lib_file_path = '';
$lib_file_link = '';
$hdLbl = 'Add New Resource';
$edit_item = '';

if (isset($_REQUEST['edit_item'])) {
    $edit_item = (int) sanitize_text_field($_REQUEST['edit_item']);
    $ItemDetails = $wpdb->get_row("SELECT * FROM wp_res_library WHERE lib_ID = " . $edit_item);
    $lib_title = stripslashes_deep($ItemDetails->lib_title);
    $lib_desc = stripslashes_deep($ItemDetails->lib_desc);
    $lib_embed_code = stripslashes_deep($ItemDetails->lib_embed_code);
    $lib_file_type = stripslashes_deep($ItemDetails->lib_file_type);
    $lib_cat = $ItemDetails->lib_cat;
    $lib_sub_cat = $ItemDetails->lib_sub_cat;
    $lib_file_path = stripslashes_deep($ItemDetails->lib_file_path);
    $lib_file_link = stripslashes_deep($ItemDetails->lib_file_link);
    $res_ID = $ItemDetails->lib_ID;
    $hdLbl = 'Edit Resource';
}

if (!empty($_POST) && check_admin_referer( 'res_form', 'res_submit' ) && current_user_can('edit_posts')) {
    
    $fileSize = '';
    $fileURL = '';
    $filePath = '';

    if (sanitize_text_field($_POST['res_file_type']) != 'video') {
        $fileSize = sanitize_text_field($_POST['lib_file_size']);
        $fileURL = sanitize_text_field($_POST['lib_file_link']);
        $filePath = sanitize_text_field($_POST['lib_file_path']);
    }


    if (sanitize_text_field($_POST['res_ID']) == '') {
        $insArr = array(
            'lib_title' => sanitize_text_field($_POST['res_title']),
            'lib_desc' => sanitize_text_field($_POST['res_desc']),
            'lib_cat' => (int) sanitize_text_field($_POST['res_cat']),
            'lib_sub_cat' => (int) sanitize_text_field($_POST['res_sub_cat']),
            'lib_file_type' => sanitize_text_field($_POST['res_file_type']),
            'lib_embed_code' => sanitize_text_field($_POST['res_media_embed']),
            'lib_file_size' => $fileSize,
            'lib_file_link' => $fileURL,
            'lib_file_path' => $filePath,
            'lib_crt_dt' => date('Y-m-d h:i:s'),
            'lib_updt_dt' => date('Y-m-d h:i:s')
        );
        $wpdb->insert('wp_res_library', $insArr);
        $lastid = $wpdb->insert_id;
        $redirectUrl = add_query_arg('task', 'added', '?page=resource-management');
    } else {
        $updtArr = array(
            'lib_title' => sanitize_text_field($_POST['res_title']),
            'lib_desc' => sanitize_text_field($_POST['res_desc']),
            'lib_cat' => (int) sanitize_text_field($_POST['res_cat']),
            'lib_sub_cat' => (int) sanitize_text_field($_POST['res_sub_cat']),
            'lib_file_type' => sanitize_text_field($_POST['res_file_type']),
            'lib_embed_code' => sanitize_text_field($_POST['res_media_embed']),
            'lib_updt_dt' => date('Y-m-d h:i:s')
        );

        if ($filePath != '' && sanitize_text_field($_POST['res_file_type']) != 'video') {
            $ItemDetails = $wpdb->get_row("SELECT * FROM wp_res_library WHERE lib_ID = " . (int) sanitize_text_field($_POST['res_ID']));
            $lib_file_path = $ItemDetails->lib_file_path;
            if (file_exists($lib_file_path)) {
                unlink($lib_file_path);
            }

            $updtArr['lib_file_size'] = $fileSize;
            $updtArr['lib_file_link'] = $fileURL;
            $updtArr['lib_file_path'] = $filePath;
        }

        $wpdb->update('wp_res_library', $updtArr, array('lib_ID' => (int) sanitize_text_field($_POST['res_ID'])));

        $redirectUrl = add_query_arg('task', 'updated', '?page=resource-management');
    }
    echo "<script>window.location = '" . $redirectUrl . "';</script>";
}

//Set Nonce
$ajax_nonce = wp_create_nonce("QwE1&*_123");
?>

<div class="wrap">
    <h1><?php echo $hdLbl; ?></h1>
    <form action="<?php echo admin_url('admin.php?page=res-form'); ?>" method="post" enctype="multipart/form-data" onsubmit="return ResLibValid();">
        <div class="lib_drop_resource_container">

            <div class="long_text_box"><input type="text" name="res_title" id="res_title" value="<?php echo esc_attr($lib_title); ?>"  placeholder="Enter Title Here"/></div>

            <div class="lib_drop_image_table">
                <div class="lib_drop_image_table_cell">
                    <div class="lib_form_group">
                        <div class="lib_select_custom">
                            <!--<select name="res_cat" id="res_cat" onchange="getSubCat(this.value)">-->
                            <select name="res_cat" id="res_cat">
                                <option value="">Category</option>
                                <?php
                                $catrows = $wpdb->get_results("SELECT * FROM wp_res_category WHERE `cat_stat`='Y' AND `cat_p_id`=0 ORDER BY `cat_title` ASC");
                                if ($catrows) {
                                    foreach ($catrows as $ListItem) {
                                        if ($ListItem->cat_id == $lib_cat)
                                            $sel = 'selected="selected"';
                                        else
                                            $sel = '';
                                        ?>
                                        <option value="<?php echo $ListItem->cat_id; ?>" <?php echo $sel; ?>><?php echo esc_attr(stripslashes_deep($ListItem->cat_title)); ?></option>
                                    <?php }
                                }
                                ?>
                            </select>

                        </div>
                    </div>

                    <div class="lib_form_group">
                        <div class="lib_select_custom">
                            <select name="res_sub_cat" id="res_sub_cat">
                                <option value="">Sub Category</option>
                                <?php
                                if ($lib_cat != '') {
                                    $subcatrows = $wpdb->get_results("SELECT * FROM wp_res_category WHERE `cat_stat`='Y' AND `cat_p_id`=" . (int) $lib_cat . " ORDER BY `cat_title` ASC");
                                    if ($subcatrows) {
                                        foreach ($subcatrows as $ListItem) {
                                            if ($ListItem->cat_id == $lib_sub_cat)
                                                $sel = 'selected="selected"';
                                            else
                                                $sel = '';
                                            ?>
                                            <option value="<?php echo $ListItem->cat_id; ?>" <?php echo $sel; ?>><?php echo esc_attr(stripslashes_deep($ListItem->cat_title)); ?></option>
        <?php }
    }
} ?>
                            </select>

                        </div>
                    </div>

                    <?php
                    if ($lib_file_type == 'doc') {
                        $fileTypVal = '.doc,.docx';
                    } else if ($lib_file_type == 'pdf') {
                        $fileTypVal = '.pdf';
                    } else if ($lib_file_type == 'image') {
                        $fileTypVal = '.png,.jpg,.gif';
                    } else if ($lib_file_type == 'text') {
                        $fileTypVal = '.txt';
                    } else if ($lib_file_type == 'excel') {
                        $fileTypVal = '.xls,.xlsx';
                    } else {
                        $fileTypVal = '';
                    }
                    ?>
                    <div class="lib_form_group">
                        <div class="lib_select_custom">
                            <select name="res_file_type" id="res_file_type" onchange="fileTypechng(this.value)">
                                <option value="">File Type</option>
                                <option value="doc" <?php if ($lib_file_type == 'doc') echo 'selected="selected"'; ?>> Document (.doc|.docx)</option>
                                <option value="pdf" <?php if ($lib_file_type == 'pdf') echo 'selected="selected"'; ?>> PDF (.pdf)</option>
                                <option value="image" <?php if ($lib_file_type == 'image') echo 'selected="selected"'; ?>>Image (.png|.jpg|.gif)</option>
                                <option value="text" <?php if ($lib_file_type == 'text') echo 'selected="selected"'; ?>>Text (.txt)</option>
                                <option value="excel" <?php if ($lib_file_type == 'excel') echo 'selected="selected"'; ?>>Excel (.xls|.xlsx)</option>
                                <option value="video" <?php if ($lib_file_type == 'video') echo 'selected="selected"'; ?>>Video</option>
                            </select>
                            <input name="res_file_type_val" id="res_file_type_val" value="<?php echo $fileTypVal; ?>" type="hidden">
                        </div>
                    </div>


                </div>
                <div class="lib_drop_image_table_cell">        

                    <div class="lib_drop_resourc_place">
                        <div class="lib_drop_resourc" >
                            <?php
                            if ($lib_file_type == 'video') {
                                $style1 = "display:block;";
                                $style2 = "display:none;";
                            } else {
                                $style1 = "display:none;";
                                $style2 = "display:block;";
                            }
                            ?>
                            <textarea name="res_media_embed" id="res_media_embed" placeholder="Paste your embedded code here."  rows="7" style="<?php echo $style1; ?>width: 100%;"><?php echo esc_attr($lib_embed_code); ?></textarea>
                            <div class="dropzone box-liner" id="myDropzone" style="<?php echo $style2; ?>"></div>
                            <label id="lblError"></label>
<?php if ($edit_item != '') { ?>
                                <a onclick="window.open('<?php echo admin_url('admin-ajax.php?action=res_handle_download_media&nonce='.$ajax_nonce) . '&media_item=' . $edit_item; ?>')" href="javascript:void(0);" >Download</a>
<?php } ?>
                        </div>
                    </div>
                </div>



            </div>
            <div class="lib_form_group"> 
                <div class="lib_label"><label><!--Description--></label></div>
                <div class="lib_textarea"><?php wp_editor($lib_desc, 'res_desc', $settings = array('textarea_name' => 'res_desc')); ?></div> 
            </div>


            <div class="lib_buton_section">
                <input type="hidden" id="lib_file_size" name="lib_file_size" value="" />
                <input type="hidden" id="lib_file_link" name="lib_file_link" value="" />
                <input type="hidden" id="lib_file_path" name="lib_file_path" value="" />
                <input type="submit" value="Submit" class="button button-primary button-large" id="lib_form_submit"  name="Submit">
                <input type="button" value="Cancel" class="button button-large" onclick="window.location = '<?php echo admin_url('admin.php?page=resource-management'); ?>'" name="Cancel">
                <input type="hidden" name="res_ID" value="<?php echo $res_ID; ?>"/>
                <?php wp_nonce_field('res_form', 'res_submit'); ?>
            </div>  

        </div>


    </form>

</div>

<script type="text/javascript" >

    jQuery(document).ready(function ($) {
        $("#res_cat").change(function () {
            var data = {
                'action': 'res_get_subcat',
                'security': '<?php echo $ajax_nonce; ?>',
                'catID': this.value
            };
            var res_sub_cat = document.getElementById("res_sub_cat");

            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajaxurl, data, function (response) {
                var sub_cat = '<option value="">Sub Category</option>';
                if (response != 'no') {
                    var subcats = JSON.parse(response);
                    for (var i = 0; i < subcats.length; i++) {
                        sub_cat += '<option value="' + subcats[i].cat_id + '" >' + subcats[i].cat_title + '</option>';
                    }
                }
                res_sub_cat.innerHTML = sub_cat;

            });
        });
    });
</script>

<script type="text/javascript">
    var allowedTypes = true;
    function fileTypechng(file_type) {
        var myDropzone = document.getElementById("myDropzone");
        var res_media_embed = document.getElementById("res_media_embed");
        var res_file_type_val = document.getElementById("res_file_type_val");
        if (file_type == 'video') {
            myDropzone.style.display = "none";
            res_media_embed.style.display = "block";
            res_file_type_val.value = file_type;

        } else {
            myDropzone.style.display = "block";
            res_media_embed.style.display = "none";
            if (file_type == 'doc') {
                res_file_type_val.value = '.doc,.docx';
            } else if (file_type == 'pdf') {
                res_file_type_val.value = '.pdf';
            } else if (file_type == 'image') {
                res_file_type_val.value = '.png,.jpg,.gif';
            } else if (file_type == 'text') {
                res_file_type_val.value = '.txt';
            } else if (file_type == 'excel') {
                res_file_type_val.value = '.xls,.xlsx';
            } else {
                res_file_type_val.value = '';
            }
        }
    }




    function ValidateExtension(fileUpload) {

        var file_type = document.getElementById("res_file_type_val").value;
        var allowedFiles = file_type.split(",");//[".doc", ".docx", ".pdf"];
        var lblError = document.getElementById("lblError");
        //var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+(" + allowedFiles.join('|') + ")$");
        var regex = new RegExp("(" + allowedFiles.join('|') + ")$");
        if (!regex.test(fileUpload.toLowerCase())) {
            lblError.innerHTML = "<b>" + allowedFiles.join(', ') + "</b> file only.";
            allowedTypes = false;
            return false;
        } else {
            allowedTypes = true;
            lblError.innerHTML = "";
            return true;
        }

    }

    function ResLibValid() {

        var res_title = document.getElementById("res_title");
        var res_cat = document.getElementById("res_cat");
        var res_file_type = document.getElementById("res_file_type");


        if (res_title.value.trim() == '') {
            alert('Please enter a Title.');
            res_title.focus();
            return false;
        }

//        if (res_cat.value == '') {
//            alert('Please select a Category.');
//            res_cat.focus();
//            return false;
//        }

        if (res_file_type.value == '') {
            alert('Please select a File Type.');
            res_file_type.focus();
            return false;
        }

        var file_type = document.getElementById("res_file_type_val").value;
        var allowedFiles = file_type.split(",");//[".doc", ".docx", ".pdf"];
        if (allowedTypes == false) {
            alert("Please upload files having extensions: " + allowedFiles.join(', ') + " only.");
            return false;
        }

    }
</script>


<script type="text/javascript">
    (function () {

        Dropzone.options.myDropzone = {
            url: '<?php echo admin_url('admin-ajax.php?action=res_handle_dropped_media&nonce='.$ajax_nonce); ?>',
            //autoProcessQueue: false,
            //uploadMultiple: true,
//            parallelUploads: 5,
            maxFiles: 1,
            dictDefaultMessage: '<p class="drag-txt">Drop Resource File Here</p>',
            maxFilesize: 100, //MB
            //acceptedFiles: document.getElementById('res_file_type_val').value,//'image/*',
            //addRemoveLinks: true,
            success: function (file, response) {
                //alert(response);
                var abd = JSON.parse(response);
                document.getElementById('lib_file_size').value = abd.fileSize;
                document.getElementById('lib_file_link').value = abd.fileURL;
                document.getElementById('lib_file_path').value = abd.filePath;
            },
            init: function () {
                // Remove previous one and upload new.
                this.on("maxfilesexceeded", function (file) {
                    this.removeAllFiles();
                    this.addFile(file);
                });

                this.on("addedfile", function (file) {
                    //alert(file.name)
                    //Validating specific file uploading
                    ValidateExtension(file.name);

                    // Create the remove button
                    var removeButton = Dropzone.createElement("<a href='javascript:void(0);'> Remove file</a>");

                    // Capture the Dropzone instance as closure.
                    var _this = this;

                    // Listen to the click event
                    removeButton.addEventListener("click", function (e) {
                        // Make sure the button click doesn't submit the form:
                        document.getElementById('lib_file_size').value = '';
                        document.getElementById('lib_file_link').value = '';
                        document.getElementById('lib_file_path').value = '';
                        document.getElementById("lblError").innerHTML = '';
                        e.preventDefault();
                        e.stopPropagation();
                        // Remove the file preview.
                        _this.removeFile(file);
                        // If you want to the delete the file on the server as well,
                        // you can do the AJAX request here.
                    });

                    // Add the button to the file preview element.
                    file.previewElement.appendChild(removeButton);
                });

            }
        };
    })();

</script>