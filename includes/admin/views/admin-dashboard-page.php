<?php
$active_tab = "";
if(isset($_GET['page']) && $_GET['page'] = "curbon-dashboard"){
    if(isset($_GET['tab'])){
        $active_tab = sanitize_text_field($_GET['tab']);
    }else{
        $active_tab = "dashboard";
    }
}
$disabled = $curbon_shop_status_option = '';
if( isset( $_GET['tab'] ) && 'curbon-onboarding' != $_GET['tab'] ){

    $curbon_laravel_api_access_token    = get_option( 'curbon_laravel_api_tokens' )['access_token'];

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

    $fetch_customer_response   = wp_remote_retrieve_body($response);
    $fetch_customer_response   = json_decode($fetch_customer_response, true);

    /** WP REMOTE GET END **/

    $shop_status = get_option( 'curbon-shop-status' );

    if( false == $fetch_customer_response['success'] || 'blocked' == $shop_status ){
        $disabled = 'curbon-blocked-blur';
        $curbon_shop_status_option = 'blocked';
    }

    update_option( 'curbon-shop-status', $curbon_shop_status_option );
}
//Fetch cart offset percentage end

/*
get onboarding status
*/

if($active_tab != "curbon-onboarding"){
    $curbon_onboarding_status = get_option('curbon-onboarding-status');
   
    if($curbon_onboarding_status['status'] != "complete"){
        $onboarding_url =  get_admin_url().'admin.php?page=curbon-dashboard&tab=curbon-onboarding';
        wp_redirect($onboarding_url);
        exit();
    }
}
?>

<div class="wrap">
    <div class="wrap-bg">
        <div class="Curbon-dashboard-bg">
            <div class="dashboard-container">
                <div class="curbon-dashboar-inner">
                    <a href="#"><img
                            src="<?php echo CURBON_PLUGIN_FILE_URL ; ?>assets/images/Curbon_logo_black-2-1.png"
                            alt="logo"></a>
                </div>
            </div>
        </div>
        <?php 
        //list of all global notice
        $global_notice = get_option( 'curbon-global-notice' );
        if(!empty($global_notice)){

            foreach ($global_notice as $key => $notice) {
                if( !empty($notice) ){
                ?>
        <div class="curbon-error">
            <p><?php echo esc_html($notice); ?></p>
        </div>
        <?php        
                }

                if($key == "card_update_notice"){
                    unset($global_notice[$key]);
                }
            }
        }
        update_option( 'curbon-global-notice', $global_notice );
        
    ?>
        <div class="curbon-loading">


        </div>
        <div class="dashboard-container <?php echo esc_attr($disabled); ?>">
            <?php 
            if( '' !== $disabled ){
                echo '<p class="blur-text">'. esc_html($fetch_customer_response['message']) ?? "An error occured! Please contact Team Curbon " .'<br/>By Clicking <a href="#"> here</a></p>';
            }
        ?>

            <form method="post" id="curbon_form" action="" enctype="multipart/form-data">
                <?php if( isset( $_GET['page'] ) && "curbon-onboarding" !== $_GET['tab']  ){ ?>
                <header class="header">
                    <input class="menu-btn" type="checkbox" id="menu-btn" />
                    <label class="menu-icon" for="menu-btn"><span class="navicon"></span></label>
                    <ul class="menu">
                        <li> <a href="?page=curbon-dashboard&amp;tab=dashboard"
                                class="nav-tab <?php if($active_tab == "dashboard") echo "nav-tab-active"; ?>">Dashboard</a>
                        </li>
                        <li> <a href="?page=curbon-dashboard&amp;tab=curbon-settings"
                                class="nav-tab <?php if($active_tab == "curbon-settings") echo "nav-tab-active"; ?>">Settings</a>
                        </li>
                        <li> <a href="?page=curbon-dashboard&amp;tab=curbon-look-and-feel"
                                class="nav-tab <?php if($active_tab == "curbon-look-and-feel") echo "nav-tab-active"; ?>">Customise
                                Cart</a></li>
                        <li>
                            <a href="?page=curbon-dashboard&amp;tab=curbon-card-manager"
                                class="nav-tab <?php if($active_tab == "curbon-card-manager") echo "nav-tab-active"; ?>">Account</a>
                        </li>
                    </ul>

                </header>
                <?php } ?>
                <?php  require_once CURBON_PLUGIN_PATH . 'includes/admin/views/'.$active_tab.'.php'; ?>
            </form>
        </div>
    </div>
</div>