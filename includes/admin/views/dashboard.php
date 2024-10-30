<?php
    // @codingStandardsIgnoreStart
    $curbon_onboarding_status       = get_option('curbon-onboarding-status');
    $curbon_settings                   = get_option('curbon_settings_options');

    $curbon_laravel_api_access_token    = get_option('curbon_laravel_api_tokens')['access_token'];

    /** WP REMOTE POST **/
    $body = array(
        "type" => "woocommerce",
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
        CURBON_API_LARAVEL_URL."api/v1/config", 
        $args
    );

    $responseBody       = wp_remote_retrieve_body($response);
    $config_response    = json_decode( $responseBody );

    /** WP REMOTE POST **/

    $response_code = get_option('curbon_laravel_api_response_code');

    $curbon_is_settings_updated = get_option('curbon_is_settings_updated');

    ?>
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
                    <?php echo get_woocommerce_currency_symbol() . ( CURBON_TOTAL_OFFSET_AMOUNT ?? '0' ); ?></p>
                <p>Offsets collected</p>
            </div>
        </div>
    </div>

</div>
<div class="dashboard-card-sections">
    <div class="dashboard-card-left">
        <div class="dashboard-card-left-inner">
            <div class="dashboard-single-section">
                <p class="card-title heading">Complete your Climate Friendly Cart setup</p>
                <div class="timeline-content">


                    <div class=" step step1 ">
                        <p
                            class="card-title heading <?php echo ( isset($curbon_is_settings_updated) && false === $curbon_is_settings_updated ) ? 'step-pending' : ''; ?>">
                            Widget Position</p>
                        <div class="card-content-cb-wrap">
                            <div class="card-content">
                                <p>You can set where the widget is displayed in your store in the ‘Settings’ tab.</p>
                            </div>
                            <div class="card-content-bottom">
                                <a href="<?php echo CURBON_SETTINGS_URL; ?>">Go to Settings</a>
                            </div>
                        </div>
                    </div>

                    <div class=" step step2">
                        <p class="card-title heading">Customize Design</p>
                        <div class="card-content-cb-wrap">
                            <div class="card-content">
                                <p>You can adjust the style of the widget in the ‘Customiser’ tab</p>
                            </div>
                            <div class="card-content-bottom">
                                <a href="<?php echo CURBON_LOOK_AND_FEEL_URL; ?>">Go to Customiser</a>
                            </div>
                        </div>
                    </div>

                    <div class="step step3">
                        <p
                            class="card-title heading <?php echo ( isset($curbon_settings['widget']) && !empty($curbon_settings['widget']) ) ? '' : 'step-pending'; ?>">
                            Enable Widget</p>
                        <div class="card-content-cb-wrap">
                            <div class="card-content">
                                <p>Enable the widget in the ‘settings’ tab</p>
                            </div>
                            <div class="card-content-bottom">
                                <a href="<?php echo CURBON_SETTINGS_URL; ?>#curbon-enable-widget">Go to Settings</a>
                            </div>
                        </div>
                    </div>

                    <div class="step step4">
                        <p class="card-title heading">Emmission Evaluation</p>
                        <div class="card-content-cb-wrap">
                            <div class="card-content">
                                <p>Complete store emission evaluation form to give your customer's the most accurate
                                    carbon offsetting solution. </p>
                            </div>
                            <div class="card-content-bottom">
                                <a href="<?php echo esc_url( $config_response->links->emission_form ); ?>" target="_blank">Go to
                                    Form</a>
                            </div>
                        </div>
                    </div>

                    <div class="step step5">
                        <p
                            class="card-title heading">
                            Plugin Autoupdates</p>
                        <div class="card-content-cb-wrap">
                            <div class="card-content">
                                <p>Enable auto updates for Curbon so that you get our latest features as soon as they
                                    become available.</p>
                            </div>
                            <div class="card-content-bottom">
                                <a href="/wp-admin/plugins.php">Go to Plugins</a>
                            </div>
                        </div>
                    </div>

                    <div class="step step6">
                        <p class="card-title heading">Billing Information</p>
                        <div class="card-content-cb-wrap">
                            <div class="card-content">
                                <p>Fill in your billing information so that we use the correct information on your invoices.</p>
                            </div>
                            <div class="card-content-bottom">
                                <a href="<?php echo CURBON_ACCOUNT_URL; ?>#billingdetails"
                                    target="_blank">Go to Account</a>
                            </div>
                        </div>
                    </div>

                    <div class="step step7">
                        <p class="card-title heading">Need Help?</p>
                        <div class="card-content-cb-wrap">
                            <div class="card-content">
                                <p>If you have any questions, check out our FAQ or reach out to our support team</p>
                            </div>
                            <div class="card-content-bottom">
                                <a href="mailto:<?php echo esc_html( $config_response->emails->support ); ?>?subject=Curbon%20help%20needed%20for%20%5B<?php echo esc_url( get_site_url() ); ?>%5D"
                                    target="_blank">Contact Us</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>



        </div>
        <?php // @codingStandardsIgnoreEnd ?>