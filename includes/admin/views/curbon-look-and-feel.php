<?php 
/**
 * HTML of Admin::Customize Cart Page
 * View of Customizer Cart page
 * php version 7.4

 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @version  GIT: @1.0.0@
 * @link     https://curbon.io/
 */
if ($_POST ) {
    // @codingStandardsIgnoreStart 
    if (! wp_verify_nonce($_POST['curbon_customizer_nonce_field'], 'curbon_customizer_nonce') ) {
        die(__('Security check', 'textdomain')); 
    } else { 
        $curbon_laravel_api_access_token    = get_option('curbon_laravel_api_tokens')['access_token'];

        $update_look_and_feel_options = [
            "caption_id"        => sanitize_text_field($_POST['curbon-selected-caption']) ?? '',
            "infographics_id"   => sanitize_text_field($_POST['infograpic_text']) ?? '',
            "colors"            => [
                                    "primary_color"     => sanitize_text_field($_POST['curbon-primary-color']) ?? '',
                                    "secondary_color"   => sanitize_text_field($_POST['curbon-secondary-color']) ?? '',
                                    "background_color"  => sanitize_text_field($_POST['curbon-background-color']) ?? '',
                                    "text_color"        => sanitize_text_field($_POST['curbon-text-color']) ?? '',
                                ],
        ];

        update_option('curbon_look_and_feel_options', $update_look_and_feel_options);

        /** WP REMOTE POST **/

        $body = array(
            "orders_count"              => CURBON_TOTAL_OFFSET_ORDERS,
            "infographic_id"            => $update_look_and_feel_options['infographics_id'],
            "caption_id"                => $update_look_and_feel_options['caption_id']
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
            CURBON_API_LARAVEL_URL."api/v1/shops", 
            $args
        );

        $update_infographic_caption_id   = wp_remote_retrieve_body($response);

        /** WP REMOTE POST **/

    }
}
// @codingStandardsIgnoreStart 

function infographics_base_64_getter( $infographics ) // phpcs:ignore
{
    $infographics_img_content = wp_remote_retrieve_body( wp_remote_get( $infographics ) );
    return base64_encode($infographics_img_content);
}

    $curbon_look_and_feel_options 
        = get_option('curbon_look_and_feel_options');
    $curbon_settings_auto_debit 
        = get_option('curbon_settings_options')['auto_debit_offset'] ?? '';

    // Captions & InfoGraphics API Call
    /** WP REMOTE POST **/

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
        CURBON_API_LARAVEL_URL."api/v1/templates", 
        $args
    );

    $captions_infographics_response   = wp_remote_retrieve_body($response);
    $captions_infographics_response   = json_decode($captions_infographics_response, true);

    /** WP REMOTE POST **/

    $curbon_logo = "<span class='curbon-regular-logo'>" . 
        wp_remote_retrieve_body( wp_remote_get(
            CURBON_PLUGIN_URL . 'assets/images/curbon-icon.svg'
        ) )  .
            "</span>";

    $auto_debit = "";
    if ('on' == $curbon_settings_auto_debit ) {
        $auto_debit = "auto_debit_offset";
        $curbon_logo = wp_remote_retrieve_body( wp_remote_get(
            CURBON_PLUGIN_URL . 'assets/images/curbon-logo.svg'
        ) );

    }

    // Captions & API Call Ends
    // @codingStandardsIgnoreStart 

    $default_look_and_feel_set = [
        "caption_id"        => $captions_infographics_response['captions'][0]['id'],
        "caption"           => $captions_infographics_response['captions'][0]['caption'],
        "infographics_id"   => $captions_infographics_response['infographics'][0]['id'],
        "infographics"      => $captions_infographics_response['infographics'][0]['text'], 
        "colors"            =>[
                                "primary_color"     => "#A7F19E",
                                "secondary_color"   => "#A09FFA",
                                "background_color"  => "#FFFFFF",
                                "text_color"        => "#000000",
                            ],
    ];
    // @codingStandardsIgnoreEnd

    $caption_key        = array_search(
        $curbon_look_and_feel_options['caption_id'], 
        array_column(
            $captions_infographics_response['captions'], 
            'id'
        )
    );

    $infographics_key   = array_search(
        $curbon_look_and_feel_options['infographics_id'], 
        array_column(
            $captions_infographics_response['infographics'], 
            'id'
        )
    );



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
    
    // @codingStandardsIgnoreStart 
