<?php
/*
Plugin Name: Gravity Forms Sticky List
Plugin URI: https://github.com/13pixlar/sticky-list
Description: List and edit submitted entries from the front end
Version: 1.2.3
Author: 13pixar
Author URI: http://13pixlar.se
*/


/* Todo
 * Support for file multiple uploads
 * Support for GF 1.9 "Save and Continue" functionallity
 * Support for multi page forms
 */

//------------------------------------------
add_action( 'gform_loaded', array( 'StickyList_Bootstrap', 'load' ), 5 );
class StickyList_Bootstrap {

    public static function load() {
        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'class-sticky-list.php' );
        GFAddOn::register( 'StickyList' );
    }
}
