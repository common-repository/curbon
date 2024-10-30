<?php
/**
 * Admin init functions for Offsets
 * Initing the Admin side functionalities
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
 * Admin functionalities
 * 
 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @link     https://curbon.io/
 */

class CURBON_Admin_Init
{

    public function __construct() //phpcs:ignore
    {
        add_action('admin_menu', array( $this, 'addAdminMenu' ));
    }

    /**
     * Adding Admin Menu Page
     * 
     * @return NULL
     */
    public function addAdminMenu()
    {
        add_menu_page(
            esc_html__('Curbon', 'wc-curbon'),
            esc_html__('Curbon', 'wc-curbon'),
            'manage_options',
            'curbon-dashboard',
            array( $this, 'adminSettingPage' ),
            CURBON_PLUGIN_URL . 'assets/images/curbon-icon.svg',
            null
        );
    }

    /**
     * Admin general setting page.
     * 
     * @return NULL
     */
    public function adminSettingPage()
    {
        include_once CURBON_PLUGIN_PATH . 'includes/admin/views/admin-dashboard-page.php'; //phpcs:ignore
    }

    /**
     * Encrption function
     * 
     * @param $plaindata plaindata to ecrypt
     * 
     * @return $ciphertext
     */
    public function curbonEncryptData($plaindata)
    {
        //$key previously generated safely, ie: openssl_random_pseudo_bytes
        $ivlen          = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv             = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt(
            $plaindata, 
            $cipher, 
            $key, 
            $options=OPENSSL_RAW_DATA, 
            $iv
        );
        $hmac           = hash_hmac(
            'sha256', 
            $ciphertext_raw, 
            $key, 
            $as_binary=true
        );
        $ciphertext     = base64_encode($iv.$hmac.$ciphertext_raw);
        return $ciphertext;
    }

    /**
     * Decryption function
     * 
     * @param $ciphertext Ciphertext to decrypt
     * 
     * @return $original_plaintext|bool 
     */
    public function curbonEecryptData($ciphertext)
    {
        $c              = base64_decode($ciphertext);
        $ivlen          = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv             = substr($c, 0, $ivlen);
        $hmac           = substr($c, $ivlen, $sha2len=32);
        $ciphertext_raw = substr($c, $ivlen+$sha2len);
        $original_plaintext = openssl_decrypt(
            $ciphertext_raw, 
            $cipher, 
            $key, 
            $options=OPENSSL_RAW_DATA, 
            $iv
        );
        $calcmac        = hash_hmac(
            'sha256', 
            $ciphertext_raw, 
            $key, 
            $as_binary=true
        );
        if (hash_equals($hmac, $calcmac)) {
            return $original_plaintext;
        }
        return false;
    }
    

    /**
     * Manipulation of order data for dashboard
     * 
     * @param $key define key which will be stored for Offset Order
     * 
     * @return $orders_with_offset|NULL 
     */
    public function getOrderDataForDashboard($key = "orders_with_offsets")
    {

        $CURBON_Carbonclick_Laravel_API    
            = new CURBON_Carbonclick_Laravel_API();
        $fetch_card_response 
            = $CURBON_Carbonclick_Laravel_API->curbonFetchCustomer();    

        /*Merchant Count*/        
        if ($key == 'merchant_count') {

             return $fetch_card_response['merchants'];

        } else if ($key == "offsets_collected") {
            
            /*Offset Collected*/ 
            if ($fetch_card_response['offsets'] ) {
                return get_woocommerce_currency_symbol().$fetch_card_response['offsets']; // phpcs:ignore
            }
            return 'N/A';

        } else if ($key == "orders_with_offsets") {

            /*Get Count of All Orders*/
            $args = array(
                'post_status'       => 'any',
                'post_type'         => 'shop_order',
                'posts_per_page'    => 1,
            );

            $total_orders       = new WP_Query($args);
            $total_orders_count = $total_orders->found_posts;
            wp_reset_postdata();

            $orders_with_offset_count   = $fetch_card_response['orders'];

            if ($total_orders_count ) {
                $orders_with_offsets 
                    = round(
                        (($orders_with_offset_count / $total_orders_count) * 100),
                        2
                    ).'%';
                if ($orders_with_offsets) {
                    return $orders_with_offsets;
                }    
            }
            
            return 'N/A';
        }
    }


    /** 
     * Manipulation of order data for dashboard
     * 
     * @return $order_ids "Order ID with failed offset charge"
     */
    public function getOrderIdsWhereChargeFail()
    {

        $order_ids = array();

        $args = array(
            'post_status'       => 'any',
            'post_type'         => 'shop_order',
            'posts_per_page'    => -1,
        );

        /*Get Count of Orders having is_curbon_deducted no*/
        $args['meta_query'] =   array(
                                    'relation'        => 'AND',
                                        array(
                                            'key'     => '_is_curbon_deducted',
                                            'value'   => 'no',
                                            'compare' => '='
                                        )
                                );


        $orders_with_offset  = new WP_Query($args);

        if ($orders_with_offset->have_posts()) {

            while ($orders_with_offset->have_posts()) : $orders_with_offset->the_post(); // phpcs:ignore

                global $post;
                $order_ids[] = $post->ID;
                
            endwhile; 
        }

        wp_reset_postdata();

        return $order_ids;
    }
    
    /**
     * Check if the store has SSL enabled
     *
     * @return bool "if the site had ssl activated or not"
     */
    public function curbonMaybeIsSsl()
    {
        // cloudflare
        if (! empty($_SERVER['HTTP_CF_VISITOR']) ) {
            $cfo = json_decode($_SERVER['HTTP_CF_VISITOR']);
            if (isset($cfo->scheme) && 'https' === $cfo->scheme ) {
                return true;
            }
        }
     
        // other proxy
        if (! empty($_SERVER['HTTP_X_FORWARDED_PROTO']) 
            && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] 
        ) {
            return true;
        }
     
        return function_exists('is_ssl') ? is_ssl() : false;
    }
    
} // End of CURBON_Admin_Init class

// Init the class
$curbon_admin_init = new CURBON_Admin_Init;