?>

<div class="customise-wrapper">
<form name="curbon-customize-cart-settings" action="" method="post">
    <div class="customise-inner">
        <div class="customise-items items-1">
            <p class="overflowH margin0">
                <button name="save_curbon_settings" class="btn-primary" type="submit" value="Save changes">Save
                changes</button>
                <?php wp_nonce_field('curbon_customizer_nonce', 'curbon_customizer_nonce_field'); ?>
            </p>
            <p class="card-title heading">Select Caption</p>
            <div class="contomize-Default-view-bg">
                <div class="contomize-Default-view-inner">
                    <div class="contomize-Default-view-items1">
                        <div class="costomize-content">
                            <div class="costomize-logo-box">
                                <a href="#" class="curbon-primary-color-reflect">
                                    <?php echo wp_kses( $curbon_logo, $allowed_tags ); ?>
                                </a>
                            </div>
                            <div class="costomize-selection">
                                <div class="select">
                                    <div class="selectBtn" data-type="<?php echo esc_attr($captions_infographics_response['captions'][$curbon_look_and_feel_options['caption_id']]['id']) ?? esc_attr($default_look_and_feel_set['caption_id']) ?>"><?php echo esc_html( ( isset($caption_key) && false !== $caption_key ) ? $captions_infographics_response['captions'][$caption_key]['caption'] : $default_look_and_feel_set['caption'] ); ?></div>
                                    <div class="selectDropdown">
                                    
                                        <?php 
                                        foreach( $captions_infographics_response['captions'] as $key => $caption ){
                                            ?>
                                                    <div class="option" data-type="<?php echo esc_attr( $caption['id'] ); ?>"><?php echo esc_html($caption['caption']); ?></div>        
                                                <?php
                                        }
                                        ?>
                                        <input type="hidden" id="curbon-selected-caption" name="curbon-selected-caption" value="<?php echo esc_html( $captions_infographics_response['captions'][1]['id'] ?? $default_look_and_feel_set['caption_id'] ); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="contomize-Default-view-items1">
                        <p class="card-title heading">Select Expanded view Template</p>

                        <?php

                        $i = 0;

                        $info_key = array_search($curbon_look_and_feel_options['infographics_id'], array_column($captions_infographics_response['infographics'], 'id'));
                        if(false == $info_key ) {
                            $curbon_look_and_feel_options['infographics_id'] = $captions_infographics_response['infographics'][0]['id'];
                            $infographics_key   = array_search($curbon_look_and_feel_options['infographics_id'], array_column($captions_infographics_response['infographics'], 'id'));

                            update_option('curbon_look_and_feel_options', $curbon_look_and_feel_options);
                        }

                        foreach( $captions_infographics_response['infographics'] as $infographics_array_key => $infographics_data ){
                            
                            $infographics_img_base64 = infographics_base_64_getter($infographics_data['desktop_image']);
                            
                            ?>

                        <div class="tamplate">
                            <div class="costomize-view-template">
                                <input type="radio" data-template="<?php echo esc_attr($infographics_array_key); ?>" id="test<?php echo esc_attr($infographics_array_key); ?>" <?php echo ( $infographics_data['id'] == $curbon_look_and_feel_options['infographics_id'] )? "checked='checked'" : ""; ?> name="infograpic_text" value="<?php echo esc_html($infographics_data['id']); ?>">
                                <label for="test<?php echo esc_attr($infographics_array_key); ?>"> Template <?php echo esc_html(++$i); ?></label>
                            </div>
                            <div class="customize-view-bg-box-imges left-side-box-customize">
                                <p id="infographic-text-<?php echo esc_attr($infographics_array_key); ?>"><?php echo esc_html($infographics_data['text']); ?></p>
                                <div class="img-box">
                                    <img id="infographic-<?php echo esc_attr($infographics_array_key); ?>" src="data:image/png;base64, <?php echo esc_html($infographics_img_base64); ?>" alt="<?php echo esc_html($infographics_data['text']); ?>" />
                                </div>
                                <span class="pwdby_curbon">Powered by</span>
                            </div>

                        </div>

                            <?php
                        }

                        ?>

                    </div>
                </div>
            </div>
            <p>
            <button name="save_curbon_settings" class="btn-primary" type="submit" value="Save changes">Save
                changes</button>
                <?php wp_nonce_field('curbon_customizer_nonce', 'curbon_customizer_nonce_field'); ?>
            </p>
        </div>
        <div class="customise-items items2">
            <p class="card-title heading">Adjust Color</p>
            <div class="customize-items-inner-bg">
                <div class="costomize-adjust-bg">
                    <div class="costomize-adjust-items">
                        <div class="color-picor">
                            <input type="color" value="<?php echo ( isset($curbon_look_and_feel_options['colors']['primary_color']) && !empty($curbon_look_and_feel_options['colors']['primary_color']) ) ? esc_html($curbon_look_and_feel_options['colors']['primary_color']) : esc_html($default_look_and_feel_set['colors']['primary_color']); ?>" id="curbon-primary-color" name="curbon-primary-color" class="active">
                        </div>
                        <div class="color-code">
                            <code id="colorCode"></code>
                            <b>Primary Color </b>
                        </div>
                    </div>
                    <div class="costomize-adjust-items">
                        <div class="color-picor">
                            <input type="color" value="<?php echo ( isset($curbon_look_and_feel_options['colors']['secondary_color']) && !empty($curbon_look_and_feel_options['colors']['secondary_color']) ) ? esc_html($curbon_look_and_feel_options['colors']['secondary_color']) : esc_html($default_look_and_feel_set['colors']['secondary_color']); ?>" id="curbon-secondary-color" name="curbon-secondary-color">
                        </div>
                        <div class="color-code">
                            <code id="colorCode4"></code>
                            <b>Secondary Color</b>
                        </div>
                    </div>
                    <div class="costomize-adjust-items">
                        <div class="color-picor">
                            <input type="color" value="<?php echo ( isset($curbon_look_and_feel_options['colors']['background_color']) && !empty($curbon_look_and_feel_options['colors']['background_color']) ) ? esc_html($curbon_look_and_feel_options['colors']['background_color']) : esc_html($default_look_and_feel_set['colors']['background_color']); ?>" id="curbon-background-color" name="curbon-background-color">
                        </div>
                        <div class="color-code">
                            <code id="colorCode2"></code>
                            <b>Background Color </b>
                        </div>
                    </div>
                    <div class="costomize-adjust-items">
                        <div class="color-picor">
                            <input type="color" value="<?php echo ( isset($curbon_look_and_feel_options['colors']['text_color']) && !empty($curbon_look_and_feel_options['colors']['text_color']) ) ? esc_html($curbon_look_and_feel_options['colors']['text_color']) : esc_html($default_look_and_feel_set['colors']['text_color']); ?>" id="curbon-text-color" name="curbon-text-color">
                        </div>
                        <div class="color-code">
                            <code id="colorCode3"></code>
                            <b>Text Color</b>
                        </div>
                    </div>
                </div>

                <div class="customize-view-bg">
                    <p class="card-title heading">Preview of Default view</p>
                    <div class="customize-view-bg-box curbon-update-border-color  <?php echo esc_attr($auto_debit); ?>">
                        <div class="customize-view-bg-itmes">
                            <div class="costomize-logo-box">
                                <a href="#" class="curbon-primary-color-reflect">
                                    <?php echo wp_kses( $curbon_logo, $allowed_tags ); ?>
                                </a>
                            </div>
                            <div class="costomize-view">
                                <p class="curbon-default-view-text"><?php echo ( isset($caption_key) && false !== $caption_key ) ? esc_attr($captions_infographics_response['captions'][$caption_key]['caption']) : esc_attr($default_look_and_feel_set['caption']); ?></p>
                                <a href="#" class="costomize-btn-primary curbon-reflect-text-color">Learn more&nbsp;
                                    <svg width="11" height="7" viewBox="0 0 11 7" class="curbon-arrow-svg" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M1.10724 1.10998C1.29477 0.922505 1.54908 0.817189 1.81424 0.817189C2.0794 0.817189 2.33371 0.922505 2.52124 1.10998L5.81424 4.40298L9.10724 1.10998C9.19949 1.01447 9.30983 0.938283 9.43184 0.885874C9.55384 0.833465 9.68506 0.805879 9.81784 0.804725C9.95062 0.803571 10.0823 0.828873 10.2052 0.879154C10.3281 0.929435 10.4397 1.00369 10.5336 1.09758C10.6275 1.19147 10.7018 1.30313 10.7521 1.42602C10.8023 1.54892 10.8276 1.6806 10.8265 1.81338C10.8253 1.94616 10.7978 2.07738 10.7453 2.19938C10.6929 2.32138 10.6167 2.43173 10.5212 2.52398L6.52124 6.52398C6.33371 6.71145 6.0794 6.81676 5.81424 6.81676C5.54908 6.81676 5.29477 6.71145 5.10724 6.52398L1.10724 2.52398C0.919769 2.33645 0.814453 2.08214 0.814453 1.81698C0.814453 1.55181 0.919769 1.2975 1.10724 1.10998Z"></path>
                                    </svg>
                                </a>
                            </div>
                            <div class="costomize-btn">
                                <a href="#" class="costomize-btn-secoundary curbon-primary-color-reflect">+R 30 (%)</a>
                                <a href="#" class="costomize-btn-transparent">Opt-Out</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="customize-Expanded-view-bg">
                    <p class="card-title heading">Preview of Expanded view</p>
                    <div class="customize-Expanded-view-inner">
                        <div class="customize-view-bg-box curbon-update-border-color <?php echo esc_attr($auto_debit); ?>">
                            <div class="customize-view-bg-itmes">
                                <div class="costomize-logo-box">
                                    <a href="#" class="curbon-primary-color-reflect">
                                        <?php echo wp_kses( $curbon_logo, $allowed_tags ); ?>
                                    </a>
                                </div>
                                <div class="costomize-view">
                                    <p class="curbon-expanded-view-text"><?php echo ( isset($caption_key) && false !== $caption_key ) ? esc_attr($captions_infographics_response['captions'][$caption_key]['caption']) : esc_attr($default_look_and_feel_set['caption']); ?></p>
                                    <a href="#" class="costomize-btn-primary curbon-reflect-text-color">Learn more
                                        <svg width="11" height="7" viewBox="0 0 11 7" class="curbon-arrow-svg" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.10724 1.10998C1.29477 0.922505 1.54908 0.817189 1.81424 0.817189C2.0794 0.817189 2.33371 0.922505 2.52124 1.10998L5.81424 4.40298L9.10724 1.10998C9.19949 1.01447 9.30983 0.938283 9.43184 0.885874C9.55384 0.833465 9.68506 0.805879 9.81784 0.804725C9.95062 0.803571 10.0823 0.828873 10.2052 0.879154C10.3281 0.929435 10.4397 1.00369 10.5336 1.09758C10.6275 1.19147 10.7018 1.30313 10.7521 1.42602C10.8023 1.54892 10.8276 1.6806 10.8265 1.81338C10.8253 1.94616 10.7978 2.07738 10.7453 2.19938C10.6929 2.32138 10.6167 2.43173 10.5212 2.52398L6.52124 6.52398C6.33371 6.71145 6.0794 6.81676 5.81424 6.81676C5.54908 6.81676 5.29477 6.71145 5.10724 6.52398L1.10724 2.52398C0.919769 2.33645 0.814453 2.08214 0.814453 1.81698C0.814453 1.55181 0.919769 1.2975 1.10724 1.10998Z"></path>
                                        </svg>
                                    </a>
                                </div>
                                <div class="costomize-btn">
                                    <a href="#" class="costomize-btn-secoundary curbon-primary-color-reflect">+R 30 (%)</a>
                                    <a href="#" class="costomize-btn-transparent">Opt-Out</a>
                                </div>
                            </div>

                        </div>
                        <div class="customize-view-bg-box-imges">
                            <p id="curbon-infographcis-text-replace"><?php echo esc_html($captions_infographics_response['infographics'][$infographics_key]['text']); ?></p>
                            <div class="img-box">
                                <img id="curbon-expanded-view-img-replace" src="data:image/png;base64, <?php echo ( isset($infographics_key) && false !== $infographics_key ) ? infographics_base_64_getter($captions_infographics_response['infographics'][$infographics_key]['desktop_image']) : infographics_base_64_getter($default_look_and_feel_set['infographics']); ?>" alt="img">
                            </div>
                            <span class="pwdby_curbon">Powered by</span>
                        </div>
                    </div>
                </div>
                <div class="customize-mini-view-bg">
                    <p class="card-title heading">Preview of Mini-cart view</p>
                    <div class="customize-mini-view-inner">

                        <div class="customize-view-bg-box curbon-update-border-color <?php echo esc_attr($auto_debit); ?>">
                            <div class="customize-view-bg-itmes">
                                <div class="content">
                                    <div class="costomize-logo-box">
                                        <a href="#" class="curbon-primary-color-reflect">
                                            <?php echo wp_kses( $curbon_logo, $allowed_tags ); ?>
                                        </a>
                                    </div>
                                    <div class="costomize-view">
                                        <p class="curbon-mini-cart-view-text"><?php echo ( isset($caption_key) && false !== $caption_key ) ? esc_html($captions_infographics_response['captions'][$caption_key]['caption']) : esc_html($default_look_and_feel_set['caption']); ?></p>
                                    </div>
                                </div>
                                <div class="costomize-btn">
                                    <a href="#" class="costomize-btn-secoundary curbon-primary-color-reflect">+R 30 (%)</a>
                                    <a href="#" class="costomize-btn-transparent">Opt-Out</a>
                                </div>
                            </div>
                        </div>
                        <div class="customize-view-bg-box curbon-update-border-color <?php echo esc_attr($auto_debit); ?>">
                            <div class="customize-view-bg-itmes">
                                <div class="content">
                                    <div class="costomize-logo-box">
                                        <a href="#" class="curbon-primary-color-reflect">
                                            <?php echo wp_kses( $curbon_logo, $allowed_tags ); ?>
                                        </a>
                                    </div>
                                    <div class="costomize-view">
                                        <p class="curbon-mini-cart-view-text-done"><?php echo ( isset($caption_key) && false !== $caption_key ) ? esc_html($captions_infographics_response['captions'][$caption_key]['caption']) : esc_html($default_look_and_feel_set['caption']); ?></p>
                                    </div>
                                </div>
                                <div class="costomize-btn">
                                    <a href="#" class="costomize-btn-transparent btn3">
                                    <svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M18.5821 0.402268C18.9726 0.792792 18.9726 1.42596 18.5821 1.81648L7.58211 12.8165C7.19158 13.207 6.55842 13.207 6.16789 12.8165L1.16789 7.81648C0.777369 7.42596 0.777369 6.79279 1.16789 6.40227C1.55842 6.01174 2.19158 6.01174 2.58211 6.40227L6.875 10.6952L17.1679 0.402268C17.5584 0.0117439 18.1916 0.0117439 18.5821 0.402268Z"/>
                                    </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
