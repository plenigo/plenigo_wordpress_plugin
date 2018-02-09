jQuery(document).ready(function () {
    jQuery('#plenigo-quantity').on('change', function (e) {
        var quantity = jQuery(this).val();
        if (!isNaN(quantity) && quantity > 0) {
            var submitBtn = jQuery("#submit");
            submitBtn.prop('disabled', true);
            var productData = submitBtn.data('product-data');
            var verificationHash = submitBtn.data('verification-hash');
            var data = {product_data: productData, verification_hash: verificationHash, quantity: quantity};

            jQuery.ajax({
                // /wp-admin/admin-ajax.php
                url: ajax_object.ajaxurl + "?" + jQuery.param(data),
                // Add action and nonce to our collected data
                data: {action: '_ajax_fetch_checkout_snippet'},
                success: function (response) {
                    var responseObject = JSON.parse(response);
                    if(responseObject.success) {
                        jQuery('#submit').replaceWith(responseObject.message);
                    } else {
                        alert(responseObject.message);
                        submitBtn.prop('disabled', false);
                    }
                }
            });
        }
    });
});