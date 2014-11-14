<?php
/*
Plugin Name: Gravity Forms Sticky List
Plugin URI: 
Description: List and edit submitted forms from the front end
Version: 1.0 beta
Author: 13pixar
Author URI: http://13pixlar.se
*/

/* Todo
 * Customized text for view, edit, delete and update buttons
 * Table sorting
 * Table search
 * Conditional notifications
 * Conditional confirmations
 * Support for multi page forms
 * Write plugin readme
 * Make plugin homepage
 */

//------------------------------------------
if (class_exists("GFForms")) {
    GFForms::include_addon_framework();

    class EditEntries extends GFAddOn {

        protected $_version = "1.0 beta";
        protected $_min_gravityforms_version = "1.7.9999";
        protected $_slug = "sticky-list";
        protected $_path = "sticky-list/sticky-list.php";
        protected $_full_path = __FILE__;
        protected $_title = "Gravity Forms Sticky List";
        protected $_short_title = "Sticky List";

        public function init(){
            parent::init();
            
            // Add setting to fields settings tab
            add_action("gform_field_standard_settings", array( $this, "stickylist_field_settings"), 10, 2);

            // Add the Sticky List shortcode
            add_shortcode( 'stickylist', array( $this, 'stickylist_shortcode' ) );

            // Add supporting scripts to field settings page
            add_action("gform_editor_js", array($this, "editor_script"));

            // Add all our tooltips
            add_filter("gform_tooltips", array( $this, "add_stickylist_tooltips"));

            // Add css
            add_action("wp_enqueue_scripts", array( $this, "register_plugin_styles"));

            // View or Edit entries
            add_filter("gform_pre_render", array($this,"pre_entry_action"));
            add_action("gform_post_submission", array($this, "post_edit_entry"), 10, 2);

            // Delete entries
            $this->maybe_delete_entry();
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
                if($position == -1){
                    ?>
                    <li class="list_setting">
                        Sticky List
                        <br>
                        <input type="checkbox" id="field_list_value" onclick="SetFieldProperty('stickylistField', this.checked);" /><label class="inline" for="field_list_value">Show in list <?php gform_tooltip("form_field_list_value") ?></label>
                        <br>
                        <label class="inline" for="field_list_text_value">Column label <?php gform_tooltip("form_field_text_value") ?></label><br><input class="fieldwidth-3" type="text" id="field_list_text_value" onkeyup="SetFieldProperty('stickylistFieldLabel', this.value);" />  
                    </li>
                    
                    <?php
                }
            }
        }

        
        /**
         * Sticky List field settings jquery function
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
         * Sticky List tooltips function
         *
         */   
        function add_stickylist_tooltips($tooltips){
           $tooltips["form_field_list_value"] = "<h6>Show field in list</h6>Check this box to show this field in the list.";
           $tooltips["form_field_text_value"] = "<h6>Header text</h6>Use this field to override the default text header.";
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

            // Get form settings
            $settings = $this->get_form_settings($form);
            if(isset($settings["enable_list"])) $enable_list = $settings["enable_list"]; else $enable_list = "";
            if(isset($settings["show_entries_to"])) $show_entries_to = $settings["show_entries_to"]; else  $show_entries_to = "";
            if(isset($settings["enable_view"])) $enable_view = $settings["enable_view"]; else $enable_view = "";
            if(isset($settings["enable_edit"])) $enable_edit = $settings["enable_edit"]; else $enable_edit = "";
            if(isset($settings["enable_delete"])) $enable_delete = $settings["enable_delete"]; else $enable_delete = "";
            if(isset($settings["action_column_header"])) $action_column_header = $settings["action_column_header"]; else $action_column_header = "";
            if(isset($settings["embedd_page"])) $embedd_page = $settings["embedd_page"]; else $embedd_page = "";
            
            // Only render list if Sticky List is enabled for this form
            if($enable_list){

                // Get current user
                $current_user = wp_get_current_user();
                $current_user_id = $current_user->ID;
                   
                // Get entries to show depending on settings
                // Show only to creator
                if($show_entries_to === "creator"){

                    $search_criteria["field_filters"][] = array("key" => "status", value => "active");
                    $search_criteria["field_filters"][] = array("key" => "created_by", value => $current_user_id);
                    $entries = GFAPI::get_entries($form_id, $search_criteria);
                
                // Show to all logged in users   
                }elseif($show_entries_to === "loggedin"){
                    
                    if(is_user_logged_in()) {
                        $search_criteria["field_filters"][] = array("key" => "status", value => "active");
                        $entries = GFAPI::get_entries($form_id, $search_criteria);
                    }
                
                // Show to everyone
                }else{
                
                    $search_criteria["field_filters"][] = array("key" => "status", value => "active");
                    $entries = GFAPI::get_entries($form_id, $search_criteria);
                }

                // If we have some entries, lets loop trough them and start building the output html
                if($entries) {
                    
                    // This vaiable will hold all html for the form                
                    $list_html = "<table class='sticky-list'><tr>";
                    
                    // Get all fields
                    $fields = $form["fields"];

                    // Make table header
                    foreach ($fields as $field) {

                        if($field["stickylistField"]) {

                            // If we have a custom field label we use that, if not we use the fields standard label
                            if($field["stickylistFieldLabel"]) {                            
                                $label = $field["stickylistFieldLabel"];                                
                            }else{
                                $label = $field["label"];
                            }
                            
                            $list_html .= "<th>$label</th>";
                        }
                    }

                    // If view, edit or delete is enabled we need an extra column
                    if($enable_view || $enable_edit || $enable_delete) {

                        $list_html .= "<th>$action_column_header</th>";
                    }

                    $list_html .= "</tr>";

                    // Make table rows
                    foreach ($entries as $entry) {
                        
                        $entry_id = $entry["id"];

                        $list_html .= "<tr>";

                        // Loop trough all the fields
                        foreach( $form['fields'] as $field ) {

                            // If the field is active 
                            if ($field['stickylistField']) {
                                
                                // ...we get the value for it
                                $field_value = RGFormsModel::get_lead_field_value( $entry, $field );

                                // If the value is an array (i.e. address field, name field, etc)
                                if(is_array($field_value)) {

                                    $field_values = "";

                                    // Concatenate field values into string separated by a space
                                    foreach ($field_value as $field => $value) {
                                        $field_values .= $value . " ";

                                    }
                                    $list_html .= "<td>$field_values</td>";

                                }else{ 
                                    $list_html .= "<td>$field_value</td>";
                                }
                            }
                        }

                        // If view, edit or delete is enabled we need a cell with appropiate links
                        if($enable_view || $enable_edit || $enable_delete){
                            
                            $list_html .= "<td>";

                                if($enable_view) {
                                    $list_html .= "
                                        <form action='$embedd_page' method='post'>
                                            <button class='submit'>View</button>
                                            <input type='hidden' name='mode' value='view'>
                                            <input type='hidden' name='view_id' value='$entry_id'>
                                        </form>";
                                }

                                // Only show edit link if current user is the creator 
                                if($enable_edit && $entry["created_by"] == $current_user->ID) {
                                    $list_html .= "
                                        <form action='$embedd_page' method='post'>
                                            <button class='submit'>Edit</button>
                                            <input type='hidden' name='mode' value='edit'>
                                            <input type='hidden' name='edit_id' value='$entry_id'>
                                        </form>";
                                }
                                // Only show delete link if current user is the creator
                                if($enable_delete && $entry["created_by"] == $current_user->ID) {
                                    $list_html .= "
                                        <form action='$embedd_page' method='post'>
                                            <button class='submit'>Delete</button>
                                            <input type='hidden' name='mode' value='delete'>
                                            <input type='hidden' name='delete_id' value='$entry_id'>
                                        </form>";
                                }

                            $list_html .= "</td>";
                        }

                        $list_html .= "</tr>";
                    }

                    

                    $list_html .= "</table>";
                
                // If we dont have any entries, show the "Empty list text" to the user
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
            wp_register_style( 'stickylist', plugins_url( 'sticky-list/css/sticky-list_styles.css' ) );
            wp_enqueue_style( 'stickylist' );
        }


        /**
         *  Editing entries
         *
         */ 
        public function post_edit_entry($entry, $form) {
            
            // Fi we are in edit mode
            if($_POST["action"] == "edit") {

                // Get original entry id
                $original_entry_id = $_POST["original_entry_id"];

                // Get current user
                $current_user = wp_get_current_user();
                
                // Get original entry
                $original_entry =  GFAPI::get_entry($original_entry_id);

                // If we have an original entry that is active and created by the current user
                if($original_entry && $original_entry["created_by"] == $current_user->ID && $original_entry["status"] == "active") {

                    // Keep starred and red staus
                    $entry["is_read"] = $original_entry["is_read"];
                    $entry["is_starred"] = $original_entry["is_starred"];

                    // Uppdate original entry with new fields
                    $success_uppdate = GFAPI::update_entry($entry, $original_entry_id);
                    
                    // Delete newly created entry
                    if($success_uppdate) $success_delete = GFAPI::delete_entry($entry["id"]);
                }
            }
        }


        /**
         * Performs actions when entrys are clicked in the list
         *
         */
        public function pre_entry_action($form) {
            
            if( ($_POST["mode"] == "edit" && $_POST["edit_id"]) || ($_POST["mode"] == "view" && $_POST["view_id"]) ) {

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
               
                // If we have an entry that is active and created by the current user
                if(!is_wp_error($form_fields) && $form_fields["created_by"] == $current_user->ID && $form_fields["status"] == "active") {
                    
                    // Loop trough all the fields
                    foreach ($form_fields as $key => &$value) {

                        // If the key is numeric we need to change it from [X.X] to [input_X_X]
                        if (is_numeric($key)) {

                            $new_key = str_replace(".", "_", "input_$key");

                            $form_fields[$new_key] = $form_fields[$key];
                            unset($form_fields[$key]);                                                           
                        }
                    }
                    
                    // Add is_submit_id field
                    $form_id = $form['id'];
                    $form_fields["is_submit_$form_id"] = "1";

                    // If we are in edit mode we insert two hidden fields with entry id and mode = edit
                    if($_POST["mode"] == "edit") { ?>

                        <script>
                        jQuery(document).ready(function($) {
                            var thisForm = $('#gform_<?php echo $form_id;?>')
                            thisForm.append('<input type="hidden" name="action" value="edit" />');
                            thisForm.append('<input type="hidden" name="original_entry_id" value="<?php echo $edit_id; ?>" />');
                            $("#gform_submit_button_<?php echo $form_id;?>").val('Update');
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

            return $form;
        }


        /**
         * Delete entries
         * This is very basic and will be improved in future versions
         */
        public function maybe_delete_entry() {
            
            if($_POST["mode"] == "delete" && $_POST["delete_id"]) {

                $delete_id = $_POST["delete_id"];                
                $current_user = wp_get_current_user();
                $entry = GFAPI::get_entry($delete_id);
                
                if(!is_wp_error($entry) && $entry["created_by"] == $current_user->ID) {
                    $success = GFAPI::delete_entry($delete_id);
                }
            }
        }


        /**
         * This is the page for info and tutorials
         *
         */
        public function plugin_page() {
            ?>
            Wellcome to the Sticky List beta. This page will contain info, usage instructions and more!
        <?php
        }


        /**
         * Form settings page
         *
         */
        public function form_settings_fields($form) {

            // Build an array of all post to allow for selection in "embedd page" dropdown
            $args = array( 'post_type' => 'any','post_status' => 'any'); 
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
                    "title"  => "Sticky List Settings",
                    "fields" => array(
                        array(
                            "label"   => "Enable for this form",
                            "type"    => "checkbox",
                            "name"    => "enable_list",
                            "tooltip" => "Check this box to enable Sticky List for this form",
                            "choices" => array(
                                array(
                                    "label" => "",
                                    "name"  => "enable_list"
                                )
                            )
                        ),
                        array(
                            "label"   => "Show entries in list to",
                            "type"    => "select",
                            "name"    => "show_entries_to",
                            "tooltip" => "Who should be able to se the entries in the list?",
                            "choices" => array(
                                array(
                                    "label" => "Entry creator",
                                    "value" => "creator"
                                ),
                                array(
                                    "label" => "All logged in users",
                                    "value" => "loggedin"
                                ),
                                array(
                                    "label" => "Everyone",
                                    "value" => "everyone"
                                )
                            )
                        ),
                        array(
                            "label"   => "Embedd page/post",
                            "type"    => "select",
                            "name"    => "embedd_page",
                            "tooltip" => "The page/post where the form is embedded. This page will be used to view/edit the entry",
                            "choices" => $posts_array
                        ),
                        array(
                            "label"   => "View entries",
                            "type"    => "checkbox",
                            "name"    => "enable_view",
                            "tooltip" => "Check this box to enable users to view the complete submitted entry. A \"View\" link will appear in the list",
                            "choices" => array(
                                array(
                                    "label" => "Enabled",
                                    "name"  => "enable_view"
                                )
                            )
                        ),
                        array(
                            "label"   => "Edit entries",
                            "type"    => "checkbox",
                            "name"    => "enable_edit",
                            "tooltip" => "Check this box to enable user to edit submitted entries. An \"Edit\" link will appear in the list",
                            "choices" => array(
                                array(
                                    "label" => "Enabled",
                                    "name"  => "enable_edit"
                                )
                            )
                        ),
                        array(
                            "label"   => "Delete entries",
                            "type"    => "checkbox",
                            "name"    => "enable_delete",
                            "tooltip" => "Check this box to enable user to delete submitted entries. A \"Delete\" link will appear in the list",
                            "choices" => array(
                                array(
                                    "label" => "Enabled",
                                    "name"  => "enable_delete"
                                )
                            )
                        ),
                        array(
                            "label"   => "Action column header",
                            "type"    => "text",
                            "name"    => "action_column_header",
                            "tooltip" => "Text to show as header for the action column",
                            "class"   => "medium"
                            
                        ),
                        array(
                            "label"   => "Empty list text",
                            "type"    => "text",
                            "name"    => "empty_list_text",
                            "tooltip" => "Text that is shown if the list is empty",
                            "class"   => "medium"  
                        )
                    )
                )
            );
        }
    }

    new EditEntries();
}
