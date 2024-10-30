<?php
    /**
     * Curbon Uninstall
     * Uninstall procedure will be followed in this file.
     * php version 7.4

     * @category Curbon
     * @package  Curbon
     * @author   Curbon <michael@curbon.io>
     * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
     * @version  GIT: @1.0.0@
     * @link     https://curbon.io/
     */

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
    
global $wpdb, $wp_version;

require_once 'curbon-constant.php';

/**
 * Uninstall plugin fallback process.
 *
 * @return NULL
 */
function curbonUninstallCallback()
{
    $curbon_onboarding_status      = get_option('curbon-onboarding-status');
    
    /*
        DELETE SHOP START HERE
    */
    $CURBON_ACCESS_TOKEN = get_option('curbon_laravel_api_tokens')['access_token'];

    $body = array(
                    "status"          => "uninstall"
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

    $response       = wp_remote_post(
        CURBON_API_LARAVEL_URL."api/v1/shops/status", 
        $args
    );
    $responseBody   = wp_remote_retrieve_body($response);
    $responseBody   = json_decode($responseBody, true);
    /*DELETE SHOP END HERE*/

    /*
    *Delete all the option related to the carbon click plugin
    */
    delete_option('curbon-onboarding-status');
    delete_option('curbon_settings_options');
    delete_option('curbon_look_and_feel_options');
    delete_option('curbon-shop-status');
    delete_option('curbon_laravel_api_tokens');
    delete_option('curbon-widget-status');
    delete_option('curbon-global-notice');
    delete_option('curbon_is_settings_updated');
    delete_option('curbon-charge-status');
    delete_option('curbon_config');
    delete_transient('curbon_api_expiration_check');
    delete_transient('curbon_api_impact_data_on_cart');
    delete_option('curbon_laravel_api_response_code');
    delete_option('curbon_call_create_shop_api_on_activate');
}

if (is_multisite()) {
    $site_ids = get_site_transient('wordpoints_all_site_ids');

    if (! $site_ids ) {

        global $wpdb;

        $site_ids = $wpdb->get_col(
            "
                    SELECT `blog_id`
                    FROM `{$wpdb->blogs}`
                    WHERE `site_id` = {$wpdb->siteid}
                "
        );

        set_site_transient(
            'wordpoints_all_site_ids', 
            $site_ids, 
            2 * MINUTE_IN_SECONDS
        );
    }

    foreach ($site_ids as $key => $site_id) {
        switch_to_blog($site_id);
                
            curbonUninstallCallback();

        restore_current_blog();
    }
} else {
    curbonUninstallCallback();
}
?>