jQuery(document).ready(function ($) {
    // Função genérica para atualizar o desconto e a melhor parcela
    function atualizarInformacoes(tipo, preco) {
        if (!preco) return;

        var action = '';
        var container = '';

        switch (tipo) {
            case 'pix':
                action = 'buscar_desconto_pix';
                container = '#desconto-pix-container';
                break;
            case 'boleto':
                action = 'buscar_desconto_boleto';
                container = '#desconto-boleto-container';
                break;
            case 'melhor_parcela':
                action = 'buscar_melhor_parcela';
                container = '#melhor-parcelas_container';
                break;
            case 'tabela_parcelamento':
                action = 'buscar_tabela_parcelamento';
                container = '#tabela-parcelamento-container';
                break;
            case 'economize':
                action = 'buscar_economize';
                container = '#economize-container';
                break;
        }

        $.ajax({
            url: parcelaFlexDeParcelamento.ajax_url,
            type: 'POST',
            data: {
                action: action,
                preco: preco,
                timestamp: new Date().getTime() // Adiciona um parâmetro de data e hora
            },
            success: function (response) {
                if (response.success) {
                    $(container).empty().html(response.data);
                } else {
                    $(container).html('Não foi possível obter as informações para ' + tipo + '.');
                }
            },
            error: function (xhr, status, error) {
                $(container).html('Erro ao buscar as informações para ' + tipo + ': ' + error);
            }
        });
    }

    // Função para obter o preço do produto ou variação
    function obterPrecoInicial() {
        var precoBaseText = $('.single_variation_wrap .woocommerce-variation-price .woocommerce-Price-amount.amount').text();
        if (precoBaseText) {
            return precoBaseText.replace(/[^0-9,.-]/g, '').replace(',', '.');
        }
        return null;
    }

    // Atualiza todas as informações com o preço inicial do produto ou variação
    function atualizarInformacoesVariacao(preco) {
        atualizarInformacoes('pix', preco);
        atualizarInformacoes('boleto', preco);
        atualizarInformacoes('melhor_parcela', preco);
        atualizarInformacoes('tabela_parcelamento', preco);
        atualizarInformacoes('economize', preco); // Adiciona a chamada para "economize"
    }

    // Atualiza as informações quando uma variação é encontrada
    $('form.variations_form').on('found_variation', function (event, variation) {
        if (variation.display_price) {
            atualizarInformacoesVariacao(variation.display_price);
        }
    });

    // Atualiza as informações quando os dados da variação são redefinidos
    $('form.variations_form').on('reset_data', function () {
        var precoBase = obterPrecoInicial();
        if (precoBase) {
            atualizarInformacoesVariacao(precoBase);
        }
    });

    // Atualiza as informações ao carregar a página
    $(window).on('load', function () {
        var precoInicial = obterPrecoInicial();
        if (precoInicial) {
            atualizarInformacoesVariacao(precoInicial);
        }
    });
});