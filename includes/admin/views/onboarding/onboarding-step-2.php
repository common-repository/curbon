<?php
/**
 * HTML of Onboarding Step - 2
 * View of onboarding page
 * php version 7.4

 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @version  GIT: @1.0.0@
 * @link     https://curbon.io/
 */

   $response_code = get_option('curbon_laravel_api_response_code');
   
   $filter_blur = "";

if (!CURBON_Admin_Init::curbonMaybeIsSsl()) {
    $filter_blur = "filter_blur";
    echo '<div class="curbon-filter-blur">Payment integrations must use HTTPS</div>';
}

    $CURBON_ACCESS_TOKEN = get_option('curbon_laravel_api_tokens')['access_token'];

// Get biz links from API

/** WP REMOTE POST **/

$body = array(
    "type"  => "woocommerce",
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
    CURBON_API_LARAVEL_URL."api/v1/config", 
    $args
);

$biz_links_response   = wp_remote_retrieve_body($response);
$biz_links_response   = json_decode($biz_links_response, true);

/** WP REMOTE POST **/

// Get biz links from API

// Get authorized Payment URl from Paystack
   
   /** WP REMOTE POST **/

    $body = array(
        "type"          => "woocommerce",
        "email"         => get_option('admin_email'),
        "domain"        => get_site_url(),
        "update_card"   => false
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
        CURBON_API_LARAVEL_URL."api/v1/payment-url", 
        $args
    );

    $response = json_decode( wp_remote_retrieve_body($response), true );

    /** WP REMOTE POST **/

   //    Get authorized Payment URl from Paystack

   $curbon_payment_check = false;
   $curbon_payment_trxref = $curbon_payment_reference = "";
if (isset($_REQUEST['trxref']) 
    && !empty($_REQUEST['trxref']) 
    && isset($_REQUEST['reference']) 
    && !empty($_REQUEST['reference'])
) {
   
    $curbon_payment_check = true;
   
    $curbon_payment_trxref = sanitize_text_field($_REQUEST['trxref']);
    $curbon_payment_reference = sanitize_text_field($_REQUEST['reference']);
   
}

// @codingStandardsIgnoreStart
   
