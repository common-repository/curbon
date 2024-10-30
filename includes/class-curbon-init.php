<?php
/**
 * Plugin init functions
 * Actions and filters for managing plugin things
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
 * CURBON_Init class responsable to load all the scripts and styles.
 * 
 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @link     https://curbon.io/
 */
class CURBON_Init
{

    public function __construct() // phpcs:ignore
    {
        $this->init_hooks();
        $this->includes();
    }

    public function init_hooks() // phpcs:ignore
    {
        add_filter(
            'plugin_action_links_'.
            plugin_basename(CURBON_PLUGIN_FILE), 
            array( 
                $this, 
                'pluginPageSettingsLink' 
            )
        );
    }

    /**
     * Includes plugin files.
     * 
     * @return NULL including assets
     */
    public function includes()
    {
        include_once CURBON_PLUGIN_PATH . 
            'includes/api/class-curbon-laravel-api.php';
        include_once CURBON_PLUGIN_PATH . 
            'includes/class-curbon-enqueue-scripts.php';
        include_once CURBON_PLUGIN_PATH . 
            'includes/woo/class-curbon-woo-init.php';
        include_once CURBON_PLUGIN_PATH . 
            'includes/class-curbon-webhook-calls.php';
        
        if (! is_admin() ) {
            $curbon_settings_options   
                = get_option('curbon_settings_options');
            $enable_widget          
                = $curbon_settings_options['curbon_enable_widget_on_cart'];
            $curbon_widget_location_on_mini_cart 
                = $curbon_settings_options['curbon_widget_location_on_mini_cart'];
            
            if ($enable_widget && $curbon_widget_location_on_mini_cart ) {
                include_once CURBON_PLUGIN_PATH . 
                    'includes/woo/avada-functions-override.php';
            }    
        }
        
        // Admin classes
        if (is_admin() ) {
            include_once CURBON_PLUGIN_PATH . 
                'includes/admin/class-curbon-admin-enqueue-scripts.php';
            include_once CURBON_PLUGIN_PATH . 
                'includes/admin/class-curbon-admin-init.php';
            include_once CURBON_PLUGIN_PATH . 
                'includes/admin/class-curbon-admin-save-settings.php';
        }
    }
    
    /**
     * Adding a Settings link to plugin
     * 
     * @param $links Set Settings page link
     * 
     * @return $links Updated ist of links
     */
    public function pluginPageSettingsLink( $links )
    {
        $links[] = '<a href="' . 
                admin_url('admin.php?page=curbon-dashboard&tab=dashboard') .
            '">' . 
            esc_html__('Settings') . 
        '</a>';

        return $links;
    }

} // end of class CURBON_Init

$curbon_init = new CURBON_Init;