</div>

<script>
const input = document.getElementById("curbon-primary-color");
const colorCode = document.getElementById("colorCode");

setColor();
input.addEventListener("input", setColor);

function setColor() {
    jQuery( ".curbon-primary-color-reflect svg path:nth-child(1)" ).attr( "style", "fill:" + input.value );
    // jQuery( ".curbon-update-border-color" ).attr( "style", "border: 1px solid " + input.value );

    jQuery( ".customize-view-bg-box-imges.left-side-box-customize" ).attr( "style", "border-color:" + input.value );
    

    jQuery( ".curbon-regular-logo" ).attr( "style", "background:" + input.value );
    jQuery( ".costomize-btn-secoundary" ).attr( "style", "background:" + input.value );
    jQuery( ".tamplate .costomize-view-template input:checked:before" ).attr( "style", "background-color:" + input.value );
    
    jQuery( ".customize-view-bg-box" ).attr( "style", "border-color:" + input.value );
    jQuery( ".contomize-Default-view-inner .contomize-Default-view-items1 p.card-title.heading" ).attr( "style", "color:" + input.value );
    jQuery( ".customise-items.items-1 p.card-title.heading" ).attr( "style", "color:" + input.value );

    
    jQuery( ".costomize-btn-secoundary:hover" ).attr( "style", "color:" + input.value );

    
    jQuery( ".costomize-btn-transparent" ).attr( "style", "color:" + input.value + ";" );
    jQuery( ".customize-mini-view-bg .customize-mini-view-inner .customize-view-bg-itmes .costomize-btn-transparent.btn3 svg path" ).attr( "style", "fill:" + input.value + ";" );
    
    colorCode.innerHTML = input.value;
}

