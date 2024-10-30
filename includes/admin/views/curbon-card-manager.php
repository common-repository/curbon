<?php 
/**
 * HTML of Admin::Accounts
 * View of Accounts page
 * php version 7.4

 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @version  GIT: @1.0.0@
 * @link     https://curbon.io/
 */
    $CURBON_Carbonclick_Laravel_API = new CURBON_Carbonclick_Laravel_API();
    $fetch_card_response = $CURBON_Carbonclick_Laravel_API->curbonFetchCustomer();
        
    // if (!empty($fetch_card_response['success']) 
    //     && ( $fetch_card_response['success'] == true 
    //     || $fetch_card_response['success'] == 1 ) 
    // ) {
        
    //     $last4      = $fetch_card_response['data']['last4'];
    //     $exp_month  = $fetch_card_response['data']['exp_month'];
    //     $exp_year   = $fetch_card_response['data']['exp_year'];
    //     $topup       = $fetch_card_response['topup'];

    // } else {

    //     $last4  = $exp_month = $exp_year = $topup = 0;
    // }
    
    wp_enqueue_script('jquery-ui-tooltip');

    $response_code = get_option('curbon_laravel_api_response_code');
    $filter_blur = "";

    $curbon_laravel_api_access_token 
        = get_option('curbon_laravel_api_tokens')['access_token'];

    /** WP REMOTE GET **/

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

    $curbon_shop_details_response   = wp_remote_retrieve_body($response);
    $curbon_shop_details_response   = json_decode($curbon_shop_details_response, true);

    /** WP REMOTE GET **/


    /** CALL BILLING DETAILS SAVE API  **/

    if(isset($_POST['save_curbon_billing_settings'])){
        
        $keys = array(
                'company_name'          => sanitize_text_field($_POST['companyname']),
                'building_unit_number'  => sanitize_text_field($_POST['buildingunitnumber']),
                'building_name'         => sanitize_text_field($_POST['buildingname']),
                'email_address'         => sanitize_email($_POST['billingemailaddress']),
                'street_address'        => sanitize_text_field($_POST['streetaddress']),
                'suburb'                => sanitize_text_field($_POST['suburb']),
                'town'                  => sanitize_text_field($_POST['town']),
                'country'               => sanitize_text_field($_POST['country']),
                'postal_address'        => sanitize_text_field($_POST['postaladdress']),
                'vat_number'            => sanitize_text_field($_POST['vatnumber']),
            );  

        $response = $CURBON_Carbonclick_Laravel_API->curbon_update_shop_billing_info($keys);

        $submitmsg = '';
        if (isset($response['errors'])) {                    
            $submitmsg = '<p style="color:red;font-size:18px;">'.esc_html($response['message']).'</p>';
        } else {
            $submitmsg = '<p style="color:green;font-size:18px;">Billing Detail Saved.</p>';
        }

    }

    ?>

<div class="dashboard-card">
    <div class="dashboard-card-inner">
        <div class="dashboard-card-items itmes-1">
            <div class="dashborard-card-content">
                <p class="dashboardh2">
                    <?php 
                        echo CURBON_TOTAL_OFFSET_ORDERS ?? '0'; 
                    ?>
                </p>
                <p>Order with offsets</p>
            </div>
        </div>
        <div class="dashboard-card-items itmes-2">
            <div class="dashborard-card-content">
                <p class="dashboardh2">
                    <?php 
                        echo get_woocommerce_currency_symbol() . 
                            ( CURBON_TOTAL_OFFSET_AMOUNT ?? '0' ); 
                    ?>
                </p>
                <p>Offsets collected</p>
            </div>
        </div>
    </div>
</div>

