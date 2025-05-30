jQuery(function($) {
    // Atualiza o valor do Pix quando o carrinho é atualizado
    $(document.body).on('updated_cart_totals', function() {
        // O valor do Pix será atualizado automaticamente através do fragmento
    });

    // Atualiza o valor do Pix quando um produto é adicionado ao carrinho
    $(document.body).on('added_to_cart', function() {
        // O valor do Pix será atualizado automaticamente através do fragmento
    });

    // Atualiza o valor do Pix quando um produto é removido do carrinho
    $(document.body).on('removed_from_cart', function() {
        // O valor do Pix será atualizado automaticamente através do fragmento
    });
}); 