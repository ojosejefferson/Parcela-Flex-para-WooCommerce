jQuery(function($) {
    // Função para verificar se o carrinho está vazio
    function verificarCarrinho() {
        if ($('.woocommerce-cart-form__cart-item').length === 0) {
            console.log('Carrinho vazio detectado');
            // Recarrega a página se o carrinho estiver vazio mas deveria ter itens
            if (localStorage.getItem('cart_has_items') === 'true') {
                window.location.reload();
            }
        }
    }

    // Função para salvar o estado do carrinho
    function salvarEstadoCarrinho() {
        var cartItems = $('.woocommerce-cart-form__cart-item').length;
        if (cartItems > 0) {
            localStorage.setItem('cart_has_items', 'true');
            localStorage.setItem('cart_items_count', cartItems);
        }
    }

    // Função para restaurar o carrinho se necessário
    function restaurarCarrinho() {
        var cartItems = $('.woocommerce-cart-form__cart-item').length;
        var savedItems = localStorage.getItem('cart_items_count');
        
        if (cartItems === 0 && savedItems > 0) {
            console.log('Tentando restaurar carrinho...');
            // Força atualização do carrinho
            $.ajax({
                url: wc_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
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

    // Atualiza o valor do Pix quando o carrinho é atualizado
    $(document.body).on('updated_cart_totals', function() {
        verificarCarrinho();
        salvarEstadoCarrinho();
    });

    // Atualiza o valor do Pix quando um produto é adicionado ao carrinho
    $(document.body).on('added_to_cart', function() {
        salvarEstadoCarrinho();
        verificarCarrinho();
    });

    // Atualiza o valor do Pix quando um produto é removido do carrinho
    $(document.body).on('removed_from_cart', function() {
        verificarCarrinho();
        salvarEstadoCarrinho();
    });

    // Monitora mudanças no método de entrega
    $(document.body).on('shipping_method_selected', function() {
        console.log('Método de entrega alterado');
        setTimeout(function() {
            restaurarCarrinho();
        }, 1000);
    });

    // Verifica o carrinho periodicamente
    setInterval(function() {
        verificarCarrinho();
        salvarEstadoCarrinho();
    }, 30000);

    // Verifica o carrinho quando a página carrega
    $(window).on('load', function() {
        verificarCarrinho();
        salvarEstadoCarrinho();
    });

    // Monitora erros de AJAX
    $(document).ajaxError(function(event, jqXHR, settings, error) {
        console.log('Erro AJAX detectado:', error);
        if (settings.url.indexOf('update_order_review') !== -1 || 
            settings.url.indexOf('update_shipping_method') !== -1) {
            restaurarCarrinho();
        }
    });
}); 