?>
<div class="curbon-onboading step2 <?php echo esc_attr( $filter_blur ); ?>" id="step7">
    <div class="curbon-onboading-left">
        <div class="curbon-onboading-bg">
            <div class="curbon-step-bg">
                <div class="curbon-step-wrap-title">
                    <p class="curbon-step-h2-t">
                        Your payment details.
                    </p>
                    <p>Nice work! Your customers are almost ready to start fighting climate change at the checkout.</p>
                </div>
                <svg width="389" height="352" viewBox="0 0 389 352" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <mask id="mask0_1131_4495" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0"
                        width="389" height="352">
                        <g clip-path="url(#clip0_1131_4495)">
                            <path
                                d="M38.1814 213.158C38.5421 208.558 41.0712 205.949 45.3785 204.428C122.947 177.088 200.501 149.7 278.041 122.263C284.232 120.077 288.621 122.063 290.85 128.349C305.537 169.787 320.176 211.239 334.767 252.706C337.09 259.285 334.912 263.635 328.355 265.959C250.997 293.278 173.636 320.598 96.273 347.919C92.0207 349.421 88.3281 348.86 85.0186 345.785L38.1814 213.158ZM119.56 285.202C126.025 282.919 132.523 280.73 138.954 278.353C142.13 277.232 144.731 274.895 146.188 271.856C147.645 268.816 147.839 265.32 146.728 262.135C145.052 256.989 143.229 251.878 141.32 246.823C138.545 239.413 131.679 236.162 124.185 238.729C111.719 243.061 99.2761 247.455 86.8551 251.912C85.17 252.467 83.6142 253.356 82.2808 254.527C80.9473 255.697 79.8636 257.126 79.0943 258.726C78.325 260.326 77.886 262.066 77.8036 263.841C77.7211 265.616 77.9968 267.389 78.6143 269.056C80.217 273.794 81.9453 278.488 83.6108 283.204C86.4689 291.098 93.3516 294.396 101.335 291.665C107.41 289.502 113.473 287.307 119.546 285.163L119.56 285.202ZM176.394 295.49C181.101 293.827 185.822 292.204 190.516 290.502C193.97 289.238 195.567 286.34 194.471 283.536C193.375 280.732 190.452 279.651 187.141 280.82C177.713 284.109 168.322 287.425 158.967 290.77C155.491 291.997 153.934 294.908 155.03 297.739C156.127 300.569 158.96 301.594 162.345 300.46C167.002 298.806 171.688 297.107 176.38 295.45L176.394 295.49ZM289.889 255.409C294.596 253.746 299.318 252.123 304.011 250.421C307.479 249.197 309.089 246.259 308.021 243.462C306.954 240.665 304.002 239.577 300.699 240.744C291.271 244.032 281.883 247.347 272.533 250.69C268.987 251.943 267.482 254.729 268.578 257.63C269.624 260.417 272.464 261.562 275.837 260.371C280.498 258.725 285.175 257.029 289.875 255.369L289.889 255.409ZM119.552 315.563C124.157 313.937 128.771 312.308 133.376 310.681C137.369 309.271 138.723 306.883 137.538 303.677C136.414 300.644 133.841 299.679 130.051 301.017C120.829 304.238 111.616 307.492 102.411 310.778C98.6607 312.103 97.2841 314.551 98.3483 317.615C99.4124 320.678 101.938 321.757 105.744 320.466C110.341 318.816 114.933 317.151 119.541 315.532L119.552 315.563ZM232.702 275.604C237.605 273.873 242.528 272.196 247.405 270.412C250.871 269.135 252.175 266.605 251.09 263.656C250.088 260.818 247.441 259.596 244.193 260.717C234.567 264.045 224.962 267.437 215.375 270.894C212.033 272.074 210.859 274.522 211.839 277.447C212.916 280.496 215.401 281.661 218.883 280.476C223.494 278.865 228.083 277.191 232.688 275.565L232.702 275.604Z"
                                fill="#A09FFA" />
                            <path
                                d="M320.928 54.2935C321.737 59.5331 322.412 64.7935 323.402 69.9957C323.958 72.92 323.374 74.1874 320.143 74.6831C288.168 79.8662 256.209 85.1941 224.28 90.4581C191.684 95.8399 159.094 101.214 126.51 106.579C106.12 109.943 85.735 113.312 65.354 116.684C60.8083 117.405 60.8083 117.405 60.0224 112.856C59.1944 107.738 58.4495 102.555 57.3381 97.4843C55.9325 91.0055 58.8841 85.4456 66.6284 84.1871C94.1182 79.8718 121.552 75.2227 149.001 70.6919L249.806 54.0465C269.27 50.8417 288.748 47.7026 308.182 44.4377C312.346 43.7362 315.778 44.7079 318.625 47.8222L320.928 54.2935Z"
                                fill="#A09FFA" />
                            <path
                                d="M176.149 147.319L76.7448 182.424C72.0374 184.086 71.9904 184.103 71.1904 179.364C68.4918 163.352 65.9534 147.319 63.0582 131.35C62.4513 127.957 63.7045 127.258 66.4943 126.812C80.5535 124.57 94.5761 122.199 108.621 119.891L207.029 103.704C245.563 97.3624 284.097 91.0035 322.63 84.627C325.131 84.2124 325.9 84.9926 326.288 87.3399C329.562 107.154 332.927 126.953 336.258 146.755C338.183 158.152 340.109 169.553 342.037 180.96C343.457 189.353 340.768 193.282 332.353 194.742C324.23 196.152 325.967 196.449 323.473 189.585C315.907 168.387 308.483 147.14 300.988 125.917C299.14 120.682 296.437 116.102 291.284 113.431C286.132 110.76 280.649 110.477 275.019 112.474C242.071 124.104 209.114 135.719 176.149 147.319Z"
                                fill="#A09FFA" />
                            <path
                                d="M108.953 254.918C114.736 252.876 120.518 250.834 126.292 248.795C129.697 247.593 131.053 248.157 132.349 251.528C133.921 255.605 135.258 259.765 136.712 263.884C137.639 266.509 136.735 268.42 134.141 269.345C122.303 273.573 110.451 277.758 98.5841 281.902C95.8695 282.86 93.9904 281.862 93.0409 279.024C91.657 274.88 90.129 270.778 88.6745 266.659C87.6279 263.546 88.2335 262.262 91.3085 261.15C97.1895 259.038 103.077 256.994 108.953 254.918Z"
                                fill="#A09FFA" />
                        </g>
                    </mask>
                    <g mask="url(#mask0_1131_4495)">
                        <rect x="14.8594" y="31.4258" width="304.211" height="287.574" fill="#A09FFA" />
                    </g>
                    <defs>
                        <clipPath id="clip0_1131_4495">
                            <rect width="319.508" height="258.842" fill="white"
                                transform="translate(0.75 107.16) rotate(-19.4507)" />
                        </clipPath>
                    </defs>
                </svg>
            </div>
        </div>
    </div>
    <div class="curbon-onboading-right">
        <div class="curbon-onboading-bg">
            <div class="payment-charge-text">
                <div class="payment-charge-header curbon-onboading-right-image">
                    <p class="curbon-title-c">
                        <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.0546875" y="0.203125" width="43.4453" height="43.4453" fill="#A09FFA" />
                            <path
                                d="M21.7779 11.7148C20.4813 11.7148 19.3469 12.9061 19.3469 14.2674C19.3469 15.6288 20.4813 16.82 21.7779 16.82C23.0745 16.82 24.2089 15.6288 24.2089 14.2674C24.2089 12.9061 23.0745 11.7148 21.7779 11.7148ZM16.1055 18.5218V20.2235C16.1055 20.2235 19.3469 20.2235 19.3469 23.627V27.0304C19.3469 30.4339 16.1055 30.4339 16.1055 30.4339V32.1356H27.4503V30.4339C27.4503 30.4339 24.2089 30.4339 24.2089 27.0304V20.2235C24.2089 19.3726 23.3986 18.5218 22.5883 18.5218H16.1055Z"
                                fill="white" />
                        </svg>
                        &nbsp;&nbsp;What will you be charged?
                    </p>
                </div>
                <div class="payment-charge-body">
                    <?php if (CURBON_SUB_PRICE == 0) { ?>
                    <p>You will be charged R500 <?php echo get_woocommerce_currency(); ?> now to top up your carbon
                        offset balance. As your customers make contributions to offset their purchases, your offset
                        balance will decrease in
                        line with the contribution value. This balance will be automatically topped up when your balance
                        gets low.</p>
                    <?php } else { ?>
                    <p>After the 14 day free trial you will be charged <strong>US$399 monthly</strong> for use of the
                        app</p>
                    <p>You will also be charged 5 <?php echo get_woocommerce_currency(); ?> now to topup your offset
                        prepay balance. This balance will be automatically topped up when your balance is low.</p>
                    <?php } ?>
                    <p>As your customers click the 'Purple Button' to purchase offsets, they reimburse you for offset
                        charges.</p>

                    <?php
                        if (!$curbon_payment_check) { 
                    ?>
                    <fieldset>
                        <input type="checkbox" class="onboarding_agreement" id="agree_tc_rp_pp" value="1" required
                            name="onboarding[agree_tc_rp_pp]"><label for="agree_tc_rp_pp">I have read and accept the <a
                                href="<?php echo esc_url($biz_links_response['links']['terms']); ?>" target="_blank">Terms and
                                Conditions</a>, <a href="<?php echo esc_url($biz_links_response['links']['refund']); ?>"
                                target="_blank">Refund Policy</a> and <a
                                href="<?php echo esc_url($biz_links_response['links']['privacy']); ?>" target="_blank">Privacy
                                Policy</a></label>
                    </fieldset>
                    <div class="curbon-step-btn">
                        <a href="<?php echo esc_url($response['url']); ?>" name="onboarding_next" disabled="disabled"
                            class="button-primary curbon-paystack-payment" id="curbon-paystack-payment"
                            value="Pay & Complete Setup">Pay & Complete Setup</a>
                        <input type="hidden" name="onboarding_previous_step" value="<?php echo esc_html($active_step) - 1; ?>">
                        <input type="hidden" name="onboarding_current_step" value="<?php echo esc_html($active_step); ?>">

                        <?php
                            // @codingStandardsIgnoreEnd
                                $last_step_details = array(
                                    'is_last_steps' => 'yes',
                                );
                    ?>
                        <?php 
                            wp_nonce_field(
                                'curbon_onboarding_next_nonce', 
                                'curbon_onboarding_next_nonce_field'
                            ); 
                    ?>
                    </div>
                    <?php } else { ?>
                    <div>
                        <?php
                                $initialize_post_field = '{
                                    "trxref": "' . $curbon_payment_trxref . '" 
                                }';
                                $onboarding_status_option 
                                    = get_option('curbon-onboarding-status');

                                /** WP REMOTE POST **/

                                $body = array(
                                    "trxref" => $curbon_payment_trxref
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
                                    CURBON_API_LARAVEL_URL."api/v1/transaction/initialize", 
                                    $args
                                );

                                $curbon_payment_response   = wp_remote_retrieve_body($response);
                                $curbon_payment_response 
                                    = json_decode($curbon_payment_response, true);

                                /** WP REMOTE POST **/

                        if (isset($curbon_payment_response['success']) 
                            && true === $curbon_payment_response['success']
                        ) {

                            echo "<p class='congrats-msg'>Congratulations! Your payment has been verified! Redirecting to the main page in 3 seconds...</p>"; //phpcs:ignore

                            // $curbon_offset_product_id = wc_get_product_id_by_sku('curbon-offset');

                            $curbon_onboarding_status 
                                = get_option('curbon-onboarding-status');
                            $curbon_offset_product_id 
                                = $curbon_onboarding_status['carbon_offset_product_id'];

                            if (! $curbon_offset_product_id ) {

                                    // that's CRUD object
                                    $product = new WC_Product_Simple();

                                    $product->set_name('Curbon Offset');

                                    $product->set_slug('curbon-offset');

                                    $product->set_regular_price(1.00);

                                    $product->set_sku('curbon-offset');

                                    $product->set_sold_individually(true);

                                    $product->save();

                                    add_option(
                                        'carbon_offset_product_id', 
                                        $product->get_id()
                                    );

                            } else {
                                update_option(
                                    'carbon_offset_product_id', 
                                    $curbon_offset_product_id
                                );
                            }


                            $onboarding_status_option['status'] = "complete";
                            $onboarding_status_option['steps']['step-2'] = "complete"; //phpcs:ignore

                            update_option(
                                'curbon-onboarding-status', 
                                $onboarding_status_option
                            );


                            echo "<script>
                                window.setTimeout(function(){
                                    window.location.href = '" . 
                                        get_site_url() . 
                                    "/wp-admin/admin.php?page=curbon-dashboard&tab=dashboard';

                                }, 3000);
                            </script>";

                        } else {
                            echo "Payment verification failed!";
                        }
                        ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>