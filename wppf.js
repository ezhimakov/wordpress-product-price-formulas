$=jQuery
$( document ).ready(function() {

    $('.wppf-input').on('keyup', function(){
        let input_val = parseInt($(this).val());
        let max_val = parseInt($(this).attr('max'));
        let min_val = parseInt($(this).attr('min'));

        if (input_val > max_val){
            $(this).val(max_val)
        }else if (input_val < min_val){
            $(this).val(min_val)
        }

        let length = $('input[name="wppf_length"]').val();
        let width = $('input[name="wppf_width"]').val();
        let height = $('input[name="wppf_height"]').val();

        let data = {
            action: 'wppf_price',
            length: length,
            width: width,
            height: height,
            id:$('input[name="wppf_length"]').data('product-id')
        };

        jQuery.post( '/wp-admin/admin-ajax.php', data, function(response) {
            console.log(response);
            $('.product-price-int').html(response);
            jQuery( document.body ).trigger( 'post-load' );
        });


    });

});