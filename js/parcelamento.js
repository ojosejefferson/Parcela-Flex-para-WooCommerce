jQuery(document).ready(function ($) {
    // Função genérica para atualizar o desconto e a melhor parcela
    function atualizarInformacoes(tipo, preco) {
        if (!preco) return; // Se o preço não for definido, não faz nada

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
                container = '#melhor-parcela-container';
                break;
            case 'tabela_parcelamento':
                action = 'buscar_tabela_parcelamento';
                container = '#tabela-parcelamento-container'; // Certifique-se de que este ID corresponde ao ID do contêiner no HTML
                break;
        }

        $.ajax({
            url: meuPluginDeParcelamento.ajax_url,
            type: 'POST',
            data: {
                action: action,
                preco: preco
            },
            success: function (response) {
                if (response.success) {
                    // Limpa o contêiner antes de adicionar o novo conteúdo
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



    // Função para obter o preço do produto simples ou o preço padrão da variação
    function obterPrecoInicial() {
        var precoBaseText = $('').first().text(); // Este seletor deve ser ajustado conforme o tema
        if (precoBaseText) {
            return precoBaseText.replace(/[^0-9,.-]/g, '').replace(',', '.');
        }
        return null;
    }

    // Atualiza todas as informações com o preço inicial do produto ou variação
    var precoInicial = obterPrecoInicial();
    if (precoInicial) {
        atualizarInformacoes('pix', precoInicial);
        atualizarInformacoes('boleto', precoInicial);
        atualizarInformacoes('melhor_parcela', precoInicial);
        atualizarInformacoes('tabela_parcelamento', precoInicial);
    }
    // Atualiza as informações com base no preço inicial ou variação
    function atualizarInformacoesVariacao(preco) {
        atualizarInformacoes('pix', preco);
        atualizarInformacoes('boleto', preco);
        atualizarInformacoes('melhor_parcela', preco);
        atualizarInformacoes('tabela_parcelamento', preco);
    }

    // Atualiza as informações quando uma variação é selecionada
    $('form.variations_form').on('found_variation', function (event, variation) {
        if (variation.display_price) {
            atualizarInformacoesVariacao(variation.display_price);
        }
    }).on('reset_data', function () {
        var precoBase = obterPrecoInicial();
        if (precoBase) {
            atualizarInformacoesVariacao(precoBase);
        }
    });

    // Atualiza as informações ao carregar a página
    var precoInicial = obterPrecoInicial();
    if (precoInicial) {
        atualizarInformacoesVariacao(precoInicial);
    }
    // Tenta obter o preço do produto simples ou o preço padrão da variação ao carregar a página
    $(window).on('load', function () {
        var precoBase = obterPrecoInicial();
        if (precoBase) {
            atualizarInformacoes('pix', precoBase);
            atualizarInformacoes('boleto', precoBase);
            atualizarInformacoes('melhor_parcela', precoBase);
            atualizarInformacoes('tabela_parcelamento', precoBase);
        }
    });
});



jQuery(document).ready(function ($) {
    // ...

    // Atualiza as informações quando uma variação é selecionada
    $('form.variations_form').on('found_variation', function (event, variation) {
        if (variation.display_price && variation.display_regular_price) {
            atualizarEconomize(variation.display_regular_price, variation.display_price);
        }
    }).on('reset_data', function () {
        var precoBase = obterPrecoInicial();
        if (precoBase) {
            atualizarEconomize(precoBase, precoBase); // Assuming the base price is the same for regular and sale prices
        }
    });

    // Função para atualizar a economia
    function atualizarEconomize(preco_regular, preco_venda) {
        $.ajax({
            url: meuPluginDeParcelamento.ajax_url,
            type: 'POST',
            data: {
                action: 'buscar_economize',
                preco_regular: preco_regular,
                preco_venda: preco_venda
            },
            success: function (response) {
                if (response.success) {
                    $('#economize-container').empty().html(response.data);
                } else {
                    $('#economize-container').html('Não foi possível obter as informações de economia.');
                }
            },
            error: function (xhr, status, error) {
                $('#economize-container').html('Erro ao buscar as informações de economia: ' + error);
            }
        });
    }

    // ...
});


