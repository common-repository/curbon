<?php

/**
 * Astra Minicart HTML
 * Show curbon offset in Astra Mini Cart
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

$curbon_look_and_feel_options   = get_option('curbon_look_and_feel_options');
$curbon_settings                = get_option('curbon_settings_options');

if (! isset($curbon_settings['cart-page-button']) ) {
    return;
}

$curbon_settings_auto_debit = $curbon_settings['auto_debit_offset'] ?? '';

$curbon_laravel_api_access_token = get_option(
    'curbon_laravel_api_tokens'
)['access_token'];

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

$curbon_offset_percentage = $fetch_customer_response['carbon_offset_percentage'];
$curbon_logo = wp_remote_retrieve_body( wp_remote_get(
    CURBON_PLUGIN_URL . 'assets/images/curbon-logo.svg'
) );

$button_wrap = "";
if ('on' !== $curbon_settings_auto_debit ) {

    $curbon_logo = "<span class='curbon-regular-logo'>" .
        wp_remote_retrieve_body( wp_remote_get(CURBON_PLUGIN_URL . 'assets/images/curbon-icon.svg') ) . 
        "</span>";

    global $woocommerce;  

    $cart_total = $woocommerce->cart->total;
    $curbon_offset = number_format(
        (float)( 
            ( $cart_total * $curbon_offset_percentage ) / 100 
        ), 
        2, 
        '.', 
        ''
    );

    // @codingStandardsIgnoreStart
    $button_wrap = '<div class="costomize-btn">
                        ' . wp_nonce_field("curbon_add_carbon_offset", "curbon_add_carbon_offset_button_nonce_field") . '
                        <input type="hidden" name="curbon_add_carbon_offset_button" value="'. $curbon_offset .'" />
                        <button type="submit" class="costomize-btn-secoundary curbon-primary-color-reflect curbon-add-offset" data-offset="' . $curbon_offset . '">+ ' . get_woocommerce_currency_symbol() . $curbon_offset . '</button>
                    </div>';
    // @codingStandardsIgnoreEnd

}

$product_id = wc_get_product_id_by_sku('curbon-offset');

$product_cart_id = WC()->cart->generate_cart_id($product_id);
$in_cart = WC()->cart->find_product_in_cart($product_cart_id);

foreach ( WC()->cart->get_cart() as $item_key => $item_data ) {
    if ('curbon-offset' === $item_data['data']->get_sku() ) {
        $remove_offset_link = wc_get_cart_remove_url($item_key);
    }
}

if ($in_cart ) {
    // @codingStandardsIgnoreStart
    $button_wrap = '<div class="costomize-btn">
                        <a href="' . $remove_offset_link . '" class="costomize-btn-transparent">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 11" fill="none" class="mini-cart-thank">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.6784 0.772177C14.9822 1.07258 14.9822 1.55963 14.6784 1.86003L6.12289 10.3216C5.81915 10.622 5.32669 10.622 5.02294 10.3216L1.13406 6.47542C0.830315 6.17502 0.830315 5.68797 1.13406 5.38756C1.4378 5.08716 1.93026 5.08716 2.234 5.38756L5.57292 8.68979L13.5785 0.772177C13.8822 0.471774 14.3747 0.471774 14.6784 0.772177Z"></path>
                            </svg>
                        </a>
                    </div>';
    // @codingStandardsIgnoreEnd

}


    $curl_opt_utl = CURBON_API_LARAVEL_URL . 
        "api/v1/templates/" . 
        $curbon_look_and_feel_options["infographics_id"] . 
        "/" . 
        $curbon_look_and_feel_options["caption_id"];
        
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
        $curl_opt_utl, 
        $args
    );

    $curbon_caption_infographics_fetch_response   = wp_remote_retrieve_body($response);
    
    /** WP REMOTE GET **/
    $curbon_template_response = json_decode($curbon_caption_infographics_fetch_response, true);

    $kses_defaults = wp_kses_allowed_html( 'post' );

    $svg_args = array(
        'svg'   => array(
            'class'           => true,
            'aria-hidden'     => true,
            'aria-labelledby' => true,
            'role'            => true,
            'xmlns'           => true,
            'width'           => true,
            'height'          => true,
            'viewbox'         => true // <= Must be lower case!
        ),
        'g'     => array( 'fill' => true ),
        'title' => array( 'title' => true ),
        'path'  => array( 
            'd'               => true, 
            'fill'            => true  
        )
    );


    $allowed_tags = array_merge( $kses_defaults, $svg_args );

?>
<form method="post">
    <div id="curbon-offset-mini-cart-widget"
        class="is-enabled customize-view-bg-box curbon-update-border-color <?php echo esc_attr($auto_debit); ?>"><?php // phpcs:ignore ?>
        <div class="customize-view-bg-itmes">
            <div class="content">
                <div class="costomize-logo-box">
                    <a href="#" class="curbon-primary-color-reflect">
                        <?php echo wp_kses( $curbon_logo, $allowed_tags ); ?>
                    </a>
                </div>
                <div class="costomize-view">
                    <p class="curbon-mini-cart-view-text">
                        <?php 
                            echo esc_html($curbon_template_response['caption']['caption']); 
                        ?>
                    </p>
                </div>
            </div>
            <?php echo wp_kses( $button_wrap, $allowed_tags ); ?>
        </div>
    </div>

</form>