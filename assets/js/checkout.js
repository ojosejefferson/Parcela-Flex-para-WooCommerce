jQuery(function($) {
    // Cache dos elementos DOM
    var $pixElement = $('.parcelas-flex-cart-info p:first');
    var $cartTable = $('.woocommerce-checkout-review-order-table');
    var lastValidTotal = 0;
    var updateAttempts = 0;
    var maxUpdateAttempts = 3;

    // Função para salvar o estado do carrinho
    function salvarEstadoCarrinho(total) {
        if (total > 0) {
            localStorage.setItem('last_valid_total', total);
            lastValidTotal = total;
        }
    }

    // Função para restaurar o estado do carrinho
    function restaurarEstadoCarrinho() {
        var savedTotal = localStorage.getItem('last_valid_total');
        if (savedTotal && savedTotal > 0) {
            lastValidTotal = parseFloat(savedTotal);
            return true;
        }
        return false;
    }

    // Função para atualizar o valor do Pix
    function updatePixValue() {
        if (!$pixElement.length) {
            console.log('Elemento do Pix não encontrado');
            return;
        }

        var data = {
            security: wc_checkout_params.update_order_review_nonce,
            post_data: $('form.checkout').serialize()
        };

        $.ajax({
            url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'update_order_review'),
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success && response.data) {
                    var cartTotal = parseFloat(response.data.cart_total) || 0;
                    var shippingTotal = parseFloat(response.data.shipping_total) || 0;
                    
                    // Se o total do carrinho for 0, tenta restaurar o último valor válido
                    if (cartTotal <= 0 && updateAttempts < maxUpdateAttempts) {
                        updateAttempts++;
                        console.log('Tentativa de atualização ' + updateAttempts);
                        
                        if (restaurarEstadoCarrinho()) {
                            // Força atualização do checkout
                            $.ajax({
                                url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
                                type: 'POST',
                                success: function(data) {
                                    if (data && data.fragments) {
                                        $.each(data.fragments, function(key, value) {
                                            $(key).replaceWith(value);
                                        });
                                        updatePixValue(); // Tenta atualizar novamente
                                    }
                                }
                            });
                            return;
                        }
                    }

                    // Se chegou aqui, reseta as tentativas
                    updateAttempts = 0;

                    // Calcula o valor total com frete
                    var totalComFrete = cartTotal + shippingTotal;
                    
                    // Se o total for válido, salva o estado
                    if (totalComFrete > 0) {
                        salvarEstadoCarrinho(totalComFrete);
                    }
                    
                    // Calcula o desconto do Pix apenas sobre o valor dos produtos
                    var descontoPix = parseFloat(response.data.desconto_pix) || 0;
                    var valorComDescontoPix = cartTotal * (1 - (descontoPix / 100));
                    
                    // Adiciona o frete ao valor com desconto
                    var valorFinalPix = valorComDescontoPix + shippingTotal;
                    
                    // Formata o valor final
                    var valorFormatado = new Intl.NumberFormat('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    }).format(valorFinalPix);

                    $pixElement.html('Total à vista no Pix: ' + valorFormatado);
                } else {
                    console.log('Resposta inválida do servidor:', response);
                }
            },
            error: function(xhr, status, error) {
                console.log('Erro ao atualizar valor do Pix:', error);
                if (updateAttempts < maxUpdateAttempts) {
                    updateAttempts++;
                    setTimeout(updatePixValue, 1000); // Tenta novamente após 1 segundo
                }
            }
        });
    }

    // Debounce para evitar múltiplas chamadas em sequência
    var updateTimeout;
    function debounceUpdate(callback, delay) {
        clearTimeout(updateTimeout);
        updateTimeout = setTimeout(callback, delay || 300);
    }

    // Função para verificar se o carrinho está vazio
    function verificarCarrinho() {
        var $cartItems = $cartTable.find('tbody tr');
        var $cartTotal = $cartTable.find('.cart-subtotal .amount');
        var totalText = $cartTotal.text().trim();
        var total = parseFloat(totalText.replace(/[^0-9,-]/g, '').replace(',', '.')) || 0;
        
        if ($cartItems.length === 0 || total <= 0) {
            console.log('Carrinho vazio detectado');
            if (restaurarEstadoCarrinho()) {
                // Força atualização do checkout
                $.ajax({
                    url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
                    type: 'POST',
                    success: function(data) {
                        if (data && data.fragments) {
                            $.each(data.fragments, function(key, value) {
                                $(key).replaceWith(value);
                            });
                        }
                    }
                });
            }
        }
    }

    // Atualiza o valor do Pix junto com as atualizações do WooCommerce
    $(document.body).on('updated_checkout', function() {
        debounceUpdate(function() {
            updatePixValue();
            verificarCarrinho();
        });
    });

    // Atualiza o valor do Pix quando o método de pagamento é alterado
    $(document.body).on('payment_method_selected', function() {
        debounceUpdate(function() {
            updatePixValue();
            verificarCarrinho();
        });
    });

    // Atualiza o valor do Pix quando o endereço é alterado
    $(document.body).on('updated_checkout', function() {
        debounceUpdate(function() {
            updatePixValue();
            verificarCarrinho();
        });
    });

    // Atualiza o valor do Pix quando o frete é alterado
    $(document.body).on('shipping_method_selected', function() {
        debounceUpdate(function() {
            updatePixValue();
            verificarCarrinho();
        }, 1000);
    });

    // Atualiza o valor do Pix quando a página carrega
    $(window).on('load', function() {
        debounceUpdate(function() {
            updatePixValue();
            verificarCarrinho();
        });
    });

    // Monitora erros de AJAX
    $(document).ajaxError(function(event, jqXHR, settings, error) {
        console.log('Erro AJAX no checkout:', error);
        if (settings.url.indexOf('update_order_review') !== -1 || 
            settings.url.indexOf('update_shipping_method') !== -1) {
            verificarCarrinho();
        }
    });

    // Verifica o carrinho periodicamente
    setInterval(function() {
        verificarCarrinho();
    }, 30000);
}); 