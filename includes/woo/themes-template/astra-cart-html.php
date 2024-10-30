<?php
/**
 * Storefront Minicart HTML
 * Show curbon offset in Storefront Mini Cart
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

$payment_status = get_option('curbon-global-notice')['payment_failure'];
$shop_status    = get_option('curbon-shop-status');

if (! empty($payment_status) || 'blocked' == $shop_status ) {
    $_SESSION['curbon_box_status'] = '';
    return;
}

if (( isset($_GET['curbon_opt_out']) && 1 == $_GET['curbon_opt_out'] )  
    || ( isset($_GET['removed_item']) && 1 == $_GET['removed_item'] ) 
) {
    $_SESSION['curbon_box_status'] = 'out';
}

$curbon_look_and_feel_options = get_option('curbon_look_and_feel_options');
$curbon_settings_auto_debit = get_option(
    'curbon_settings_options'
)['auto_debit_offset'] ?? '';

$curbon_laravel_api_access_token = get_option(
    'curbon_laravel_api_tokens'
)['access_token'];

/** WP REMOTE GET **/

$curbon_logo = file_get_contents(
    CURBON_PLUGIN_URL . 
    'assets/images/curbon-logo.svg'
);


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

$product_id = wc_get_product_id_by_sku('curbon-offset');

$product_cart_id = WC()->cart->generate_cart_id($product_id);
$in_cart = WC()->cart->find_product_in_cart($product_cart_id);

$button_wrap = "";

if (empty($_SESSION['curbon_box_status']) && 'on' !== $curbon_settings_auto_debit ) {
    echo "<script>
        jQuery( document ).ready( function(){
            jQuery( '.wc-proceed-to-checkout .checkout-button' )
                .attr( 'href', '#curbon-offset-box' );
        } );
    </script>";
}


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

/**
 * Get the revised tax for Discounted price and offers
 * 
 * @return $revised_cart_total new cart total with regualar prices
 */
function getRevisedTax()
{

    $price_total = 0;
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $price_total += $cart_item['data']
            ->get_regular_price() * $cart_item['quantity'];
    }

    $shipping_charge = 0;
    $chosen_shippings = WC()->session->get('chosen_shipping_methods');
    foreach ( WC()->cart->get_shipping_packages() as $id => $package ) {
        $chosen = $chosen_shippings[$id]; // The chosen shipping method
        if (WC()->session->__isset('shipping_for_package_'.$id) ) {
            $shipping_charge = WC()->session->get(
                'shipping_for_package_'.$id
            )['rates'][$chosen]->get_cost();
        }
    }

    $subtotal_price = $price_total + $shipping_charge;

    $tax_percentage = round(reset(WC_Tax::get_rates())['rate']);

    $revised_tax = ( ( $subtotal_price * $tax_percentage ) / 100 );

    $revised_cart_total = $revised_tax + $subtotal_price;

    return $revised_cart_total;
}

$cart_total = getRevisedTax();

