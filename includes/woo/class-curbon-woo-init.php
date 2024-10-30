<?php
/**
 * WooCommerce Init functions needed to handle curbon offset
 * Actions and filters for managing curbon offset
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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * CURBON_WooCommerce_Init class responsable to load all the scripts and styles.
 * 
 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @link     https://curbon.io/
 */
class CURBON_WooCommerce_Init
{

    public function __construct() // phpcs:ignore
    {
        $this->initHooks();
    }

    /**
     * Initialize woocommerce hooks
     * 
     * @return NULL
     */
    public function initHooks()
    {
        
        $curbon_settings_options    = get_option('curbon_settings_options');
        $enable_widget              = isset($curbon_settings_options['widget']) ? $curbon_settings_options['widget'] : '';

        $curbon_widget_location_on_cart        
            = isset($curbon_settings_options['cart-page-button']) ? $curbon_settings_options['cart-page-button'] : '' ;
        $curbon_widget_location_on_mini_cart   
            = isset($curbon_settings_options['mini-cart-page-button']) ? $curbon_settings_options['mini-cart-page-button'] : '' ;
        $curbon_widget_location_on_checkout    
            = isset($curbon_settings_options['checkout-page-button']) ? $curbon_settings_options['checkout-page-button']: '';

        $curbon_shop_status = get_option('curbon-shop-status');

        // $curbon_offset_amount      = $curbon_settings_options['topup-amount'];
        
        if (isset($_GET['curbon']) && $_GET['curbon'] == true ) {
            $enable_widget = true;   
        }
        
        if ($enable_widget ) {
            
            add_action(
                'pre_get_posts', 
                array( 
                    $this, 
                    'curbonHideCarbonOffsetFromExternalAccessCallback' 
                )
            );
            
            add_action(
                'wp', 
                array( 
                    $this, 
                    'curbonAddCarbonOffsetToCartOnBtnClickCallback' 
                )
            );
            
            add_action(
                'wp', 
                array( 
                    $this, 
                    'curbonRemoveCarbonOffsetFromCartOnBtnClickCallback' 
                )
            );

            add_filter(
                'woocommerce_cart_totals_after_order_total', 
                array( 
                    $this, 
                    'filterWoocommerceUpdateCartActionCartUpdated' 
                ), 
                10, 
                1
            );

            add_action(
                'wp',
                array(
                    $this,
                    'curbonSetShortcode'
                )
            );

            if ($curbon_widget_location_on_cart ) {
                add_action(
                    'woocommerce_after_cart_table', 
                    array( 
                        $this, 
                        'curbonAddCarbonOffsetBtnOnCartPageCallback' 
                    )
                );
            }

            if ($curbon_widget_location_on_checkout ) {
                add_action(
                    'woocommerce_before_checkout_form', 
                    array( 
                        $this, 
                        'curbonAddCarbonOffsetBtnOnCartPageCallback' 
                    )
                );
            }
            
            if ($curbon_widget_location_on_mini_cart ) {
                add_action(
                    'woocommerce_mini_cart_contents', 
                    array( 
                        $this, 
                        'curbonAddCarbonOffsetBtnOnMiniCartCallback' 
                    )
                );

            }
            
            add_action(
                'woocommerce_thankyou', 
                array( 
                    $this, 
                    'curbonCheckOrderAndManageOffsetCallback' 
                )
            );

            add_action(
                'woocommerce_quantity_input_max', 
                array( 
                    $this, 
                    'curbonWoocommerceQuantityMax100Callback' 
                ), 
                9999, 
                2
            );
            
            add_filter(
                'woocommerce_cart_item_quantity', 
                array( 
                    $this, 
                    'curbonWoocommerceQuantityMax100InCartCallback' 
                ), 
                9999, 
                3
            );

            add_filter(
                'woocommerce_coupon_get_discount_amount', 
                array( 
                    $this, 
                    'curbonZeroDiscountForOffsetCallback' 
                ), 
                12, 
                5
            );

            add_filter( 
                'wp_kses_allowed_html',
                array( 
                    $this, 
                    'curbonextendAllowedTags' 
                ),10, 2 );

        } else {
            add_action('wp', array( $this, 'removeOffsetIfWidgetDisableCallback' ));
        }

        if ('blocked' == $curbon_shop_status ) {
            $_SESSION['curbon_box_status'] = '';
            add_action('wp', array( $this, 'removeOffsetIfWidgetDisableCallback' ));
        }
        
        add_filter(
            'manage_edit-shop_order_columns', 
            array( 
                $this, 
                'curbonAdminOrderHasOffsetColumnCallback' 
            )
        );
        
        add_action(
            'manage_shop_order_posts_custom_column', 
            array( 
                $this, 
                'curbonAdminOrderHasOffsetColumnContentCallback' 
            )
        );
        
        add_action(
            'woocommerce_order_refunded', 
            array( 
                $this, 
                'curbonOrderWithOffsetRefundedCallback' 
            ), 
            10, 
            2
        ); 

    }

