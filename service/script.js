(function() {

    /***********************************************************/
    /* Handle Proceed to Payment
    /***********************************************************/
    jQuery(function() {

        // save all payment methods
        var payment_methods = [];
        $('#js__payment__method option').each(function (i, element) {
            payment_methods.push({'value': $(element).attr('value'), 'text': $(element).text()});
        });

        updateOptions();

        $('#js__shipping__method').change(function () {
            updateOptions();
        });
        
        function updateOptions() {
            $('#js__payment__method option').remove();
            var has_mapping = false;
            var allowed_payment_methods = [];
            for(var i = 0; i < shipping_payment_mapping.length; i++){
                if($('#js__shipping__method').val() == shipping_payment_mapping[i]['shipping']){
                    has_mapping = true;
                    allowed_payment_methods.push(shipping_payment_mapping[i]['payment']);
                }
            }
            for(var i = 0; i < payment_methods.length; i++){
                if(has_mapping && allowed_payment_methods.indexOf(payment_methods[i]['value']) == -1){
                    continue;
                }
                $("#js__payment__method").append("<option value='" + payment_methods[i]['value'] + "'>" + payment_methods[i]['text'] + "</option>");
            }
        }

    });

})();