if ('on' !== $curbon_settings_auto_debit ) {
    
    // @codingStandardsIgnoreStart
    $curbon_logo = "<span class='curbon-regular-logo'>" .wp_remote_retrieve_body( wp_remote_get(CURBON_PLUGIN_URL . 'assets/images/curbon-icon.svg')) . "</span>";

    global $woocommerce;  
    $curbon_offset = number_format((float)( ( $cart_total * $curbon_offset_percentage ) / 100 ), 2, '.', '');
    $button_wrap = '<div class="button_two">
                        ' . wp_nonce_field("curbon_add_carbon_offset", "curbon_add_carbon_offset_button_nonce_field") . '
                        <input type="hidden" name="curbon_add_carbon_offset_button" value="'. $curbon_offset .'" />
                        <button type="submit" class="curbon-add-offset" data-offset="' . $curbon_offset . '">+ ' . get_woocommerce_currency_symbol() . $curbon_offset . '</button>
                        <a href="?curbon_opt_out=1" class="btn-optout">Opt-Out</a>
                    </div>';

    foreach ( WC()->cart->get_cart() as $item_key => $item_data ) {
        if ('curbon-offset' === $item_data['data']->get_sku() ) {
            $remove_offset_link = wc_get_cart_remove_url($item_key);
        }
    }
    // @codingStandardsIgnoreEnd

    if ($in_cart || '' != $_SESSION['curbon_box_status'] ) {
    
        // @codingStandardsIgnoreStart
        $button_wrap = '<div class="button_two success-btn-two">';
        if('in' == $_SESSION['curbon_box_status'] ) {
            $button_wrap .= '<button type="button"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 11" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M14.6784 0.772177C14.9822 1.07258 14.9822 1.55963 14.6784 1.86003L6.12289 10.3216C5.81915 10.622 5.32669 10.622 5.02294 10.3216L1.13406 6.47542C0.830315 6.17502 0.830315 5.68797 1.13406 5.38756C1.4378 5.08716 1.93026 5.08716 2.234 5.38756L5.57292 8.68979L13.5785 0.772177C13.8822 0.471774 14.3747 0.471774 14.6784 0.772177Z"/>
                                </svg>Thank You</button>';
        }
        if (( ! $in_cart ) && 'out' == $_SESSION['curbon_box_status'] || 'inout' == $_SESSION['curbon_box_status'] ) {
            $button_wrap .= wp_nonce_field("curbon_add_carbon_offset", "curbon_add_carbon_offset_button_nonce_field") .'
                <input type="hidden" name="curbon_add_carbon_offset_button" value="'. $curbon_offset .'" />
                <button type="submit" class="curbon-add-offset" data-offset="' . $curbon_offset . '">+ ' . get_woocommerce_currency_symbol() . $curbon_offset . 
                '</button>';
        } else {
            $button_wrap .= '<a href="' . 
                $remove_offset_link . 
                '" class="btn-optout">Opt-Out</a>';
        }
                        $button_wrap .= '</div>';

        // @codingStandardsIgnoreEnd

    }
}

if ('on' == $curbon_settings_auto_debit && $in_cart ) {
    if (!is_admin() && isset($product_id) ) {
            WC()->cart->remove_cart_item($in_cart); 
    }
}

/**
 * Convert image to base64 format.
 * 
 * @param $infographics URL of infographics image
 * 
 * @return String Base64 image string
 */
function infographicsBase64Getter( $infographics )
{
    $infographics_img_content = wp_remote_retrieve_body( wp_remote_get($infographics) );
    return base64_encode($infographics_img_content);
}

function get_curbon_caption_infographic_curl( $curbon_infograhics_id, $curbon_caption_id ) // phpcs:ignore
{
    $curbon_laravel_api_access_token = get_option(
        'curbon_laravel_api_tokens'
    )['access_token'];

    $curl_opt_utl = CURBON_API_LARAVEL_URL . 
        "api/v1/templates/" . 
        $curbon_infograhics_id . 
        "/" . 
        $curbon_caption_id;

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

    return json_decode($curbon_caption_infographics_fetch_response, true);
}

$curbon_caption_infographics_fetch_response 
    = get_curbon_caption_infographic_curl(
        $curbon_look_and_feel_options["infographics_id"], 
        $curbon_look_and_feel_options["caption_id"]
    );

if (empty($curbon_caption_infographics_fetch_response['infographic']) ) {

    // Captions & InfoGraphics API Call
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
        CURBON_API_LARAVEL_URL . 'api/v1/templates', 
        $args
    );

    $captions_infographics_response   = wp_remote_retrieve_body($response);
    $captions_infographics_response   = json_decode($captions_infographics_response, true);

    /** WP REMOTE GET **/

    $captions_infographics_response 
        = json_decode($captions_infographics_response, true);

    $curbon_look_and_feel_options['infographics_id'] 
        = $captions_infographics_response['infographics'][0]['id'];

    update_option('curbon_look_and_feel_options', $curbon_look_and_feel_options);

    $curbon_caption_infographics_fetch_response 
        = get_curbon_caption_infographic_curl(
            $curbon_look_and_feel_options["infographics_id"], 
            $curbon_look_and_feel_options["caption_id"]
        );

}

