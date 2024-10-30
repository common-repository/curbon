<?php
add_action('init', 'avada_nav_woo_cart', 999999);
if (! function_exists('avada_nav_woo_cart') ) {
    function avada_nav_woo_cart( $position = 'main' )
    {

        if (! class_exists('WooCommerce') ) {
            return '';
        }

        if (! function_exists('Avada') ) {
            return;
        }

        $woo_cart_page_link       = wc_get_cart_url();
        $cart_link_active_class   = '';
        $cart_link_active_text    = '';
        $is_enabled               = false;
        $main_cart_class          = '';
        $cart_link_inactive_class = '';
        $cart_link_inactive_text  = '';
        $items                    = '';
        $cart_contents_count      = WC()->cart->get_cart_contents_count();

        if ('main' === $position ) {
            $is_enabled               = Avada()->settings->get('woocommerce_cart_link_main_nav');
            $main_cart_class          = ' fusion-main-menu-cart';
            $cart_link_active_class   = 'fusion-main-menu-icon fusion-main-menu-icon-active';
            $cart_link_inactive_class = 'fusion-main-menu-icon';

            if (Avada()->settings->get('woocommerce_cart_counter') ) {
                if ($cart_contents_count ) {
                    $cart_link_active_text = '<span class="fusion-widget-cart-number">' . $cart_contents_count . '</span>';
                }
                $main_cart_class      .= ' fusion-widget-cart-counter';
            } elseif ($cart_contents_count ) {
                // If we're here, then ( Avada()->settings->get( 'woocommerce_cart_counter' ) ) is not true.
                $main_cart_class .= ' fusion-active-cart-icons';
            }
        } elseif ('secondary' === $position ) {
            $is_enabled               = Avada()->settings->get('woocommerce_cart_link_top_nav');
            $main_cart_class          = ' fusion-secondary-menu-cart';
            $cart_link_active_class   = 'fusion-secondary-menu-icon';
            /* translators: Number of items. */
            $cart_link_active_text    = sprintf(esc_html__('%s Item(s)', 'Avada'), $cart_contents_count) . ' <span class="fusion-woo-cart-separator">-</span> ' . WC()->cart->get_cart_subtotal();
            $cart_link_inactive_class = $cart_link_active_class;
            $cart_link_inactive_text  = esc_html__('Cart', 'Avada');
        }

        $highlight_class = '';
        if ('bar' === Avada()->settings->get('menu_highlight_style') ) {
            $highlight_class = ' fusion-bar-highlight';
        }
        $cart_link_markup = '<a class="' . $cart_link_active_class . $highlight_class . '" href="' . $woo_cart_page_link . '"><span class="menu-text" aria-label="' . esc_html__('View Cart', 'Avada') . '">' . $cart_link_active_text . '</span></a>';

        if ($is_enabled ) {
            if (is_cart() ) {
                $main_cart_class .= ' current-menu-item current_page_item';
            }

            $items = '<li role="menuitem" class="fusion-custom-menu-item fusion-menu-cart' . $main_cart_class . '">';
            if ($cart_contents_count ) {
                $checkout_link = wc_get_checkout_url();

                $items .= $cart_link_markup;
                $items .= '<div class="fusion-custom-menu-item-contents fusion-menu-cart-items">';


                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                    $_product     = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                    $product_link = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                    $thumbnail_id = ( $cart_item['variation_id'] && has_post_thumbnail($cart_item['variation_id']) ) ? $cart_item['variation_id'] : $cart_item['product_id'];

                    if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key) ) {
                        $items .= '<div class="fusion-menu-cart-item">';
                        $items .= '<a href="' . $product_link . '">';
                        $items .= get_the_post_thumbnail($thumbnail_id, 'recent-works-thumbnail');
                        // Check needed for pre Woo 2.7 versions only.
                        $item_name = method_exists($_product, 'get_name') ? $_product->get_name() : $cart_item['data']->post->post_title;
                        $items .= '<div class="fusion-menu-cart-item-details">';
                        $items .= '<span class="fusion-menu-cart-item-title">' . $item_name . '</span>';
                        $product_price = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
                        if ('' !== $product_price ) {
                            $product_price = ' x ' . $product_price;
                        }
                        $items .= '<span class="fusion-menu-cart-item-quantity">' . $cart_item['quantity'] . $product_price . '</span>';
                        $items .= '</div>';
                        $items .= '</a>';
                        $items .= '</div>';
                    }
                }
                
                /**********************************************************/
                /**********************************************************/
                /*********
* CUSTOM CODE FOR AVADA MINI CART START HERE
*******/
                /**********************************************************/
                /**********************************************************/
                $curbon_onboarding_status  = get_option('curbon-onboarding-status');
                $product_id             = $curbon_onboarding_status['carbon_offset_product_id'];
    
                if($product_id) {

                    $product_cart_id = WC()->cart->generate_cart_id($product_id);


                    $items .= '<form method="post">';
                        $items .= '<div id="curbon-offset-mini-cart-widget" class="is-enabled Avada">';
                            $items .= '<div class="curbon-cart-wrapper-main">';
                                $items .= '<div class="curbon-content">';
                                    $items .= '<div class="curbon-content-title">';
                                         $items .= 'Reduce the carbon footprint of your purchase <img src="'.CURBON_PLUGIN_URL.'/assets/images/look-and-feel/curbon-logo-'.$curbon_logo.'-picker.svg" alt="curbon" class="curbon-logo-inline">';
                                    $items .= '</div>';

                                    
                    if(WC()->cart->find_product_in_cart($product_cart_id) ) {
                                           
                        $items .= '<div class="curbon-carbo-offset-button curbon-thankyou-button">';
                            $items .= '<button type="submit" name="curbon_remove_carbon_offset_button" value="curbon_thank_you">Thank you ';
                            $items .= '<span class="cc-add-button-tick">';
                            $items .= '<svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                  <path d="M7.2427 11.3849C6.5173 12.1103 5.3402 12.1103 4.61514 11.3849L0.544073 7.31378C-0.181358 6.58868 -0.181358 5.41168 0.544073 4.68658C1.26915 3.96118 2.44621 3.96118 3.17164 4.68658L5.5972 7.11177C5.7803 7.29457 6.0775 7.29457 6.261 7.11177L12.8287 0.544065C13.5538 -0.181355 14.7309 -0.181355 15.4563 0.544065C15.8046 0.892425 16.0004 1.36508 16.0004 1.85768C16.0004 2.35029 15.8046 2.82293 15.4563 3.17128L7.2427 11.3849Z" fill="#2AA43C"></path>
                                                </svg>';
                            $items .= '</span>';
                            $items .= '</button>';
                            ob_start();
                            wp_nonce_field('curbon_remove_carbon_offset_button_nonce', 'curbon_remove_carbon_offset_button_nonce_field');
                            $items .= ob_get_clean();
                        $items .= '</div>';
                                            
                    }else{

                        $pprice = 0;
                                            
                        $curbon_onboarding_status  = get_option('curbon-onboarding-status');
                        $product_id             = $curbon_onboarding_status['carbon_offset_product_id'];
                        $_product               = wc_get_product($product_id);
                                            
                        if($_product) {
                            $pprice             = get_woocommerce_currency_symbol().$_product->get_price();
                                                
                            $items .= '    <div class="curbon-carbo-offset-button">';
                                    $items .= '<button type="submit" name="curbon_add_carbon_offset_button" value="curbon_add_carbon_offset"><span class="curbon-offset-plus">+</span> '.$pprice.' </button>';
                                                        
                                    ob_start();
                                wp_nonce_field('curbon_add_carbon_offset_button_nonce', 'curbon_add_carbon_offset_button_nonce_field');
                                $items .= ob_get_clean();

                                $items .= '</div>';
                                                
                        }
                    }
                                    
                                $items .= '</div>';
                            $items .= '</div>';
                        $items .= '</div>';
                    $items .= '</form>';

                }

                /**********************************************************/
                /**********************************************************/
                /*********
* CUSTOM CODE FOR AVADA MINI CART END HERE
*********/
                /**********************************************************/
                /**********************************************************/

                $items .= '<div class="fusion-menu-cart-checkout">';
                $items .= '<div class="fusion-menu-cart-link"><a href="' . $woo_cart_page_link . '">' . esc_html__('View Cart', 'Avada') . '</a></div>';
                $items .= '<div class="fusion-menu-cart-checkout-link"><a href="' . $checkout_link . '">' . esc_html__('Checkout', 'Avada') . '</a></div>';
                $items .= '</div>';
                $items .= '</div>';
            } else {
                $items .= '<a class="' . $cart_link_inactive_class . $highlight_class . '" href="' . $woo_cart_page_link . '"><span class="menu-text" aria-label="' . esc_html__('View Cart', 'Avada') . '">' . $cart_link_inactive_text . '</span></a>';
            }
            $items .= '</li>';
        }
        return $items;
    }
}
?>