const input2 = document.getElementById("curbon-background-color");
const colorCode2 = document.getElementById("colorCode2");

setColor2();
input2.addEventListener("input", setColor2);

function setColor2() {
    jQuery( ".curbon-update-border-color" ).attr( "style", "background:" + input2.value );
    jQuery( ".curbon-regular-logo path" ).attr( "style", "fill:" + input2.value );
    jQuery( ".costomize-btn-secoundary:hover" ).attr( "style", "background:" + input2.value );
    jQuery( ".costomize-btn-transparent:hover" ).attr( "style", "color:" + input2.value + "; background: " + input2.value + ";" );
    
    colorCode2.innerHTML = input2.value;
}

const input3 = document.getElementById("curbon-text-color");
const colorCode3 = document.getElementById("colorCode3");

setColor3();
input3.addEventListener("input", setColor3);

function setColor3() {
    jQuery( ".curbon-default-view-text, .curbon-expanded-view-text, .curbon-mini-cart-view-text, .curbon-mini-cart-view-text-done, .curbon-reflect-text-color" ).attr( "style", "color: " + input3.value );
    colorCode3.innerHTML = input3.value;
}


/*Secondary Color Changes */
const input4 = document.getElementById("curbon-secondary-color");
const colorCode4 = document.getElementById("colorCode4");

