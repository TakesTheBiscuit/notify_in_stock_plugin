jQuery(document).ready(function(){
    jQuery('#wp-notify-me').on('click', function(){
        // spawn a modal
        alert(jQuery(this).attr('data-product-id') + ' Product ');

        var data = {
            'action': 'my_action',
            'whatever': ajax_object.we_value      // We pass php values differently!
        };
        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post(ajax_object.ajax_url, data, function(response) {
            alert('Got this from the server: ' + response);
        });

        
    });

    jQuery('#wp-notify-form').on('submit', function(){
        // save or submit email and product ID to backend
        
    });

    console.log(ajax_object);

});




// function ajaxloadpost_loadpost(postid,nonce) {

//     jQuery.ajax({
    
//     type: ‘POST’,
    
//     url: ajaxloadpostajax.ajaxurl,
    
//     data: {
    
//     action: ‘ajaxloadpost_ajaxhandler’,
    
//     postid: postid,
    
//     nonce: nonce
    
//     },
    
//     success: function(data, textStatus, XMLHttpRequest) {
    
//     var loadpostresult = ‘#loadpostresult’;
    
//     jQuery(loadpostresult).html(”);
    
//     jQuery(loadpostresult).append(data);
    
//     },
    
//     error: function(MLHttpRequest, textStatus, errorThrown) {
    
//     alert(errorThrown);
    
//     }
    
//     });
    
//     }