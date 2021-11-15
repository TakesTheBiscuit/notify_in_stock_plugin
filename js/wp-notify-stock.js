jQuery(document).ready(function(){
    jQuery('#wp-notify-me').on('click', function(){
        // spawn a modal
        alert(jQuery(this).attr('data-product-id') + ' Product ');
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