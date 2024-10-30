<?php
/**
 * Laravel API Callbacks
 * API calls which need to handle on WP side
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
 * CURBON_Carbonclick_Laravel_API class 
 * responsable to load all the scripts and styles.
 * 
 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @link     https://curbon.io/
 */
class CURBON_Carbonclick_Laravel_API
{

    public function __construct() // phpcs:ignore
    {
        
        $curbon_onboarding_status = get_option('curbon-onboarding-status');
    }


    public function curbon_laravel_api_response_code( $key = "", $value = "" ) // phpcs:ignore
    {
        
        $response_code = get_option('curbon_laravel_api_response_code');

        $response_code[$key] = $value;

        update_option('curbon_laravel_api_response_code', $response_code);
    }

    /**
     * Curbon Config
     * 
     * @param $args Arguments needed to post data to Laravel API
     *
     * @return NULL "Config details for shop in LARAVEL api"
     */
    public function curbonConfigCallback( $args = array() )
    {
        /*
            Required Parameters

            type            : woocommerce or magento
            server          : staging or production
        */

        $body = array(
                    "type"          => "woocommerce",
                );
        

        $headers = array(
                            "Accept"        => "application/json",
                            "Content-Type"  => "application/json",
                        );

        $args = array(
                        'headers'       => $headers,
                        'timeout'       => 120,
                        'httpversion'   => '1.1',
                        'sslverify'     => true,
                        'body'          => json_encode($body)
                    );

        $response       = wp_remote_post(
            CURBON_API_LARAVEL_URL."api/v1/config", 
            $args
        );

        $responseBody   = wp_remote_retrieve_body($response);
        $response_code  = wp_remote_retrieve_response_code($response);
        $this->curbon_laravel_api_response_code('config_api', $response_code);
        
        $responseBody   = json_decode($responseBody, true);

        update_option('curbon_config', $responseBody);

    }

    /**
     * Create Shop API Callback
     *
     * @param $args Arguments needed to post data to Laravel API
     * 
     * @return $responseBody Response from Laravel API after creating shop
     */
    public function curbonCreateShop( $args = array() )
    {
        /*
            Required Parameters

            type        : woocommerce or magento
            domain      : example.com
            name        : Nitesh Chauhan
            shop_owner  : Nitesh
            email       : hi@example.com
            currency    : USD
            merchant_code : Unique code provided by Curbon
            description : Desription of bloginfo
            setupintent_id  : tok_1HuEPNLxxxxxxxx
        */

        $keys = array(
                    'type',
                    'domain',
                    'name',
                    'shop_owner',
                    'email',
                    'currency',
                    'merchant_code',
                    'description',
                    'setupintent_id'
                );    

        $woocommerce_currency       = get_woocommerce_currency();
        $base_country               = (new WC_Countries)->get_base_country();
        $name                       = get_bloginfo('name');
        $email                      = get_option('admin_email');
        
        $body = array(
                    "type"          => "woocommerce",
                    "domain"        => get_site_url()."-new",
                    "name"          => $name,
                    "shop_owner"    => $name,
                    "email"         => $email,
                    "currency"      => $woocommerce_currency,
                    "description"   => get_bloginfo('description'),
                    "country_code"  => $base_country,
                    "timezone"      => get_option('timezone_string'),
                    "weight_unit"   => get_option('woocommerce_weight_unit'),
                    "mode"          => "prepaid",
                );
        
        foreach ( $keys as $key ) {
            if (isset($args[ $key ])  && !empty($args[ $key ]) ) {
                $body[ $key ] = $args[ $key ];
            }
        }

        $headers = array(
                            "Accept"        => "application/json",
                            "Content-Type"  => "application/json"
                        );
        
        $args = array(
                        'headers'       => $headers,
                        'timeout'       => 120,
                        'httpversion'   => '1.1',
                        'sslverify'     => true,
                        'body'          => json_encode($body)
                    );

        $response       = wp_remote_post(CURBON_API_LARAVEL_URL."api/v1/shops", $args);
        $responseBody   = wp_remote_retrieve_body($response);
        $responseBody   = json_decode($responseBody, true);

        return $responseBody;
    }

