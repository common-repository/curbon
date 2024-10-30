<?php
/**
 * Admin page save settings
 * Save funstions of Admin page
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
 * Saving settings from the admin page
 * 
 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @link     https://curbon.io/
 */
class CURBON_Admin_Save_Settings
{

    public function __construct() // phpcs:ignore
    {

        add_action(
            'woocommerce_update_product', 
            array( 
                $this, 
                'saveCurbonOnProductSave' 
            ), 
            10, 
            1
        );
        
        add_action(
            'admin_init', 
            array( 
                $this, 
                'saveCurbonSettingsCallback' 
            )
        );

        add_action(
            'admin_init', 
            array( 
                $this, 
                'saveCurbonLookAndFeelCallback' 
            )
        );

        add_action(
            'admin_init', 
            array( 
                $this, 
                'saveCurbonOnboardingDataCallback' 
            )
        );

    }

    /**
     * This function is used to check and
     * make sure to update offset price greater than 0
     *
     * @param $product_id which product is updatd
     * 
     * @return NULL saving products data on products save
     */
    public function saveCurbonOnProductSave( $product_id )
    {

        /*
            get onboarding_option_data to get the offset product id. 
            key : carbon_offset_product_id
        */
        $curbon_onboarding_status 
            = get_option('curbon-onboarding-status');
        $carbon_offset_product_id 
            = $curbon_onboarding_status['carbon_offset_product_id'];
        
        if ($carbon_offset_product_id 
            && ( $carbon_offset_product_id == $product_id ) 
        ) {
            $curbon_settings_options 
                = get_option('curbon_settings_options');
            $curbon_offset_amount    
                = $curbon_settings_options['curbon_offset_amount'];

            if (isset($_POST['_regular_price']) 
                && $_POST['_regular_price'] < $curbon_offset_amount 
            ) {
                update_post_meta(
                    $carbon_offset_product_id, 
                    '_price', 
                    $curbon_offset_amount
                );
                update_post_meta(
                    $carbon_offset_product_id, 
                    '_regular_price', 
                    $curbon_offset_amount
                );
            }
        }
    }

