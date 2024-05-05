<?php
class TabelaParcelamentoShortcode {
    public function parcelas_flex_tabela_parcelamento_shortcode() {
        global $product;
        $texto_melhor_parcela = get_option('parcelas_flex_texto_melhor_parcela', 'sem juros');
        $texto_melhor_parcelas_cjuros = get_option('parcelas_flex_texto_melhor_parcelas_cjuros', 'com juros');

        if (!$product || !($product instanceof WC_Product)) {
            return '<div id="tabela-parcelamento-container">Produto não encontrado.</div>';
        }

        $preco = $product->is_type('variable') ? $product->get_variation_price('min', true) : $product->get_price();
        $preco = floatval($preco);
        $valor_minimo_parcela = floatval(get_option('valor_minimo_parcela', 0)); // Valor definido pelo usuário
        $exibir_juros = get_option('exibir_juros_porcentagem', 0); // Obtenha a opção de exibir juros

        $output = "<div id='tabela-parcelamento-container'><table class='tabela-parcelamento'><tbody>";

        // Verifica se o preço do produto é maior que zero
        if ($preco >= 0 && ($preco > $valor_minimo_parcela || $valor_minimo_parcela == 0)) {
            // Se o preço do produto for menor ou igual a zero, exiba apenas 1x sem juros
            if ($preco == 0 || $preco <= $valor_minimo_parcela) {
                $output .= "<tr><td>1x de " . wc_price($preco) . " " . esc_html($texto_melhor_parcela) . "</td></tr>";
            } else {
                // Se o preço for maior, calcule as parcelas normalmente
                for ($i = 1; $i <= 12; $i++) {
                    $juros = get_option("parcelamento_juros_$i", ''); // Alterada para obter a taxa de juros como string

                    // Verifica se o campo de juros foi preenchido
                    if ($juros !== '' && is_numeric($juros) && floatval($juros) >= 0) {
                        $juros = floatval($juros);

                        // Se a taxa de juros for zero, trata como "sem juros"
                        if ($juros == 0) {
                            $output .= "<tr><td>{$i}x de " . wc_price($preco / $i) . " " . esc_html($texto_melhor_parcela) . "</td></tr>";
                        } else {
                            $valor_total_com_juros = $preco * (1 + ($juros / 100));
                            $valor_parcela = $valor_total_com_juros / $i;

                            // Verifique se a opção de exibir juros está ativada antes de adicionar ao output
                            if ($exibir_juros) {
                                $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcelas_cjuros) . " de {$juros}%</td></tr>";
                            } else {
                                $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcelas_cjuros) . "</td></tr>";
                            }
                        }
                    }
                }
            }
        }

        $output .= "</tbody></table></div>"; // Fechamos o div do contêiner aqui
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
        $exibir_juros = get_option('exibir_juros_porcentagem', 0); // Obtenha a opção de exibir juros

        $output = "<table class='tabela-parcelamento'><tbody>";

        // Verifica se o preço do produto é maior que zero
        if ($preco >= 0 && ($preco > $valor_minimo_parcela || $valor_minimo_parcela == 0)) {
            // Se o preço do produto for menor ou igual a zero, exiba apenas 1x sem juros
            if ($preco == 0 || $preco <= $valor_minimo_parcela) {
                $output .= "<tr><td>1x de " . wc_price($preco) . " " . esc_html($texto_melhor_parcela) . "</td></tr>";
            } else {
                // Se o preço for maior, calcule as parcelas normalmente
                $valores_preenchidos = false; // Flag para verificar se pelo menos um valor foi preenchido

                for ($i = 1; $i <= 12; $i++) {
                    $juros = get_option("parcelamento_juros_$i", ''); // Obtenha o valor do juros

                    // Verifica se o campo de juros foi preenchido com um valor
                    if ($juros !== '') {
                        $juros = floatval($juros);

                        // Se a taxa de juros for zero, trata como "sem juros"
                        if ($juros == 0) {
                            $output .= "<tr><td>{$i}x de " . wc_price($preco / $i) . " " . esc_html($texto_melhor_parcela) . "</td></tr>";
                            $valores_preenchidos = true;
                        } else {
                            $valor_total_com_juros = $preco * (1 + ($juros / 100));
                            $valor_parcela = $valor_total_com_juros / $i;

                            // Verifique se a opção de exibir juros está ativada antes de adicionar ao output
                            if ($exibir_juros) {
                                $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcelas_cjuros) . " de {$juros}%</td></tr>";
                            } else {
                                $output .= "<tr><td>{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcelas_cjuros) . "</td></tr>";
                            }

                            $valores_preenchidos = true;
                        }
                    }
                }

                // Se nenhum valor foi preenchido, retorna uma mensagem de erro
                if (!$valores_preenchidos) {
                    wp_send_json_error('Nenhum valor de juros foi configurado.');
                    wp_die();
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