    /**
     * Registering Shortcode
     * 
     * @return NULL register shortcode to use it anywhere
     */
    public function curbonSetShortcode()
    {
        add_shortcode(
            'curbon-offset-box', 
            array( 
                $this, 
                'curbonOffsetBoxOnShortcode' 
            )
        );
    }

    /**
     * Render shortcode for Curbon Offset Box
     * 
     * @return NULL rendernig through shortcode
     */
    public function curbonOffsetBoxOnShortcode()
    {
        global $woocommerce;

        $curbon_onboarding_status  = get_option('curbon-onboarding-status');
        $product_id = $curbon_onboarding_status['carbon_offset_product_id'];

        if ($product_id) {

            $product_cart_id = $woocommerce->cart->generate_cart_id($product_id);
            
            if (is_checkout()) {
                echo "<form method='post'>";
            }

            $active_theme_textdomain = $this->getActiveThemeTextdomainCallback();
            $theme_template_path = CURBON_PLUGIN_PATH . 
                'includes/woo/themes-template/'.
                    $active_theme_textdomain.
                '-cart-html.php';

            if (file_exists($theme_template_path)) {
                include_once $theme_template_path;
            } else {
                include_once CURBON_PLUGIN_PATH . 
                'includes/woo/themes-template/default-cart-html.php';
            }

            if (is_checkout()) {
                echo "</form>";
            }
        }
    }

    /**
     * Remove offset from the cart if widget is disable
     * 
     * @return NULL removing offset if widget is disabled
     */
    public function removeOffsetIfWidgetDisableCallback()
    {
        
        $carbon_offset_product_id = get_option('carbon_offset_product_id');

        if (isset($carbon_offset_product_id) ) {
            if (!is_admin()) {

                $_SESSION['curbon_box_status'] = '';

                $cartId = WC()->cart->generate_cart_id($carbon_offset_product_id);
            
                $cartItemKey = WC()->cart->find_product_in_cart($cartId);
            
                WC()->cart->remove_cart_item($cartItemKey); 
            }
        }
    }
    
