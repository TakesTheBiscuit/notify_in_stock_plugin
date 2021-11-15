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
}

add_action('wp_enqueue_scripts', 'wp_notify_stock_scripts');


add_action( 'wp_ajax_my_action', 'my_action' );
function my_action() {
	global $wpdb;
	echo 10;
	wp_die();
}

// function ajaxloadpost_ajaxhandler() {

//     if ( !wp_verify_nonce( $_POST['nonce'], "ajaxloadpost_nonce")) {
    
//         exit("Wrong nonce");
    
//     }

//     $results ='';

//     $content_post = get_post($_POST['postid']);

//     $results = $content_post->post_content;

//     die($results);

// }
    
// add_action( 'wp_ajax_nopriv_ajaxloadpost_ajaxhandler', 'ajaxloadpost_ajaxhandler');

// add_action( 'wp_ajax_ajaxloadpost_ajaxhandler', 'ajaxloadpost_ajaxhandler' );


// add_action( 'wp_ajax_my_action', 'my_action' );
// add_action( 'wp_ajax_nopriv_my_action', 'my_action' );

?>