    /**
     * Retrieve Card details
     * 
     * @return $responseBody "Shop details from shops api fallback"
     */
    public function curbonFetchCustomer()
    {
        
        $curbon_laravel_api_access_token = get_option(
            'curbon_laravel_api_tokens'
        )['access_token'];

        $headers = array(
                            "Accept"        => "application/json",
                            "Content-Type"  => "application/json",
                            "Authorization" =>  "Bearer ".$curbon_laravel_api_access_token
                        );
        $args = array(
                        'headers'       => $headers,
                        'timeout'       => 120,
                        'httpversion'   => '1.1',
                        'sslverify'     => true,
                    );

        $response       = wp_remote_get(
            CURBON_API_LARAVEL_URL."api/v1/shops/details", 
            $args
        );
        $responseBody   = wp_remote_retrieve_body($response);
        $responseBody   = json_decode($responseBody, true);

        $response_code = wp_remote_retrieve_response_code($response);
        $this->curbon_laravel_api_response_code('fetch_customer', $response_code);
        
        return $responseBody;
    }



    /**
     * Save Purchase API Callback
     * 
     * @param $args Arguments needed to post data to Laravel API
     * 
     * @return $responseBody Response from Laravel API after Purchase fallback
     */
    public function curbonSavePurchase( $args = array() )
    {
        /*
            Required Parameters

            email           : Buyer email
            price           : Price of carbon offset
            currency        : USD
            quantity         : Total offset quantity
            preferred_topup : Preferred topup amount of merchant
            number          : Platform generated order ID
        */

        $curbon_laravel_api_access_token = get_option(
            'curbon_laravel_api_tokens'
        )['access_token'];

        
        /** WP REMOTE POST **/

        $body = array(
            "email"                 => $args['email'],
            "name"                  => $args['name'],
            "price"                 => $args['price'],
            "currency"              => $args['currency'],
            "quantity"              => "1",
            "number"                => $args['number'],
            "tax"                   => $args['tax'],
            "total_price"           => $args['total_price'],
            "order_status_url"      => $args['order_status_url'],
            "gateway"               => $args['gateway'],
            "city"                  => $args['billing_address']['city'],
            "state"                 => $args['billing_address']['state'],
            "country"               => $args['billing_address']['country'],
            "preferred_topup"       => $args['preferred_topup'],
            "offset_all_purchase"   => true,
            "billing_address"       => array(
                "city"          => $args['billing_address']['city'],
                "name"          => $args['billing_address']['name'],
                "phone"         => $args['billing_address']['phone'],
                "company"       => $args['billing_address']['company'],
                "country"       => $args['billing_address']['country'],
                "address1"      => $args['billing_address']['address1'],
                "address2"      => $args['billing_address']['address2'],
                "last_name"     => $args['billing_address']['last_name'],
                "first_name"    => $args['billing_address']['first_name'],
                "country_code"  => $args['billing_address']['country_code'],
            )
        );


        $headers = array(
            "Accept"        => "application/json",
            "Content-Type"  => "application/json",
            "Authorization" =>  "Bearer ".$curbon_laravel_api_access_token
        );

        $args = array(
            'headers'       => $headers,
            'timeout'       => 120,
            'httpversion'   => '1.1',
            'sslverify'     => true,
            'body'          => json_encode($body)
        );

        $response       = wp_remote_post(
            CURBON_API_LARAVEL_URL."api/v1/purchases", 
            $args
        );

        $response   = wp_remote_retrieve_body($response);

        /** WP REMOTE POST **/

        return json_decode($response, true);
    }



    /**
     * Refund Callback for orders
     * 
     * @param $args Arguments needed to post data to Laravel API
     * 
     * @return $responseBody Response from Laravel API after refund fallback
     */
    public function curbonRefund( $args = array() )
    {
        
        /*
            Required Parameters

            type          : woocommerce or magento
            merchant_code : Unique code provided by Curbon
            number: Order number on which refund performed
        */

        $curbon_laravel_api_access_token = get_option(
            'curbon_laravel_api_tokens'
        )['access_token'];

        $keys = array(
                    'cancel_reason',
                    'number'
                );    

        $body = array();
        
        foreach ( $keys as $key ) {
            if (isset($args[ $key ])  && !empty($args[ $key ]) ) {
                $body[ $key ] = $args[ $key ];
            }
        }

        $headers = array(
            "Accept"        => "application/json",
            "Content-Type"  => "application/json",
            "Authorization" =>  "Bearer ".$curbon_laravel_api_access_token
        );

        $args = array(
            'headers'       => $headers,
            'timeout'       => 120,
            'httpversion'   => '1.1',
            'sslverify'     => true,
            'method'        => 'PUT',
            'body'          => json_encode($body)
        );

        $response       = wp_remote_request(
            CURBON_API_LARAVEL_URL."api/v1/purchases/refund", 
            $args
        );

        $responseBody   = wp_remote_retrieve_body($response);
        $responseBody   = json_decode($responseBody, true);

        return $responseBody;
    }

