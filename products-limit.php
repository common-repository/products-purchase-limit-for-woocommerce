<?php
/**
 * Plugin Name: Products Purchase Limit for WooCommerce
 * Description: This plugin allows you to set up minimum or maximum products purchase limits for your WooCommerce shop.
 * Author: Yogesh C. Pant
 * Version: 1.0.0
 */
/**
* Description of Products Purchase Limit for WooCommerce
 *
 * @package Products Purchase Limit for WooCommerce
 * @version 1.0.0
 * @author Yogesh C. Pant
 */
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}
/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    /**
      * Create the Setting tab
    **/
	add_filter( 'woocommerce_get_sections_products', 'cartlimit_add_section' );
	function cartlimit_add_section( $sections ) {	
		$sections['products_purchase_limit'] = __( 'Products Purchase Limit', 'text-domain' );
		return $sections;
		
	}
    /**
      * Create the admin panel for product limit
    **/
	add_filter( 'woocommerce_get_settings_products', 'product_limit_settings', 10, 2 );
	function product_limit_settings( $settings, $current_section ) {
		/**
		 * Check the current section is what we want
		 **/
		if ( $current_section == 'products_purchase_limit' ) {
			$settings_purchase = array();
			// Add Title to the Settings
			$settings_purchase[] = array( 'name' => __( 'Products Purchase Limit Settings', 'text-domain' ), 'type' => 'title', 'desc' => __( 'The following options are used to configure Products Purchase Limit', 'text-domain' ), 'id' => 'products_purchase_limit' );
			// Add first checkbox option
			$settings_purchase[] = array(
				'name'     => __( 'Enable Products Limit', 'text-domain' ),
				'id'       => 'product_limit_check',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable Products Limit', 'text-domain' ),
			);
			// Add minimum product field
			$settings_purchase[] = array(
				'name'     => __( 'Minimum Products', 'text-domain' ),
				'desc_tip' => __( 'Minimum required products for checkout', 'text-domain' ),
				'id'       => 'cartlimit_min',
				'type'     => 'number',
				'custom_attributes' => array(
						'min'  => 1,
						'step' => 1
					),
				'desc'     => __( 'Enter the minimum required products for checkout', 'text-domain' ),
			);
			// Add maximum product field
			$settings_purchase[] = array(
				'name'     => __( 'Maximum Products', 'text-domain' ),
				'desc_tip' => __( 'Maximum required products for checkout', 'text-domain' ),
				'id'       => 'cartlimit_max',
				'type'     => 'number',
				'custom_attributes' => array(
						'min'  => 1,
						'step' => 1
					),
				'desc'     => __( 'Enter the maximum required products for checkout', 'text-domain' ),
			);

			$settings_purchase[] = array( 'type' => 'sectionend', 'id' => 'products_purchase_limit' );
			return $settings_purchase;
			/**
			 * If not, return the standard settings
			 **/
		} else {
			return $settings;
		}
	}
	/**
	 * Check the cart items and set the conditions
	**/
    add_action( 'woocommerce_check_cart_items', 'set_min_max_total');
	function set_min_max_total() {
		// Only run in the Cart or Checkout pages
		if( is_cart() || is_checkout() ) {
			global $woocommerce;
			// get minimum products total
			$minimum_products = get_option('cartlimit_min');
			// get maximum products total
			$maximum_products = get_option('cartlimit_max');
	        // get total products from cart
			$total = $woocommerce->cart->cart_contents_count;
			$msg = "Current Products on Cart: $total";
			$enable = get_option('product_limit_check');
	        // Check the product limit options
            if( $enable == "yes" ) {
            	    $message = '';
					switch (true) {
		               case ( !empty($minimum_products) && !empty($maximum_products) && ($total < $minimum_products ||  $total > $maximum_products) ) :
		               $message = "You must purchase a minimum of $minimum_products products and maximum of $maximum_products products. <br/> $msg";
		               break;

		               case ( !empty($minimum_products) && $total < $minimum_products ) :
		               $message = "You must purchase a minimum of $minimum_products products. <br/> $msg";
		               break;

		               case ( !empty($maximum_products) && $total > $maximum_products ) :
		               $message = "You must purchase a maximum of $maximum_products products. <br/> $msg";
		               break;

		               default:
                       return true;
					}
					wc_add_notice($message,'error');
			}
		}
	}
}