    /**
     * Logic implementation for order refunded having offset value
     * 
     * @param $order_id  current order id
     * @param $refund_id current refund id
     * 
     * @return NULL triggering refund process to Laravel
     */
    public function curbonOrderWithOffsetRefundedCallback( $order_id, $refund_id ) // phpcs:ignore
    {

        global $wpdb;
        
        $order_has_offset = get_post_meta($order_id, '_order_has_offset', true);

        if ($order_has_offset && $order_has_offset== "yes"  ) {

            $curbon_onboarding_status 
                = get_option('curbon-onboarding-status');
            $carbon_offset_product_id 
                = wc_get_product_id_by_sku('curbon-offset');;

            $order = wc_get_order($order_id);

            // Get the Order refunds (array of refunds)
            $order_refunds = $order->get_refunds();
            
            $total_offset_amount_refunded   = $refund_reason = "Order cancelled";
            $current_refund_amount_bool     = true;
            $offset_amount = $current_refund_amount = 0;

            // Loop through the order refunds array
            foreach ( $order_refunds as $refund ) {
                // Loop through the order refund line items
                foreach ( $refund->get_items() as $item_id => $item ) {


                    $refunded_quantity      = $item->get_quantity();
                    $refunded_line_subtotal = $item->get_subtotal();
                    $refunded_product_id    = $item->get_product_id();

                    if ($refunded_product_id 
                        && $refunded_product_id == $carbon_offset_product_id 
                    ) {
                        
                        if ($current_refund_amount_bool ) {
                            $current_refund_amount = $refunded_line_subtotal;
                            $current_refund_amount_bool = false;
                            $refund_reason = $refund->get_reason() 
                                ? $refund->get_reason() 
                                : __('customer', 'woocommerce');
                        } else {
                            $total_offset_amount_refunded += $refunded_line_subtotal;
                        }
                    }
                }
            }

            // Loop through the order items array
            $order_items = $order->get_items();
            foreach ( $order_items as $item_id => $item ) {

                $product_id = $item->get_variation_id() 
                    ? $item->get_variation_id() 
                    : $item->get_product_id();

                if ($product_id 
                    && $product_id == $carbon_offset_product_id 
                ) {

                        $offset_amount = $item->get_subtotal();
                }
            }

            $payment_method = get_post_meta($order_id, '_payment_method');

            if ($current_refund_amount > 0 || 'cod' == $payment_method[0] ) {
                /*
                    $current_refund_amount
                    $total_offset_amount_refunded
                    $offset_amount
                */

                $args = array(
                        "number"        => $order_id,
                        "cancel_reason" => $refund_reason
                        );

                $CURBON_Carbonclick_Laravel_API 
                    = new CURBON_Carbonclick_Laravel_API();
                $refund_response 
                    = $CURBON_Carbonclick_Laravel_API->curbonRefund($args);
                
                if (!empty($refund_response['success']) 
                    && ( $refund_response['success'] == true 
                    || $refund_response['success'] == 1) 
                ) {
                    
                    update_post_meta($order_id, '_order_has_offset', 'no');
                }

            }
        }
    }


    /**
     * Exclude offset from all woocommerce coupons
     * 
     * @param $discount           get the discount applied
     * @param $discounting_amount get the discount amount
     * @param $cart_item          get the offset item
     * @param $single             check ig single page
     * @param $coupon             get the coupon applied
     * 
     * @return $dicount 0 discount for offset product
     */
    public function curbonZeroDiscountForOffsetCallback( $discount, $discounting_amount, $cart_item, $single, $coupon ) // phpcs:ignore
    {
        $curbon_onboarding_status = get_option('curbon-onboarding-status');
        $carbon_offset_product_id 
            = $curbon_onboarding_status['carbon_offset_product_id'];
            
        if ($cart_item['product_id'] == $carbon_offset_product_id  ) {
            $discount = 0;
        }

        return $discount;
    }


    /**
     * Limit maximum amount of product to be updated as 100 on single product page
     * 
     * @param $max     maximum anount of product
     * @param $product offset product
     * 
     * @return $max restricting updating cart with max quantity
     */
    public function curbonWoocommerceQuantityMax100Callback( $max, $product )
    {

        if (is_product() ) {
            $curbon_onboarding_status = get_option('curbon-onboarding-status');
            $carbon_offset_product_id 
                = $curbon_onboarding_status['carbon_offset_product_id'];

            if ($carbon_offset_product_id === $product->get_id() ) {
                $max = ceil(100 / $product->get_price());
            }
        }

        return $max;
    }


    /**
     * Limit maximum amount of product to be updated as 100 on cart page
     * 
     * @param $product_quantity Current quantity of offset in cart
     * @param $cart_item_key    Current item key
     * @param $cart_item        Current item in cart
     * 
     * @return $product_quantity updated quantity of offset
     */
    public function curbonWoocommerceQuantityMax100InCartCallback( $product_quantity, $cart_item_key, $cart_item ) // phpcs:ignore
    {

        $_product = apply_filters(
            'woocommerce_cart_item_product', 
            $cart_item['data'], 
            $cart_item, 
            $cart_item_key
        );

        $max = 0;

        $curbon_onboarding_status = get_option('curbon-onboarding-status');
        $carbon_offset_product_id 
            = $curbon_onboarding_status['carbon_offset_product_id'];
        
        if ($carbon_offset_product_id === $_product->get_id() ) {
            $max = ceil(100 / $_product->get_price());
        }

        $product_quantity = woocommerce_quantity_input(
            array(
            'input_name'   => "cart[{$cart_item_key}][qty]",
            'input_value'  => $cart_item['quantity'],
            'max_value'    => $max,
            'min_value'    => $_product->get_min_purchase_quantity(),
            'product_name' => $_product->get_name(),
            ), $_product, false 
        );

        return $product_quantity;

    }


