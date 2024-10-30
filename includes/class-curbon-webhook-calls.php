<?php

/**
 * Curbon Webhook calls
 * Responsible to gather data and process from Laravel
 * php version 7.4

 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @version  GIT: @1.0.0@
 * @link     https://curbon.io/
 */


/**
 * POST URL : SITEURL/wp-json/curbon_api/v1/curbon-webhook
 *
 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @link     https://curbon.io/
 */
class CURBON_Rest_Server extends WP_REST_Controller
{
    private $_api_namespace;
    private $_api_version;

    /**
     * Constructor of the function.
     * 
     * @return NULL
     */
    public function __construct()
    {
        $this->_api_namespace = 'curbon_api/v';
        $this->_api_version = '1';
        $this->init();
    }
    
    /**
     * Registering routes for webook
     * 
     * @return NULL
     */
    public function registerRoutes()
    {
        $namespace = $this->_api_namespace . $this->_api_version;
        
        register_rest_route(
            $namespace, '/curbon-webhook', array(
                array( 
                    'methods' => WP_REST_Server::EDITABLE, 
                    'callback' => array( 
                                    $this, 
                                    'curbonWebhookCallback' 
                                ), 
                ),
            )  
        );
    }


    /**
     * Register our REST Server
     *
     * @return NULL 
     */
    public function init()
    {
        add_action('rest_api_init', array( $this, 'registerRoutes' ));
    }
    

    /**
     * Responsible for handling webhooks from Laravel
     * 
     * @param $request handles webhook requests
     * 
     * @return NULL
     */
    public function curbonWebhookCallback( WP_REST_Request $request )
    {
        $creds         = array();
        $headers     = getallheaders();
        $get_params = $request->get_params();
        
        $Authorization = $headers['Authorization'];
        $Authorization = explode(" ", $Authorization);
        $Authorization = $Authorization[1];

        $curbon_laravel_api_access_token = get_option(
            'curbon_laravel_api_tokens'
        )['access_token'];

        if (trim($Authorization) == $curbon_laravel_api_access_token ) {
            
            $action_type     = $get_params['event'];
            $message         = $get_params['message'];

            $is_action_done = false;

            if ($action_type == "charge.success" 
                || $action_type == "invoice.create"
            ) {
                
                update_option('curbon-charge-status', 'paid');
                $global_notice = get_option('curbon-global-notice');
                $global_notice['payment_failure'] = "";
                update_option('curbon-global-notice', $global_notice);

                $is_action_done = true;

            } else if ($action_type == "invoice.payment_failed" ) {
                
                update_option('curbon-charge-status', 'fail');
                $global_notice = get_option('curbon-global-notice');
                $global_notice['payment_failure'] = $message;
                update_option('curbon-global-notice', $global_notice);    

                $is_action_done = true;

            } else if ($action_type == 'blocked'  
                || $action_type == 'customer_source_expiring' 
            ) {

                update_option('curbon-shop-status', 'blocked');
                $global_notice = get_option('curbon-global-notice');
                $global_notice['curbon_shop_status'] = $message;
                update_option('curbon-global-notice', $global_notice);    

                $is_action_done = true;

            } else if ($action_type == 'unblocked' ) {
                
                update_option('curbon-shop-status', '');
                $global_notice = get_option('curbon-global-notice');
                $global_notice['curbon_shop_status'] = "";
                update_option('curbon-global-notice', $global_notice);    

                $is_action_done = true;
                
            }
            
            return array( 'sucess' => $is_action_done );
        } else {
            return 
                new WP_Error(
                    'invalid-method', 
                    'You must specify a valid username and password.',
                    array( 'status' => 400 /* Bad Request */ )
                );
        }
    }
}
 
$CURBON_Rest_Server = new CURBON_Rest_Server();
?>