    /**
     * Get Order count that take place when widget is enable
     *
     * @return $total_orders number of orders wihich has offset
     */
    public function wooOrderCountWhenWidgetEnable()
    {
        $args = array(
            'post_status'       => 'any',
            'post_type'         => 'shop_order',
            'posts_per_page'    => -1,
        );

        /*Get Count of Orders having is_curbon_enable yes*/
        $args['meta_query'] =   array(
                                    'relation'        => 'AND',
                                        array(
                                            'key'     => '_is_curbon_enable',
                                            'value'   => 'yes',
                                            'compare' => '='
                                        )
                                );


        $orders  = new WP_Query($args);
        $total_orders = $orders->found_posts;
        return $total_orders;
    }
    /*
    * Update Shop info
    * This function will be called on setting page, checkout page 
    * and install help required submission button
    */
    public function curbon_update_shop_info( $args = array(), $acccess_token = false ) //phpcs:ignore
    {
        
        $keys = array(
                    'orders_count',
                    'version',
                    'last_impression',
                    'install_help_required',
                    'setup',
                    'preferred_topup',
                );    

        $curbon_settings = get_option('curbon_settings_options');
        $preferred_topup 
            = (isset($curbon_settings['curbon_card_management_prefered_topup']) 
            && $curbon_settings['curbon_card_management_prefered_topup'] >= 500 ) 
            ? $curbon_settings['curbon_card_management_prefered_topup'] 
            : 500;
        
        $body = array(
                // 'orders_count' => $this->wooOrderCountWhenWidgetEnable(),
                'orders_count' => CURBON_TOTAL_OFFSET_ORDERS,
                'preferred_topup' => $preferred_topup
            );
        
        foreach ( $keys as $key ) {
            if (isset($args[ $key ]) ) {
                $body[ $key ] = $args[ $key ];
            }
        }


        $curbon_laravel_api_access_token = get_option(
            'curbon_laravel_api_tokens'
        )['access_token'];

        $CURBON_ACCESS_TOKEN = $curbon_laravel_api_access_token; 
        if ($acccess_token ) {
            $CURBON_ACCESS_TOKEN = $acccess_token; 
        }

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
                        'method'        => 'PUT',
                        'body'          => json_encode($body)
                    );
        

        $response       = wp_remote_request(
            CURBON_API_LARAVEL_URL."api/v1/shops", 
            $args
        );
        $responseBody   = wp_remote_retrieve_body($response);
        $responseBody   = json_decode($responseBody, true);
        
        return $responseBody;
    }



    /*
    * Update Shop Billing Information 
    * This function will be called on Account Page form
    * and install help required submission button
    */
    public function curbon_update_shop_billing_info( $args = array(), $acccess_token = false ) //phpcs:ignore
    {
        
        $keys = array(
                    'company_name',
                    'building_unit_number',
                    'building_name',
                    'email_address',
                    'street_address',
                    'suburb',
                    'town',
                    'country',
                    'postal_address',
                    'vat_number',
                );    

        $body = array(
            );
        
        foreach ( $keys as $key ) {
            $body[ $key ] = $args[ $key ];
        }


        $CURBON_ACCESS_TOKEN = get_option(
            'curbon_laravel_api_tokens'
        )['access_token'];

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
                        'method'        => 'PUT',
                        'body'          => json_encode($body)
                    );
        

        $response       = wp_remote_request(
            CURBON_API_LARAVEL_URL."api/v1/shops/billing", 
            $args
        );
        $responseBody   = wp_remote_retrieve_body($response);
        $responseBody   = json_decode($responseBody, true);
        
        return $responseBody;
    }

} // end of class CURBON_Carbonclick_Laravel_API