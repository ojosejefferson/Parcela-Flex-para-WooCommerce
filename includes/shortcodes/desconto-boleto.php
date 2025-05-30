<?php 

class DescontoBoletoShortcode {
    public function parcelas_flexsconto_boleto_shortcode() {
        global $product;
        $texto_a_boleto = get_option('parcelas_flex_texto_a_boleto', 'à vista Boleto');
        $texto_no_boleto = get_option('parcelas_flex_texto_no_boleto', 'no Boleto');

        // Tenta obter o produto global se não estiver definido
        if (!is_a($product, 'WC_Product')) {
            $product = wc_get_product(get_the_ID());
        }

        // Se ainda não temos um produto, retornamos uma mensagem de erro
        if (!is_a($product, 'WC_Product')) {
            return '<p>Desconto no boleto disponível apenas na página de produtos ou em loops de produtos.</p>';
        }

        $output = "<div id='desconto-boleto-container'>";
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
        if ($produto_disponivel && $preco > 0) {
            $desconto_boleto = floatval(get_option('desconto_boleto', 0));
            $preco_com_desconto_boleto = $preco * (1 - ($desconto_boleto / 100));
            $preco_formatado = wc_price($preco_com_desconto_boleto);
            $output .= $this->parcelas_flex_gerar_html_desconto_boleto($preco_formatado, $desconto_boleto);
        }

        $output .= "</div>";
        return $output;
    }

    public function parcelas_flexsconto_boleto_loop_shortcode() {
        global $product;
        $texto_a_boleto = get_option('parcelas_flex_texto_a_boleto', 'à vista Boleto');
        $texto_no_boleto = get_option('parcelas_flex_texto_no_boleto', 'no Boleto');

        // Tenta obter o produto global se não estiver definido
        if (!is_a($product, 'WC_Product')) {
            $product = wc_get_product(get_the_ID());
        }

        // Se ainda não temos um produto, retornamos uma mensagem de erro
        if (!is_a($product, 'WC_Product')) {
            return '<p>Desconto no boleto disponível apenas na página de produtos ou em loops de produtos.</p>';
        }

        $output = "<div class='desconto-boleto-loop-container'>"; // Classe modificada para estilização específica do loop

        // Verifica se o produto é variável ou simples
        if ($product->is_type('variable')) {
            $preco = floatval($product->get_variation_price('min', true)); // Preço mínimo da variação
        } else {
            $preco = floatval($product->get_price()); // Preço atual do produto
        }

        $desconto_boleto = floatval(get_option('desconto_boleto', 0));
        $preco_com_desconto_boleto = $preco * (1 - ($desconto_boleto / 100));
        $preco_formatado = wc_price($preco_com_desconto_boleto);

        // Supondo que você tenha uma função chamada parcelas_flex_gerar_html_desconto_boleto
        $output .= $this->parcelas_flex_gerar_html_desconto_boleto($preco_formatado, $desconto_boleto);
        $output .= "</div>";

        return $output;
    }

    public function buscar_desconto_boleto() {
        if (!isset($_POST['preco'])) {
            wp_send_json_error('Preço não foi enviado.');
            wp_die();
        }

        $preco = floatval($_POST['preco']);
        $desconto_boleto = floatval(get_option('desconto_boleto', 0));
        $preco_com_desconto_boleto = $preco * (1 - ($desconto_boleto / 100));
        $preco_formatado = wc_price($preco_com_desconto_boleto);

        wp_send_json_success($this->parcelas_flex_gerar_html_desconto_boleto($preco_formatado, $desconto_boleto));
        wp_die();
    }

    private function parcelas_flex_gerar_html_desconto_boleto($preco_formatado, $desconto_boleto) {
        $texto_a_boleto = get_option('parcelas_flex_texto_a_boleto', 'à vista Boleto');
        $texto_no_boleto = get_option('parcelas_flex_texto_no_boleto', 'no Boleto');

        return '<div class="opcao-pagamento img-economize">
        <img src="' . plugin_dir_url(__FILE__) . '../src/imagem/icon-boleto.svg" alt="Ícone de desconto" width="20" height="20">
        <span class="preco"> ' . $preco_formatado . ' </span>
        <span class="parcelas">' . esc_html($texto_a_boleto) . ' <span> ' . esc_html($texto_no_boleto) .' </span></span>
        <div class="best-price__Badge-sc-1v0eo34-3 hWoKbG badge">' . (-$desconto_boleto) . '%</div>
    </div>';
    


    }
}

$desconto_boleto_shortcode = new DescontoBoletoShortcode();

add_shortcode('desconto_boleto', array($desconto_boleto_shortcode, 'parcelas_flexsconto_boleto_shortcode'));
add_shortcode('desconto_boleto_loop', array($desconto_boleto_shortcode, 'parcelas_flexsconto_boleto_loop_shortcode'));

add_action('wp_ajax_buscar_desconto_boleto', array($desconto_boleto_shortcode, 'buscar_desconto_boleto'));
add_action('wp_ajax_nopriv_buscar_desconto_boleto', array($desconto_boleto_shortcode, 'buscar_desconto_boleto'));