    /**
     * This is used to remove product to be listed in wp rest api
     * 
     * @param $query get the query of posts
     * 
     * @return NULL tweaked current query to hide offset product
     */
    public function curbonHideCarbonOffsetFromExternalAccessCallback( $query = false ) // phpcs:ignore
    {
        if (! is_admin() 
            && isset($query->query['post_type']) 
            && $query->query['post_type'] === 'product' 
        ) {
            
            $tax_query = array();
            
            if ($query->get('tax_query')) {
                $tax_query = $query->get('tax_query');
            }
            
            $tax_query[] = array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'name',
                    'terms'    => 'exclude-from-catalog',
                    'operator' => 'NOT IN',
                ),
                array(
                
                    'taxonomy' => 'product_visibility',
                    'field'    => 'name',
                    'terms'    => 'exclude-from-catalog',
                    'operator' => '!=',
                ),
            );

            $query->set('tax_query', $tax_query);
        }
    }

    /**
     * This function is used to display, offset button on cart page as per condition
     * 
     * @return NULL Add offset button to current theme
     */
    public function curbonAddCarbonOffsetBtnOnCartPageCallback()
    {
        
        

        $curbon_onboarding_status  = get_option('curbon-onboarding-status');
        $product_id = $curbon_onboarding_status['carbon_offset_product_id'];

        if ($product_id) {

            $product_cart_id = WC()->cart->generate_cart_id($product_id);
            
            if (is_checkout()) {
                echo "<form method='post'>";
            }

            $active_theme_textdomain = $this->getActiveThemeTextdomainCallback();
            $theme_template_path = CURBON_PLUGIN_PATH . 
                'includes/woo/themes-template/'.
                    $active_theme_textdomain.
                '-cart-html.php';

            if (file_exists($theme_template_path)) {
                include_once $theme_template_path;
            } else {
                include_once CURBON_PLUGIN_PATH . 
                'includes/woo/themes-template/default-cart-html.php';
            }

            if (is_checkout()) {
                echo "</form>";
            }
        }

    }

    /**
     * Add offset button on mini cart page as per condition
     * 
     * @return NULL adding Mini cart HTML in current theme
     */
    public function curbonAddCarbonOffsetBtnOnMiniCartCallback()
    {
        
        $curbon_onboarding_status  = get_option('curbon-onboarding-status');
        $product_id = $curbon_onboarding_status['carbon_offset_product_id'];
        
        if ($product_id) {
            $product_cart_id = WC()->cart->generate_cart_id($product_id);
            
            $active_theme_textdomain = $this->getActiveThemeTextdomainCallback();
            
            $theme_template_path = CURBON_PLUGIN_PATH . 
                'includes/woo/themes-template/mini-cart/'.
                    $active_theme_textdomain.
                '-mini-cart-html.php';

            if (file_exists($theme_template_path)) {
                include_once $theme_template_path;
            } else {
                include_once CURBON_PLUGIN_PATH . 
                'includes/woo/themes-template/mini-cart/default-mini-cart-html.php';
            }
        }
    }


    /**
     * This function is used to add carbon offset product to the cart,
     * when user click on Offset Button located below cart table
     * 
     * @return NULL Adding offset to cart
     */
    public function curbonAddCarbonOffsetToCartOnBtnClickCallback()
    {
        
        if (is_cart()) {
            /*Last Impression update shop info*/
            $args  = array(
                'last_impression' => true
                );
            $CURBON_Carbonclick_Laravel_API 
                = new CURBON_Carbonclick_Laravel_API();

            $CURBON_Carbonclick_Laravel_API->curbon_update_shop_info($args);

        }

        if (! isset($_POST['curbon_add_carbon_offset_button'])   
            || ! wp_verify_nonce(
                $_REQUEST['curbon_add_carbon_offset_button_nonce_field'],
                'curbon_add_carbon_offset'
            )
        ) {
            return;
        }

        $curbon_onboarding_status   = get_option('curbon-onboarding-status');
        $product_id                 = wc_get_product_id_by_sku('curbon-offset');

        update_post_meta(
            $product_id, 
            '_price', 
            sanitize_text_field($_POST['curbon_add_carbon_offset_button'])
        );

        if ($product_id) {
            $product_cart_id    = WC()->cart->generate_cart_id($product_id);

            if (! WC()->cart->find_product_in_cart($product_cart_id) ) {
                // Yep, the product with ID is NOT in the cart, let's add it then!
                WC()->cart->add_to_cart($product_id);
                $_SESSION['curbon_box_status'] = 'in';
            }     
        }
    }

    /**
     * Sync the offset price on-the-fly when cart is updated
     * 
     * @param $cart_updated Check if cart_update action happened
     * 
     * @return $cart_updated updated cart object
     */
    public function filterWoocommerceUpdateCartActionCartUpdated( $cart_updated )
    {
        global $woocommerce;  

        $curbon_laravel_api_access_token = get_option(
            'curbon_laravel_api_tokens'
        )['access_token'];
        $product_id = wc_get_product_id_by_sku('curbon-offset');
        $offset_price = get_post_meta($product_id, '_price')[0];

        /** WP REMOTE GET **/

        $headers = array(
            "Accept"        => "application/json",
            "Content-Type"  => "application/json",
            "Authorization" =>  "Bearer ". $curbon_laravel_api_access_token
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

        $fetch_customer_response   = wp_remote_retrieve_body($response);
        $fetch_customer_response   = json_decode($fetch_customer_response, true);

        /** WP REMOTE GET **/

        $curbon_offset_percentage 
            = $fetch_customer_response['carbon_offset_percentage'];

        $cart_total     = $woocommerce->cart->total;
        $curbon_offset  = number_format(
            (float)( 
                ( ( $cart_total - $offset_price) * $curbon_offset_percentage ) / 100 
            ), 
            2, 
            '.', 
            ''
        );

        update_post_meta($product_id, '_price', $curbon_offset);

        return $cart_updated;
    }

    /**
     * This function is used to remove carbon offset product from the cart,
     * when user click on Thank you Offset Button located below cart table
     *
     * @return NULL removing curbon offset
     */
    public function curbonRemoveCarbonOffsetFromCartOnBtnClickCallback()
    {
        
        if (! isset($_POST['curbon_remove_carbon_offset_button'])   
            || !wp_verify_nonce(
                $_REQUEST['curbon_remove_carbon_offset_button_nonce_field'], 
                'curbon_remove_carbon_offset_button_nonce'
            )
        ) {
            return;
        }

        $curbon_onboarding_status  = get_option('curbon-onboarding-status');
        $product_id = $curbon_onboarding_status['carbon_offset_product_id'];

        if ($product_id) {
            $product_cart_id    = WC()->cart->generate_cart_id($product_id);
            $cart_item_key      = WC()->cart->find_product_in_cart($product_cart_id);

            if ($cart_item_key ) { 
                WC()->cart->remove_cart_item($cart_item_key);
            }
        }
    }

    
    /**
     * This function is used add column to the orders listing in admin 
     * to easily identify whether order has offset or not
     *
     * @param $columns Curret available columns in WooCommerce orders
     * 
     * @return $columns Current columns + Offset columns
     */
    public function curbonAdminOrderHasOffsetColumnCallback( $columns )
    {
        $columns['has_offset'] = 'Has Offset?';
        return $columns;
    }

    /**
     * Get current theme details. 
     * This is used to support few theme cart template. 
     * Based on active theme we will display the cart layout to meet the theme
     * 
     * @return $active_theme_textdomain current theme's textdomain
     */
    public function getActiveThemeTextdomainCallback()
    {

        $active_theme_details = wp_get_theme();
        
        $active_theme_textdomain = esc_html(
            $active_theme_details->get('TextDomain')
        );
        
        if (!$active_theme_textdomain) { 
            //The child theme is active. You need to fetch the "Template"
            $active_theme_textdomain = esc_html(
                $active_theme_details->get('Template')
            );
            
            if (!$active_theme_textdomain) { 
                $active_theme_textdomain = esc_html(
                    $active_theme_details->get('Name')
                );
            }
            
        }

        return $active_theme_textdomain;
    }

    /**
     * This function is used add column content to the orders listing in admin
     * 
     * @param $column current column to show data
     * 
     * @return NULL Yes/No if offset is there in order
     */ 
    public function curbonAdminOrderHasOffsetColumnContentCallback( $column )
    {

        global $post, $the_order;

        if ('has_offset' === $column ) {

            $order_has_offset = get_post_meta($post->ID, '_order_has_offset', true);

            if ($order_has_offset 
                && $order_has_offset == "yes" 
                && get_post_meta($post->ID, '_purchase_api_status', true) == "SUCCESS" //phpcs:ignore
            ) {
                echo '<mark class="order-status status-processing">
                        <span>Yes</span>
                    </mark>';
            } else {
                echo '<mark class="order-status status-failed">
                        <span>No</span>
                    </mark>';
            }
        }
    }


    /**
     * This function is used to check order has offset and 
     * if it has offset then call purchase api
     * 
     * @param $order_id Current Order ID to manage
     * 
     * @return NULL
     */
    public function curbonCheckOrderAndManageOffsetCallback( $order_id )
    {
        global $wpdb;

        $curbon_onboarding_status       = get_option('curbon-onboarding-status');
        $carbon_offset_product_id       = get_option('carbon_offset_product_id');
        $carbon_settings_options        = get_option('curbon_settings_options');
        
        $CURBON_Carbonclick_Laravel_API    = new CURBON_Carbonclick_Laravel_API();
        $curbon_update_shop_info           = $CURBON_Carbonclick_Laravel_API
                                                ->curbon_update_shop_info();

        update_post_meta($order_id, '_is_curbon_enable', "yes");
        
        /*Update shop info with order count*/
        $CURBON_Carbonclick_Laravel_API->wooOrderCountWhenWidgetEnable();

        $order = wc_get_order($order_id);
        $items = $order->get_items();

        foreach ( $items as $item_id => $item ) {

            $product_id 
                = $item->get_variation_id() ? 
                $item->get_variation_id() : 
                $item->get_product_id();

            if (( $product_id 
                && $product_id == $carbon_offset_product_id ) 
                || 'on' == $carbon_settings_options['auto_debit_offset'] 
            ) {




                //if($order->get_transaction_id()){
                    $product        = $item->get_product();
                    $active_price   = $product->get_price();
                    $regular_price  = $product->get_sale_price();
                    $sale_price     = $product->get_regular_price();
                    $item_quantity  = $item->get_quantity(); // Get the item quantit

                    $paymentReference   = rand();//$order->get_transaction_id();
                    $payment_method_title = $order->get_payment_method_title();
                    $paymentProviderId  = $order->get_payment_method();
                    $total_tax          = $item->get_total_tax();
                    $offset_total       = $item->get_total();
                    $amount_currency    = $order->get_currency();
                    
                    $billing_first_name = $order->get_billing_first_name();
                    $billing_last_name  = $order->get_billing_last_name();
                    $billing_company    = $order->get_billing_company();
                    $billing_address_1  = $order->get_billing_address_1();
                    $billing_address_2  = $order->get_billing_address_2();
                    $billing_city       = $order->get_billing_city();
                    $billing_state      = $order->get_billing_state();
                    $billing_postcode   = $order->get_billing_postcode();
                    $billing_country    = $order->get_billing_country();
                    $billing_email      = $order->get_billing_email();
                    $billing_phone      = $order->get_billing_phone();
                    $order_received_url = $order->get_checkout_order_received_url();
                    

                if (!get_post_meta($order_id, '_order_has_offset', true)  ) {

                    update_post_meta(
                        $order_id, 
                        '_carbon_offset_product_id', 
                        $product_id
                    ); 
                    
                    update_post_meta(
                        $order_id, 
                        '_order_has_offset', 
                        "yes"
                    );

                    $curbon_api    = new CURBON_Carbonclick_Laravel_API();
                    $fetch_card_response    = $curbon_api->curbonFetchCustomer();


                    if('on' == $carbon_settings_options['auto_debit_offset']){

                        $curbon_offset_percentage = $fetch_card_response['carbon_offset_percentage'];
                        $order_total = $order->get_total();
                        $active_price = number_format((float)( ( $order_total * $curbon_offset_percentage ) / 100 ), 2, '.', '');
                        $offset_total = $active_price;
                    }


                    $deduct_topup = true;

                    /* check topup and is less than offset amount charge customer*/
                    $CURBON_TOPUP_AMOUNT = CURBON_MINIMUM_TOP_UP_AMOUNT;

                    if ($offset_total >= $CURBON_TOPUP_AMOUNT + $fetch_card_response['topup'] ) { //phpcs:ignore
                        $CURBON_TOPUP_AMOUNT = ceil($offset_total);
                    }

                    $args = array(
                                "email"                 => $billing_email,
                                'name'                  => $billing_first_name . 
                                                            " " . 
                                                            $billing_last_name,
                                "price"                 => $active_price,
                                "currency"              => $amount_currency,
                                "quantity"              => $item_quantity,
                                "preferred_topup"       => $CURBON_TOPUP_AMOUNT,
                                "number"                => $order_id,
                                "tax"                   => $total_tax,
                                "total_price"           => $active_price,
                                "order_status_url"      => $order_received_url,
                                "gateway"               => $payment_method_title .
                                                        '('.$paymentProviderId.')',
                                "billing_address" => array(
                                        'city'       => $billing_city,
                                        'name'       => $billing_first_name . 
                                                        " " . 
                                                        $billing_last_name,
                                        'phone'      => $billing_phone,
                                        'state'      => $billing_state,
                                        'company'    => $billing_company,
                                        'country'    => WC()
                                            ->countries
                                            ->countries[$billing_country],
                                        'address1'   => $billing_address_1,
                                        'address2'   => $billing_address_2,
                                        'last_name'  => $billing_last_name,
                                        'first_name' => $billing_first_name,
                                        'country_code' => $billing_country,
                                    )
                            );
                    
                    $save_purchase_response 
                        = $CURBON_Carbonclick_Laravel_API
                            ->curbonSavePurchase($args);

                    if (!empty($save_purchase_response['success']) 
                        && ( $save_purchase_response['success'] == true 
                        || $save_purchase_response['success'] == 1) 
                    ) {

                        update_post_meta(
                            $order_id, 
                            '_is_curbon_deducted', 
                            "yes"
                        );
                        
                        update_post_meta(
                            $order_id, 
                            '_purchase_api_status', 
                            "SUCCESS"
                        );
                        
                        update_post_meta(
                            $order_id, 
                            '_offset_purchase_amount', 
                            $offset_total
                        );

                        $global_notice = get_option('curbon-global-notice');
                        $global_notice['payment_failure'] = "";
                        update_option(
                            'curbon-global-notice', 
                            $global_notice
                        );
                        
                        update_option(
                            'curbon-charge-status', 
                            'paid'
                        );

                    } else {

                        update_post_meta(
                            $order_id, 
                            '_is_curbon_deducted', 
                            "no"
                        );
                        update_post_meta(
                            $order_id, 
                            '_purchase_api_status', 
                            "FAIL"
                        );

                    }

                    $_SESSION['curbon_box_status'] = '';
                        
                }
                //}
            }
        }
    }

    public function curbonextendAllowedTags($allowed_tags, $context){

        if ( $context !== 'post' ) {
            return $allowed_tags;
        }

        // Add sizes attribute to img.
        if ( isset( $allowed_tags['img'] ) ) {
            $allowed_tags['img']['sizes'] = true;
        }

        // form fields - input
        $allowed_tags['input'] = array(
            'class' => array(),
            'id'    => array(),
            'name'  => array(),
            'value' => array(),
            'type'  => array(),
        );
        // select
        $allowed_tags['select'] = array(
            'class'  => array(),
            'id'     => array(),
            'name'   => array(),
            'value'  => array(),
            'type'   => array(),
        );
        // select options
        $allowed_tags['option'] = array(
            'selected' => array(),
        );
        // style
        $allowed_tags['style'] = array(
            'types' => array(),
        );
        return $allowed_tags;

    }
} // end of class CURBON_WooCommerce_Init

$curbon_woocommerce_init = new CURBON_WooCommerce_Init;