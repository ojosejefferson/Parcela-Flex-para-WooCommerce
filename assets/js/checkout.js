jQuery(function($) {
    // Função para atualizar o valor do Pix
    function updatePixValue() {
        var data = {
            security: wc_checkout_params.update_order_review_nonce,
            post_data: $( 'form.checkout' ).serialize()
        };

        $.ajax({
            url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'update_order_review'),
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    $('.parcelas-flex-cart-info p:first').html('Valor em Pix: ' + response.data.pix_value);
                }
            }
        });
    }

    // Atualiza o valor do Pix junto com as atualizações do WooCommerce
    $(document.body).on('updated_checkout', function() {
        updatePixValue();
    });

    // Atualiza o valor do Pix quando o método de pagamento é alterado
    $(document.body).on('payment_method_selected', function() {
        updatePixValue();
    });

    // Atualiza o valor do Pix quando o endereço é alterado
    $(document.body).on('updated_checkout', function() {
        updatePixValue();
    });

    // Atualiza o valor do Pix quando o frete é alterado
    $(document.body).on('shipping_method_selected', function() {
        updatePixValue();
    });
}); 