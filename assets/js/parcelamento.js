jQuery(document).ready(function ($) {
    // Cache dos elementos DOM frequentemente acessados
    const $form = $('form.variations_form');
    const $priceElement = $('.single_variation_wrap .woocommerce-variation-price .woocommerce-Price-amount.amount');
    const containers = {
        pix: '#desconto-pix-container',
        boleto: '#desconto-boleto-container',
        melhor_parcela: '#melhor-parcelas_container',
        tabela_parcelamento: '#tabela-parcelamento-container',
        economize: '#economize-container'
    };

    // Cache das ações AJAX
    const actions = {
        pix: 'buscar_desconto_pix',
        boleto: 'buscar_desconto_boleto',
        melhor_parcela: 'buscar_melhor_parcela',
        tabela_parcelamento: 'buscar_tabela_parcelamento',
        economize: 'buscar_economize'
    };

    // Debounce para evitar múltiplas chamadas em sequência
    let updateTimeout;
    function debounceUpdate(callback, delay = 300) {
        clearTimeout(updateTimeout);
        updateTimeout = setTimeout(callback, delay);
    }

    // Função genérica para atualizar o desconto e a melhor parcela
    function atualizarInformacoes(tipo, preco) {
        if (!preco) return;

        $.ajax({
            url: parcelaFlexDeParcelamento.ajax_url,
            type: 'POST',
            data: {
                action: actions[tipo],
                preco: preco,
                nonce: parcelaFlexDeParcelamento.nonce
            },
            success: function (response) {
                if (response.success) {
                    $(containers[tipo]).empty().html(response.data);
                } else {
                    $(containers[tipo]).html('Não foi possível obter as informações para ' + tipo + '.');
                }
            },
            error: function (xhr, status, error) {
                $(containers[tipo]).html('Erro ao buscar as informações para ' + tipo + ': ' + error);
            }
        });
    }

    // Função para obter o preço do produto ou variação
    function obterPrecoInicial() {
        const precoBaseText = $priceElement.text();
        if (precoBaseText) {
            return precoBaseText.replace(/[^0-9,.-]/g, '').replace(',', '.');
        }
        return null;
    }

    // Atualiza todas as informações com o preço inicial do produto ou variação
    function atualizarInformacoesVariacao(preco) {
        if (!preco) return;

        // Atualiza todas as informações em paralelo
        const promises = Object.keys(actions).map(tipo => {
            return new Promise((resolve) => {
                atualizarInformacoes(tipo, preco);
                resolve();
            });
        });

        Promise.all(promises).catch(error => {
            console.error('Erro ao atualizar informações:', error);
        });
    }

    // Eventos para produtos variáveis
    if ($form.length) {
        $form.on('show_variation', function (event, variation) {
            if (variation.display_price) {
                debounceUpdate(() => {
                    atualizarInformacoesVariacao(variation.display_price);
                });
            }
        });

        $form.on('hide_variation', function () {
            debounceUpdate(() => {
                const precoBase = obterPrecoInicial();
                if (precoBase) {
                    atualizarInformacoesVariacao(precoBase);
                }
            });
        });

        $form.on('woocommerce_variation_has_changed', function () {
            debounceUpdate(() => {
                const precoBase = obterPrecoInicial();
                if (precoBase) {
                    atualizarInformacoesVariacao(precoBase);
                }
            });
        });
    }

    // Atualiza as informações ao carregar a página
    $(window).on('load', function () {
        debounceUpdate(() => {
            const precoInicial = obterPrecoInicial();
            if (precoInicial) {
                atualizarInformacoesVariacao(precoInicial);
            }
        });
    });
});