    /**
     * This function will trigger when CURBON Settings Tab data is saved/updated
     * 
     * @return NULL saving settings data
     */
    public function saveCurbonSettingsCallback()
    {

        /*onboarding input data*/
        $nonce = $_REQUEST['curbon_settings_nonce_field'] ?? NULL;
        if (! is_admin() 
            || ! isset($_POST['save_curbon_settings'])  
            || !wp_verify_nonce(
                $nonce, 
                'curbon_settings_nonce'
            )
        ) {
            return;
        }

        $curbon_offset_amount = isset($_POST['curbon_offset_amount']) ? sanitize_text_field($_POST['curbon_offset_amount']) : '';

        if ($curbon_offset_amount < 1) {
            $curbon_offset_amount   = 2;
        }

        if (!isset($_POST['curbon_widget_location_on_cart']) 
            && !isset($_POST['curbon_widget_location_on_mini_cart']) 
            && !isset($_POST['curbon_widget_location_on_checkout']) 
        ) {
            $_POST['curbon_widget_location_on_cart'] = 1;
        }

        // @codingStandardsIgnoreStart
        $curbon_settings       = array(
            'curbon_enable_widget_on_cart'         => isset($_POST['curbon_enable_widget_on_cart']) ? sanitize_text_field($_POST['curbon_enable_widget_on_cart']): '0',
            'curbon_widget_location_on_cart'       => isset($_POST['curbon_widget_location_on_cart']) ? sanitize_text_field($_POST['curbon_widget_location_on_cart']): '0',
            'curbon_widget_location_on_mini_cart'  => isset($_POST['curbon_widget_location_on_mini_cart']) ? sanitize_text_field($_POST['curbon_widget_location_on_mini_cart']): '0',
            'curbon_widget_location_on_checkout'   => isset($_POST['curbon_widget_location_on_checkout']) ? sanitize_text_field($_POST['curbon_widget_location_on_checkout']): '0',
            'curbon_offset_amount'                 => $curbon_offset_amount,
            'curbon_card_management_prefered_topup' => (isset($_POST['curbon_card_management_prefered_topup']) && $_POST['curbon_card_management_prefered_topup'] >= '20' ) ? sanitize_text_field($_POST['curbon_card_management_prefered_topup']) : '20',

        );
        // @codingStandardsIgnoreEnd

        /*Update shop info when setting details are changes*/
        $args  = array(
            'setup' => isset($_POST['curbon_enable_widget_on_cart']) ? true : false
        );

        $CURBON_Carbonclick_Laravel_API  = new CURBON_Carbonclick_Laravel_API();
        $response = $CURBON_Carbonclick_Laravel_API->curbon_update_shop_info($args);

        /*Logic applied if account is blocked by curbon*/
        if (isset($response['error']) && $response['error'] == 'blocked' ) {
            update_option('curbon-widget-status', 'blocked');
            $global_notice = get_option('curbon-global-notice');
            $global_notice['payment_failure'] = $response['message'];
            update_option('curbon-global-notice', $global_notice);
            
            $curbon_settings['curbon_enable_widget_on_cart']  = 0;
        } else {
            update_option('curbon-widget-status', 'unblocked');
            $global_notice = get_option('curbon-global-notice');
            $global_notice['payment_failure'] = "";
            update_option('curbon-global-notice', $global_notice);
        }
        
        
        update_option('curbon_settings_options', $curbon_settings);

        /*  
            get onboarding_option_data to get the offset product id.
            key : carbon_offset_product_id
        */
        $curbon_onboarding_status 
            = get_option('curbon-onboarding-status');
        $carbon_offset_product_id 
            = $curbon_onboarding_status['carbon_offset_product_id'];
        if ($carbon_offset_product_id) {
            update_post_meta(
                $carbon_offset_product_id, 
                '_price', 
                $curbon_offset_amount
            );
            update_post_meta(
                $carbon_offset_product_id, 
                '_regular_price', 
                $curbon_offset_amount
            );
        }

        add_action('admin_notices', array( $this, 'adminNotices' ));

    }

    /**
     * This function will trigger when 
     * CURBON Look and Feel Tab data is saved/updated
     * 
     * @return NULL saving Look and Feeling data
     */
    public function saveCurbonLookAndFeelCallback()
    {

        if (! is_admin() 
            || ! isset($_POST['save_curbon_look_and_feel'])  
            || !wp_verify_nonce(
                $_REQUEST['curbon_look_and_feel_nonce_field'], 
                'curbon_look_and_feel_nonce'
            )
        ) {
            return;
        }

        $curbon_look_and_feel = sanitize_post($_POST['curbon_look_and_feel']);
     
        update_option('curbon_look_and_feel_options', $curbon_look_and_feel);
        
        $this->updateFeaturedImageOnLookAndFeelCallback($curbon_look_and_feel);
        
        add_action('admin_notices', array( $this, 'adminNotices' ));
        
    }

    /**
     * Save Onboarding data
     * 
     * @return NULL saving onboarding data
     */
    public function saveCurbonOnboardingDataCallback()
    {
        
        if (! is_admin() 
            || !isset($_POST['stripeToken']) 
            || !wp_verify_nonce(
                $_REQUEST['curbon_onboarding_next_nonce_field'], 
                'curbon_onboarding_next_nonce'
            ) 
        ) {
            if (! is_admin() 
                || ! isset($_POST['onboarding_next'])  
                || !wp_verify_nonce(
                    $_REQUEST['curbon_onboarding_next_nonce_field'], 
                    'curbon_onboarding_next_nonce'
                )
            ) {
                return;
            }
        }
        
        $stripe_process_completed   = false;
   
        $onboarding_previous_step 
            = sanitize_text_field($_POST['onboarding_previous_step']);
        $onboarding_current_step 
            = sanitize_text_field($_POST['onboarding_current_step']);
        $onboarding_next_step 
            = sanitize_text_field($_POST['onboarding_next_step']);
        

        $curbon_onboarding_status = get_option('curbon-onboarding-status');
        $curbon_onboarding_status['steps']['step-'.$onboarding_current_step] 
            = 'complete';


        update_option('curbon-onboarding-status', $curbon_onboarding_status);
        
        if ($curbon_onboarding_status['status'] == "complete") {
            
            $curbon_settings_url 
                =  get_admin_url().
                    'admin.php?page=curbon-dashboard&tab=dashboard&tab=dashboard';

            wp_redirect($curbon_settings_url);
            exit();

        } else {
            
            /*If onboarding process is pending, then redirect user to next steps*/
            if ($onboarding_next_step == 1 || $onboarding_next_step == 2) {
                $onboarding_next_step = $onboarding_next_step;
            } else {
                $onboarding_next_step = 2;
            }
            
            $onboarding_url 
                =  get_admin_url().
                'admin.php?page=curbon-dashboard&tab=curbon-onboarding&step='.
                $onboarding_next_step;

            wp_redirect($onboarding_url);
            exit();
        }
        
    }

