<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */

// Show Name of the store in products 
/*
Show Store name on the product thumbnail For Dokan Multivendor plugin 
*/

  add_action( 'woocommerce_after_shop_loop_item_title','sold_by' );
    function sold_by(){
    ?>
        </a>
        <?php
            global $product;
            $seller = get_post_field( 'post_author', $product->get_id());
            $author  = get_user_by( 'id', $seller );
            $vendor = dokan()->vendor->get( $seller );

            $store_info = dokan_get_store_info( $author->ID );
            if ( !empty( $store_info['store_name'] ) ) { ?>
                    <span class="details">
                        <?php printf( 'Sold by: <a href="%s">%s</a>', $vendor->get_shop_url(),  $vendor->get_shop_name() ); ?>
                    </span>
            <?php 
        } 

    }




/*Extra field on the seller settings and show the value on the store banner -Dokan*/

// Add extra field in seller settings

add_filter( 'dokan_settings_form_bottom', 'extra_fields', 10, 2);

function extra_fields( $current_user, $profile_info ){
	
$seller_url= isset( $profile_info['seller_url'] ) ? $profile_info['seller_url'] : '';
?>
 <div class="gregcustom dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="setting_address">
            <?php _e( 'Website', 'dokan' ); ?>
        </label>
        <div class="dokan-w5">
            <input type="text" class="dokan-form-control input-md valid" name="seller_url" id="reg_seller_url" value="<?php echo $seller_url; ?>" />
        </div>
    </div>
    <?php
	
	
	// About
	  $booth_news= get_user_meta( $current_user, 'booth_news', true );
?>
     <div class="gregcustom dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="setting_address">
                <?php _e( 'About', 'dokan' ); ?>
            </label>

                <div class="dokan-w8 dokan-text-left">
                        <?php
                            $booth_news_args = array(
                                'editor_height' => 200,
                                'media_buttons' => true,
                                'teeny'         => true,
                                'quicktags'     => false
                            );
                            wp_editor( $booth_news, 'booth_news', $booth_news_args );
                        ?>
            </div>


<?php
}

    //save the field value

add_action( 'dokan_store_profile_saved', 'save_extra_fields', 15 );
function save_extra_fields( $store_id ) {
    $dokan_settings = dokan_get_store_info($store_id);
    if ( isset( $_POST['seller_url'] ) ) {
        $dokan_settings['seller_url'] = $_POST['seller_url'];
    }
 update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );
	
	//About
	if ( ! $store_id ) {
            return;
        }

        if ( ! isset( $_POST['booth_news'] ) ) {
            return;
        }

        update_user_meta( $store_id, 'booth_news', $_POST['booth_news'] );
    
	
}

    // show on the store page

add_action( 'dokan_store_header_info_fields', 'save_seller_url', 10);

function save_seller_url($store_user){

    $store_info    = dokan_get_store_info( $store_user);

   ?>
        <?php if ( isset( $store_info['seller_url'] ) && !empty( $store_info['seller_url'] ) ) { ?>
            <i class="fa fa-globe"></i>
            <a href="<?php echo esc_html( $store_info['seller_url'] ); ?>"><?php echo esc_html( $store_info['seller_url'] ); ?></a>
    
    <?php } ?>
       
  <?php

}


// About on settings
// 
// /* Store notice field seller settings */
/*
add_filter( 'dokan_settings_form_bottom', 'extra_fields', 10, 2);
function extra_fields( $current_user, $profile_info ){
   $booth_news= get_user_meta( $current_user, 'booth_news', true );
?>
     <div class="gregcustom dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="setting_address">
                <?php _e( 'About', 'dokan' ); ?>
            </label>

                <div class="dokan-w8 dokan-text-left">
                        <?php
                            $booth_news_args = array(
                                'editor_height' => 200,
                                'media_buttons' => true,
                                'teeny'         => true,
                                'quicktags'     => false
                            );
                            wp_editor( $booth_news, 'booth_news', $booth_news_args );
                        ?>
            </div>


<?php
}
//Saving the booth news field /
add_action( 'dokan_store_profile_saved','save_extra_fields', 10 );

    function save_extra_fields( $store_id ) {
         if ( ! $store_id ) {
            return;
        }

        if ( ! isset( $_POST['booth_news'] ) ) {
            return;
        }

        update_user_meta( $store_id, 'booth_news', $_POST['booth_news'] );
    
    }

//Showing extra field data on the store page/
*/
add_action( 'dokan_store_profile_frame_after','custom_side_bar',10,2);

function custom_side_bar( $store_user, $store_info ){
    $booth_news = get_user_meta( $store_user->ID, 'booth_news', true );
?>
        <?php if ( !empty( $booth_news ) ) { ?>
            <div class="dokan-store-sidebar">
                   <aside class="widget">
                        <h3 class="widget-title"><?php _e( 'About', 'dokan' ); ?></h3>
                            <?php echo  wpautop( $booth_news ); ?>
                     </aside>
            </div>
        <?php } ?>


        <?php
}

/**
*   Change Proceed To Checkout Text in WooCommerce
*   Add this code in your active theme functions.php file
**/
function woocommerce_button_proceed_to_checkout() {
	
       $new_checkout_url = WC()->cart->get_checkout_url();
       ?>
       <a href="<?php echo $new_checkout_url; ?>" class="checkout-button button alt wc-forward">
	   
	   <?php _e( 'Create Project Space', 'woocommerce' ); ?></a>
	   
<?php
}


// Proceed to checkout into projet space button
// 
// 
  add_filter('woocommerce_get_checkout_url', 'dj_redirect_checkout');

    function dj_redirect_checkout($url) {
         global $woocommerce;
         if(is_cart()){
              $checkout_url = 'http://kickztrade.com/projects/#/projects/active';
         }
         else { 
         //other url or leave it blank.
         }
         return  $checkout_url; 
    }

