jQuery(document).ready(function(){
    jQuery('#wp-notify-me').on('click', function(){
        // spawn a modal into the page
        if (jQuery('#wp-notify-stock').length) {

        } else {
            jQuery('body').append('<div style="display:none" id="wp-notify-stock"></div>');

            jQuery('#wp-notify-stock').append('<a class="wp-notify-stock-close-small" onclick="wpStockNotifyClose()">X</a>');
            jQuery('#wp-notify-stock').append('<h3>Notify me when in stock</h3>');
            jQuery('#wp-notify-stock').append('<p>Enter your email address and we will let you know when it is back in stock.</p>');
            jQuery('#wp-notify-stock').append('<form>Email:<br><input type="email" name="email"/><br><button type="submit">Notify me</button>');
            jQuery('#wp-notify-stock').append('<input type="hidden" id="wp-notify-stock-product-id" value="' + jQuery(this).attr('data-product-id') + '">');
            jQuery('#wp-notify-stock').append('</form>');
            jQuery('#wp-notify-stock').append('<span>Alternatively you can <a href="#close" onclick="wpStockNotifyClose()">back order</a> this item by clicking "Add to cart"</span>');

            jQuery('#wp-notify-stock').slideDown('fast');
            // register the handler for form submission
            notifyStockFormHandler();
        }
        
    });
});

function wpStockNotifyClose() {
    
    jQuery('#wp-notify-stock').fadeOut('fast');
    setTimeout(function(){
        jQuery('#wp-notify-stock').remove();
    }, 550);

}

function notifyStockFormHandler() {
    jQuery('#wp-notify-stock form').on('submit', function(e){
        e.preventDefault();

        // save or submit email and product ID to backend
        var data = {
            'action': 'wp_notify_stock_alert',
            'product': jQuery('#wp-notify-stock-product-id').val(),
            'email': jQuery('#wp-notify-stock form input[name=email]').val()
        };
        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post(ajax_object.ajax_url, data, function(response) {
            console.log('Got this from the server: ' + response);
            response = JSON.parse(response);
            jQuery('#wp-notify-stock').empty();

            jQuery('#wp-notify-stock').append('<h3>Ok - we will let you know when this is in stock</h3>');
            jQuery('#wp-notify-stock').append('<p>We will email: '+response.email+'</p>');

            jQuery('#wp-notify-stock').append('<button id="wp-notify-stock-close" onclick="wpStockNotifyClose()">Close</button>');

        });
        
    });
}