setColor4();
input4.addEventListener("input", setColor4);

function setColor4() {
    // jQuery( ".curbon-default-view-text, .curbon-expanded-view-text, .curbon-mini-cart-view-text, .curbon-mini-cart-view-text-done, .curbon-reflect-text-color" ).attr( "style", "color: " + input4.value );
    jQuery( ".curbon-update-border-color" ).attr( "style", "border: 1px solid " + input4.value );
    jQuery( ".customize-Expanded-view-bg .customize-Expanded-view-inner" ).attr( "style", "border-color:" + input4.value );
    jQuery( ".customize-Expanded-view-bg .customize-Expanded-view-inner .customize-view-bg-box" ).attr( "style", "border-color:" + input4.value );
    jQuery( ".costomize-btn-primary" ).attr( "style", "border-color:" + input4.value );
    jQuery( ".customize-view-bg-box-imges.left-side-box-customize" ).attr( "style", "border-color:" + input4.value );
    jQuery( ".costomize-selection .select" ).attr( "style", "border-color:" + input4.value );
    jQuery( ".curbon-arrow-svg" ).attr( "style", "fill: " + input4.value + ";" );
    jQuery( ".costomize-selection .select .selectBtn:after" ).attr( "style", "fill: " + input4.value + ";" );
    jQuery( ".costomize-btn-transparent" ).attr( "style", "border-color: " + input4.value + ";" );
    


    
    colorCode4.innerHTML = input4.value;
}