?>
<div class="curbon-box-wrap <?php echo esc_attr($auto_debit); ?> astra" id='curbon-offset-box'>
    <div class="curbon-box-cart">
        <div class="curbon-box-cart-left amin3">
            <div class="curbon-box-cart-left-box">
                <?php echo wp_kses( $curbon_logo, $allowed_tags ); ?>
                <div class="tooltip-custom">
                <?php // @codingStandardsIgnoreStart ?>
                    <svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="7.96203" cy="8.54992" r="7.82335" fill="#F15A29" />
                        <path
                            d="M7.96159 4.65625C7.44225 4.65625 6.98782 5.11068 6.98782 5.63002C6.98782 6.14937 7.44225 6.6038 7.96159 6.6038C8.48094 6.6038 8.93537 6.14937 8.93537 5.63002C8.93537 5.11068 8.48094 4.65625 7.96159 4.65625ZM5.68945 7.25298V7.90216C5.68945 7.90216 6.98782 7.90216 6.98782 9.20053V10.4989C6.98782 11.7973 5.68945 11.7973 5.68945 11.7973V12.4464H10.2337V11.7973C10.2337 11.7973 8.93537 11.7973 8.93537 10.4989V7.90216C8.93537 7.57757 8.61077 7.25298 8.28618 7.25298H5.68945Z"
                            fill="white" />
                    </svg>
                    <div class="tooltip-text">
                        <?php echo esc_html($curbon_caption_infographics_fetch_response['infographic']['text']);?></div>
                </div>
            </div>
            <div
                class="curbon-box-cart-right-box <?php echo ( 'on' == $curbon_settings_auto_debit ? 'curbon_all_purchase' : '' ) ?>">
                <p><?php echo esc_html($curbon_caption_infographics_fetch_response['caption']['caption']); ?></p>
                <a href="javascript:void(0);" title="Learn more" class="learn_more">Learn more
                    <svg width="11" height="7" viewBox="0 0 11 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M1.10724 1.10998C1.29477 0.922505 1.54908 0.817189 1.81424 0.817189C2.0794 0.817189 2.33371 0.922505 2.52124 1.10998L5.81424 4.40298L9.10724 1.10998C9.19949 1.01447 9.30983 0.938283 9.43184 0.885874C9.55384 0.833465 9.68506 0.805879 9.81784 0.804725C9.95062 0.803571 10.0823 0.828873 10.2052 0.879154C10.3281 0.929435 10.4397 1.00369 10.5336 1.09758C10.6275 1.19147 10.7018 1.30313 10.7521 1.42602C10.8023 1.54892 10.8276 1.6806 10.8265 1.81338C10.8253 1.94616 10.7978 2.07738 10.7453 2.19938C10.6929 2.32138 10.6167 2.43173 10.5212 2.52398L6.52124 6.52398C6.33371 6.71145 6.0794 6.81676 5.81424 6.81676C5.54908 6.81676 5.29477 6.71145 5.10724 6.52398L1.10724 2.52398C0.919769 2.33645 0.814453 2.08214 0.814453 1.81698C0.814453 1.55181 0.919769 1.2975 1.10724 1.10998Z"
                            fill="#A09FFA" />
                    </svg>
                </a>
            <?php // @codingStandardsIgnoreEnd ?>
            </div>
        </div>
        <div class="curbon-box-cart-right">
            <?php echo wp_kses( $button_wrap, $allowed_tags ); ?>
        </div>
    </div>
</div>
<?php // @codingStandardsIgnoreStart ?>
<div id="slide_down_wrapper" style="display: none;">
    <p><?php echo esc_html($curbon_caption_infographics_fetch_response['infographic']['text']);?></p>
    <img src="data:image/png;base64, <?php echo esc_html(infographicsBase64Getter($curbon_caption_infographics_fetch_response['infographic']['desktop_image']));  ?>"
        alt="">
    <img src="data:image/png;base64, <?php echo esc_html(infographicsBase64Getter($curbon_caption_infographics_fetch_response['infographic']['mobile_image'])); ?>"
        alt="" class="mobile_friendly">
    <a target="_blank" href="https://curbon.io"><span class="pwdby_curbon">Powered by</span></a>
</div>
<?php // @codingStandardsIgnoreEnd ?>

<script>
jQuery(document).ready(function() {
    jQuery(".learn_more").click(function() {

        jQuery(this)
            .closest(".curbon-box-wrap")
            .siblings("#slide_down_wrapper")
            .slideToggle("slow");

        jQuery(this)
            .closest(".curbon-box-wrap")
            .toggleClass('border-bottom-flat');

        jQuery(this)
            .closest(".learn_more")
            .toggleClass('icon_rotate');
    });
});
</script>