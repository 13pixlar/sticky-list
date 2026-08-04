<?php
/*
Plugin Name: Gravity Forms Sticky List
Plugin URI: https://github.com/13pixlar/sticky-list
Description: List and edit submitted entries from the front end
Version: 1.6.0
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Author: 13pixar
Author URI: http://13pixlar.se
License: GPLv2 or later
Text Domain: sticky-list
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* Todo
 * Support for file multiple uploads
 */

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
