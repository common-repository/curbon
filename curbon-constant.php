<?php
/**
 * Constants for plugin
 * All constants that can be used in the plugin are defined here.
 * php version 7.4

 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @version  GIT: @1.0.0@
 * @link     https://curbon.io/
 */

    global $wpdb;

if (! defined('CURBON_PLUGIN_PATH') ) {
    define('CURBON_PLUGIN_PATH', plugin_dir_path(CURBON_PLUGIN_FILE));
}

if (! defined('CURBON_PLUGIN_URL') ) {
    define('CURBON_PLUGIN_URL', plugin_dir_url(CURBON_PLUGIN_FILE));
}

if (! defined('CURBON_PLUGIN_VER') ) {
    define('CURBON_PLUGIN_VER', '1.0.0');
}

if (! defined('CURBON_LOOK_AND_FEEL_URL') ) {
    define(
        'CURBON_LOOK_AND_FEEL_URL', 
        get_admin_url().
        'admin.php?page=curbon-dashboard&tab=curbon-look-and-feel'
    );
}

if (! defined('CURBON_SETTINGS_URL') ) {
    define(
        'CURBON_SETTINGS_URL', 
        get_admin_url().
        'admin.php?page=curbon-dashboard&tab=curbon-settings'
    );
}

if (! defined('CURBON_FRIENDLY_REWARD_URL') ) {
    define(
        'CURBON_FRIENDLY_REWARD_URL', 
        get_admin_url().
            'admin.php?page=curbon-dashboard&tab=climate-friendly-rewards'
    );
}

if (! defined('CURBON_ACCOUNT_URL') ) {
    define(
        'CURBON_ACCOUNT_URL', 
        get_admin_url().
        'admin.php?page=curbon-dashboard&tab=curbon-card-manager'
    );
}

    /*
    *Define Carbon Click Laravel API Details
    */
    if (! defined('CURBON_API_LARAVEL_URL') ) {
        /* *NB* DO NOT modify the line below - 
        our automated build searches for 
        and replaces this line with an environment specific value */
        define('CURBON_API_LARAVEL_URL', 'https://api.curbon.io/');
    }
    
    $supported_currency_code = array("ZAR");

    // @codingStandardsIgnoreStart
    /*
    * Define laravel access token to access API
    */
    if (! defined('CURBON_ACCESS_TOKEN') ) {
        define('CURBON_ACCESS_TOKEN', "");
    }

    $curbon_onboarding_status  = get_option('curbon-onboarding-status');
    
    if( empty( $curbon_onboarding_status ) ){
        return;
    }

    $curbon_settings           = get_option('curbon_settings_options');
    $curbon_config             = get_option('curbon_config');

    $curbon_installed_time  = get_option('curbon_installed_time');
    // $curbon_current_time    = date('Y-m-d H:s:i');
    $curbon_current_time    = current_datetime()->format('Y-m-d H:i:s');

    $get_order_has_offset_query = "SELECT 
        count(*) as offset_count 
        FROM " . $wpdb->postmeta . " 
        WHERE 
            post_id IN ( 
                SELECT id FROM " . $wpdb->posts . " WHERE `post_type` = 'shop_order' AND `post_date` BETWEEN '" . $curbon_installed_time . "' AND '" . $curbon_current_time . "' 
            )
        AND `meta_key` LIKE '_order_has_offset'";
    $get_order_offset 
        = $wpdb->get_results(
            $get_order_has_offset_query
        )[0]->offset_count;

    define('CURBON_TOTAL_OFFSET_ORDERS', $get_order_offset);

    $get_total_offset_amount_query = "SELECT 
        SUM(`meta_value`) as offset_amount
        FROM " . $wpdb->postmeta . " 
        WHERE 
            post_id IN ( 
                SELECT id FROM " . 
                    $wpdb->posts . 
                " WHERE `post_type` = 'shop_order' 
                    AND `post_date` BETWEEN '" . 
                        $curbon_installed_time . "' 
                    AND '" . $curbon_current_time . "' 
            )
            AND `meta_key` = '_offset_purchase_amount'";
    $get_order_offset_amount 
        = $wpdb->get_results(
            $get_total_offset_amount_query
        )[0]->offset_amount;

    define('CURBON_TOTAL_OFFSET_AMOUNT', round($get_order_offset_amount, 2));
    // @codingStandardsIgnoreEnd
    /*
    *Define T and C, Privacy links
    */
    if (! defined('CURBON_T_C_LINK') ) {
        define('CURBON_T_C_LINK', $curbon_config['links']['terms']);
    }

    if (! defined('CURBON_REFUND_LINK') ) {
        define('CURBON_REFUND_LINK', $curbon_config['links']['refund']);
    }

    if (! defined('CURBON_PRIVACY_LINK') ) {
        define('CURBON_PRIVACY_LINK', $curbon_config['links']['privacy']);
    }

    if (! defined('CURBON_OPEN_INSTRUCTIONS_LINK') ) {
        define(
            'CURBON_OPEN_INSTRUCTIONS_LINK', 
            $curbon_config['plugin']['instruction']
        );
    }

    if (! defined('CURBON_SUB_PRICE') ) {
        define('CURBON_SUB_PRICE', 0);
    }    

    /*Minimum top up amount*/
    if (!defined('CURBON_MINIMUM_TOP_UP_AMOUNT') ) {
        if (isset($curbon_settings['curbon_card_management_prefered_topup'])) {
            define(
                'CURBON_MINIMUM_TOP_UP_AMOUNT', 
                $curbon_settings['curbon_card_management_prefered_topup']
            );
        } else {
            define('CURBON_MINIMUM_TOP_UP_AMOUNT', 500);
        }
    }

?>