// js for select onption
const select = document.querySelectorAll('.selectBtn');
const option = document.querySelectorAll('.option');
let index = 1;

select.forEach(a => {
    a.addEventListener('click', b => {
        const next = b.target.nextElementSibling;
        next.classList.toggle('toggle');
        next.style.zIndex = index++;
    })
})
option.forEach(a => {
    a.addEventListener('click', b => {
        b.target.parentElement.classList.remove('toggle');

        const parent = b.target.closest('.select').children[0];
        parent.setAttribute('data-type', b.target.getAttribute('data-type'));
        parent.innerText = b.target.innerText;
        jQuery( 'p.curbon-default-view-text, p.curbon-expanded-view-text, p.curbon-mini-cart-view-text, p.curbon-mini-cart-view-text-done' ).text( b.target.innerText );
        jQuery( "#curbon-selected-caption" )
            .val( 
                jQuery( '.selectBtn' ).data( 'type' ) 
            );
    })
})

jQuery('input[type=radio][name=infograpic_text]').on('change', function() {
    var $this_template = jQuery(this).data('template');
    jQuery( '#curbon-expanded-view-img-replace' ).fadeIn( "slow", function(){
        jQuery( this )
            .attr( 
                'src', 
                jQuery( '#infographic-' + $this_template ).attr( 'src' ) 
            );
    } );
    
    jQuery( this )
        .attr( 
            'src', 
            jQuery( '#infographic-' + $this_template ).attr( 'src' ) 
        );

    jQuery( '#curbon-infographcis-text-replace' )
        .text( 
            jQuery( "#infographic-text-" + $this_template ).text() 
        );
});
</script>
<?php // @codingStandardsIgnoreEnd ?>