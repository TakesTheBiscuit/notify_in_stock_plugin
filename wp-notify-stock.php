<?php
/*
Plugin Name: WP Notify Stock
Plugin URI:  http://pauldrage.co.uk
Description: This plugin allows woocommerce customers to request to be notified when a product is back in stock. Sends emails.
Version:     2.0.0
Author:      Paul Drage
Author URI:  http://pauldrage.co.uk
License:     GPL2 etc
License URI: https://
 */

function change_backorder_message($text, $product)
{

    if ($product->managing_stock() && $product->is_on_backorder(1)) {
        $text = 'OUT OF STOCK - More is on the way - you can back order this item.<br>';
        $text .= __('<div id="wp-notify-complete" style="display:none;"><em>We will notify you when in stock. You should back order it if you want to secure the item for yourself. Press "Add to basket".</em></div><a href="#notifyme" style="display:none;" id="wp-notify-me" data-product-id="' . $product->id . '" class="single_add_to_cart_button button alt">Notify me when it is in stock</a>', 'wp-notify-stock-domain');
    }

    return $text;
}

add_filter('woocommerce_get_availability_text', 'change_backorder_message', 10, 2);

function wp_notify_stock_scripts()
{
    wp_enqueue_script('wp_notify_stock', plugin_dir_url(__FILE__) . 'js/wp-notify-stock.js', array('jquery'), '1.3.0', false);

    wp_localize_script('wp_notify_stock', 'ajax_object',
        array('ajax_url' => admin_url('admin-ajax.php')));

    wp_enqueue_style('wp_notify_stock_styles', plugin_dir_url(__FILE__) . 'css/wp-notify-stock.css');
}

add_action('wp_enqueue_scripts', 'wp_notify_stock_scripts');

add_action('wp_ajax_wp_notify_stock_alert', 'wp_notify_stock_alert');
add_action( 'wp_ajax_nopriv_wp_notify_stock_alert', 'wp_notify_stock_alert' );


add_action( 'woocommerce_new_product', 'mp_sync_on_product_save', 10, 1 );
add_action( 'woocommerce_update_product', 'mp_sync_on_product_save', 10, 1 );


add_action('init', 'wp_notify_stock_cpt');

add_filter( 'wp_mail_from', 'custom_wp_mail_from' );
function custom_wp_mail_from( $original_email_address ) {
    //Make sure the email is from the same domain 
    //as your website to avoid being marked as spam.
    return 'sales@abikething.com';
}
add_filter( 'wp_mail_from_name', 'custom_wp_mail_from_name' );
function custom_wp_mail_from_name( $original_email_from ) {
    return 'A Bike Thing';
}

function wp_notify_stock_alert()
{
    global $wpdb;
    // called by XHR/Ajax
    // must S A N I T I Z E

    $product_id = sanitize_text_field($_POST['product']);
    $customer_email = sanitize_text_field($_POST['email']);

    $product = wc_get_product( $product_id );
    $sku = $product->get_sku();
    $product_name = $product->get_title();

    $notification_body = "Notify when in stock\n\n" . $product_name ."\nCustomer email: ". $customer_email."\nView product at: /wp-admin/post.php?post=".$product_id."&action=edit"."\nSKU: ".$sku;

    $id = wp_insert_post(array(
        'post_title'=> $customer_email.' - '.$product_name, 
        'post_type'=>'wp-notify-stock', 
        'post_status'=>'private',
        'post_content'=> $notification_body
    ));

    echo json_encode(['email' => $customer_email]);

    if ($id) {
        email_site_owner($notification_body);
    }

    wp_die();
}

function email_site_owner($notification_body) {
    $blog_info = get_bloginfo();
    
    $to_addr = $blog_info->admin_email;
    $subject = $blog_info->name." - Backorder notification";

    wp_mail($to_addr, $subject, $notification_body);

    return true;
}


function mp_sync_on_product_save($product_id)
{

    $product = wc_get_product( $product_id );

    // we need to know if we have > 0 in inventory
    if ($product->get_stock_quantity())
    {
        if ($product->get_stock_quantity() > 0) {
            cycle_all_back_order_notify_requests($product_id, $product);
        }
    }

}

function cycle_all_back_order_notify_requests($pID, $product) {
    // for all of them - if we match the sku drop them an email! :) 

    // the product is an object
    // echo $pID. ' '. $product->get_name(). ' has '.$product->get_stock_quantity();

    get_all_notify_requests($pID, $product->get_name(), $product->get_stock_quantity());


}

function get_all_notify_requests($pID, $product_name, $product_stock_quantity)
{

    $query_params = array(
        'numberposts'      => 5000,
        'post_type'        => 'wp-notify-stock',
        'post_status'=>'private',
        'posts_per_page' => -1
    );

    $posts = get_posts($query_params);

    foreach ($posts as $post) {
        $content = $post->post_content;

        $should_email = should_send_email_check($content, $pID);
        if ($should_email) {
            // send an email now to
            $did_send = email_user_info($should_email, $product_name, $product_stock_quantity);
            if ($did_send) {
                // delete this post so we don't spam the user again in future
                wp_delete_post($post->ID);
            }
        }
    }
    
}

