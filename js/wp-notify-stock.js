jQuery(document).ready(function () {
    init_wp_notify_stock('doco ready');
});


jQuery(window).on('found_variation',
    function (event, variation) {
        // console.log('variations_form', variation.variation_id);
        init_wp_notify_stock('found_variation');
    }
);

function init_wp_notify_stock(evSource) {
    console.log('init_wp_notify_stock running, source, ', evSource);
    jQuery('#wp-notify-me').on('click', function () {
        if (jQuery('#wp-notify-stock').length) {

        } else {
            var pID = jQuery(this).attr('data-product-id');
            show_wp_notify_stock(pID, jQuery(this).parent().parent());
        }
    });
}

function show_wp_notify_stock(productID, domElAppendTo) {

    domElAppendTo.append('<div style="display:none" id="wp-notify-stock"></div>');

    jQuery('#wp-notify-stock').append('<a class="wp-notify-stock-close-small" onclick="wpStockNotifyClose()">X</a>');
    jQuery('#wp-notify-stock').append('<h3>Notify me when in stock</h3>');
    jQuery('#wp-notify-stock').append('<p>Enter your email address and we will let you know when it is back in stock.</p>');
    jQuery('#wp-notify-stock').append('<form>Email:<br><input type="email" name="email"/><br><button type="submit" id="wp-notify-stock-notify-me" >Notify me</button>');
    jQuery('#wp-notify-stock').append('<input type="hidden" id="wp-notify-stock-product-id" value="' + productID + '">');
    jQuery('#wp-notify-stock').append('</form>');
    jQuery('#wp-notify-stock').append('<span>Alternatively you can <a href="#close" onclick="triggerAddToCart()">back order</a> this item by clicking "Add to cart"</span>');

    jQuery('#wp-notify-stock').slideDown('fast');

    jQuery('#wp-notify-stock form').on('submit', function (e) {
        e.preventDefault();
        var data = {
            'action': 'wp_notify_stock_alert',
            'product': jQuery('#wp-notify-stock-product-id').val(),
            'email': jQuery('#wp-notify-stock form input[name=email]').val()
        };
        jQuery.post(ajax_object.ajax_url, data, function (response) {
            response = JSON.parse(response);
            jQuery('#wp-notify-stock').empty();

            jQuery('#wp-notify-stock').append('<strong>Ok - we will let you know when this is in stock</strong>');
            jQuery('#wp-notify-stock').append('<p>We will email:<br>' + response.email + '</p>');

            jQuery('#wp-notify-stock').append("<p>If other customers backorder this item then you could miss out. Back order?</p>");

            jQuery('#wp-notify-stock').append('<button id="wp-notify-stock-close" onclick="wpStockNotifyClose()">Close</button>');
            jQuery('#wp-notify-stock').append('<button id="wp-notify-stock-add-cart" onclick="triggerAddToCart()">Back order</button><div class="wp-notify-stock-clear-floats"></div>');

        });
    });
}

function wpStockNotifyClose() {
    jQuery('#wp-notify-stock').fadeOut('fast');

    setTimeout(function () {
        // tidy up dom incase it gets fired a second time
        jQuery('#wp-notify-stock').remove();
    }, 550);
}

function triggerAddToCart() {
    jQuery('body').find('form.cart button').click();
}