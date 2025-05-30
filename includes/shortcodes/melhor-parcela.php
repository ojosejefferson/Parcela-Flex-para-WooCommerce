<?php
class MelhorParcelasShortcode {
    public function parcelas_flex_melhor_parcelas_shortcode() {
        global $product;
        $texto_melhor_parcela = get_option('parcelas_flex_texto_melhor_parcela', 'sem juros');

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
            return '';
        }

        // Inicializa a variável para armazenar a última parcela sem juros
        $ultima_parcelas_sem_juros = '';
        $melhor_parcela = 0;

        // Calcula as parcelas normalmente
        for ($i = 1; $i <= 12; $i++) {
            $juros = get_option("parcelamento_juros_$i", '');

            if ($juros !== '' && is_numeric($juros) && floatval($juros) >= 0) {
                $juros = floatval($juros);

                if ($juros == 0) {
                    $valor_parcela = $preco / $i;
                    $melhor_parcela = $i;
                    $ultima_parcelas_sem_juros = "{$i}x de <span class=\"precos\">" . wc_price($valor_parcela) . "</span> " . esc_html($texto_melhor_parcela);
                }
            }
        }

        // Verifica se a última parcela sem juros foi encontrada
        if (!empty($ultima_parcelas_sem_juros)) {
            return '<div id="melhor-parcelas_container">
            <div class="opcao-pagamento">
                <img src="' . plugin_dir_url(__FILE__) . '../src/imagem/icon-card.svg" alt="Ícone de cartão" width="20" height="20">
                <span class="">'. $ultima_parcelas_sem_juros . '</span>
            </div>
        </div>';            
        }

        return '';
    }

    public function buscar_melhor_parcelas_sem_juros() {
        $texto_melhor_parcela = get_option('parcelas_flex_texto_melhor_parcela', 'sem juros');

        if (!isset($_POST['preco'])) {
            wp_send_json_error('Preço não foi enviado.');
            wp_die();
        }

        $preco = floatval($_POST['preco']);
        
        // Verifica se o preço é válido
        if ($preco <= 0 || $preco > 999999999) {
            wp_send_json_success('');
            wp_die();
        }

        $valor_minimo_parcela = floatval(get_option('valor_minimo_parcela', 0)); // Valor definido pelo usuário
        $melhor_parcelas_sem_juros = 0;
        $valor_melhor_parcela = PHP_FLOAT_MAX;

        // Verifica se o preço do produto é maior que o valor mínimo para parcelamento
        if ($preco > $valor_minimo_parcela) {
            // Encontrar a última parcela sem juros configurada
            for ($i = 1; $i <= 12; $i++) {
                $juros = floatval(get_option("parcelamento_juros_$i", ''));
                if ($juros == 0) {
                    $melhor_parcelas_sem_juros = $i;
                    $valor_melhor_parcela = $preco / $melhor_parcelas_sem_juros;
                } else {
                    break; // Se encontrar juros, interrompe a busca
                }
            }

            // Se a última parcela sem juros for encontrada, mostra como melhor parcela
            if ($melhor_parcelas_sem_juros > 0) {
                wp_send_json_success('
                <div class="opcao-pagamento">
                <img src="' . plugin_dir_url(__FILE__) . '../src/imagem/icon-card.svg" alt="Ícone de boleto" width="20" height="20">
                <span class="parcelas">' . $melhor_parcelas_sem_juros . 'x de</span>
                    <span class="preco">' . wc_price($valor_melhor_parcela) . '</span>
                    <span class="parcelas">' . esc_html($texto_melhor_parcela) . '</span>
                </div>
                ');
                wp_die();
            }
        }

        // Se o preço do produto for menor ou igual ao valor mínimo para parcelamento, exiba apenas 1x sem juros
        wp_send_json_success('
        <div class="opcao-pagamento">
        <img src="' . plugin_dir_url(__FILE__) . '../src/imagem/icon-card.svg" alt="Ícone de boleto" width="20" height="20">
        <span class="parcelas">1x de</span>
            <span class="preco">' . wc_price($preco) . '</span>
            <span class="parcelas">' . esc_html($texto_melhor_parcela) . '</span>
        </div>
        ');
        wp_die();
    }
}

$melhor_parcelas_shortcode = new MelhorParcelasShortcode();

add_shortcode('melhor_parcelamento', array($melhor_parcelas_shortcode, 'parcelas_flex_melhor_parcelas_shortcode'));
add_action('wp_ajax_buscar_melhor_parcela', array($melhor_parcelas_shortcode, 'buscar_melhor_parcelas_sem_juros'));
add_action('wp_ajax_nopriv_buscar_melhor_parcela', array($melhor_parcelas_shortcode, 'buscar_melhor_parcelas_sem_juros'));
