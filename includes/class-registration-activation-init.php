<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * CURBON_Registration_Activation_Init class responsable to load all the scripts and styles.
 */
class CURBON_Registration_Activation_Init
{

    public function __construct()
    {
        
        $this->curbon_check_woocommerce_is_active_callback();
        $this->curbon_check_woocommerce_currency_support_callback();
        $this->curbonConfigCallback();

        $current_datetime = current_datetime()->format('Y-m-d H:i:s');
        update_option('curbon_installed_time', $current_datetime);
        
        if(get_option('curbon_call_create_shop_api_on_activate', 'true') == 'true' ) {
            $this->curbonCreateShop_callback();    
        }else{
            $this->curbon_shop_status_callback();    
        }
        
        $this->curbon_onboarding_process_status_callback();
        $this->curbon_settings_update_options_callback();
        $this->curbon_look_and_feel_update_options_callback();
        $this->curbon_create_carbon_offset_product_callback();
        add_action('activated_plugin', array( $this, 'curbon_redirect_on_activation_callback' ));

    }

    public function curbon_countries()
    {
        
        $headers = array(
                            "Accept"        => "application/json",
                            "Content-Type"  => "application/json",
                        );

        $args = array(
                        'headers'       => $headers,
                        'timeout'       => 120,
                        'httpversion'   => '1.1',
                        'sslverify'     => true,
                    );

        $response       = wp_remote_get(CURBON_API_LARAVEL_URL."api/v1/countries", $args);

        $responseBody   = wp_remote_retrieve_body($response);
        $responseBody   = json_decode($responseBody, true);

        return $responseBody;
    }

    /*
    * Check woocommerce is active or not.
    */
    public function curbon_check_woocommerce_is_active_callback()
    {
        if (!class_exists('WooCommerce') ) {
            die('Plugin not activated: WooCommerce is not installed or activated.');
        }
    }


    /*
    * check woocommerce currency support. If currency is not from the globally defined currency then don't activate plugin and show message to use the currency from listed one
    */
    public function curbon_check_woocommerce_currency_support_callback()
    {
        global $supported_currency_code;
        $error = '';
        if (class_exists('WooCommerce') ) {
            if(! in_array(get_woocommerce_currency(), $supported_currency_code) ) {
                $error .= "Supported Currencies are: " . json_encode($supported_currency_code);
                $error .= '<p><br>'.get_woocommerce_currency().'</b> is not supported by the plugin "Curbon".</p>';
                
                die($error);
            }
        }
    }

    
    /*
    *Curbon Config
    */
    public function curbonConfigCallback( $args = array() )
    {
        /*
            Required Parameters

            type            : woocommerce or magento
            server          : staging or production
        */

        $body = array(
                    "type"          => "woocommerce"
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

        $response       = wp_remote_post(CURBON_API_LARAVEL_URL."api/v1/config", $args);
        $responseBody   = wp_remote_retrieve_body($response);
        $responseBody   = json_decode($responseBody, true);

        
        $response_code = get_option('curbon_laravel_api_response_code');

        $response_code['config_api'] = wp_remote_retrieve_response_code($response);

        update_option('curbon_laravel_api_response_code', $response_code);

        update_option('curbon_config', $responseBody);

    }


    /*
    *Create Shop API Callback
    */
    public function curbonCreateShop_callback( $args = array() )
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

        $curbon_config = get_option('curbon_config');

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
                    "domain"        => get_site_url(),
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
                            "Content-Type"  => "application/json",
                            "Authorization" =>  "Bearer " . $curbon_config['plugin']['access_token']
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

        if(!empty($responseBody['success']) && ($responseBody['success'] == true || $responseBody['success'] == 1 ) ) {
            update_option('curbon_call_create_shop_api_on_activate', 'false');
            
            $curbon_tokens = [
                "access_token" => $responseBody['access_token'],
            ];

            update_option('curbon_laravel_api_tokens', $curbon_tokens);

            $update_look_and_feel_options = [
                "colors"    =>[
                                "primary_color"     => "#A09FFA",
                                "secondary_color"   => "#A09FFA",
                                "background_color"  => "#FFFFFF",
                                "text_color"        => "#000000",
                            ],
            ];

            update_option('curbon_look_and_feel_options', $update_look_and_feel_options);


        }

        return $responseBody;
    }


