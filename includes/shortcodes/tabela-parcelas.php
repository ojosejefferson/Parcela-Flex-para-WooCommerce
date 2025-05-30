<?php
class TabelaParcelamentoShortcode {
    public function parcelas_flex_tabela_parcelamento_shortcode() {
        global $product;
        $texto_melhor_parcela = get_option('parcelas_flex_texto_melhor_parcela', 'sem juros');
        $texto_melhor_parcelas_cjuros = get_option('parcelas_flex_texto_melhor_parcelas_cjuros', 'com juros');

        if (!$product || !($product instanceof WC_Product)) {
            return '<div id="tabela-parcelamento-container">Produto não encontrado.</div>';
        }

        $preco = 0;
        $produto_disponivel = false;

        // Verifica o tipo do produto
        switch ($product->get_type()) {
            case 'variable':
                // Produto variável
                $variacoes = $product->get_available_variations();
                $preco_minimo = PHP_FLOAT_MAX;

                foreach ($variacoes as $variacao) {
                    if ($variacao['is_purchasable'] && $variacao['is_in_stock']) {
                        $produto_disponivel = true;
                        $preco_variacao = floatval($variacao['display_price']);
                        if ($preco_variacao < $preco_minimo) {
                            $preco_minimo = $preco_variacao;
                        }
                    }
                }

                if ($produto_disponivel) {
                    $preco = $preco_minimo;
                }
                break;

            case 'grouped':
                // Produto agrupado
                $children = $product->get_children();
                $preco_minimo = PHP_FLOAT_MAX;

                foreach ($children as $child_id) {
                    $child = wc_get_product($child_id);
                    if ($child && $child->is_purchasable() && $child->is_in_stock()) {
                        $produto_disponivel = true;
                        $preco_filho = floatval($child->get_price());
                        if ($preco_filho < $preco_minimo) {
                            $preco_minimo = $preco_filho;
                        }
                    }
                }

                if ($produto_disponivel) {
                    $preco = $preco_minimo;
                }
                break;

            case 'external':
                // Produto externo
                if ($product->is_purchasable()) {
                    $produto_disponivel = true;
                    $preco = floatval($product->get_price());
                }
                break;

            default:
                // Produtos simples, virtuais, baixáveis
                if ($product->is_purchasable() && $product->is_in_stock()) {
                    $produto_disponivel = true;
                    $preco = floatval($product->get_price());
                }
                break;
        }

        // Verifica se o produto está disponível e tem preço válido
        if (!$produto_disponivel || $preco <= 0) {
            return '<div id="tabela-parcelamento-container"><table class="tabela-parcelamento"><tbody><tr><td>Produto indisponível</td></tr></tbody></table></div>';
        }

        $valor_minimo_parcela = floatval(get_option('valor_minimo_parcela', 0));
        $exibir_juros = get_option('exibir_juros_porcentagem', '0') === '1';

        $output = "<div id='tabela-parcelamento-container'><table class='tabela-parcelamento'><tbody>";

        // Sempre mostra todas as parcelas
        for ($i = 1; $i <= 12; $i++) {
            $juros = get_option("parcelamento_juros_$i", '');
            $juros = $juros === '' ? 0 : floatval($juros);

            // Se a taxa de juros for zero, trata como "sem juros"
            if ($juros == 0) {
                $output .= "<tr><td>{$i}x de " . wc_price($preco / $i) . " " . esc_html($texto_melhor_parcela) . "</td></tr>";
            } else {
                $valor_total_com_juros = $preco * (1 + ($juros / 100));
                $valor_parcela = $valor_total_com_juros / $i;

                if ($exibir_juros) {
                    $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcelas_cjuros) . " (" . number_format($juros, 1, ',', '.') . "%)</td></tr>";
                } else {
                    $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcelas_cjuros) . "</td></tr>";
                }
            }
        }

        $output .= "</tbody></table></div>";
        return $output;
    }

    public function buscar_tabela_parcelamento() {
        $texto_melhor_parcela = get_option('parcelas_flex_texto_melhor_parcela', 'sem juros');
        $texto_melhor_parcelas_cjuros = get_option('parcelas_flex_texto_melhor_parcelas_cjuros', 'com juros');

        if (!isset($_POST['preco'])) {
            wp_send_json_error('Preço não foi enviado.');
            wp_die();
        }

        $preco = floatval($_POST['preco']);
        $valor_minimo_parcela = floatval(get_option('valor_minimo_parcela', 0)); // Valor definido pelo usuário
        $exibir_juros = get_option('exibir_juros_porcentagem', '0') === '1'; // Corrigido para verificar se é '1'

        $output = "<table class='tabela-parcelamento'><tbody>";

        // Verifica se o preço do produto é maior que zero
        if ($preco > 0) {
            // Sempre mostra todas as parcelas
            for ($i = 1; $i <= 12; $i++) {
                $juros = get_option("parcelamento_juros_$i", ''); // Obtém a taxa de juros
                $juros = $juros === '' ? 0 : floatval($juros); // Se vazio, considera 0

                // Se a taxa de juros for zero, trata como "sem juros"
                if ($juros == 0) {
                    $output .= "<tr><td>{$i}x de " . wc_price($preco / $i) . " " . esc_html($texto_melhor_parcela) . "</td></tr>";
                } else {
                    $valor_total_com_juros = $preco * (1 + ($juros / 100));
                    $valor_parcela = $valor_total_com_juros / $i;

                    // Verifique se a opção de exibir juros está ativada antes de adicionar ao output
                    if ($exibir_juros) {
                        $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcelas_cjuros) . " (" . number_format($juros, 1, ',', '.') . "%)</td></tr>";
                    } else {
                        $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcelas_cjuros) . "</td></tr>";
                    }
                }
            }
        }

        $output .= "</tbody></table>";

        wp_send_json_success($output);
        wp_die();
    }
}

$tabela_parcelamento_shortcode = new TabelaParcelamentoShortcode();

add_shortcode('tabela_parcelamento', array($tabela_parcelamento_shortcode, 'parcelas_flex_tabela_parcelamento_shortcode'));

add_action('wp_ajax_buscar_tabela_parcelamento', array($tabela_parcelamento_shortcode, 'buscar_tabela_parcelamento'));
add_action('wp_ajax_nopriv_buscar_tabela_parcelamento', array($tabela_parcelamento_shortcode, 'buscar_tabela_parcelamento'));