    /**
     * Show Notice on Admin Pages
     * 
     * @return NULL
     */
    public function adminNotices()
    {

        ?>
        <div class="curbon-updated">
          <p>Settings saved successfully</p>
        </div>
        <?php
       
    }

    /**
     * This snippet is used to update the featured image 
     * of the product based on look and feel data
     * 
     * @param $curbon_look_and_feel Options
     * 
     * @return bool
     */
    public function updateFeaturedImageOnLookAndFeelCallback($curbon_look_and_feel = array()) // phpcs:ignore
    {
        $curbon_onboarding_status = get_option('curbon-onboarding-status');
        if (isset($curbon_onboarding_status['carbon_offset_product_id'])) {
            
            $product_id = $curbon_onboarding_status['carbon_offset_product_id'];
            
            $curbon_product_image  = $curbon_look_and_feel['curbon_product_image'];

            $curbon_carbon_product_image_as_featured 
                = get_post_meta(
                    $product_id, 
                    'curbon_carbon_product_image_as_featured', 
                    true
                );

            if ($curbon_carbon_product_image_as_featured != $curbon_product_image ) {

                    update_post_meta(
                        $product_id, 
                        'curbon_carbon_product_image_as_featured', 
                        $curbon_product_image
                    );
                
                    $image_url = CURBON_PLUGIN_URL."assets/images/look-and-feel/cloud-".$curbon_product_image.".png"; //phpcs:ignore
                
                    $this->curbonGenerateFeaturedImageLookFeelCallback(
                        $image_url, 
                        $product_id
                    );
            }
        }

        return true;
    }
    

    /**
     * This function is used to update the featured image
     * based on setting done in look and feel tab
     *
     * @param $image_url Featured Image URL
     * @param $post_id   Post ID for Featured Image URL
     * 
     * @return NULL
     */
    public function curbonGenerateFeaturedImageLookFeelCallback( $image_url, $post_id  ) // phpcs:ignore
    {
        $upload_dir = wp_upload_dir();
        $image_data = wp_remote_retrieve_body( wp_remote_get(($image_url) ) );
        $filename   = basename($image_url);
        
        if (has_post_thumbnail($post_id)) {
            $attachment_id = get_post_thumbnail_id($post_id);
            wp_delete_attachment($attachment_id, true);
        }

        if (wp_mkdir_p($upload_dir['path'])) {
            $file   = $upload_dir['path'] . '/' . $filename;
        } else {
            $file   = $upload_dir['basedir'] . '/' . $filename;
        }

        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);
        
        $attachment = array(
            'post_mime_type'    => $wp_filetype['type'],
            'post_title'        => sanitize_file_name($filename),
            'post_content'      => '',
            'post_status'       => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $file, $post_id);

        include_once ABSPATH . 'wp-admin/includes/image.php';
        
        $attach_data    = wp_generate_attachment_metadata($attach_id, $file);
        set_post_thumbnail($post_id, $attach_id);
    }

} // end of class CURBON_Admin_Save_Settings

$curbon_admin_save_settings = new CURBON_Admin_Save_Settings;