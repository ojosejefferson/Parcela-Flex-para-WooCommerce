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

        // Verifica se o produto é variável ou simples
        if ($product->is_type('variable')) {
            $preco = floatval($product->get_variation_price('min', true)); // Preço mínimo da variação
        } else {
            $preco = floatval($product->get_price()); // Preço atual do produto
        }

        $desconto_boleto = floatval(get_option('desconto_boleto', 0));
        $preco_com_desconto_boleto = $preco * (1 - ($desconto_boleto / 100));
        $preco_formatado = wc_price($preco_com_desconto_boleto);

        $output .= $this->parcelas_flex_gerar_html_desconto_boleto($preco_formatado, $desconto_boleto);
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
