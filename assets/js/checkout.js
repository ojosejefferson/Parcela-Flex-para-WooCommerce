jQuery(function($) {
    // Função para atualizar o valor do Pix
    function updatePixValue() {
        $.ajax({
            url: parcelasFlexCheckout.ajax_url,
            type: 'POST',
            data: {
                action: 'parcelas_flex_update_pix_value',
                nonce: parcelasFlexCheckout.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.parcelas-flex-cart-info p:first').html('Valor em Pix: ' + response.data.pix_value);
                }
            }
        });
    }

    // Atualiza o valor do Pix junto com as atualizações do WooCommerce
    $(document.body).on('updated_checkout', function() {
        // Aguarda a atualização do WooCommerce terminar
        setTimeout(updatePixValue, 100);
    });

    // Atualiza o valor do Pix quando o método de pagamento é alterado
    $(document.body).on('payment_method_selected', function() {
        // Aguarda a atualização do WooCommerce terminar
        setTimeout(updatePixValue, 100);
    });

    // Atualiza o valor do Pix quando o endereço é alterado
    $(document.body).on('updated_checkout', function() {
        // Aguarda a atualização do WooCommerce terminar
        setTimeout(updatePixValue, 100);
    });

    // Atualiza o valor do Pix quando o frete é alterado
    $(document.body).on('shipping_method_selected', function() {
        // Aguarda a atualização do WooCommerce terminar
        setTimeout(updatePixValue, 100);
    });
}); 