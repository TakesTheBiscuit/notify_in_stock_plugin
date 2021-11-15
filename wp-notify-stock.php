<?php
/*
Plugin Name: WP Notify Stock
Plugin URI:  http://pauldrage.co.uk
Description: This plugin allows woocommerce customers to request to be notified when a product is back in stock
Version:     1.0
Author:      Paul Drage
Author URI:  http://pauldrage.co.uk
License:     GPL2 etc
License URI: https://
*/


function change_backorder_message( $text, $product ){

    if ( $product->managing_stock() && $product->is_on_backorder( 1 ) ) {
        $text = __( '<a href="#notifyme" id="wp-notify-me" data-product-id="' .$product->id. '" class="single_add_to_cart_button button alt">Notify me when it is in stock</a>', 'your-textdomain' );
    }

    return $text;
}


add_filter( 'woocommerce_get_availability_text', 'change_backorder_message', 10, 2 );

function wp_notify_stock_scripts()
{   
    wp_enqueue_script( 'wp_notify_stock', plugin_dir_url( __FILE__ ) . 'js/wp-notify-stock.js', array('jquery'), '1.0.0', false );

    wp_localize_script( 'wp_notify_stock', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );

    wp_enqueue_style( 'wp_notify_stock_styles', plugin_dir_url( __FILE__ ) . 'css/wp-notify-stock.css');
}

add_action('wp_enqueue_scripts', 'wp_notify_stock_scripts');


add_action( 'wp_ajax_my_action', 'my_action' );
function my_action() {
	global $wpdb;

    // need to sanitize the user input properly 
	echo json_encode(['email'=>'paul@foob.com']);
	wp_die();
}

//     if ( !wp_verify_nonce( $_POST['nonce'], "ajaxloadpost_nonce")) {
//         exit("Wrong nonce");
//     }


?>