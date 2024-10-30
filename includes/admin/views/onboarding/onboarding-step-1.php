<?php 
/**
 * HTML of Onboarding Step - 1
 * View is defined in thie file
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
    // @codingStandardsIgnoreStart
?>

<div class="curbon-onboading step1 <?php echo esc_attr($filter_blur); ?>" id="step1">
    <div class="curbon-onboading-left">
        <div class="curbon-onboading-bg">
            <div class="curbon-step-bg">
                <div class="curbon-step-wrap-title">
                    <p class="curbon-step-h2-t">Thank you for installing Curbon for WooCommerce</p>
                    <p>You are about to join the community of forward-thinking businesses taking action against climate
                        change and we'd like to thank you for starting that journey towards carbon neutrality with us!
                    </p>
                </div>
                <svg width="461" height="432" viewBox="0 0 461 432" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M53.3256 311.904C51.9069 296.309 53.2188 280.92 59.065 266.278C70.3798 238.017 90.6183 219.135 119.476 209.526C122.625 208.514 124.216 207.543 123.414 203.784C115.885 167.735 131.249 130.289 161.82 109.985C196.56 86.9246 240.697 89.4554 272.544 116.534C274.928 118.585 276.577 118.89 279.391 116.951C320.477 88.5329 377.274 105.183 396.961 151.33C397.384 152.235 398.065 153.071 398.119 153.98C398.651 162.332 403.966 163.243 411.054 163.874C440.233 166.733 464.431 187.883 471.955 216.181C483.9 261.316 461.103 307.36 417.901 325.258C413.57 327.042 408.975 328.525 404.399 329.727C366.517 339.767 328.526 349.744 290.599 359.612C284.987 361.086 284.455 361.872 281.564 355.432C274.138 339.107 255.305 330.3 237.923 334.403C218.986 338.915 206.46 354.48 206.66 373.164C206.737 381.543 206.76 381.629 198.707 383.651C184.502 387.197 170.591 391.866 155.95 393.403C115.986 397.622 76.3442 374.251 60.5964 337.13C60.1743 336.225 59.6885 335.43 59.2664 334.526C57.2937 327.014 55.2983 319.416 53.3256 311.904ZM350.283 267.054C351.525 264.051 353.579 263.789 355.02 262.949C374.964 252.082 383.962 231.167 377.857 209.326C373.789 194.889 360.735 184.195 345.542 182.924C330.153 181.612 315.399 190.101 308.759 203.475C302.987 215.236 304.921 226.819 308.718 238.466C309.303 240.343 310.671 240.63 312.311 240.199C318.01 238.703 323.622 237.229 329.321 235.732C331.479 235.166 332.289 234.03 331.699 231.785C330.865 228.959 330.116 226.109 329.563 223.301C328 215.589 333.02 207.995 340.24 207.022C348.002 205.999 354.846 211.677 355.546 219.616C356.337 229.653 351.97 237.63 342.799 242.069C335.845 245.464 330.249 250.164 326.061 256.709C317.876 269.473 319.419 282.728 324.037 296.098C324.799 298.298 326.399 298.062 328.126 297.609C349.712 291.94 371.297 286.271 392.883 280.603C395.473 279.923 396.215 278.528 395.471 276.047C393.956 270.63 392.55 265.277 391.208 259.814C390.569 257.029 389.092 256.678 386.588 257.336C381.776 258.692 376.832 259.898 371.996 261.168C365.048 263.177 358.054 265.014 350.283 267.054ZM297.548 249.706C295.057 242.331 294.098 234.46 290.83 227.289C283.423 210.682 265.239 201.89 247.453 206.561C229.666 211.232 217.435 227.919 219.897 245.732C222.03 261.232 226.02 276.428 231.779 290.976C238.54 307.936 257.088 316.356 274.984 311.749C292.857 307.055 304.506 290.613 302.734 272.619C301.948 264.703 299.003 257.354 297.548 249.706ZM140.077 290.783C142.05 298.295 143.14 306.315 146.19 313.36C154.473 332.598 176.306 340.524 195.803 332.174C209.978 326.051 219.624 308.658 217.219 293.876C216.703 290.504 215.454 289.263 211.873 290.388C206.933 291.963 201.903 293.191 196.936 294.311C194.323 294.905 193.341 296.086 193.135 298.817C192.504 305.904 187.54 310.9 181.287 311.342C174.862 311.83 169.185 307.783 167.33 301.071C164.391 290.582 161.711 280.026 159.031 269.469C157.212 262.194 159.996 255.925 166.241 252.993C171.818 250.328 178.25 252.331 182.609 258.385C183.931 260.253 185.303 260.908 187.525 260.233C192.251 258.899 196.936 257.761 201.685 256.514C207.211 255.063 207.698 254.104 205.278 249.109C197.73 233.371 179.959 224.747 163.486 228.796C145.35 233.189 133.706 248.246 134.378 266.621C134.519 274.891 138.141 282.708 140.077 290.783Z"
                        fill="#A09FFA" />
                    <path
                        d="M19.2709 182.217C19.7978 173.68 22.5461 166.221 29.3646 160.553C41.0925 150.736 58.3035 152.308 68.1254 164.404C77.5297 175.963 75.6168 193.633 63.9255 202.887C51.9342 212.405 34.2828 210.21 25.1969 198.105C24.2297 196.883 23.3489 195.637 22.468 194.392C21.4023 190.334 20.3366 186.276 19.2709 182.217Z"
                        fill="#A09FFA" />
                    <path
                        d="M344.915 47.691C341.128 33.2717 350.042 18.0092 364.57 14.2862C378.84 10.6312 393.993 19.4811 397.821 33.7051C401.671 48.0153 392.739 63.5595 378.32 67.3462C363.987 71.1102 348.724 62.1966 344.915 47.691Z"
                        fill="#A09FFA" />
                    <path
                        d="M260.876 369.082C263.075 377.457 258.323 385.72 249.925 387.833C241.873 389.855 233.478 384.953 231.283 376.946C229.197 369.002 234.054 360.435 242.061 358.24C250.414 355.954 258.677 360.707 260.876 369.082Z"
                        fill="#A09FFA" />
                    <path
                        d="M248.157 262.31C246.851 256.284 244.705 250.572 243.917 244.41C243.098 237.426 247.118 231.294 253.53 229.702C259.77 228.156 266.497 231.281 268.739 237.707C272.863 249.546 276.146 261.697 278.285 274.057C279.513 280.842 275.316 286.651 268.949 288.416C262.41 290.225 256.114 286.987 253.373 280.415C251.008 274.575 250.025 268.372 248.157 262.31Z"
                        fill="#A09FFA" />
                </svg>
            </div>
        </div>



    </div>
    <div class="curbon-onboading-right">
        <div class="curbon-onboading-bg">
            <div class="curbon-onboading-right-image">
                <p class="curbon-title-c">How does Curbon work?</p>
                <div class="onboarding-step1-image-wrapper">
                    <div class="onboarding-step1-image-block">
                        <img src="<?php echo CURBON_PLUGIN_URL ?>/assets/images/onboarding/onboarding_1.svg" />
                        <p>Customer clicks the Purple button adding a Contribution to their cart.</p>
                    </div>
                    <div class="onboarding-step1-image-block">
                        <img src="<?php echo CURBON_PLUGIN_URL ?>/assets/images/onboarding/onboarding_2.svg" />
                        <p>Customer completes their purchase as normal.</p>
                    </div>
                    <div class="onboarding-step1-image-block">
                        <img src="<?php echo CURBON_PLUGIN_URL ?>/assets/images/onboarding/onboarding_3.svg" />
                        <p>Customer receives a confirmation and can see which projects they are supporting.</p>
                    </div>
                    <div class="onboarding-step1-image-block">
                        <img src="<?php echo CURBON_PLUGIN_URL ?>/assets/images/onboarding/onboarding_4.svg" />
                        <p>We collect payment from you via credit card, once your carbon credit balance gets close to
                            zero.</p>
                    </div>
                </div>

            </div>

            <div class="curbon-step-btn">
                <button name="onboarding_next" class="button-primary" type="submit" value="Move to Next Step">Move to
                    Next Step</button>
                <input type="hidden" name="onboarding_previous_step" value="<?php echo esc_html($active_step) - 1 ; ?>">
                <input type="hidden" name="onboarding_current_step" value="<?php echo esc_html($active_step); ?>">
                <input type="hidden" name="onboarding_next_step" value="<?php echo esc_html($active_step) + 1 ; ?>">
                <?php wp_nonce_field('curbon_onboarding_next_nonce', 'curbon_onboarding_next_nonce_field'); ?>
            </div>
        </div>
    </div>
</div>
<?php // @codingStandardsIgnoreEnd ?>