    public function curbon_shop_status_callback()
    {

        $curbon_onboarding_status      = get_option('curbon-onboarding-status');
        $curbon_settings_options      = get_option('curbon_settings_options');

        $CURBON_ACCESS_TOKEN       = get_option('curbon_laravel_api_tokens')['access_token'];

        $body = array(
                    "status"   => "activate",
                    'setup'    => true 
                    // 'setup'    => ( $curbon_settings_options['curbon_enable_widget_on_cart'] == 1 ) ? true : false
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
    }

    /*
    * Updating default status of onboarding process to complete.
    */
    public function curbon_onboarding_process_status_callback()
    {
        
        
        //Remove comment when you go live
        $curbon_onboarding_status = get_option('curbon-onboarding-status');

        if(isset($curbon_onboarding_status['status']) &&  $curbon_onboarding_status['status'] == 'complete' ) {
            return true;
        }
        
            
        $curbon_onboarding_status = array();

        $curbon_onboarding_status['status'] = 'pending';
        
        $curbon_onboarding_status['onboarding_current_step'] = '1';
        
        $curbon_onboarding_status['steps'] = array(
                                            'step-1' => 'pending',
                                            'step-2' => 'pending',
                                        );
        
        $curbon_onboarding_status['dashboard_installation_instruction']    = '0';
        $curbon_onboarding_status['dashboard_settings_page']               = '0';
        $curbon_onboarding_status['dashboard_get_started']                 = '0';
        $curbon_onboarding_status['dashboard_guide']                       = '0';
        $curbon_onboarding_status['dashboard_head_over']                   = '0';
        $curbon_onboarding_status['dashboard_template_to_use']             = '0';
        $curbon_onboarding_status['dashboard_badge_here']                  = '0';
        $curbon_onboarding_status['dashboard_social_post_ideas']           = '0';
        $curbon_onboarding_status['dashboard_look_and_feel']               = '0';

        update_option('curbon-onboarding-status', $curbon_onboarding_status);
    }


    /*
    * Updating default option for settings tab
    */
    public function curbon_settings_update_options_callback()
    {

        $curbon_settings = get_option('curbon_settings_options');
        if(isset($curbon_settings['curbon_offset_amount']) && !empty($curbon_settings['curbon_offset_amount']) ) {
            return true;
        }

        $curbon_settings  = array(
            'curbon_enable_widget_on_cart'         => 0,
            'curbon_widget_location_on_cart'       => 1,
            'curbon_widget_location_on_mini_cart'  => 0,
            'curbon_widget_location_on_checkout'   => 1,
            'curbon_offset_amount'                 => 2,
            'curbon_card_management_prefered_topup' => 20,
        );

        update_option('curbon_settings_options', $curbon_settings);
    }
    

    /*
    * Updating default option for look and feel tab
    */
    public function curbon_look_and_feel_update_options_callback()
    {

        $curbon_look_and_feel = get_option('curbon_look_and_feel_options');
        if(!empty($curbon_look_and_feel)) {
            return true;
        }

        $curbon_look_and_feel  = array(
            'plugin_border_color'               => "#2AA43C",
            'plugin_background_color'           => "#ffffff",
            'plugin_background_color_expanded'  => "#EAF9EC",
            'plugin_icons_color'                => "#2AA43C",
            'plugin_text_colour_top_section'    => "#000000",
            'plugin_large_text_colour_expanded' => "#000000",
            'plugin_small_text_colour_expanded' => "#000000",
            'button_border_colour'              => "#2AA43C",
            'button_background_colour'          => "#2AA43C",
            'button_text_colour'                => "#ffffff",
            'button_plus_icon_colour'           => "#ffffff",
            'button_background_colour_selected' => "#ffffff",
            'button_text_colour_selected'       => "#000",
            'button_checkmark_icon_selected'    => "#2AA43C",
            'curbon_logo'                  => 'standard',
            'curbon_product_image'         => 'standard',
        );

        update_option('curbon_look_and_feel_options', $curbon_look_and_feel); 
    }


    /*
    * Creating carbon offset product on plugin activation
    */
    public function curbon_create_carbon_offset_product_callback()
    {
        
        /*get the onboarding status on plugin activation and add the product id to this array below*/
        $curbon_onboarding_status = get_option('curbon-onboarding-status');
            
            $id = "";

            /*get the price from the curbon setting options*/
            $curbon_settings_options = get_option('curbon_settings_options');
            $product_price = $curbon_settings_options['curbon_offset_amount'];

            /*
                check whether product exists or not. If product exists then update the details
            */
            $args = array(
                'post_type'     => "product",
                'post_status'   => "publish",
                'meta_query' => array(
                    array(
                        'key'     => 'curbon_is_offset_product',
                        'value'   => true,
                        'compare' => '=',
                    ),
                ),
            );

            $the_query = new WP_Query($args);
        
            // The Loop
            if ($the_query->have_posts() ) :
                while ( $the_query->have_posts() ) : $the_query->the_post();
                    $id =  get_the_ID();
                endwhile;
            endif;

            // Reset Post Data
            wp_reset_postdata();

            if($id) {
                /*Update existing offset*/
                wp_update_post(
                    array(
                        'ID'           => $id,
                        'post_title'    => "Curbon", 
                        'post_content'  => "<p>Curbon's carbon offsets help neutralize the carbon emissions from your purchase.</p><p>Your contribution helps funds forest restoration, tree planting, and clean energy projects that fight climate change.</p><p>All it takes is a single click at the checkout.</p>",
                        'post_status'   => "publish"
                    ) 
                );
            }else{
                /*Insert new offset*/
                $id = wp_insert_post(
                    array(
                                'post_title'    => "Curbon", 
                                'post_name'     => "curbon",
                                'post_content'  => "<p>Curbon's carbon offsets help neutralize the carbon emissions from your purchase.</p><p>Your contribution helps funds forest restoration, tree planting, and clean energy projects that fight climate change.</p><p>All it takes is a single click at the checkout.</p>",
                                'post_type'     => "product",
                                'post_status'   => "publish"
                            )
                );
            }
            
            // $sku='curbon-carbon-offset';
            $sku='curbon-offset';

            /*Taxable Offset Logic Start Here*/
            $response_array = array();
            // The country/state
            $store_raw_country = get_option('woocommerce_default_country');

            // Split the country/state
            $split_country = explode(":", $store_raw_country);

            // Country and state separated:
            $store_country = $split_country[0];
            $store_state   = $split_country[1];

            $curbon_countries = $this->curbon_countries();
            foreach ($curbon_countries['data'] as $key => $data)
            {
                if ($data['country_alpha2'] == $store_country ) {
                    $response_array = $data;
                }
            }
            
            if(!empty($response_array) ) {
                if(!$response_array['taxable'] ) {
                    update_post_meta($id, '_tax_status', 'none');
                    update_post_meta($id, '_tax_class', 'zero-rate');        
                }
            }
            /*Taxable Offset Logic End Here*/

            update_post_meta($id, '_sku', $sku);
            update_post_meta($id, '_visibility', 'hidden');
            update_post_meta($id, '_regular_price', 0);
            update_post_meta($id, '_price', $product_price);
            update_post_meta($id, 'curbon_is_offset_product', true);
            
            $curbon_onboarding_status['carbon_offset_product_id'] = $id;

            /*Update onboarding option with the product id*/
            update_option('curbon-onboarding-status', $curbon_onboarding_status);

            // $terms = array( 'exclude-from-catalog', 'exclude-from-search');
            $product = wc_get_product($id);
            $product->set_catalog_visibility('search');
            $product->save();
            
            $this->curbon_generate_featured_image(CURBON_PLUGIN_URL.'/assets/images/carbon-offset-thumbnail.png', $id);

    }


    /*
    * Redirect to onboarding process on plugin activation
    */
    public function curbon_redirect_on_activation_callback()
    {
        
        /*
        * If the onboarding status is complete then redirect to dashboard page. If the status is pending then redirect to onboarding page
        */
        $curbon_onboarding_status = get_option('curbon-onboarding-status');

        $redirect_url =  get_admin_url().'admin.php?page=curbon-dashboard&tab=curbon-onboarding';
        
        if(isset($curbon_onboarding_status['status']) &&  $curbon_onboarding_status['status'] == 'complete' ) {
            $redirect_url =  get_admin_url().'admin.php?page=curbon-dashboard&tab=dashboard';
        }

        wp_redirect($redirect_url);
        exit();

    }


    /*
    * This function is used to generate the featured image for the product that we are generating on activation
    */
    public function curbon_generate_featured_image( $image_url, $post_id  )
    {
        $upload_dir = wp_upload_dir();
        $image_data = wp_remote_retrieve_body( wp_remote_get( $image_url ) );
        $filename   = basename($image_url);
        
        if(wp_mkdir_p($upload_dir['path'])) {
            $file   = $upload_dir['path'] . '/' . $filename;
        }else{
            $file   = $upload_dir['basedir'] . '/' . $filename;
        }

        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);
        
        $attachment = array(
            'post_mime_type'    => $wp_filetype['type'],
            'post_title'        => sanitize_file_name($filename),
            'post_content'      => '',
            'post_status'       => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $file, $post_id);

        include_once ABSPATH . 'wp-admin/includes/image.php';
        
        $attach_data    = wp_generate_attachment_metadata($attach_id, $file);
        
        /*default black image is getting uploaded*/
        update_post_meta($post_id, 'curbon_carbon_product_image_as_featured', 'black');

        set_post_thumbnail($post_id, $attach_id);
    }
} // end of class CURBON_Registration_Activation_Init

$curbon_registration_activation_init = new CURBON_Registration_Activation_Init;