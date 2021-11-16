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

    // how about wp nonces for ajax calls?
    // need to sanitize the user input properly 
	echo json_encode(['email'=>'paul@foob.com']);
	wp_die();
}

// init ? should this be in the activate code?
new_cpt_notify_me();

// warning need to check if this data is/can/could end up in the frontend - how can we be sure?
function new_cpt_notify_me() {
    $cap_type = 'post';
    $plural = 'Back Order Notify';
    $single = 'Notify request';
    $cpt_name = 'wp-notify-stock';
    $opts['can_export'] = TRUE;
    $opts['capability_type'] = $cap_type;
    $opts['description'] = '';

    // when searching the front end site you cannot see these posts
    // but are they properly hidden, e.g if someone registers as a guest?
    // how about in xml site reader feeds ? 
    $opts['exclude_from_search'] = TRUE;
    $opts['has_archive'] = FALSE;
    $opts['hierarchical'] = FALSE;
    $opts['map_meta_cap'] = TRUE;
    $opts['show_in_admin_bar'] = TRUE;
    $opts['show_in_menu'] = TRUE;
    $opts['show_in_nav_menu'] = TRUE;
    $opts['public'] = true;
    $opts['publicly_querable'] = false;

    $opts['menu_icon'] = 'dashicons-businessman';
    $opts['menu_position'] = 25;

    $opts['query_var'] = TRUE;
    $opts['register_meta_box_cb'] = '';
    $opts['rewrite'] = FALSE;

    
    $opts['labels']['add_new'] = esc_html__( "Add New {$single}", 'wisdom' );
    $opts['labels']['add_new_item'] = esc_html__( "Add New {$single}", 'wisdom' );
    $opts['labels']['all_items'] = esc_html__( $plural, 'wisdom' );
    $opts['labels']['edit_item'] = esc_html__( "Edit {$single}" , 'wisdom' );
    $opts['labels']['menu_name'] = esc_html__( $plural, 'wisdom' );
    $opts['labels']['name'] = esc_html__( $plural, 'wisdom' );
    $opts['labels']['name_admin_bar'] = esc_html__( $single, 'wisdom' );
    $opts['labels']['new_item'] = esc_html__( "New {$single}", 'wisdom' );
    $opts['labels']['not_found'] = esc_html__( "No {$plural} Found", 'wisdom' );
    $opts['labels']['not_found_in_trash'] = esc_html__( "No {$plural} Found in Trash", 'wisdom' );
    $opts['labels']['parent_item_colon'] = esc_html__( "Parent {$plural} :", 'wisdom' );
    $opts['labels']['search_items'] = esc_html__( "Search {$plural}", 'wisdom' );
    $opts['labels']['singular_name'] = esc_html__( $single, 'wisdom' );
    $opts['labels']['view_item'] = esc_html__( "View {$single}", 'wisdom' );

    $opts['supports'] = array( 
        'title', 
        'editor', 
        'excerpt', 
        'thumbnail', 
        'custom-fields', 
        'revisions' 
    );

    register_post_type( strtolower( $cpt_name ), $opts );

}


?>