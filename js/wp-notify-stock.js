jQuery(document).ready(function(){
    jQuery('#wp-notify-me').on('click', function(){
        // spawn a modal
        alert(jQuery(this).attr('data-product-id') + ' Product ');

        jQuery('body').append('<div id="wp-notify-stock"></div>');

        jQuery('#wp-notify-stock').append('<h3>Notify me when in stock</h3><p>Enter your email address and we will let you know when it is back in stock.</p><form>Email:<br><input type="email" name="email"/><br><button type="submit">Notify me</button></form>');

        notifyStockFormHandler();
    });
});

function notifyStockFormHandler() {
    jQuery('#wp-notify-stock form').on('submit', function(e){
        e.preventDefault();

        // save or submit email and product ID to backend

        var data = {
            'action': 'my_action',
            'whatever': ajax_object.we_value      // We pass php values differently!
        };
        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post(ajax_object.ajax_url, data, function(response) {
            console.log('Got this from the server: ' + response);
        });
        
    });
}