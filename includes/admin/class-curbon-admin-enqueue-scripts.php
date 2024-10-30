<?php
/**
 * All assets loading
 * CSS & JS added to admin and front-end both
 * php version 7.4

 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @version  GIT: @1.0.0@
 * @link     https://curbon.io/
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * CURBON_Admin_Enqueue_Scripts class responsable to load all the scripts and styles.
 * 
 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @link     https://curbon.io/
 */
class CURBON_Admin_Enqueue_Scripts
{

    public function __construct() // phpcs:ignore
    {
        add_action('admin_enqueue_scripts', array( $this, 'adminEnqueueScripts' ));
        add_action('admin_head', array( $this, 'adminInternalCss' ));
    }

    /**
     * Adding CSS & JS assets to pages
     *
     * @param $hook get correct hook for enqueuing the scripts
     * 
     * @return NULL
     */
    public function adminEnqueueScripts( $hook )
    {
        if ($this->isCurbonAdminPage($hook) !== true ) {
            return;
        }
        
        // Load thickbox
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');

        // Add the color picker css file   
        wp_enqueue_script('wp-color-picker'); 
        wp_enqueue_style('wp-color-picker'); 
        
        // Load admin scripts
        wp_enqueue_style(
            'curbon-jquery-ui', 
            CURBON_PLUGIN_URL . 'assets/css/jquery-ui.css', 
            array(), 
            CURBON_PLUGIN_VER
        );

        wp_enqueue_style(
            'curbon-admin-style', 
            CURBON_PLUGIN_URL . 'assets/css/curbon-admin-style.css', 
            array(), 
            CURBON_PLUGIN_VER
        );

        wp_enqueue_script(
            'curbon-admin-script', 
            CURBON_PLUGIN_URL . 'assets/js/curbon-admin-script.js', 
            array(), 
            CURBON_PLUGIN_VER, true
        );

        wp_localize_script(
            'curbon-admin-script', 'curbonAdminObj', array(
            'admin_url'          => admin_url('admin-ajax.php?ver=' . uniqid()),
            'look_and_feel_path' => CURBON_PLUGIN_URL."assets/images/look-and-feel/",
            ) 
        );
    }

    /**
     * Static css for admin pade
     *
     * @return NULL
     */
    public function adminInternalCss()
    {
        echo '<style>
            '.esc_html('#adminmenu li.toplevel_page_curbon-dashboard .wp-menu-image img {
                height: 25px;
                padding: 5px;
            }').'
        </style>';
    }

    /**
     * Check current admin page is plugin admin page or not.
     *
     * @param string $hook check if page is admin
     *
     * @return boolean
     */
    public function isCurbonAdminPage( $hook )
    {

        if ($hook == 'toplevel_page_curbon-dashboard' ) {
            return true;
        }

        return false;
    }

} // end of class CURBON_Admin_Enqueue_Scripts

$curbon_admin_enqueue_scripts = new CURBON_Admin_Enqueue_Scripts;