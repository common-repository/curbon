<?php

if(isset($_POST) && !empty($_POST['curbon-settings']) ) {
    global $woocommerce;
    $curbon_settings = sanitize_post( $_POST['curbon-settings'] );

    update_option('curbon_settings_options', $curbon_settings);

    $curbon_look_and_feel_options = get_option('curbon_look_and_feel_options');

    $caption_id         = $curbon_look_and_feel_options['caption_id'];

    $curbon_is_settings_updated = get_option('curbon_is_settings_updated');

    if(false === $curbon_is_settings_updated ) {
        add_option('curbon_is_settings_updated', 'true');
    }

    $curbon_laravel_api_access_token    = get_option('curbon_laravel_api_tokens')['access_token'];
        
    $curbon_settings_details = '{
            "orders_count" : '. CURBON_TOTAL_OFFSET_ORDERS .',
            "last_impression": false,
            "setup": ' . ( 'on' == $curbon_settings['widget'] ? true : false ) . ',
            "preferred_topup": ' . $curbon_settings['topup-amount'] . ',
            "version": "' . $woocommerce->version . '",
            "offset_all_purchase": ' . ( 'on' == isset($curbon_settings['auto_debit_offset']) ? true : false ) . '
        }';

    /** WP REMOTE POST **/

    $body = array(
        "orders_count"              => CURBON_TOTAL_OFFSET_ORDERS,
        "last_impression"           => false,
        "setup"                     => ( 'on' == isset($curbon_settings['widget']) ? true : false ),
        "preferred_topup"           => $curbon_settings['topup-amount'],
        "version"                   => $woocommerce->version,
        "offset_all_purchase"       => ( 'on' == isset($curbon_settings['auto_debit_offset']) ? true : false ),
    );


    $headers = array(
        "Accept"        => "application/json",
        "Content-Type"  => "application/json",
        "Authorization" =>  "Bearer ".$curbon_laravel_api_access_token
    );

    $args = array(
        'headers'       => $headers,
        'method'        => 'PUT',
        'timeout'       => 120,
        'httpversion'   => '1.1',
        'sslverify'     => true,
        'body'          => json_encode($body)
    );

    $response       = wp_remote_request(
        CURBON_API_LARAVEL_URL."api/v1/shops", 
        $args
    );

    $responseBody   = wp_remote_retrieve_body($response);

    /** WP REMOTE POST **/

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
        CURBON_API_LARAVEL_URL."api/v1/templates", 
        $args
    );

    $captions_infographics_response   = wp_remote_retrieve_body($response);
    $captions_infographics_response   = json_decode($captions_infographics_response, true);

    /** WP REMOTE GET **/

    $key = array_search($caption_id, array_column($captions_infographics_response['captions'], 'id'));

    if(empty($key) ) {
        $curbon_look_and_feel_options['caption_id'] = $captions_infographics_response['captions'][0]['id'];
        update_option('curbon_look_and_feel_options', $curbon_look_and_feel_options);
    }

    // Captions & API Call Ends

}

    $curbon_settings                    = get_option('curbon_settings_options');
    $curbon_charge_status                 = get_option('curbon-charge-status');
    $curbon_laravel_api_access_token    = get_option('curbon_laravel_api_tokens')['access_token'];

    $disabled             = "";

    wp_enqueue_script('jquery-ui-tooltip');

    // Config cURL Call
    $body = array(
        "type"  => "woocommerce",
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
        'body'          => json_encode($body)
    );

    $response       = wp_remote_post(
        CURBON_API_LARAVEL_URL."api/v1/config", 
        $args
    );

    $config_response   = wp_remote_retrieve_body($response);
    $config_response   = json_decode($config_response, false);
    
    //Fetch cart offset percentage
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

    if(false == $fetch_customer_response['success'] ) {
        $disabled = 'curbon-blocked-blur';
    }

    //Fetch cart offset percentage end

    $encreypted_percent = curbon_encrypt_offset_percentage($fetch_customer_response['carbon_offset_percentage']);

    function curbon_encrypt_offset_percentage( $value )
    {
        if(!empty($value) ) {
            $domain_key_counter = strlen(get_site_url());
            return ( $value * $domain_key_counter );
        }
    }

    function curbon_decrypt_offset_percentage( $value )
    {
        if(!empty($value) ) {
            $domain_key_counter = strlen(get_site_url());
            return ( $value / $domain_key_counter );
        }
    }


    ?>
