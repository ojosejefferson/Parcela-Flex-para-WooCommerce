<?php
class MelhorParcelasShortcode {
    public function parcelas_flex_melhor_parcelas_shortcode() {
        global $product;
        $texto_melhor_parcela = get_option('parcelas_flex_texto_melhor_parcela', 'sem juros');

        if (!$product || !($product instanceof WC_Product)) {
            return '<div id="tabela-parcelamento-container">Produto não encontrado.</div>';
        }

        $preco = 0; // Inicializa o preço como zero

        // Verifica se o produto é variável
        if ($product->is_type('variable')) {
            // Obtém todas as variações do produto
            $variacoes = $product->get_available_variations();

            // Inicializa o preço mínimo como o maior valor possível
            $preco_minimo = PHP_FLOAT_MAX;

            // Itera sobre as variações para encontrar o preço mínimo
            foreach ($variacoes as $variacao) {
                $preco_variacao = floatval($variacao['display_price']); // Obtém o preço de exibição da variação
                if ($preco_variacao < $preco_minimo) {
                    $preco_minimo = $preco_variacao;
                }
            }

            $preco = $preco_minimo; // Define o preço como o preço mínimo encontrado
        } else {
            $preco = floatval($product->get_price()); // Obtém o preço do produto simples
        }

        // Verifica se o preço é maior que zero
        if ($preco > 0) {
            // Inicializa a variável para armazenar a última parcela sem juros
            $ultima_parcelas_sem_juros = '';

            // Calcula as parcelas normalmente
            for ($i = 1; $i <= 12; $i++) {
                $juros = get_option("parcelamento_juros_$i", ''); // Obtém a taxa de juros como string

                // Verifica se o campo de juros foi preenchido
                if ($juros !== '' && is_numeric($juros) && floatval($juros) >= 0) {
                    $juros = floatval($juros);

                    // Se a taxa de juros for zero, trata como "sem juros"
                    if ($juros == 0) {
                        $valor_parcela = $preco / $i;
                        $ultima_parcelas_sem_juros = "{$i}x de " . wc_price($valor_parcela) . " " . esc_html($texto_melhor_parcela);
                    }
                }
            }

            // Verifica se a última parcela sem juros foi encontrada
            if (!empty($ultima_parcelas_sem_juros)) {
                return '<div id="melhor-parcelas_container">
                <div class="opcao-pagamento" itemscope itemtype="http://schema.org/PaymentMethod">
                    <img src="' . plugin_dir_url(__FILE__) . '../src/imagem/icon-card.svg" alt="Ícone de cartão" width="20" height="20">
                    <span class="parcelas">' . $ultima_parcelas_sem_juros . '</span>
                </div>
            </div>';            
            }
        }

        return ''; // Se não houver parcela sem juros, retorna vazio
    }

    public function buscar_melhor_parcelas_sem_juros() {
        $texto_melhor_parcela = get_option('parcelas_flex_texto_melhor_parcela', 'sem juros');

        if (!isset($_POST['preco'])) {
            wp_send_json_error('Preço não foi enviado.');
            wp_die();
        }

        $preco = floatval($_POST['preco']);
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
                <div class="opcao-pagamento" itemscope itemtype="http://schema.org/PaymentMethod">
                <img src="' . plugin_dir_url(__FILE__) . '../src/imagem/icon-card.svg" alt="Ícone de boleto" width="20" height="20">
                <span class="parcelas">' . $melhor_parcelas_sem_juros . 'x de</span>
                    <span class="preco" itemprop="price">' . wc_price($valor_melhor_parcela) . '</span>
                    <span class="parcelas">' . esc_html($texto_melhor_parcela) . '</span>
                </div>
                ');
            }
        }

        // Se o preço do produto for menor ou igual ao valor mínimo para parcelamento, exiba apenas 1x sem juros
        wp_send_json_success('
        <div class="opcao-pagamento" itemscope itemtype="http://schema.org/PaymentMethod">
        <img src="' . plugin_dir_url(__FILE__) . '../src/imagem/icon-card.svg" alt="Ícone de boleto" width="20" height="20">
        <span class="parcelas">1x de</span>
            <span class="preco" itemprop="price">' . wc_price($preco) . '</span>
            <span class="parcelas">' . esc_html($texto_melhor_parcela) . '</span>
        </div>
        ');
    }
}

$melhor_parcelas_shortcode = new MelhorParcelasShortcode();

add_shortcode('melhor_parcelamento', array($melhor_parcelas_shortcode, 'parcelas_flex_melhor_parcelas_shortcode'));
add_action('wp_ajax_buscar_melhor_parcela', array($melhor_parcelas_shortcode, 'buscar_melhor_parcelas_sem_juros'));
add_action('wp_ajax_nopriv_buscar_melhor_parcela', array($melhor_parcelas_shortcode, 'buscar_melhor_parcelas_sem_juros'));
