<?php
/**
 * Enqueuing styles & Scripts
 * For public & admin both with the dynamic css
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
 * CURBON_Enqueue_Scripts class responsable 
 * to load all the scripts and styles.
 * 
 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @link     https://curbon.io/
 */
class CURBON_Enqueue_Scripts
{

    public function __construct() //phpcs:ignore
    {

        add_action(
            'wp_enqueue_scripts', 
            array( 
                $this, 
                'publicEnqueueScripts' 
            ), 
            200
        );

        add_action(
            'wp_enqueue_scripts', 
            array( 
                $this, 
                'publicDynamicResources'
            ), 
            200
        );

        add_action(
            'admin_enqueue_scripts', 
            array( 
                $this, 
                'adminEnqueueFonts'
            ), 
            200
        );

    }

    /**
     * Enqueuing scripts & style for Front-end
     *
     * @return NULL "Adding scripts & style"
     */
    public function publicEnqueueScripts()
    {
        // Load public scripts
        wp_enqueue_style(
            'curbon-public-style', 
            CURBON_PLUGIN_URL . 'assets/css/curbon-public-style.css', 
            array(), 
            CURBON_PLUGIN_VER
        );

        wp_enqueue_script(
            'curbon-public-script', 
            CURBON_PLUGIN_URL . 'assets/js/curbon-public-script.js', 
            array( 'jquery' ), 
            CURBON_PLUGIN_VER, 
            true
        );
    }

    /**
     * Enqueuing fonts for Admin Panel
     *
     * @return NULL "Adding fonts"
     */
    public function adminEnqueueFonts()
    {
        wp_enqueue_script(
            'curbon-NeurialGrotesk-font', 
            CURBON_PLUGIN_URL . 'assets/fonts/NeurialGrotesk-Regular.otf', 
            null, 
            CURBON_PLUGIN_VER
        );

        wp_enqueue_script(
            'curbon-OpenSauceSans-Medium-font', 
            CURBON_PLUGIN_URL . 'assets/fonts/OpenSauceSans-Medium.ttf', 
            null, 
            CURBON_PLUGIN_VER
        );

        wp_enqueue_script(
            'curbon-OpenSauceSans-Regular-font', 
            CURBON_PLUGIN_URL . 'assets/fonts/OpenSauceSans-Regular.ttf', 
            null, 
            CURBON_PLUGIN_VER
        );

        wp_enqueue_script(
            'curbon-OpenSauceSans-SemiBold-font',
            CURBON_PLUGIN_URL . 'assets/fonts/OpenSauceSans-SemiBold.ttf', 
            null, 
            CURBON_PLUGIN_VER
        );
    }