<form method="post">
    <div class="dashboard-card">
        <div class="dashboard-card-inner">
            <div class="dashboard-card-items itmes-1">
                <div class="dashborard-card-content">
                    <p class="dashboardh2"><?php echo CURBON_TOTAL_OFFSET_ORDERS ?? '0'; ?></p>
                    <p>Order with offsets</p>
                </div>
            </div>
            <div class="dashboard-card-items itmes-2">
                <div class="dashborard-card-content">
                    <p class="dashboardh2">
                        <?php echo get_woocommerce_currency_symbol() . ( CURBON_TOTAL_OFFSET_AMOUNT ?? '0' ); ?>
                    </p>
                    <p>Offsets collected</p>
                </div>
            </div>
        </div>

    </div>
    <div class="setting-tab-wrapper">
        <p class="card-title heading">Settings</p>

        <div class="settting-bg">
            <div class="setting-inner">
                <div class="setting-items">
                    <table class="form-table">
                        <tbody>

                            <tr valign="top" if="curbon-enable-widget">
                                <th scope="row">
                                    <p class="description">Enable Widget</p>
                                </th>
                                <td class="forminp forminp-checkbox">
                                    <div class="inner-bg">
                                        <fieldset>
                                            <div class="toggle-button">
                                                <div>
                                                    <label class="switch">
                                                        <input type="checkbox" <?php 
                                                            echo ( isset($curbon_settings['widget']) && "on" === $curbon_settings['widget'] ) ? "checked" : "";
                                                        ?> name="curbon-settings[widget]">
                                                        <span class="slider"></span>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <p class="description">Widget Location</p>
                                </th>
                                <td class="forminp forminp-checkbox">
                                    <div class="inner-bg">
                                        <fieldset>
                                            <div class="toggle-icon">
                                                <p class="description">Cart Page</p>
                                                <div class="toggle-button">
                                                    <div>
                                                        <label class="switch">
                                                            <input type="checkbox"
                                                                name="curbon-settings[cart-page-button]"
                                                                <?php echo (isset($curbon_settings['cart-page-button']) && "on" === $curbon_settings['cart-page-button'] ) ? "checked" : ""; ?>>
                                                            <span class="slider"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="text-field">Most themes have this page which lets user review
                                                their
                                                cart prior to
                                                checkout</span>
                                        </fieldset>


                                        <fieldset>
                                            <div class="toggle-icon mini-cart">
                                                <p class="description">Mini Cart</p>
                                                <div class="toggle-button">
                                                    <div>
                                                        <label class="switch">
                                                            <input type="checkbox"
                                                                name="curbon-settings[mini-cart-page-button]"
                                                                <?php echo (isset($curbon_settings['mini-cart-page-button']) && "on" === $curbon_settings['mini-cart-page-button'] ) ? "checked" : ""; ?>>
                                                            <span class="slider"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="text-field">Also called drawer-cart, only some themes have this
                                                feature.</span>
                                        </fieldset>

                                        <fieldset>
                                            <div class="toggle-icon">
                                                <p class="description">Checkout</p>
                                                <div class="toggle-button">
                                                    <div>
                                                        <label class="switch">
                                                            <input type="checkbox"
                                                                name="curbon-settings[checkout-page-button]"
                                                                <?php echo (isset($curbon_settings['checkout-page-button']) && "on" === $curbon_settings['checkout-page-button'] ) ? "checked" : ""; ?>>
                                                            <span class="slider"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="text-field">This is the payment page</span>
                                        </fieldset>
                                    </div>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <p class="description">Embed Widget</p>
                                </th>
                                <td class="embed_column">
                                    <a onClick="copyText()" class="btn_embed">&lt;&#47;embed&gt;</a>
                                    <span class="text-field copied-text" style="display: none;">Copied!</span>
                                    <span class="text-field">Click to copy widge shortcode</span>
                                </td>
                            </tr>
                            <script>
                            function copyText() {
                                navigator.clipboard.writeText('[curbon-offset-box]');
                                jQuery('.text-field.copied-text').show();
                                setTimeout(function() {
                                    jQuery('.text-field.copied-text').fadeOut('fast');
                                }, 5000); // <-- time in milliseconds
                            }
                            </script>
                        </tbody>
                    </table>

                </div>
                <div class="setting-items">
                    <table class="form-table form-table-right-store-offset">
                        <tbody>
                            <tr valign="top">
                                <td class="forminp forminp-checkbox">
                                    <div class="inner-bg">
                                        <fieldset>
                                            <div class="toggle-icon">
                                                <p class="description">Store/Merchant Offsets All Purchases</p>
                                                <div class="toggle-button">
                                                    <div>
                                                        <label class="switch">
                                                            <input type="checkbox"
                                                                name="curbon-settings[auto_debit_offset]"
                                                                <?php echo (isset($curbon_settings['auto_debit_offset']) && "on" === $curbon_settings['auto_debit_offset'] ) ? "checked" : ""; ?>>
                                                            <span class="slider"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="text-field">Select this to make all products on your store
                                                carbon neutral. With this option, your business offsets the emissions of
                                                any purchase on your online store on behalf of the customer</span>
                                        </fieldset>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="setting-items-inner">
                        <div class="items">
                            <div class="items-inner">
                                <div class="item-box">
                                    <p class="description">Cart Offset Percentage </p>
                                    <a href="<?php echo esc_url( $config_response->links->emission_form ); ?>"
                                        target="_blank">Store emission evaluation form </a>
                                </div>
                                <div class="item-box">
                                    <p>cart Offset percentage </p>
                                    <div class="input-btn">
                                        <input type="hidden" name="curbon-settings[curbon-offset-percentage]"
                                            value="<?php echo esc_html($encreypted_percent); ?>">
                                        <input type="text" id=""
                                            value="<?php echo esc_html(curbon_decrypt_offset_percentage($encreypted_percent)); ?>"
                                            readonly="readonly">
                                        <label for="text">%</label>
                                    </div>
                                </div>
                            </div>
                            <p class="text-field">Tell us a little about your store's offering in our emission evalution
                                form and our team will
                                evaluate your emissions to give your customer's the most accurate carbon offsetting
                                solution. We will display the offset percentage allocated to your store here. </p>
                        </div>
                        <div class="items">
                            <div class="items-inner">
                                <div class="item-box">
                                    <p class="description">Topup Amount (ZAR) </p>
                                </div>
                                <div class="item-box">
                                    <p>Preferred Topup </p>
                                    <!-- <a href="#" class="main-btn">R500</a> -->
                                    <?php echo get_woocommerce_currency_symbol(); ?>
                                    <input type="number" class="main-btn" name="curbon-settings[topup-amount]"
                                        value="<?php echo (isset($curbon_settings['topup-amount']) && ! empty($curbon_settings['topup-amount']) ) ? $curbon_settings['topup-amount'] : "500"; ?>">
                                </div>
                            </div>
                            <p class="text-field">The Topup Amount is the amount that we will bill your card each time
                                your
                                balance approaches
                                0. </p>
                        </div>
                        <div class="items">
                            <div class="items-inner">
                                <div class="item-box">
                                    <p class="description">Need Help? </p>
                                </div>
                                <div class="item-box">
                                    <p class="text-field faq-text">Check out our <a
                                            href="<?php echo esc_url( $config_response->links->faq ); ?>" target="_blank"
                                            class="faq">FAQ</a> or email us
                                        <a href="mailto:<?php echo sanitize_email( $config_response->emails->support ); ?>"
                                            class="faq">here</a>
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
        <p>
            <button name="save_curbon_settings" class="btn-primary" type="submit" value="Save changes">Save
                changes</button>
            <?php wp_nonce_field('curbon_settings_nonce', 'curbon_settings_nonce_field'); ?>
        </p>
    </div>
</form>