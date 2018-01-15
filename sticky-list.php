<?php
/*
Plugin Name: Gravity Forms Sticky List
Plugin URI: https://github.com/13pixlar/sticky-list
Description: List and edit submitted entries from the front end
Version: 1.5
Author: 13pixar
Author URI: http://13pixlar.se
*/


/* Todo
 * Support for file multiple uploads
 */

//------------------------------------------

// Create a helper function for easy SDK access.
function stickylist_fs() {
    global $stickylist_fs;

    if ( ! isset( $stickylist_fs ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/includes/start.php';

        $stickylist_fs = fs_dynamic_init( array(
            'id'                  => '1528',
            'slug'                => 'gravity-forms-sticky-list',
            'type'                => 'plugin',
            'public_key'          => 'pk_ab96a4a2bac76862a81de1dde21a9',
            'is_premium'          => false,
            'has_addons'          => false,
            'has_paid_plans'      => false,
            'menu'                => array(
                'first-path'     => 'plugins.php',
            ),
        ) );
    }

    return $stickylist_fs;
}

// Init Freemius.
stickylist_fs();
// Signal that SDK was initiated.
do_action( 'stickylist_fs_loaded' );

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