    /**
     * Dynamic CSS as per the customizer
     *
     * @return NULL "Adding dynamic CSS to front-end"
     */
    public function publicDynamicResources()
    {
        // @codingStandardsIgnoreStart
        $curbon_look_and_feel_options = get_option('curbon_look_and_feel_options');
        // extract($curbon_look_and_feel_options);

        $curbon_look_and_feel_options = $curbon_look_and_feel_options['colors'];

        $dynamic_css = '';

        $dynamic_css .= '
        .curbon-box-cart-left .curbon-regular-logo{background: ' . $curbon_look_and_feel_options['primary_color'] . ';}
        .curbon-box-cart-left .curbon-box-cart-left-box .curbon-regular-logo svg path{fill: ' . $curbon_look_and_feel_options['background_color'] . ';}
        .curbon-box-cart-left svg rect{fill: ' . $curbon_look_and_feel_options['primary_color'] . ';}
        .curbon-box-cart-left svg path:nth-child(1){fill: ' . $curbon_look_and_feel_options['secondary_color'] . ';}
        .curbon-box-wrap{border-color: ' . $curbon_look_and_feel_options['secondary_color'] . '; background: ' . $curbon_look_and_feel_options['background_color'] . ';}
        .curbon-box-cart-right a.learn_more svg path{fill: ' . $curbon_look_and_feel_options['primary_color'] . ';}
        .curbon-box-cart-right a.learn_more:after{background: ' . $curbon_look_and_feel_options['primary_color'] . '; border-color: ' . $curbon_look_and_feel_options['secondary_color'] . ';}
        .curbon-box-cart-left .curbon-box-cart-right-box a.learn_more:after{background: ' . $curbon_look_and_feel_options['secondary_color'] . ';}
        
        
        .curbon-box-cart-right .button_two button{background: ' . $curbon_look_and_feel_options['primary_color'] . '; color: ' . $curbon_look_and_feel_options['background_color'] . ';}
        .curbon-box-cart-right .button_two button:hover{color: ' . $curbon_look_and_feel_options['primary_color'] . ';}
        .curbon-box-cart-right .button_two a.btn-optout{border-color: ' . $curbon_look_and_feel_options['secondary_color'] . ';color: ' . $curbon_look_and_feel_options['primary_color'] . ';}
        .curbon-box-cart-right .button_two a.btn-optout:hover{background: ' . $curbon_look_and_feel_options['primary_color'] . '; color: ' . $curbon_look_and_feel_options['background_color'] . ';}
        

        
        .curbon-box-cart-right .button_two.success-btn-two a.btn-optout{color: ' . $curbon_look_and_feel_options['text_color'] . ';}
        .curbon-box-cart-right .button_two.success-btn-two a.btn-optout:hover{color: ' . $curbon_look_and_feel_options['primary_color'] . ';}
        .curbon-box-cart-right .button_two.success-btn-two a.btn-optout:after{background: ' . $curbon_look_and_feel_options['primary_color'] . ';}

        .curbon-box-cart-left p{color: ' . $curbon_look_and_feel_options['text_color'] . ';}
        .curbon-box-cart-right a.learn_more{color: ' . $curbon_look_and_feel_options['text_color'] . ';}

        #slide_down_wrapper{border-color: ' . $curbon_look_and_feel_options['secondary_color'] . ';}

        ul.woocommerce-mini-cart form .customize-view-bg-box,
        .costomize-btn-primary{
            border-color: ' . $curbon_look_and_feel_options['secondary_color'] . ';
        }
        .costomize-btn-transparent{
            border-color: ' . $curbon_look_and_feel_options['secondary_color'] . ';
        }
        .costomize-selection .select{
            border-color: ' . $curbon_look_and_feel_options['secondary_color'] . ';
        }
        .customize-view-bg-box-imges.left-side-box-customize{
            border-color: ' . $curbon_look_and_feel_options['secondary_color'] . ';
        }
        .customize-Expanded-view-bg .customize-Expanded-view-inner{
            border-color: ' . $curbon_look_and_feel_options['secondary_color'] . ';
        }
        
        .curbon-box-cart-right .button_two.success-btn-two button{
            border-color: ' . $curbon_look_and_feel_options['secondary_color'] . ';
            background: ' . $curbon_look_and_feel_options['primary_color'] . ';
            color: ' . $curbon_look_and_feel_options['background_color'] . ';

        }
        .curbon-box-cart-right .button_two.success-btn-two button:hover{
            border-color: ' . $curbon_look_and_feel_options['secondary_color'] . ';
            background: ' . $curbon_look_and_feel_options['primary_color'] . ';
        }

        .curbon-box-cart-right .button_two.success-btn-two button.curbon-add-offset:hover{
            color: ' . $curbon_look_and_feel_options['primary_color'] . ';
        }

        .curbon-box-cart-right .button_two.success-btn-two button svg{
            fill: ' . $curbon_look_and_feel_options['background_color'] . ';
        }

        .curbon-box-cart-right .button_two.success-btn-two button:focus{
            outline: none;
        }

        ul.woocommerce-mini-cart form .customize-view-bg-box{
            background: ' . $curbon_look_and_feel_options['background_color'] . ';
        }
        ul.woocommerce-mini-cart form .customize-view-bg-box .customize-view-bg-itmes .content .costomize-logo-box a.curbon-primary-color-reflect .curbon-regular-logo{
            background: ' . $curbon_look_and_feel_options['primary_color'] . ';
        }
        ul.woocommerce-mini-cart form .customize-view-bg-box .customize-view-bg-itmes .content .costomize-logo-box a.curbon-primary-color-reflect .curbon-regular-logo svg path{
            fill: ' . $curbon_look_and_feel_options['background_color'] . ';
        }
        ul.woocommerce-mini-cart form .customize-view-bg-box .customize-view-bg-itmes .content .costomize-view p.curbon-mini-cart-view-text{
            color: ' . $curbon_look_and_feel_options['text_color'] . ';
        }
        ul.woocommerce-mini-cart form .customize-view-bg-box .customize-view-bg-itmes .costomize-btn button {
            background: ' . $curbon_look_and_feel_options['primary_color'] . ';
            color: ' . $curbon_look_and_feel_options['background_color'] . ';
            border-color: ' . $curbon_look_and_feel_options['primary_color'] . ';
        }
        ul.woocommerce-mini-cart form .customize-view-bg-box .customize-view-bg-itmes .costomize-btn button:hover{
            background: ' . $curbon_look_and_feel_options['background_color'] . ';
            color: ' . $curbon_look_and_feel_options['primary_color'] . ';
        }
        ul.woocommerce-mini-cart form .customize-view-bg-box .customize-view-bg-itmes .costomize-btn button.curbon-add-offset:hover{
            background: ' . $curbon_look_and_feel_options['background_color'] . ';
        }
        ul.woocommerce-mini-cart form .customize-view-bg-box .customize-view-bg-itmes .costomize-btn button.curbon-add-offset:hover:before{
            background: ' . $curbon_look_and_feel_options['background_color'] . ';
        }
        ul.woocommerce-mini-cart form .customize-view-bg-box .customize-view-bg-itmes .costomize-btn a.costomize-btn-transparent[href^="https://"]{
            background: ' . $curbon_look_and_feel_options['background_color'] . ';
            border-color: ' . $curbon_look_and_feel_options['secondary_color'] . ';
        }
        ul.woocommerce-mini-cart form .customize-view-bg-box .customize-view-bg-itmes .costomize-btn a.costomize-btn-transparent[href^="https://"]:hover{
            background: ' . $curbon_look_and_feel_options['primary_color'] . ';
        }
        ul.woocommerce-mini-cart form .customize-view-bg-box .customize-view-bg-itmes .costomize-btn a.costomize-btn-transparent[href^="https://"] svg path{
            fill: ' . $curbon_look_and_feel_options['primary_color'] . ';
        }
        ul.woocommerce-mini-cart form .customize-view-bg-box .customize-view-bg-itmes .costomize-btn a.costomize-btn-transparent[href^="https://"]:hover svg path{
            fill: ' . $curbon_look_and_feel_options['background_color'] . ';
        }
        .curbon-box-cart-right .button_two.success-btn-two button.btn-optout[type="submit"]:hover{
            color: ' . $curbon_look_and_feel_options['primary_color'] . ';
        }
        .curbon-box-cart-right .button_two.success-btn-two button.btn-optout[type="submit"]:after{
            border-color: ' . $curbon_look_and_feel_options['secondary_color'] . ';
        }
        .curbon-box-cart-right .button_two.success-btn-two button.btn-optout[type="submit"]:hover:after{
            border-color: ' . $curbon_look_and_feel_options['secondary_color'] . ';
        }
        ';
        // @codingStandardsIgnoreEnd
        wp_add_inline_style('curbon-public-style', $dynamic_css);
    }
    
} // end of class CURBON_Enqueue_Scripts

$curbon_enqueue_scripts = new CURBON_Enqueue_Scripts;