function email_user_info($email_address, $product_name, $product_stock_quantity) {
    $instock_subject = 'Item is back in stock. '.$product_name;
    $notification_body = "Hi there, You asked us to email you when this item came back in stock.";
    $notification_body .= "\nWe currently have ".$product_stock_quantity." in stock ready to go";
    $notification_body .= "\n\nView product here: https://www.abikething.com/?s=". urlencode($product_name)."&post_type=product";
    $notification_body .= "\n\nAny questions please drop us a line, thanks.";

    $did_send = wp_mail($email_address, $instock_subject, $notification_body);

    return $did_send;
}


function getEmailFromBackOrderMessage($message) {
    $pattern = "/email:\s*(.*)$/";
    $lines = explode("\n", $message);
    $target_line = $lines[3];
    
    $did_find = preg_match($pattern, $target_line, $matches); 
    
    if ($did_find && count($matches) > 0) {
        return trim($matches[1]);
    }
    
    // fall through
    return false;
}

function getSKUFromBackOrderMessage($message) {
    $pattern = "/SKU:\s*(.*)$/";
    $lines = explode("\n", $message);
    $target_line = $lines[5];
    
    $did_find = preg_match($pattern, $target_line, $matches); 
    
    if ($did_find && count($matches) > 0) {
        return trim($matches[1]);
    }
    
    // fall through
    return false;
}

function getProductIDFromBackOrderMessage($message) {
    $pattern = "/post=\s*(.*)(?=&)/";
    $lines = explode("\n", $message);
    $target_line = $lines[4];
    
    $did_find = preg_match($pattern, $target_line, $matches); 
    
    if ($did_find && count($matches) > 0) {
        return trim($matches[1]);
    }
    
    // fall through
    return false;
}


function should_send_email_check($str, $pID) {
  
    $emailFound = getEmailFromBackOrderMessage($str);

    if ($emailFound) {
        
        // get sku - not all items have skus
        $skuFound = getSKUFromBackOrderMessage($str);
        $productID = getProductIDFromBackOrderMessage($str);

        if ($productID && is_numeric($productID)) {

            // now we check if the request matches the item that just got edited
            if ($productID == $pID) {
                return $emailFound;
            }            
        }
    }

    // fall through
    return false;


}



function wp_notify_stock_cpt()
{
    $cap_type = 'post';
    $plural = 'Back Order Notify';
    $single = 'Notify request';
    $cpt_name = 'wp-notify-stock';
    $opts['can_export'] = true;
    $opts['capability_type'] = $cap_type;
    $opts['description'] = '';

    $opts['exclude_from_search'] = true;
    $opts['has_archive'] = false;
    $opts['hierarchical'] = false;
    $opts['map_meta_cap'] = true;
    $opts['show_in_admin_bar'] = true;
    $opts['show_in_menu'] = true;

    $opts['show_in_nav_menu'] = true;
    $opts['public'] = true;
    $opts['publicly_querable'] = false;

    $opts['menu_icon'] = 'dashicons-businessman';
    $opts['menu_position'] = 25;

    $opts['query_var'] = true;
    $opts['register_meta_box_cb'] = '';
    $opts['rewrite'] = false;

    $opts['labels']['add_new'] = esc_html__("Add New {$single}", 'wisdom');
    $opts['labels']['add_new_item'] = esc_html__("Add New {$single}", 'wisdom');
    $opts['labels']['all_items'] = esc_html__($plural, 'wisdom');
    $opts['labels']['edit_item'] = esc_html__("Edit {$single}", 'wisdom');
    $opts['labels']['menu_name'] = esc_html__($plural, 'wisdom');
    $opts['labels']['name'] = esc_html__($plural, 'wisdom');
    $opts['labels']['name_admin_bar'] = esc_html__($single, 'wisdom');
    $opts['labels']['new_item'] = esc_html__("New {$single}", 'wisdom');
    $opts['labels']['not_found'] = esc_html__("No {$plural} Found", 'wisdom');
    $opts['labels']['not_found_in_trash'] = esc_html__("No {$plural} Found in Trash", 'wisdom');
    $opts['labels']['parent_item_colon'] = esc_html__("Parent {$plural} :", 'wisdom');
    $opts['labels']['search_items'] = esc_html__("Search {$plural}", 'wisdom');
    $opts['labels']['singular_name'] = esc_html__($single, 'wisdom');
    $opts['labels']['view_item'] = esc_html__("View {$single}", 'wisdom');

    $opts['supports'] = array(
        'title',
        'editor',
        'excerpt',
        'thumbnail',
        'custom-fields',
        'revisions',
    );

    register_post_type(strtolower($cpt_name), $opts);

}

add_action('admin_head', 'woocom_admins_only');
function woocom_admins_only()
{
    
    $screen = get_current_screen();
    // this could be better but does prevent non `manage_woocommerce` users
    // from seing anything related to this CPT in the backend interface
    if ($screen->post_type == "wp-notify-stock") {
        if (!current_user_can('manage_woocommerce')) {
            wp_redirect(home_url(), 301);
            exit;
        }
    }

}



