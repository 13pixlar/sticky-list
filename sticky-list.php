<?php
/*
Plugin Name: Gravity Forms Sticky List
Plugin URI: https://github.com/13pixlar/sticky-list
Description: List and edit submitted entries from the front end
Version: 1.4.5.1
Author: 13pixar
Author URI: http://13pixlar.se
Text Domain: sticky-list
Domain Path: /languages
*/


/* Todo
 * Support for file multiple uploads
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
