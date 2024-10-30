<?php
    /*
        Plugin Name:     CURBON
        Plugin URI:     https://wordpress.org/plugins/curbon/
        Description:     Ecommerce carbon offsetting made simple
        Author:         CURBON
        Version:         1.0.0
        Author URI:     https://www.curbon.io/
    */

    // Preventing to direct access
    defined('ABSPATH') OR die('Direct access not acceptable!');

    global $supported_currency_code, $wpdb;
    
if (! defined('CURBON_PLUGIN_FILE') ) {
    define('CURBON_PLUGIN_FILE', __FILE__);
}

if (! defined('CURBON_PLUGIN_FILE_URL') ) {
    define('CURBON_PLUGIN_FILE_URL', plugin_dir_url(__FILE__));
}
    
    require_once 'curbon-constant.php';
    
    // Load plugin with plugins_load
function curbon_init()
{
    include_once CURBON_PLUGIN_PATH . 'includes/class-curbon-init.php';
}
    add_action('plugins_loaded', 'curbon_init', 20);

function curbon_registration_activation_init_callback()
{
    include_once CURBON_PLUGIN_PATH . 'includes/class-registration-activation-init.php';
}
    register_activation_hook(__FILE__, 'curbon_registration_activation_init_callback');

    /*
     *   We will remove "curbon_registration_deactivation_init_callback" at the end.
     *   This functinality will be handle when plugin get uninstalled
    */
function curbon_registration_deactivation_init_callback()
{
    global $wpdb, $wp_version;
        
    include_once 'curbon-constant.php';

    $curbon_onboarding_status      = get_option('curbon-onboarding-status');
        
    /*
    DELETE SHOP START HERE
    */
    $CURBON_ACCESS_TOKEN       = get_option('curbon_laravel_api_tokens')['access_token'];

    $body = array(
                "status"       => "deactivate"
            );

    $headers = array(
                        "Accept"        => "application/json",
                        "Content-Type"  => "application/json",
                        "Authorization" =>  "Bearer ".$CURBON_ACCESS_TOKEN
                    );

    $args = array(
                    'headers'       => $headers,
                    'timeout'       => 120,
                    'httpversion'   => '1.1',
                    'sslverify'     => true,
                    'body'          => json_encode($body)
                );

    $response       = wp_remote_post(CURBON_API_LARAVEL_URL."api/v1/shops/status", $args);
    $responseBody   = wp_remote_retrieve_body($response);
    $responseBody   = json_decode($responseBody, true);


    $timestamp = wp_next_scheduled('curbon_cron_hook_1min');
    wp_unschedule_event($timestamp, 'curbon_cron_hook_1min');

    $timestamp = wp_next_scheduled('curbon_cron_hook_5min');
    wp_unschedule_event($timestamp, 'curbon_cron_hook_5min');

    $timestamp = wp_next_scheduled('curbon_cron_hook_hourly');
    wp_unschedule_event($timestamp, 'curbon_cron_hook_hourly');

    $timestamp = wp_next_scheduled('curbon_cron_hook_weekly');
    wp_unschedule_event($timestamp, 'curbon_cron_hook_weekly');


    /*
        DELETE SHOP END HERE
    */
}
    register_deactivation_hook(__FILE__, 'curbon_registration_deactivation_init_callback');


function curbon_upgrade_woo_callback( $upgrader_object, $options )
{

    $current_plugin_path_name = plugin_basename(__FILE__);

    if ($options['action'] == 'update' && $options['type'] == 'plugin' ) {

        foreach($options['plugins'] as $each_plugin) {

            if ($each_plugin==$current_plugin_path_name ) {
                 
                if(class_exists('CURBON_Carbonclick_Laravel_API')) {

                    if(! function_exists('get_plugin_data') ) {
                        include_once ABSPATH . 'wp-admin/includes/plugin.php';
                    }
                        
                    $plugin_data = get_plugin_data(__FILE__);

                    $shop_info = array(
                            'version' => $plugin_data['Version'],
                        );

                    $CURBON_Carbonclick_Laravel_API    = new CURBON_Carbonclick_Laravel_API();

                    $response = $CURBON_Carbonclick_Laravel_API->curbon_update_shop_info($shop_info);   
                }
            }
        }
    }
}
    add_action('upgrader_process_complete', 'curbon_upgrade_woo_callback', 10, 2);
?>