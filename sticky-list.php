<?php
/*
Plugin Name: Gravity Forms Sticky List
Plugin URI: https://github.com/13pixlar/sticky-list
Description: List and edit submitted entries from the front end
Version: 1.0
Author: 13pixar
Author URI: http://13pixlar.se
*/


/* Todo
 * Support for multi page forms
 */

//------------------------------------------
if (class_exists("GFForms")) {
    GFForms::include_addon_framework();

    class StickyList extends GFAddOn {

        protected $_version = "1.0";
        protected $_min_gravityforms_version = "1.8.19.2";
        protected $_slug = "sticky-list";
        protected $_path = "gravity-forms-sticky-list/sticky-list.php";
        protected $_full_path = __FILE__;
        protected $_title = "Gravity Forms Sticky List";
        protected $_short_title = "Sticky List";

        public function init(){
            parent::init();

            // Add localization
            $this->stickylist_localize();
            
            // Add setting to fields settings tab
            add_action("gform_field_standard_settings", array( $this, "stickylist_field_settings"), 10, 2);

            // Add the Sticky List shortcode
            add_shortcode( 'stickylist', array( $this, 'stickylist_shortcode' ) );

            // Add supporting scripts to field settings page
            add_action("gform_editor_js", array($this, "editor_script"));

            // Add field settings page tooltips
            add_filter("gform_tooltips", array( $this, "add_stickylist_tooltips"));

            // Add css
            add_action("wp_enqueue_scripts", array( $this, "register_plugin_styles"));

            // Add scripts
            add_action("wp_enqueue_scripts", array( $this, "register_plugin_scripts"));

            // View or Edit entries
            add_filter("gform_pre_render", array($this,"pre_entry_action"));
            add_action("gform_post_submission", array($this, "post_edit_entry"), 10, 2);

            // Delete entries
            $this->maybe_delete_entry();

            // Add notification options
            add_action("gform_notification_ui_settings", array($this, "stickylist_gform_notification_ui_settings"), 10, 3 );
            add_action("gform_pre_notification_save", array($this, "stickylist_gform_pre_notification_save"), 10, 2 );
            add_filter("gform_disable_notification", array($this, "stickylist_gform_disable_notification" ), 10, 4 );

            // Add confirmation options
            add_action("gform_confirmation_ui_settings", array($this, "stickylist_gform_confirmation_ui_settings"), 10, 3 );
            add_action("gform_pre_confirmation_save", array($this, "stickylist_gform_pre_confirmation_save"), 10, 2 );
            add_filter("gform_confirmation", array($this, "stickylist_gform_confirmation"), 10, 4);
        }

        
        /**
         * Sticky List localization function
         *
         */
        function stickylist_localize() {
            load_plugin_textdomain('sticky-list', false, basename( dirname( __FILE__ ) ) . '/languages' );
        }
        
        
        /**
         * Sticky List field settings function
         *
         */
        function stickylist_field_settings($position, $form_id){

            // Get the form
            $form = GFAPI::get_form($form_id);

            // Get form settings
            $settings = $this->get_form_settings($form);
                         
            // Only show settings if Sticky List is enabled for this form
            if(isset($settings["enable_list"]) && true == $settings["enable_list"]){
                
                // Show below everything else
                if($position == -1){ ?>
                    
                    <li class="list_setting">
                        Sticky List
                        <br>
                        <input type="checkbox" id="field_list_value" onclick="SetFieldProperty('stickylistField', this.checked);" /><label class="inline" for="field_list_value"><?php _e('Show in list', 'sticky-list'); ?> <?php gform_tooltip("form_field_list_value") ?></label>
                        <br>
                        <label class="inline" for="field_list_text_value"><?php _e('Column label', 'sticky-list'); ?> <?php gform_tooltip("form_field_text_value") ?></label><br><input class="fieldwidth-3" type="text" id="field_list_text_value" onkeyup="SetFieldProperty('stickylistFieldLabel', this.value);" />  
                    </li>
                    
                    <?php
                }
            }
        }

        
        /**
         * Sticky List field settings JQuery function
         *
         */
        function editor_script(){
            ?>
            <script type='text/javascript'>
                // Bind to the load field settings event to initialize the inputs
                jQuery(document).bind("gform_load_field_settings", function(event, field, form){
                    jQuery("#field_list_value").attr("checked", field["stickylistField"] == true);
                    jQuery("#field_list_text_value").val(field["stickylistFieldLabel"]);
                });
            </script>
            <?php
        }

       
        /**
         * Sticky List field settings tooltips function
         *
         */   
        function add_stickylist_tooltips($tooltips){
           $tooltips["form_field_list_value"] = __('<h6>Show field in list</h6>Check this box to show this field in the list.','sticky-list');
           $tooltips["form_field_text_value"] = __('<h6>Header text</h6>Use this field to override the default text header.','sticky-list');
           return $tooltips;
        }

      
        /**
         * Sticky List shortcode function
         *
         */
        function stickylist_shortcode( $atts ) {
            $shortcode_id = shortcode_atts( array(
                'id' => '1',
            ), $atts );

            // Get the form ID from shortcode
            $form_id = $shortcode_id['id'];

            // Get the form
            $form = GFAPI::get_form($form_id);

            // Get form settings (if someone know of a better way of doing this, do tell)
            $settings = $this->get_form_settings($form);
            if(isset($settings["enable_list"])) $enable_list = $settings["enable_list"]; else $enable_list = "";
            if(isset($settings["show_entries_to"])) $show_entries_to = $settings["show_entries_to"]; else  $show_entries_to = "";
            if(isset($settings["enable_view"])) $enable_view = $settings["enable_view"]; else $enable_view = "";
            if(isset($settings["enable_view_label"])) $enable_view_label = $settings["enable_view_label"]; else $enable_view_label = "";
            if(isset($settings["enable_edit"])) $enable_edit = $settings["enable_edit"]; else $enable_edit = "";
            if(isset($settings["enable_edit_label"])) $enable_edit_label = $settings["enable_edit_label"]; else $enable_edit_label = "";
            if(isset($settings["enable_delete"])) $enable_delete = $settings["enable_delete"]; else $enable_delete = "";
            if(isset($settings["enable_delete_label"])) $enable_delete_label = $settings["enable_delete_label"]; else $enable_delete_label = "";
            if(isset($settings["action_column_header"])) $action_column_header = $settings["action_column_header"]; else $action_column_header = "";
            if(isset($settings["embedd_page"])) $embedd_page = $settings["embedd_page"]; else $embedd_page = "";
            if(isset($settings["enable_sort"])) $enable_sort = $settings["enable_sort"]; else $enable_sort = "";
            if(isset($settings["enable_search"])) $enable_search = $settings["enable_search"]; else $enable_search = "";
            
            // Only render list if Sticky List is enabled for this form
            if($enable_list){

                // Get current user
                $current_user = wp_get_current_user();
                $current_user_id = $current_user->ID;
                   
                // Get entries to show depending on settings
                // Show only to creator
                if($show_entries_to === "creator"){

                    $search_criteria["field_filters"][] = array("key" => "status", "value" => "active");
                    $search_criteria["field_filters"][] = array("key" => "created_by", "value" => $current_user_id);
                    $entries = GFAPI::get_entries($form_id, $search_criteria);
                
                // Show to all logged in users   
                }elseif($show_entries_to === "loggedin"){
                    
                    if(is_user_logged_in()) {
                        $search_criteria["field_filters"][] = array("key" => "status", "value" => "active");
                        $entries = GFAPI::get_entries($form_id, $search_criteria);
                    }
                
                // Show to everyone
                }else{
                
                    $search_criteria["field_filters"][] = array("key" => "status", "value" => "active");
                    $entries = GFAPI::get_entries($form_id, $search_criteria);
                }

                // If we have some entries, lets loop trough them and start building the output html
                if($entries) {
                    
                    // This vaiable will hold all html for the form                
                    $list_html = "<div id='sticky-list-wrapper'>";
                    
                    // If sorting and searching is enabled, show search box        
                    if($enable_sort && $enable_search) {
                        $list_html .= "<input class='search' placeholder='" . __("Search", "sticky-list") . "' />";
                    }

                    $list_html .= "<table class='sticky-list'><tr>";
                    
                    // Get all fields
                    $fields = $form["fields"];

                    // Make a counter for use in sorting
                    $i = 0;

                    // Make table header
                    foreach ($fields as $field) {

                        if(isset($field["stickylistField"]) && $field["stickylistField"] != "") {

                            // If we have a custom field label we use that, if not we use the fields standard label
                            if(isset($field["stickylistFieldLabel"]) && $field["stickylistFieldLabel"] != "") {                            
                                $label = $field["stickylistFieldLabel"];                                
                            }else{
                                $label = $field["label"];
                            }
                            
                            $list_html .= "<th class='sort' data-sort='sort-$i'>$label</th>";

                            // Increment sorting counter
                            $i++;
                        }
                    }

                    // If view, edit or delete is enabled we need an extra column
                    if($enable_view || $enable_edit || $enable_delete) {

                        $list_html .= "<th class='sticky-action'>$action_column_header</th>";
                    }

                    $list_html .= "</tr><tbody class='list'>";

                    // Make table rows
                    foreach ($entries as $entry) {
                        
                        $entry_id = $entry["id"];

                        $list_html .= "<tr>";

                        // Recycle the sorting counter we used above
                        $i=0;

                        // Loop trough all the fields
                        foreach( $form["fields"] as $field ) {

                            // If the field is active 
                            if (isset($field["stickylistField"]) && $field["stickylistField"] != "") {
                                
                                // ...we get the value for it
                                $field_value = RGFormsModel::get_lead_field_value( $entry, $field );

                                // If the value is an array (i.e. address field, name field, etc)
                                if(is_array($field_value)) {

                                    // Sort the array so that the fields are shown in the correct order
                                    asort($field_value);
                                   
                                    $field_values = "";

                                    // Concatenate field values into string separated by a space
                                    foreach ($field_value as $field => $value) {
                                        $field_values .= $value . " ";

                                    }
                                    $list_html .= "<td class='sort-$i'>$field_values</td>";

                                }else{ 
                                    $list_html .= "<td class='sort-$i'>$field_value</td>";
                                }

                                // Increment sorting counter
                                $i++;
                            }
                        }

                        // If view, edit or delete is enabled we need a cell with appropiate links
                        if($enable_view || $enable_edit || $enable_delete){
                            
                            $list_html .= "<td class='sticky-action'>";

                                // Only show view link if view is enabled
                                if($enable_view) {
                                    $list_html .= "
                                        <form action='$embedd_page' method='post'>
                                            <button class='submit'>$enable_view_label</button>
                                            <input type='hidden' name='mode' value='view'>
                                            <input type='hidden' name='view_id' value='$entry_id'>
                                        </form>";
                                }

                                // Only show edit link if edit is enabled
                                if($enable_edit) {

                                    // ...and current user is the creator OR has the capability to edit others posts
                                    if($entry["created_by"] == $current_user->ID || current_user_can('edit_others_posts')) {
                                        $list_html .= "
                                            <form action='$embedd_page' method='post'>
                                                <button class='submit'>$enable_edit_label</button>
                                                <input type='hidden' name='mode' value='edit'>
                                                <input type='hidden' name='edit_id' value='$entry_id'>
                                            </form>";
                                    }
                                }

                                // Only show delete link if delete is enabled
                                if($enable_delete) {

                                    // ...and current user is the creator OR has the capability to delete others posts
                                    if($entry["created_by"] == $current_user->ID || current_user_can('delete_others_posts')) {
                                        
                                        $list_html .= "
                                            <button class='sticky-list-delete submit'>$enable_delete_label</button>
                                            <input type='hidden' name='delete_id' class='sticky-list-delete-id' value='$entry_id'>
                                        ";                                        
                                    }
                                    ?>
                                    
                                    <?php
                                }

                            $list_html .= "</td>";
                        }

                        $list_html .= "</tr>";
                    }

                    $list_html .= "</tbody></table></div>";

                    // If list sorting is enabled
                    if($enable_sort) {

                        // Build and initialize list.js
                        $sort_fileds = "";
                        for ($a=0; $a<$i; $a++) { 
                            $sort_fileds .= "'sort-$a',"; 
                        }
                        $list_html .= "<script>var options = { valueNames: [$sort_fileds] };var userList = new List('sticky-list-wrapper', options);</script><br><style>table.sticky-list th:not(.sticky-action) {cursor: pointer;}</style>";
                    }


                    // If delete is enabled we need to insert ajax scripts to help with deletion
                    if($enable_delete) {

                        // Set som variables to use in the ajax function
                        $ajax_delete = plugin_dir_url( __FILE__ ) . 'ajax-delete.php';
                        $ajax_spinner = plugin_dir_url( __FILE__ ) . 'img/ajax-spinner.gif';
                        $delete_failed = __('Delete failed','sticky-list');

                        $list_html .= "
                            <script>
                            jQuery(document).ready(function($) {
                                $('.sticky-list-delete').click(function(event) {
                                    
                                    var delete_id = $(this).siblings('.sticky-list-delete-id').val();
                                    var current_button = $(this);
                                    var current_row = current_button.parent().parent();
                                    current_button.html('<img src=\'$ajax_spinner\'>');
                                    
                                    $.post( '', { mode: 'delete', delete_id: delete_id, form_id: '$form_id' })
                                    .done(function() {
                                        current_button.html('');
                                        current_row.css({   
                                            background: '#fbdcdc',
                                            color: '#fff'
                                        });
                                        current_row.hide('slow');
                                    })
                                    .fail(function() {
                                        current_button.html('$delete_failed');
                                    })

                                });
                            });
                            </script>
                        ";
                    }
                
                // If we dont have any entries, show the "Empty list" text to the user
                }else{
                    $list_html = $settings["empty_list_text"];
                }
                                    
                return $list_html;
            }
        }
        

        /**
         * Add Sticky List stylesheet
         *
         */
        public function register_plugin_styles() {
            wp_register_style( 'stickylist', plugins_url( 'gravity-forms-sticky-list/css/sticky-list_styles.css' ) );
            wp_enqueue_style( 'stickylist' );
        }


        /**
         * Add Sticky List sortning js (using list.js)
         *
         */
        public function register_plugin_scripts() {
            wp_register_script( 'list-js', plugins_url( 'gravity-forms-sticky-list/js/list.min.js' ) );
            wp_enqueue_script( 'list-js' );

        }


        /**
         *  Editing entries
         *
         */ 
        public function post_edit_entry($entry, $form) {
            
            // If we are in edit mode
            if(isset($_POST["action"]) && $_POST["action"] == "edit") {

                // Get original entry id
                $original_entry_id = $_POST["original_entry_id"];

                // Get current user
                $current_user = wp_get_current_user();
                
                // Get original entry
                $original_entry =  GFAPI::get_entry($original_entry_id);

                // If we have an original entry that is active 
                if($original_entry && $original_entry["status"] == "active") {

                    // ...and the current user is creator OR has the capability to edit others posts
                    if($original_entry["created_by"] == $current_user->ID || current_user_can('edit_others_posts')) {

                        // Keep starred and read status
                        $entry["is_read"] = $original_entry["is_read"];
                        $entry["is_starred"] = $original_entry["is_starred"];

                        // Uppdate original entry with new fields
                        $success_uppdate = GFAPI::update_entry($entry, $original_entry_id);
                        
                        // Delete newly created entry
                        if($success_uppdate) $success_delete = GFAPI::delete_entry($entry["id"]);
                    }
                }
            }
        }


        /**
         * Performs actions when entrys are clicked in the list
         *
         */
        public function pre_entry_action($form) {
            
            if( isset($_POST["mode"]) == "edit" || isset($_POST["mode"]) == "view" ) {

                if($_POST["mode"] == "edit") {
                    $edit_id = $_POST["edit_id"];
                    $form_fields = GFAPI::get_entry($edit_id);
                }

                if($_POST["mode"] == "view") {
                    $view_id = $_POST["view_id"];
                    $form_fields = GFAPI::get_entry($view_id);
                }
                
                // Get current user
                $current_user = wp_get_current_user();
               
                // If we have an entry that is active
                if(!is_wp_error($form_fields) && $form_fields["status"] == "active") {
                    
                    // ... and the current user is the creator OR has the capability to edit others posts OR is viewing the entry
                    if($form_fields["created_by"] == $current_user->ID || current_user_can('edit_others_posts') || $_POST["mode"] == "view") {
                        
                       
                     
                        // Loop trough all the fields
                        foreach ($form_fields as $key => &$value) {

                            // If the key is numeric we need to change it from [X.X] to [input_X_X]
                            if (is_numeric($key)) {

                                // If the current field is a list field we need to unserialize it and flatten the array
                                if(is_array(maybe_unserialize($value))) {
                                    $list = maybe_unserialize($value);
                                    $value = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($list)), FALSE);
                                }

                                $new_key = str_replace(".", "_", "input_$key");
                                $form_fields[$new_key] = $form_fields[$key];
                                unset($form_fields[$key]);                                                           
                            }
                        }
                        
                        // Add is_submit_id field
                        $form_id = $form['id'];
                        $form_fields["is_submit_$form_id"] = "1";

                        // Get current form settings
                        $settings = $this->get_form_settings($form);

                        // Get update text
                        if(isset($settings["update_text"])) $update_text = $settings["update_text"]; else $update_text = "";

                        // If we are in edit mode we insert two hidden fields with entry id and mode = edit
                        if($_POST["mode"] == "edit") { ?>

                            <script>
                            jQuery(document).ready(function($) {
                                var thisForm = $('#gform_<?php echo $form_id;?>')
                                thisForm.append('<input type="hidden" name="action" value="edit" />');
                                thisForm.append('<input type="hidden" name="original_entry_id" value="<?php echo $edit_id; ?>" />');
                                $("#gform_submit_button_<?php echo $form_id;?>").val('<?php echo $update_text; ?>');
                            });
                            </script>

                <?php   }

                        // If we are in view mode we disable all inputs and hide the submit button        
                        if($_POST["mode"] == "view") { ?>

                            <script>
                            jQuery(document).ready(function($) {
                                $("#gform_<?php echo $form_id;?> :input").attr("disabled", true);
                                $("#gform_submit_button_<?php echo $form_id;?>").css('display', 'none');
                            });
                            </script>
                <?php   }

                        // Add our manipulated fields to the $_POST variable
                        $_POST = $form_fields;
                    }
                }
            }
            
            return $form;
        }


        /**
         * Delete entries
         * This function is used to delete entries with an ajax request
         * Could use better (or at least some) error handling
         */
        public function maybe_delete_entry() {
            
            // First we make sure that delete mode is set to "delete" and that we have the entry id and form id
            if(isset($_POST["mode"]) && $_POST["mode"] == "delete" && isset($_POST["delete_id"]) && isset($_POST["form_id"])) {

                // Get form id
                $form_id = $_POST["form_id"];

                // Get the form
                $form = GFAPI::get_form($form_id);

                // Get delete settings
                $settings = $this->get_form_settings($form);
                $enable_delete = $settings["enable_delete"];
                $delete_type = $settings["delete_type"];

                // Make sure that delete is enabled
                if($enable_delete) {

                    $delete_id = $_POST["delete_id"];                
                    $current_user = wp_get_current_user();
                    $entry = GFAPI::get_entry($delete_id);
                    
                    // If we were able to retrieve the entry
                    if(!is_wp_error($entry)) {

                        // ...and the current user is the creator OR has the capability to delete others posts
                        if($entry["created_by"] == $current_user->ID || current_user_can('delete_others_posts' )) {
                           
                            // Move to trash
                            if($delete_type == "trash") { 
                                $entry["status"] = "trash";
                                $success = GFAPI::update_entry($entry, $delete_id);
                            }

                            // Delete permanently
                            if($delete_type == "permanent") {
                                $success = GFAPI::delete_entry($delete_id);
                            }

                            // If delete (regardles of type) was successful, we send the notification (if any)
                            if($success) {

                                // Get all notifications for current form
                                $notifications = $form["notifications"];
                                $notification_ids = array();
                                
                                // Loop trough the notifications 
                                foreach ($notifications as $notification) {

                                    // Gett current notification type
                                    $notification_type = $notification["stickylist_notification_type"];

                                    // Collect ids from notifications that are set to "all" or "delete"
                                    if($notification_type == "delete" || $notification_type == "all") {
                                        $id = $notification["id"];
                                        array_push($notification_ids, $id);        
                                    }
                                }
                                
                                // Send the notification(s)
                                GFCommon::send_notifications($notification_ids, $form, $entry);
                            }          
                        }
                    }
                }
            }
        }


        /**
         * Form settings page
         *
         */
        public function form_settings_fields($form) {
            ?>
            <script>
            // Instert headers into the settings page. Since we need the headers to be translatable we set them here
            jQuery(document).ready(function($) { 
                $('#gaddon-setting-row-header-0 h4').html('<?php _e("General settings","sticky-list"); ?>')
                $('#gaddon-setting-row-header-1 h4').html('<?php _e("View, edit & delete","sticky-list"); ?>')
                $('#gaddon-setting-row-header-2 h4').html('<?php _e("Labels","sticky-list"); ?>')
                $('#gaddon-setting-row-header-3 h4').html('<?php _e("Sort & search","sticky-list"); ?>')
                $('#gaddon-setting-row-header-4 h4').html('<?php _e("Donate","sticky-list"); ?>')
                $('#gaddon-setting-row-donate .donate-text').html('<?php _e("Sticky List is completely free. But if you like, you can always <a target=\"_blank\" href=\"https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8R393YVXREFN6\">donate</a> a few bucks.","sticky-list"); ?>')
             });
            </script>
            <?php

            // Build an array of all post to allow for selection in "embedd page" dropdown
            $args = array( 'posts_per_page' => 999, 'post_type' => 'any','post_status' => 'any'); 
            $posts = get_posts( $args );
            $posts_array = array();
            foreach ($posts as $post) {
                $post_title = get_the_title($post->ID);
                $post_url = get_permalink($post->ID);

                // We do not want attachments
                if($post->post_type != 'attachment') {
                    $posts_array = array_merge(
                        array(
                            array(
                                "label" => $post_title,
                                "value" => $post_url
                            )
                        ),$posts_array);
                }
            }
            
            return array(
                array(
                    "title"  => __('Sticky List Settings','sticky-list'),
                    "fields" => array(
                        array(
                            "label"   => __('Enable for this form','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "enable_list",
                            "tooltip" => __('Check this box to enable Sticky List for this form','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => "",
                                    "name"  => "enable_list"
                                )
                            )
                        ),
                        array(
                            "label"   => __('Show entries in list to','sticky-list'),
                            "type"    => "select",
                            "name"    => "show_entries_to",
                            "tooltip" => __('Who should be able to se the entries in the list?','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Entry creator','sticky-list'),
                                    "value" => "creator"
                                ),
                                array(
                                    "label" => __('All logged in users','sticky-list'),
                                    "value" => "loggedin"
                                ),
                                array(
                                    "label" => __('Everyone','sticky-list'),
                                    "value" => "everyone"
                                )
                            )
                        ),
                        array(
                            "label"   => __('Embedd page/post','sticky-list'),
                            "type"    => "select",
                            "name"    => "embedd_page",
                            "tooltip" => __('The page/post where the form is embedded. This page will be used to view/edit the entry','sticky-list'),
                            "choices" => $posts_array
                        ),
                        array(
                            "label"   => __('View entries','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "enable_view",
                            "tooltip" => __('Check this box to enable users to view the complete submitted entry. A \"View\" link will appear in the list','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Enabled','sticky-list'),
                                    "name"  => "enable_view"
                                )
                            )
                        ),
                        array(
                            "label"   => __('View label','sticky-list'),
                            "type"    => "text",
                            "name"    => "enable_view_label",
                            "tooltip" => __('Label for the view button','sticky-list'),
                            "class"   => "small",
                            "default_value" => __('View','sticky-list')
                            
                        ),
                        array(
                            "label"   => __('Edit entries','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "enable_edit",
                            "tooltip" => __('Check this box to enable user to edit submitted entries. An \"Edit\" link will appear in the list','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Enabled','sticky-list'),
                                    "name"  => "enable_edit"
                                )
                            )
                        ),
                        array(
                            "label"   => __('Edit label','sticky-list'),
                            "type"    => "text",
                            "name"    => "enable_edit_label",
                            "tooltip" => __('Label for the edit button','sticky-list'),
                            "class"   => "small",
                            "default_value" => __('Edit','sticky-list')
                            
                        ),
                         array(
                            "label"   => __('Update button text','sticky-list'),
                            "type"    => "text",
                            "name"    => "update_text",
                            "tooltip" => __('Text for the submit button that is displayed when editing an entry','sticky-list'),
                            "class"   => "small",
                            "default_value" => __('Update','sticky-list')              
                        ),
                        array(
                            "label"   => __('Delete entries','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "enable_delete",
                            "tooltip" => __('Check this box to enable user to delete submitted entries. A \"Delete\" link will appear in the list','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Enabled','sticky-list'),
                                    "name"  => "enable_delete"
                                )
                            )
                        ),
                        array(
                            "label"   => __('Delete label','sticky-list'),
                            "type"    => "text",
                            "name"    => "enable_delete_label",
                            "tooltip" => __('Label for the delete button','sticky-list'),
                            "class"   => "small",
                            "default_value" => __('Delete','sticky-list')
                        ),
                        array(
                            "label"   => __('On delete','sticky-list'),
                            "type"    => "select",
                            "name"    => "delete_type",
                            "tooltip" => __('Move deleted entries to trash or delete permanently?','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Move to trash','sticky-list'),
                                    "value" => "trash"
                                ),
                                array(
                                    "label" => __('Delete permanently','sticky-list'),
                                    "value" => "permanent"
                                )
                            )
                        ),
                        array(
                            "label"   => __('Action column header','sticky-list'),
                            "type"    => "text",
                            "name"    => "action_column_header",
                            "tooltip" => __('Text to show as header for the action column','sticky-list'),
                            "class"   => "medium"
                            
                        ),
                        array(
                            "label"   => __('Empty list text','sticky-list'),
                            "type"    => "text",
                            "name"    => "empty_list_text",
                            "tooltip" => __('Text that is shown if the list is empty','sticky-list'),
                            "class"   => "medium"  
                        ),
                        array(
                            "label"   => __('List sort','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "enable_sort",
                            "tooltip" => __('Check this box to enable sorting for the list','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Enabled','sticky-list'),
                                    "name"  => "enable_sort"
                                )
                            )
                        ),
                        array(
                            "label"   => __('List search','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "enable_search",
                            "tooltip" => __('Check this box to enable search for the list','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Enabled','sticky-list'),
                                    "name"  => "enable_search"
                                )
                            )
                        )
                    )
                )
            );
        }


        /**
         * Include admin scripts
         *
         */
        public function scripts() {
        $scripts = array(
            array("handle" => "sticky_list_js",
                "src" => $this->get_base_url() . "/js/sticky-list_scripts.js",
                "version" => $this->_version,
                "deps" => array("jquery"),
                "enqueue" => array(
                    array(
                        "admin_page" => array("form_settings"),
                        "tab" => "sticky-list"
                        )
                    )
                ),
            );
            return array_merge(parent::scripts(), $scripts);
        }


        /**
         * Include admin css
         *
         */
        public function styles() {
            $styles = array(
                array("handle" => "sticky-list_admin_styles",
                    "src" => $this->get_base_url() . "/css/sticky-list_admin_styles.css",
                    "version" => $this->_version,
                    "enqueue" => array(
                    array(
                        "admin_page" => array("form_settings"),
                        "tab" => "sticky-list"
                        )
                    )
                )
            );
            return array_merge(parent::styles(), $styles);
        }


        /**
         * Add new notification settings
         *
         */
        function stickylist_gform_notification_ui_settings( $ui_settings, $notification, $form ) {

            $settings = $this->get_form_settings($form);

            if (isset($settings["enable_list"])) {

                // Add new notification options    
                $type = rgar( $notification, 'stickylist_notification_type' );
                $options = array(
                    'all' => __( "Always", 'sticky-list' ),
                    'new' => __( "When a new entry is submitted", 'sticky-list' ),
                    'edit' => __( "When an entry is updated", 'sticky-list' ),
                    'delete' => __( "When an entry is deleted", 'sticky-list' )
                );

                $option = '';

                // Loop trough the options
                foreach ( $options as $key => $value ) {
                    
                    $selected = '';
                    if ( $type == $key ) $selected = ' selected="selected"';
                    $option .= "<option value=\"{$key}\" {$selected}>{$value}</option>\n";
                }

                // Oputput the new setting
                $ui_settings['sticky-list_notification_setting'] = '
                <tr>
                    <th><label for="stickylist_notification_type">' . __( "Send this notification", 'sticky-list' ) . '</label></th>
                    <td><select name="stickylist_notification_type" value="' . $type . '">' . $option . '</select></td>
                </tr>';


                return ( $ui_settings );              
            }  
        }


        /**
         * Save the notification settings
         *
         */
        function stickylist_gform_pre_notification_save($notification, $form) {

            $notification['stickylist_notification_type'] = rgpost( 'stickylist_notification_type' );
            return ( $notification );
        }


        /**
         * Send selected notification type
         *
         */
        function stickylist_gform_disable_notification( $is_disabled, $notification, $form, $entry ) {

            // Get form settings
            $settings = $this->get_form_settings($form);

            // Only show if Sticky List is enabled for the current form
            if(isset($settings["enable_list"])) {
                
                if(isset($notification["stickylist_notification_type"]) && $notification["stickylist_notification_type"] != "") {

                    $is_disabled = true;

                    // If we are in edit mode
                    if($_POST["action"] == "edit") {
                        
                        // ...and the current notification has the "edit" or "all" setting
                        if($notification["stickylist_notification_type"] == "edit" || $notification["stickylist_notification_type"] == "all") {
                            $is_disabled = false;
                        }

                    // Or if this is a new entry    
                    }else{
                        
                        // ...and the current notification has the "new" or "all" setting
                        if ( $notification["stickylist_notification_type"] == "new" || $notification["stickylist_notification_type"] == "all" ) {
                            $is_disabled = false;
                        }
                    }
                }           
            }

            return ( $is_disabled );
        }




        /**
         * Add new confirmation settings
         *
         */
        function stickylist_gform_confirmation_ui_settings( $ui_settings, $confirmation, $form ) {

            $settings = $this->get_form_settings($form);

            if (isset($settings["enable_list"])) {

                // Add new confirmation options    
                $type = rgar( $confirmation, 'stickylist_confirmation_type' );
               
                $options = array(
                    'all' => __( "Always", 'sticky-list' ),
                    'never' => __( "Never", 'sticky-list' ),
                    'new' => __( "When a new entry is submitted", 'sticky-list' ),
                    'edit' => __( "When an entry is updated", 'sticky-list' ),
                );

                $option = '';

                // Loop trough the options 
                foreach ( $options as $key => $value ) {
                    
                    $selected = '';
                    if ( $type == $key ) $selected = ' selected="selected"';
                    $option .= "<option value=\"{$key}\" {$selected}>{$value}</option>\n";
                }

                // Oputput the new setting
                $ui_settings['sticky-list_confirmation_setting'] = '
                <tr>
                    <th><label for="stickylist_confirmation_type">' . __( "Display this confirmation", 'sticky-list' ) . '</label></th>
                    <td><select name="stickylist_confirmation_type" value="' . $type . '">' . $option . '</select></td>
                </tr>';

                return ( $ui_settings );              
            }  
        }


        /**
         * Save the confirmation settings
         *
         */
        function stickylist_gform_pre_confirmation_save($confirmation, $form) {

            $confirmation['stickylist_confirmation_type'] = rgpost( 'stickylist_confirmation_type' );
            return ( $confirmation );
        }


        /**
         * Show confirmations
         *
         */
        function stickylist_gform_confirmation($original_confirmation, $form, $lead, $ajax){
            
            // Get all confirmations for the current form
            $confirmations = $form["confirmations"];
            $new_confirmation = "";

            // If action is not set we assume its a new entry
            if($_POST["action"] == NULL) {
                $_POST["action"] = "new";
            }
            
            // Loop trough all confirmations
            foreach ($confirmations as $confirmation) {

                // Show matching confirmations
                if( $confirmation["stickylist_confirmation_type"] == $_POST["action"] || $confirmation["stickylist_confirmation_type"] == "all" || !isset($confirmation["stickylist_confirmation_type"])) {
                    
                    // If the confirmation is a message we add that message to the output sting
                    if($confirmation["type"] == "message") {
                        $new_confirmation .= $confirmation["message"] . " ";

                    // If not, we set the redirect variable to true    
                    }else{
                        $redirect = true;
                    }
                }                
            }
            
            // If the confirmation is not a redirect we return it, else we return the redirect confirmation
            if($redirect != true) { 
                return $new_confirmation;
            }else{
                return $original_confirmation;
            }
        }
    }

    // Phew, thats it. Lets initialize the class
    new StickyList();
}