<div class="card-manager-tab-wrapper <?php echo esc_attr($filter_blur); ?>">
    <div class="card-manager-tab-bg">
        <p class="card-title heading">Your Account</p>
        <div class="carddetails">
            <table cellspacing="0" cellpadding="0">
                <tr>
                    <th>
                        <div>
                            <label class="table-text">Offset Credit Spend</label>

                        </div>
                    </th>
                    <th>
                        <div>
                            <label class="table-text">Offset Credit Balance</label>

                        </div>
                    </th>

                </tr>
                <tr>
                    <td>
                        <div>
                            <label
                                class="table-data-text">
                                <?php 
                                echo get_woocommerce_currency_symbol() . esc_html( ( $curbon_shop_details_response['orders'] - $curbon_shop_details_response['offsets'] ) ); //phpcs:ignore 
                                ?></label>

                        </div>
                    </td>
                    <td>
                        <div>
                            <label
                                class="table-data-text">
                                <?php echo get_woocommerce_currency_symbol() . 
                                    esc_html( $curbon_shop_details_response['balance_remaining'] ?? 0 );
                                ?>
                            </label>

                        </div>
                    </td>

                </tr>

            </table>
        </div>

        <?php
            // @codingStandardsIgnoreStart 
            /** WP REMOTE POST **/

            $body = array(
                    "type"          => "woocommerce",
                    "email"         => get_option('admin_email'),
                    "domain"        => get_site_url(),
                    "update_card"   => true,
                );
        

            $headers = array(
                "Accept"        => "application/json",
                "Content-Type"  => "application/json",
                "Authorization" =>  "Bearer " . $curbon_laravel_api_access_token
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

            $payment_url   = wp_remote_retrieve_body($response);
            $payment_url   = json_decode($payment_url, true);

            /** WP REMOTE POST **/
            if(isset($_GET['trxref']) && !empty($_GET['trxref']) ) {

                $last_verified_trxref = get_option('curbon_last_updated_trxref');
                $current_trxref = sanitize_text_field($_GET['trxref']);

                if($current_trxref == $last_verified_trxref ) {
                    echo "<h3 class='error-msg'>Sorry! this transaction is not valid!</h3>";
                }else{

                    update_option('curbon_last_updated_trxref', $current_trxref);

                    echo "<h3 class='congrats-msg'>Congratulations! Your card has been verified and udated! 1 ZAR is added to your Offset Credit Balance.</h3>";

                    /** WP REMOTE POST **/

                    $body = array(
                            "type"          => "woocommerce",
                            "trxref"        => $current_trxref,
                        );
                

                    $headers = array(
                        "Accept"        => "application/json",
                        "Content-Type"  => "application/json",
                        "Authorization" =>  "Bearer " . $curbon_laravel_api_access_token
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
                    $curbon_payment_response   = json_decode($curbon_payment_response, true);

                    /** WP REMOTE POST **/
                    
                }

                if($curbon_payment_response['success'] ) {
                    global $woocommerce;

                    $curbon_settings = get_option('curbon_settings_options');

                    /** WP REMOTE POST **/

                    $body = array(
                            "orders_count"          => CURBON_TOTAL_OFFSET_ORDERS,
                            "last_impression"       => false,
                            "setup"                 => true,
                            "version"               => $woocommerce->version,
                            "preferred_topup"       => $curbon_settings['topup-amount'],
                        );
                

                    $headers = array(
                        "Accept"        => "application/json",
                        "Content-Type"  => "application/json",
                        "Authorization" =>  "Bearer " . $curbon_laravel_api_access_token
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

                    $response   = wp_remote_retrieve_body($response);
                    $response   = json_decode($response, true);

                    /** WP REMOTE POST **/
                }
            }

            ?>

        <div class="update_card">
            <div class="update_card_wrap">
                <p class="update-card-titl">Update Card</p>
                <p>On click, it will redirect to the external Paystack link <br>We will charge
                    <?php echo get_woocommerce_currency_symbol(); ?>1 at that time that will get added to your Account</p>
            </div>
            <a href="<?php echo esc_url( $payment_url['url'] ) ?>" target="_blank" title="Update" class="update_btn">Update</a>
        </div>
    </div>
</div>
<?php       // @codingStandardsIgnoreEnd   ?>