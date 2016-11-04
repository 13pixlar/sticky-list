<?php

/* 
Attention developers
There is a fully documented and commented version of this file on Github
https://github.com/13pixlar/sticky-list
*/

if (class_exists("GFForms")) {
    GFForms::include_addon_framework();

    class StickyList extends GFAddOn {

        protected $_version = "1.4.1";
        protected $_min_gravityforms_version = "1.8.19.2";
        protected $_slug = "sticky-list";
        protected $_path = "gravity-forms-sticky-list/sticky-list.php";
        protected $_full_path = __FILE__;
        protected $_title = "Gravity Forms Sticky List";
        protected $_short_title = "Sticky List";
        protected $_capabilities_form_settings = 'gravityforms_stickylist';
        protected $_capabilities_uninstall = 'gravityforms_stickylist_uninstall';
        protected $_capabilities = array('gravityforms_stickylist', 'gravityforms_stickylist_uninstall');

        public function init(){
            parent::init();

            
            $this->stickylist_localize();
            
            
            add_action("gform_field_standard_settings", array( $this, "stickylist_field_settings"), 10, 2);

            
            add_shortcode( 'stickylist', array( $this, 'stickylist_shortcode' ) );

            
            add_action("gform_editor_js", array($this, "editor_script"));

            
            add_filter("gform_tooltips", array( $this, "add_stickylist_tooltips"));

            
            add_action("wp_enqueue_scripts", array( $this, "register_plugin_styles"), 5);

            
            add_filter("gform_pre_render", array($this,"pre_entry_action"));
            add_action("gform_after_submission", array($this, "post_edit_entry"), 10, 2);

            
            $this->maybe_delete_entry();

            
            add_action("gform_notification_ui_settings", array($this, "stickylist_gform_notification_ui_settings"), 10, 3 );
            add_action("gform_pre_notification_save", array($this, "stickylist_gform_pre_notification_save"), 10, 2 );
            add_filter("gform_disable_notification", array($this, "stickylist_gform_disable_notification" ), 10, 4 );
            add_filter("gform_notification", array($this, "stickylist_modify_notification" ), 10, 3 );

            
            add_action("gform_confirmation_ui_settings", array($this, "stickylist_gform_confirmation_ui_settings"), 10, 3 );
            add_action("gform_pre_confirmation_save", array($this, "stickylist_gform_pre_confirmation_save"), 10, 2 );
            add_filter("gform_confirmation", array($this, "stickylist_gform_confirmation"), 10, 4);

            
            add_filter("gform_post_data", array( $this, "stickylist_gform_post_data" ), 10, 3 );

            
            add_filter('gform_validation', array( $this, "stickylist_validate_fileupload" ) );
        }

        
        private static $_instance = null;
		public static function get_instance() {
			if ( self::$_instance == null ) {
				self::$_instance = new StickyList();
			}
			return self::$_instance;
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

            
            $form = GFAPI::get_form($form_id);

            
            $settings = $this->get_form_settings($form);
                         
            
            if(isset($settings["enable_list"]) && true == $settings["enable_list"]){
                
                
                if($position == -1){ ?>
                    
                    <li class="list_setting">
                        Sticky List
                        <br>
                        <input type="checkbox" id="field_list_value" onclick="SetFieldProperty('stickylistField', this.checked);" /><label class="inline" for="field_list_value"><?php _e('Show in list', 'sticky-list'); ?> <?php gform_tooltip("form_field_list_value") ?></label>
                        <br>
                        <input type="checkbox" id="field_nowrap_value" onclick="SetFieldProperty('stickylistFieldNoWrap', this.checked);" /><label class="inline" for="field_nowrap_value"><?php _e('Dont wrap text from this field', 'sticky-list'); ?> <?php gform_tooltip("form_field_nowrap_value") ?></label>
                        <br>
                        <label class="inline" for="field_list_text_value"><?php _e('Column label', 'sticky-list'); ?> <?php gform_tooltip("form_field_text_value") ?></label><br><input class="fieldwidth-3" type="text" id="field_list_text_value" onkeyup="SetFieldProperty('stickylistFieldLabel', this.value);" />  
                    </li>
                    
                    <?php
                }
            }
        }


        /**
         * Format date field according to user preference
         *
         */
        function format_the_date($timestamp,$format) {
            switch ( $format ) {
                case 'mdy' :
                    $format_name = 'm/d/Y';
                    break;
                case 'dmy' :
                    $format_name = 'd/m/Y';
                    break;
                case 'dmy_dash' :
                    $format_name = 'd-m-Y';
                    break;
                case 'dmy_dot' :
                    $format_name = 'd.m.Y';
                    break;
                case 'ymd_slash' :
                    $format_name = 'Y/m/d';
                    break;
                case 'ymd_dash' :
                    $format_name = 'Y-m-d';
                    break;
                case 'ymd_dot' :
                    $format_name = 'Y.m.d';
                    break;
                case '' :
                    $format_name = 'Y-m-d';
                    break;
            }
            return date($format_name, strtotime($timestamp));
        }

        
        /**
         * Sticky List field settings JQuery function
         *
         */
        function editor_script(){
            ?>
            <script type='text/javascript'>
                
                jQuery(document).bind("gform_load_field_settings", function(event, field, form){
                    jQuery("#field_list_value").attr("checked", field["stickylistField"] == true);
                    jQuery("#field_nowrap_value").attr("checked", field["stickylistFieldNoWrap"] == true);
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
           $tooltips["form_field_nowrap_value"] = __('<h6>Dont wrap whitespace</h6>Check this box to prevent wraping of text from this field','sticky-list');
           $tooltips["form_field_text_value"] = __('<h6>Header text</h6>Use this field to override the default text header.','sticky-list');
           return $tooltips;
        }
        

        /**
         * Helper function to get current user
         *
         */
        public function stickylist_get_current_user() {
            $current_user = wp_get_current_user();
            $current_user_id = $current_user->ID;

            
            if( $current_user_id == NULL && function_exists("bp_loggedin_user_id") ) {
                $current_user_id = bp_loggedin_user_id();
            }
            return $current_user_id;
        }


        /**
         * Helper function to get form settings
         *
         */
        function get_sticky_setting($setting_key, $settings) {
            if(isset($settings[$setting_key])) {
                $setting = $settings[$setting_key];
            }else{
                $setting = "";
            }
            return $setting;
        }

      
        /**
         * Sticky List shortcode function
         *
         */
        function stickylist_shortcode( $atts ) {
            $shortcode_id = shortcode_atts( array(
                'id'            => '1',
                'user'          => '',
                'showto'        => '',
                'field'         => '',
                'value'         => '',
                'test'          => ''
            ), $atts );

            
            $form_id = $shortcode_id['id'];

            
            $user_id = $shortcode_id['user'];

            
            $showto = $shortcode_id['showto'];

            
            $filterField = $shortcode_id['field'];

            
            $filterValue = $shortcode_id['value'];

            
            $form = GFAPI::get_form($form_id);

            
            $settings = $this->get_form_settings($form);

            
            $enable_list            = $this->get_sticky_setting("enable_list", $settings);
            $show_entries_to        = $this->get_sticky_setting("show_entries_to", $settings);
            $max_entries            = $this->get_sticky_setting("max_entries", $settings);
            $enable_clickable       = $this->get_sticky_setting("enable_clickable", $settings);
            $enable_postlink        = $this->get_sticky_setting("enable_postlink", $settings);
            $link_label             = $this->get_sticky_setting("link_label", $settings);
			$enable_pdf             = $this->get_sticky_setting("enable_pdf", $settings);
            $enable_pdf_label       = $this->get_sticky_setting("enable_pdf_label", $settings);
			$enable_pdf_id          = $this->get_sticky_setting("enable_pdf_id", $settings);
            $enable_view            = $this->get_sticky_setting("enable_view", $settings);
            $enable_view_label      = $this->get_sticky_setting("enable_view_label", $settings);
            $enable_edit            = $this->get_sticky_setting("enable_edit", $settings);
            $enable_edit_label      = $this->get_sticky_setting("enable_edit_label", $settings);
            $new_entry_id           = $this->get_sticky_setting("new_entry_id", $settings);
            $enable_delete          = $this->get_sticky_setting("enable_delete", $settings);
            $enable_delete_label    = $this->get_sticky_setting("enable_delete_label", $settings);
            $confirm_delete         = $this->get_sticky_setting("confirm_delete", $settings);
            $confirm_delete_text    = $this->get_sticky_setting("confirm_delete_text", $settings);
            $enable_duplicate       = $this->get_sticky_setting("enable_duplicate", $settings);
            $enable_duplicate_label = $this->get_sticky_setting("enable_duplicate_label", $settings);
            $action_column_header   = $this->get_sticky_setting("action_column_header", $settings);
            $enable_sort            = $this->get_sticky_setting("enable_sort", $settings);
            $initial_sort           = $this->get_sticky_setting("initial_sort", $settings);
            $initial_sort_direction = $this->get_sticky_setting("initial_sort_direction", $settings);
            $enable_search          = $this->get_sticky_setting("enable_search", $settings);
            $embedd_page            = $this->get_sticky_setting("embedd_page", $settings);
            $enable_pagination      = $this->get_sticky_setting("enable_pagination", $settings);
            $page_entries           = $this->get_sticky_setting("page_entries", $settings);

            
            if(isset($settings["custom_embedd_page"]) && $settings["custom_embedd_page"] != "") $embedd_page = $settings["custom_embedd_page"];

            
            if(isset($showto) && $showto != "") $show_entries_to = $showto;
            
            
            if($enable_list){

                
                if($user_id != "") {
                    $current_user_id = $user_id;
                }else{
                    $current_user_id = $this->stickylist_get_current_user();
                }

                //Set max nr of entries to be shown
                if($max_entries == "") { $max_entries = 999999; }

                
                if($enable_sort && $initial_sort) {
                    if($initial_sort == "date_added") {
                        $sorting = array();
                    }else{
                        $sorting = array('key' => $initial_sort, 'direction' => $initial_sort_direction );
                    }
                }else{
                    $sorting = array();
                }
                
                
                $paging = array('offset' => 0, 'page_size' => $max_entries );
                   
                
                
                if($show_entries_to === "creator"){

                    $search_criteria["field_filters"][] = array("key" => "status", "value" => "active");
                    $search_criteria["field_filters"][] = array("key" => "created_by", "value" => $current_user_id);

                    $entries = GFAPI::get_entries($form_id, $search_criteria, $sorting, $paging);
                
                
                }elseif($show_entries_to === "loggedin"){
                    
                    if(is_user_logged_in()) {
                        $search_criteria["field_filters"][] = array("key" => "status", "value" => "active");
                        $entries = GFAPI::get_entries($form_id, $search_criteria, $sorting, $paging);
                    }
                
                
                }elseif($show_entries_to === "everyone"){
                
                    $search_criteria["field_filters"][] = array("key" => "status", "value" => "active");
                    $entries = GFAPI::get_entries($form_id, $search_criteria, $sorting, $paging);
                
                
                }else{
                    $user = wp_get_current_user();
                    
                    if( in_array( $show_entries_to, (array) $user->roles ) || in_array( "administrator", (array) $user->roles )) {
                        $search_criteria["field_filters"][] = array("key" => "status", "value" => "active");
                        $entries = GFAPI::get_entries($form_id, $search_criteria, $sorting, $paging);
                    }
                }

                
                if(!empty($entries) && $filterField) {
                     foreach ($entries as $id => $data) {
                        if($data[$filterField] != $filterValue) {
                            unset($entries[$id]);
                        }
                     }
                }

                
                if(!empty($entries)) {
                    $entries = apply_filters( 'filter_entries', $entries );
                }

                
                if(!empty($entries)) {

                    
                    if($initial_sort == "date_added" && $initial_sort_direction == "ASC") {
                        $entries = array_reverse($entries);
                    }
                    
                    
                    $list_html = "<div id='sticky-list-wrapper_$form_id' class='sticky-list-wrapper'>";
                    
                    
                    if($enable_sort && $enable_search) {
                        $list_html .= "<input class='search' placeholder='" . __("Search", "sticky-list") . "' />";
                    }

                    $list_html .= "<table class='sticky-list'><thead><tr>";
                    
                    
                    $fields = $form["fields"];

                    
                    $i = 0;

                    
                    foreach ($fields as $field) {

                        if(isset($field["stickylistField"]) && $field["stickylistField"] != "") {

                            
                            if(isset($field["stickylistFieldLabel"]) && $field["stickylistFieldLabel"] != "") {                            
                                $label = $field["stickylistFieldLabel"];                                
                            }else{
                                $label = $field["label"];
                            }

                            $class_label = "header-" . str_replace(" ", "-", strtolower($label));
                            
                            $list_html .= "<th class='sort $class_label' data-sort='sort-$i'>$label</th>";

                            
                            $i++;
                        }
                    }

                    
                    if($enable_pdf || $enable_view || $enable_edit || $enable_delete || $enable_postlink || $enable_duplicate) {

                        $list_html .= "<th class='sticky-action'>$action_column_header</th>";
                    }

                    $list_html .= "</tr></thead><tbody class='list'>";

                    
                    foreach ($entries as $entry) {
                        
                        $entry_id = $entry["id"];

                        $list_html .= "<tr>";

                        
                        $i=0;

                        
                        foreach( $form["fields"] as $field ) {

                            
                            if (isset($field["stickylistField"]) && $field["stickylistField"] != "") {
                                
                                
                                $field_value = RGFormsModel::get_lead_field_value( $entry, $field );

                                
                                $tdClass = "stickylist-" . $field["type"];

                                
                                $nowrap = "";
                                if(isset($field["stickylistFieldNoWrap"]) && $field["stickylistFieldNoWrap"] != "") {
                                    $nowrap = " sticky-nowrap";
                                }

                                
                                if($field["type"] == "post_custom_field" && $field["inputType"] == "fileupload") { $custom_file_upload = true; }else{ $custom_file_upload = false; }

                                
                                if ($field["type"] == "product" || $field["type"] == "shipping" || $field["type"] == "option") {
                                    
                                    
                                    if(is_array($field_value)) {

                                        
                                        if($field["type"] == "option") {

                                            
                                            foreach ($field_value as &$option) {
                                                $option = substr($option, 0, strpos($option, "|"));
                                            }

                                            
                                            $field_value = array_filter($field_value);
                                            $field_value = implode(", ", $field_value);
                                        
                                        }else{

                                            
                                            $field_value = end($field_value); 
                                        }
                                        
                                        $list_html .= "<td class='sort-$i $nowrap $tdClass'>$field_value</td>";

                                    }else{

                                        
                                        $field_value = substr($field_value, 0, strpos($field_value, "|"));
                                        $list_html .= "<td class='sort-$i $nowrap $tdClass'>$field_value</td>";
                                    }
                                }

                                
                                elseif(is_array($field_value)) {

                                    
                                    ksort($field_value);
                                    $field_values = "";

                                    
                                    foreach ($field_value as $field => $value) {
                                        $field_values .= $value . " ";
                                    }
                                    $list_html .= "<td class='sort-$i $nowrap $tdClass'>$field_values</td>";
                                }

                                
                                elseif ($field["type"] == "fileupload" || $field["type"] == "post_image" || $custom_file_upload == true ) {

                                    $field_value = strtok($field_value, "|");
                                    $file_name = basename($field_value);

                                    
                                    if($enable_clickable && $field_value != "") {
                                        $list_html .= "<td class='sort-$i $nowrap $tdClass'><a href='$field_value'>$file_name</a></td>";
                                    }else{
                                        $list_html .= "<td class='sort-$i $nowrap $tdClass'>$file_name</td>";
                                    }
                                }

                                
                                elseif ($field["type"] == "date" && $field_value != "") {
                                    $field_value = $this->format_the_date($field_value,$field["dateFormat"]);
                                    $list_html .= "<td class='sort-$i $nowrap $tdClass'>$field_value</td>";
                                }

                                
                                elseif ($field["type"] == "website" && $field_value != "") {
                                    $tdClass = "stickylist-url";
                                    $list_html .= "<td class='sort-$i $nowrap $tdClass'><a href='$field_value'>$field_value</a></td>";
                                }

                                
                                elseif ($field["type"] == "post_category" && $field_value != "") {
                                    $tdClass = "stickylist-category";
                                    $field_value = strtok($field_value, ":");
                                    $list_html .= "<td class='sort-$i $nowrap $tdClass'>$field_value</td>";
                                }

                                
                                elseif ($field["type"] == "list" && $field_value != "") {
                                    if(is_array(maybe_unserialize($field_value))) {
                                        $list = maybe_unserialize($field_value);
                                        $field_value = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($list)), FALSE);
                                        $field_value = implode(", ", $field_value);
                                        $list_html .= "<td class='sort-$i $nowrap $tdClass'>$field_value</td>";
                                    }
                                }

                                
                                else{ 
                                    $list_html .= "<td class='sort-$i $nowrap $tdClass'>$field_value</td>";
                                }

                                
                                $i++;
                            }
                        }

                        
                        if($enable_pdf || $enable_view || $enable_edit || $enable_delete || $enable_postlink || $enable_duplicate){
                            
                            $list_html .= "<td class='sticky-action'>";

                                
                                if($enable_pdf) {
									$list_html .= "<a href='". get_bloginfo('url') ."/pdf/$enable_pdf_id/$entry_id/' target='_blank'><button type='button' class='sticky-list-view submit'>$enable_pdf_label</button></a>";
								}

                                if($enable_view) {
                                    $list_html .= "
                                        <form action='$embedd_page' method='post'>
                                            <button class='sticky-list-view submit'>$enable_view_label</button>
                                            <input type='hidden' name='mode' value='view'>
                                            <input type='hidden' name='view_id' value='$entry_id'>
                                        </form>";
                                }

                                
                                if($enable_edit) {

                                    
                                    if($entry["created_by"] == $this->stickylist_get_current_user() || current_user_can('edit_others_posts') || current_user_can('stickylist_edit_entries')) {
                                        $list_html .= "
                                            <form action='$embedd_page' method='post'>
                                                <button class='sticky-list-edit submit'>$enable_edit_label</button>
                                                <input type='hidden' name='mode' value='edit'>
                                                <input type='hidden' name='edit_id' value='$entry_id'>
                                            </form>";
                                    }
                                }

                                
                                if($enable_delete) {

                                    
                                    if($entry["created_by"] == $this->stickylist_get_current_user() || current_user_can('delete_others_posts') || current_user_can('stickylist_delete_entries')) {
                                        
                                        $list_html .= "
                                            <button class='sticky-list-delete submit'>$enable_delete_label</button>
                                            <input type='hidden' name='delete_id' class='sticky-list-delete-id' value='$entry_id'>
                                        ";

                                        
                                        if($entry["post_id"] != null ) {
                                            $delete_post_id = $entry["post_id"];
                                            $list_html .= "<input type='hidden' name='delete_post_id' class='sticky-list-delete-post-id' value='$delete_post_id'>";
                                        }
                                    }
                                }

                                
                                if($enable_postlink && $entry["post_id"] != NULL) {

                                    $permalink = get_permalink($entry["post_id"]);
                                    $list_html .= "<button class='sticky-list-postlink submit' onclick='document.location.href=\"$permalink\"'>$link_label</button>";
                                }

                                
                                if($enable_duplicate) {

                                    $list_html .= "
                                        <form action='$embedd_page' method='post'>
                                            <button class='sticky-list-duplicate submit'>$enable_duplicate_label</button>
                                            <input type='hidden' name='mode' value='duplicate'>
                                            <input type='hidden' name='duplicate_id' value='$entry_id'>
                                        </form>";
                                }

                            $list_html .= "</td>";
                        }

                        $list_html .= "</tr>";
                    }

                    $list_html .= "</tbody></table>";

                    
                    if($enable_pagination && $page_entries < count($entries)) {
                        $list_html .= "<ul class='pagination'></ul>";
                    }

                    $list_html .= "</div>";


                    
                    if($enable_sort || $enable_pagination) {

                        
                        $sort_fileds = "";
                        for ($a=0; $a<$i; $a++) { 
                            $sort_fileds .= "'sort-$a',"; 
                        }

                        
                        $list_html .= "<script src='" . plugins_url( 'gravity-forms-sticky-list/js/list.min.js' ) . "'></script>";
                        
                        
                        if($enable_pagination) {
                            $list_html .= "<script src='" . plugins_url( 'gravity-forms-sticky-list/js/list.pagination.min.js' ) . "'></script>";
                        }

                        
                        if($enable_sort && $enable_pagination) {
                            $list_html .= "<script>var options = { valueNames: [$sort_fileds], page: $page_entries, plugins: [ ListPagination({ outerWindow: 1 }) ] };var userList = new List('sticky-list-wrapper_$form_id', options); function callback() { window.listUpdated() } userList.on('updated', callback);</script><style>table.sticky-list th:not(.sticky-action) {cursor: pointer;}</style>";
                        
                        
                        }elseif($enable_sort && !$enable_pagination) {
                            $list_html .= "<script>var options = { valueNames: [$sort_fileds] };var userList = new List('sticky-list-wrapper_$form_id', options);</script><style>table.sticky-list th:not(.sticky-action) {cursor: pointer;}</style>";
                        
                        
                        }elseif(!$enable_sort && $enable_pagination) {                 
                            $list_html .= "<script>var options = { valueNames: ['xxx'], page: $page_entries, plugins: [ ListPagination({ outerWindow: 1 }) ] };var userList = new List('sticky-list-wrapper_$form_id', options); function callback() { window.listUpdated() } userList.on('updated', callback);</script></style>";
                        }
                    }

                    
                    if($enable_delete) {

                        
                        $ajax_delete = plugin_dir_url( __FILE__ ) . 'ajax-delete.php';
                        $ajax_spinner = plugin_dir_url( __FILE__ ) . 'img/ajax-spinner.gif';
                        $delete_failed = __('Delete failed','sticky-list');

                        $list_html .= "
                            <img src='$ajax_spinner' style='display: none;'>
                            <script>
                            jQuery(document).ready(function($) {

                                window.listUpdated = function(){

                                    $('#sticky-list-wrapper_$form_id .sticky-list-delete').click(function(event) {

                                        event.stopImmediatePropagation()
                                    
                                        var delete_id       = $(this).siblings('.sticky-list-delete-id').val();
                                        var delete_post_id  = $(this).siblings('.sticky-list-delete-post-id').val();
                                        var current_button  = $(this);
                                        var current_row     = current_button.parent().parent();
                                        var confirm_delete  = $confirm_delete;
                                        
                                        if(confirm_delete == 1) {
                                            var confirm_dialog = confirm('$confirm_delete_text');
                                        }                         

                                        if (confirm_dialog == true || confirm_delete != 1) {

                                            current_button.html('<img src=\'$ajax_spinner\'>');
                                            
                                            $.post( '', { mode: 'delete', delete_id: delete_id, delete_post_id: delete_post_id, form_id: '$form_id' })
                                            .done(function() {
                                                current_button.html('');
                                                current_row.css({   
                                                    background: '#fbdcdc',
                                                    color: '#fff'
                                                });
                                                current_row.hide('slow', function() {
                                                    current_row.remove();
                                                    remaining_rows = $('#sticky-list-wrapper_$form_id tbody tr');
                                                    if(remaining_rows.length === 0) {
                                                        $('#sticky-list-wrapper_$form_id table').html('" . $settings["empty_list_text"] . "');
                                                    }
                                                });
                                            })
                                            .fail(function() {
                                                current_button.html('$delete_failed');
                                            })
                                        }
                                    });   
                                }

                                window.listUpdated();
                            });
                            </script>
                        ";
                    }
                
                
                }else{
                    $list_html = $settings["empty_list_text"] . "<br>";
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
         * Performs actions when entrys are clicked in the list
         *
         */
        public function pre_entry_action($form) {
            
            if( isset($_POST["mode"]) == "edit" || isset($_POST["mode"]) == "view" || isset($_POST["mode"]) == "duplicate") {

                if($_POST["mode"] == "edit") {
                    $edit_id = $_POST["edit_id"];
                    $form_fields = GFAPI::get_entry($edit_id);
                }

                if($_POST["mode"] == "view") {
                    $view_id = $_POST["view_id"];
                    $form_fields = GFAPI::get_entry($view_id);
                }

                if($_POST["mode"] == "duplicate") {
                    $duplicate_id = $_POST["duplicate_id"];
                    $form_fields = GFAPI::get_entry($duplicate_id);
                }
               
                
                if(!is_wp_error($form_fields) && $form_fields["status"] == "active") {
                    
                    
                    if($form_fields["created_by"] == $this->stickylist_get_current_user() || current_user_can('edit_others_posts') || current_user_can('stickylist_edit_entries') || $_POST["mode"] == "view" || $_POST["mode"] == "duplicate") {

                        
                        foreach ($form["fields"] as $fkey => &$fvalue) {
                            if($fvalue["type"] == 'fileupload' || $fvalue["type"] == "post_image") {
                                $uploads[] = $fvalue["id"];
                            }elseif ($fvalue["type"] == "post_custom_field" && $fvalue["inputType"] == "fileupload") {
                                $uploads[] = $fvalue["id"];
                            }elseif ($fvalue["type"] == 'post_category') {
                                $categories[] = $fvalue["id"];  
                            }
                        }
                        if (!isset($uploads)) $uploads = "";
                        if (!isset($categories)) $categories = "";

                        
                        $upload_inputs = "";
                     
                        
                        foreach ($form_fields as $key => &$value) {

                            
                            if (is_numeric($key)) {

                                
                                if(is_array(maybe_unserialize($value))) {
                                    $list = maybe_unserialize($value);
                                    $value = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($list)), FALSE);
                                }

                                
                                if (is_array($categories) && in_array( $key, $categories ) ) {
                                    $value = substr( $value, strpos( $value, ':') + 1);                              
                                }

                                
                                $new_key = str_replace(".", "_", "input_$key");
                                $form_fields[$new_key] = $form_fields[$key];

                                
                                if (is_array($uploads) && in_array( $key, $uploads ) ) {
                                    if ($value != "") {

                                        
                                        $path = strtok($value, "|");
                                        $file = basename($path);
                                        $delete_icon = plugin_dir_url( __FILE__ ) . 'img/delete.png';
                                        
                                        
                                        if ($_POST["mode"] == "edit") {
                                            $show_delete = " <a title=\"" . __("Remove","sticky-list") . "\" class=\"remove-entry\"><img alt=\"" . __("Remove","sticky-list") . "\" src=\"$delete_icon\"></a>";
                                        }else{
                                            $show_delete = "";
                                        }

                                        $upload_inputs .= "$('input[name=\"$new_key\"]').before('<div class=\"file_$key\"><a href=\"$path\">$file</a>$show_delete<input name=\"file_$key\" type=\"hidden\" value=\"$value\"></div>');";
                                    }
                                }

                                
                                unset($form_fields[$key]);                    
                            }
                        }
                        
                        
                        $form_id = $form['id'];

                        
                        if ($_POST["gform_submit"] == $form_id || isset($_POST["mode"])) {
                            $form_fields["is_submit_$form_id"] = "1";
                        }
                        
                        
                        $settings = $this->get_form_settings($form);

                        
                        if(isset($settings["update_text"])) $update_text = $settings["update_text"]; else $update_text = ""; ?>

                        <!-- Add JQuery to help with view/update/delete -->
                        <script>
                        jQuery(document).ready(function($) {
                            var thisForm = $('#gform_<?php echo $form_id;?>')

                <?php   
                        if($_POST["mode"] == "edit") { ?>

                            thisForm.append('<input type="hidden" name="action" value="edit" />');
                            thisForm.append('<input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>" />');
                            thisForm.append('<input type="hidden" name="mode" value="edit" />');
                            $("#gform_submit_button_<?php echo $form_id;?>").val('<?php echo $update_text; ?>');

                <?php   }

                        
                        if($_POST["mode"] == "view") { ?>

                            $("#gform_<?php echo $form_id;?> :input").attr("disabled", true);
                            $("#gform_submit_button_<?php echo $form_id;?>").css('display', 'none');
                <?php   }

                        
                        if($form_fields["post_id"] != null ) { ?>

                            thisForm.append('<input type="hidden" name="post_id" value="<?php echo $form_fields["post_id"];?>" />');
                <?php   } 

                        
                        if($upload_inputs != "") {
                            $upload_inputs .= "$('div[class^=\"file_\"] .remove-entry').click( function(event){ event.preventDefault; $(this).parent().remove();});";
                            echo $upload_inputs;
                        } ?>

                        });
                        </script>
                        <!-- End JQuery -->
                        
                <?php   
                        $_POST = array_merge($form_fields,$_POST);
                    }
                }
            }
            
            return $form;
        }


        /**
         *  Editing entries
         *
         */ 
        public function post_edit_entry($entry, $form) {
            
            
            if(isset($_POST["action"]) && $_POST["action"] == "edit") {

                
                $original_entry_id = $_POST["edit_id"];
                
                
                $original_entry =  GFAPI::get_entry($original_entry_id);

                
                if($original_entry && is_wp_error($original_entry) == false) {

                    
                    if($original_entry["status"] == "active") {

                        
                        if($original_entry["created_by"] == $this->stickylist_get_current_user() || current_user_can('edit_others_posts') || current_user_can('stickylist_edit_entries')) { 

                            
                            $entry["is_read"] = $original_entry["is_read"];
                            $entry["is_starred"] = $original_entry["is_starred"];
                            $entry["created_by"] = $original_entry["created_by"];

                            
                            foreach ($form["fields"] as $field) {
                                if($field["adminOnly"] == true) {
                                    $entry[$field["id"]] = $original_entry[$field["id"]];
                                }
                            }

                            
                            foreach ($_POST as $key => &$value) {
                                if (strpos($key, "file_") !== false) {
                                    $entry[str_replace("file_", "", $key)] = $value;
                                }     
                            }


                            
                            $new_entry_id = $this->get_sticky_setting("new_entry_id", $this->get_form_settings($form));
                            if (!$new_entry_id) {

                                
                                $success_uppdate = GFAPI::update_entry($entry, $original_entry_id);

                                
                                foreach ($entry as $key => &$value) {
                                    
                                    
                                    if ($key != "id") {
                                        $entry[$key] = "";
                                    }
                                }
                                
                                
                                if($success_uppdate) {
                                    $empty_the_entry = GFAPI::update_entry($entry, $entry["id"]);
                                    $success_delete = GFAPI::delete_entry($entry["id"]);
                                }

                            
                            }else{
                                $success_delete = GFAPI::delete_entry($original_entry_id);    
                            }
                        }
                    }
                }

                
                if(is_wp_error($original_entry)) {
                    $success_delete = GFAPI::delete_entry($entry["id"]);
                }
            }
        }


        /**
         * Validate required file input fields
         *
         */
        function stickylist_validate_fileupload($validation_result) {

            
            $form = $validation_result["form"];

            foreach($form['fields'] as &$field){

                

                if($field["type"] == "post_custom_field" && $field["inputType"] == "fileupload") { $custom_file_upload = true; }else{ $custom_file_upload = false; }
                if($field["type"] == 'fileupload' || $field["type"] == "post_image"|| $custom_file_upload == true) {
                    
                    
                    if(rgpost("file_{$field['id']}") != "") {
                        
                        
                        $field["isRequired"] = 0;                     
                        $field['failed_validation'] = false;

                        
                        $validation_result["is_valid"] = true;
                    }
                }
            }

            
            $validation_result['form'] = $form;

            
            foreach($form['fields'] as &$field) {
                if ($field['failed_validation'] == true) {
                    $validation_result["is_valid"] = false;
                    break;
                }
            }

            return $validation_result;
        }


        /**
         * Sticky List update Wordpress post
         *
         */
        function stickylist_gform_post_data( $post_data, $form, $entry ) {

            
            if (isset($_POST["post_id"])) {
                $post_id = $_POST["post_id"];
                $post_data['ID'] = $post_id;

                
                delete_post_meta($post_id, "_gform-entry-id");
                delete_post_meta($post_id, "_gform-form-id");
                $form_fields = $form["fields"];
                foreach ($form_fields as $form_field) {
                    if($form_field["type"] == "post_custom_field") {
                        delete_post_meta($post_id, $form_field["postCustomFieldName"]);
                    }
                }

                
                $this_post = get_post($post_id);
                $post_data["comment_status"] = $this_post->comment_status;
            }
            return ( $post_data );
        }


        /**
         * Delete entries
         * This function is used to delete entries with an ajax request
         * Could use better (or at least some) error handling
         */
        public function maybe_delete_entry() {
            
            
            if(isset($_POST["mode"]) && $_POST["mode"] == "delete" && isset($_POST["delete_id"]) && isset($_POST["form_id"])) {

                
                $form_id = $_POST["form_id"];

                
                $form = GFAPI::get_form($form_id);

                
                $settings = $this->get_form_settings($form);
                $enable_delete = $settings["enable_delete"];
                $delete_type = $settings["delete_type"];

                
                if($enable_delete) {

                    $delete_id = $_POST["delete_id"];                
                    $entry = GFAPI::get_entry($delete_id);
                    
                    
                    if(!is_wp_error($entry)) {

                        
                        if($entry["created_by"] == $this->stickylist_get_current_user() || current_user_can('delete_others_posts') || current_user_can('stickylist_delete_entries')) {

                            
                            if($_POST["delete_post_id"] != null) {
                                $delete_post_id = $_POST["delete_post_id"];
                            }else{
                                $delete_post_id = "";
                            }
                           
                            
                            if($delete_type == "trash") { 
                                $entry["status"] = "trash";
                                $success = GFAPI::update_entry($entry, $delete_id);

                                
                                if($delete_post_id != "") {
                                    wp_delete_post( $delete_post_id, false );
                                }
                            }

                            
                            if($delete_type == "permanent") {
                                $success = GFAPI::delete_entry($delete_id);

                                
                                if($delete_post_id != "") {
                                     wp_delete_post( $delete_post_id, true );
                                }
                            }

                            
                            if($success) {

                                
                                $notifications = $form["notifications"];
                                $notification_ids = array();
                                
                                
                                foreach ($notifications as $notification) {

                                    
                                    $notification_type = $notification["stickylist_notification_type"];

                                    
                                    if($notification_type == "delete" || $notification_type == "all") {
                                        $id = $notification["id"];
                                        array_push($notification_ids, $id);        
                                    }
                                }
                                
                                
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
            
            jQuery(document).ready(function($) { 
                $('#gaddon-setting-row-header-0 h4').html('<?php _e("General settings","sticky-list"); ?>')
                $('#gaddon-setting-row-header-1 h4').html('<?php _e("PDF, view, edit, delete & duplicate buttons","sticky-list"); ?>')
                $('#gaddon-setting-row-header-2 h4').html('<?php _e("Labels","sticky-list"); ?>')
                $('#gaddon-setting-row-header-3 h4').html('<?php _e("Sort & search","sticky-list"); ?>')
                $('#gaddon-setting-row-header-4 h4').html('<?php _e("Pagination","sticky-list"); ?>')
                $('#gaddon-setting-row-header-5 h4').html('<?php _e("Donate","sticky-list"); ?>')
                $('#gaddon-setting-row-donate .donate-text').html('<?php _e("Sticky List is completely free. But if you like, you can always <a target=\"_blank\" href=\"https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8R393YVXREFN6\">donate</a> a few bucks.","sticky-list"); ?>')
             });
            </script>
            <?php

            
            $args = array( 'posts_per_page' => 1001, 'post_type' => 'any', 'post_status' => 'any', 'orderby' => 'date', 'order' => 'ASC'); 
            $posts = get_posts( $args );
            $posts_array = array();
            foreach ($posts as $post) {
                $post_title = get_the_title($post->ID);
                $post_url = get_permalink($post->ID);

                
                if($post->post_type != 'attachment') {
                    $posts_array = array_merge(
                        array(
                            array(
                                "label" => $post_title,
                                "value" => $post_url
                            )
                        ),$posts_array
                    );
                }
            }

            if(count($posts_array) > 1000) {
                $embedd_post_page_label = __('Embedd page/post','sticky-list') . "<br><small>" . __('<strong>NOTE</strong>: Only the latest 1000 items are shown. Use "custom url" below if your desired post is not listed.','sticky-list') . "</small>";
            }else {
                $embedd_post_page_label = __('Embedd page/post','sticky-list');
            }

            
            $fields_array = array();

            
            $fields_array = array_merge(
                array(
                    array(
                        "label" => __('Date added','sticky-list'),
                        "value" => "date_added"
                    )
                ),$fields_array
            );
            foreach ($form["fields"] as $key => $value) {

                
                if($value["label"] == "") {
                    $label = __('Field ','sticky-list') . $value["id"];
                }else{
                    $label = $value["label"];
                }
                $fields_array = array_merge(
                    array(
                        array(
                            "label" => $label,
                            "value" => $value["id"]
                        )
                    ),$fields_array
                );
            }
            $fields_array = array_reverse($fields_array);


            
            $roles_array = array();
            $roles_array = array_merge(
                array(
                    array(
                        "label" => __('Everyone','sticky-list'),
                        "value" => "everyone"
                    ),
                    array(
                        "label" => __('All logged in users','sticky-list'),
                        "value" => "loggedin"
                    ),
                    array(
                        "label" => __('Entry creator','sticky-list'),
                        "value" => "creator"
                    )                    
                ),$roles_array
            );

            //Get all avalible roles
            global $wp_roles;
            $roles = $wp_roles->get_names();
            
            foreach ($roles as $key => $value) {
                $roles_array = array_merge(
                    array(
                        array(
                            "label" => $value,
                            "value" => $key
                        )
                    ),$roles_array
                );
            }
            $roles_array = array_reverse($roles_array);

            
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
                            "tooltip" => __('Select who should be able to se the entries in the list. Administrators will always see all entries','sticky-list'),
                            "choices" => $roles_array
                        ),
                        array(
                            "label"   => $embedd_post_page_label,
                            "type"    => "select",
                            "name"    => "embedd_page",
                            "tooltip" => __('The page/post where the form is embedded. This page will be used to view/edit the entry','sticky-list'),
                            "choices" => $posts_array
                        ),
                        array(
                            "label"   => __('Custom url','sticky-list'),
                            "type"    => "text",
                            "name"    => "custom_embedd_page",
                            "tooltip" => __('Manually input the url of the form. This overrides the selection made in the dropdown above. Use this if you cannot find the page/post in the list.','sticky-list'),
                            "class"   => "medium"
                        ),
                        array(
                            "label"   => __('Max nr of entries','sticky-list'),
                            "type"    => "text",
                            "name"    => "max_entries",
                            "tooltip" => __('Maximum number of entries to be shown in the list.','sticky-list'),
                            "class"   => "small"
                        ),
                        array(
                            "label"   => __('Make files clickable','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "enable_clickable",
                            "tooltip" => __('Check this box to make uploaded files that are shown in the list clickable','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Enabled','sticky-list'),
                                    "name"  => "enable_clickable"
                                )
                            )
                        ),
                        array(
                            "label"   => __('Link to post','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "enable_postlink",
                            "tooltip" => __('Check this box to insert a link to the WordPress post in the action column. Only applicable if the list actually contains WordPress posts.','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Enabled','sticky-list'),
                                    "name"  => "enable_postlink"
                                )
                            )
                        ),
                        array(
                            "label"   => __('Link label','sticky-list'),
                            "type"    => "text",
                            "name"    => "link_label",
                            "tooltip" => __('Label for the post link.','sticky-list'),
                            "class"   => "small",
                            "default_value" => __('Post','sticky-list')
                        ),
                        array(
                            "label"   => __('View PDF','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "enable_pdf",
                            "tooltip" => __('Check this box to enable users to view the complete submitted entry as a PDF (requires Gravity PDF Addon). A "PDF" link will appear in the list','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Enabled','sticky-list'),
                                    "name"  => "enable_pdf"
                                )
                            )
                        ),
                        array(
                            "label"   => __('PDF button label','sticky-list'),
                            "type"    => "text",
                            "name"    => "enable_pdf_label",
                            "tooltip" => __('Label for the PDF button','sticky-list'),
                            "class"   => "small",
                            "default_value" => __('PDF','sticky-list')
						),
                        array(
                            "label"   => __('Gravity PDF ID','sticky-list'),
                            "type"    => "text",
                            "name"    => "enable_pdf_id",
                            "tooltip" => __('The Gravity PDF id, found in the settings for each PDF created, without any slashes, dashes, etc. Example = 579e892aaf1b5','sticky-list'),
                            "class"   => "small",
                        ),
						array(
                            "label"   => __('View entries','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "enable_view",
                            "tooltip" => __('Check this box to enable users to view the complete submitted entry. A "View" link will appear in the list','sticky-list'),
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
                            "tooltip" => __('Check this box to enable user to edit submitted entries. An "Edit" link will appear in the list','sticky-list'),
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
                            "label"   => __('New entry ID','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "new_entry_id",
                            "tooltip" => __('Check this box to give an edited entry a new ID every time it is updated.','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Enabled','sticky-list'),
                                    "name"  => "new_entry_id"
                                )
                            )
                        ),
                        array(
                            "label"   => __('Delete entries','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "enable_delete",
                            "tooltip" => __('Check this box to enable user to delete submitted entries. A "Delete" link will appear in the list','sticky-list'),
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
                            "label"   => __('Confirm delete','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "confirm_delete",
                            "tooltip" => __('Check this box to require deletions to be confirmed by clicking OK in a dialog box','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Enabled','sticky-list'),
                                    "name"  => "confirm_delete"
                                )
                            )
                        ),
                        array(
                            "label"   => __('Confirm delete text','sticky-list'),
                            "type"    => "text",
                            "name"    => "confirm_delete_text",
                            "tooltip" => __('Text for the confirm delete dialog box','sticky-list'),
                            "class"   => "small",
                            "default_value" => __('Really delete this entry?','sticky-list')
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
                            "label"   => __('Duplicate entries','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "enable_duplicate",
                            "tooltip" => __('Check this box to enable user to duplicate entries in the list. A "Duplicate" link will appear in the list that allows the user to use an existing entry as a template for a new one.','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Enabled','sticky-list'),
                                    "name"  => "enable_duplicate"
                                )
                            )
                        ),
                        array(
                            "label"   => __('Duplicate label','sticky-list'),
                            "type"    => "text",
                            "name"    => "enable_duplicate_label",
                            "tooltip" => __('Label for the duplicate button','sticky-list'),
                            "class"   => "small",
                            "default_value" => __('Duplicate','sticky-list')
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
                            "class"   => "medium",
                            "default_value" => __('The list is empty. You can edit or remove this text in settings','sticky-list')
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
                            "label"   => __('Initial sort by','sticky-list'),
                            "type"    => "select",
                            "name"    => "initial_sort",
                            "tooltip" => __('Initially sort the list by a specific field or by date added. ','sticky-list'),
                            "choices" => $fields_array
                        ),
                        array(
                            "label" => __('Sort direction','sticky-list'),
                            "type" => "radio",
                            "horizontal" => true,
                            "name" => "initial_sort_direction",
                            "default_value" => "DESC",
                            "tooltip" => __('Select the direction in which the list should be sorted when first rendered','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Descending','sticky-list'),
                                    "value" => "DESC"
                                ),
                                array(
                                    "label" => __('Ascending','sticky-list'),
                                    "value" => "ASC"
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
                        ),
                        array(
                            "label"   => __('List pagination','sticky-list'),
                            "type"    => "checkbox",
                            "name"    => "enable_pagination",
                            "tooltip" => __('Check this box to enable pagination for the list','sticky-list'),
                            "choices" => array(
                                array(
                                    "label" => __('Enabled','sticky-list'),
                                    "name"  => "enable_pagination"
                                )
                            )
                        ),
                        array(
                            "label"   => __('Entries per page','sticky-list'),
                            "type"    => "text",
                            "name"    => "page_entries",
                            "tooltip" => __('Number of entries to be shown on each page.','sticky-list'),
                            "class"   => "small",
                            "default_value" => "10"
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

            if(isset($settings["enable_list"]) && true == $settings["enable_list"]){

                
                $type = rgar( $notification, 'stickylist_notification_type' );
                $options = array(
                    'all' => __( "Always", 'sticky-list' ),
                    'new' => __( "When a new entry is submitted", 'sticky-list' ),
                    'edit' => __( "When an entry is updated", 'sticky-list' ),
                    'delete' => __( "When an entry is deleted", 'sticky-list' )
                );

                $option = '';

                
                foreach ( $options as $key => $value ) {
                    
                    $selected = '';
                    if ( $type == $key ) $selected = ' selected="selected"';
                    $option .= "<option value=\"{$key}\" {$selected}>{$value}</option>\n";
                }

                
                $ui_settings['sticky-list_notification_setting'] = '
                <tr>
                    <th><label for="stickylist_notification_type">' . __( "Send this notification", 'sticky-list' ) . '</label></th>
                    <td><select name="stickylist_notification_type" value="' . $type . '">' . $option . '</select></td>
                </tr>';              
            }  

            return ( $ui_settings );
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
         * Maybe disable notification notification type
         *
         */
        function stickylist_gform_disable_notification( $is_disabled, $notification, $form, $entry ) {

            
            $settings = $this->get_form_settings($form);

            
            if(isset($settings["enable_list"]) && true == $settings["enable_list"]){
                
                if(isset($notification["stickylist_notification_type"]) && $notification["stickylist_notification_type"] != "") {

                    $is_disabled = true;

                    
                    if($_POST["action"] == "edit") {
                        
                        
                        if($notification["stickylist_notification_type"] == "edit" || $notification["stickylist_notification_type"] == "all") {
                            $is_disabled = false;
                        }

                    
                    }else{
                        
                        
                        if ( $notification["stickylist_notification_type"] == "new" || $notification["stickylist_notification_type"] == "all" ) {
                            $is_disabled = false;
                        }
                    }
                }           
            }

            return ( $is_disabled );
        }


        /**
         * Make sure that the notification has the correct ID
         *
         */
        function stickylist_modify_notification($notification, $form, $entry) {

            $settings = $this->get_form_settings($form);
            if(isset($_POST["edit_id"]) && $_POST["edit_id"] != "") {
                if(true != $settings["new_entry_id"]){
                    $entry["id"] = $_POST["edit_id"];
                }
            }
            return $notification;
        }


        /**
         * Add new confirmation settings
         *
         */
        function stickylist_gform_confirmation_ui_settings( $ui_settings, $confirmation, $form ) {

            $settings = $this->get_form_settings($form);

            if(isset($settings["enable_list"]) && true == $settings["enable_list"] && !isset($confirmation["event"])){

                
                $type = rgar( $confirmation, 'stickylist_confirmation_type' );
               
                $options = array(
                    'all' => __( "Always", 'sticky-list' ),
                    'never' => __( "Never", 'sticky-list' ),
                    'new' => __( "When a new entry is submitted", 'sticky-list' ),
                    'edit' => __( "When an entry is updated", 'sticky-list' ),
                );

                $option = '';

                
                foreach ( $options as $key => $value ) {
                    
                    $selected = '';
                    if ( $type == $key ) $selected = ' selected="selected"';
                    $option .= "<option value=\"{$key}\" {$selected}>{$value}</option>\n";
                }

                
                $ui_settings['sticky-list_confirmation_setting'] = '
                <tr>
                    <th><label for="stickylist_confirmation_type">' . __( "Display this confirmation", 'sticky-list' ) . '</label></th>
                    <td><select name="stickylist_confirmation_type" value="' . $type . '">' . $option . '</select></td>
                </tr>';  
            }

            return ( $ui_settings );  
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

            
            $settings = $this->get_form_settings($form);

            
            if(isset($settings["enable_list"]) && true == $settings["enable_list"]){
            
                
                $confirmations = $form["confirmations"];
                $new_confirmation = "";

                
                if(!isset($_POST["action"])) {
                    $_POST["action"] = "new";
                }

                
                if(isset($_POST["edit_id"]) && $_POST["edit_id"] != "") {
                    if(true != $settings["new_entry_id"]){
                        $lead["id"] = $_POST["edit_id"];
                    }
                }

                
                foreach ($confirmations as $confirmation) {

                    
                    if (isset($confirmation["stickylist_confirmation_type"])) {
                        $confirmation_type = $confirmation["stickylist_confirmation_type"];
                    }else{
                        $confirmation_type = "";
                    }

                    
                    if( $confirmation_type == $_POST["action"] || $confirmation_type == "all" || !isset($confirmation["stickylist_confirmation_type"])) {
                        
                        
                        if (!isset($confirmation["event"])) {
                            
                            
                            if($confirmation["type"] == "message") {
                                $new_confirmation .= $confirmation["message"] . " ";

                            
                            }else{
                                $new_confirmation = $original_confirmation;
                                break;
                            }
                        }
                    }        
                }

                
                if(!isset($new_confirmation["redirect"]) ) {

                    $new_confirmation = GFCommon::replace_variables($new_confirmation, $form, $lead);
                    $new_confirmation = '<div id="gform_confirmation_message_' . $form["id"] . '" class="gform_confirmation_message_' . $form["id"] . ' gform_confirmation_message">' . $new_confirmation . '</div>';
                    return $new_confirmation;
                }else{
                    return $new_confirmation;
                }

            }else{

                
                return $original_confirmation;
            }
        }
    }
}
